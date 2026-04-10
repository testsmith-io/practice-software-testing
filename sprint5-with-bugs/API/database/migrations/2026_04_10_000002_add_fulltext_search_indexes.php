<?php
// Copyright (c) 2024-2026 Testsmith. All rights reserved.
// See LICENSE for details.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up() {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }
        DB::statement('ALTER TABLE products ADD FULLTEXT idx_products_ft_name (name)');
        DB::statement('ALTER TABLE products ADD FULLTEXT idx_products_ft_name_desc (name, description)');
        DB::statement('ALTER TABLE brands ADD FULLTEXT idx_brands_ft_name (name)');
        DB::statement('ALTER TABLE categories ADD FULLTEXT idx_categories_ft_name (name)');
        DB::statement('ALTER TABLE users ADD FULLTEXT idx_users_ft (first_name, last_name, email, city)');
    }
    public function down() {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }
        DB::statement('ALTER TABLE products DROP INDEX idx_products_ft_name');
        DB::statement('ALTER TABLE products DROP INDEX idx_products_ft_name_desc');
        DB::statement('ALTER TABLE brands DROP INDEX idx_brands_ft_name');
        DB::statement('ALTER TABLE categories DROP INDEX idx_categories_ft_name');
        DB::statement('ALTER TABLE users DROP INDEX idx_users_ft');
    }
};
