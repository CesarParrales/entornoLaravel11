<?php

namespace App\Listeners;

use App\Events\OrderPaymentConfirmed;
use App\Services\BonusService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessBonusesOnOrderPaymentListener implements ShouldQueue
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
    public function handle(OrderPaymentConfirmed $event): void
    {
        $order = $event->order;
        $orderOwner = $order->user; // El usuario que realizó la orden

        if (!$orderOwner) {
            Log::warning("ProcessBonusesOnOrderPaymentListener: No se pudo obtener el propietario de la orden ID {$order->id}. No se procesarán bonos.");
            return;
        }

        Log::info("ProcessBonusesOnOrderPaymentListener: Procesando bonos para la orden ID {$order->id} del usuario ID {$orderOwner->id}.");

        // Intentar establecer la fecha de primera activación
        if (is_null($orderOwner->first_activation_date)) {
            // Asumimos que $order tiene una propiedad/método para los puntos comisionables.
            // Por ejemplo: $order->total_commissionable_points
            // Y una constante para el umbral de activación.
            // TODO: Definir cómo obtener 'total_commissionable_points' de la orden y el umbral.
            // Por ahora, usaremos un placeholder.
            $commissionablePoints = $order->total_points_generated ?? 0; // Usando total_points_generated como placeholder
            $activationThreshold = 40; // Puntos requeridos para activación

            if ($commissionablePoints >= $activationThreshold) {
                // Usar la fecha de pago de la orden si está disponible, sino la fecha actual.
                // TODO: Asegurar que $order tenga una fecha de pago (ej. paid_at o payment_confirmed_at)
                $activationDate = $order->paid_at ?? $order->payment_date ?? now();
                $orderOwner->first_activation_date = $activationDate;
                $orderOwner->save();
                Log::info("ProcessBonusesOnOrderPaymentListener: Fecha de primera activación establecida para usuario ID {$orderOwner->id} a {$activationDate->toDateString()} por orden ID {$order->id}.");
            }
        }

        // 1. Procesar Bono Reconsumo (si aplica)
        // El beneficiario es el mismo dueño de la orden.
        try {
            Log::info("Procesando 'order_paid_by_self' para Orden ID: {$order->id}, Usuario ID: {$orderOwner->id}");
            $this->bonusService->processEvent(
                'order_paid_by_self',
                $order,
                $orderOwner
            );
        } catch (\Exception $e) {
            Log::error("Error procesando 'order_paid_by_self' para Orden ID {$order->id}: " . $e->getMessage());
        }


        // 2. Procesar Bono de Inicio Rápido (si aplica)
        // El beneficiario es el referrer del dueño de la orden.
        if ($orderOwner->referrer_id) {
            $referrer = User::find($orderOwner->referrer_id);
            if ($referrer) {
                try {
                    Log::info("Procesando 'first_order_paid_by_referred_user' para Orden ID: {$order->id}, Referido por ID: {$referrer->id}, Nuevo Socio ID: {$orderOwner->id}");
                    $this->bonusService->processEvent(
                        'first_order_paid_by_referred_user',
                        $order,
                        $referrer
                    );
                } catch (\Exception $e) {
                    Log::error("Error procesando 'first_order_paid_by_referred_user' para Orden ID {$order->id} (Referrer ID {$referrer->id}): " . $e->getMessage());
                }
            } else {
                Log::warning("ProcessBonusesOnOrderPaymentListener: No se encontró el referrer con ID {$orderOwner->referrer_id} para la orden ID {$order->id}.");
            }
        } else {
            Log::info("ProcessBonusesOnOrderPaymentListener: El usuario ID {$orderOwner->id} no tiene referrer. No se procesa Bono de Inicio Rápido para la orden ID {$order->id}.");
        }
        
        // Aquí se podrían añadir llamadas para otros tipos de bonos que se disparen con el mismo evento,
        // determinando el beneficiario correcto para cada uno.
    }

    /**
     * Handle a job failure.
    */
    public function failed(OrderPaymentConfirmed $event, $exception): void
    {
        Log::critical("Fallo al procesar bonos encolados para la orden ID {$event->order->id}: " . $exception->getMessage());
        // Aquí se podría añadir lógica para reintentar o notificar.
    }
}
