<?php
namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class DeliveryRequestDetail extends Model
{
    protected $fillable = [
        'delivery_request_id',
        'item_id',
        'qty',
        'unit_id',
        'desc',
        'status',
        'created_by',
        'updated_by',
        'delivery_qty',
        'approval_status',
        'approval_date',
        'approved_by',
    ];

    public function deliveryRequest()
    {
        return $this->belongsTo(DeliveryRequest::class, 'delivery_request_id');
    }

    public static function approvalStatusOption()
    {
        return [
            '0' => ['text' => 'Tolak', 'label' => '<label class="label label-danger">Tolak</label>'],
            '1' => ['text' => 'Menunggu Persetujuan', 'label' => '<label class="label label-warning">Menunggu Persetujuan</label>'],
            '2' => ['text' => 'Setuju', 'label' => '<label class="label label-success">Setuju</label>'],
        ];
    }

    public static function statusOption()
    {
        return [
            '1' => ['text' => 'Terbuka', 'label' => '<label class="label label-default">Terbuka</label>'],
            '2' => ['text' => 'Terpenuhi', 'label' => '<label class="label label-success">Terpenuhi</label>'],
        ];
    }
}
