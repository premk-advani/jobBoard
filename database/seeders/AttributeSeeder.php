<?php

namespace Database\Seeders;

use App\Models\Attribute;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    public function run()
    {
        Attribute::create([
            'name' => 'Experience Level',
            'type' => 'select',
            'options' => json_encode(['Junior', 'Mid', 'Senior']),
        ]);

        Attribute::create([
            'name' => 'Job Duration',
            'type' => 'number',
            'options' => null,
        ]);

        Attribute::create([
            'name' => 'Is it remote?',
            'type' => 'boolean',
            'options' => null,
        ]);
    }
}
