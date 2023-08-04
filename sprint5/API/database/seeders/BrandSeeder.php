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
            'name' => 'Brand name 1',
            'slug' => 'brand-name-1'
        ], ['id' => Str::ulid()->toBase32(),
            'name' => 'Brand name 2',
            'slug' => 'brand-name-2']]);

    }
}
