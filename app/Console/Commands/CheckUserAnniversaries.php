<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Events\UserAnnualReviewEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckUserAnniversaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-user-anniversaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica los aniversarios de la primera activación de los usuarios y dispara eventos para bonos anuales.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Iniciando verificación de aniversarios de usuarios...');
        Log::info('Comando CheckUserAnniversaries: Iniciando verificación.');

        $today = Carbon::today();
        
        // Usuarios con fecha de primera activación no nula
        // y cuyo día y mes de activación coinciden con hoy
        // y el año de activación es anterior al año actual.
        $usersWithAnniversary = User::whereNotNull('first_activation_date')
            ->whereMonth('first_activation_date', '=', $today->month)
            ->whereDay('first_activation_date', '=', $today->day)
            ->whereYear('first_activation_date', '<', $today->year)
            ->get();

        if ($usersWithAnniversary->isEmpty()) {
            $this->info('No hay usuarios cumpliendo aniversario de activación hoy.');
            Log::info('Comando CheckUserAnniversaries: No hay usuarios cumpliendo aniversario hoy.');
            return;
        }

        $this->info("Se encontraron {$usersWithAnniversary->count()} usuarios cumpliendo aniversario hoy.");

        foreach ($usersWithAnniversary as $user) {
            $firstActivationDate = Carbon::parse($user->first_activation_date);
            $yearsSinceActivation = $firstActivationDate->diffInYears($today);

            // Asegurarse de que realmente ha pasado al menos un año completo.
            // Esto es una doble verificación, ya que whereYear lo cubre, pero es bueno ser explícito.
            if ($yearsSinceActivation >= 1) {
                $this->info("Procesando aniversario para Usuario ID: {$user->id}. Años desde activación: {$yearsSinceActivation}. Fecha de activación: {$firstActivationDate->toDateString()}");
                Log::info("Comando CheckUserAnniversaries: Disparando UserAnnualReviewEvent para Usuario ID: {$user->id}. Fecha de aniversario evaluada: {$today->toDateString()}");
                
                // Disparar el evento con el usuario y la fecha de fin del aniversario (hoy)
                UserAnnualReviewEvent::dispatch($user, $today);
            } else {
                 Log::info("Comando CheckUserAnniversaries: Usuario ID: {$user->id} tiene coincidencia de día/mes pero no ha completado un año completo. Años: {$yearsSinceActivation}. Se omite.");
            }
        }

        $this->info('Verificación de aniversarios completada.');
        Log::info('Comando CheckUserAnniversaries: Verificación completada.');
    }
}
