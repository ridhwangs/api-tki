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
            $table->string('rfid', 11);
            $table->integer('jumlah');
            $table->integer('hari');
            $table->enum('jenis', ['topup', 'keluar']);
            $table->enum('status', ['open', 'approve', 'rejected']);
            $table->integer('operator_id');
            $table->integer('shift_id');
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
