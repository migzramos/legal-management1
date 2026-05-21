<?php
namespace Database\Seeders;

use App\Models\CaseCategory;
use Illuminate\Database\Seeder;

class CaseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Criminal Law',        'description' => 'Cases involving criminal offenses'],
            ['name' => 'Civil Law',            'description' => 'Disputes between individuals or organizations'],
            ['name' => 'Family Law',           'description' => 'Divorce, custody, adoption, and related matters'],
            ['name' => 'Corporate Law',        'description' => 'Business and commercial legal matters'],
            ['name' => 'Labor Law',            'description' => 'Employment and workplace disputes'],
            ['name' => 'Real Estate Law',      'description' => 'Property and land disputes'],
            ['name' => 'Immigration Law',      'description' => 'Visa, citizenship, and immigration matters'],
            ['name' => 'Intellectual Property','description' => 'Patents, trademarks, and copyright cases'],
            ['name' => 'Tax Law',              'description' => 'Tax disputes and compliance matters'],
            ['name' => 'Administrative Law',   'description' => 'Government agency disputes'],
        ];

        foreach ($categories as $category) {
            CaseCategory::updateOrCreate(
                ['name' => $category['name']],
                [...$category, 'is_active' => true]
            );
        }
    }
}