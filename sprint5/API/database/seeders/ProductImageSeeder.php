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
            'file_name' => 'pliers01.avif',
            'title' => 'Combination pliers'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Everyday basics',
            'by_url' => 'https://unsplash.com/@zanardi',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/I8eTuMmxIfo',
            'file_name' => 'pliers02.avif',
            'title' => 'Pliers'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Michael Dziedzic',
            'by_url' => 'https://unsplash.com/@lazycreekimages',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/pM9pkc9J918',
            'file_name' => 'pliers03.avif',
            'title' => 'Bolt cutters'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Brett Jordan',
            'by_url' => 'https://unsplash.com/@brett_jordan',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/GamuDTVm02g',
            'file_name' => 'pliers04.avif',
            'title' => 'Long nose pliers'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Yasin Hasan',
            'by_url' => 'https://unsplash.com/@yasin',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/dwlxTSpfKXg',
            'file_name' => 'pliers05.avif',
            'title' => 'Slip joint pliers'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'iMattSmart',
            'by_url' => 'https://unsplash.com/@imattsmart',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/jaLaLQdkBOE',
            'file_name' => 'hammer01.avif',
            'title' => 'Claw Hammer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Jozsef Hocza',
            'by_url' => 'https://unsplash.com/@hocza',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/D3nouOYbALc',
            'file_name' => 'hammer02.avif',
            'title' => 'Hammer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Andrew George',
            'by_url' => 'https://unsplash.com/@andrewjoegeorge',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/YU2mCvXR0wA',
            'file_name' => 'hammer03.avif',
            'title' => 'Claw Hammer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'ANIRUDH',
            'by_url' => 'https://unsplash.com/@lanirudhreddy',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/3esjG-nlgyk',
            'file_name' => 'hammer04.avif',
            'title' => 'Hammer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Fausto Marqués',
            'by_url' => 'https://unsplash.com/@faustomarques',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/A9dq-L3zzHA',
            'file_name' => 'hammer05.avif',
            'title' => 'Hammer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Jonny Gios',
            'by_url' => 'https://unsplash.com/@supergios',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/ARaYGFeuwpU',
            'file_name' => 'hammer06.avif',
            'title' => 'Claw Hammer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Wesley Tingey',
            'by_url' => 'https://unsplash.com/@wesleyphotography',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/9z9fxr_7Z-k',
            'file_name' => 'hammer07.avif',
            'title' => 'Hammer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Jonathan Cooper',
            'by_url' => 'https://unsplash.com/@theshuttervision',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/fTpzd-PjyDQ',
            'file_name' => 'saw01.avif',
            'title' => 'Wood Saw'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Recha Oktaviani',
            'by_url' => 'https://unsplash.com/@rechaoktaviani',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/t__61ap00Mc',
            'file_name' => 'wrench01.avif',
            'title' => 'Adjustable wrench'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Tekton',
            'by_url' => 'https://unsplash.com/@tekton_tools',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/9z7t48S5C_g',
            'file_name' => 'wrench02.avif',
            'title' => 'Wrench'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Akshat Swaminath',
            'by_url' => 'https://unsplash.com/@akshat_swaminath',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/hg34mNWFRaI',
            'file_name' => 'wrench03.avif',
            'title' => 'Open-end spanners (Set)'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Tekton',
            'by_url' => 'https://unsplash.com/@tekton_tools',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/jlY6nV_STIw',
            'file_name' => 'screwdriver01.avif',
            'title' => 'Phillips Screwdriver'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Everyday basics',
            'by_url' => 'https://unsplash.com/@zanardi',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/a6LO3iO5AIk',
            'file_name' => 'screwdriver02.avif',
            'title' => 'Screwdriver'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'subvertivo _lab',
            'by_url' => 'https://unsplash.com/@subvertivo_lab',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/G6yracEEm8A',
            'file_name' => 'sander01.avif',
            'title' => 'Sheet sander'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Luther.M.E. Bottrill',
            'by_url' => 'https://unsplash.com/@luthermeb',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/7kHN1-9dD_4',
            'file_name' => 'sander02.avif',
            'title' => 'Belt sander'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Luther.M.E. Bottrill',
            'by_url' => 'https://unsplash.com/@luthermeb',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/EgIT3YHBo9E',
            'file_name' => 'saw02.avif',
            'title' => 'Saw'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Adi Goldstein',
            'by_url' => 'https://unsplash.com/@adigold1',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/aO4c6o4H2MI',
            'file_name' => 'sander03.avif',
            'title' => 'Random orbit sander'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Syed Hussaini',
            'by_url' => 'https://unsplash.com/@syhussaini',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/MXeDE_yCdHQ',
            'file_name' => 'drill01.avif',
            'title' => 'Cordless Drill'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'NeONBRAND',
            'by_url' => 'https://unsplash.com/@neonbrand',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/CuDoRFyTkAQ',
            'file_name' => 'drill02.avif',
            'title' => 'Cordless Drill'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Jonathan Cooper',
            'by_url' => 'https://unsplash.com/@theshuttervision',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/7sZwThSntdw',
            'file_name' => 'drill03.avif',
            'title' => 'Cordless Drill 18V'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Manik Roy',
            'by_url' => 'https://unsplash.com/@pixnum',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/_UGTrlvki_A',
            'file_name' => 'drill04.avif',
            'title' => 'Cordless Drill'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Ade Adebowale',
            'by_url' => 'https://unsplash.com/@adebowax',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/DKr6BEdI2sE',
            'file_name' => 'crane01.avif',
            'title' => 'Crane'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'John Kakuk',
            'by_url' => 'https://unsplash.com/@mgnfy',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/HvvPceHYLOg',
            'file_name' => 'excavator01.avif',
            'title' => 'Excavator'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Zac Edmonds',
            'by_url' => 'https://unsplash.com/@zacedmo',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/N1LBcqLP9ec',
            'file_name' => 'bulldozer01.avif',
            'title' => 'Bulldozer'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Collab Media',
            'by_url' => 'https://unsplash.com/@collab_media',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/a-couple-of-tools-that-are-on-a-table-JQZyN8WJPh0',
            'file_name' => 'chisels01.avif',
            'title' => 'Chisel'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Octavian Iordache',
            'by_url' => 'https://unsplash.com/@octavianiordache',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/brown-wooden-wall-mounted-rack-FgP27oU8LWw',
            'file_name' => 'chisels02.avif',
            'title' => 'Chisel'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Clay Banks',
            'by_url' => 'https://unsplash.com/@claybanks',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/brown-wooden-handled-stainless-steel-fork-and-bread-knife-PHO_ilA8dGg',
            'file_name' => 'chisels03.avif',
            'title' => 'Chisel'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Brett Jordan',
            'by_url' => 'https://unsplash.com/@brett_jordan',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/blue-and-yellow-measuring-tape-faOPbPmznfQ',
            'file_name' => 'measure01.avif',
            'title' => 'Tape Measure 7.5m'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Diana Polekhina',
            'by_url' => 'https://unsplash.com/@diana_pole',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/silver-and-black-necklace-on-yellow-textile-iUfusOthmgQ',
            'file_name' => 'measure02.avif',
            'title' => 'Measuring Tape'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Immo Wegmann',
            'by_url' => 'https://unsplash.com/@macroman',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/yellow-and-black-measuring-tape-1abCQ_3g_UY',
            'file_name' => 'measure03.avif',
            'title' => 'Tape Measure 5m'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Markus Spiske',
            'by_url' => 'https://unsplash.com/@markusspiske',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/brown-ruler-with-stand-pwpVGQ-A5qI',
            'file_name' => 'measure04.avif',
            'title' => 'Square Ruler'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'wilson montoya',
            'by_url' => 'https://unsplash.com/@dogblack22',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/black-framed-sunglasses-with-yellow-lens-Cf_d_nDtOcM',
            'file_name' => 'goggles01.avif',
            'title' => 'Safety Goggles'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Laurence Ziegler',
            'by_url' => 'https://unsplash.com/@laurenceziegler',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/a-helmet-that-is-on-top-of-a-pile-of-leaves-niKVn6qfYAs',
            'file_name' => 'goggles02.avif',
            'title' => 'Safety Helmet Face Shield'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Roman Kraft',
            'by_url' => 'https://unsplash.com/@iamromankraft',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/pair-of-brown-and-yellow-leather-gloves-n_vD-7RxA3Q',
            'file_name' => 'gloves01.avif',
            'title' => 'Protective Gloves'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Gabriel Alenius',
            'by_url' => 'https://unsplash.com/@gabrielalenius',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/a-person-wearing-a-yellow-jacket-and-black-gloves-dA-oPHeQZkA',
            'file_name' => 'gloves02.avif',
            'title' => 'Super-thin Protection Gloves'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Ümit Yıldırım',
            'by_url' => 'https://unsplash.com/@umityildirim',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/red-hard-hat-on-pavement-9OB46apMbC4',
            'file_name' => 'helmet01.avif',
            'title' => 'Construction Helmet'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Eliza Diamond',
            'by_url' => 'https://unsplash.com/@eliza28diamonds',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/gray-screw-bolt-lot-pvm5QvjFrJ8',
            'file_name' => 'fasteners01.avif',
            'title' => 'Screws'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Edge2Edge Media',
            'by_url' => 'https://unsplash.com/@edge2edgemedia',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/silver-and-black-metal-tools-qHhJkxare7A',
            'file_name' => 'fasteners02.avif',
            'title' => 'Nuts and bolts'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Milad Fallah',
            'by_url' => 'https://unsplash.com/@miladfallah90',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/silver-screw-lot-on-brown-wooden-table-wwZYL4BYFto',
            'file_name' => 'fasteners03.avif',
            'title' => 'Cross-head screws'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Ben Lodge',
            'by_url' => 'https://unsplash.com/@benlodge123',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/stainless-steel-11-screw-e3Y7qB67aow',
            'file_name' => 'fasteners04.avif',
            'title' => 'Flat-Head Wood Screws'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Mika Baumeister',
            'by_url' => 'https://unsplash.com/@kommumikation',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/silver-and-black-round-coins-c71yQepDEjo',
            'file_name' => 'fasteners05.avif',
            'title' => 'M4 Nuts'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Robert Ruggiero',
            'by_url' => 'https://unsplash.com/@robert2301',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/a-pile-of-different-colored-washers-sitting-on-top-of-a-table-oY6774he6GQ',
            'file_name' => 'fasteners06.avif',
            'title' => 'Washers'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'cetteup',
            'by_url' => 'https://unsplash.com/@cetteup',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/black-and-red-3m-earmuffs-beside-miter-saw-IC5sX-7PRN8',
            'file_name' => 'earprotection01.avif',
            'title' => 'Ear Protection'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Tekton',
            'by_url' => 'https://unsplash.com/@tekton_tools',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/black-and-gray-metal-tool-LtphNTXHQAc',
            'file_name' => 'toolcabinet02.avif',
            'title' => 'Drawer Tool Cabinet'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Georg Eiermann',
            'by_url' => 'https://unsplash.com/@georgeiermann',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/a-group-of-wooden-objects-uXWbowHdJqk',
            'file_name' => 'toolcabinet01.avif',
            'title' => 'Tool Cabinet'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Matteo Minoglio',
            'by_url' => 'https://unsplash.com/@ilmino',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/opened-brown-wooden-drawers-m8-NX7WxbpM',
            'file_name' => 'workbench02.avif',
            'title' => 'Workbench with Drawers'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'Brett Garwood',
            'by_url' => 'https://unsplash.com/@brettgarwood',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/brown-wooden-stick-on-brown-wooden-table-asZVvgMGshc',
            'file_name' => 'workbench01.avif',
            'title' => 'Wooden Workbench'
        ], [
            'id' => Str::ulid()->toBase32(),
            'by_name' => 'jesse orrico',
            'by_url' => 'https://unsplash.com/@jessedo81',
            'source_name' => 'Unsplash',
            'source_url' => 'https://unsplash.com/photos/brown-handled-hammer-and-with-tool-belt-L94dWXNKwrY',
            'file_name' => 'toolbelt01.avif',
            'title' => 'Leather toolbelt'
        ]]);
    }
}
