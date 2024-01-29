<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('products')->insert([[
            'id' => Str::ulid()->toBase32(),
            'name' => 'Combination Pliers',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris viverra felis nec pellentesque feugiat. Donec faucibus arcu maximus, convallis nisl eu, placerat dolor. Morbi finibus neque nec tincidunt pharetra. Sed eget tortor malesuada, mollis enim id, condimentum nisi. In viverra quam at bibendum ultricies. Aliquam quis eros ex. Etiam at pretium massa, ut pharetra tortor. Sed vel metus sem. Suspendisse ac molestie turpis. Duis luctus justo massa, faucibus ornare eros elementum et. Vestibulum quis nisl vitae ante dapibus tempor auctor ut leo. Mauris consectetur et magna at ultricies. Proin a aliquet turpis.',
            'stock' => 25,
            'price' => 14.15,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Pliers')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'pliers01.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Pliers',
            'description' => 'Nunc vulputate, orci at congue faucibus, enim neque sodales nulla, nec imperdiet augue odio vel nibh. Etiam auctor, ligula quis gravida dictum, mi massa commodo ante, sollicitudin pulvinar nulla justo hendrerit lacus. Vivamus rutrum pharetra molestie. Fusce tristique odio tristique, elementum est eget, porttitor diam. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Maecenas at ligula sed sapien porta pretium. Aenean cursus, magna in blandit consectetur, libero orci aliquet eros, et maximus nunc est eu dolor. Aenean non pellentesque eros. In sodales orci eget orci fringilla, vitae feugiat elit porta. Etiam aliquam, mi pretium tempus mattis, mauris ipsum gravida risus, at tempor nulla ipsum molestie ligula. Ut placerat, urna sit amet tincidunt volutpat, ex orci luctus purus, nec laoreet dolor sapien vel erat.',
            'stock' => 25,
            'price' => 12.01,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Pliers')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'pliers02.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Bolt Cutters',
            'description' => 'Aliquam viverra scelerisque tempus. Ut vehicula, ex sed elementum rhoncus, sem neque vehicula turpis, sit amet accumsan mauris justo non magna. Cras ut vulputate lectus, sit amet sollicitudin enim. Quisque sit amet turpis ut orci pulvinar vestibulum non at velit. Quisque ultrices malesuada felis non rutrum. Sed molestie lobortis nisl, in varius arcu dictum vel. In sit amet fringilla orci. Quisque ac magna dui. Nam pulvinar nulla sed commodo ultricies. Suspendisse aliquet quis eros sit amet gravida. Aenean vitae arcu in sapien sodales commodo.',
            'stock' => 25,
            'price' => 48.41,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Pliers')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'pliers03.jpeg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Long Nose Pliers',
            'description' => 'Phasellus consequat fermentum quam id sodales. Curabitur nec dui orci. Fusce id turpis laoreet, lobortis ex non, finibus libero. Vivamus id enim eu nibh placerat maximus. Aenean semper dui a laoreet venenatis. Vestibulum at ligula quam. Donec interdum tristique neque lacinia laoreet. Sed auctor fermentum congue. Integer aliquet vulputate feugiat. Quisque malesuada diam iaculis ornare maximus. Mauris quam massa, sodales at mattis non, consectetur eget magna. Aliquam eget congue metus, sed congue leo. Nam sit amet est id ligula volutpat pharetra non id nisl. Vestibulum ac enim vitae nisi tempus cursus. Aliquam erat volutpat.',
            'stock' => 0,
            'price' => 14.24,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Pliers')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'pliers04.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Slip Joint Pliers',
            'description' => 'Ut cursus dui non ante convallis, facilisis auctor leo luctus. Maecenas a rhoncus metus. Sed in efficitur dolor, vulputate accumsan odio. Sed ex quam, dictum in fringilla at, vestibulum eu sem. Quisque ante orci, vulputate non porttitor eu, aliquet et nunc. Nunc a rhoncus dui. Nunc ac est non eros scelerisque maximus at a eros. Phasellus sed egestas diam, at tempus erat. Morbi sit amet congue tellus, at accumsan magna. Etiam non ornare nisl, sed luctus nisi. Pellentesque ut odio ut sapien aliquet eleifend.',
            'stock' => 25,
            'price' => 9.17,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Pliers')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'pliers05.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Claw Hammer with Shock Reduction Grip',
            'description' => 'Nam efficitur, turpis molestie bibendum lobortis, risus arcu congue tortor, id consequat nibh sem a libero. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Aenean non tincidunt tortor, vel ultricies tortor. Vivamus pulvinar efficitur arcu sit amet accumsan. Aenean dui erat, bibendum at dapibus in, feugiat non eros. Duis sodales felis ex, quis ullamcorper odio interdum non. Ut viverra magna eu augue luctus, quis convallis lectus auctor. Duis ultrices erat urna. Aliquam augue odio, mattis vitae finibus in, pellentesque nec dui. Curabitur nec odio in augue posuere posuere. Vivamus ante est, iaculis ut interdum et, hendrerit ac ante. Fusce ac venenatis neque, id fermentum nulla. Quisque tristique ornare nisi, vitae convallis dui faucibus quis. Vestibulum vel dapibus dolor.',
            'stock' => 25,
            'price' => 13.41,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hammer')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'hammer01.jpeg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Hammer',
            'description' => 'Mauris mollis odio est, ac vehicula dui lobortis vel. Cras facilisis, mauris ut vehicula dignissim, ex nunc sollicitudin velit, a fermentum mi odio ut massa. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent a sapien vel libero fermentum rhoncus. Etiam ac imperdiet arcu, ac accumsan risus. Fusce vitae lacinia sem, sit amet sagittis lorem. Curabitur efficitur ultricies sem, eu placerat ante tincidunt a. Morbi faucibus ullamcorper mi a mollis. Aenean sed magna aliquam, mollis dui at, condimentum ex. Donec blandit bibendum enim, lacinia vestibulum tellus laoreet sollicitudin. In vitae ullamcorper metus, ut interdum augue. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Nullam scelerisque dignissim varius. Nulla facilisi. Praesent eros dolor, ultricies sit amet pellentesque porttitor, pretium ut lectus. Nullam tempus tellus sapien, non condimentum mauris volutpat eget.',
            'stock' => 25,
            'price' => 12.58,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hammer')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'hammer02.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Claw Hammer',
            'description' => 'Cras pulvinar nisl a quam fringilla tempus. Sed lectus urna, mattis quis arcu eget, aliquam laoreet mauris. Praesent accumsan facilisis eros, ac mattis nulla interdum nec. Phasellus ultrices eu metus in lobortis. Donec ac efficitur orci. Phasellus nulla neque, congue nec tincidunt id, ultrices vel sapien. Curabitur gravida ex leo, laoreet mollis arcu blandit vel.',
            'stock' => 25,
            'price' => 11.48,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hammer')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'hammer03.jpeg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Thor Hammer',
            'description' => 'Donec malesuada tempus purus. Integer sit amet arcu magna. Sed vel laoreet ligula, non sollicitudin ex. Mauris euismod ac dolor venenatis lobortis. Aliquam iaculis at diam nec accumsan. Ut sodales sed elit et imperdiet. Maecenas vitae molestie mauris. Integer quis placerat libero, in finibus diam. Interdum et malesuada fames ac ante ipsum primis in faucibus.',
            'stock' => 25,
            'price' => 11.14,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hammer')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'hammer04.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Sledgehammer',
            'description' => 'Mauris sodales ligula vel mi sagittis, vel lobortis orci accumsan. Donec enim velit, lobortis sit amet dignissim vitae, molestie vitae libero. Curabitur ultrices interdum pellentesque. Nullam feugiat sagittis mi, sed hendrerit erat finibus cursus. Morbi mollis nulla ac metus posuere faucibus. Ut at purus turpis. Aliquam erat volutpat. Mauris euismod lacus pulvinar, aliquam erat non, laoreet turpis. Nunc sagittis tellus sed purus mattis lobortis. Vivamus venenatis ut lorem at finibus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Suspendisse mi felis, luctus tristique vulputate nec, auctor quis erat. Nulla eget massa vel massa bibendum vulputate. Interdum et malesuada fames ac ante ipsum primis in faucibus. Ut lacus felis, egestas ac hendrerit at, posuere venenatis quam.',
            'stock' => 25,
            'price' => 17.75,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hammer')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'hammer05.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Claw Hammer with Fiberglass Handle',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque quis elit ipsum. Maecenas eu tortor vel elit pharetra sodales. Praesent posuere odio mauris, id faucibus quam sollicitudin et. Suspendisse tristique sapien at mi blandit auctor. Aliquam ullamcorper, odio eget suscipit malesuada, velit magna pharetra diam, at pharetra enim lectus vel massa. Nulla vel lectus et quam feugiat interdum. Fusce hendrerit dignissim purus sed tincidunt. Cras eu ligula urna. Praesent laoreet ipsum ut dictum sodales. Proin sollicitudin imperdiet ante, suscipit dignissim nibh condimentum sagittis. Suspendisse placerat metus a enim fermentum imperdiet. In ut elementum lacus. Aliquam ut arcu a elit tempus tincidunt. Sed sem eros, ornare eu felis consequat, pulvinar consectetur urna. Etiam at cursus elit. Pellentesque aliquet, neque id viverra porta, purus arcu malesuada quam, at cursus ipsum ex nec erat.',
            'stock' => 25,
            'price' => 20.14,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hammer')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'hammer06.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Court Hammer',
            'description' => 'Interdum et malesuada fames ac ante ipsum primis in faucibus. Duis ut dictum dolor. Curabitur ligula ipsum, aliquet at nunc a, porta aliquam orci. Praesent tempor sagittis arcu et tempus. Donec porta odio et felis vulputate, ut semper mi varius. Integer bibendum nulla sed justo vehicula molestie. Quisque dictum magna sed metus maximus, non dignissim eros accumsan. Suspendisse eget augue maximus, rhoncus risus nec, sodales arcu. Sed sed egestas tortor. Proin quis cursus ligula. Integer finibus turpis eget dolor ultricies, non facilisis dolor sollicitudin. Aliquam hendrerit mollis scelerisque. Mauris interdum purus est, rutrum interdum nisi aliquam pellentesque. Cras eros eros, scelerisque quis ipsum nec, bibendum mollis mi.',
            'stock' => 25,
            'price' => 18.63,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hammer')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'hammer07.jpeg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Wood Saw',
            'description' => 'Quisque quis fermentum ligula. Aenean vulputate orci in ipsum varius, quis lacinia mauris eleifend. Quisque in turpis dapibus, consectetur nulla quis, semper felis. Nunc venenatis malesuada neque, ac rhoncus risus imperdiet eu. Proin eu eros at nibh blandit suscipit. Vestibulum condimentum nibh sit amet arcu congue, eu bibendum lorem venenatis. Nam molestie est at sem mollis porttitor. Aliquam eget purus sed lectus hendrerit finibus ac ac nunc. Nulla mi metus, euismod nec pharetra quis, porta vel nisl. Mauris euismod purus volutpat odio rhoncus, id elementum turpis rutrum. Integer semper, nunc vel pharetra mollis, magna magna euismod sem, ut scelerisque purus turpis eget lorem. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque pharetra lectus eget lorem fermentum, ac pulvinar nulla vulputate.',
            'stock' => 25,
            'price' => 12.18,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Hand Saw')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'saw01.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Adjustable Wrench',
            'description' => 'Praesent ullamcorper suscipit arcu eget interdum. Praesent bibendum molestie turpis eu lobortis. Curabitur feugiat lacus a pharetra volutpat. Vivamus et vehicula nulla. Donec vulputate mauris vitae pharetra maximus. Donec rhoncus dolor ante, in bibendum enim ultrices vitae. Curabitur ultricies, ex id aliquam sollicitudin, leo turpis posuere mauris, eget placerat ipsum nisi ac nunc. Pellentesque dapibus est id pellentesque maximus. Nunc consectetur, ex vel interdum ultricies, nisl dui cursus odio, a faucibus felis massa id massa.',
            'stock' => 25,
            'price' => 20.33,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Wrench')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'wrench01.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Angled Spanner',
            'description' => 'Cras convallis, eros vitae laoreet cursus, lorem elit fringilla felis, non tincidunt massa leo sit amet odio. In fringilla feugiat ipsum, in pretium erat faucibus sollicitudin. Curabitur leo ex, tincidunt vel luctus non, lobortis et ex. Praesent eget maximus metus. Mauris quis ex posuere, pretium nisl nec, lacinia velit. Pellentesque ut justo faucibus, vehicula nisi vel, egestas massa. In viverra lorem sed mi aliquam, sit amet pulvinar sem lacinia. Pellentesque convallis rutrum diam in pharetra. Ut nibh lectus, tristique ac ipsum et, eleifend tempus risus. Nam sollicitudin magna ut scelerisque porttitor. In eu accumsan purus. Nam suscipit lorem erat, a pellentesque enim commodo faucibus. Aenean id tincidunt lorem.',
            'stock' => 25,
            'price' => 14.14,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Wrench')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'wrench02.jpeg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Open-end Spanners (Set)',
            'description' => 'Donec sit amet auctor ex, non pellentesque nisl. Integer dignissim, sapien sit amet bibendum porttitor, justo elit lobortis erat, non hendrerit nisl nunc lobortis lacus. Pellentesque tempor metus felis, eu dapibus sem viverra vel. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Sed cursus molestie sem at feugiat. Nam suscipit, augue non dignissim molestie, arcu nunc suscipit risus, vitae vehicula risus tortor ut justo. Cras sed eros id orci laoreet ultricies. Phasellus lacinia euismod odio at sagittis. Nullam faucibus finibus vehicula. Aenean mauris risus, porttitor sodales consequat sed, lobortis nec massa. Duis quis ante ultrices, lacinia lacus nec, rhoncus libero. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nunc pellentesque quam sed ligula porttitor, id aliquam eros placerat.',
            'stock' => 25,
            'price' => 38.51,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Wrench')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'wrench03.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Phillips Screwdriver',
            'description' => 'Sed posuere libero diam, vel mollis nulla malesuada vel. In hac habitasse platea dictumst. Proin quis sollicitudin lectus. Cras et posuere nisi. Curabitur nibh felis, sagittis id vulputate a, tempus at diam. Vestibulum molestie quis nunc in tincidunt. Vestibulum vestibulum suscipit enim id molestie. Morbi vitae nibh nibh. Cras et orci aliquet, euismod lorem eu, tincidunt orci. Sed hendrerit mattis tellus vitae vestibulum. Donec posuere elit quis condimentum porta. Pellentesque sit amet pellentesque elit, tincidunt luctus lectus. Ut porta orci sed nisi condimentum, et egestas mauris facilisis. In mollis lectus sapien, a laoreet lorem tincidunt sed.',
            'stock' => 25,
            'price' => 4.92,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Screwdriver')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'screwdriver01.jpeg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Mini Screwdriver',
            'description' => 'Pellentesque tristique tortor id nulla accumsan, sit amet ultricies turpis elementum. Suspendisse quis vulputate felis. Maecenas a nibh augue. Pellentesque nec volutpat urna, quis finibus erat. Nulla leo velit, porttitor vel tortor ut, fermentum sagittis magna. Proin egestas velit quis volutpat vulputate. Aliquam id gravida orci. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Nunc in metus pretium, euismod ipsum sed, viverra enim. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Mauris lacinia, ligula vel ultrices commodo, quam turpis pharetra erat, id suscipit odio tortor eu urna. Morbi id dui eget augue tempor fermentum. Vestibulum iaculis lectus vel nisl ultricies ornare. Suspendisse ac risus a nibh condimentum mattis ac lacinia diam. Integer venenatis hendrerit turpis non blandit.',
            'stock' => 25,
            'price' => 13.96,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Screwdriver')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'screwdriver02.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Chisels Set',
            'description' => 'Praesent ornare, purus vitae volutpat consectetur, nulla arcu volutpat magna, ac ultrices purus risus ac purus. Aliquam quis massa erat. Etiam vulputate malesuada nunc, eu tempor felis pellentesque et. Aenean sodales neque non justo finibus sodales. Aenean varius imperdiet laoreet. Quisque feugiat interdum lobortis. Morbi dictum nisi tincidunt purus sollicitudin, a pellentesque erat interdum. Aliquam dignissim vel nisi non semper. Sed sagittis dignissim nulla sed porta. Pellentesque suscipit, erat vitae congue faucibus, dolor erat semper felis, sed maximus velit arcu et odio.',
            'stock' => 25,
            'price' => 12.96,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Chisels')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'chisels01.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Wood Carving Chisels',
            'description' => 'Fusce cursus cursus leo, nec pharetra turpis mattis porttitor. Ut fringilla, nisl a iaculis dictum, enim ante eleifend nisl, fermentum fringilla ligula nunc quis eros. Donec in magna quis ex placerat dapibus. Vivamus in iaculis elit. Nulla commodo tincidunt urna, sed gravida erat egestas in. Nunc mauris sapien, placerat vitae vehicula quis, sagittis a est. Morbi condimentum vitae enim in hendrerit. Suspendisse est magna, ullamcorper et porta id, auctor eu est. Nulla id magna vestibulum, porttitor tellus porta, maximus libero. Integer pulvinar mauris at posuere interdum. Nam vel ligula placerat, condimentum lacus quis, gravida eros. Curabitur sit amet erat ipsum. Sed ut suscipit sem, ac lacinia purus. Nam dignissim imperdiet dolor, quis efficitur nulla tincidunt non.',
            'stock' => 25,
            'price' => 45.23,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Chisels')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'chisels02.jpeg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Swiss Woodcarving Chisels',
            'description' => 'Aliquam hendrerit interdum quam sed aliquam. Donec finibus ligula nec nisi bibendum, et bibendum tortor pharetra. Pellentesque eu dui suscipit, maximus risus vitae, rutrum orci. Sed dolor quam, elementum eu erat vel, venenatis eleifend tortor. Sed posuere posuere efficitur. Nam sit amet tortor id metus bibendum lobortis et sit amet odio. Aenean ultricies sapien eget risus lacinia porttitor. Vivamus lorem elit, consequat in sem non, dapibus lacinia justo. Aliquam erat volutpat.',
            'stock' => 25,
            'price' => 22.96,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Chisels')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'chisels03.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Tape Measure 7.5m',
            'description' => 'Mauris commodo lacus eu ante mattis pulvinar. Curabitur porta nibh nec magna viverra, eu feugiat felis semper. Maecenas malesuada lacus a iaculis commodo. Maecenas mollis egestas urna, sed rutrum libero vehicula et. Suspendisse aliquam, lorem a imperdiet fringilla, nulla tortor consequat nibh, quis venenatis sapien dui quis ligula. Integer id nibh vestibulum, imperdiet tellus suscipit, laoreet libero. Pellentesque a nisi et neque vestibulum faucibus in ac leo. Etiam est tellus, feugiat in iaculis vitae, tincidunt sit amet tellus. Nam sit amet nunc justo. Pellentesque feugiat lorem vitae tellus ultrices ultrices. Pellentesque id posuere augue, eget imperdiet turpis. Phasellus nec finibus augue. Quisque tincidunt mi malesuada, placerat nunc ut, dictum nibh.',
            'stock' => 25,
            'price' => 7.23,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Measures')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'measure01.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Measuring Tape',
            'description' => 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Nunc eu orci orci. Mauris maximus ligula sed eros faucibus maximus. Aliquam vitae iaculis mi. In sit amet nulla gravida, egestas risus ut, condimentum dolor. Phasellus vulputate, ligula at eleifend congue, mauris justo lobortis justo, id mattis massa orci in quam. Fusce sit amet enim id tellus facilisis fermentum vitae eget velit. Quisque accumsan, libero ut ullamcorper aliquet, orci mauris imperdiet quam, quis ultricies elit elit quis arcu. Integer scelerisque nulla vel magna porttitor commodo.',
            'stock' => 25,
            'price' => 10.07,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Measures')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'measure02.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Tape Measure 5m',
            'description' => 'Aenean elementum, turpis sed lobortis commodo, arcu massa ullamcorper dolor, vel pretium leo tortor sit amet elit. Curabitur tellus nulla, fermentum vitae pretium in, feugiat vel tellus. Ut non porttitor elit, vel auctor est. Phasellus euismod eu lectus vel tristique. Etiam varius semper eros, a rhoncus erat dignissim vitae. In ut ultricies odio. Donec ultricies rhoncus ipsum, quis ullamcorper justo sodales nec. Integer vulputate facilisis nibh et tincidunt. Aliquam ac tortor augue. Aenean eu nisl rutrum, varius felis rhoncus, blandit justo. Sed gravida, arcu a venenatis dictum, lorem lacus commodo diam, vel pulvinar diam leo nec tellus.',
            'stock' => 25,
            'price' => 12.91,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Measures')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'measure03.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Square Ruler',
            'description' => 'In elementum arcu vel pellentesque dignissim. Praesent est nisl, eleifend a mi at, tempor vehicula libero. Etiam sed tortor luctus, fringilla leo sed, dignissim dui. Integer interdum nunc ac mauris fermentum, nec dapibus ex facilisis. Proin eleifend blandit ipsum. Cras iaculis eget sem ut ornare. Morbi eleifend elementum elit quis ornare. Integer a nulla sit amet dui varius auctor scelerisque at lectus. Donec vitae ipsum ullamcorper, rutrum ex vel, suscipit nisi. Suspendisse potenti. Cras condimentum erat vel imperdiet gravida.',
            'stock' => 25,
            'price' => 15.75,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Measures')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'measure04.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Safety Goggles',
            'description' => 'Sed urna lectus, commodo ut dolor finibus, condimentum posuere purus. Phasellus faucibus, ex in congue mattis, purus ipsum tristique sem, vitae elementum arcu metus nec lacus. Fusce convallis tellus ornare metus hendrerit pellentesque. In porta leo in iaculis eleifend. Duis in diam ut ante finibus condimentum non eget massa. Suspendisse potenti. Donec justo ligula, scelerisque ac facilisis eu, sollicitudin eu lectus. Mauris hendrerit fermentum sapien.',
            'stock' => 25,
            'price' => 24.26,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Safety Gear')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'goggles01.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Safety Helmet Face Shield',
            'description' => 'Quisque nisi lectus, laoreet in enim maximus, auctor porta nunc. Vivamus in dui condimentum, aliquam mi cursus, scelerisque nunc. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Ut facilisis quam sit amet enim commodo ultrices. Quisque ac ante vehicula quam aliquet semper. Phasellus fermentum orci nec dui accumsan, non tincidunt ante facilisis. Ut laoreet blandit euismod.',
            'stock' => 25,
            'price' => 35.62,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Safety Gear')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'goggles02.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Protective Gloves',
            'description' => 'Vivamus vitae nisl vel felis condimentum condimentum. Morbi molestie placerat fermentum. Morbi id dictum ex. Vestibulum ut ex convallis, imperdiet enim bibendum, dictum nulla. Phasellus porttitor molestie est ac tincidunt. Praesent in dolor finibus metus hendrerit sodales non non tellus. Mauris odio justo, tincidunt nec commodo in, semper id ante.',
            'stock' => 25,
            'price' => 21.42,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Safety Gear')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'gloves01.jpeg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Super-thin Protection Gloves',
            'description' => 'Quisque quis egestas lacus, vel lacinia tortor. Phasellus consectetur enim ut est luctus scelerisque. Morbi vestibulum, dui nec vehicula pharetra, lectus erat vulputate magna, at placerat arcu diam id leo. In accumsan elit orci, ut eleifend ligula semper eu. Maecenas faucibus egestas fermentum. Donec et elit nec est facilisis ornare placerat ut ipsum. Praesent facilisis enim a massa semper cursus. Etiam vitae lectus malesuada nulla sagittis rutrum. Nulla facilisi. Duis aliquet diam iaculis dui laoreet, in posuere orci facilisis. Vestibulum mollis lorem a ex tempor, nec posuere nisi convallis.',
            'stock' => 25,
            'price' => 38.45,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Safety Gear')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'gloves02.jpeg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Construction Helmet',
            'description' => 'Integer lobortis diam tellus. Sed blandit felis non nisl pellentesque ornare. In hendrerit ante vel leo sodales, in hendrerit urna ultrices. Nunc fermentum vitae tellus id convallis. Praesent vulputate ligula vitae luctus mattis. Donec vestibulum urna sed hendrerit posuere. Praesent vel erat ex. Sed sagittis eros eu dui pulvinar, vel varius velit consectetur. Morbi dignissim mattis neque. Donec rhoncus tempor ornare. Suspendisse congue nulla nec fringilla ultricies. Vestibulum hendrerit, risus vel venenatis vulputate, neque sem dapibus velit, ut tristique libero velit consequat augue.',
            'stock' => 25,
            'price' => 41.29,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Safety Gear')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'helmet01.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Ear Protection',
            'description' => 'Nullam egestas nunc at massa ullamcorper euismod. Phasellus ac feugiat dui. Ut et mauris id turpis maximus molestie ac in nunc. Cras eleifend auctor libero sed tempor. Phasellus varius, neque ut egestas fringilla, libero purus feugiat eros, et viverra leo dui a diam. Sed facilisis et nibh eu congue. Praesent consequat feugiat neque ac porta. Morbi et viverra urna. Curabitur sit amet mauris vel velit ultricies sollicitudin et eu leo. Mauris et ullamcorper ex.',
            'stock' => 25,
            'price' => 18.58,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Safety Gear')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'earprotection01.jpeg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Screws',
            'description' => 'Integer nec orci a est vulputate maximus. Vivamus commodo, mauris at convallis ultrices, massa neque porttitor mauris, eget tristique magna sapien nec orci. Sed faucibus metus ac leo sollicitudin, ac placerat tellus malesuada. Aenean quis urna feugiat, interdum mi in, feugiat metus. Etiam tincidunt, mauris a fermentum malesuada, felis mauris laoreet lectus, quis hendrerit nisi urna a ex. Morbi vitae lacus consectetur, vestibulum nibh vel, aliquet est. Nam vitae nisi cursus, pulvinar turpis ac, egestas elit. Phasellus volutpat augue nec augue luctus, eu pulvinar nisl porta. Duis nec nisl fringilla, posuere nulla ut, aliquet nunc. Quisque ac pulvinar nunc. In varius eleifend lectus sed gravida. Duis luctus aliquam condimentum. Fusce rhoncus varius elit, a mollis dolor accumsan vitae.',
            'stock' => 25,
            'price' => 6.25,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Fasteners')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'fasteners01.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Nuts and bolts',
            'description' => 'Phasellus ex dolor, commodo sed lacus vitae, scelerisque dapibus neque. Sed nec arcu sit amet tortor pretium tempor sit amet nec mauris. Ut ultrices magna vitae tortor pharetra pellentesque. Cras auctor vulputate ex, sit amet faucibus ligula volutpat ac. Fusce ultrices malesuada iaculis. Vivamus sit amet orci nibh. Nunc id imperdiet est. Nulla feugiat semper aliquet. Aliquam cursus ex sem, sit amet porttitor enim elementum eu.',
            'stock' => 25,
            'price' => 5.55,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Fasteners')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'fasteners02.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Cross-head screws',
            'description' => 'Sed cursus nibh eros, nec placerat diam vestibulum sed. Nulla at dapibus mauris. Sed a ornare lorem, nec tempus lacus. Donec a sem molestie, aliquam est eu, mollis urna. Vestibulum sed lacinia urna. Quisque porta justo eget ex cursus, et eleifend mi lobortis. Donec ornare velit elit, vel dapibus quam malesuada nec. Mauris egestas ligula urna, eu lacinia nulla scelerisque a. Suspendisse sit amet congue justo, quis luctus mi.',
            'stock' => 25,
            'price' => 7.99,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Fasteners')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'fasteners03.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Flat-Head Wood Screws',
            'description' => 'Nullam ac ligula sagittis, dapibus tortor at, hendrerit leo. Nam tempus lorem turpis. Vivamus laoreet odio non fringilla viverra. Curabitur ac dapibus massa. Nulla in augue velit. Nunc feugiat semper commodo. Maecenas venenatis tellus nunc, ac venenatis tortor tristique id. Aenean sed eros placerat, consectetur nisl in, placerat neque. Nullam gravida elit eu porta eleifend. Mauris at tincidunt libero. Sed vel lorem diam. Cras dui mauris, viverra sed imperdiet ac, congue varius dolor. Nunc id risus erat. Vestibulum eu libero eros.',
            'stock' => 25,
            'price' => 3.95,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Fasteners')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'fasteners04.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'M4 Nuts',
            'description' => 'Maecenas viverra et risus non accumsan. Nullam sodales, leo ac hendrerit fermentum, nulla metus mattis sem, eget sagittis dolor nibh ac nisi. In nec gravida ex. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Morbi non pretium erat. Sed ac congue nisl. Aenean egestas ac lorem sit amet facilisis. Nullam porta velit non lorem pulvinar, a laoreet orci posuere. Vestibulum sed tellus porta, aliquet magna ut, tincidunt lectus. Morbi ac quam sed nisi placerat tempus. Nam ultrices dignissim lobortis. Nam ut erat pretium, imperdiet sem vel, volutpat magna. Nam luctus volutpat aliquam.',
            'stock' => 25,
            'price' => 4.65,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Fasteners')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'fasteners05.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Washers',
            'description' => 'Nullam aliquam a odio tristique facilisis. Maecenas rhoncus eleifend nulla, in laoreet felis convallis vel. Praesent ultricies tincidunt arcu. Nam eleifend consequat tempor. Aenean consectetur semper quam, ac gravida odio scelerisque pharetra. Nam eu sem non mi hendrerit semper nec nec mauris. Praesent at metus nec eros egestas dictum. Quisque quis massa diam. Aenean consequat sem at lorem vestibulum, et volutpat sem imperdiet. Nulla facilisi.',
            'stock' => 25,
            'price' => 3.55,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Fasteners')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'fasteners06.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Drawer Tool Cabinet',
            'description' => 'Curabitur gravida scelerisque mauris maximus fringilla. Maecenas id velit ac ligula porta aliquet at lobortis nunc. Pellentesque euismod volutpat libero. Duis molestie tincidunt hendrerit. Sed luctus, ipsum eget volutpat consequat, dui nibh dictum quam, in ullamcorper nisi justo auctor massa. Sed eu neque arcu. Duis eu ullamcorper risus. Etiam feugiat erat in condimentum ullamcorper.',
            'stock' => 25,
            'price' => 89.55,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Storage Solutions')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'toolcabinet02.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Tool Cabinet',
            'description' => 'Curabitur eu dictum eros. Integer ante neque, viverra ut dapibus eget, scelerisque et erat. Vivamus cursus laoreet dolor, id aliquet diam vulputate at. Sed mi magna, feugiat nec finibus porttitor, rhoncus ac ante. Interdum et malesuada fames ac ante ipsum primis in faucibus. Duis ligula risus, convallis vitae pulvinar vitae, porttitor vitae purus. Ut eget placerat nisi.',
            'stock' => 25,
            'price' => 86.71,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Storage Solutions')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'toolcabinet01.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Workbench with Drawers',
            'description' => 'Nulla vitae lacus risus. Phasellus accumsan mi nec consectetur euismod. Donec a finibus quam. Proin id tempus tellus. Etiam vel lectus vel nunc convallis commodo. In massa ante, hendrerit eu ultricies ac, sagittis quis urna. Aliquam porttitor varius mattis. Etiam ac auctor risus, vitae pellentesque enim. Donec convallis est vel tellus dictum rhoncus. Vestibulum suscipit mollis diam, vehicula facilisis libero egestas sed. Mauris at urna aliquet, iaculis dui et, aliquam nunc.',
            'stock' => 25,
            'price' => 178.20,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Workbench')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'workbench02.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Wooden Workbench',
            'description' => 'Suspendisse non felis urna. Donec iaculis feugiat enim ac dignissim. Suspendisse vel hendrerit eros. Morbi in posuere quam. Aliquam ipsum felis, sollicitudin id magna et, facilisis bibendum metus. Donec egestas tincidunt orci, vitae dignissim nibh rutrum eu. Proin sit amet dui ut tortor luctus luctus at a velit. Vivamus facilisis orci id gravida aliquam. Nam tempus vehicula sem, nec semper ex tincidunt in. Pellentesque tellus est, sodales sit amet condimentum ac, ullamcorper sed lorem. Nullam egestas ac libero nec imperdiet.',
            'stock' => 25,
            'price' => 172.52,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Workbench')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'workbench01.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Leather toolbelt',
            'description' => 'Aliquam molestie congue efficitur. Nunc molestie malesuada eleifend. Vestibulum non accumsan magna, at lacinia nunc. In laoreet vitae nisi ut feugiat. Quisque ac felis nec turpis scelerisque venenatis. Donec sed sollicitudin massa. Suspendisse commodo pharetra ex nec rhoncus. Proin ut porttitor ligula, eget fringilla risus. Quisque a lectus quis velit luctus congue non eget diam.',
            'stock' => 25,
            'price' => 61.16,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Tool Belts')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'toolbelt01.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Sheet Sander',
            'description' => 'Suspendisse vel pellentesque tellus, ut lobortis risus. Praesent sit amet feugiat felis, vel vehicula sapien. Quisque aliquet purus ut mi sagittis hendrerit. Vivamus pharetra massa non massa varius hendrerit. In dictum lectus ac volutpat congue. Donec sed lorem nec justo ullamcorper egestas nec id nibh. Mauris condimentum odio ut dui congue, sit amet blandit felis varius. Vestibulum iaculis varius lorem non venenatis.',
            'stock' => 25,
            'price' => 58.48,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Sander')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'sander01.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Belt Sander',
            'description' => 'Nulla id consequat libero. Proin porta purus quis nulla condimentum, ac pharetra nunc tempor. Nulla sit amet volutpat quam. Aliquam suscipit non nisi quis facilisis. Integer vel urna non ante porttitor laoreet. Duis vitae justo enim. Vivamus sagittis libero et placerat malesuada. Integer non eros lectus. Aenean hendrerit nisi et vulputate vehicula. Proin euismod quis nunc sit amet mattis.',
            'stock' => 25,
            'price' => 73.59,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Sander')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'sander02.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Circular Saw',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc ut fermentum tortor. Donec dolor urna, cursus sed scelerisque a, pulvinar eu metus. Vestibulum consectetur neque at diam laoreet, quis pharetra lacus auctor. Vestibulum volutpat porttitor lectus et hendrerit. Sed aliquet ultricies lobortis. Integer quis felis vel orci porta vehicula. Suspendisse potenti. Nullam nec ligula sed urna fermentum efficitur ac quis turpis. Aliquam ac molestie dui. Integer eleifend mattis metus id tristique. Donec aliquet erat vehicula dolor faucibus porta. Vestibulum est leo, bibendum in luctus non, tristique pellentesque lectus. Nam sit amet convallis est. Nam molestie finibus eros vitae facilisis.',
            'stock' => 25,
            'price' => 80.19,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Saw')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'saw02.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Random Orbit Sander',
            'description' => 'Sed nec luctus sem. Nunc et nibh massa. Curabitur laoreet, ligula ut aliquam blandit, turpis nibh scelerisque tortor, sit amet scelerisque ligula odio at neque. Nulla facilisi. Etiam ac dui sit amet est dapibus lacinia in quis odio. Cras pharetra tempor tincidunt. Donec dignissim dignissim urna a euismod. Integer non eros non turpis pharetra laoreet sed non mi.',
            'stock' => 25,
            'price' => 100.79,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Sander')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'sander03.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Cordless Drill 20V',
            'description' => 'Nullam id lorem hendrerit, sodales magna eleifend, maximus lectus. Nulla volutpat dolor id dolor blandit dictum. Duis at accumsan risus. Etiam tempor sem at purus maximus, quis faucibus enim iaculis. Pellentesque viverra est ipsum, sed bibendum enim cursus sed. Donec quis sapien ex. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam vitae arcu nulla. Aliquam porttitor purus est, non dignissim sapien rhoncus in. In lacus nisl, fringilla sit amet consequat eget, consequat vel purus. Phasellus fringilla, sapien dignissim fringilla viverra, ex nulla faucibus augue, id lobortis nulla diam vitae nisl.',
            'stock' => 25,
            'price' => 125.23,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Drill')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'drill01.jpeg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Cordless Drill 24V',
            'description' => 'Aenean vel dolor eu erat rutrum dapibus. Aenean consectetur velit in quam pulvinar volutpat. Etiam at laoreet augue. Sed sed diam venenatis, pharetra quam a, consectetur massa. Vivamus purus enim, placerat non augue eu, viverra sagittis purus. Sed dictum massa ac orci posuere, pulvinar dignissim est mattis. Curabitur at convallis ipsum. Donec id arcu vel massa tincidunt porta. Nullam quis accumsan mauris.',
            'stock' => 25,
            'price' => 66.54,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Drill')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'drill02.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Cordless Drill 18V',
            'description' => 'Maecenas sed maximus dolor, et interdum odio. Nullam et tellus elit. Sed nec sollicitudin nibh. Nam sed turpis at dui hendrerit auctor. Vestibulum vel ultricies lacus. In augue est, porta quis tempus in, feugiat eget elit. Donec luctus turpis id elit bibendum, in accumsan felis luctus. Sed condimentum id nisi sed posuere. Quisque sed sagittis sapien. Aenean aliquet quis nunc nec venenatis. Fusce sed metus justo.',
            'stock' => 25,
            'price' => 119.24,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Drill')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'drill03.jpeg')->first()->id,
            'is_location_offer' => false,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Cordless Drill 12V',
            'description' => 'Sed viverra nec massa eget imperdiet. In hac habitasse platea dictumst. Donec interdum magna vitae lectus commodo scelerisque. Mauris non placerat sem. Vivamus sodales vel nibh facilisis pellentesque. Ut ut egestas mi. Sed quis tellus ut dolor aliquam semper sed quis arcu. Vivamus mi ipsum, rutrum id varius sit amet, tempus at nibh. Sed consequat mauris nulla, id sodales sapien malesuada a. Nulla id justo eget justo pharetra lacinia. Vestibulum ultrices, lacus sed pellentesque interdum, augue libero posuere lorem, et vehicula orci odio et nulla.',
            'stock' => 25,
            'price' => 46.50,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Drill')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'drill04.jpeg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => false
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Excavator',
            'description' => 'Aliquam tempus consequat ligula rutrum consequat. Pellentesque quis felis bibendum nunc facilisis vehicula et sed nunc. Pellentesque condimentum at ex id auctor. Suspendisse potenti. Etiam ac finibus lectus. Vestibulum vestibulum nunc dapibus odio tempus iaculis et molestie ex. Etiam scelerisque ipsum justo, sit amet tempor nibh auctor at. Praesent gravida pulvinar urna id congue.',
            'stock' => null,
            'price' => 136.50,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Drill')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'excavator01.jpg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => true
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Bulldozer',
            'description' => 'Maecenas varius suscipit rutrum. Quisque vel egestas mi. Cras gravida vitae ipsum non placerat. Nulla facilisi. Integer vehicula faucibus sollicitudin. Duis volutpat eros in urna interdum, congue luctus libero convallis. Integer finibus, eros in suscipit porttitor, urna libero imperdiet erat, a aliquet leo nibh sit amet nisi.',
            'stock' => null,
            'price' => 147.50,
            'brand_id' => DB::table('brands')->where('name', '=', 'ForgeFlex Tools')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Drill')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'bulldozer01.jpg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => true
        ], [
            'id' => Str::ulid()->toBase32(),
            'name' => 'Crane',
            'description' => 'Donec lacus nulla, posuere id nibh convallis, lobortis sollicitudin sem. Nulla facilisi. Praesent quis dui quis dolor tincidunt vehicula. Nam venenatis consequat massa, vel vulputate erat molestie in. Donec nec risus lacus. Donec ac convallis odio, at feugiat ipsum. Integer ex enim, porttitor vel ligula non, vestibulum consectetur orci. Morbi ac pharetra lorem, sit amet vulputate odio.',
            'stock' => null,
            'price' => 153.50,
            'brand_id' => DB::table('brands')->where('name', '=', 'MightyCraft Hardware')->first()->id,
            'category_id' => DB::table('categories')->where('name', '=', 'Drill')->first()->id,
            'product_image_id' => DB::table('product_images')->where('file_name', '=', 'crane01.jpg')->first()->id,
            'is_location_offer' => true,
            'is_rental' => true
        ]
        ]);

    }
}
