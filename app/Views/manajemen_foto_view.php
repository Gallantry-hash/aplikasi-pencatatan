<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background-color: #f4f7f6;
        }

        #log-area {
            background-color: #212529;
            color: #fff;
            font-family: monospace;
            font-size: 0.85rem;
            height: 300px;
            overflow-y: scroll;
            padding: 10px;
            border-radius: 5px;
        }

        .modal-body ul {
            font-size: 0.9rem;
        }

        .file-input-btn {
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><i class="fas fa-camera"></i> Manajemen Foto</a>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Upload Foto Geotagging</h4>
                <p class="card-subtitle mb-3 text-muted">Pilih detail, username, dan pilih semua foto yang akan diupload. Field selain foto bersifat opsional.</p>

                <form id="uploadForm">
                    <?= csrf_field() ?>
                    <fieldset id="form-fieldset">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tahun" class="form-label"><b>Tahun</b></label>
                                <select class="form-select" id="tahun" name="tahun">
                                    <option value="" disabled selected>-- Pilih Tahun --</option>
                                    <option value="2025">2025</option>
                                    <option value="2026">2026</option>
                                    <option value="2027">2027</option>
                                </select>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="kategori" class="form-label"><b>Kategori</b></label>
                                <select class="form-select" id="kategori" name="kategori">
                                    <option value="" disabled selected>-- Pilih Kategori --</option>
                                    <option value="BIBIT PERSEMAIAN PERMANEN">BIBIT PERSEMAIAN PERMANEN</option>
                                    <option value="BIBIT PRODUKTIF">BIBIT PRODUKTIF</option>
                                    <option value="BIBIT KEBUN BIBIT RAKYAT (KBR)">BIBIT KEBUN BIBIT RAKYAT (KBR)</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3" id="subKategoriWrapper" style="display: none;">
                            <label for="sub_kategori" class="form-label"><b>Sub-Kategori Persemaian</b></label>
                            <select class="form-select" id="sub_kategori" name="sub_kategori">
                                <option value="PP Sungai Selamat">PP Sungai Selamat</option>
                                <option value="PP Nanga Pinoh">PP Nanga Pinoh</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label"><b>Username Pengirim</b></label>
                            <input type="text" class="form-control" name="username" id="username" placeholder="isi nama">
                        </div>
                        <div class="mb-3">
                            <label for="no_telepon" class="form-label"><b>Nomor Telepon Pengirim</b></label>
                            <input type="tel" class="form-control" name="no_telepon" id="no_telepon" placeholder="isi nomor telepon">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><b>Pilih Foto (Wajib)</b></label>
                            <div class="alert alert-warning p-2" role="alert">
                                <h5 class="alert-heading" style="font-size: 1rem;"><i class="fas fa-exclamation-triangle"></i> PENTING UNTUK PENGGUNA HP</h5>
                                <p class="mb-0" style="font-size: 0.9rem;">
                                    Gunakan < palla untuk mengambil foto langsung. Gunakan <strong>File Manager</strong> untuk mempertahankan <strong>nama file asli</strong> dan <strong>data GPS</strong>. <a href="#" data-bs-toggle="modal" data-bs-target="#filePickerModal">Lihat panduan</a>.
                                </p>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="galleryInput" class="btn btn-secondary w-100 file-input-btn">
                                        <i class="fas fa-camera"></i> Ambil dari Galeri/Kamera
                                        <input type="file" id="galleryInput" name="files[]" multiple accept="image/jpeg,image/png" capture="environment" style="display: none;">
                                    </label>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="fileManagerInput" class="btn btn-primary w-100 file-input-btn">
                                        <i class="fas fa-folder-open"></i> Pilih dari File Manager
                                        <input type="file" id="fileManagerInput" name="files[]" multiple accept="image/jpeg,image/png" style="display: none;">
                                    </label>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                    <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                        <i class="fas fa-upload"></i> Upload Sekarang
                    </button>
                </form>
            </div>
        </div>

        <div id="progress-section" class="mt-4" style="display: none;">
            <div class="card">
                <div class="card-body">
                    <h5>Progress Upload:</h5>
                    <div class="progress" role="progressbar">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" style="width: 0%">0%</div>
                    </div>
                    <div class="mt-2 text-center" id="progress-text"></div>
                    <div class="mt-3">
                        <strong>Log Proses:</strong>
                        <div id="log-area"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for File Picker Instructions -->
    <div class="modal fade" id="filePickerModal" tabindex="-1" aria-labelledby="filePickerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="filePickerModalLabel">Panduan Memilih Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Untuk memastikan <strong>nama file asli</strong> dan <strong>data GPS</strong> tetap terjaga, ikuti langkah-langkah berikut:</p>
                    <ul>
                        <li><strong>Tombol Galeri/Kamera:</strong> Gunakan untuk mengambil foto baru atau memilih dari galeri. Catatan: Foto dari galeri mungkin kehilangan data GPS.</li>
                        <li><strong>Tombol File Manager:</strong> Pilih opsi <strong>"File Manager"</strong>, <strong>"Files"</strong>, atau <strong>"Dokumen"</strong> untuk mempertahankan nama file asli dan data GPS.</li>
                        <li><strong>Pada Android:</strong> Pilih <strong>"Files"</strong> atau <strong>"File Manager"</strong>, bukan <strong>"Galeri"</strong> atau <strong>"Google Photos"</strong>.</li>
                        <li><strong>Pada iOS:</strong> Pilih <strong>"Browse"</strong> dari aplikasi Files, bukan <strong>"Photos"</strong>.</li>
                        <li>Jika tidak ada opsi File Manager, coba gunakan browser lain (Chrome/Firefox).</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const kategoriSelect = document.getElementById('kategori');
        const subKategoriWrapper = document.getElementById('subKategoriWrapper');
        kategoriSelect.addEventListener('change', function() {
            subKategoriWrapper.style.display = (this.value === 'BIBIT PERSEMAIAN PERMANEN') ? 'block' : 'none';
        });

        const uploadForm = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');
        const galleryInput = document.getElementById('galleryInput');
        const fileManagerInput = document.getElementById('fileManagerInput');
        const progressSection = document.getElementById('progress-section');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progress-text');
        const logArea = document.getElementById('log-area');
        const formFieldset = document.getElementById('form-fieldset');

        const addLog = (message, type = 'info') => {
            const color = type === 'error' ? 'text-danger' : (type === 'success' ? 'text-success' : '');
            logArea.innerHTML += `<div class="${color}">[${new Date().toLocaleTimeString()}] ${message}</div>`;
            logArea.scrollTop = logArea.scrollHeight;
        };

        // Combine both inputs into a single file list for submission
        let selectedFiles = [];

        [galleryInput, fileManagerInput].forEach(input => {
            input.addEventListener('change', function() {
                selectedFiles = Array.from(this.files);
                const source = this.id === 'galleryInput' ? 'Galeri/Kamera' : 'File Manager';
                if (this.id === 'galleryInput') {
                    addLog(`Peringatan: File dari ${source} mungkin kehilangan data GPS atau nama asli. Disarankan menggunakan File Manager.`, 'error');
                } else {
                    addLog(`File dipilih dari ${source}. Memeriksa nama file...`);
                    for (const file of selectedFiles) {
                        if (file.name.match(/^IMG_\d{8}_\d{6}/) || file.name.includes('Screenshot')) {
                            addLog(`Peringatan: File "${file.name}" mungkin berasal dari galeri. Pastikan memilih dari File Manager.`, 'error');
                        }
                    }
                }
                // Update the form data with selected files
                const formData = new FormData(uploadForm);
                selectedFiles.forEach((file, index) => {
                    formData.append(`files[${index}]`, file);
                });
            });
        });

        uploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            if (selectedFiles.length === 0) {
                alert('Silakan pilih file foto terlebih dahulu.');
                return;
            }

            const formData = new FormData(uploadForm);
            selectedFiles.forEach((file, index) => {
                formData.append('files', file);
            });

            formFieldset.disabled = true;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengupload...';
            progressSection.style.display = 'block';
            logArea.innerHTML = '';

            let filesUploaded = 0;
            let filesFailed = 0;
            const totalFiles = selectedFiles.length;

            addLog(`Memulai proses upload untuk ${totalFiles} foto...`);

            try {
                const response = await fetch('/manajemen-foto/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error(`Server merespon dengan status ${response.status}`);
                }

                const result = await response.json();

                if (result.status === 'success') {
                    result.results.forEach(res => {
                        if (res.status === 'success') {
                            filesUploaded++;
                            addLog(`(${filesUploaded}/${totalFiles}) Berhasil: ${res.fileName} diupload.`, 'success');
                        } else {
                            filesFailed++;
                            addLog(`(${filesUploaded}/${totalFiles}) GAGAL: ${res.fileName}. Pesan: ${res.message}`, 'error');
                        }
                    });
                } else {
                    filesFailed += totalFiles;
                    addLog(`GAGAL: ${result.message}`, 'error');
                }

            } catch (error) {
                filesFailed += totalFiles;
                addLog(`ERROR KRITIS: ${error.message}`, 'error');
            }

            const processedFiles = filesUploaded + filesFailed;
            const percentage = totalFiles > 0 ? Math.round((processedFiles / totalFiles) * 100) : 0;
            progressBar.style.width = percentage + '%';
            progressBar.textContent = percentage + '%';
            progressText.textContent = `Memproses ${processedFiles} dari ${totalFiles} foto...`;

            addLog('------------------------------------');
            addLog(`Semua proses selesai.`);
            addLog(`Berhasil: ${filesUploaded} foto.`, 'success');
            addLog(`Gagal: ${filesFailed} foto.`, 'error');

            formFieldset.disabled = false;
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Sekarang';
            selectedFiles = []; // Reset selected files
        });
    </script>
</body>
</html>