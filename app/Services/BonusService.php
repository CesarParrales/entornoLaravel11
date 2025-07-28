<?php

namespace App\Services;

use App\Models\BonusType;
use App\Models\User;
use App\Models\Order;
use App\Models\Period;
use App\Models\UserPeriodRank;
use App\Models\UserLoyaltyProductLedger;
use App\Models\Rank;
use App\Models\FinancialFreedomCommissionTier;
use App\Models\RecognitionBonusTier;
use App\Models\UserCarBonusProgress;
use App\Models\UserEarnedAward; // Añadido
use App\Models\WalletTransaction;
use App\Events\UserEarnedNonMonetaryAwardEvent; // Añadido
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BonusService
{
    protected WalletService $walletService;
    private ?string $lastQualifiedRecognitionRankName = null;
    private ?array $lastCarBonusPaymentDetails = null;
    private ?string $lastTripAwardDescriptionDetails = null; // Para Bono Viaje

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    protected function getApplicableBonusTypes(string $eventName): Collection
    {
        return BonusType::where('is_active', true)
                        ->where('trigger_event', $eventName)
                        ->get();
    }

    public function processEvent(string $eventName, $eventPayload, User $beneficiary): void
    {
        $applicableBonusTypes = $this->getApplicableBonusTypes($eventName);
        $this->lastQualifiedRecognitionRankName = null;
        $this->lastCarBonusPaymentDetails = null;
        $this->lastTripAwardDescriptionDetails = null;

        foreach ($applicableBonusTypes as $bonusType) {
            if (!$this->checkBonusConditions($bonusType, $eventPayload, $beneficiary)) {
                $payloadIdentifier = 'N/A';
                if (is_object($eventPayload) && property_exists($eventPayload, 'id')) {
                    $payloadIdentifier = $eventPayload->id;
                } elseif ($eventPayload instanceof User) {
                    $payloadIdentifier = "UserID:{$eventPayload->id}";
                } elseif ($eventPayload instanceof Carbon) {
                    $payloadIdentifier = "Date:{$eventPayload->toDateString()}";
                }
                Log::info("[BonusService] Bono '{$bonusType->name}' (ID: {$bonusType->id}) no cumple condiciones para beneficiario ID {$beneficiary->id}.", ['event' => $eventName, 'payload_id' => $payloadIdentifier ]);
                continue;
            }

            $metadataForWalletTx = ['bonus_type_id' => $bonusType->id, 'bonus_type_slug' => $bonusType->slug];
            if ($eventPayload instanceof Period) {
                $metadataForWalletTx['period_id'] = $eventPayload->id;
            } elseif ($eventPayload instanceof User && $bonusType->trigger_event === 'user_annual_review') {
                // $metadataForWalletTx['anniversary_year_ending'] = ($eventPayload->anniversary_end_date ?? Carbon::now())->toDateString();
            } elseif ($bonusType->trigger_event === 'monthly_bonus_review' && $eventPayload instanceof Carbon) {
                 $metadataForWalletTx['month_evaluated'] = $eventPayload->format('Y-m');
            }

            if ($bonusType->slug === 'bono-fidelizacion-rango') {
                $productsToAward = $this->calculateProductsToAward($bonusType, $eventPayload, $beneficiary);
                if ($productsToAward > 0) {
                    $this->awardLoyaltyProducts($beneficiary, $productsToAward, $bonusType, $eventPayload);
                } else {
                    Log::info("[BonusService] Cero productos a otorgar para Bono Fidelización '{$bonusType->name}' para beneficiario ID {$beneficiary->id}.");
                }
            } elseif ($bonusType->slug === 'bono-viaje-anual') {
                // $eventPayload para 'user_annual_review' es el User. La fecha de evaluación se toma como Carbon::now() o del payload si se añade.
                $evaluationDate = ($eventPayload instanceof User && isset($eventPayload->anniversary_end_date)) 
                                ? Carbon::parse($eventPayload->anniversary_end_date) 
                                : Carbon::now();
                $awardDetails = $this->determineNonMonetaryAwardDetails($bonusType, $beneficiary, $evaluationDate);
                
                if ($awardDetails && isset($awardDetails['award_description'])) {
                    UserEarnedAward::create([
                        'user_id' => $beneficiary->id,
                        'bonus_type_id' => $bonusType->id,
                        'award_description' => $awardDetails['award_description'],
                        'awarded_at' => Carbon::now(),
                        'status' => 'pending_claim', 
                    ]);
                    Log::info("[BonusService] Premio '{$bonusType->name}' otorgado a Usuario ID {$beneficiary->id}. Descripción: {$awardDetails['award_description']}");
                    UserEarnedNonMonetaryAwardEvent::dispatch($beneficiary, $bonusType, $awardDetails['award_description']);
                } else {
                    Log::info("[BonusService] Bono Viaje '{$bonusType->name}' no ganado por Usuario ID {$beneficiary->id} este año.");
                }
            } else { 
                $amount = $this->calculateBonusAmount($bonusType, $eventPayload, $beneficiary);
                if ($amount > 0) {
                    $description = $this->generateTransactionDescription($bonusType, $eventPayload, $beneficiary);
                    
                    $transaction = $this->walletService->credit(
                        $beneficiary,
                        $amount,
                        'bonus_payout',
                        $description,
                        $eventPayload, 
                        $metadataForWalletTx
                    );

                    if ($transaction) {
                        Log::info("Bono '{$bonusType->name}' de {$amount} acreditado al usuario ID {$beneficiary->id}. Transacción ID: {$transaction->transaction_uuid}");
                        if ($bonusType->slug === 'bono-inicio-rapido' && $eventPayload instanceof Order) {
                            $newSocio = $eventPayload->user;
                            if ($newSocio) {
                               $this->markBonusAsPaid($beneficiary, $newSocio, $bonusType, $eventPayload);
                            }
                        } elseif ($bonusType->slug === 'bono-auto') {
                            $this->updateCarBonusProgress($beneficiary, $bonusType, $config = is_string($bonusType->configuration_details) ? json_decode($bonusType->configuration_details, true) : ($bonusType->configuration_details ?? []));
                        }
                    } else {
                        Log::error("Error al acreditar bono '{$bonusType->name}' al usuario ID {$beneficiary->id}.");
                    }
                } else {
                     Log::info("[BonusService] Monto/cantidad calculado para bono '{$bonusType->name}' (ID: {$bonusType->id}) es cero para beneficiario ID {$beneficiary->id}.");
                }
            }
        }
    }

    protected function getRankLevel(string $rankSlug, array $rankHierarchy): ?int
    {
        foreach ($rankHierarchy as $entry) {
            if (isset($entry['slug']) && $entry['slug'] === $rankSlug) {
                return $entry['level'];
            }
        }
        Log::warning("[BonusService] getRankLevel: Slug de rango '{$rankSlug}' no encontrado en la jerarquía proporcionada.");
        return null;
    }

    protected function userHasActiveSubscription(User $user, $context = null): bool
    {
        Log::warning("[BonusService] userHasActiveSubscription: Lógica PENDIENTE. Asumiendo activo para usuario ID {$user->id}.");
        return true; 
    }
    
    protected function getUserPeriodCommissionableVolume(User $user, Period $closedPeriod): int
    {
        $userPeriodRank = UserPeriodRank::where('user_id', $user->id)
                                    ->where('period_id', $closedPeriod->id)
                                    ->first();
        if ($userPeriodRank && isset($userPeriodRank->calculation_details['commissionable_points'])) {
            return (int)$userPeriodRank->calculation_details['commissionable_points'];
        }
        Log::warning("[BonusService] getUserPeriodCommissionableVolume: Lógica PENDIENTE o datos no encontrados. Usando 0 para usuario ID {$user->id} en periodo {$closedPeriod->id}.");
        return 0;
    }

    protected function checkBonusConditions(BonusType $bonusType, $eventPayload, User $beneficiary): bool
    {
        if ($bonusType->slug === 'bono-inicio-rapido') {
            if (!$eventPayload instanceof Order) { Log::warning("[Bono Inicio Rápido] Payload no es Order."); return false; }
            $newSocio = $eventPayload->user;
            if (!$newSocio) { Log::warning("[Bono Inicio Rápido] No user en Order."); return false; }
            if (!$newSocio->referrer_id || $newSocio->referrer_id !== $beneficiary->id) { Log::info("[Bono Inicio Rápido] Beneficiario no es referrer."); return false; }
            $previousPaidOrdersCount = Order::where('user_id', $newSocio->id)->where('payment_status', 'paid')->where('id', '<', $eventPayload->id)->count();
            if ($previousPaidOrdersCount > 0) { Log::info("[Bono Inicio Rápido] No es primer pedido pagado."); return false; }
            if ($this->hasBonusBeenPaid($beneficiary, $newSocio, $bonusType)) { Log::info("[Bono Inicio Rápido] Ya pagado."); return false; }
            return true;
        } elseif ($bonusType->slug === 'bono-reconsumo-puntos') {
            if (!$eventPayload instanceof Order) { Log::warning("[Bono Reconsumo] Payload no es Order."); return false; }
            if ($eventPayload->user_id !== $beneficiary->id) { Log::info("[Bono Reconsumo] Beneficiario no es dueño de la orden."); return false; }
            return true;
        } elseif ($bonusType->slug === 'bono-referido') {
            if (!$eventPayload instanceof Order) { Log::warning("[Bono Referido] Payload no es Order."); return false; }
            $buyer = $eventPayload->user;
            if (!$buyer) { Log::warning("[Bono Referido] No user en Order."); return false; }
            if (!$buyer->referrer_id || $buyer->referrer_id !== $beneficiary->id) { Log::info("[Bono Referido] Beneficiario no es referrer."); return false; }
            if (!$eventPayload->items()->where('product_pays_bonus_at_purchase', true)->exists()) { Log::info("[Bono Referido] Orden sin productos calificables."); return false; }
            $config = is_string($bonusType->configuration_details) ? json_decode($bonusType->configuration_details, true) : ($bonusType->configuration_details ?? []);
            $checkBuyerStatus = $config['check_buyer_status'] ?? true;
            if ($checkBuyerStatus) {
                $requiredStatus = $config['required_buyer_status'] ?? 'active';
                if ($buyer->status !== $requiredStatus) { Log::info("[Bono Referido] Comprador no tiene estado requerido."); return false; }
            }
            return true;
        } elseif ($bonusType->slug === 'bono-fidelizacion-rango') {
            if (!$eventPayload instanceof Period) { Log::warning("[Bono Fidelización] Payload no es Period."); return false; }
            $closedPeriod = $eventPayload;
            if (!$this->userHasActiveSubscription($beneficiary, $closedPeriod)) { Log::info("[Bono Fidelización] Usuario sin suscripción activa."); return false; }
            $previousPeriod = Period::where('end_date', '<', $closedPeriod->start_date)->orderBy('end_date', 'desc')->first();
            if (!$previousPeriod) { Log::info("[Bono Fidelización] No hay periodo anterior."); return false; }
            $currentPeriodRank = UserPeriodRank::where('user_id', $beneficiary->id)->where('period_id', $closedPeriod->id)->with('rank')->first();
            $previousPeriodRank = UserPeriodRank::where('user_id', $beneficiary->id)->where('period_id', $previousPeriod->id)->with('rank')->first();
            if (!$currentPeriodRank || !$previousPeriodRank || !$currentPeriodRank->rank || !$previousPeriodRank->rank) { Log::info("[Bono Fidelización] Sin registros de rango para ambos periodos."); return false; }
            $config = is_string($bonusType->configuration_details) ? json_decode($bonusType->configuration_details, true) : ($bonusType->configuration_details ?? []);
            $rankHierarchy = $config['rank_hierarchy_for_comparison'] ?? [];
            $minQualifyingRankSlug = $config['min_qualifying_rank_slug'] ?? null;
            if (empty($rankHierarchy) || !$minQualifyingRankSlug) { Log::error("[Bono Fidelización] Falta config de jerarquía o rango mínimo."); return false; }
            $minQualifyingLevel = $this->getRankLevel($minQualifyingRankSlug, $rankHierarchy);
            if ($minQualifyingLevel === null) { Log::error("[Bono Fidelización] Rango mínimo no encontrado en jerarquía."); return false; }
            $currentRankLevel = $this->getRankLevel($currentPeriodRank->rank->slug, $rankHierarchy);
            $previousRankLevel = $this->getRankLevel($previousPeriodRank->rank->slug, $rankHierarchy);
            if ($currentRankLevel === null || $previousRankLevel === null) { Log::info("[Bono Fidelización] No se pudo determinar nivel de rangos."); return false; }
            return $currentRankLevel >= $minQualifyingLevel && $previousRankLevel >= $minQualifyingLevel;
        } elseif ($bonusType->slug === 'bono-libertad-financiera') {
            if (!$eventPayload instanceof Period) { Log::warning("[Bono Libertad Financiera] Payload no es Period."); return false; }
            if (!$this->userHasActiveSubscription($beneficiary, $eventPayload)) { Log::info("[Bono Libertad Financiera] Usuario sin suscripción activa."); return false; }
            $userRankEntry = UserPeriodRank::where('user_id', $beneficiary->id)->where('period_id', $eventPayload->id)->with('rank')->first();
            if (!$userRankEntry || !$userRankEntry->rank) { Log::info("[Bono Libertad Financiera] Usuario sin rango para el periodo."); return false; }
            $commissionTier = FinancialFreedomCommissionTier::where('rank_id', $userRankEntry->rank_id)->where('is_active', true)->first();
            if (!$commissionTier) { Log::info("[Bono Libertad Financiera] Rango ID {$userRankEntry->rank_id} sin nivel de comisión activo."); return false; }
            return true;
        } elseif ($bonusType->slug === 'bono-liderazgo') {
            if (!$eventPayload instanceof Period) { Log::warning("[Bono Liderazgo] Payload no es Period."); return false; }
            if (!$this->userHasActiveSubscription($beneficiary, $eventPayload)) { Log::info("[Bono Liderazgo] Patrocinador ID {$beneficiary->id} no tiene suscripción activa."); return false; }
            $config = is_string($bonusType->configuration_details) ? json_decode($bonusType->configuration_details, true) : ($bonusType->configuration_details ?? []);
            $minSponsorRankSlug = $config['min_sponsor_rank_slug'] ?? null;
            if ($minSponsorRankSlug) {
                $sponsorRankEntry = UserPeriodRank::where('user_id', $beneficiary->id)->where('period_id', $eventPayload->id)->with('rank')->first();
                if (!$sponsorRankEntry || !$sponsorRankEntry->rank) { Log::info("[Bono Liderazgo] Patrocinador ID {$beneficiary->id} sin rango para el periodo."); return false; }
                $rankHierarchy = $config['rank_hierarchy_for_comparison'] ?? ($this->getBonusTypeConfig($bonusType, 'bono-fidelizacion-rango')['rank_hierarchy_for_comparison'] ?? []);
                if (empty($rankHierarchy)) {
                    Log::warning("[Bono Liderazgo] No se encontró jerarquía de rangos para comparar el rango del patrocinador.");
                    if ($sponsorRankEntry->rank->slug !== $minSponsorRankSlug) {
                        Log::info("[Bono Liderazgo] Patrocinador ID {$beneficiary->id} no cumple rango mínimo exacto '{$minSponsorRankSlug}' (sin jerarquía).");
                        return false;
                    }
                } else {
                    $sponsorRankLevel = $this->getRankLevel($sponsorRankEntry->rank->slug, $rankHierarchy);
                    $minRequiredLevel = $this->getRankLevel($minSponsorRankSlug, $rankHierarchy);
                    if ($sponsorRankLevel === null || $minRequiredLevel === null || $sponsorRankLevel < $minRequiredLevel) {
                        Log::info("[Bono Liderazgo] Patrocinador ID {$beneficiary->id} no cumple rango mínimo '{$minSponsorRankSlug}'. Nivel: {$sponsorRankLevel}, Requerido: {$minRequiredLevel}");
                        return false;
                    }
                }
            }
            return true;
        } elseif ($bonusType->slug === 'bono-reconocimiento-anual' || $bonusType->slug === 'bono-viaje-anual') {
            if (!$eventPayload instanceof User) { Log::warning("[Bono Anual] Payload no es User para bono {$bonusType->slug}."); return false; }
            if ($beneficiary->id !== $eventPayload->id) { Log::warning("[Bono Anual] Beneficiario no es el usuario del payload para bono {$bonusType->slug}."); return false; }
            if (is_null($beneficiary->first_activation_date)) { Log::info("[Bono Anual] Usuario ID {$beneficiary->id} no tiene fecha de primera activación para bono {$bonusType->slug}."); return false; }
            
            $firstActivation = Carbon::parse($beneficiary->first_activation_date);
            $evaluationDate = ($eventPayload instanceof User && isset($eventPayload->anniversary_end_date)) 
                                ? Carbon::parse($eventPayload->anniversary_end_date) 
                                : Carbon::now(); 

            if ($firstActivation->isFuture() || $firstActivation->diffInYears($evaluationDate) < 1) {
                 Log::info("[Bono Anual] Usuario ID {$beneficiary->id} aún no cumple un año desde la activación ({$firstActivation->toDateString()}) o fecha de evaluación ({$evaluationDate->toDateString()}) es futura para bono {$bonusType->slug}.");
                 return false; 
            }
            if (!$this->userHasActiveSubscription($beneficiary)) { Log::info("[Bono Anual] Usuario ID {$beneficiary->id} sin suscripción activa para bono {$bonusType->slug}."); return false; }
            return true;
        } elseif ($bonusType->slug === 'bono-auto') {
            if (!$eventPayload instanceof Carbon) { Log::warning("[Bono Auto] Payload no es Carbon (endDateOfMonth)."); return false; }
            if (!$this->userHasActiveSubscription($beneficiary)) { Log::info("[Bono Auto] Usuario ID {$beneficiary->id} sin suscripción activa."); return false; }

            $config = is_string($bonusType->configuration_details) ? json_decode($bonusType->configuration_details, true) : ($bonusType->configuration_details ?? []);
            $qualifyingRankSlug = $config['qualifying_rank_slug'] ?? 'diamante';
            $totalPaymentsPerCycle = (int)($config['total_payments_per_cycle'] ?? 48);
            $rankHierarchy = $config['rank_hierarchy_for_comparison'] ?? ($this->getBonusTypeConfig($bonusType, 'bono-fidelizacion-rango')['rank_hierarchy_for_comparison'] ?? []);

            $progress = UserCarBonusProgress::firstOrCreate(
                ['user_id' => $beneficiary->id, 'bonus_type_id' => $bonusType->id],
                ['current_cycle_number' => 1, 'payments_made_this_cycle' => 0, 'is_eligible_for_cycle' => false, 'cycle_config_snapshot' => json_encode($config)]
            );
            
            if ($progress->payments_made_this_cycle >= $totalPaymentsPerCycle) {
                $progress->current_cycle_number += 1;
                $progress->payments_made_this_cycle = 0;
                $progress->is_eligible_for_cycle = false; 
                $progress->cycle_config_snapshot = json_encode($config);
                $progress->save();
                Log::info("[Bono Auto] Ciclo completado para Usuario ID {$beneficiary->id}. Nuevo ciclo #{$progress->current_cycle_number} iniciado. Requiere recalificación de rango.");
            }
            
            $monthEndDate = $eventPayload; 
            $monthStartDate = $monthEndDate->copy()->startOfMonth();

            if (!$progress->is_eligible_for_cycle) {
                if ($this->checkRankInMonth($beneficiary, $qualifyingRankSlug, $monthStartDate, $monthEndDate, $rankHierarchy)) {
                    $progress->is_eligible_for_cycle = true;
                    Log::info("[Bono Auto] Usuario ID {$beneficiary->id} ahora es elegible para el ciclo #{$progress->current_cycle_number} del Bono Auto.");
                } else {
                    Log::info("[Bono Auto] Usuario ID {$beneficiary->id} no es elegible (aún) para el ciclo #{$progress->current_cycle_number} del Bono Auto este mes ({$monthStartDate->format('Y-m')}).");
                    return false;
                }
            }
            
            if (!$this->checkRankInMonth($beneficiary, $qualifyingRankSlug, $monthStartDate, $monthEndDate, $rankHierarchy)) {
                Log::info("[Bono Auto] Usuario ID {$beneficiary->id} es elegible para el ciclo pero NO cumplió rango este mes ({$monthStartDate->format('Y-m')}). No hay pago.");
                return false; 
            }
            
            if ($progress->payments_made_this_cycle < $totalPaymentsPerCycle) {
                 if ($progress->isDirty('is_eligible_for_cycle')) { 
                    $progress->save();
                 }
                return true;
            } else {
                Log::info("[Bono Auto] Usuario ID {$beneficiary->id} ya completó las {$totalPaymentsPerCycle} cuotas del ciclo #{$progress->current_cycle_number}. Esperando renovación en próxima evaluación.");
                return false; 
            }
        }
        return true;
    }

    protected function checkRankInMonth(User $user, string $requiredRankSlug, Carbon $monthStartDate, Carbon $monthEndDate, array $rankHierarchy): bool
    {
        $qualifyingRank = Rank::where('slug', $requiredRankSlug)->first();
        if (!$qualifyingRank) { Log::error("[checkRankInMonth] Rango calificable '{$requiredRankSlug}' no encontrado."); return false; }
        
        $requiredRankLevel = null;
        if (!empty($rankHierarchy)) {
            $requiredRankLevel = $this->getRankLevel($requiredRankSlug, $rankHierarchy);
            if ($requiredRankLevel === null) { Log::error("[checkRankInMonth] Rango '{$requiredRankSlug}' no encontrado en jerarquía."); return false; }
        } else { $requiredRankLevel = $qualifyingRank->rank_order; }

        $periodsInMonth = Period::where('start_date', '<=', $monthEndDate)
                                ->where('end_date', '>=', $monthStartDate)
                                ->get();
        
        if ($periodsInMonth->isEmpty()) { Log::warning("[checkRankInMonth] No se encontraron periodos para el mes {$monthStartDate->format('Y-m')}."); return false; }

        foreach ($periodsInMonth as $period) {
            $userRankEntry = UserPeriodRank::where('user_id', $user->id)->where('period_id', $period->id)->with('rank')->first();
            if ($userRankEntry && $userRankEntry->rank) {
                $userRankLevel = !empty($rankHierarchy) ? $this->getRankLevel($userRankEntry->rank->slug, $rankHierarchy) : $userRankEntry->rank->rank_order;
                if ($userRankLevel !== null && $userRankLevel >= $requiredRankLevel) { return true; }
            }
        }
        return false;
    }

    protected function determineNonMonetaryAwardDetails(BonusType $bonusType, User $beneficiary, Carbon $evaluationDate): ?array
    {
        if ($bonusType->slug !== 'bono-viaje-anual') return null;

        $config = is_string($bonusType->configuration_details) ? json_decode($bonusType->configuration_details, true) : ($bonusType->configuration_details ?? []);
        $qualifyingRankSlug = $config['qualifying_rank_slug'] ?? null;
        $requiredConsecutivePeriods = (int)($config['required_consecutive_periods'] ?? 0);
        $awardDescription = $config['award_description'] ?? "Premio Viaje Anual";
        $rankHierarchy = $config['rank_hierarchy_for_comparison'] ?? [];


        if (!$qualifyingRankSlug || $requiredConsecutivePeriods <= 0) {
            Log::error("[Bono Viaje Det] Configuración inválida para Bono Viaje ID {$bonusType->id}.");
            return null;
        }

        $qualifyingRank = Rank::where('slug', $qualifyingRankSlug)->first();
        if (!$qualifyingRank) { Log::error("[Bono Viaje Det] Rango calificable '{$qualifyingRankSlug}' no encontrado."); return null; }
        
        $tierRankLevelForComparison = null;
        if(!empty($rankHierarchy)) {
            $tierRankLevelForComparison = $this->getRankLevel($qualifyingRankSlug, $rankHierarchy);
            if($tierRankLevelForComparison === null) { Log::error("[Bono Viaje Det] Rango '{$qualifyingRankSlug}' no encontrado en jerarquía para Bono Viaje."); return null;}
        } else {
            $tierRankLevelForComparison = $qualifyingRank->rank_order; // Usar rank_order si no hay jerarquía específica
        }


        $firstActivationDate = Carbon::parse($beneficiary->first_activation_date);
        $evaluationStartDate = $evaluationDate->copy()->subYear()->startOfDay();
        $evaluationEndDate = $evaluationDate->endOfDay();

        Log::info("[Bono Viaje Det] Evaluando para Usuario ID {$beneficiary->id}. Periodo: {$evaluationStartDate->toDateTimeString()} a {$evaluationEndDate->toDateTimeString()} para rango {$qualifyingRankSlug} (Nivel: {$tierRankLevelForComparison})");

        $userRanksInEvaluationYear = UserPeriodRank::where('user_id', $beneficiary->id)
            ->join('periods', 'user_period_ranks.period_id', '=', 'periods.id')
            ->where('periods.start_date', '>=', $evaluationStartDate)
            ->where('periods.end_date', '<=', $evaluationEndDate)
            ->orderBy('periods.start_date', 'asc')
            ->with('rank')->get();

        if ($userRanksInEvaluationYear->isEmpty()) {
            Log::info("[Bono Viaje Det] Usuario ID {$beneficiary->id} sin historial de rangos en el periodo.");
            return null;
        }

        $maxConsecutiveStreak = 0; $currentConsecutiveStreak = 0;
        foreach ($userRanksInEvaluationYear as $userPeriodRank) {
            $currentUserRankLevel = null;
            if ($userPeriodRank->rank) {
                $currentUserRankLevel = !empty($rankHierarchy) ? $this->getRankLevel($userPeriodRank->rank->slug, $rankHierarchy) : $userPeriodRank->rank->rank_order;
            }

            if ($currentUserRankLevel !== null && $currentUserRankLevel >= $tierRankLevelForComparison) {
                $currentConsecutiveStreak++;
            } else {
                $maxConsecutiveStreak = max($maxConsecutiveStreak, $currentConsecutiveStreak);
                $currentConsecutiveStreak = 0;
            }
        }
        $maxConsecutiveStreak = max($maxConsecutiveStreak, $currentConsecutiveStreak);

        if ($maxConsecutiveStreak >= $requiredConsecutivePeriods) {
            Log::info("[Bono Viaje Det] Usuario ID {$beneficiary->id} CALIFICA para '{$awardDescription}'. Racha: {$maxConsecutiveStreak} (Requerida: {$requiredConsecutivePeriods}).");
            $this->lastTripAwardDescriptionDetails = $awardDescription; 
            return ['award_description' => $awardDescription];
        } else {
            Log::info("[Bono Viaje Det] Usuario ID {$beneficiary->id} NO califica para '{$awardDescription}'. Racha: {$maxConsecutiveStreak} (Requerida: {$requiredConsecutivePeriods}).");
        }
        return null;
    }


    protected function calculateBonusAmount(BonusType $bonusType, $eventPayload, User $beneficiary): float
    {
        $amount = 0.0;
        if ($bonusType->slug === 'bono-fidelizacion-rango' || $bonusType->slug === 'bono-viaje-anual') { 
            // Bono Viaje no es monetario, su lógica está en determineNonMonetaryAwardDetails y processEvent
            // Bono Fidelización devuelve productos, no monto.
            return 0.0; // No debería llegar aquí si processEvent lo maneja antes.
        }
        elseif ($bonusType->slug === 'bono-libertad-financiera') {
            if (!$eventPayload instanceof Period) { Log::warning("[BLF Calculate] Payload no es Period."); return 0.0; }
            $userRankEntry = UserPeriodRank::where('user_id', $beneficiary->id)->where('period_id', $eventPayload->id)->with('rank')->first();
            if (!$userRankEntry || !$userRankEntry->rank) { Log::warning("[BLF Calculate] No UserPeriodRank para User ID {$beneficiary->id}."); return 0.0; }
            $commissionTier = FinancialFreedomCommissionTier::where('rank_id', $userRankEntry->rank_id)->where('is_active', true)->first();
            if (!$commissionTier) { Log::warning("[BLF Calculate] No commissionTier para Rank ID {$userRankEntry->rank_id}."); return 0.0; }
            $userCommissionableVolume = $this->getUserPeriodCommissionableVolume($beneficiary, $eventPayload);
            $pointsBase = $userCommissionableVolume; 
            if ($userCommissionableVolume > $commissionTier->max_points_for_rank) {
                 $pointsBase = $commissionTier->max_points_for_rank;
            }
            $amount = $pointsBase * $commissionTier->percentage;
            return round($amount, 2);
        } 
        elseif ($bonusType->slug === 'bono-liderazgo') {
            if (!$eventPayload instanceof Period) { Log::warning("[Bono Liderazgo Calculate] Payload no es Period."); return 0.0; }
            $closedPeriod = $eventPayload;
            $config = is_string($bonusType->configuration_details) ? json_decode($bonusType->configuration_details, true) : ($bonusType->configuration_details ?? []);
            $percentageOfEarnings = (float)($config['percentage_of_earnings'] ?? 0.10);
            $totalDirectDownlineEarnings = 0;
            $directDownlines = User::where('sponsor_id', $beneficiary->id)->get();
            foreach ($directDownlines as $direct) {
                $earnings = WalletTransaction::where('user_id', $direct->id)
                                            ->where('period_id', $closedPeriod->id)
                                            ->where('type', 'bonus_payout') 
                                            ->where('status', 'completed')
                                            ->sum('amount');
                $totalDirectDownlineEarnings += $earnings;
            }
            $amount = $totalDirectDownlineEarnings * $percentageOfEarnings;
            return round($amount, 2);
        } 
        elseif ($bonusType->slug === 'bono-reconocimiento-anual') { 
            if (!$eventPayload instanceof User || $beneficiary->id !== $eventPayload->id) { Log::warning("[Bono Reconocimiento Calc] Payload no es el User beneficiario."); return 0.0;}
            if (is_null($beneficiary->first_activation_date)) { Log::info("[Bono Reconocimiento Calc] Usuario ID {$beneficiary->id} no tiene fecha de primera activación."); return 0.0; }
            
            $evaluationDate = ($eventPayload instanceof User && isset($eventPayload->anniversary_end_date)) 
                                ? Carbon::parse($eventPayload->anniversary_end_date) 
                                : Carbon::now();
            
            $firstActivationDate = Carbon::parse($beneficiary->first_activation_date);
            $evaluationStartDate = $evaluationDate->copy()->subYear()->startOfDay();
            $evaluationEndDate = $evaluationDate->endOfDay();
            Log::info("[Bono Reconocimiento Calc] Evaluando para Usuario ID {$beneficiary->id}. Año de activación: {$firstActivationDate->toDateString()}. Periodo de evaluación: {$evaluationStartDate->toDateTimeString()} a {$evaluationEndDate->toDateTimeString()}");
            $userRanksInEvaluationYear = UserPeriodRank::where('user_id', $beneficiary->id)
                ->join('periods', 'user_period_ranks.period_id', '=', 'periods.id')
                ->where('periods.start_date', '>=', $evaluationStartDate)
                ->where('periods.end_date', '<=', $evaluationEndDate)
                ->orderBy('periods.start_date', 'asc')
                ->with('rank')->get();
            if ($userRanksInEvaluationYear->isEmpty()) { Log::info("[Bono Reconocimiento Calc] Usuario ID {$beneficiary->id} sin historial de rangos en el periodo de evaluación."); return 0.0; }
            $recognitionTiers = RecognitionBonusTier::where('is_active', true)
                                    ->join('ranks', 'recognition_bonus_tiers.rank_id', '=', 'ranks.id')
                                    ->orderBy('ranks.rank_order', 'desc')
                                    ->select('recognition_bonus_tiers.*', 'ranks.name as rank_name_for_tier', 'ranks.rank_order as tier_rank_level')->get();
            foreach ($recognitionTiers as $tier) {
                $requiredConsecutivePeriods = $tier->annual_periods_required;
                $tierRankLevelForComparison = $tier->tier_rank_level;
                $maxConsecutiveStreak = 0; $currentConsecutiveStreak = 0;
                foreach ($userRanksInEvaluationYear as $userPeriodRank) {
                    if ($userPeriodRank->rank && $userPeriodRank->rank->rank_order >= $tierRankLevelForComparison) { $currentConsecutiveStreak++; } 
                    else { $maxConsecutiveStreak = max($maxConsecutiveStreak, $currentConsecutiveStreak); $currentConsecutiveStreak = 0; }
                }
                $maxConsecutiveStreak = max($maxConsecutiveStreak, $currentConsecutiveStreak);
                if ($maxConsecutiveStreak >= $requiredConsecutivePeriods) {
                    Log::info("[Bono Reconocimiento Calc] Usuario ID {$beneficiary->id} CALIFICA para tier '{$tier->rank_name_for_tier}' (Nivel {$tierRankLevelForComparison}). Racha: {$maxConsecutiveStreak} (Requerida: {$requiredConsecutivePeriods}). Monto: {$tier->bonus_amount}");
                    $this->lastQualifiedRecognitionRankName = $tier->rank_name_for_tier;
                    return (float) $tier->bonus_amount;
                } else {
                    Log::info("[Bono Reconocimiento Calc] Usuario ID {$beneficiary->id} NO califica para tier '{$tier->rank_name_for_tier}'. Racha: {$maxConsecutiveStreak} (Requerida: {$requiredConsecutivePeriods}).");
                }
            }
            Log::info("[Bono Reconocimiento Calc] Usuario ID {$beneficiary->id} no calificó para ningún tier del bono de reconocimiento este año.");
            return 0.0;
        } 
        elseif ($bonusType->slug === 'bono-auto') {
            $config = is_string($bonusType->configuration_details) ? json_decode($bonusType->configuration_details, true) : ($bonusType->configuration_details ?? []);
            $bonusAmountPerMonth = (float)($config['bonus_amount_per_month'] ?? 0);
            $progress = UserCarBonusProgress::where('user_id', $beneficiary->id)
                                          ->where('bonus_type_id', $bonusType->id)
                                          ->first(); 
            if ($progress) {
                 $this->lastCarBonusPaymentDetails = [
                    'payment_number' => $progress->payments_made_this_cycle + 1, 
                    'total_payments' => (int)($config['total_payments_per_cycle'] ?? 48),
                    'cycle_number' => $progress->current_cycle_number
                ];
            }
            return $bonusAmountPerMonth;
        }

        if ($eventPayload instanceof Order) {
             switch ($bonusType->calculation_type) {
                case 'fixed_amount':
                    $amount = (float) $bonusType->amount_fixed;
                    break;
                case 'percentage_of_purchase': break; 
                case 'points_to_currency':
                    $points = $eventPayload->total_points_generated ?? 0;
                    $amount = $points * (float) $bonusType->points_to_currency_conversion_factor;
                    break;
                case 'product_bonus_from_order_items':
                    $config = is_string($bonusType->configuration_details) ? json_decode($bonusType->configuration_details, true) : ($bonusType->configuration_details ?? []);
                    $allowMultiple = $config['allow_multiple_product_bonuses_per_order'] ?? false;
                    $totalBonusAmount = 0;
                    foreach ($eventPayload->items as $item) {
                        if (isset($item->product_pays_bonus_at_purchase) && $item->product_pays_bonus_at_purchase && isset($item->product_bonus_amount_at_purchase) && $item->product_bonus_amount_at_purchase > 0) {
                            if ($allowMultiple) {
                                $totalBonusAmount += (float) $item->product_bonus_amount_at_purchase;
                            } else {
                                $totalBonusAmount = (float) $item->product_bonus_amount_at_purchase;
                                break; 
                            }
                        }
                    }
                    $amount = $totalBonusAmount;
                    break;
                default:
                    Log::warning("[BonusService] Tipo de cálculo desconocido '{$bonusType->calculation_type}' para bono {$bonusType->id} en orden.");
            }
        }
        return round($amount, 2);
    }

    protected function updateCarBonusProgress(User $user, BonusType $bonusType, array $config): void
    {
        $progress = UserCarBonusProgress::where('user_id', $user->id)
                                      ->where('bonus_type_id', $bonusType->id)
                                      ->first();
        if ($progress) {
            $progress->payments_made_this_cycle += 1;
            $progress->save();
            Log::info("[Bono Auto Update] Progreso actualizado para Usuario ID {$user->id}. Ciclo: {$progress->current_cycle_number}, Pagos hechos este ciclo: {$progress->payments_made_this_cycle}.");
        } else {
            Log::error("[Bono Auto Update] No se pudo encontrar el progreso del Bono Auto para Usuario ID {$user->id} para actualizar.");
        }
    }

    protected function calculateProductsToAward(BonusType $bonusType, Period $closedPeriod, User $beneficiary): int
    {
        $productsToAward = 0;
        $config = is_string($bonusType->configuration_details) ? json_decode($bonusType->configuration_details, true) : ($bonusType->configuration_details ?? []);
        $rankHierarchy = $config['rank_hierarchy_for_comparison'] ?? [];
        $loyaltyTiers = $config['loyalty_award_tiers'] ?? []; 
        if (empty($rankHierarchy) || empty($loyaltyTiers)) { Log::error("[Bono Fidelización] Falta config de jerarquía o tiers para bono {$bonusType->id}."); return 0; }
        $previousPeriod = Period::where('end_date', '<', $closedPeriod->start_date)->orderBy('end_date', 'desc')->first();
        if (!$previousPeriod) { Log::info("[Bono Fidelización] No hay periodo anterior."); return 0; }
        $currentPeriodRankEntry = UserPeriodRank::where('user_id', $beneficiary->id)->where('period_id', $closedPeriod->id)->with('rank')->first();
        $previousPeriodRankEntry = UserPeriodRank::where('user_id', $beneficiary->id)->where('period_id', $previousPeriod->id)->with('rank')->first();
        if (!$currentPeriodRankEntry || !$previousPeriodRankEntry || !$currentPeriodRankEntry->rank || !$previousPeriodRankEntry->rank) { Log::info("[Bono Fidelización] Sin rangos para ambos periodos."); return 0; }
        $currentRankLevel = $this->getRankLevel($currentPeriodRankEntry->rank->slug, $rankHierarchy);
        $previousRankLevel = $this->getRankLevel($previousPeriodRankEntry->rank->slug, $rankHierarchy);
        if ($currentRankLevel === null || $previousRankLevel === null) { Log::info("[Bono Fidelización] No se pudo determinar nivel de rangos."); return 0; }
        $paymentRankLevel = min($currentRankLevel, $previousRankLevel);
        foreach ($loyaltyTiers as $tier) {
            $tierMinRankSlug = $tier['min_rank_slug_for_tier'] ?? null;
            if (!$tierMinRankSlug) continue;
            $tierMinLevel = $this->getRankLevel($tierMinRankSlug, $rankHierarchy);
            if ($tierMinLevel === null) continue;
            if ($paymentRankLevel >= $tierMinLevel) {
                $productsToAward = $tier['products_to_award'] ?? 0;
                break; 
            }
        }
        return $productsToAward;
    }
    
    protected function awardLoyaltyProducts(User $beneficiary, int $productsToAward, BonusType $bonusType, Period $period): void
    {
        if ($productsToAward <= 0) return;
        DB::transaction(function () use ($beneficiary, $productsToAward, $bonusType, $period) {
            $currentBalance = UserLoyaltyProductLedger::getCurrentBalance($beneficiary->id);
            $newBalance = $currentBalance + $productsToAward;
            UserLoyaltyProductLedger::create([
                'user_id' => $beneficiary->id,
                'transaction_type' => 'earned',
                'products_quantity' => $productsToAward,
                'balance_after_transaction' => $newBalance,
                'notes' => "Bono Fidelización: {$productsToAward} producto(s) por rango en periodo " . ($period->name ?? $period->id),
                'source_bonus_type_id' => $bonusType->id,
                'source_period_id' => $period->id,
                'processed_at' => now(),
            ]);
            Log::info("Bono Fidelización: {$productsToAward} productos acreditados al usuario ID {$beneficiary->id} por periodo ID {$period->id}. Nuevo saldo: {$newBalance}.");
        });
    }

    protected function generateTransactionDescription(BonusType $bonusType, $eventPayload, User $beneficiary): string
    {
        if ($bonusType->slug === 'bono-fidelizacion-rango') { /* ... */ }
        if ($bonusType->slug === 'bono-libertad-financiera') { /* ... */ }
        if ($bonusType->slug === 'bono-liderazgo') { /* ... */ }
        if ($bonusType->slug === 'bono-reconocimiento-anual') { /* ... */ }
        if ($bonusType->slug === 'bono-auto') { /* ... */ }
        // Para bono-viaje-anual, no se genera descripción de transacción de billetera aquí.

        $template = $bonusType->wallet_transaction_description_template ?? "Bono: {BONUS_NAME}";
        $description = str_replace('{BONUS_NAME}', $bonusType->name, $template);
        if ($eventPayload instanceof Order) {
            $description = str_replace('{ORDER_ID}', $eventPayload->id, $description);
            $orderUser = $eventPayload->user; 
            if ($orderUser) {
                $buyerName = $orderUser->first_name . ' ' . $orderUser->last_name;
                $description = str_replace('{NEW_USER_NAME}', $buyerName, $description);
                $description = str_replace('{BUYER_NAME}', $buyerName, $description);
            }
            if ($bonusType->slug === 'bono-reconsumo-puntos' || $bonusType->slug === 'bono-inicio-rapido') {
                $points = $eventPayload->total_points_generated ?? 0; 
                $description = str_replace('{ORDER_POINTS}', $points, $description);
            }
            if ($bonusType->slug === 'bono-referido') {
                $qualifyingItems = $eventPayload->items()->where('product_pays_bonus_at_purchase', true)->get();
                if ($qualifyingItems->isNotEmpty()) {
                    $productNames = $qualifyingItems->pluck('product_name')->filter()->implode(', ');
                    if (!empty($productNames)) { $description = str_replace('{PRODUCT_NAMES_CONCATENATED}', $productNames, $description); }
                    else { $description = str_replace('{PRODUCT_NAMES_CONCATENATED}', "producto(s) calificado(s)", $description); }
                } else { $description = str_replace('{PRODUCT_NAMES_CONCATENATED}', "producto(s) calificado(s)", $description); }
            }
        } elseif ($eventPayload instanceof Period) {
             $periodIdentifier = $eventPayload->name ?? $eventPayload->id;
             $description = str_replace('{PERIOD_ID_OR_NAME}', $periodIdentifier, $description);
        } elseif ($eventPayload instanceof Carbon && ($bonusType->trigger_event === 'monthly_bonus_review' || $bonusType->trigger_event === 'user_annual_review')) {
            $description = str_replace('{MONTH_YEAR}', $eventPayload->format('m/Y'), $description);
        }
        return $description;
    }

    protected function hasBonusBeenPaid(User $beneficiary, User $newSocio, BonusType $bonusType): bool
    {
        if ($bonusType->slug === 'bono-inicio-rapido') {
            return DB::table('paid_fast_start_bonuses')
                     ->where('beneficiary_id', $beneficiary->id)
                     ->where('new_socio_id', $newSocio->id)
                     ->where('bonus_type_id', $bonusType->id)
                     ->exists();
        }
        return false; 
    }

    protected function markBonusAsPaid(User $beneficiary, User $newSocio, BonusType $bonusType, Order $order): void
    {
        if ($bonusType->slug !== 'bono-inicio-rapido') { return; }
        try {
            DB::table('paid_fast_start_bonuses')->insert([
                'beneficiary_id' => $beneficiary->id,
                'new_socio_id' => $newSocio->id,
                'bonus_type_id' => $bonusType->id,
                'order_id' => $order->id,
                'paid_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            Log::info("Bono '{$bonusType->name}' (ID: {$bonusType->id}) marcado como pagado para beneficiario ID {$beneficiary->id} por nuevo socio ID {$newSocio->id} y orden ID {$order->id}.");
        } catch (\Exception $e) {
            Log::error("Error al marcar bono '{$bonusType->name}' como pagado: " . $e->getMessage());
        }
    }

    private function getBonusTypeConfig(BonusType $bonusTypeContext, string $targetBonusSlug): array
    {
        if ($bonusTypeContext->slug === $targetBonusSlug) {
            return is_string($bonusTypeContext->configuration_details) ? 
                   json_decode($bonusTypeContext->configuration_details, true) : 
                   ($bonusTypeContext->configuration_details ?? []);
        }
        
        $targetBonusType = BonusType::where('slug', $targetBonusSlug)->first();
        if ($targetBonusType) {
            return is_string($targetBonusType->configuration_details) ? 
                   json_decode($targetBonusType->configuration_details, true) : 
                   ($targetBonusType->configuration_details ?? []);
        }
        return [];
    }
}