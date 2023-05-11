<?php

namespace App\Exports;

use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class ReportProfitAndLossExport implements FromView, ShouldAutoSize, WithColumnFormatting
{
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View {
        // Log::info(json_encode($this->data));
        return view("accounting.report.profit_loss.excel", [
            "data" => $this->data
        ]);
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'D' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
            'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED2,
        ];
    }
}
