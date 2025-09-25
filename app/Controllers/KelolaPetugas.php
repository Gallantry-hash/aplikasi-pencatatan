<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PetugasModel;

class KelolaPetugas extends BaseController
{
    // Method index tetap sama, hanya menampilkan halaman utama
    public function index()
    {
        if (session()->get('role') !== 'admin') {
            return redirect()->to('/login')->with('msg', 'Akses ditolak.');
        }
        $petugasModel = new PetugasModel();
        $data['petugas'] = $petugasModel->findAll();
        $data['title'] = 'Kelola Petugas'; // Tambahkan baris ini

        return view('petugas/index', $data);
    }

    // Method 'create' tidak lagi menampilkan view, jadi bisa dihapus.

    /**
     * REVISI: Menyimpan data baru dan mengembalikan response JSON.
     */
    public function store()
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['message' => 'Akses ditolak.']);
        }

        $petugasModel = new PetugasModel();
        $data = [
            'username'   => $this->request->getPost('username'),
            'no_telepon' => $this->request->getPost('no_telepon'),
        ];

        if ($petugasModel->save($data)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Petugas berhasil ditambahkan.']);
        } else {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'errors' => $petugasModel->errors()]);
        }
    }

    /**
     * REVISI: Mengambil data satu petugas dan mengembalikannya sebagai JSON.
     */
    public function edit($id)
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setStatusCode(403);
        }

        $petugasModel = new PetugasModel();
        $data = $petugasModel->find($id);

        if ($data) {
            return $this->response->setJSON($data);
        } else {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Data tidak ditemukan.']);
        }
    }

    /**
     * REVISI: Memperbarui data dan mengembalikan response JSON.
     */
    public function update($id)
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['message' => 'Akses ditolak.']);
        }

        $petugasModel = new PetugasModel();
        $data = [
            'username'   => $this->request->getPost('username'),
            'no_telepon' => $this->request->getPost('no_telepon'),
        ];

        if ($petugasModel->update($id, $data)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Data petugas berhasil diperbarui.']);
        } else {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'errors' => $petugasModel->errors()]);
        }
    }

    /**
     * REVISI: Menghapus data dan mengembalikan response JSON.
     */
    public function delete($id)
    {
        if (session()->get('role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON(['message' => 'Akses ditolak.']);
        }
        
        $petugasModel = new PetugasModel();
        if ($petugasModel->delete($id)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Petugas berhasil dihapus.']);
        } else {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data.']);
        }
    }
}