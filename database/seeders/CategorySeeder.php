<?php

namespace Database\Seeders;

use App\Modules\Category\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Électronique',
                'slug' => 'electronique',
                'description' => 'Téléphones, ordinateurs, tablettes, etc.',
                'icon' => 'phone',
                'color' => '#3B82F6',
                'order' => 1,
                'children' => [
                    ['name' => 'Téléphones', 'slug' => 'telephones', 'icon' => 'smartphone'],
                    ['name' => 'Ordinateurs', 'slug' => 'ordinateurs', 'icon' => 'laptop'],
                    ['name' => 'Tablettes', 'slug' => 'tablettes', 'icon' => 'tablet'],
                ],
            ],
            [
                'name' => 'Documents',
                'slug' => 'documents',
                'description' => 'Cartes d\'identité, passeports, permis, etc.',
                'icon' => 'file',
                'color' => '#EF4444',
                'order' => 2,
                'children' => [
                    ['name' => 'Cartes d\'identité', 'slug' => 'cartes-identite', 'icon' => 'id-card'],
                    ['name' => 'Passeports', 'slug' => 'passeports', 'icon' => 'passport'],
                    ['name' => 'Permis de conduire', 'slug' => 'permis-conduire', 'icon' => 'card'],
                ],
            ],
            [
                'name' => 'Vêtements',
                'slug' => 'vetements',
                'description' => 'Vêtements, chaussures, accessoires',
                'icon' => 'shirt',
                'color' => '#10B981',
                'order' => 3,
            ],
            [
                'name' => 'Sacs et Bagages',
                'slug' => 'sacs-bagages',
                'description' => 'Sacs à main, sacs à dos, valises',
                'icon' => 'bag',
                'color' => '#F59E0B',
                'order' => 4,
            ],
            [
                'name' => 'Clés',
                'slug' => 'cles',
                'description' => 'Clés de voiture, clés de maison, etc.',
                'icon' => 'key',
                'color' => '#8B5CF6',
                'order' => 5,
            ],
            [
                'name' => 'Portefeuilles',
                'slug' => 'portefeuilles',
                'description' => 'Portefeuilles, porte-monnaie',
                'icon' => 'wallet',
                'color' => '#6366F1',
                'order' => 6,
            ],
            [
                'name' => 'Bijoux',
                'slug' => 'bijoux',
                'description' => 'Bagues, colliers, montres',
                'icon' => 'gem',
                'color' => '#EC4899',
                'order' => 7,
            ],
            [
                'name' => 'Animaux',
                'slug' => 'animaux',
                'description' => 'Chiens, chats, autres animaux perdus',
                'icon' => 'paw',
                'color' => '#F97316',
                'order' => 8,
            ],
            [
                'name' => 'Personnes',
                'slug' => 'personnes',
                'description' => 'Personnes disparues ou recherchées',
                'icon' => 'user',
                'color' => '#14B8A6',
                'order' => 9,
            ],
            [
                'name' => 'Autre',
                'slug' => 'autre',
                'description' => 'Autres objets non classifiés',
                'icon' => 'question',
                'color' => '#6B7280',
                'order' => 10,
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $category = Category::create($categoryData);

            // Créer les sous-catégories
            foreach ($children as $childData) {
                $childData['parent_id'] = $category->id;
                $childData['color'] = $category->color;
                $childData['is_active'] = true;
                Category::create($childData);
            }
        }

        $this->command->info('Categories created successfully!');
    }
}
