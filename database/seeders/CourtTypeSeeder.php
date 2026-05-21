<?php
namespace Database\Seeders;

use App\Models\CourtType;
use Illuminate\Database\Seeder;

class CourtTypeSeeder extends Seeder
{
    public function run(): void
    {
        $courtTypes = [
            ['name' => 'Supreme Court',               'jurisdiction' => 'National'],
            ['name' => 'Court of Appeals',             'jurisdiction' => 'National'],
            ['name' => 'Regional Trial Court',         'jurisdiction' => 'Regional'],
            ['name' => 'Metropolitan Trial Court',     'jurisdiction' => 'Metropolitan'],
            ['name' => 'Municipal Trial Court',        'jurisdiction' => 'Municipal'],
            ['name' => 'Family Court',                 'jurisdiction' => 'Regional'],
            ['name' => 'Sandiganbayan',                'jurisdiction' => 'National'],
            ['name' => 'Court of Tax Appeals',         'jurisdiction' => 'National'],
            ['name' => 'Shari\'a District Court',      'jurisdiction' => 'Regional'],
            ['name' => 'National Labor Relations Commission', 'jurisdiction' => 'National'],
        ];

        foreach ($courtTypes as $courtType) {
            CourtType::updateOrCreate(
                ['name' => $courtType['name']],
                [...$courtType, 'is_active' => true]
            );
        }
    }
}