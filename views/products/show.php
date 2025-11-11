<?php ob_start(); $title = htmlspecialchars($p['name']); ?>

<div class="container py-5">
  <div class="card shadow-sm mx-auto" style="max-width: 500px;">
    <div class="card-body">
      <h1 class="h4 mb-3 text-center"><?= htmlspecialchars($p['name']) ?></h1>

      <?php if (isset($_GET['purchased'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          ✅ Purchase successful!
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <ul class="list-group list-group-flush mb-4">
        <li class="list-group-item d-flex justify-content-between">
          <span class="fw-semibold">Price:</span>
          <span>$<?= number_format((float)$p['price'], 2) ?></span>
        </li>
        <li class="list-group-item d-flex justify-content-between">
          <span class="fw-semibold">Available:</span>
          <span>
            <?= (int)$p['quantity_available'] ?>
            <?php if ((int)$p['quantity_available'] === 0): ?>
              <span class="badge bg-secondary ms-2">Out of stock</span>
            <?php endif; ?>
          </span>
        </li>
      </ul>

      <div class="d-flex justify-content-center gap-3">
        <a href="/products" class="btn btn-outline-secondary">Back</a>
        <a href="/products/<?= (int)$p['id'] ?>/purchase"
           class="btn btn-primary <?= ((int)$p['quantity_available'] === 0) ? 'disabled' : '' ?>">
          Buy Now
        </a>
      </div>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); include __DIR__ . '/../layouts/base.php'; ?>
