<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTarifEventTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tarif_event', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('kendaraan_id');
            $table->integer('tarif_persent')->nullable();
            $table->integer('tarif_rp')->nullable();
            $table->date('tgl_awal')->nullable();
            $table->date('tgl_akhir')->nullable();
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
        Schema::dropIfExists('tarif_event');
    }
}
