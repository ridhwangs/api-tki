<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToLogOperatorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_operator', function (Blueprint $table) {
            $table->foreign(['shift_id'], 'log_operator_ibfk_3')->references(['shift_id'])->on('shift')->onDelete('CASCADE');
            $table->foreign(['operator_id'], 'log_operator_ibfk_4')->references(['operator_id'])->on('operator')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_operator', function (Blueprint $table) {
            $table->dropForeign('log_operator_ibfk_3');
            $table->dropForeign('log_operator_ibfk_4');
        });
    }
}
