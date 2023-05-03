<?php

namespace App\Exports;

use App\Models\Accounting\JurnalHeader;
use App\Models\Master\Cabang;
use App\Models\Master\Slip;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;

class ReportGiroExport implements FromView
{
    private $cabang;
    private $slip;
    private $tipe;
    private $tanggal;
    private $status;

    public function __construct($cabang, $slip, $tipe, $tanggal, $status)
    {
        $this->cabang = $cabang;
        $this->slip = $slip;
        $this->tipe = $tipe;
        $this->tanggal = $tanggal;
        $this->status = $status;
    }

    public function view(): View
    {
        $giro = DB::table("jurnal_header as head")
            ->join('jurnal_detail as det', 'head.id_jurnal', 'det.id_jurnal')
            ->join('saldo_transaksi as saldo', 'saldo.id_jurnal', 'head.id_jurnal')
            ->selectRaw('head.id_jurnal,
                head.tanggal_jurnal,
                head.kode_jurnal,
                head.no_giro,
                head.tanggal_giro,
                head.tanggal_giro_jt,
                saldo.total')
            ->where('head.void', 0)
            ->where('head.id_cabang', $this->cabang)
            ->where('head.jenis_jurnal', $this->tipe)
            ->where('head.tanggal_giro_jt', '<=', $this->tanggal);

        if ($this->slip != 'All') {
            $giro = $giro->where('head.id_slip', $this->slip);
        }

        if ($this->status != 'All') {
            if ($this->status == 0) {
                $giro = $giro->where('saldo.sisa', '>', 0);
                $giro = $giro->where('saldo.status_giro', $this->status);
            } else {
                $giro = $giro->where('saldo.sisa', 0);
                $giro = $giro->where('saldo.status_giro', $this->status);
            }
        }

        $giro = $giro->groupBy('det.id_jurnal')
            ->orderBy('head.tanggal_jurnal', 'DESC');

        $data = $giro->get();

        foreach ($data as $key => $value) {
            $cair = DB::table('jurnal_header as head')
                ->join('saldo_transaksi as saldo', 'saldo.id_jurnal', 'head.id_jurnal')
                ->join('master_slip as slip', 'slip.id_slip', 'head.id_slip')
                ->selectRaw('head.kode_jurnal,
                    head.tanggal_giro_jt,
                    slip.nama_slip')
                ->where('head.id_jurnal', $value->id_jurnal)
                ->where('saldo.sisa', '=', 0)
                ->where('saldo.status_giro', '=', 1)
                ->first();
            Log::debug(json_encode($cair));

            $value->cair_kode_jurnal = isset($cair) ? $cair->kode_jurnal : '';
            $value->cair_tanggal_giro = isset($cair) ? $cair->tanggal_giro_jt : '';
            $value->cair_slip = isset($cair) ? $cair->nama_slip : '';

            $tolak = DB::table('jurnal_header as head')
                ->join('saldo_transaksi as saldo', 'saldo.id_jurnal', 'head.id_jurnal')
                ->join('master_slip as slip', 'slip.id_slip', 'head.id_slip')
                ->selectRaw('head.kode_jurnal,
                    head.tanggal_giro_jt,
                    slip.nama_slip')
                ->where('head.id_jurnal', $value->id_jurnal)
                ->where('saldo.sisa', '=', 0)
                ->where('saldo.status_giro', '=', 2)
                ->first();
            Log::debug(json_encode($tolak));

            $value->tolak_kode_jurnal = isset($tolak) ? $tolak->kode_jurnal : '';
            $value->tolak_tanggal_giro = isset($tolak) ? $tolak->tanggal_giro_jt : '';
        }

        Log::debug($data);

        $cabang = $this->cabang == 'All' ? 'All' : Cabang::find($this->cabang)->nama_cabang;
        $slip = $this->slip == 'All' ? 'All' : Slip::find($this->slip)->nama_slip; 
        $tipe = $this->tipe == 'PG' ? 'Piutang Giro' : 'Hutang Giro';
        
        if ($this->status == '0') {
            $status = 'Belum Cair';
        } else if ($this->status == '1') {
            $status = 'Cair';
        } else if ($this->status == '2') {
            $status = 'Tolak';
        } else {
            $status = 'All';
        }

        return view('accounting.report.giro.excel', [
            'data' => $data,
            'cabang' => $cabang,
            'slip' => $slip,
            'tanggal' => $this->tanggal,
            'tipe' => $tipe,
            'status' => $status
        ]);
    }
}
