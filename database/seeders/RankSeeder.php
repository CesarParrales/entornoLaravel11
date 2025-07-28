<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rank;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Importar Str

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // It's good practice to disable foreign key checks when truncating or re-seeding
        // if there are self-referential keys, though updateOrCreate handles many cases.
        // However, for rank_order and potential self-referential rank_id, it's safer.
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // MySQL
        // For PostgreSQL, you might need to defer constraints or handle differently if issues arise.
        // Rank::truncate(); // Clears the table for a fresh seed. Be careful with this in production.

        $ranksData = [
            // Based on the provided image
            ['name' => 'Inactivo', 'rank_order' => 0, 'required_group_volume' => 0, 'required_direct_sponsors_count' => 0, 'required_direct_sponsor_rank_name' => null, 'compression_depth_level' => null, 'instant_qualification_personal_points' => null, 'leg_alpha_min_percentage_vg' => null, 'leg_beta_min_percentage_vg' => null, 'color_badge' => 'gray'],
            ['name' => 'Registrado', 'rank_order' => 1, 'required_group_volume' => 20, 'required_direct_sponsors_count' => 0, 'required_direct_sponsor_rank_name' => null, 'compression_depth_level' => null, 'instant_qualification_personal_points' => 20, 'leg_alpha_min_percentage_vg' => null, 'leg_beta_min_percentage_vg' => null, 'color_badge' => 'slate'],
            ['name' => 'Activo', 'rank_order' => 2, 'required_group_volume' => 40, 'required_direct_sponsors_count' => 0, 'required_direct_sponsor_rank_name' => null, 'compression_depth_level' => null, 'instant_qualification_personal_points' => 40, 'leg_alpha_min_percentage_vg' => null, 'leg_beta_min_percentage_vg' => null, 'color_badge' => 'green'],
            ['name' => 'Calificado', 'rank_order' => 3, 'required_group_volume' => 80, 'required_direct_sponsors_count' => 2, 'required_direct_sponsor_rank_name' => 'Activo', 'compression_depth_level' => 1, 'instant_qualification_personal_points' => 80, 'leg_alpha_min_percentage_vg' => 0.50, 'leg_beta_min_percentage_vg' => 0.50, 'color_badge' => 'lime'],
            ['name' => 'Empresario', 'rank_order' => 4, 'required_group_volume' => 360, 'required_direct_sponsors_count' => 2, 'required_direct_sponsor_rank_name' => 'Calificado', 'compression_depth_level' => 1, 'instant_qualification_personal_points' => 360, 'leg_alpha_min_percentage_vg' => 0.50, 'leg_beta_min_percentage_vg' => 0.50, 'color_badge' => 'sky'], // Assuming "Activos o calificado" means at least Calificado
            ['name' => 'Bronce', 'rank_order' => 5, 'required_group_volume' => 900, 'required_direct_sponsors_count' => 2, 'required_direct_sponsor_rank_name' => 'Empresario', 'compression_depth_level' => 3, 'instant_qualification_personal_points' => 900, 'leg_alpha_min_percentage_vg' => 0.50, 'leg_beta_min_percentage_vg' => 0.50, 'color_badge' => 'amber'],
            ['name' => 'Lider', 'rank_order' => 6, 'required_group_volume' => 1500, 'required_direct_sponsors_count' => 3, 'required_direct_sponsor_rank_name' => 'Empresario', 'compression_depth_level' => 3, 'instant_qualification_personal_points' => 1500, 'leg_alpha_min_percentage_vg' => 0.40, 'leg_beta_min_percentage_vg' => 0.40, 'color_badge' => 'orange'], // Example percentages, adjust as needed
            ['name' => 'Lider Plata', 'rank_order' => 7, 'required_group_volume' => 3000, 'required_direct_sponsors_count' => 2, 'required_direct_sponsor_rank_name' => 'Bronce', 'compression_depth_level' => 3, 'instant_qualification_personal_points' => 3000, 'leg_alpha_min_percentage_vg' => 0.40, 'leg_beta_min_percentage_vg' => 0.40, 'color_badge' => 'stone'],
            ['name' => 'Lider Oro', 'rank_order' => 8, 'required_group_volume' => 5000, 'required_direct_sponsors_count' => 3, 'required_direct_sponsor_rank_name' => 'Bronce', 'compression_depth_level' => 4, 'instant_qualification_personal_points' => 5000, 'leg_alpha_min_percentage_vg' => 0.40, 'leg_beta_min_percentage_vg' => 0.40, 'color_badge' => 'yellow'],
            ['name' => 'Master', 'rank_order' => 9, 'required_group_volume' => 10000, 'required_direct_sponsors_count' => 2, 'required_direct_sponsor_rank_name' => 'Lider Plata', 'compression_depth_level' => 4, 'instant_qualification_personal_points' => 10000, 'leg_alpha_min_percentage_vg' => 0.40, 'leg_beta_min_percentage_vg' => 0.40, 'color_badge' => 'purple'],
            ['name' => 'Diamante', 'rank_order' => 10, 'required_group_volume' => 20000, 'required_direct_sponsors_count' => 3, 'required_direct_sponsor_rank_name' => 'Lider Plata', 'compression_depth_level' => 4, 'instant_qualification_personal_points' => 20000, 'leg_alpha_min_percentage_vg' => 0.40, 'leg_beta_min_percentage_vg' => 0.40, 'color_badge' => 'cyan'],
            ['name' => 'Doble Diamante', 'rank_order' => 11, 'required_group_volume' => 30000, 'required_direct_sponsors_count' => 4, 'required_direct_sponsor_rank_name' => 'Master', 'compression_depth_level' => 4, 'instant_qualification_personal_points' => 30000, 'leg_alpha_min_percentage_vg' => 0.40, 'leg_beta_min_percentage_vg' => 0.40, 'color_badge' => 'teal'],
            ['name' => 'Director', 'rank_order' => 12, 'required_group_volume' => 60000, 'required_direct_sponsors_count' => 4, 'required_direct_sponsor_rank_name' => 'Diamante', 'compression_depth_level' => 4, 'instant_qualification_personal_points' => 60000, 'leg_alpha_min_percentage_vg' => 0.40, 'leg_beta_min_percentage_vg' => 0.40, 'color_badge' => 'indigo'],
            ['name' => 'Gerente', 'rank_order' => 13, 'required_group_volume' => 120000, 'required_direct_sponsors_count' => 4, 'required_direct_sponsor_rank_name' => 'Diamante', 'compression_depth_level' => 4, 'instant_qualification_personal_points' => 120000, 'leg_alpha_min_percentage_vg' => 0.40, 'leg_beta_min_percentage_vg' => 0.40, 'color_badge' => 'pink'],
            ['name' => 'Embajador', 'rank_order' => 14, 'required_group_volume' => 200000, 'required_direct_sponsors_count' => 4, 'required_direct_sponsor_rank_name' => 'Director', 'compression_depth_level' => 4, 'instant_qualification_personal_points' => 200000, 'leg_alpha_min_percentage_vg' => 0.40, 'leg_beta_min_percentage_vg' => 0.40, 'color_badge' => 'rose'],
            ['name' => 'Presidente', 'rank_order' => 15, 'required_group_volume' => 400000, 'required_direct_sponsors_count' => 4, 'required_direct_sponsor_rank_name' => 'Director', 'compression_depth_level' => 4, 'instant_qualification_personal_points' => 400000, 'leg_alpha_min_percentage_vg' => 0.40, 'leg_beta_min_percentage_vg' => 0.40, 'color_badge' => 'fuchsia'],
            ['name' => 'Millonario ASP', 'rank_order' => 16, 'required_group_volume' => 1000000, 'required_direct_sponsors_count' => 4, 'required_direct_sponsor_rank_name' => 'Embajador', 'compression_depth_level' => 4, 'instant_qualification_personal_points' => 1000000, 'leg_alpha_min_percentage_vg' => 0.40, 'leg_beta_min_percentage_vg' => 0.40, 'color_badge' => 'violet'],
        ];

        // First pass: create all ranks without the self-referential foreign key
        foreach ($ranksData as $rankDatum) {
            Rank::updateOrCreate(
                ['rank_order' => $rankDatum['rank_order']], // Use rank_order as the unique key for updateOrCreate
                [
                    'name' => $rankDatum['name'],
                    'description' => $rankDatum['description'] ?? null,
                    'required_group_volume' => $rankDatum['required_group_volume'],
                    'required_direct_sponsors_count' => $rankDatum['required_direct_sponsors_count'],
                    // 'required_direct_sponsor_rank_id' will be updated in the second pass
                    'compression_depth_level' => $rankDatum['compression_depth_level'],
                    'instant_qualification_personal_points' => $rankDatum['instant_qualification_personal_points'],
                    'leg_alpha_min_percentage_vg' => $rankDatum['leg_alpha_min_percentage_vg'],
                    'leg_beta_min_percentage_vg' => $rankDatum['leg_beta_min_percentage_vg'],
                    'is_active' => $rankDatum['is_active'] ?? true,
                    'color_badge' => $rankDatum['color_badge'] ?? null,
                    'slug' => Str::slug($rankDatum['name']), // Generar y guardar slug
                ]
            );
        }

        // Second pass: update the self-referential foreign key 'required_direct_sponsor_rank_id'
        foreach ($ranksData as $rankDatum) {
            if (!empty($rankDatum['required_direct_sponsor_rank_name'])) {
                $currentRank = Rank::where('rank_order', $rankDatum['rank_order'])->first();
                $requiredSponsorRank = Rank::where('name', $rankDatum['required_direct_sponsor_rank_name'])->first();

                if ($currentRank && $requiredSponsorRank) {
                    $currentRank->required_direct_sponsor_rank_id = $requiredSponsorRank->id;
                    $currentRank->save();
                } else {
                    $this->command->warn("Could not set required_direct_sponsor_rank_id for rank '{$rankDatum['name']}'. Required rank '{$rankDatum['required_direct_sponsor_rank_name']}' not found.");
                }
            }
        }
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // MySQL

        $this->command->info(count($ranksData) . ' rangos han sido creados o actualizados.');
    }
}
