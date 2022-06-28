<?php

namespace App\Http\Controllers;



class NetworkController extends Controller
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

    public function ping()
    {      
        $response = [
            'status' => true,
            'message' => 'Server is ready ',
            'current_date' => date('Y-m-d H:i:s'),
        ];
        return response()->json($response, 200);
    }
   
}
