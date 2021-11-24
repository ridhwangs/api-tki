<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDataKendaraanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_kendaraan', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('barcode_id', 32)->index('barcode_id');
            $table->string('no_kend', 12);
            $table->string('nama_pemilik', 64);
            $table->string('alamat', 128);
            $table->string('merk', 32);
            $table->string('warna', 32);
            $table->string('tipe', 32);
            $table->string('no_rangka', 64);
            $table->string('no_mesin', 32);
            $table->string('no_bpkb', 32);
            $table->string('keterangan', 32);
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
        Schema::dropIfExists('data_kendaraan');
    }
}
