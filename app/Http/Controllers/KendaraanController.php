<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Kendaraan;

class KendaraanController extends Controller
{
    
    public function index()
    {
        $response = Kendaraan::get();
        return response()->json($response, 200);
    }

    public function listKendaraan(Type $var = null)
    {
        $response = Kendaraan::get();
        return response()->json($response, 200);
    }
}
