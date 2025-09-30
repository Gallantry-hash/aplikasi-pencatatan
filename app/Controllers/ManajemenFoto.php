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
                // --- SOLUSI NAMA FILE: Kembali menggunakan nama asli dari client ---
                $originalName = $file->getClientName();

                $result = $this->processAndUploadImage($file->getTempName(), $id_petugas, $fotoModel, $folderId, $originalName);

                if ($result['status'] === 'success') {
                    $processedCount++;
                } else {
                    return $this->response->setJSON(['status' => 'error', 'message' => $result['message']]);
                }
            }

            return $this->response->setJSON(['status' => 'success', 'processed' => $processedCount, 'fileName' => $originalName ?? 'N/A']);
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

    private function processAndUploadImage($filePath, $id_petugas, $fotoModel, $driveFolderId, $fileName)
    {
        if (empty($fileName)) {
            return ['status' => 'error', 'message' => 'Nama file tidak valid atau tidak terbaca.'];
        }

        try {
            $exif = @exif_read_data($filePath);

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

            // Gunakan fungsi helper yang sangat aman untuk mem-parse GPS
            $gpsData = $this->_parseGpsExif($exif);
            if ($gpsData) {
                $dbData['gps_latitude']  = $gpsData['lat'];
                $dbData['gps_longitude'] = $gpsData['lon'];
                $dbData['lokasi'] = $this->getAlamatDariKoordinat($gpsData['lat'], $gpsData['lon']);
            }

            $fotoModel->save($dbData);
            return ['status' => 'success'];
        } catch (\Exception $e) {
            log_message('error', "Gagal memproses file {$fileName}: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function _parseGpsExif($exif)
    {
        if (empty($exif['GPSLatitude']) || empty($exif['GPSLongitude']) || empty($exif['GPSLatitudeRef']) || empty($exif['GPSLongitudeRef'])) {
            return null;
        }

        $lat = $this->convertGpsToDecimal($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
        $lon = $this->convertGpsToDecimal($exif['GPSLongitude'], $exif['GPSLongitudeRef']);

        if ($lat !== null && $lon !== null && is_finite($lat) && is_finite($lon)) {
            return ['lat' => $lat, 'lon' => $lon];
        }

        return null;
    }

    private function _gpsToFloat($value)
    {
        if ($value === null) return null;
        if (is_numeric($value)) return (float) $value;

        if (is_string($value) && preg_match('/^([0-9\.]+)\/([0-9\.]+)$/', $value, $matches)) {
            $numerator = (float) $matches[1];
            $denominator = (float) $matches[2];
            if ($denominator != 0) {
                return $numerator / $denominator;
            }
        }
        return null;
    }

    private function convertGpsToDecimal($dmsArray, $hemisphere)
    {
        if (!is_array($dmsArray) || count($dmsArray) !== 3) {
            return null;
        }

        $degrees = $this->_gpsToFloat($dmsArray[0]);
        $minutes = $this->_gpsToFloat($dmsArray[1]);
        $seconds = $this->_gpsToFloat($dmsArray[2]);

        if ($degrees === null || $minutes === null || $seconds === null) {
            return null;
        }

        $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

        if (strtoupper($hemisphere) === 'S' || strtoupper($hemisphere) === 'W') {
            $decimal *= -1;
        }

        return is_finite($decimal) ? $decimal : null;
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
