<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToParkirBiayaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parkir_biaya', function (Blueprint $table) {
            $table->foreign(['barcode_id'], 'parkir_biaya_ibfk_5')->references(['barcode_id'])->on('parkir')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parkir_biaya', function (Blueprint $table) {
            $table->dropForeign('parkir_biaya_ibfk_5');
        });
    }
}
