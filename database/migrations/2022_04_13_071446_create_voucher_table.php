<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoucherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('voucher', function (Blueprint $table) {
            $table->integer('voucher_id', true);
            $table->string('barcode_id', 12);
            $table->string('kode_toko', 64);
            $table->string('key', 64);
            $table->integer('nominal');
            $table->integer('potongan');
            $table->date('tgl_awal');
            $table->date('tgl_akhir');
            $table->integer('penggunaan');
            $table->string('created_by', 64);
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
        Schema::dropIfExists('voucher');
    }
}
