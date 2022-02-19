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

    public function doLoginT(Request $request)
    {
        $message = '';
        $status = false;

        $shift = Shift::where('shift_id', $request->shift_id)->first();
        $jamSekarang = date('H:i:s');

        if($shift->jam_awal >= $jamSekarang || $shift->jam_akhir <= $jamSekarang){
           
            $message = 'Jam diluar operasional';
            $response = [
                'status' => false,
                'username' => $request->username,
                'message' => $message,
                'code' => 404
            ];
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

                $shift = Shift::where('shift_id', $request->shift_id)->first();

                DB::table('log_operator')->insert($insert);
                $response = [
                    'status' => $status,
                    'nama' => $data->nama,
                    'username' => $request->username,
                    'message' => $message,
                    'shift_id' => $request->shift_id,
                    'operator_id' => $data->operator_id,
                    'nama_shift' => $shift->nama_shift,
                    'jam_awal' => $shift->jam_awal,
                    'jam_akhir' => $shift->jam_akhir,
                    'code' => 200
                ];
            }else{
                $message = 'Username / Password tidak ditemukan, silahkan coba kembali.';
                $response = [
                    'status' => $status,
                    'username' => $request->username,
                    'message' => $message,
                    'code' => 404
                ];
            }
        }
        
        return response()->json($response);
    }
    
    public function doLogin(Request $request)
    {
        $message = '';
        $status = false;

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

                $shift = Shift::where('shift_id', $request->shift_id)->first();

                DB::table('log_operator')->insert($insert);
                $response = [
                    'status' => $status,
                    'nama' => $data->nama,
                    'username' => $request->username,
                    'message' => $message,
                    'shift_id' => $request->shift_id,
                    'operator_id' => $data->operator_id,
                    'nama_shift' => $shift->nama_shift,
                    'jam_awal' => $shift->jam_awal,
                    'jam_akhir' => $shift->jam_akhir,
                    'code' => 200
                ];
            }else{
                $message = 'Username / Password tidak ditemukan, silahkan coba kembali.';
                $response = [
                    'status' => $status,
                    'username' => $request->username,
                    'message' => $message,
                    'code' => 404
                ];
            }
        
        return response()->json($response);
    }

    public function checkMaster(Request $request)
    {
        $operator = Operator::where([
                        'username' => $request->username,
                        'password' => $request->password,
                        'status' => 1,
                        'level' => 'hard'
                    ]);
            if($operator->count() > 0){
                $status = true;
                // $message = Str::orderedUuid();
                $message = 'Berhasil login';
                $data = $operator->first();
                $insert = [
                    'operator_id' => $data->operator_id,
                    'shift_id' => $request->shift_id,
                    'keterangan' => 'Gate Setting',
                    'created_at' => date('Y-m-d H:i:s')
                ];

                $shift = Shift::where('shift_id', $request->shift_id)->first();

                DB::table('log_operator')->insert($insert);
                $response = [
                    'status' => $status,
                    'nama' => $data->nama,
                    'username' => $request->username,
                    'message' => $message,
                    'code' => 200
                ];
            }else{
                $message = 'Username / Password tidak ditemukan, silahkan coba kembali.';
                $response = [
                    'status' => false,
                    'username' => $request->username,
                    'message' => $message,
                    'code' => 404
                ];
            }

     

        return response()->json($response);
    }

    public function laporan(Request $request)
    {
        $where = [
            'parkir.status' => 'keluar',
            'parkir.operator_id' => $request->operator_id,
            'parkir.shift_id' => $request->shift_id,
        ];

        $parkir = Parkir::where($where)
                        ->join('shift','shift.shift_id','parkir.shift_id')
                        ->join('kendaraan','kendaraan.kendaraan_id','parkir.kendaraan_id')
                        ->whereDate('parkir.check_out', $request->tanggal)->orderBy('parkir.parkir_id','DESC')->get();
        $response = [
            'count' => $parkir->count(),
            'data' => $parkir
        ];
        return response()->json($response);
    }

    public function print(Request $request)
    {
        $where = [
            'parkir.status' => 'keluar',
            'parkir.operator_id' => $request->operator_id,
            'parkir.shift_id' => $request->shift_id,
        ];
        
        $parkir = Parkir::where($where)
                        ->selectRaw('SUM(parkir.tarif) AS tarif, kendaraan.nama_kendaraan AS nama_kendaraan, COUNT(*) AS qty')
                        ->join('shift','shift.shift_id','parkir.shift_id')
                        ->join('kendaraan','kendaraan.kendaraan_id','parkir.kendaraan_id')
                        ->where('parkir.kategori', '!=' , 'member')
                        ->whereDate('parkir.check_out', $request->tanggal)
                        ->orderBy('parkir.parkir_id','DESC')
                        ->groupBy('kendaraan.nama_kendaraan')
                        ->get();

        $member = Parkir::where($where)
                        ->selectRaw('SUM(parkir.tarif) AS tarif, kendaraan.nama_kendaraan AS nama_kendaraan, COUNT(*) AS qty')
                        ->join('shift','shift.shift_id','parkir.shift_id')
                        ->join('kendaraan','kendaraan.kendaraan_id','parkir.kendaraan_id')
                        ->where('parkir.kategori', 'member')
                        ->whereDate('parkir.check_out', $request->tanggal)
                        ->orderBy('parkir.parkir_id','DESC')
                        ->groupBy('kendaraan.nama_kendaraan')
                        ->get();

        $member_transaksi = DB::table('member_transaksi')
                                ->selectRaw('SUM(member_transaksi.jumlah) AS jumlah, COUNT(*) AS count')
                                ->where('jenis', 'topup')
                                ->whereDate('created_at', $request->tanggal)->first();
        $response = [
            'count' => $parkir->count(),
            'data' => $parkir,
            'data_member' => $member,
            'member' => $member_transaksi,
        ];
        
        return response()->json($response);
    }
}
