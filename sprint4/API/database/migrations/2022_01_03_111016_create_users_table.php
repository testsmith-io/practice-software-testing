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
            $table->id();
            $table->string('first_name', 40);
            $table->string('last_name', 20);
            $table->string('address', 70);
            $table->string('city', 40);
            $table->string('state', 40)->nullable();
            $table->string('country', 40);
            $table->string('postcode', 10)->nullable();
            $table->string('phone', 24)->nullable();
            $table->date('dob');
            $table->string('email', 60)->unique();
            $table->string('password')->nullable();
            $table->string('role');
            $table->timestamps();
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
