<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Parkir;

class GenerateController extends Controller
{
    
    public function generate()
    {
        $response = [
            'no_ticket' => str_pad($this->generateTicketNumber(), 6, '0', STR_PAD_LEFT),
            'barcode_id' => str_pad($this->generateBarcodeNumber(), 10, '0', STR_PAD_LEFT),
            'tanggal' => date('d F Y'),
            'waktu' => date('H:i:s')
        ];
        return response()->json($response, 200);
    }

    function generateBarcodeNumber() {
        $number = mt_rand(date('mdHis'), 9999999999); // better than rand()
        // call the same function if the barcode exists already
        if ($this->barcodeNumberExists($number)) {
            return $this->generateBarcodeNumber();
        }
        // otherwise, it's valid and can be used
        return $number;
    }
    
    function barcodeNumberExists($number) {
        $cekData = Parkir::where('barcode_id', $number)->exists();
        return $cekData;
    }

    function generateTicketNumber() {
        $number = mt_rand(date('His'), 999999); // better than rand()
        // call the same function if the barcode exists already
        if ($this->TicketExists($number)) {
            return $this->generateTicketNumber();
        }
        // otherwise, it's valid and can be used
        return $number;
    }
    
    function TicketExists($number) {
        $cekData = Parkir::where('no_ticket', $number)->exists();
        return $cekData;
    }
}
