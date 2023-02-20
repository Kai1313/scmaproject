<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Master\Akun;

class AkunsExport implements FromView
{
    public function view(): View
    {
        return view('accounting.master.coa.export-excel', [
            'akuns' => Akun::select("master_akun.kode_akun", "master_akun.nama_akun", "master_akun.tipe_akun", "par.nama_akun as parent_name", "master_akun.header1", "master_akun.header2", "master_akun.header3")->leftJoin("master_akun as par", "master_akun.id_akun", "master_akun.id_parent")->get()
        ]);
    }
}
