<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMasterGateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('master_gate', function (Blueprint $table) {
            $table->integer('gate_id', true);
            $table->string('nama', 64);
            $table->integer('delay');
            $table->string('nama_printer', 32);
            $table->integer('kuota');
            $table->integer('default_kuota');
            $table->integer('arduino_com');
            $table->string('api_key', 128);
            $table->string('ip_address', 16);
            $table->string('text_title', 256);
            $table->string('text_caption', 256);
            $table->string('text_footer', 256);
            $table->string('text_end', 256);
            $table->string('version', 256);
            $table->string('created_by', 32);
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
        Schema::dropIfExists('master_gate');
    }
}
