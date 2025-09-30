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
                            <label for="files" class="form-label"><b>Pilih Foto (Wajib)</b></label>
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-mobile-alt"></i> <strong>Tips Pengguna HP:</strong> Untuk menjaga lokasi GPS, pilih foto dari <strong>"File Manager"</strong> atau <strong>"Dokumen"</strong>, bukan dari "Galeri".
                            </div>
                            <input class="form-control" type="file" name="files[]" id="files" multiple required accept="image/jpeg,image/png">
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

    <script>
        const kategoriSelect = document.getElementById('kategori');
        const subKategoriWrapper = document.getElementById('subKategoriWrapper');
        kategoriSelect.addEventListener('change', function() {
            subKategoriWrapper.style.display = (this.value === 'BIBIT PERSEMAIAN PERMANEN') ? 'block' : 'none';
        });

        const uploadForm = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');
        const filesInput = document.getElementById('files');
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

        uploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const files = Array.from(filesInput.files);
            const totalFiles = files.length;

            if (totalFiles === 0) {
                alert('Silakan pilih file foto terlebih dahulu.');
                return;
            }

            const formDataBase = new FormData(uploadForm);
            
            formFieldset.disabled = true;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengupload...';
            progressSection.style.display = 'block';
            logArea.innerHTML = '';

            let filesUploaded = 0;
            let filesFailed = 0;

            addLog(`Memulai proses upload untuk ${totalFiles} foto...`);

            for (const file of files) {
                const formData = new FormData();
                // Salin semua data dari form utama, KECUALI field file
                for (const [key, value] of formDataBase.entries()) {
                    if (key !== 'files[]') {
                        formData.append(key, value);
                    }
                }
                // Tambahkan satu file untuk request ini
                formData.append('files', file);

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
                        filesUploaded++;
                        addLog(`(${filesUploaded}/${totalFiles}) Berhasil: ${result.fileName} diupload.`, 'success');
                    } else {
                        filesFailed++;
                        addLog(`(${filesUploaded}/${totalFiles}) GAGAL: ${file.name}. Pesan: ${result.message}`, 'error');
                    }

                } catch (error) {
                    filesFailed++;
                    addLog(`(${filesUploaded}/${totalFiles}) ERROR KRITIS saat mengirim ${file.name}: ${error.message}`, 'error');
                }

                const processedFiles = filesUploaded + filesFailed;
                const percentage = totalFiles > 0 ? Math.round((processedFiles / totalFiles) * 100) : 0;
                progressBar.style.width = percentage + '%';
                progressBar.textContent = percentage + '%';
                progressText.textContent = `Memproses ${processedFiles} dari ${totalFiles} foto...`;
            }

            addLog('------------------------------------');
            addLog(`Semua proses selesai.`);
            addLog(`Berhasil: ${filesUploaded} foto.`, 'success');
            addLog(`Gagal: ${filesFailed} foto.`, 'error');

            formFieldset.disabled = false;
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Sekarang';
        });
    </script>
</body>

</html>