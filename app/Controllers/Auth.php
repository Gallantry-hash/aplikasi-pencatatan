<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PetugasModel; // <-- Pastikan ini ada

class Auth extends BaseController
{
    public function index()
    {
        return view('login_view');
    }

    public function process()
    {
        $session = session();
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');
        $petugasModel = new PetugasModel();

        // 1. Cek jika ini adalah Admin
        if ($username === 'admin' && $password === 'admin123') {
            $sessionData = [
                'username'   => 'admin',
                'role'       => 'admin',
                'isLoggedIn' => TRUE
            ];
            $session->set($sessionData);
            return redirect()->to('/admin/dashboard');
        }

        // 2. LOGIKA BARU: Cek jika ini adalah Petugas (tanpa password)
        $petugas = $petugasModel->where('username', $username)->first();
        if ($petugas && empty($password)) {
            $sessionData = [
                'id_petugas' => $petugas['id_petugas'],
                'username'   => $petugas['username'],
                'no_telepon' => $petugas['no_telepon'], // <-- TAMBAHKAN BARIS INI
                'role'       => 'petugas',
                'isLoggedIn' => TRUE
            ];
            $session->set($sessionData);
            return redirect()->to('/petugas/dashboard');
        }

        // 3. Jika login gagal
        $session->setFlashdata('msg', 'Username atau Password salah!');
        return redirect()->to('/login');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
