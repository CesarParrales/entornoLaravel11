<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rank;
use App\Models\FinancialFreedomCommissionTier;
use Illuminate\Support\Facades\DB;

class FinancialFreedomCommissionTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Ejecutando Seeder de Niveles de Comisión Libertad Financiera...');

        $commissionTiersData = [
            ['rank_slug' => 'empresario', 'max_points_for_rank' => 600, 'percentage' => 0.14],
            ['rank_slug' => 'bronce', 'max_points_for_rank' => 1200, 'percentage' => 0.13],
            ['rank_slug' => 'lider', 'max_points_for_rank' => 2200, 'percentage' => 0.13],
            ['rank_slug' => 'lider-plata', 'max_points_for_rank' => 3800, 'percentage' => 0.12],
            ['rank_slug' => 'lider-oro', 'max_points_for_rank' => 7200, 'percentage' => 0.11],
            ['rank_slug' => 'master', 'max_points_for_rank' => 12000, 'percentage' => 0.09],
            ['rank_slug' => 'diamante', 'max_points_for_rank' => 22000, 'percentage' => 0.07],
            ['rank_slug' => 'doble-diamante', 'max_points_for_rank' => 40000, 'percentage' => 0.06],
            ['rank_slug' => 'director', 'max_points_for_rank' => 80000, 'percentage' => 0.05],
            ['rank_slug' => 'gerente', 'max_points_for_rank' => 150000, 'percentage' => 0.04],
            ['rank_slug' => 'embajador', 'max_points_for_rank' => 250000, 'percentage' => 0.03],
            ['rank_slug' => 'presidente', 'max_points_for_rank' => 450000, 'percentage' => 0.02],
            ['rank_slug' => 'millonario-asp', 'max_points_for_rank' => 1200000, 'percentage' => 0.01],
        ];

        DB::transaction(function () use ($commissionTiersData) {
            foreach ($commissionTiersData as $tierData) {
                $rank = Rank::where('slug', $tierData['rank_slug'])->first();

                if ($rank) {
                    FinancialFreedomCommissionTier::updateOrCreate(
                        ['rank_id' => $rank->id],
                        [
                            'max_points_for_rank' => $tierData['max_points_for_rank'],
                            'percentage' => $tierData['percentage'],
                            'is_active' => true,
                        ]
                    );
                } else {
                    $this->command->warn("No se encontró el rango con slug: " . $tierData['rank_slug'] . ". Omitiendo este nivel de comisión.");
                }
            }
        });

        $this->command->info('Seeder de Niveles de Comisión Libertad Financiera completado.');
    }
}
