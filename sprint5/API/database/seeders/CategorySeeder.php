<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('categories')->insert([[
            'id' => Str::ulid()->toBase32(),
            'parent_id' => null,
            'name' => 'Hand Tools',
            'slug' => 'hand-tools'
        ], ['id' => Str::ulid()->toBase32(),
            'parent_id' => null,
            'name' => 'Power Tools',
            'slug' => 'power-tools'
        ], [
            'id' => Str::ulid()->toBase32(),
            'parent_id' => null,
            'name' => 'Other',
            'slug' => 'other']]);

        $handToolsId = DB::table('categories')->where('name', '=', 'Hand Tools')->first()->id;
        $powerToolsId = DB::table('categories')->where('name', '=', 'Power Tools')->first()->id;

        DB::table('categories')->insert([
            [
                'id' => Str::ulid()->toBase32(),
                'parent_id' => $handToolsId,
                'name' => 'Hammer',
                'slug' => 'hammer'],
            [
                'id' => Str::ulid()->toBase32(),
                'parent_id' => $handToolsId,
                'name' => 'Hand Saw',
                'slug' => 'hand-saw'],
            [
                'id' => Str::ulid()->toBase32(),
                'parent_id' => $handToolsId,
                'name' => 'Wrench',
                'slug' => 'wrench'],
            [
                'id' => Str::ulid()->toBase32(),
                'parent_id' => $handToolsId,
                'name' => 'Screwdriver',
                'slug' => 'screwdriver'],
            [
                'id' => Str::ulid()->toBase32(),
                'parent_id' => $handToolsId,
                'name' => 'Pliers',
                'slug' => 'pliers'],
            [
                'id' => Str::ulid()->toBase32(),
                'parent_id' => $powerToolsId,
                'name' => 'Grinder',
                'slug' => 'grinder'],
            [
                'id' => Str::ulid()->toBase32(),
                'parent_id' => $powerToolsId,
                'name' => 'Sander',
                'slug' => 'sander'],
            [
                'id' => Str::ulid()->toBase32(),
                'parent_id' => $powerToolsId,
                'name' => 'Saw',
                'slug' => 'saw'],
            [
                'id' => Str::ulid()->toBase32(),
                'parent_id' => $handToolsId,
                'name' => 'Drill',
                'slug' => 'drill']]);

    }
}
