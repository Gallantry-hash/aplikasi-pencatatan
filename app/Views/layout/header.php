<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Admin Panel') ?></title>
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
                            900: '#1e3a8a',
                        },
                        gray: {
                            50: '#f9fafb',
                            100: '#f3f4f6',
                            800: '#1f2937',
                            900: '#111827',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .sidebar {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
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

        .hover-lift {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .submenu-hidden {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .submenu-visible {
            max-height: 200px;
            opacity: 1;
        }

        .nav-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .glass-morphism {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .sidebar-item {
            position: relative;
            overflow: hidden;
        }

        .sidebar-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.5s;
        }

        .sidebar-item:hover::before {
            left: 100%;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-gray-50 via-white to-gray-100 min-h-screen">
    <!-- Mobile Navigation -->
    <nav class="lg:hidden nav-gradient text-white p-4 flex justify-between items-center fixed w-full z-50 shadow-lg">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                <i class="fas fa-user-shield text-sm"></i>
            </div>
            <span class="text-xl font-bold">Admin Panel</span>
        </div>
        <button id="menu-toggle" class="p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <!-- Sidebar -->
    <div id="sidebar" class="fixed inset-y-0 left-0 w-72 nav-gradient text-white transform -translate-x-full lg:translate-x-0 sidebar z-40 pt-16 lg:pt-0 overflow-y-auto shadow-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between p-6 border-b border-white/20">
            <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-shield fa-lg"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold">Admin Panel</h1>
                    <p class="text-sm text-white/70">Management System</p>
                </div>
            </div>
            <button id="menu-close" class="lg:hidden p-2 rounded-lg bg-white/10 hover:bg-white/20 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="p-4">
            <ul class="space-y-2">
                <li>
                    <a href="/admin/dashboard" class="sidebar-item flex items-center p-3 rounded-xl text-white/90 hover:bg-white/10 hover:text-white transition-all duration-200 group">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mr-3 group-hover:bg-white/20 transition-colors">
                            <i class="fas fa-tachometer-alt"></i>
                        </div>
                        <div>
                            <span class="font-medium">Dashboard</span>
                            <p class="text-xs text-white/60">Overview & Stats</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="/kelola-petugas" class="sidebar-item flex items-center p-3 rounded-xl text-white/90 hover:bg-white/10 hover:text-white transition-all duration-200 group">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mr-3 group-hover:bg-white/20 transition-colors">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <div>
                            <span class="font-medium">Kelola Petugas</span>
                            <p class="text-xs text-white/60">Manage Officers</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="/manajemen-foto" class="sidebar-item flex items-center p-3 rounded-xl text-white/90 hover:bg-white/10 hover:text-white transition-all duration-200 group">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mr-3 group-hover:bg-white/20 transition-colors">
                            <i class="fas fa-images"></i>
                        </div>
                        <div>
                            <span class="font-medium">Manajemen Foto</span>
                            <p class="text-xs text-white/60">Photo Management</p>
                        </div>
                    </a>
                </li>
                <li>
                    <a href="<?= site_url('admin/buat-link') ?>" class="sidebar-item flex items-center p-3 rounded-xl text-white/90 hover:bg-white/10 hover:text-white transition-all duration-200 group">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center mr-3 group-hover:bg-white/20 transition-colors">
                            <i class="fas fa-link"></i>
                        </div>
                        <div>
                            <span class="font-medium">Buat Link</span>
                            <p class="text-xs text-white/60">Generate Links</p>
                        </div>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Logout Button -->
        <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/20">
            <a href="/logout" class="flex items-center p-3 rounded-xl text-white/90 hover:bg-red-500/20 hover:text-white transition-all duration-200 group">
                <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center mr-3 group-hover:bg-red-500/30 transition-colors">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <div>
                    <span class="font-medium">Logout</span>
                    <p class="text-xs text-white/60">Sign Out</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <main class="lg:ml-72 p-6 fade-in pt-20 lg:pt-6">