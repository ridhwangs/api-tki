<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Operator;
use App\Models\Shift;
use App\Models\Parkir;
use DateTime;


class OperatorController extends Controller
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

    public function showAllShift()
    {
        $shift = Shift::all();
        return response()->json($shift);
    }

    public function doLogin(Request $request)
    {
        $message = '';
        $status = false;

        $shift = Shift::where('shift_id',$request->shift_id)->first();
        $jamSekarang = date('H:i:s');

        if($shift->jam_awal >= $jamSekarang || $shift->jam_akhir <= $jamSekarang){
           
            $message = 'Jam diluar operasional';
        }else{
            $operator = Operator::where([
                        'username' => $request->username,
                        'password' => $request->password,
                        'status' => 1
                    ]);
            if($operator->count() > 0){
                $status = true;
                // $message = Str::orderedUuid();
                $message = 'Berhasil login';
                $data = $operator->first();
                $insert = [
                    'operator_id' => $data->operator_id,
                    'shift_id' => $request->shift_id,
                    'keterangan' => 'Login',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                DB::table('log_operator')->insert($insert);
            }else{
                $message = 'Username / Password tidak ditemukan, silahkan coba kembali.';
            }
        }

        $response = [
            'status' => $status,
            'username' => $request->username,
            'message' => $message,
        ];

        return response()->json($response);
    }

    public function laporan(Request $request)
    {
        $where = [
            'status' => 'keluar',
            'operator_id' => $request->operator_id,
            'shift_id' => $request->shift_id,
        ];

        $parkir = Parkir::where($where)->whereDate('check_out', $request->tanggal)->get();
        $response = [
            'count' => $parkir->count(),
            'data' => $parkir
        ];
        return response()->json($response);
    }
}
