<?php
namespace App\Models\Transaction;

use App\Cabang;
use Illuminate\Database\Eloquent\Model;
use Log;

class DeliveryRequest extends Model
{
    protected $fillable = [
        'delivery_request_code',
        'branch_id',
        'destination_branch_id',
        'date',
        'status',
        'desc',
        'created_by',
        'updated_by',
        'estimated_delivery_date',
        'delivery_request_type',
        'approval_status',
        'approval_desc',
        'approved_at',
        'approved_by',
    ];

    public function details()
    {
        return $this->hasMany(DeliveryRequestDetail::class, 'delivery_request_id');
    }

    public function formatdetail()
    {
        return $this->details()
            ->select('delivery_request_details.*', 'barang.nama_barang', 'satuan_barang.nama_satuan_barang')
            ->join('barang', 'delivery_request_details.item_id', 'barang.id_barang')
            ->join('satuan_barang', 'delivery_request_details.unit_id', 'satuan_barang.id_satuan_barang')
            ->where('status', '1');
    }

    public function branch()
    {
        return $this->belongsTo(Cabang::class, 'branch_id');
    }

    public function destinationBranch()
    {
        return $this->belongsTo(Cabang::class, 'destination_branch_id');
    }

    public static function approvalStatusOption()
    {
        return [
            '0' => ['text' => 'Tolak', 'label' => '<label class="label label-danger">Tolak</label>'],
            '1' => ['text' => 'Pending', 'label' => '<label class="label label-warning">Pending</label>'],
            '2' => ['text' => 'Setuju', 'label' => '<label class="label label-success">Setuju</label>'],
        ];
    }

    public static function statusOption()
    {
        return [
            '1' => ['text' => 'Pending', 'label' => '<label class="label label-default">Pending</label>'],
            '2' => ['text' => 'Terkirim Sebagian', 'label' => '<label class="label label-warning">Terkirim Sebagian</label>'],
            '3' => ['text' => 'Terkirim Semua', 'label' => '<label class="label label-success">Terkirim Semua</label>'],
        ];
    }

    public static function createcode()
    {
        $endString = 'PKB/' . date('m') . '/' . date('Y');
        $check     = DeliveryRequest::where('delivery_request_code', 'like', '%' . $endString)->count();
        $check += 1;
        $nol = '';
        for ($i = 0; $i < (3 - strlen((string) $check)); $i++) {
            $nol .= '0';
        }

        $string = $endString . '/' . $nol . $check;
        return $string;
    }

    public function savedetails($details)
    {
        try {
            $details = json_decode($details);
            // Save new details
            foreach ($details as $detail) {
                $data = DeliveryRequestDetail::find($detail->id);
                if (! $data) {
                    $data                      = new DeliveryRequestDetail;
                    $data->delivery_request_id = $this->id;
                    $data->item_id             = $detail->item_id;

                }

                $data->unit_id = $detail->unit_id;
                $data->qty     = $detail->qty;
                $data->desc    = $detail->desc;
                $data->save();
            }

            return ['result' => true];
        } catch (\Exception $e) {
            Log::error($e);
            return ['result' => false, 'message' => 'Failed to save delivery request details.'];
        }
    }

    public function removedetails($details)
    {
        try {
            $details   = json_decode($details);
            $detailIds = [];
            foreach ($details as $detail) {
                $detailIds[] = $detail->id;
            }

            DeliveryRequestDetail::whereIn('id', $detailIds)->delete();

            return ['result' => true];
        } catch (\Exception $e) {
            Log::error($e);
            return ['result' => false, 'message' => 'Failed to remove delivery request details.'];
        }
    }
}
