<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member', function (Blueprint $table) {
            $table->integer('member_id', true);
            $table->string('rfid', 11)->unique('rfid');
            $table->string('nama', 64)->nullable();
            $table->text('alamat')->nullable();
            $table->string('tempat', 32)->nullable();
            $table->date('tgl_lahir')->nullable();
            $table->string('email', 128)->nullable();
            $table->string('no_telp', 32)->nullable();
            $table->string('no_hp', 32)->nullable();
            $table->string('id_line', 64)->nullable();
            $table->string('jenis_identitas', 12)->nullable();
            $table->string('no_identitas', 64)->nullable();
            $table->integer('kendaraan_id')->nullable()->index('kendaraan_id');
            $table->string('no_kend', 12)->nullable();
            $table->string('merk', 128)->nullable();
            $table->string('warna', 128)->nullable();
            $table->enum('jenis_member', ['abonemen', 'flat', 'free', 'master'])->nullable();
            $table->date('tgl_awal');
            $table->integer('reminder_day')->nullable();
            $table->integer('synchronize')->nullable()->default(0);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['aktif', 'pasif', 'blokir'])->nullable()->default('pasif');
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
        Schema::dropIfExists('member');
    }
}
