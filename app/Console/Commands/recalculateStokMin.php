<?php

namespace App\Console\Commands;

use App\Http\Controllers\ApiController;
use App\Models\Transaction\StokMin;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class recalculateStokMin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:stokMin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate stok minimal';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // $this->info('You call cron:stokMin command');
        \Log::info(date("Y-m-d H:i:s") . ": MULAI KALKULASI STOK MINIMAL");
        try {
            \DB::beginTransaction();
            $allStokMin = StokMin::all();
            foreach ($allStokMin as $rowItem) {
                $resultCalculated = (new ApiController)->stokmin(new Request([
                    'id' => $rowItem->id_barang,
                ]));
                $rowItem->jumlah_stok_minimal_barang_gudang = $resultCalculated->getData()->total;
                $rowItem->date_stok_minimal_barang_gudang = date("Y-m-d H:i:s");
                $rowItem->save();
            }
            \DB::commit();
            \Log::info(date("Y-m-d H:i:s") . ": SELESAI KALKULASI STOK MINIMAL");
        } catch (\Exception $e) {
            \DB::rollback();
            $message = "Error when storing stok minimal hitung";
            \Log::error($message);
            \Log::error($e);
        }
    }
}
