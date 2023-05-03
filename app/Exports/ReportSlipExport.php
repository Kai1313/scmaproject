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

class ReportSlipExport implements FromView
{
    private $cabang;
    private $slip;
    private $from;
    private $to;
    private $fromOrg;
    private $toOrg;

    public function __construct($cabang, $slip, $from, $to)
    {
        $this->cabang = $cabang;
        $this->slip = $slip;
        $this->from = "'" . $from . "'";
        $this->to = "'" . $to . "'";
        $this->fromOrg = $from;
        $this->toOrg = $to;
    }

    public function view(): View
    {
        $slip = Slip::find($this->slip);

        $saldo_awal = DB::table("jurnal_header as head")
            ->join('jurnal_detail as det', 'head.id_jurnal', 'det.id_jurnal')
            ->join('master_akun as akun', 'akun.id_akun', 'det.id_akun')
            ->join('master_slip as slip', 'slip.id_slip', 'head.id_slip')
            ->selectRaw('head.tanggal_jurnal,
        "" as kode_jurnal,
        "" as nama_slip,
        akun.nama_akun,
        "Saldo Awal" as keterangan,
        "" as id_transaksi,
        det.debet,
        det.credit')
            ->where('head.void', 0)
            ->where('head.id_cabang', $this->cabang)
            ->where('head.id_slip', $this->slip)
            ->where('det.id_akun', $slip->id_akun)
            ->whereRaw("head.tanggal_jurnal BETWEEN $this->from AND $this->to")
            ->groupBy('det.id_akun')
            ->orderBy('head.tanggal_jurnal', 'DESC')
            ->get();

        $mutasis = DB::table("jurnal_header as head")
            ->join('jurnal_detail as det', 'head.id_jurnal', 'det.id_jurnal')
            ->join('master_akun as akun', 'akun.id_akun', 'det.id_akun')
            ->join('master_slip as slip', 'slip.id_slip', 'head.id_slip')
            ->selectRaw('head.tanggal_jurnal,
            head.kode_jurnal,
            slip.nama_slip,
            akun.nama_akun,
            det.keterangan,
            det.id_transaksi,
            det.debet,
            det.credit')
            ->where('head.void', 0)
            ->where('head.id_cabang', $this->cabang)
            ->where('head.id_slip', $this->slip)
            ->where('det.id_akun', '!=', $slip->id_akun)
            ->whereRaw("head.tanggal_jurnal BETWEEN $this->from AND $this->to")
            ->orderBy('head.tanggal_jurnal', 'DESC')
            ->get();

        $cabang = Cabang::find($this->cabang);
        $slip = Slip::find($this->slip);

        foreach ($saldo_awal as $key => $value) {
            $notes = str_replace("\n", '<br>', $value->keterangan);
            $value->keterangan = $notes;
        }

        foreach ($mutasis as $key => $value) {
            $notes = str_replace("\n", '<br>', $value->keterangan);
            $value->keterangan = $notes;
        }

        return view('accounting.report.slip.excel', [
            'saldo_awal' => $saldo_awal,
            'mutasis' => $mutasis,
            'cabang' => $cabang,
            'slip' => $slip,
            'from' => $this->fromOrg,
            'to' => $this->toOrg
        ]);
    }
}
