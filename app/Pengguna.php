<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Pengguna extends Model
{
    protected $table = 'pengguna';
    protected $primaryKey = 'id_pengguna';
    public $timestamps = false;

    protected $fillable = [
        'id_grup_pengguna', 'nama_pengguna', 'id_provinsi', 'id_kabupaten', 'id_kecamatan', 'id_kelurahan', 'alamat_pengguna', 'telepon1_pengguna', 'telepon2_pengguna', 'email_pengguna', 'username', 'password', 'foto_pengguna', 'nomor_ktp_pengguna', 'tempat_lahir_pengguna', 'tanggal_lahir_pengguna', 'jenis_kelamin_pengguna', 'keterangan_pengguna', 'status_pengguna', 'user_pengguna', 'date_pengguna',
    ];
}
