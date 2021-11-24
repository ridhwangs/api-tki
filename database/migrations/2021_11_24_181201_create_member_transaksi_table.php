<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberTransaksiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_transaksi', function (Blueprint $table) {
            $table->integer('topup_id', true);
            $table->string('rfid', 11)->index('rfid');
            $table->integer('jumlah');
            $table->integer('hari');
            $table->enum('jenis', ['topup', 'keluar']);
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
        Schema::dropIfExists('member_transaksi');
    }
}
