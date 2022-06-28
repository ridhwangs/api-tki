<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoveParkirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('move_parkir', function (Blueprint $table) {
            $table->integer('parkir_id', true);
            $table->string('no_ticket', 32)->index('no_ticket');
            $table->string('barcode_id', 32);
            $table->string('rfid', 128)->nullable();
            $table->string('image_in', 256)->nullable();
            $table->string('image_out', 256)->nullable();
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->enum('kategori', ['member', 'roda_dua', 'roda_empat']);
            $table->integer('kendaraan_id')->nullable();
            $table->enum('kategori_update', ['member', 'roda_dua', 'roda_empat'])->nullable();
            $table->string('no_kend', 32)->nullable();
            $table->integer('tarif')->nullable();
            $table->integer('bayar')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status', ['masuk', 'keluar', 'expired'])->default('masuk');
            $table->integer('operator_id')->nullable();
            $table->integer('shift_id')->nullable();
            $table->string('created_by', 32)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('move_parkir');
    }
}
