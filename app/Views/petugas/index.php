<?= $this->include('layout/header') ?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                Kelola Petugas
            </h1>
            <p class="text-gray-600 mt-1">Manajemen data petugas dan akses sistem</p>
        </div>
        <button id="btn-tambah"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-xl hover:from-primary-700 hover:to-primary-800 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105 font-semibold">
            <i class="fas fa-plus mr-2"></i>
            Tambah Petugas
        </button>
    </div>

    <div id="alert-placeholder"></div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-semibold text-gray-800">Daftar Petugas</h3>
                    <p class="text-gray-600 text-sm mt-1">Kelola akses dan informasi petugas</p>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="relative">
                        <input type="text"
                            id="searchInput"
                            placeholder="Cari petugas..."
                            class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Petugas</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php if (!empty($petugas)): ?>
                        <?php foreach ($petugas as $p): ?>
                            <tr id="row-petugas-<?= $p['id_petugas'] ?>" class="hover:bg-gray-25 transition-colors group">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-600 font-mono text-sm">#<?= str_pad($p['id_petugas'], 2, '0', STR_PAD_LEFT) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-gradient-to-r from-primary-500 to-primary-600 rounded-xl flex items-center justify-center mr-4">
                                            <span class="text-white font-bold text-lg">
                                                <?= strtoupper(substr(esc($p['username']), 0, 1)) ?>
                                            </span>
                                        </div>
                                        <div>
                                            <h4 class="text-sm font-semibold text-gray-900"><?= esc($p['username']) ?></h4>
                                            <p class="text-sm text-gray-500">Petugas Lapangan</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center text-sm text-gray-700">
                                        <i class="fas fa-phone text-gray-400 mr-2"></i>
                                        <?= esc($p['no_telepon']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                        <i class="fas fa-circle text-emerald-500 mr-1" style="font-size: 6px;"></i>
                                        Aktif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <button class="btn-edit inline-flex items-center px-3 py-2 text-sm bg-amber-50 text-amber-700 rounded-lg hover:bg-amber-100 transition-colors group" data-id="<?= $p['id_petugas'] ?>">
                                            <i class="fas fa-edit mr-1 group-hover:scale-110 transition-transform"></i>
                                            Edit
                                        </button>
                                        <button class="btn-hapus inline-flex items-center px-3 py-2 text-sm bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors group" data-id="<?= $p['id_petugas'] ?>">
                                            <i class="fas fa-trash mr-1 group-hover:scale-110 transition-transform"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="fas fa-users text-gray-400 text-2xl"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-700 mb-2">Belum ada petugas</h3>
                                    <p class="text-gray-500 mb-4">Tambahkan petugas pertama untuk memulai</p>
                                    <button class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                        <i class="fas fa-plus mr-2"></i>
                                        Tambah Petugas
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="petugas-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform transition-all duration-300 scale-95 opacity-0" id="modal-content">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <div>
                <h3 class="text-xl font-bold text-gray-800" id="modal-title">Tambah Petugas</h3>
                <p class="text-gray-600 text-sm mt-1">Masukkan informasi petugas baru</p>
            </div>
            <button id="modal-close" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                <i class="fas fa-times text-gray-400"></i>
            </button>
        </div>

        <div class="p-6">
            <form id="petugas-form" class="space-y-6">
                <?= csrf_field() ?>
                <input type="hidden" name="id_petugas" id="id_petugas">

                <div class="space-y-2">
                    <label for="username" class="block text-sm font-semibold text-gray-700">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text"
                            id="username"
                            name="username"
                            class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200"
                            placeholder="Masukkan username"
                            required>
                    </div>
                </div>

                <div class="space-y-2">
                    <label for="no_telepon" class="block text-sm font-semibold text-gray-700">Nomor Telepon</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-phone text-gray-400"></i>
                        </div>
                        <input type="tel"
                            id="no_telepon"
                            name="no_telepon"
                            class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-all duration-200"
                            placeholder="Masukkan nomor telepon"
                            required>
                    </div>
                </div>
            </form>
        </div>

        <div class="flex items-center justify-end space-x-3 p-6 border-t border-gray-100">
            <button type="button"
                id="modal-batal"
                class="px-6 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                Batal
            </button>
            <button type="submit"
                form="petugas-form"
                class="px-6 py-2 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-lg hover:from-primary-700 hover:to-primary-800 transition-all duration-200 shadow-lg hover:shadow-xl">
                Simpan
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('petugas-modal');
        const modalContent = document.getElementById('modal-content');
        const btnTambah = document.getElementById('btn-tambah');
        const modalClose = document.getElementById('modal-close');
        const modalBatal = document.getElementById('modal-batal');
        const petugasForm = document.getElementById('petugas-form');
        const modalTitle = document.getElementById('modal-title');
        
        // --- FITUR PENCARIAN ---
        const searchInput = document.getElementById('searchInput');
        const tableBody = document.querySelector('tbody');
        const tableRows = tableBody.querySelectorAll('tr');

        searchInput.addEventListener('keyup', function() {
            const searchTerm = searchInput.value.toLowerCase();

            tableRows.forEach(row => {
                // Jangan sembunyikan baris 'Belum ada petugas'
                if (row.querySelector('td[colspan="5"]')) {
                    return;
                }

                // Ambil teks dari kolom username dan kontak
                const username = row.cells[1].textContent.toLowerCase();
                const contact = row.cells[2].textContent.toLowerCase();

                // Cek apakah teks cocok dengan kata kunci pencarian
                if (username.includes(searchTerm) || contact.includes(searchTerm)) {
                    row.style.display = ''; // Tampilkan baris jika cocok
                } else {
                    row.style.display = 'none'; // Sembunyikan baris jika tidak cocok
                }
            });
        });
        // --- AKHIR FITUR PENCARIAN ---

        function openModal() {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModal() {
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        // Open modal for adding new officer
        btnTambah.addEventListener('click', () => {
            petugasForm.reset();
            document.getElementById('id_petugas').value = '';
            modalTitle.textContent = 'Tambah Petugas Baru';
            petugasForm.action = '/kelola-petugas';
            openModal();
        });

        // Event delegation for Edit and Delete buttons
        document.querySelector('tbody').addEventListener('click', async (e) => {
            const btnEdit = e.target.closest('.btn-edit');
            const btnHapus = e.target.closest('.btn-hapus');

            if (btnEdit) {
                const id = btnEdit.dataset.id;
                try {
                    const response = await fetch(`/kelola-petugas/edit/${id}`);
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

                    const data = await response.json();
                    petugasForm.reset();
                    document.getElementById('id_petugas').value = data.id_petugas;
                    document.getElementById('username').value = data.username;
                    document.getElementById('no_telepon').value = data.no_telepon;

                    modalTitle.textContent = 'Edit Data Petugas';
                    petugasForm.action = `/kelola-petugas/update/${id}`;
                    openModal();
                } catch (error) {
                    showModernAlert('Gagal memuat data petugas.', 'error');
                }
            }

            if (btnHapus) {
                const id = btnHapus.dataset.id;
                if (confirm('Apakah Anda yakin ingin menghapus petugas ini?')) {
                    const csrfToken = document.querySelector('input[name=csrf_test_name]').value;

                    try {
                        const response = await fetch(`/kelola-petugas/delete/${id}`, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: `csrf_test_name=${csrfToken}`
                        });

                        const result = await response.json();
                        showModernAlert(result.message, result.status);
                        if (result.status === 'success') {
                            setTimeout(() => location.reload(), 1500);
                        }
                    } catch (error) {
                        showModernAlert('Terjadi kesalahan saat menghapus data.', 'error');
                    }
                }
            }
        });

        // Form submission
        petugasForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(petugasForm);
            const actionUrl = petugasForm.action;

            try {
                const response = await fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();
                showModernAlert(result.message, result.status);

                if (result.status === 'success') {
                    closeModal();
                    setTimeout(() => location.reload(), 1500);
                }
            } catch (error) {
                showModernAlert('Terjadi kesalahan. Periksa input Anda.', 'error');
            }
        });

        // Close modal events
        modalClose.addEventListener('click', closeModal);
        modalBatal.addEventListener('click', closeModal);

        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
    });
</script>

<?= $this->include('layout/footer') ?>