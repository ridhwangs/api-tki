<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $table = "member";
    protected $fillable = [
        'rfid','nama','tgl_awal','no_kend','merk','warna','level','kendaraan_id','keterangan','jenis_member',
    ];

    public function kendaraan()
    {
        return $this->belongsTo('App\Models\Kendaraan','kendaraan_id','kendaraan_id');
    }
} 