<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Tarif extends Model
{
    protected $table = 'tarif_setting';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['tarif_berlaku'];

} 