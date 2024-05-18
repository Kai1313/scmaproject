<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JawabanChecklistPekerjaan extends Model
{
    protected $table = 'jawaban_checklist_pekerjaan';
    protected $primaryKey = 'id_jawaban_checklist_pekerjaan';
    public $timestamps = false;
}
