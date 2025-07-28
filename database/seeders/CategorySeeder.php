<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str; // Para generar slugs

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Suplemento Nutricional',
                'description' => 'Productos físicos naturales con vitaminas, minerales y nutrientes que apoyan el bienestar general del organismo.',
            ],
            [
                'name' => 'Sistema Reproductor',
                'description' => 'Suplementos naturales enfocados en fortalecer la fertilidad, el equilibrio hormonal y la salud reproductiva.',
            ],
            [
                'name' => 'Sistema Nervioso',
                'description' => 'Productos físicos naturales que favorecen la memoria, concentración y salud cerebral.',
            ],
            [
                'name' => 'Sistema Músculo - Esquelético',
                'description' => 'Fórmulas naturales diseñadas para fortalecer huesos, músculos y aliviar molestias articulares.',
            ],
            [
                'name' => 'Sistema Inmunológico',
                'description' => 'Suplementos físicos que estimulan y refuerzan las defensas naturales del cuerpo de forma saludable.',
            ],
            [
                'name' => 'Sistema Digestivo y Metabólico',
                'description' => 'Productos naturales que apoyan la digestión, el metabolismo y el equilibrio intestinal.',
            ],
            [
                'name' => 'Cosmetica Biologica',
                'description' => 'Línea de cuidado corporal con ingredientes biológicos, enfocada en proteger, nutrir y embellecer de forma natural.',
            ],
            [
                'name' => 'Paquete Registro',
                'description' => 'Kit físico de bienvenida para nuevos usuarios, con productos esenciales y beneficios iniciales.',
            ],
            [
                'name' => 'Paquete Re-consumo y Activaciones',
                'description' => 'Paquetes especiales con productos físicos destinados a clientes frecuentes o reactivaciones.',
            ],
            [
                'name' => 'Herramientas',
                'description' => 'Material físico impreso de apoyo comercial y de marketing para promover los productos y el negocio.',
            ],
            [
                'name' => 'Promociones',
                'description' => 'Productos físicos creados para campañas promocionales o edición limitada con fines de impulso comercial.',
            ],
            [
                'name' => 'Eventos',
                'description' => 'Entradas o accesos físicos para participar en eventos, convenciones o capacitaciones de la marca.',
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::updateOrCreate(
                ['slug' => Str::slug($categoryData['name'])], // Usar slug como identificador único para evitar duplicados
                [
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'is_active' => true, // Por defecto activas
                    // 'parent_id' => null, // Asumimos que son categorías de nivel superior por ahora
                ]
            );
        }

        $this->command->info(count($categories) . ' categorías han sido creadas o actualizadas.');
    }
}
