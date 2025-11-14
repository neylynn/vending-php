<?php
// $user is passed from controllers when needed.
// Fallback to null if not provided.
$user = $user ?? null;
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
      background: #f5f7fb;
    }
    .navbar-brand span {
      font-weight: 700;
      letter-spacing: 0.05em;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="/products">
        <span class="ms-1">Vending</span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="/products">Products</a></li>
          <?php if (($user['role'] ?? '') === 'Admin'): ?>
            <li class="nav-item"><a class="nav-link" href="/products/create">New Product</a></li>
          <?php endif; ?>
        </ul>
        <ul class="navbar-nav ms-auto">
          <?php if ($user): ?>
            <li class="nav-item me-3">
              <span class="navbar-text text-light">
                <?= htmlspecialchars($user['email'] ?? '') ?>
                <?php if (!empty($user['role'])): ?>
                  <span class="badge bg-light text-primary ms-1"><?= htmlspecialchars($user['role']) ?></span>
                <?php endif; ?>
              </span>
            </li>
            <li class="nav-item">
              <form method="post" action="/logout" class="d-inline">
                <button class="btn btn-outline-light btn-sm" type="submit">Logout</button>
              </form>
            </li>
          <?php else: ?>
            <li class="nav-item">
              <a href="/login" class="btn btn-outline-light btn-sm">Login</a>
            </li>
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
