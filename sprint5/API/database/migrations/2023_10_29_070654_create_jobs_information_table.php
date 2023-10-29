<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('jobs_information', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->dateTime('start_time');
            $table->dateTime('end_time')->nullable();
            $table->unsignedBigInteger('duration_ms')->nullable();
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
        Schema::dropIfExists('jobs_information');
    }
};
