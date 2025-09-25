<?php
// Variabel ini sudah otomatis dikirim dari Controller
$photos = $photos ?? [];
$pager = $pager ?? null;
$filterPetugas = $filterPetugas ?? '';
$currentPage = $pager ? $pager->getCurrentPage() : 1;
$perPage = $pager ? $pager->getPerPage() : 5;
$nomorAwal = (($currentPage - 1) * $perPage) + 1;
?>

<?= $this->include('layout/header') ?>

<div class="space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                    Dashboard Utama
                </h1>
                <p class="text-gray-600 mt-1">Selamat Datang, Admin! Kelola sistem dengan mudah.</p>
            </div>
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-xl font-semibold text-gray-800">Daftar Seluruh Foto</h3>
            <p class="text-gray-600 text-sm mt-1">Semua foto yang telah diupload ke sistem</p>

            <form action="<?= current_url() ?>" method="GET" class="mt-4 flex flex-col sm:flex-row items-center gap-3">
                <div class="relative w-full sm:w-auto flex-grow">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="petugas" placeholder="Cari berdasarkan nama petugas..." value="<?= esc($filterPetugas) ?>" class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg w-full focus:ring-2 focus:ring-primary-500">
                </div>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    <i class="fas fa-filter mr-2"></i>Filter
                </button>
                <a href="<?= base_url('/admin/dashboard') ?>" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    Reset
                </a>
                <button type="button" id="export-btn" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors">
                    <i class="fas fa-download mr-2"></i>Export CSV
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Foto</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Petugas</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Koordinat</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Alamat Lokasi</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (!empty($photos)): ?>
                        <?php $i = $nomorAwal; ?>
                        <?php foreach ($photos as $photo): ?>
                            <tr class="hover:bg-gray-25 transition-colors group">
                                <td class="px-6 py-4"><span class="text-sm font-medium text-gray-700"><?= $i++ ?></span></td>
                                <td class="px-6 py-4"><a href="<?= $photo['path_file'] ?>" target="_blank"><img src="<?= $photo['path_file'] ?>" alt="Foto" class="w-16 h-16 rounded-xl object-cover border border-gray-200 hover:scale-105 transition-transform"></a></td>
                                <td class="px-6 py-4"><span class="text-sm font-medium text-gray-900"><?= esc($photo['username']) ?></span></td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700 font-mono">
                                        <div><?= esc(number_format((float)($photo['gps_latitude'] ?? 0), 5)) ?></div>
                                        <div class="text-gray-500"><?= esc(number_format((float)($photo['gps_longitude'] ?? 0), 5)) ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-700 max-w-xs truncate"><?= esc($photo['lokasi'] ?? 'N/A') ?></p>
                                </td>
                                <td class="px-6 py-4"><?php if (!empty($photo['gps_latitude']) && !empty($photo['gps_longitude'])): ?><a href="http://googleusercontent.com/maps.google.com/5<?= $photo['gps_latitude'] ?>,<?= $photo['gps_longitude'] ?>" target="_blank" class="inline-flex items-center px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 text-sm"><i class="fas fa-map-marker-alt mr-2"></i>Lihat Peta</a><?php else: ?><span class="px-3 py-2 text-xs bg-gray-100 text-gray-400 rounded-lg">Tidak tersedia</span><?php endif; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <h3 class="text-lg font-medium text-gray-700">Data Tidak Ditemukan</h3>
                                <p class="text-gray-500">Tidak ada data foto yang cocok dengan filter pencarian Anda.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-gray-100">
            <?php if ($pager): ?>
                <?= $pager->links('default', 'tailwind') ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const exportBtn = document.getElementById('export-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', function(event) {
                // 1. Mencegah aksi default tombol
                event.preventDefault();

                // 2. Minta input dari pengguna dengan dialog prompt
                const userInput = prompt("Masukkan nama awalan untuk file CSV:", "LAPORAN");

                // 3. Jika pengguna menekan "Cancel" atau tidak mengisi apa-apa, hentikan proses
                if (userInput === null || userInput.trim() === "") {
                    return;
                }

                // 4. Ambil parameter filter yang sedang aktif dari URL
                const currentQueryString = window.location.search;
                const params = new URLSearchParams(currentQueryString);

                // Hapus parameter 'page' agar semua data diekspor
                params.delete('page');

                // 5. Tambahkan input pengguna sebagai parameter 'prefix'
                params.set('prefix', userInput);

                // 6. Buat URL unduhan yang final
                const baseUrl = '<?= base_url('/admin/export') ?>';
                const finalUrl = baseUrl + '?' + params.toString();

                // 7. Arahkan browser ke URL unduhan untuk memulai download
                window.location.href = finalUrl;
            });
        }
    });
</script>

<?= $this->include('layout/footer') ?>


<!-- <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                    Dashboard Utama
                </h1>
                <p class="text-gray-600 mt-1">Selamat Datang, Admin! Kelola sistem dengan mudah.</p>
            </div>
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-line text-white"></i>
                </div>
            </div>
        </div>
    </div> -->