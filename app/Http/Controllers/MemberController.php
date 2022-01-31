<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

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
        $this->member = new Member();
    }

    public function index(Request $request)
    {
      
        $query = Member::with('kendaraan')->where('jenis_member', '!=', 'master')->get();
        $response = [
            'count' => $query->count(),
            'data' => $query
        ];
        return response()->json($response);
    }

    public function memberInfo(Request $request)
    {
        $query = Member::with('kendaraan')->where('member_id', $request->member_id)->first();
        if($query){
            $sum_hari = $this->member->member_transaksi($query->rfid)->where('status', 'approve')->sum('hari');
            $daysToAdd = $sum_hari;
            $registrasi_date = Carbon::createFromFormat('Y-m-d', $query->tgl_awal);
            $expired_date = date('Y-m-d', strtotime($registrasi_date->addDays($daysToAdd)));
            
            $response = [
                'status' => true,
                'expired_date' => $expired_date,
                'remaining' => 0,
                'member' => $query,
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'RFID tidak ditemukan',
            ];
        }
        return response()->json($response);
    }

    public function MemberRegistrasi(Request $request)
    {
        $validasiMember = Member::where('rfid', $request->rfid)->first();
        if(!empty($validasiMember)){
            $response = [
                'status' => false,
                'message' => 'Member sudah terdaftar',
                'data' => $validasiMember,
            ];
        }else{
            $data = [
                'rfid' => $request->rfid,
                'tgl_awal' => $request->tgl_awal,
                'nama' => $request->nama,
                'no_kend' => $request->no_kend,
                'merk' => $request->merk,
                'warna' => $request->warna,
                'kendaraan_id' => $request->kendaraan_id,
                'keterangan' => $request->keterangan,
                'jenis_member' => $request->jenis_member,
            ];
            $create = Member::create($data);
            if ($create) {
                $response = [
                    'status' => true,
                    'message' => 'Member berhasil di simpan',
                    'member_id' => $create->id
                ];
            }else{
                $response = [
                    'status' => false,
                    'message' => 'Member Gagal di simpan',
                ];
            }
            
        }   

        return response()->json($response, 200);
    }

    public function memberUpdate(Request $request)
    {
        $data = [
            'rfid' => $request->rfid,
            'tgl_awal' => $request->tgl_awal,
            'nama' => $request->nama,
            'no_kend' => $request->no_kend,
            'merk' => $request->merk,
            'warna' => $request->warna,
            'kendaraan_id' => $request->kendaraan_id,
            'keterangan' => $request->keterangan,
            'jenis_member' => $request->jenis_member,
        ];

        $rules = [
            'rfid' => 'required|numeric|digits_between:6,16|unique:member,rfid,'.$request->member_id.',member_id',
        ];
  
        $validator = Validator::make($request->all(), $rules);
  
        if($validator->fails()){
            $response = [
                'status' => false,
                'message' => '!!!! RFID tidak boleh duplicate',
            ];
        }else{
            $udpate = Member::where('member_id', $request->member_id)->update($data);
            if ($udpate) {
                $response = [
                    'status' => false,
                    'message' => 'Member berhasil di simpan',
                    'member_id' => $request->member_id
                ];
            }else{
                $response = [
                    'status' => false,
                    'message' => 'Member Gagal di simpan',
                ];
            }
        }

      
        return response()->json($response, 200);
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
                ->where('member.jenis_member', 'abonemen')
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
