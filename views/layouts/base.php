<?php
use App\Auth\SessionAuth;
SessionAuth::start();
$user = SessionAuth::user(); // ['id','email','role'] or null
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Vending') ?></title>

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }
    .navbar {
      padding: 0.5rem 1rem;
    }
    .navbar-brand {
      font-weight: 600;
    }
    main {
      padding: 2rem;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
      <a class="navbar-brand" href="/products">Vending</a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarMenu">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="/products">Products</a></li>
          <?php if (($user['role'] ?? '') === 'Admin'): ?>
            <li class="nav-item"><a class="nav-link" href="/products/create">New Product</a></li>
          <?php endif; ?>
        </ul>

        <ul class="navbar-nav ms-auto">
          <?php if ($user): ?>
            <li class="nav-item">
              <a class="nav-link" href="/logout">
                Logout<?= $user['email'] ? ' ('.htmlspecialchars($user['email']).')' : '' ?>
              </a>
            </li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/login">Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container-fluid px-4">
    <?php if ($msg = flash('success')): ?>
      <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        <?= htmlspecialchars($msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?= $content ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
