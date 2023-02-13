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
            'akuns' => Akun::all()
        ]);
    }
}
