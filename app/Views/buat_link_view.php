<?= $this->include('layout/header') ?>

<div class="fade-in">
    <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-4">Buat Link Publik</h1>
    <p class="text-gray-600 mb-6">
        Gunakan link di bawah ini untuk dibagikan kepada petugas atau pihak eksternal yang akan mengupload foto.
        Link ini akan mengarahkan mereka ke halaman verifikasi token sebelum bisa mengakses form upload.
    </p>
    <hr class="mb-6 border-gray-300">

    <div class="bg-white p-6 rounded-lg shadow">
        <label for="public-link" class="block text-sm font-medium text-gray-700 mb-2">Link Halaman Upload:</label>
        <div class="flex items-center space-x-2">
            <input type="text" id="public-link" value="<?= esc($upload_link) ?>" readonly
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
            
            <button id="copy-btn"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition transform hover:scale-105">
                <i class="fas fa-copy mr-2"></i>Salin
            </button>
        </div>
        <div id="copy-feedback" class="text-green-600 text-sm mt-2 h-4"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const copyBtn = document.getElementById('copy-btn');
        const publicLinkInput = document.getElementById('public-link');
        const copyFeedback = document.getElementById('copy-feedback');

        copyBtn.addEventListener('click', function() {
            // Pilih teks di dalam input
            publicLinkInput.select();
            publicLinkInput.setSelectionRange(0, 99999); // Untuk mobile

            try {
                // Gunakan Clipboard API modern
                navigator.clipboard.writeText(publicLinkInput.value).then(function() {
                    showFeedback('Link berhasil disalin!');
                }, function(err) {
                    // Fallback untuk browser lama
                    document.execCommand('copy');
                    showFeedback('Link berhasil disalin!');
                });
            } catch (err) {
                // Fallback jika Clipboard API tidak didukung
                document.execCommand('copy');
                showFeedback('Link berhasil disalin!');
            }
        });

        function showFeedback(message) {
            copyFeedback.textContent = message;
            copyBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Tersalin!';
            copyBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            copyBtn.classList.add('bg-green-500');

            setTimeout(function() {
                copyFeedback.textContent = '';
                copyBtn.innerHTML = '<i class="fas fa-copy mr-2"></i>Salin';
                copyBtn.classList.remove('bg-green-500');
                copyBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
            }, 2000);
        }
    });
</script>

<?= $this->include('layout/footer') ?>
