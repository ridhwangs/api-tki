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
use App\Models\Operator;

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
        $this->member = new Member();
    }

    public function index(Request $request)
    {
        $response = Gate::where('api_key', $request->api_key)->first();   
 
        return response()->json($response, 200);
    }

    public function info(Request $request)
    {
        $parkir_in = Parkir::where('status','masuk')->whereDate('parkir.check_in', $request->tanggal)->count();
        $parkir_out = Parkir::where('status','keluar')->whereDate('parkir.check_out', $request->tanggal)->count();
        $response = [
            'status' => true,
            'parkir_in' => $parkir_in,
            'parkir_out' => $parkir_out,
            'code' => '202'
        ];
        return response()->json($response, 200);
    }

    public function parkirIn(Request $request)
    {
        $kendaraan = DB::table('kendaraan')->where('kategori', $request->kategori)->first();
        $barcode_id = $request->barcode_id;
        $imageName = $request->kategori.'_'.$barcode_id;

        if($request->file('image')){
            $request->file('image')->move(storage_path('images'), $imageName);
        }

        $data = [
            'no_ticket' => $request->no_ticket,
            'barcode_id' => $barcode_id,
            'kendaraan_id' => $kendaraan->kendaraan_id,
            'image_in' => $imageName,
            'check_in' => $request->check_in,
            'kategori' => $request->kategori,
            'status' => 'masuk',
        ];

        if (Parkir::create($data)) {
            if (Gate::where('api_key', $request->api_key)->decrement('kuota', 1)) {
                $gate = Gate::where('api_key', $request->api_key)->first();
                $response = [
                    'status' => true,
                    'message' =>  ucwords(str_replace('_',' ',$request->kategori)) .' ID ' .$data['barcode_id'].' / '. $data['no_ticket'],
                    'check_in' => $request->check_in,
                    'kuota' => $gate->kuota,
                    'code' => 201,
                    'data' => $data,
                ];
            }else{
                $response = [
                    'status' => false,
                    'message' => 'Gagal membuat data level 1',
                    'code' => 404
                ];
            }             
        }else{
            $response = [
                'status' => false,
                'message' => 'Gagal membuat data level 0',
                'code' => 404
            ];
        } 

        return response()->json($response, 200);
    }

    public function parkirManual(Request $request)
    {
        $kendaraan = DB::table('kendaraan')->where('kategori', $request->kategori)->first();
        $barcode_id = 'M'.substr($this->generateBarcodeNumber(),0,9);
        $imageName = $request->kategori.'_'.$barcode_id;

        if($request->file('image')){
            $request->file('image')->move(storage_path('images'), $imageName);
        }

        $data = [
            'no_ticket' => 'M'.substr($this->generateTicketNumber(),0,4),
            'barcode_id' => $barcode_id,
            'kendaraan_id' => $kendaraan->kendaraan_id,
            'image_in' => $imageName,
            'check_in' => $request->check_in,
            'kategori' => $request->kategori,
            'shift_id' => $request->shift_id,
            'operator_id' => $request->operator_id,
            'created_by' => $request->created_by,
            'status' => 'masuk',
        ];

        if (Parkir::create($data)) {
            $response = [
                'status' => true,
                'message' =>  ucwords(str_replace('_',' ',$request->kategori)) .' ID ' .$data['barcode_id'].' / '. $data['no_ticket'],
                'check_in' => $request->check_in,
                'code' => 201,
                'data' => $data,
            ];            
        }else{
            $response = [
                'status' => false,
                'message' => 'Gagal membuat data level 0',
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
                
                $response = [
                    'status' => true,
                    'no_kend' => $request->no_kend,
                    'data' => $result,
                    'code' => 201
                ];               
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

    public function parkirBayar(Request $request)
    {
        $operator = DB::table('operator')->where('username', $request->created_by)->first();
        $data = [
            'check_out' => date('Y-m-d H:i:s'),
            'no_kend' => $request->no_kend,
            'tarif' => $request->tarif,
            'bayar' => $request->bayar,
            'keterangan' => $request->keterangan,
            'kategori' => $request->kategori,
            'kendaraan_id' => $request->kendaraan_id,
            'status' => 'keluar',
            'shift_id' => $request->shift_id,
            'created_by' => $request->created_by,
            'operator_id' => $operator->operator_id,
        ];
        if (Parkir::where('parkir_id', $request->parkir_id)->update($data)) {
            $response = [
                'status' => true,
                'message' => 'Berhasil',
                'code' => 201,
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'Gagal',
                'code' => 404
            ];
        } 
        return response()->json($response, 200);
    }

    public function getTarif(Request $request)
    {
        $settingTarif = Tarif::where('api_key', $request->api_key)->first();
        
        $result = Parkir::join('kendaraan','kendaraan.kendaraan_id','parkir.kendaraan_id')->where('parkir.parkir_id', $request->parkir_id)->first();
        if($result){
            $time1 = new DateTime($result->check_in);
            if($result->status == 'masuk'){
                $time2 = new DateTime(date('Y-m-d H:i:s'));
            }else{
                $time2 = new DateTime(date('Y-m-d H:i:s', strtotime($result->check_out)));
            }
            
            $durasi = $time1->diff($time2);

            $hari = $durasi->d;
            $jam = $durasi->h;
            $menit = $durasi->i;
            if($result->status == 'masuk'){
                if($settingTarif->tarif_berlaku == 'flat'){
                    $queryTarif = DB::table('tarif_flat')->where('kendaraan_id', $result->kendaraan_id)->first();
                    $tarif = $queryTarif->tarif;
                    $keterangan = 'Flat';
                }elseif($settingTarif->tarif_berlaku == 'progressive'){
                    $queryTarif = DB::table('tarif_progressive')->where('kendaraan_id', $result->kendaraan_id)->first();
                    // if ($jam <= 1) {
                    //     $ke = 1;
                    //     $tarif = $queryTarif->tarif_1;
                    // }elseif ($jam == 2 || $jam == 1 && $menit > 0) {
                    //     $ke = 2;
                    //     $tarif = $queryTarif->tarif_2;
                    // }elseif ($jam == 3 || $jam == 2 && $menit > 0) {
                    //     $ke = 3;
                    //     $tarif = $queryTarif->tarif_3;
                    // }elseif ($jam == 4 || $jam == 3 && $menit > 0) {
                    //     $ke = 4;
                    //     $tarif = $queryTarif->tarif_4;
                    // }elseif ($jam == 5 || $jam == 4 && $menit > 0) {
                    //     $ke = 5;
                    //     $tarif = $queryTarif->tarif_5;
                    // }elseif ($jam > 5) {
                    //     $ke = 5;
                    //     $tarif = $queryTarif->tarif_5;
                    // }

                    if ($jam == 0) {
                        $ke = 1;
                        $tarif = $queryTarif->tarif_1;
                   }elseif ($jam == 1 && $menit > 0 || $jam == 2 && $menit == 0) {
                        $ke = 2;
                        $tarif = $queryTarif->tarif_2;
                    }elseif ($jam == 2 && $menit > 0 || $jam == 3 && $menit == 0) {
                        $ke = 3;
                        $tarif = $queryTarif->tarif_3;
                    }elseif ($jam == 3 && $menit > 0 || $jam == 4 && $menit == 0) {
                        $ke = 4;
                        $tarif = $queryTarif->tarif_4;
                    }elseif ($jam == 4 && $menit > 0 || $jam == 5 && $menit == 0) {
                        $ke = 5;
                        $tarif = $queryTarif->tarif_5;
                    }elseif ($jam > 5) {
                        $ke = 5;
                        $tarif = $queryTarif->tarif_5;
                    }

                    $keterangan = 'Tarif Progressive ke-'. $ke;
                }else{
                    $response = [
                        'status' => false,
                        'message' => 'Tarif belum di tentukan',
                        'code' => 404
                    ];
                }
            }else{
                $tarif = $result->tarif;
                $keterangan = $result->keterangan;
            }
            if($result->status == 'masuk'){
                $check_out = date('Y-m-d H:i:s');
            }else{
                $check_out = $result->check_out;
            }
            $data = [
                'parkir_id' => $result->parkir_id,
                'no_ticket' => $result->no_ticket,
                'barcode_id' => $result->barcode_id,
                'image_in' => $result->image_in,
                'kategori' => $result->kategori,
                'kendaraan_id' => $result->kendaraan_id,
                'nama_kendaraan' => $result->nama_kendaraan,
                'check_in' => $result->check_in,
                'check_out' => $check_out,
                'tarif' => $tarif,
                'bayar' => $result->bayar,
                'hari' => $hari,
                'jam' => $jam,
                'menit' => $menit,
                'keterangan' => $keterangan,
                'status' => $result->status,
                'no_kend' => $result->no_kend,
            ];

            $nm_kendaraan = DB::table('kendaraan')->where('kategori', $result->kategori)->get();
            $response = [
                'status' => true,
                'nm_kendaraan' => $nm_kendaraan,
                'data' => $data,
            ];
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
                'rfid' => $request->rfid,
                'message' => 'Berhasil di reset',
                'code' => 201
            ];
        }else{
                $query = Member::where('rfid', $request->rfid)->first();
                if(!empty($query)){
                    if($query->jenis_member == 'master'){
                            $response = [
                                'status' => true,
                                'rfid' => $request->rfid,
                                'message' => 'Berhasil membuat data',
                                'code' => 201
                            ];
                    }else{
                        if($query->status == 'blokir' || $query->status == 'pasif'){
                        $response = [
                            'status' => false,
                            'rfid' => $request->rfid,
                            'message' => 'RFID Kartu tidak aktif status '. $query->status,
                            'code' => 201,
                        ];
                        }else{
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
                                        'status' => true,
                                        'rfid' => $request->rfid,
                                        'message' => 'Berhasil update data',
                                        'code' => 201,
                                    ];
                                }else{
                                    $response = [
                                        'status' => false,
                                        'rfid' => $request->rfid,
                                        'message' => 'Gagal update data',
                                        'code' => 404
                                    ];
                                } 
                            }else{
                                $data['no_ticket'] = $this->generateTicketNumber();
                                $data['barcode_id'] = $this->generateBarcodeNumber();
                            
                                if (Parkir::create($data)) {
                                    $response = [
                                        'status' => true,
                                        'rfid' => $request->rfid,
                                        'message' => 'Berhasil membuat data',
                                        'code' => 201
                                    ];
                                }else{
                                    $response = [
                                        'status' => false,
                                        'message' => 'Gagal membuat data',
                                        'rfid' => $request->rfid,
                                        'code' => 404
                                    ];
                                } 
                            }
                        }
                    }
                }else{
                    $response = [
                        'status' => false,
                        'message' => 'RFID tidak ditemukan',
                        'rfid' => $request->rfid,
                        'code' => 404
                    ];
                }
        }
        return response()->json($response, 200);
    }

    public function memberOut(Request $request)
    {

        $member = Member::with('kendaraan')->where('rfid', $request->rfid)->first();
        if($member->jenis_member == 'master'){
            $response = [
                'rfid' => $request->rfid,
                'jenis' => 'master',
                'status' => true,
                'message' => 'Kartu Master pada '.date('Y-m-d H:i:s'),
                'code' => 202
            ];
        }else{
            $where = [
                'rfid' => $request->rfid,
            ];
            
            $query = Parkir::where($where)->first();
            if(!empty($query)){

                $operator = Operator::where('username', $request->created_by)->first();
                $data = [
                    'check_out' => date('Y-m-d H:i:s'),
                    'status' => 'keluar',
                    'shift_id' => $request->shift_id,
                    'created_by' => $request->created_by,
                    'operator_id' => $operator->operator_id,
                ];
                if (Parkir::where('parkir_id', $request->parkir_id)->where('status', 'masuk')->update($data)) {
                    $response = [
                        'status' => true,
                        'message' => 'Berhasil',
                        'code' => 201,
                    ];
                }else{
                    $response = [
                        'status' => false,
                        'message' => 'Gagal',
                        'code' => 404
                    ];
                }

                $sum_hari = $this->member->member_transaksi($request->rfid)->where('status', 'approve')->sum('hari');
                $daysToAdd = $sum_hari;
                $registrasi_date = Carbon::createFromFormat('Y-m-d', $member->tgl_awal);
                $expired_date = date('Y-m-d', strtotime($registrasi_date->addDays($daysToAdd)));
                $parkir = Parkir::where('parkir_id', $query->parkir_id)->first();

                $time1 = new DateTime($parkir->check_in);
                
                if($parkir->status == 'masuk'){
                    $time2 = new DateTime(date('Y-m-d H:i:s'));
                }else{
                    $time2 = new DateTime(date('Y-m-d H:i:s', strtotime($parkir->check_out)));
                }

                $durasi = $time1->diff($time2);

                $hari = $durasi->d;
                $jam = $durasi->h;
                $menit = $durasi->i;

                $response = [
                    'status' => true,
                    'rfid' => $member->rfid,
                    'expired_date' => $expired_date,
                    'remaining' => 0,
                    'member' => $member,
                    'parkir' => $parkir,
                    'parkir_id' => $parkir->parkir_id,
                    'hari' => $hari,
                    'jam' => $jam,
                    'menit' => $menit,
                ];
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
    
    public function setExpiredPakir()
    {
        $data = [
            'status' => 'expired',
            'keterangan' => 'By System '. date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (Parkir::where('status','masuk')->whereDate('check_in', '<', date('Y-m-d H:i:s', strtotime("-1 days")))->update($data)) {
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
  
        if (Parkir::where('status','expired')->whereDate('check_in', '<', date('Y-m-d H:i:s', strtotime("-4 days")))->delete()) {
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

    public function setKategori(Request $request)
    {
        $where = [
            'parkir_id' => $request->parkir_id
        ];
        $kendaraan = DB::table('kendaraan')->where('kategori', $request->kategori)->first();
        $data = [
            'kategori' => $request->kategori,
            'kendaraan_id' => $kendaraan->kendaraan_id,
        ];
        if (Parkir::where($where)->update($data)) {
            $response = [
                'status' => true,
                'message' => 'Berhasil update kategori',
                'code' => 201,
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'Gagal update kategori',
                'code' => 404
            ];
        } 
        return response()->json($response, 200);
    }

    public function setKendaraan(Request $request)
    {
        $where = [
            'parkir_id' => $request->parkir_id
        ];
        $data = [
            'kendaraan_id' => $request->kendaraan_id,
        ];
        if (Parkir::where($where)->update($data)) {
            $response = [
                'status' => true,
                'message' => 'Berhasil update kategori',
                'code' => 201,
            ];
        }else{
            $response = [
                'status' => false,
                'message' => 'Gagal update kategori',
                'code' => 404
            ];
        } 
        return response()->json($response, 200);
    }

    public function image($imageName)
    {
        $image_path = storage_path('images/') . $imageName;
        if (file_exists($image_path)) {
            $file = file_get_contents($image_path);
            return response($file, 200)->header('Content-Type', 'image/jpeg');
        }
       
        $response = [
            'status' => false,
            'message' => 'Gagal mengambil data',
            'code' => 404
        ];
        return response()->json($response, 200);
    }

    function generateBarcodeNumber() {
        $number = mt_rand(1000000000, 9999999999); // better than rand()
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
        $number = mt_rand(10000, 99999); // better than rand()
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

    public function sync()
    {
        $response = Parkir::get();
        return response()->json($response, 200);

    }
}
