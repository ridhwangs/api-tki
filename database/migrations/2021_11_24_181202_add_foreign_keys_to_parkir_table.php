<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToParkirTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parkir', function (Blueprint $table) {
            $table->foreign(['rfid'], 'parkir_ibfk_8')->references(['rfid'])->on('member');
            $table->foreign(['operator_id'], 'parkir_ibfk_10')->references(['operator_id'])->on('operator');
            $table->foreign(['shift_id'], 'parkir_ibfk_12')->references(['shift_id'])->on('shift');
            $table->foreign(['kendaraan_id'], 'parkir_ibfk_11')->references(['kendaraan_id'])->on('kendaraan');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parkir', function (Blueprint $table) {
            $table->dropForeign('parkir_ibfk_8');
            $table->dropForeign('parkir_ibfk_10');
            $table->dropForeign('parkir_ibfk_12');
            $table->dropForeign('parkir_ibfk_11');
        });
    }
}
