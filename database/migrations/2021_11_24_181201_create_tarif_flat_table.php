<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTarifFlatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tarif_flat', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('kendaraan_id')->index('kendaraan_id');
            $table->integer('tarif')->nullable();
            $table->string('api_key', 256)->nullable()->index('api_key');
            $table->string('created_by', 32);
            $table->dateTime('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tarif_flat');
    }
}
