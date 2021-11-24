<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperatorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operator', function (Blueprint $table) {
            $table->integer('operator_id', true);
            $table->string('username', 32);
            $table->string('password', 32);
            $table->string('nama', 128);
            $table->string('email', 32);
            $table->text('alamat');
            $table->string('no_telp', 32);
            $table->integer('status')->default(1);
            $table->enum('level', ['easy', 'medium', 'hard'])->default('easy');
            $table->string('created_by', 32);
            $table->date('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operator');
    }
}
