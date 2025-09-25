<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Akses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .card {
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <h3 class="card-title text-center mb-3">Verifikasi Akses</h3>
            <p class="text-center text-muted">Silakan masukkan kode token untuk melanjutkan.</p>
            
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
            <?php endif; ?>

            <form action="/verifikasi-token" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <input type="password" class="form-control" name="token" id="token" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary w-100">Lanjutkan</button>
            </form>
        </div>
    </div>
</body>
</html>