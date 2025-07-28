<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rank;
use App\Models\RecognitionBonusTier;
use Illuminate\Support\Facades\DB;

class RecognitionBonusTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Ejecutando Seeder de Niveles de Bono Reconocimiento...');

        $recognitionTiersData = [
            // Datos de la tabla proporcionada
            ['rank_slug' => 'diamante',       'annual_periods_required' => 3, 'bonus_amount' => 1000.00],
            ['rank_slug' => 'doble-diamante', 'annual_periods_required' => 3, 'bonus_amount' => 2000.00],
            ['rank_slug' => 'director',       'annual_periods_required' => 3, 'bonus_amount' => 3000.00],
            ['rank_slug' => 'gerente',        'annual_periods_required' => 6, 'bonus_amount' => 6000.00],
            ['rank_slug' => 'embajador',      'annual_periods_required' => 6, 'bonus_amount' => 9000.00],
            ['rank_slug' => 'presidente',     'annual_periods_required' => 6, 'bonus_amount' => 20000.00],
        ];

        DB::transaction(function () use ($recognitionTiersData) {
            foreach ($recognitionTiersData as $tierData) {
                $rank = Rank::where('slug', $tierData['rank_slug'])->first();

                if ($rank) {
                    RecognitionBonusTier::updateOrCreate(
                        ['rank_id' => $rank->id],
                        [
                            'annual_periods_required' => $tierData['annual_periods_required'],
                            'bonus_amount' => $tierData['bonus_amount'],
                            'is_active' => true,
                        ]
                    );
                } else {
                    $this->command->warn("No se encontró el rango con slug: " . $tierData['rank_slug'] . ". Omitiendo este nivel de bono reconocimiento.");
                }
            }
        });

        $this->command->info('Seeder de Niveles de Bono Reconocimiento completado.');
    }
}
