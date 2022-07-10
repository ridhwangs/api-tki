<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogOperatorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_operator', function (Blueprint $table) {
            $table->integer('log_operator_id', true);
            $table->integer('operator_id')->index('operator_id');
            $table->integer('shift_id')->index('shift_id');
            $table->string('keterangan', 32);
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
        Schema::dropIfExists('log_operator');
    }
}
