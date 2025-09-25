<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Rute Utama & Otentikasi Admin
$routes->get('/', 'Auth::index');
$routes->get('/login', 'Auth::index');
$routes->post('/auth/process', 'Auth::process');
$routes->get('/logout', 'Auth::logout');

// Rute Dashboard Admin
$routes->get('/admin/dashboard', 'Admin::dashboard');

// Rute untuk Alur Upload Foto oleh Petugas (via Token)
$routes->get('/manajemen-foto', 'ManajemenFoto::halamanToken');
$routes->post('/verifikasi-token', 'ManajemenFoto::verifikasiToken');
$routes->get('/form-upload', 'ManajemenFoto::index');
$routes->post('/manajemen-foto/upload', 'ManajemenFoto::upload');
$routes->get('/admin/export', 'Admin::export');
$routes->resource('kelola-petugas', [
  'controller' => 'KelolaPetugas',
  'only' => ['index', 'create', 'store', 'edit', 'update', 'delete']
]);

$routes->get('kelola-petugas/edit/(:num)', 'KelolaPetugas::edit/$1');
// Tambahkan dua baris ini untuk mempermudah AJAX POST
$routes->post('kelola-petugas/update/(:num)', 'KelolaPetugas::update/$1');
$routes->post('kelola-petugas/delete/(:num)', 'KelolaPetugas::delete/$1');


$routes->get('/petugas/dashboard', 'Petugas::dashboard');

// Tambahkan grup untuk rute admin agar lebih rapi
$routes->group('admin', static function ($routes) {
  $routes->get('dashboard', 'Admin::dashboard');

  // -- BARIS BARU DI SINI --
  // Rute untuk menampilkan halaman pembuatan link
  $routes->get('buat-link', 'Admin::buatLink');
});
