<?php

namespace App\Listeners;

use App\Events\UserAnnualReviewEvent;
use App\Services\BonusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessUserAnnualReviewBonuses implements ShouldQueue
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
    public function handle(UserAnnualReviewEvent $event): void
    {
        $user = $event->user;
        // $anniversaryEndDate = $event->anniversaryEndDate; // Disponible si BonusService se adapta para usarla

        Log::info("ProcessUserAnnualReviewBonuses: Procesando bonos de revisión anual para Usuario ID: {$user->id} en su aniversario (fecha de revisión: {$event->anniversaryEndDate->toDateString()}).");

        try {
            // El payload del evento para 'user_annual_review' es el propio usuario.
            // BonusService usará $user->first_activation_date y la fecha actual (o $anniversaryEndDate si se pasa)
            // para determinar el rango de un año a evaluar.
            $this->bonusService->processEvent(
                'user_annual_review',
                $user, // El payload es el usuario mismo
                $user  // El beneficiario es el usuario mismo
            );
        } catch (\Exception $e) {
            Log::error("Error procesando bonos de revisión anual para Usuario ID {$user->id}: " . $e->getMessage(), [
                'exception' => $e
            ]);
            // Considerar reintentar el job si es apropiado
            // $this->release(60); // Reintentar después de 60 segundos
        }
    }

    /**
     * Handle a job failure.
    */
    public function failed(UserAnnualReviewEvent $event, $exception): void
    {
        Log::critical("Fallo CRÍTICO al procesar bonos de revisión anual encolados para Usuario ID {$event->user->id}: " . $exception->getMessage());
    }
}
