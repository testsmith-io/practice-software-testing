<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductImageSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('product_images')->insert([[
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Helinton Fantin',
            'by_url' => 'https://unsplash.com/@fantin',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/W8BNwvOvW4M',
            'file_name' => 'pliers01.jpeg',
            'title' => 'Combination pliers'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Everyday basics',
            'by_url' => 'https://unsplash.com/@zanardi',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/I8eTuMmxIfo',
            'file_name' => 'pliers02.jpeg',
            'title' => 'Pliers'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Michael Dziedzic',
            'by_url' => 'https://unsplash.com/@lazycreekimages',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/pM9pkc9J918',
            'file_name' => 'pliers03.jpeg',
            'title' => 'Bolt cutters'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Brett Jordan',
            'by_url' => 'https://unsplash.com/@brett_jordan',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/GamuDTVm02g',
            'file_name' => 'pliers04.jpeg',
            'title' => 'Long nose pliers'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Yasin Hasan',
            'by_url' => 'https://unsplash.com/@yasin',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/dwlxTSpfKXg',
            'file_name' => 'pliers05.jpeg',
            'title' => 'Slip joint pliers'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'iMattSmart',
            'by_url' => 'https://unsplash.com/@imattsmart',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/jaLaLQdkBOE',
            'file_name' => 'hammer01.jpeg',
            'title' => 'Claw Hammer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Jozsef Hocza',
            'by_url' => 'https://unsplash.com/@hocza',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/D3nouOYbALc',
            'file_name' => 'hammer02.jpeg',
            'title' => 'Hammer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Andrew George',
            'by_url' => 'https://unsplash.com/@andrewjoegeorge',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/YU2mCvXR0wA',
            'file_name' => 'hammer03.jpeg',
            'title' => 'Claw Hammer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'ANIRUDH',
            'by_url' => 'https://unsplash.com/@lanirudhreddy',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/3esjG-nlgyk',
            'file_name' => 'hammer04.jpeg',
            'title' => 'Hammer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Fausto Marqués',
            'by_url' => 'https://unsplash.com/@faustomarques',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/A9dq-L3zzHA',
            'file_name' => 'hammer05.jpeg',
            'title' => 'Hammer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Jonny Gios',
            'by_url' => 'https://unsplash.com/@supergios',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/ARaYGFeuwpU',
            'file_name' => 'hammer06.jpeg',
            'title' => 'Claw Hammer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Wesley Tingey',
            'by_url' => 'https://unsplash.com/@wesleyphotography',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/9z9fxr_7Z-k',
            'file_name' => 'hammer07.jpeg',
            'title' => 'Hammer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Jonathan Cooper',
            'by_url' => 'https://unsplash.com/@theshuttervision',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/fTpzd-PjyDQ',
            'file_name' => 'saw01.jpeg',
            'title' => 'Wood Saw'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Recha Oktaviani',
            'by_url' => 'https://unsplash.com/@rechaoktaviani',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/t__61ap00Mc',
            'file_name' => 'wrench01.jpeg',
            'title' => 'Adjustable wrench'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Tekton',
            'by_url' => 'https://unsplash.com/@tekton_tools',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/9z7t48S5C_g',
            'file_name' => 'wrench02.jpeg',
            'title' => 'Wrench'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Akshat Swaminath',
            'by_url' => 'https://unsplash.com/@akshat_swaminath',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/hg34mNWFRaI',
            'file_name' => 'wrench03.jpeg',
            'title' => 'Open-end spanners (Set)'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Tekton',
            'by_url' => 'https://unsplash.com/@tekton_tools',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/jlY6nV_STIw',
            'file_name' => 'screwdriver01.jpeg',
            'title' => 'Phillips Screwdriver'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Everyday basics',
            'by_url' => 'https://unsplash.com/@zanardi',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/a6LO3iO5AIk',
            'file_name' => 'screwdriver02.jpeg',
            'title' => 'Screwdriver'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'subvertivo _lab',
            'by_url' => 'https://unsplash.com/@subvertivo_lab',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/G6yracEEm8A',
            'file_name' => 'sander01.jpeg',
            'title' => 'Sheet sander'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Luther.M.E. Bottrill',
            'by_url' => 'https://unsplash.com/@luthermeb',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/7kHN1-9dD_4',
            'file_name' => 'sander02.jpeg',
            'title' => 'Belt sander'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Luther.M.E. Bottrill',
            'by_url' => 'https://unsplash.com/@luthermeb',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/EgIT3YHBo9E',
            'file_name' => 'saw02.jpeg',
            'title' => 'Saw'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Adi Goldstein',
            'by_url' => 'https://unsplash.com/@adigold1',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/aO4c6o4H2MI',
            'file_name' => 'sander03.jpeg',
            'title' => 'Random orbit sander'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Syed Hussaini',
            'by_url' => 'https://unsplash.com/@syhussaini',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/MXeDE_yCdHQ',
            'file_name' => 'drill01.jpeg',
            'title' => 'Cordless Drill'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'NeONBRAND',
            'by_url' => 'https://unsplash.com/@neonbrand',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/CuDoRFyTkAQ',
            'file_name' => 'drill02.jpeg',
            'title' => 'Cordless Drill'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Jonathan Cooper',
            'by_url' => 'https://unsplash.com/@theshuttervision',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/7sZwThSntdw',
            'file_name' => 'drill03.jpeg',
            'title' => 'Cordless Drill 18V'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Manik Roy',
            'by_url' => 'https://unsplash.com/@pixnum',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/_UGTrlvki_A',
            'file_name' => 'drill04.jpeg',
            'title' => 'Cordless Drill'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Ade Adebowale',
            'by_url' => 'https://unsplash.com/@adebowax',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/DKr6BEdI2sE',
            'file_name' => 'crane01.jpg',
            'title' => 'Crane'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'John Kakuk',
            'by_url' => 'https://unsplash.com/@mgnfy',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/HvvPceHYLOg',
            'file_name' => 'excavator01.jpg',
            'title' => 'Excavator'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Zac Edmonds',
            'by_url' => 'https://unsplash.com/@zacedmo',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/N1LBcqLP9ec',
            'file_name' => 'bulldozer01.jpg',
            'title' => 'Bulldozer'
        ]]);

    }
}
