<?php

namespace App\Listeners;

use App\Events\MonthlyBonusReviewEvent;
use App\Services\BonusService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessMonthlyBonusReview implements ShouldQueue
{
    use InteractsWithQueue;

    protected BonusService $bonusService;

    /**
     * Create the event listener.
     */
    public function __construct(BonusService $bonusService)
    {
        $this->bonusService = $bonusService;
    }

    /**
     * Handle the event.
     */
    public function handle(MonthlyBonusReviewEvent $event): void
    {
        $monthToReview = $event->monthToReview;
        $endDateOfMonth = $monthToReview->copy()->endOfMonth(); // Usar como payload para BonusService

        Log::info("ProcessMonthlyBonusReview: Iniciando procesamiento de bonos para el mes de {$monthToReview->format('Y-F')}. Fecha de evaluación (payload): {$endDateOfMonth->toDateString()}");

        // Obtener usuarios activos o relevantes para bonos mensuales.
        // Podría filtrarse más si es necesario (ej. solo usuarios con ciertos roles o estados).
        $users = User::where('status', 'active') // Ejemplo de filtro
                     // ->whereNotNull('first_activation_date') // Podría ser útil si todos los bonos mensuales lo requieren
                     ->get();

        if ($users->isEmpty()) {
            Log::info("ProcessMonthlyBonusReview: No hay usuarios activos para procesar bonos mensuales para {$monthToReview->format('Y-F')}.");
            return;
        }
        
        Log::info("ProcessMonthlyBonusReview: {$users->count()} usuarios para procesar para el mes de {$monthToReview->format('Y-F')}.");

        foreach ($users as $user) {
            Log::info("ProcessMonthlyBonusReview: Procesando bonos mensuales para Usuario ID: {$user->id} para el mes de {$monthToReview->format('Y-F')}.");
            try {
                // El payload para 'monthly_bonus_review' es la fecha de fin de mes.
                // El beneficiario es el usuario actual en la iteración.
                $this->bonusService->processEvent(
                    'monthly_bonus_review',
                    $endDateOfMonth, 
                    $user  
                );
            } catch (\Exception $e) {
                Log::error("Error procesando bonos mensuales para Usuario ID {$user->id} para el mes de {$monthToReview->format('Y-F')}: " . $e->getMessage(), [
                    'exception' => $e
                ]);
            }
        }
        Log::info("ProcessMonthlyBonusReview: Procesamiento de bonos para el mes de {$monthToReview->format('Y-F')} completado.");
    }

    /**
     * Handle a job failure.
    */
    public function failed(MonthlyBonusReviewEvent $event, $exception): void
    {
        $monthToReview = $event->monthToReview;
        Log::critical("Fallo CRÍTICO al procesar bonos mensuales encolados para el mes de {$monthToReview->format('Y-F')}: " . $exception->getMessage());
    }
}
