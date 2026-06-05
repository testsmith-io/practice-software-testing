<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            // Composite index for the most common filter combination on the
            // product overview page (category, brand, rental flag).
            $table->index(
                ['category_id', 'brand_id', 'is_rental'],
                'idx_products_category_brand_rental'
            );

            // Index used by the eco-friendly filter and CO2 sorting.
            $table->index('co2_rating', 'idx_products_co2_rating');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_category_brand_rental');
            $table->dropIndex('idx_products_co2_rating');
        });
    }
};
