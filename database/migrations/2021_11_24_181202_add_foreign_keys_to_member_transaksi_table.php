<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToMemberTransaksiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_transaksi', function (Blueprint $table) {
            $table->foreign(['rfid'], 'member_transaksi_ibfk_2')->references(['rfid'])->on('member')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_transaksi', function (Blueprint $table) {
            $table->dropForeign('member_transaksi_ibfk_2');
        });
    }
}
