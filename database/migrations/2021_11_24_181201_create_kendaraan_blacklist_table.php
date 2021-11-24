<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKendaraanBlacklistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kendaraan_blacklist', function (Blueprint $table) {
            $table->integer('blacklist_id', true);
            $table->string('no_kend', 32);
            $table->enum('status', ['aktif', 'pasif']);
            $table->enum('kategori', ['roda_dua', 'roda_empat']);
            $table->string('merk', 32);
            $table->string('warna', 32);
            $table->string('tipe', 32);
            $table->text('keterangan');
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
        Schema::dropIfExists('kendaraan_blacklist');
    }
}
