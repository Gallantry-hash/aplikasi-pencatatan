<?php

namespace App\Models;

use CodeIgniter\Model;

class FotoModel extends Model
{
    protected $table            = 'foto';
    protected $primaryKey       = 'id_foto';

    // Sesuaikan dengan field baru di foto.sql
    protected $allowedFields    = [
        'id_petugas', 'nama_file', 'path_file', 'ukuran_file',
        'format_file', 'tanggal_dibuat', 'kamera_merek', 'kamera_model',
        'orientasi', 'gps_latitude', 'gps_longitude',
        'gps_altitude', 'lokasi', 'deskripsi', 'tag'
    ];
}