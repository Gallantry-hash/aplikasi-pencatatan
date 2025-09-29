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
        
        $files = $this->request->getFiles();

        if (empty($files['files'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File foto tidak ditemukan dalam permintaan.']);
        }

        $username     = $this->request->getPost('username') ?: 'TIDAK DIKETAHUI';
        $no_telepon   = $this->request->getPost('no_telepon') ?: '0000';
        $tahun        = $this->request->getPost('tahun') ?: date('Y');
        $kategori     = $this->request->getPost('kategori') ?: 'TANPA KATEGORI';
        $sub_kategori = $this->request->getPost('sub_kategori');


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

            $pathArray = [$tahun, $kategori, $username];
            if ($kategori === 'BIBIT PERSEMAIAN PERMANEN' && !empty($sub_kategori)) {
                array_splice($pathArray, 2, 0, $sub_kategori);
            }

            $folderId = $this->getOrCreateNestedFolder($pathArray);
            if (!$folderId) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat struktur folder di Google Drive. Pastikan folder "geotaging" ada dan periksa log.']);
            }

            $fotoModel = new FotoModel();
            $processedCount = 0;
            
            $file = $files['files'];

            if ($file->isValid() && !$file->hasMoved()) {
                $result = $this->processAndUploadImage($file->getTempName(), $id_petugas, $fotoModel, $folderId, $file->getClientName());
                if ($result) {
                    $processedCount++;
                } else {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal memproses EXIF atau mengupload file: ' . $file->getClientName()]);
                }
            }
            
            return $this->response->setJSON(['status' => 'success', 'processed' => $processedCount, 'fileName' => $file->getClientName()]);
        } catch (\Exception $e) {
            log_message('error', '[Upload Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
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
        try {
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
                $lat = $this->convertGpsToDecimal($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
                $lon = $this->convertGpsToDecimal($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
                $alt = !empty($exif['GPSAltitude']) ? $this->_gpsToFloat($exif['GPSAltitude']) : null;
                
                // --- PERBAIKAN FINAL: Validasi menggunakan filter_var untuk keamanan maksimum ---
                $dbData['gps_latitude']  = filter_var($lat, FILTER_VALIDATE_FLOAT) ? $lat : null;
                $dbData['gps_longitude'] = filter_var($lon, FILTER_VALIDATE_FLOAT) ? $lon : null;
                $dbData['gps_altitude']  = filter_var($alt, FILTER_VALIDATE_FLOAT) ? $alt : null;
            }

            if (!empty($dbData['gps_latitude']) && !empty($dbData['gps_longitude'])) {
                $dbData['lokasi'] = $this->getAlamatDariKoordinat($dbData['gps_latitude'], $dbData['gps_longitude']);
            }
            
            $fotoModel->save($dbData);
            return true;

        } catch (\Exception $e) {
            log_message('error', "Gagal memproses file {$originalName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * --- FUNGSI BARU (LEBIH AMAN) ---
     * Mengonversi nilai pecahan dari EXIF (contoh: "7/1" atau "5591/100") menjadi angka float.
     * @param string|null $value Nilai dari EXIF
     * @return float|null Mengembalikan float jika valid, null jika tidak.
     */
    private function _gpsToFloat($value)
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        if (is_string($value) && strpos($value, '/') !== false) {
            list($numerator, $denominator) = explode('/', $value, 2);
            
            // Validasi bahwa keduanya adalah numerik
            if (!is_numeric($numerator) || !is_numeric($denominator)) {
                return null;
            }
            
            $numerator = (float) $numerator;
            $denominator = (float) $denominator;

            // Mencegah error "Division by zero"
            if ($denominator == 0) {
                return null;
            }

            return $numerator / $denominator;
        }

        return null;
    }


    /**
     * --- FUNGSI LAMA (DIPERBARUI TOTAL) ---
     * Fungsi yang jauh lebih tahan banting untuk mengonversi data GPS.
     */
    private function convertGpsToDecimal($dmsArray, $hemisphere)
    {
        // Pastikan inputnya adalah array
        if (!is_array($dmsArray)) {
            return null;
        }

        $degrees = count($dmsArray) > 0 ? $this->_gpsToFloat($dmsArray[0]) : 0;
        $minutes = count($dmsArray) > 1 ? $this->_gpsToFloat($dmsArray[1]) : 0;
        $seconds = count($dmsArray) > 2 ? $this->_gpsToFloat($dmsArray[2]) : 0;
        
        // Jika salah satu komponen gagal dikonversi, batalkan seluruh proses.
        if ($degrees === null || $minutes === null || $seconds === null) {
            return null;
        }

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);
        
        if ($hemisphere === 'S' || $hemisphere === 'W') {
            $decimal *= -1;
        }
        
        return $decimal;
    }
    
    private function getAlamatDariKoordinat($latitude, $longitude)
    {
        try {
            $client = \Config\Services::curlrequest([
                'baseURI' => 'https://nominatim.openstreetmap.org/',
                'timeout' => 10,
            ]);

            $response = $client->request('GET', 'reverse', [
                'query' => [
                    'format' => 'json',
                    'lat'    => $latitude,
                    'lon'    => $longitude,
                    'zoom'   => 18,
                    'addressdetails' => 1
                ],
                'headers' => [
                    'User-Agent' => 'AplikasiGeotagging/1.0 (' . base_url() . ')'
                ]
            ]);

            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody());
                if (isset($body->display_name)) {
                    return $body->display_name;
                }
            }
            return null;

        } catch (\Exception $e) {
            log_message('error', 'Gagal melakukan Reverse Geocoding: ' . $e->getMessage());
            return null;
        }
    }
}