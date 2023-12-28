<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('brands')->insert([[
            'id' => Str::ulid()->toBase32(),
            'name' => 'ForgeFlex Tools',
            'slug' => 'forgeflex-tools'
        ], ['id' => Str::ulid()->toBase32(),
            'name' => 'MightyCraft Hardware',
            'slug' => 'mightycraft-hardware']]);
    }
}
