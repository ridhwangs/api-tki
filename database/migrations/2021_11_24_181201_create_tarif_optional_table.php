<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTarifOptionalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tarif_optional', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('kendaraan_id')->index('kendaraan_id');
            $table->integer('tarif_inap')->nullable();
            $table->integer('tarif_vallet')->nullable();
            $table->integer('tarif_hilang')->nullable();
            $table->integer('menit_toleransi')->nullable();
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
        Schema::dropIfExists('tarif_optional');
    }
}
