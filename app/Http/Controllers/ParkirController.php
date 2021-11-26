<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Parkir;
use App\Models\Member;
use App\Models\Tarif;
use App\Models\Gate;
use DateTime;

class ParkirController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    protected $API_KEY = '352a9c39-70ae-4355-90ea-4978c418df88';

    public function __construct()
    {
        //
    }

    public function index(Request $request)
    {
        $response = Gate::where('api_key', $request->api_key)->first();   
 
        return response()->json($response, 200);
    }

    function generateBarcodeNumber() {
        $number = mt_rand(1000000000, 9999999999); // better than rand()
    
        // call the same function if the barcode exists already
        if ($this->barcodeNumberExists($number)) {
            return generateBarcodeNumber();
        }
    
        // otherwise, it's valid and can be used
        return $number;
    }
    
    function barcodeNumberExists($number) {
        // query the database and return a boolean
        // for instance, it might look like this in Laravel
        return Parkir::where(['barcode_id' => $number ,'status' => 'masuk'])->exists();
    }

    function generateTicketNumber() {
        $number = mt_rand(100000, 999999); // better than rand()
    
        // call the same function if the barcode exists already
        if ($this->barcodeTicketExists($number)) {
            return generateTicketNumber();
        }
    
        // otherwise, it's valid and can be used
        return $number;
    }
    
    function barcodeTicketExists($number) {
        // query the database and return a boolean
        // for instance, it might look like this in Laravel
        return Parkir::where(['no_ticket' => $number ,'status' => 'masuk'])->exists();
    }

    public function parkirIn(Request $request)
    {
   
        $data = [
            'no_ticket' => $this->generateTicketNumber(),
            'barcode_id' => $this->generateBarcodeNumber(),
            'check_in' => date('Y-m-d H:i:s'),
            'kategori' => $request->kategori,
            'status' => 'masuk',
        ];

        if (Parkir::create($data)) {
            if (Gate::where('api_key', $request->api_key)->decrement('kuota', 1)) {
                $response = [
                    'status' => true,
                    'message' =>  ucwords(str_replace('_',' ',$request->kategori)) .' ID ' .$data['barcode_id'].' / '. $data['no_ticket'],
                    'code' => 201,
                    'data' => $data,
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
                'message' => 'Gagal membuat data',
                'code' => 404
            ];
        } 

        return response()->json($response, 200);
    }

    public function parkirOut(Request $request)
    {
        $result = Parkir::where('no_ticket', $request->barcode_id)->orWhere('barcode_id', $request->barcode_id)->first();
        if(!empty($result)){
            if($result->status == 'masuk'){
                $settingTarif = Tarif::where('api_key', $this->API_KEY)->first();
               
                $time1 = new DateTime($result->check_in);
                $time2 = new DateTime(date('Y-m-d H:i:s'));
                $durasi = $time1->diff($time2);

                $jam = $durasi->h;
                $menit = $durasi->i;

                if($settingTarif->tarif_berlaku == 'flat'){
                    $queryTarif = DB::table('tarif_flat')->where('kendaraan_id', $request->kendaraan_id)->first();
                    $tarif = $query->tarif;
                    $keterangan = 'Flat';
                }elseif($settingTarif->tarif_berlaku == 'progressive'){
                    $queryTarif = DB::table('tarif_progressive')->where('kendaraan_id', $request->kendaraan_id)->first();
                    if ($jam <= 1) {
                        $ke = 1;
                        $tarif = $queryTarif->tarif_1;
                    }elseif ($jam == 2 || $jam == 1 && $menit > 0) {
                        $ke = 2;
                        $tarif = $queryTarif->tarif_2;
                    }elseif ($jam == 3 || $jam == 2 && $menit > 0) {
                        $ke = 3;
                        $tarif = $queryTarif->tarif_3;
                    }elseif ($jam == 4 || $jam == 3 && $menit > 0) {
                        $ke = 4;
                        $tarif = $queryTarif->tarif_4;
                    }elseif ($jam == 5 || $jam == 4 && $menit > 0) {
                        $ke = 5;
                        $tarif = $queryTarif->tarif_5;
                    }elseif ($jam > 5) {
                        $ke = 5;
                        $tarif = $queryTarif->tarif_5;
                    }
                    $keterangan = 'Tarif ke-'. $ke;
                }else{
                    $response = [
                        'status' => false,
                        'message' => 'Tarif belum di tentukan',
                        'code' => 404
                    ];
                }
                
                $data = [
                    'check_out' => date('Y-m-d H:i:s'),
                    'no_kend' => $request->no_kend,
                    'kendaraan_id' => $request->kendaraan_id,
                    'tarif' => $tarif,
                    'bayar' => $request->bayar,
                    'keterangan' => 'Durasi '.$jam.' Jam '.$menit.' Menit '.$keterangan,
                    'status' => 'keluar',
                    'operator_id' => $request->operator_id,
                    'shift_id' => $request->shift_id,
                    'created_by' => $request->created_by,
                    'updated_at' => date('Y-m-d H:i:s'),
                ];

                if (Parkir::where('parkir_id', $result->parkir_id)->update($data)) {
                    $response = [
                        'status' => true,
                        'message' => 'Berhasil',
                        'code' => 201,
                        'data' => [
                            'no_ticket' => $result->no_ticket,
                            'barcode_id' => $result->barcode_id,       
                        ],
                    ];
                }
            }else{
                $response = [
                    'status' => false,
                    'message' => 'Data sudah keluar pada '. $result->check_out,
                    'code' => 404
                ];
            }
            
        }else{
            $response = [
                'status' => false,
                'message' => 'Data tidak ditemukan',
                'code' => 404
            ];
        }
        
        return response()->json($response, 200);
    }

    public function memberIn(Request $request)
    {
        if($request->rfid == 'reset'){
            Gate::where('api_key', $request->api_key)->update(['kuota' => DB::raw("`default_kuota`")]);
            $response = [
                'status' => false,
                'message' => 'Berhasil di reset',
                'code' => 201
            ];
        }else{
            DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
            $query = Member::leftJoin('member_transaksi','member_transaksi.rfid','member.rfid')
                            ->selectRaw('member.kendaraan_id, member.no_kend, member.status ,member.jenis_member, member.rfid, member.tgl_awal,SUM(member_transaksi.hari) AS jumlah_hari, SUM(member_transaksi.jumlah) AS saldo')
                            ->where('member.rfid', $request->rfid)
                            ->groupBy('member.rfid')
                            ->first();
            
            if(!empty($query)){
                $status = true;
                $message = 'Success';
                
                $registrasi_date = Carbon::createFromFormat('Y-m-d', $query->tgl_awal);
                $daysToAdd = $query->jumlah_hari;
                $expired_date = date('Y-m-d', strtotime($registrasi_date->addDays($daysToAdd)));
                
                if($query->jenis_member != 'free'){
                    if($expired_date <= date('Y-m-d')){
                        $status = false;
                        $message = 'Kartu expired';
                    }
                }elseif($query->status == 'blokir' || $query->status == 'pasif'){
                    $status = false;
                    $message = 'RFID Kartu tidak aktif';
                }

                $data = [
                    'rfid' => $request->rfid,
                    'kendaraan_id' => $query->kendaraan_id,
                    'check_in' => date('Y-m-d H:i:s'),
                    'kategori' => 'member',
                    'no_kend' => $query->no_kend,
                    'status' => 'masuk'
                ];

                $where = [
                    'rfid' => $request->rfid,
                    'status' => 'masuk'
                ];

                $validasiParkirDuplicate = Parkir::where($where)->count();
                if($validasiParkirDuplicate > 0){
                    if (Parkir::where($where)->update($data)) {
                        $response = [
                            'status' => $status,
                            'rfid' => $request->rfid,
                            'message' => $message,
                            'code' => 201,
                        ];
                    }else{
                        $response = [
                            'status' => false,
                            'message' => 'Gagal membuat data',
                            'code' => 404
                        ];
                    } 
                }else{
                    $data['no_ticket'] = $this->generateTicketNumber();
                    $data['barcode_id'] = $this->generateBarcodeNumber();
                
                    if (Parkir::create($data)) {
                        $response = [
                            'status' => $status,
                            'rfid' => $request->rfid,
                            'registrasi_date' => $query->tgl_awal,
                            'expired_date' => $expired_date,
                            'saldo' => $query->saldo,
                            'message' => $message,
                            'code' => 201
                        ];
                    }else{
                        $response = [
                            'status' => false,
                            'message' => 'Gagal membuat data',
                            'code' => 404
                        ];
                    } 
                }
            
            }else{
                $response = [
                    'status' => false,
                    'message' => 'RFID tidak ditemukan',
                    'code' => 404
                ];
            }
        }
        return response()->json($response, 200);
    }

    public function memberOut(Request $request)
    {
        $where = [
            'rfid' => $request->rfid,
            'status' => 'masuk'
        ];
        $query = Parkir::where($where)->first();
        if(!empty($query)){
            $tarif = DB::table('tarif_member')->where('kendaraan_id', $query->kendaraan_id)->first();
            $data = [
                'check_out' => date('Y-m-d H:i:s'),
                'status' => 'keluar',
                'operator_id' => $request->operator_id,
                'shift_id' => $request->shift_id,
                'created_by' => $request->created_by,
                'updated_at' => date('Y-m-d H:i:s'),
                'tarif' => $tarif->jumlah,
                'bayar' => $tarif->jumlah,
            ];

            if (Parkir::where($where)->update($data)) {
                $transaksiMember = [
                    'rfid' => $request->rfid,
                    'jumlah' => -$tarif->jumlah,
                    'hari' => 0,
                    'jenis' => 'keluar',
                    'created_by' => $request->created_by,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                DB::table('member_transaksi')->insert($transaksiMember);
                DB::statement("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));");
                $saldo = Member::join('member_transaksi','member_transaksi.rfid','member.rfid')
                                ->selectRaw('member.kendaraan_id, member.no_kend, member.status ,member.jenis_member, member.rfid, member.tgl_awal,SUM(member_transaksi.hari) AS jumlah_hari, SUM(member_transaksi.jumlah) AS saldo')
                                ->where('member.rfid', $request->rfid)
                                ->groupBy('member.rfid')
                                ->first();

                $registrasi_date = Carbon::createFromFormat('Y-m-d', $saldo->tgl_awal);
                $daysToAdd = $saldo->jumlah_hari;
                $expired_date = date('Y-m-d', strtotime($registrasi_date->addDays($daysToAdd)));

                $response = [
                    'status' => true,
                    'expired_date' => $expired_date,
                    'saldo' => $saldo->saldo,
                    'message' => 'Berhasil',
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
                'message' => 'RFID tidak ditemukan',
                'code' => 404
            ];
        }
        return response()->json($response, 200);
    }
    
    public function setExpiredPakir()
    {
        $data = [
            'status' => 'expired',
            'keterangan' => 'By System '. date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (Parkir::where('status','masuk')->whereDate('check_in', '<', date('Y-m-d H:i:s', strtotime("-7 days")))->update($data)) {
            $response = [
                'status' => true,
                'message' => 'Berhasil Set to Expired data',
                'code' => 205
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'Tidak ada data yang di Set to Expired',
                'code' => 204
            ];
        }

        return response()->json($response, 200);
    }

    public function deleteExpiredPakir()
    {
  
        if (Parkir::where('status','expired')->whereDate('check_in', '<', date('Y-m-d H:i:s', strtotime("-14 days")))->delete()) {
            $response = [
                'status' => true,
                'message' => 'Berhasil Delete data Expired',
                'code' => 205
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'Tidak ada data Expired yang di Delete',
                'code' => 204
            ];
        }

        return response()->json($response, 200);
    }

    public function gateSetting(Request $request)
    {
        $data = Gate::where('api_key', $request->api_key)->first();
        if($data){
            $response = [
                'status' => true,
                'code' => 205,
                'data' => $data
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'Data tidak ditemukan',
                'code' => 204
            ];
        }
        return response()->json($response, 200);
    }

}
