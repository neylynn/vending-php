<?php ob_start(); $title='Edit Product'; ?>

<div class="container py-5">
  <div class="card shadow-sm mx-auto" style="max-width: 520px;">
    <div class="card-body">
      <h1 class="h4 mb-3 text-center">Edit Product</h1>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
          <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="/products/<?= (int)$p['id'] ?>/edit" id="productForm" class="needs-validation" novalidate>
        <div class="form-floating mb-3">
          <input
            type="text"
            class="form-control"
            id="name"
            name="name"
            value="<?= htmlspecialchars($old['name'] ?? $p['name'] ?? '') ?>"
            required
            maxlength="255"
            placeholder="Name"
          >
          <label for="name">Name</label>
          <div class="invalid-feedback">Please enter a product name.</div>
        </div>

        <div class="form-floating mb-3">
          <input
            type="number"
            class="form-control"
            id="price"
            name="price"
            value="<?= htmlspecialchars($old['price'] ?? $p['price'] ?? '') ?>"
            required
            min="0.001"
            step="0.001"
            placeholder="Price"
          >
          <label for="price">Price (USD)</label>
          <div class="invalid-feedback">Enter a price greater than 0 (up to 3 decimals).</div>
        </div>

        <div class="form-floating mb-4">
          <input
            type="number"
            class="form-control"
            id="quantity_available"
            name="quantity_available"
            value="<?= htmlspecialchars($old['quantity_available'] ?? $p['quantity_available'] ?? '') ?>"
            required
            min="0"
            step="1"
            placeholder="Quantity"
          >
          <label for="quantity_available">Quantity Available</label>
          <div class="invalid-feedback">Enter a non-negative integer.</div>
        </div>

        <div class="d-flex justify-content-between">
          <a href="/products" class="btn btn-outline-secondary">Cancel</a>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Bootstrap-style client validation
(() => {
  const form = document.getElementById('productForm');
  form.addEventListener('submit', (e) => {
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');
  }, false);
})();
</script>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/base.php'; ?>
