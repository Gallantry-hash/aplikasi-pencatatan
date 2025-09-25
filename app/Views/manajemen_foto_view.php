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
            height: 200px;
            overflow-y: scroll;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark bg-dark shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-camera"></i>
                Manajemen Foto
            </a>
            <a href="/logout" class="btn btn-outline-light">Logout</a>
        </div>
    </nav>

    <div class="container my-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Upload Foto Geotagging Baru</h4>
                <p class="card-subtitle mb-3 text-muted">Pilih tujuan upload dan masukkan username Anda.</p>

                <form id="uploadForm">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tahun" class="form-label"><b>Tahun</b></label>
                            <select class="form-select" id="tahun" name="tahun" required>
                                <option value="" disabled selected>-- Pilih Tahun --</option>
                                <option value="2025">2025</option>
                                <option value="2026">2026</option>
                                <option value="2027">2027</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="kategori" class="form-label"><b>Kategori</b></label>
                            <select class="form-select" id="kategori" name="kategori" required>
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
                        <label for="username" class="form-label"><b>Username Petugas</b></label>
                        <input type="text" class="form-control" name="username" id="username" required>
                    </div>

                    <div class="mb-3">
                        <label for="no_telepon" class="form-label"><b>Nomor Telepon</b></label>
                        <input type="tel" class="form-control" name="no_telepon" id="no_telepon" required>
                    </div>

                    <div class="mb-3">
                        <label for="files" class="form-label"><b>Pilih Foto</b></label>
                        <input class="form-control" type="file" name="files[]" id="files" multiple required accept="image/jpeg,image/png">
                    </div>

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
        const subKategoriSelect = document.getElementById('sub_kategori');

        kategoriSelect.addEventListener('change', function() {
            if (this.value === 'BIBIT PERSEMAIAN PERMANEN') {
                subKategoriWrapper.style.display = 'block';
                subKategoriSelect.required = true;
            } else {
                subKategoriWrapper.style.display = 'none';
                subKategoriSelect.required = false;
            }
        });

        const uploadForm = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');
        const filesInput = document.getElementById('files');
        const usernameInput = document.getElementById('username');
        const tahunSelect = document.getElementById('tahun');
        const progressSection = document.getElementById('progress-section');
        const progressBar = document.getElementById('progressBar');
        const logArea = document.getElementById('log-area');
        const noTeleponInput = document.getElementById('no_telepon');

        uploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const username = usernameInput.value;
            const files = Array.from(filesInput.files);
            const tahun = tahunSelect.value;
            const kategori = kategoriSelect.value;
            const sub_kategori = subKategoriSelect.value;
            const no_telepon = noTeleponInput.value;
            const totalFiles = files.length;
            const batchSize = 50;

            if (!username || totalFiles === 0 || !tahun || !kategori) {
                alert('Semua pilihan filter, username, dan file harus diisi.');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Mengupload...';
            progressSection.style.display = 'block';
            logArea.innerHTML = '';

            let filesUploaded = 0;

            for (let i = 0; i < totalFiles; i += batchSize) {
                const batch = files.slice(i, i + batchSize);
                const formData = new FormData();
                // Tambahkan semua data form ke FormData
                formData.append('username', username);
                formData.append('no_telepon', no_telepon);
                formData.append('tahun', tahun);
                formData.append('kategori', kategori);
                if (kategori === 'BIBIT PERSEMAIAN PERMANEN' && sub_kategori) {
                    formData.append('sub_kategori', sub_kategori);
                }
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

                batch.forEach(file => {
                    formData.append('files[]', file);
                });

                try {
                    addLog(`Mengirim antrian ${i / batchSize + 1} (${batch.length} foto)...`);
                    const response = await fetch('/manajemen-foto/upload', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();

                    if (result.status === 'success') {
                        filesUploaded += result.processed;
                        const percentage = Math.round((filesUploaded / totalFiles) * 100);
                        progressBar.style.width = percentage + '%';
                        progressBar.textContent = percentage + '%';
                        addLog(`> Berhasil: ${result.processed} foto diproses. Total terupload: ${filesUploaded}/${totalFiles}`);
                    } else {
                        addLog(`> Gagal: ${result.message}. Menghentikan proses.`);
                        throw new Error(result.message);
                    }
                } catch (error) {
                    addLog(`> Terjadi error kritis: ${error.message}`);
                    break;
                }
            }
            submitBtn.disabled = false;
            submitBtn.textContent = 'Upload Selesai';
            addLog('Semua proses antrian selesai.');
        });

        function addLog(message) {
            logArea.innerHTML += `<div>[${new Date().toLocaleTimeString()}] ${message}</div>`;
            logArea.scrollTop = logArea.scrollHeight;
        }
    </script>
</body>

</html>