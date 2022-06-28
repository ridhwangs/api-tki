<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

use App\Models\Card;

class CardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    //

    public function read(Request $request) {
        $query = Card::where('rfid', $request->rfid)->first();
        if($query){
            $response = [
                'status' => true,
                'data' => $query
            ];
        }else{
            $response = [
                'status' => false,
                'message' => "Data tidak ditemukan"
            ];
        }
        
        return response()->json($response);
    }
}
