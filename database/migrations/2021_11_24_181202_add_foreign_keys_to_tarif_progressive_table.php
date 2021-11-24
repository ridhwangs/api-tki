<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTarifProgressiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tarif_progressive', function (Blueprint $table) {
            $table->foreign(['kendaraan_id'], 'tarif_progressive_ibfk_1')->references(['kendaraan_id'])->on('kendaraan')->onDelete('CASCADE');
            $table->foreign(['api_key'], 'tarif_progressive_ibfk_2')->references(['api_key'])->on('tarif_setting')->onUpdate('NO ACTION')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tarif_progressive', function (Blueprint $table) {
            $table->dropForeign('tarif_progressive_ibfk_1');
            $table->dropForeign('tarif_progressive_ibfk_2');
        });
    }
}
