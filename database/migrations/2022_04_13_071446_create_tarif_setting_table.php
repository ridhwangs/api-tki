<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTarifSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tarif_setting', function (Blueprint $table) {
            $table->string('api_key', 256)->primary();
            $table->enum('tarif_berlaku', ['flat', 'progressive']);
            $table->string('created_by', 32);
            $table->dateTime('created_at');
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
        Schema::dropIfExists('tarif_setting');
    }
}
