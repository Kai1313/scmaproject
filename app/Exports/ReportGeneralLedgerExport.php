<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromView;

class ReportGeneralLedgerExport implements FromView
{
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View {
        Log::info(json_encode($this->data));
        return view("accounting.report.general_ledger.excel", [
            "data" => $this->data
        ]);
    }
}
