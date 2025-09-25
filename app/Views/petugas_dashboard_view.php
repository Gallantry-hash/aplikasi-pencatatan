<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .fade-in {
            animation: fadeIn 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .nav-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .floating-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
            z-index: -1;
        }

        .floating-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            animation: float 25s infinite linear;
        }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); }
            100% { transform: translateY(-100px) rotate(360deg); }
        }

        .profile-gradient {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .stats-card {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 51, 234, 0.1) 100%);
            border: 1px solid rgba(59, 130, 246, 0.2);
        }
    </style>
</head>
<body>
    <!-- Floating Background Elements -->
    <div class="floating-background">
        <div class="floating-circle w-24 h-24" style="left: 15%; animation-delay: -8s;"></div>
        <div class="floating-circle w-16 h-16" style="left: 75%; animation-delay: -3s;"></div>
        <div class="floating-circle w-32 h-32" style="left: 45%; animation-delay: -12s;"></div>
        <div class="floating-circle w-20 h-20" style="left: 85%; animation-delay: -18s;"></div>
    </div>

    <!-- Navigation -->
    <nav class="glass-card shadow-lg border-b border-white/20 sticky top-0 z-40">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 profile-gradient rounded-lg flex items-center justify-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-lg text-gray-800">
                            Dashboard Petugas
                        </h1>
                        <p class="text-sm text-gray-600">
                            <strong><?= esc(session()->get('username')) ?></strong>
                            <span class="ml-2 text-gray-500">(<?= esc($no_telepon ?? '') ?>)</span>
                        </p>
                    </div>
                </div>
                <a href="/logout" class="inline-flex items-center px-4 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors border border-red-200">
                    <i class="fas fa-sign-out-alt mr-2"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container mx-auto p-4 sm:p-6 lg:p-8 max-w-6xl">
        <!-- Welcome Section -->
        <div class="glass-card rounded-2xl shadow-2xl mb-8 fade-in hover-lift">
            <div class="p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between">
                    <div class="flex items-center space-x-4 mb-4 sm:mb-0">
                        <div class="w-16 h-16 profile-gradient rounded-2xl flex items-center justify-center shadow-lg">
                            <span class="text-white font-bold text-2xl">
                                <?= strtoupper(substr(esc(session()->get('username')), 0, 1)) ?>
                            </span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold bg-gradient-to-r from-gray-800 to-gray-600 bg-clip-text text-transparent">
                                Selamat Datang, <?= esc(session()->get('username')) ?>
                            </h2>
                            <p class="text-gray-600 mt-1">Kelola dan pantau foto yang telah Anda upload</p>
                        </div>
                    </div>
                    <div class="stats-card rounded-xl p-4 text-center">
                        <div class="text-2xl font-bold text-primary-700"><?= count($photos ?? []) ?></div>
                        <div class="text-sm text-gray-600">Total Foto</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 fade-in">
            <div class="glass-card rounded-2xl p-6 hover-lift">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Total Upload</h3>
                        <p class="text-3xl font-bold text-primary-600 mt-2"><?= count($photos ?? []) ?></p>
                        <p class="text-gray-600 text-sm mt-1">Foto terupload</p>
                    </div>
                    <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-images text-primary-600"></i>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-6 hover-lift">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Dengan GPS</h3>
                        <p class="text-3xl font-bold text-emerald-600 mt-2">
                            <?= count(array_filter($photos ?? [], function($p) { 
                                return !empty($p['gps_latitude']) && !empty($p['gps_longitude']); 
                            })) ?>
                        </p>
                        <p class="text-gray-600 text-sm mt-1">Lokasi tersedia</p>
                    </div>
                    <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-map-marker-alt text-emerald-600"></i>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-6 hover-lift">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Bulan Ini</h3>
                        <p class="text-3xl font-bold text-purple-600 mt-2">
                            <?= count(array_filter($photos ?? [], function($p) { 
                                return date('Y-m', strtotime($p['tanggal_upload'])) === date('Y-m'); 
                            })) ?>
                        </p>
                        <p class="text-gray-600 text-sm mt-1">Upload terbaru</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photos Table -->
        <div class="glass-card rounded-2xl shadow-2xl fade-in">
            <div class="p-6 border-b border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-800">Riwayat Upload Foto</h3>
                        <p class="text-gray-600 text-sm mt-1">Semua foto yang telah Anda upload ke sistem</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="relative">
                            <input type="text" 
                                   placeholder="Cari foto..." 
                                   id="searchInput"
                                   class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 text-sm bg-white/70 backdrop-blur-sm">
                            <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 text-sm"></i>
                        </div>
                        <button class="px-4 py-2 bg-primary-50 text-primary-700 rounded-lg hover:bg-primary-100 transition-colors">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" id="photosTable">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Foto</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Nama File</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Tanggal Upload</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Lokasi</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php if (!empty($photos) && is_array($photos)): ?>
                            <?php $i = 1; ?>
                            <?php foreach($photos as $photo): ?>
                            <tr class="hover:bg-gray-25 transition-colors group photo-row">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-700"><?= $i++ ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="relative group">
                                        <a href="<?= $photo['path_file'] ?>" target="_blank" class="block">
                                            <div class="w-16 h-16 rounded-xl overflow-hidden shadow-sm border border-gray-200 group-hover:shadow-lg transition-all duration-200">
                                                <img src="<?= $photo['path_file'] ?>" alt="foto" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-200">
                                            </div>
                                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 rounded-xl transition-all duration-200 flex items-center justify-center">
                                                <i class="fas fa-expand text-white opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                            </div>
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="max-w-xs">
                                        <p class="text-sm font-medium text-gray-900 truncate"><?= esc($photo['nama_file']) ?></p>
                                        <p class="text-xs text-gray-500">Foto Geotagging</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-700">
                                        <div><?= date('d M Y', strtotime($photo['tanggal_upload'])) ?></div>
                                        <div class="text-xs text-gray-500"><?= date('H:i', strtotime($photo['tanggal_upload'])) ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if (!empty($photo['gps_latitude']) && !empty($photo['gps_longitude'])): ?>
                                        <div class="flex items-center text-sm text-gray-700">
                                            <i class="fas fa-map-pin text-emerald-500 mr-2"></i>
                                            <div>
                                                <div class="font-mono text-xs"><?= number_format((float)$photo['gps_latitude'], 4) ?></div>
                                                <div class="font-mono text-xs text-gray-500"><?= number_format((float)$photo['gps_longitude'], 4) ?></div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-500">
                                            <i class="fas fa-map-pin mr-1"></i>
                                            Tidak tersedia
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <?php if (!empty($photo['gps_latitude']) && !empty($photo['gps_longitude'])): ?>
                                            <a href="https://www.google.com/maps?q=<?= $photo['gps_latitude'] ?>,<?= $photo['gps_longitude'] ?>" 
                                               class="inline-flex items-center px-3 py-2 bg-primary-50 text-primary-700 rounded-lg hover:bg-primary-100 transition-colors group text-sm" 
                                               target="_blank">
                                                <i class="fas fa-map-marker-alt mr-2 group-hover:scale-110 transition-transform"></i>
                                                Lihat Peta
                                            </a>
                                        <?php else: ?>
                                            <span class="px-3 py-2 text-xs bg-gray-100 text-gray-400 rounded-lg">GPS tidak tersedia</span>
                                        <?php endif; ?>
                                        <a href="<?= $photo['path_file'] ?>" 
                                           download
                                           class="inline-flex items-center px-3 py-2 bg-emerald-50 text-emerald-700 rounded-lg hover:bg-emerald-100 transition-colors text-sm">
                                            <i class="fas fa-download mr-2"></i>
                                            Download
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="emptyState">
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                            <i class="fas fa-images text-gray-400 text-2xl"></i>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-700 mb-2">Belum ada foto</h3>
                                        <p class="text-gray-500 mb-4">Anda belum mengupload foto apapun</p>
                                        <a href="/manajemen-foto" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                            <i class="fas fa-plus mr-2"></i>
                                            Upload Foto Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.photo-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const fileName = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const uploadDate = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                
                if (fileName.includes(searchTerm) || uploadDate.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide empty state
            const emptyState = document.getElementById('emptyState');
            if (emptyState) {
                emptyState.style.display = visibleCount === 0 && searchTerm !== '' ? '' : 'none';
            }
        });

        // Enhanced hover effects for images
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('load', function() {
                this.classList.add('fade-in');
            });
        });

        // Auto-refresh stats
        function updateStats() {
            const rows = document.querySelectorAll('.photo-row:not([style*="display: none"])');
            const totalCount = rows.length;
            
            // Update total photos count in stats card
            const statsCard = document.querySelector('.stats-card .text-2xl');
            if (statsCard) {
                statsCard.textContent = totalCount;
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateStats();
            
            // Add loading state to external links
            document.querySelectorAll('a[target="_blank"]').forEach(link => {
                link.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    const originalClass = icon.className;
                    icon.className = 'fas fa-spinner fa-spin mr-2';
                    
                    setTimeout(() => {
                        icon.className = originalClass;
                    }, 2000);
                });
            });
        });

        // Smooth scroll for any anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>
</html>