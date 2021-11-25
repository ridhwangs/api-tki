<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Gate extends Model
{
    protected $table = 'master_gate';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
} 