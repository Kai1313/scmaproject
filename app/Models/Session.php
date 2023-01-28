<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = "token_pengguna";
    protected $primaryKey = 'id_token_pengguna';
}
