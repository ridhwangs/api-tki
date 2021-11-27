<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Carbon\Carbon;
use App\Models\Member;
use DateTime;

class MemberController extends Controller
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

    public function index(Request $request)
    {
      
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $query = Member::leftJoin('member_transaksi','member_transaksi.rfid','member.rfid')
                ->join('kendaraan','kendaraan.kendaraan_id','member.kendaraan_id')
                ->selectRaw('member.nama AS nama, 
                            member.alamat AS alamat,
                            member.jenis_member AS jenis_member,
                            member.tgl_awal AS tgl_awal,
                            member.kendaraan_id, 
                            member.no_kend, 
                            member.status, 
                            member.jenis_member, 
                            member.rfid, 
                            member.tgl_awal,
                            member.status AS status, 
                            kendaraan.kategori AS ketegori,
                            SUM(member_transaksi.hari) AS jumlah_hari,
                            SUM(member_transaksi.jumlah) AS saldo')
                ->where('member.status', $request->status)
                ->groupBy('member.rfid')
                ->get();

        $response = [
            'count' => $query->count(),
            'data' => $query
        ];
        return response()->json($response);
    }

    public function infoMember(Request $request)
    {
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $query = Member::leftJoin('member_transaksi','member_transaksi.rfid','member.rfid')
                        ->join('kendaraan','kendaraan.kendaraan_id','member.kendaraan_id')
                        ->selectRaw('kendaraan.kategori, kendaraan.nama_kendaraan, member.nama ,member.kendaraan_id, member.no_kend, member.status ,member.jenis_member, member.rfid, member.tgl_awal,SUM(member_transaksi.hari) AS jumlah_hari, SUM(member_transaksi.jumlah) AS saldo')
                        ->where('member.rfid', $request->rfid)
                        ->groupBy('member.rfid')
                        ->first();
        if(!empty($query)){
            if($query->jenis_member == 'free'){
                $expired_date = null;
                $days = 0;
            }else{
                $registrasi_date = Carbon::createFromFormat('Y-m-d', $query->tgl_awal);
                $daysToAdd = $query->jumlah_hari;
                $expired_date = date('Y-m-d', strtotime($registrasi_date->addDays($daysToAdd)));

                $datetime1 = new DateTime(date('Y-m-d'));
                $datetime2 = new DateTime($expired_date);
                $interval = $datetime1->diff($datetime2);
                $days = $interval->format('%a');
            }
           
            $response = [
                'expired_date' => $expired_date,
                'remaining' => $days,
                'data' => $query,
                'code' => 200
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'RFID tidak ditemukan',
                'code' => 404
            ];
        }
       
        return response()->json($response);
    }

    public function memberTopup(Request $request)
    {
        $validasiMember = Member::where('rfid', $request->rfid)->first();
        if(!empty($validasiMember)){
            $data = [
                'rfid' => $request->rfid,
                'jumlah' => $request->jumlah,
                'hari' => $request->hari,
                'jenis' => 'topup',
                'created_by' => $request->created_by,
                'created_at' => date('Y-m-d H:i:s')
            ];

            if (DB::table('member_transaksi')->insert($data)) {
                DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
                $query = Member::join('member_transaksi','member_transaksi.rfid','member.rfid')
                        ->selectRaw('member.kendaraan_id, member.no_kend, member.status ,member.jenis_member, member.rfid, member.tgl_awal,SUM(member_transaksi.hari) AS jumlah_hari, SUM(member_transaksi.jumlah) AS saldo')
                        ->where('member.rfid', $request->rfid)
                        ->groupBy('member.rfid')
                        ->first();
                $registrasi_date = Carbon::createFromFormat('Y-m-d', $query->tgl_awal);
                $daysToAdd = $query->jumlah_hari;
                $expired_date = date('Y-m-d', strtotime($registrasi_date->addDays($daysToAdd)));
                
                $response = [
                    'status' => true,
                    'rfid' => $request->rfid,
                    'registrasi_date' => $query->tgl_awal,
                    'expired_date' => $expired_date,
                    'saldo' => $query->saldo,
                    'message' => "Berhasil di topup",
                    'code' => 201
                ];
            }else{
                $response = [
                    'status' => false,
                    'message' => 'Gagal membuat data',
                    'code' => 404
                ];
            }
        }else{
            $response = [
                'status' => false,
                'message' => 'Member tidak ditemukan',
                'code' => 404
            ];
        }
        
        
        return response()->json($response, 200);

    }

    public function ValidasiMember()
    {
        Member::query()->update(['status' => 'aktif']);
        DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
        $query = Member::leftJoin('member_transaksi','member_transaksi.rfid','member.rfid')
                ->selectRaw('member.rfid AS rfid, member.tgl_awal AS tgl_awal, SUM(member_transaksi.hari) AS jumlah_hari')
                ->where('member.status', 'aktif')
                ->groupBy('member.rfid')
                ->get();

        $data = [];
        foreach ($query as $key => $rows) {
            $jumlah_hari = 0;
            if($rows->jumlah_hari> 0){
                $jumlah_hari = $rows->jumlah_hari;
            }
            $registrasi_date = Carbon::createFromFormat('Y-m-d', $rows->tgl_awal);
            $daysToAdd = $jumlah_hari;
            $expired_date = date('Y-m-d', strtotime($registrasi_date->addDays($daysToAdd)));
           
            if(date('Y-m-d') >= $expired_date){
                $dataArr = [
                    'rfid' => $rows->rfid,
                    'daysToAdd' => $daysToAdd,
                    'today' => date('Y-m-d'),
                    'expired_date' => $expired_date,
                    'status' => 'pasif',
                ];
                array_push($data, $dataArr);
            }
        }
        $rfid = array_column($data, 'rfid');

        if (Member::whereIn('rfid', $rfid)->update(['status' => 'pasif'])) {
            $response = [
                'status' => true,
                'count' => $query->count(),
            ];
        }else{
            $response = [
                'status' => false,
            ];
        }
      
        return response()->json($response);
    }
}
