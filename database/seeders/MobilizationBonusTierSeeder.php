<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rank;
use App\Models\MobilizationBonusTier;
use Illuminate\Support\Facades\DB;

class MobilizationBonusTierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Ejecutando Seeder de Niveles de Bono Movilización...');

        $mobilizationTiersData = [
            ['rank_slug' => 'lider', 'bonus_amount' => 50.00],
            ['rank_slug' => 'lider-plata', 'bonus_amount' => 100.00],
            ['rank_slug' => 'lider-oro', 'bonus_amount' => 150.00],
            ['rank_slug' => 'master', 'bonus_amount' => 250.00],
            ['rank_slug' => 'diamante', 'bonus_amount' => 400.00],
            ['rank_slug' => 'doble-diamante', 'bonus_amount' => 600.00],
            ['rank_slug' => 'director', 'bonus_amount' => 900.00],
            ['rank_slug' => 'gerente', 'bonus_amount' => 1800.00],
            ['rank_slug' => 'embajador', 'bonus_amount' => 3000.00],
            ['rank_slug' => 'presidente', 'bonus_amount' => 6000.00],
            // Asumimos que 'Millonario ASP' no está en esta tabla de movilización según la imagen,
            // pero si lo estuviera, se añadiría aquí.
        ];

        DB::transaction(function () use ($mobilizationTiersData) {
            foreach ($mobilizationTiersData as $tierData) {
                $rank = Rank::where('slug', $tierData['rank_slug'])->first();

                if ($rank) {
                    MobilizationBonusTier::updateOrCreate(
                        ['rank_id' => $rank->id],
                        [
                            'required_consecutive_periods' => 2, // Siempre 2 según la tabla
                            'bonus_amount' => $tierData['bonus_amount'],
                            'is_active' => true,
                        ]
                    );
                } else {
                    $this->command->warn("No se encontró el rango con slug: " . $tierData['rank_slug'] . ". Omitiendo este nivel de bono movilización.");
                }
            }
        });

        $this->command->info('Seeder de Niveles de Bono Movilización completado.');
    }
}
