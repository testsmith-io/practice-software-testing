<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', 220);
            $table->text('description')->nullable();
            $table->integer('stock')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_location_offer');
            $table->boolean('is_rental');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->foreignUlid('brand_id')->references('id')->on('brands');
            $table->foreignUlid('category_id')->references('id')->on('categories');
            $table->foreignUlid('product_image_id')->nullable()->references('id')->on('product_images');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
