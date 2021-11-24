<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTarifProgressiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tarif_progressive', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('kendaraan_id')->index('kendaraan_id');
            $table->integer('tarif_1')->nullable();
            $table->integer('tarif_2')->nullable();
            $table->integer('tarif_3')->nullable();
            $table->integer('tarif_4')->nullable();
            $table->integer('tarif_5')->nullable();
            $table->string('api_key', 256)->nullable()->index('api_key');
            $table->string('created_by', 32);
            $table->dateTime('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tarif_progressive');
    }
}
