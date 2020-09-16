<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePharmaciesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

     // Migration to store pharmacies coming from 'pharmacies.json'
    public function up()
    {
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();
            $table->string("name", 200);
            $table->string("address", 500);
            $table->string("city", 100);
            $table->char("state", 2);
            $table->string("zip", 10);
            $table->string("latitude", 20);
            $table->string("longitude", 20);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pharmacies');
    }
}
