<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\MonthlyBonusReviewEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessMonthlyBonuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-monthly-bonuses {--month=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa bonos mensuales (ej. Bono Auto) para el mes anterior o un mes específico (YYYY-MM).';

    /**
     * Execute the console command.
     */
    public function handle(): int // Cambiado a int
    {
        $this->info('Iniciando procesamiento de bonos mensuales...');
        Log::info('Comando ProcessMonthlyBonuses: Iniciando.');

        $monthOption = $this->option('month');
        $monthToProcess = null;

        if ($monthOption) {
            try {
                $monthToProcess = Carbon::createFromFormat('Y-m', $monthOption)->startOfMonth();
                $this->info("Procesando para mes específico: {$monthToProcess->format('Y-F')}");
            } catch (\Exception $e) {
                $this->error("Formato de mes inválido. Use YYYY-MM. Ejemplo: --month=2023-10");
                Log::error("Comando ProcessMonthlyBonuses: Formato de mes inválido '{$monthOption}'.");
                return Command::FAILURE; // Cambiado a Command::FAILURE
            }
        } else {
            // Por defecto, procesar el mes anterior
            $monthToProcess = Carbon::now()->subMonthNoOverflow()->startOfMonth();
            $this->info("Procesando para el mes anterior: {$monthToProcess->format('Y-F')}");
        }
        
        Log::info("Comando ProcessMonthlyBonuses: Disparando MonthlyBonusReviewEvent para el mes: {$monthToProcess->format('Y-m')}");
        
        // Disparar el evento con una fecha del mes a revisar (el listener usará start/end of month)
        // Pasamos el último día del mes para que BonusService lo use como $eventPayload (endDateOfMonth)
        MonthlyBonusReviewEvent::dispatch($monthToProcess->copy()->endOfMonth());

        $this->info("Evento MonthlyBonusReviewEvent disparado para {$monthToProcess->format('Y-F')}. El procesamiento continuará en el listener.");
        Log::info('Comando ProcessMonthlyBonuses: Completado.');
        return Command::SUCCESS; // Cambiado a Command::SUCCESS
    }
}
