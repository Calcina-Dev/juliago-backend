<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Mesa;

class MesasTableSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 10) as $i) {
            Mesa::create([
                'numero' => $i,
                'estado' => 'libre'
                
            ]);
        }
    }
}
