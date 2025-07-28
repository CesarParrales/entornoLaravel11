<?php

namespace App\Listeners;

use App\Events\OrderPaymentConfirmed;
use App\Models\User;
use App\Models\Rank;
use App\Models\UserPeriodRank;
use App\Services\WalletService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; // For sending activation email
use App\Mail\AccountActivatedMail; // Mailable to be created

class ActivateUserAndAssignInitialRankListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected WalletService $walletService;

    /**
     * Create the event listener.
     */
    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPaymentConfirmed $event): void
    {
        $order = $event->order;
        $user = $order->user;

        // Asegurarse de que el usuario y el pedido existan
        if (!$user || !$order) {
            Log::warning("ActivateUserAndAssignInitialRankListener: Usuario u Pedido no encontrado para Order ID: {$event->order->id}.");
            return;
        }

        Log::info("ActivateUserAndAssignInitialRankListener: Procesando Order ID: {$order->id} para User ID: {$user->id}, Status Usuario: {$user->status}");

        // Tarea 3.2.2: Verificar si es el primer pedido confirmado y el estado del usuario
        // Asumimos que un pedido solo puede tener un evento OrderPaymentConfirmed una vez.
        // La verificación principal es el estado del usuario.
        if ($user->status === 'pending_first_payment') {
            Log::info("Usuario {$user->id} está 'pending_first_payment'. Procediendo con la activación.");

            // Tarea 3.2.3: Cambiar estado y establecer fechas
            $user->status = 'active';
            $user->activated_at = now();
            if (!$user->first_activation_date) { // Solo establecer si no existe
                $user->first_activation_date = now();
            }

            // Tarea 3.2.4: Asignar Rango MLM Inicial
            $orderPoints = $order->total_points_generated; // Usar el accesor del modelo Order
            
            // Encontrar el rango más alto que el usuario califica instantáneamente
            // Asumimos que 'min_personal_points_for_activation' es el campo en 'ranks' para esta calificación.
            // O 'instant_qualification_personal_points' como se menciona en la guía. Usaremos este último.
            $rankToAssign = Rank::where('is_active', true)
                                ->where('instant_qualification_personal_points', '<=', $orderPoints)
                                ->orderBy('rank_order', 'desc') // O por 'instant_qualification_personal_points' desc si rank_order no es para esto
                                ->first();

            if ($rankToAssign) {
                $user->rank_id = $rankToAssign->id;
                Log::info("Usuario {$user->id} asignado al Rango ID: {$rankToAssign->id} ({$rankToAssign->name}) basado en {$orderPoints} puntos.");

                // Tarea 3.2.5: Crear registro en user_period_ranks
                // Asumimos que existe un modelo Period y una forma de obtener el periodo actual.
                // $currentPeriod = Period::current(); // Esto es un placeholder
                // if ($currentPeriod) {
                //     UserPeriodRank::updateOrCreate(
                //         ['user_id' => $user->id, 'period_id' => $currentPeriod->id],
                //         ['rank_id' => $rankToAssign->id, 'achieved_at' => now()]
                //     );
                // } else {
                //     Log::warning("No se pudo encontrar el periodo actual para registrar el rango del usuario {$user->id}.");
                // }
                // Por simplicidad, si no hay un sistema de Periodos complejo, se podría omitir o simplificar.
                // La guía menciona "Crear un registro en user_period_ranks para el periodo actual".
                // Si UserPeriodRank solo necesita user_id y rank_id y timestamps, se puede hacer más simple.
                // Asumiendo que UserPeriodRank tiene user_id, rank_id, y timestamps.
                // Y que queremos un registro por cada vez que se alcanza un rango en un periodo.
                // Esta lógica puede necesitar refinamiento según el modelo Period.
                // Por ahora, crearemos un registro simple si UserPeriodRank lo permite.
                 UserPeriodRank::create([
                     'user_id' => $user->id,
                     'rank_id' => $rankToAssign->id,
                     // 'period_id' => $currentPeriod->id, // Si se usa
                     'achieved_at' => now(),
                     // 'paid_as_rank_id' => $rankToAssign->id, // Si se usa
                 ]);


            } else {
                Log::warning("No se encontró un rango calificable para el Usuario {$user->id} con {$orderPoints} puntos.");
                // Considerar asignar un rango base por defecto si no califica a ninguno específico.
            }

            $user->save();

            // Tarea 3.2.6: Asegurar creación de billetera
            $this->walletService->ensureWalletExistsForSocio($user);
            Log::info("Billetera asegurada/creada para Usuario {$user->id}.");

            // Tarea 3.4.3: Enviar email de activación
            Mail::to($user->email)->send(new AccountActivatedMail($user, $rankToAssign));
            Log::info("Email de activación encolado para {$user->email} para Rango: " . ($rankToAssign ? $rankToAssign->name : 'N/A'));

            Log::info("Usuario {$user->id} activado exitosamente.");

        } else {
            Log::info("Usuario {$user->id} no está 'pending_first_payment' (estado actual: {$user->status}). No se requiere activación por este listener.");
        }
    }
}
