<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AIDetection extends Model
{
    protected $table = 'ai_detections';

    protected $fillable = [
        'detected_name',
        'path',
        'cctv_name',
    ];
}
