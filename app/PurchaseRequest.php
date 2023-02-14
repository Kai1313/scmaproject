<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    protected $table = 'purchase_request_header';
    protected $primaryKey = 'purchase_request_id';

    const CREATED_AT = 'dt_created';
    const UPDATED_AT = 'dt_modified';
}
