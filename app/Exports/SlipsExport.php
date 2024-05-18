<?php

namespace App\Exports;

use App\Models\Master\Slip;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class SlipsExport implements FromView
{
    public function view(): View
    {
        return view('accounting.master.slip.export-excel', [
            'slips' => Slip::join("master_akun", "master_akun.id_akun", "master_slip.id_akun")->get(),
        ]);
    }
}
