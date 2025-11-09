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
}
