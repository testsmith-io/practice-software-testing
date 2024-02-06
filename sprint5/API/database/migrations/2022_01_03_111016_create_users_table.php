<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('uid')->nullable();
            $table->string('provider')->nullable();
            $table->string('first_name', 40);
            $table->string('last_name', 20);
            $table->string('address', 70)->nullable();
            $table->string('city', 40)->nullable();
            $table->string('state', 40)->nullable();
            $table->string('country', 40)->nullable();
            $table->string('postcode', 10)->nullable();
            $table->string('phone', 24)->nullable();
            $table->date('dob')->nullable();
            $table->string('email', 256)->unique();
            $table->string('password')->nullable();
            $table->string('role');
            $table->boolean('enabled')->default(true);
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
