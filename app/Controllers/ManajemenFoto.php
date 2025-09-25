<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PetugasModel;
use App\Models\FotoModel;

class ManajemenFoto extends BaseController
{
    private $drive;

    public function __construct()
    {
        try {
            $client = new \Google_Client();
            $client->setClientId(getenv('GOOGLE_CLIENT_ID'));
            $client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
            $client->setRedirectUri('https://developers.google.com/oauthplayground');
            $client->setAccessType('offline');

            $client->fetchAccessTokenWithRefreshToken(getenv('GOOGLE_REFRESH_TOKEN'));

            $this->drive = new \Google_Service_Drive($client);
        } catch (\Exception $e) {
            log_message('error', 'Google Drive API connection error: ' . $e->getMessage());
            $this->drive = null;
        }
    }

    public function halamanToken()
    {
        return view('token_view');
    }

    public function verifikasiToken()
    {
        $token = $this->request->getPost('token');
        $validToken = 'bpdas';

        if ($token === $validToken) {
            session()->set('akses_upload_diizinkan', true);
            return redirect()->to('/form-upload');
        } else {
            return redirect()->back()->with('error', 'Kode Token Salah!');
        }
    }

    public function index()
    {
        if (session()->get('akses_upload_diizinkan') !== true) {
            return redirect()->to('/manajemen-foto');
        }
        return view('manajemen_foto_view');
    }

    public function upload()
    {
        if (session()->get('akses_upload_diizinkan') !== true) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak. Sesi verifikasi tidak ditemukan.']);
        }

        if (!$this->drive) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal terhubung ke Google Drive. Periksa file log untuk detail.']);
        }

        $username = $this->request->getPost('username');
        $no_telepon = $this->request->getPost('no_telepon');
        $tahun = $this->request->getPost('tahun');
        $kategori = $this->request->getPost('kategori');
        $sub_kategori = $this->request->getPost('sub_kategori');
        $files = $this->request->getFiles('files');

        if (empty($username) || empty($no_telepon) || empty($tahun) || empty($kategori) || !$files || empty($files['files'][0]->getName())) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Semua field harus diisi.']);
        }

        try {
            $petugasModel = new PetugasModel();
            $petugas = $petugasModel->where('username', $username)->first();
            if (!$petugas) {
                $petugasModel->save([
                    'username' => $username,
                    'no_telepon' => $no_telepon
                ]);
                $petugas = $petugasModel->where('username', $username)->first();
            }
            $id_petugas = $petugas['id_petugas'];

            $pathArray = [$tahun, $kategori];
            if ($kategori === 'BIBIT PERSEMAIAN PERMANEN' && !empty($sub_kategori)) {
                $pathArray[] = $sub_kategori;
            }
            $pathArray[] = $username;

            $folderId = $this->getOrCreateNestedFolder($pathArray);
            if (!$folderId) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat struktur folder di Google Drive. Pastikan folder "geotaging" ada dan periksa log.']);
            }

            $fotoModel = new FotoModel();
            $processedCount = 0;
            foreach ($files['files'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $this->processAndUploadImage($file->getTempName(), $id_petugas, $fotoModel, $folderId, $file->getClientName());
                    $processedCount++;
                }
            }
            return $this->response->setJSON(['status' => 'success', 'processed' => $processedCount]);
        } catch (\Exception $e) {
            log_message('error', '[Upload Error] ' . $e->getMessage());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan di server: ' . $e->getMessage()]);
        }
    }

    private function getOrCreateNestedFolder(array $pathArray)
    {
        try {
            $query = "name='geotaging' and mimeType='application/vnd.google-apps.folder' and 'root' in parents and trashed=false";
            $response = $this->drive->files->listFiles(['q' => $query, 'fields' => 'files(id)']);
            if (empty($response->getFiles())) {
                log_message('error', 'Folder utama "geotaging" tidak ditemukan di root Google Drive. Harap buat folder tersebut secara manual.');
                return null;
            }
            $parentId = $response->getFiles()[0]->getId();

            foreach ($pathArray as $folderName) {
                $parentId = $this->findOrCreateFolder($folderName, $parentId);
            }
            return $parentId;

        } catch (\Exception $e) {
            log_message('error', 'Gagal saat memproses folder: ' . $e->getMessage());
            return null;
        }
    }

    private function findOrCreateFolder($folderName, $parentId)
    {
        $query = "name='{$folderName}' and mimeType='application/vnd.google-apps.folder' and '{$parentId}' in parents and trashed=false";
        $response = $this->drive->files->listFiles(['q' => $query, 'fields' => 'files(id)']);

        if (!empty($response->getFiles())) {
            return $response->getFiles()[0]->getId();
        } else {
            $fileMetadata = new \Google_Service_Drive_DriveFile([
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$parentId]
            ]);
            $folder = $this->drive->files->create($fileMetadata, ['fields' => 'id']);
            return $folder->id;
        }
    }

    private function processAndUploadImage($filePath, $id_petugas, $fotoModel, $driveFolderId, $originalName = null)
    {
        $exif = @exif_read_data($filePath);
        $fileName = $originalName ?? basename($filePath);

        $fileMetadata = new \Google_Service_Drive_DriveFile([
            'name' => $fileName,
            'parents' => [$driveFolderId]
        ]);
        $content = file_get_contents($filePath);
        $file = $this->drive->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => mime_content_type($filePath),
            'uploadType' => 'multipart',
            'fields' => 'id, webViewLink'
        ]);
        
        $dbData = [
            'id_petugas' => $id_petugas,
            'nama_file' => $fileName,
            'path_file' => $file->webViewLink,
            'ukuran_file' => filesize($filePath),
            'format_file' => mime_content_type($filePath),
            'tanggal_dibuat' => !empty($exif['DateTimeOriginal']) ? date('Y-m-d H:i:s', strtotime($exif['DateTimeOriginal'])) : null,
            'kamera_merek' => $exif['Make'] ?? null,
            'kamera_model' => $exif['Model'] ?? null,
            'orientasi' => $exif['Orientation'] ?? null,
        ];

        if (!empty($exif['GPSLatitude']) && !empty($exif['GPSLongitude'])) {
            $dbData['gps_latitude'] = $this->convertGpsToDecimal($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
            $dbData['gps_longitude'] = $this->convertGpsToDecimal($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
            $dbData['gps_altitude'] = !empty($exif['GPSAltitude']) ? eval('return ' . $exif['GPSAltitude'] . ';') : null;
        }

        // --- PENAMBAHAN KODE BARU DIMULAI DI SINI ---
        if (!empty($dbData['gps_latitude']) && !empty($dbData['gps_longitude'])) {
            // Panggil fungsi baru untuk mendapatkan alamat dari koordinat
            $dbData['lokasi'] = $this->getAlamatDariKoordinat($dbData['gps_latitude'], $dbData['gps_longitude']);
        }
        // --- PENAMBAHAN KODE BARU SELESAI ---
        
        $fotoModel->save($dbData);
    }

    private function convertGpsToDecimal($dmsArray, $hemisphere)
    {
        $degrees = count($dmsArray) > 0 ? eval('return ' . $dmsArray[0] . ';') : 0;
        $minutes = count($dmsArray) > 1 ? eval('return ' . $dmsArray[1] . ';') : 0;
        $seconds = count($dmsArray) > 2 ? eval('return ' . $dmsArray[2] . ';') : 0;
        
        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);
        
        if ($hemisphere === 'S' || $hemisphere === 'W') {
            $decimal *= -1;
        }
        
        return $decimal;
    }

    // --- FUNGSI BARU UNTUK MENGAMBIL ALAMAT ---
    /**
     * Mengambil alamat fisik dari koordinat GPS menggunakan OpenStreetMap Nominatim API.
     * @param float $latitude
     * @param float $longitude
     * @return string|null Alamat lengkap atau null jika gagal.
     */
    private function getAlamatDariKoordinat($latitude, $longitude)
    {
        try {
            // Siapkan HTTP client
            $client = \Config\Services::curlrequest([
                'baseURI' => 'https://nominatim.openstreetmap.org/',
                'timeout' => 10, // Waktu tunggu 10 detik
            ]);

            // Buat request ke API Nominatim
            $response = $client->request('GET', 'reverse', [
                'query' => [
                    'format' => 'json',
                    'lat'    => $latitude,
                    'lon'    => $longitude,
                    'zoom'   => 18,
                    'addressdetails' => 1
                ],
                'headers' => [
                    // API Nominatim memerlukan User-Agent yang valid
                    'User-Agent' => 'AplikasiGeotagging/1.0 (' . base_url() . ')'
                ]
            ]);

            // Proses hasilnya jika request berhasil
            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody());
                if (isset($body->display_name)) {
                    return $body->display_name; // Kembalikan alamat lengkap
                }
            }
            return null; // Kembalikan null jika tidak ada alamat atau error

        } catch (\Exception $e) {
            log_message('error', 'Gagal melakukan Reverse Geocoding: ' . $e->getMessage());
            return null; // Kembalikan null jika terjadi exception
        }
    }
}

