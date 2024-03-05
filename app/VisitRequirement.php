<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VisitRequirement extends Model
{
    protected $table = 'visit_requirement';
    protected $primaryKey = 'id';
    protected $fillable = [
        'visit_id','item_id', 'unit_id','description','total_price','status','qty'
    ];
}
