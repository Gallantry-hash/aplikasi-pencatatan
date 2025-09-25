<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FotoModel;

class Admin extends BaseController
{
    /**
     * Menampilkan halaman dashboard utama dengan data foto yang sudah dipaginasi dan difilter.
     */
    public function dashboard()
    {
        // Pemeriksaan session untuk memastikan hanya admin yang bisa akses
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $fotoModel = new FotoModel();
        $filterPetugas = $this->request->getGet('petugas');

        $query = $fotoModel
            ->select('foto.*, petugas.username')
            ->join('petugas', 'petugas.id_petugas = foto.id_petugas', 'left');

        if (!empty($filterPetugas)) {
            $query->like('petugas.username', $filterPetugas);
        }

        $data = [
            'photos'        => $query->orderBy('tanggal_upload', 'DESC')->paginate(5, 'default'),
            'pager'         => $fotoModel->pager,
            'filterPetugas' => $filterPetugas,
            'title'         => 'Dashboard Utama'
        ];

        return view('admin_dashboard', $data);
    }

    // VVV LETAKKAN KODE ANDA DI SINI (DENGAN PERBAIKAN) VVV
    public function export()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $filterPetugas = $this->request->getGet('petugas');
        $fotoModel = new FotoModel();

        // --- PERBAIKAN PENTING ADA DI SINI ---
        // Anda harus melakukan JOIN terlebih dahulu agar bisa memfilter berdasarkan 'username'
        // dan agar kolom 'username' tersedia untuk diekspor.
        $query = $fotoModel
            ->select('foto.*, petugas.username')
            ->join('petugas', 'petugas.id_petugas = foto.id_petugas', 'left');

        if (!empty($filterPetugas)) {
            // Filter berdasarkan 'petugas.username' setelah join
            $query->like('petugas.username', $filterPetugas);
        }

        // Ambil semua data yang cocok dengan query
        $dataToExport = $query->findAll();


        // 1. Ambil nama awalan dari input URL (?prefix=...)
        $prefixInput = $this->request->getGet('prefix');

        // 2. Bersihkan nama awalan dan berikan nilai default "EXPORT" jika kosong
        //    Ini untuk keamanan agar nama file tidak mengandung karakter aneh
        $namaAwalan = !empty($prefixInput) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $prefixInput) : 'EXPORT';

        // 3. Buat template tanggal dan angka acak
        $tanggal = date('Ymd');
        $angkaAcak = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);

        // 4. Gabungkan menjadi format nama file yang baru
        $fileName = "{$namaAwalan}_{$tanggal}_{$angkaAcak}.csv";


        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$fileName");
        header("Content-Type: application/csv; charset=utf-8");

        $file = fopen('php://output', 'w');

        // Tulis header CSV
        fputcsv($file, ["ID", "Username Petugas", "Latitude", "Longitude", "Lokasi", "Path File"]);

        // Tulis data ke file
        foreach ($dataToExport as $row) {
            $csvData = [
                $row['id_foto'],
                $row['username'], // <-- Kolom ini sekarang tersedia karena sudah di-JOIN
                $row['gps_latitude'],
                $row['gps_longitude'],
                $row['lokasi'],
                $row['path_file']
            ];
            fputcsv($file, $csvData);
        }

        fclose($file);
        exit;
    }
    // ^^^ AKHIR DARI KODE ANDA ^^^

    public function buatLink()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }
        $data['upload_link'] = base_url('manajemen-foto');
        $data['title'] = 'Buat Link Publik';
        return view('buat_link_view', $data);
    }
}
