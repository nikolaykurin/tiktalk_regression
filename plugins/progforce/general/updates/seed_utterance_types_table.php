<?php namespace Progforce\General\Updates;

use Seeder;
use Progforce\General\Models\UtteranceType;

class SeedUtteranceTypesTable extends Seeder
{
    public function run() {
        UtteranceType::create(['id' => 1, 'name' => 'Words']);
        UtteranceType::create(['id' => 2, 'name' => 'Sentences']);
    }
}
