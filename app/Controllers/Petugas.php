<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FotoModel; // <-- Panggil model foto

class Petugas extends BaseController
{
     public function dashboard()
    {
        $session = session();

        if (!$session->get('isLoggedIn') || $session->get('role') !== 'petugas') {
            return redirect()->to('/login')->with('msg', 'Akses ditolak.');
        }

        $idPetugas = $session->get('id_petugas');
        
        $fotoModel = new FotoModel();
        $data['photos'] = $fotoModel->where('id_petugas', $idPetugas)
                                    ->orderBy('tanggal_upload', 'DESC')
                                    ->findAll();
        
        // Ambil no_telepon dari sesi dan kirim ke view
        $data['no_telepon'] = $session->get('no_telepon'); // <-- TAMBAHKAN BARIS INI

        return view('petugas_dashboard_view', $data);
    }
}