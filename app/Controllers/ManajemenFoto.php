<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PetugasModel;
use App\Models\FotoModel;
use ZipArchive;

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
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal terhubung ke Google Drive.']);
        }

        $files = $this->request->getFiles();
        if (empty($files['files'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'File tidak ditemukan dalam permintaan.']);
        }

        $file = $files['files'];
        $maxSize = 5 * 1024 * 1024; // 5 MB

        if ($file->getSize() > $maxSize) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal: Ukuran file ' . $file->getClientName() . ' melebihi batas maksimal 5 MB.']);
        }

        $username     = $this->request->getPost('username') ?: 'TIDAK DIKETAHUI';
        $no_telepon   = $this->request->getPost('no_telepon') ?: '0000';
        $tahun        = $this->request->getPost('tahun') ?: date('Y');
        $kategori     = $this->request->getPost('kategori') ?: 'TANPA KATEGORI';
        $sub_kategori = $this->request->getPost('sub_kategori');

        try {
            // Dapatkan ID Petugas
            $petugasModel = new PetugasModel();
            $petugas = $petugasModel->where('username', $username)->first() ?: $petugasModel->insert(['username' => $username, 'no_telepon' => $no_telepon]);
            $id_petugas = is_int($petugas) ? $petugas : ($petugas['id_petugas'] ?? null);

            // Buat struktur folder
            $pathArray = [$tahun, $kategori, $username];
            if ($kategori === 'BIBIT PERSEMAIAN PERMANEN' && !empty($sub_kategori)) {
                array_splice($pathArray, 2, 0, $sub_kategori);
            }
            $folderId = $this->getOrCreateNestedFolder($pathArray);
            if (!$folderId) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat struktur folder di Google Drive.']);
            }

            $fotoModel = new FotoModel();
            $originalName = $file->getClientName();

            // Logika baru untuk menangani tipe file yang berbeda
            $fileType = strtolower($file->getExtension());

            if (in_array($fileType, ['jpg', 'jpeg', 'png'])) {
                $result = $this->processAndUploadImage($file->getTempName(), $id_petugas, $fotoModel, $folderId, $originalName);
            } elseif ($fileType === 'pdf') {
                $result = $this->processAndUploadPdf($file->getTempName(), $id_petugas, $fotoModel, $folderId, $originalName);
            } elseif ($fileType === 'zip') {
                // Untuk ZIP, nama file yang dikembalikan adalah nama file ZIP itu sendiri
                return $this->processAndUploadZip($file->getTempName(), $id_petugas, $fotoModel, $folderId, $originalName);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => "Tipe file .{$fileType} tidak didukung."]);
            }
            
            if ($result['status'] === 'success') {
                return $this->response->setJSON(['status' => 'success', 'fileName' => $originalName]);
            } else {
                return $this->response->setJSON(['status' => 'error', 'message' => $result['message']]);
            }

        } catch (\Exception $e) {
            log_message('error', '[Upload Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return $this->response->setJSON(['status' => 'error', 'message' => 'Terjadi kesalahan fatal di server: ' . $e->getMessage()]);
        }
    }

    private function processAndUploadZip($zipPath, $id_petugas, $fotoModel, $driveFolderId, $zipFileName)
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== TRUE) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuka file ZIP.']);
        }

        $extractPath = WRITEPATH . 'uploads/' . uniqid('zip_');
        if (!is_dir($extractPath) && !mkdir($extractPath, 0777, true)) {
             return $this->response->setJSON(['status' => 'error', 'message' => 'Gagal membuat direktori sementara.']);
        }
        
        $zip->extractTo($extractPath);
        $zip->close();
        
        $processedCount = 0;
        $totalFilesInZip = 0;

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($extractPath, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $totalFilesInZip++;
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $this->processAndUploadImage($file->getPathname(), $id_petugas, $fotoModel, $driveFolderId, $file->getFilename());
                    $processedCount++;
                }
            }
        }
        
        // Hapus folder sementara setelah selesai
        $this->deleteDirectory($extractPath);
        
        $message = "File ZIP '{$zipFileName}' berhasil diproses. {$processedCount} dari {$totalFilesInZip} file gambar di dalamnya berhasil diupload.";
        return $this->response->setJSON(['status' => 'success', 'fileName' => $zipFileName, 'message' => $message]);
    }

    // Fungsi helper untuk menghapus direktori sementara
    private function deleteDirectory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return rmdir($dir);
    }

    private function processAndUploadPdf($filePath, $id_petugas, $fotoModel, $driveFolderId, $fileName)
    {
        if (empty($fileName)) {
            return ['status' => 'error', 'message' => 'Nama file PDF tidak valid.'];
        }

        try {
            $fileMetadata = new \Google_Service_Drive_DriveFile(['name' => $fileName, 'parents' => [$driveFolderId]]);
            $content = file_get_contents($filePath);
            $file = $this->drive->files->create($fileMetadata, [
                'data' => $content,
                'mimeType' => 'application/pdf',
                'uploadType' => 'multipart',
                'fields' => 'id, webViewLink'
            ]);
            
            $dbData = [
                'id_petugas' => $id_petugas,
                'nama_file' => $fileName,
                'path_file' => $file->webViewLink,
                'ukuran_file' => filesize($filePath),
                'format_file' => 'application/pdf',
            ];
            
            $fotoModel->save($dbData);
            return ['status' => 'success'];

        } catch (\Exception $e) {
            log_message('error', "Gagal memproses file PDF {$fileName}: " . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    // Fungsi processAndUploadImage, getOrCreateNestedFolder, findOrCreateFolder, _parseGpsExif, dan fungsi GPS lainnya tetap sama
    // ... (kode fungsi-fungsi ini dari versi sebelumnya disalin di sini)

    private function getOrCreateNestedFolder(array $pathArray)
    {
        try {
            $query = "name='geotaging' and mimeType='application/vnd.google-apps.folder' and 'root' in parents and trashed=false";
            $response = $this->drive->files->listFiles(['q' => $query, 'fields' => 'files(id)']);
            if (empty($response->getFiles())) {
                log_message('error', 'Folder utama "geotaging" tidak ditemukan di root Google Drive.');
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
            return ['status' => 'error', 'message' => 'Nama file tidak valid atau kosong.'];
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