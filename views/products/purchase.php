<?php
// ---- Safe inputs & defaults ----
$p      = $p ?? ($product ?? null);
$errors = $errors ?? [];

if (!$p || !is_array($p)) {
  // If controller didn’t provide a product, show a friendly message.
  ob_start(); $title = 'Purchase';
  ?>
  <div class="container py-5">
    <div class="alert alert-danger">Product not found.</div>
    <a href="/products" class="btn btn-outline-secondary">Back</a>
  </div>
  <?php
  $content = ob_get_clean(); include __DIR__.'/../layouts/base.php'; return;
}

$name   = (string)($p['name'] ?? '');
$price  = (float)($p['price'] ?? 0);
$qtyAvl = (int)($p['quantity_available'] ?? 0);
$id     = (int)($p['id'] ?? 0);
?>

<?php ob_start(); $title = 'Purchase '.htmlspecialchars($name); ?>

<div class="container py-5">
  <div class="card shadow-sm mx-auto" style="max-width: 520px;">
    <div class="card-body">
      <h1 class="h4 mb-3 text-center">Buy: <?= htmlspecialchars($name) ?></h1>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
          <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <ul class="list-group list-group-flush mb-4">
        <li class="list-group-item d-flex justify-content-between">
          <span class="fw-semibold">Unit price</span>
          <span>$<?= number_format($price, 2) ?></span>
        </li>
        <li class="list-group-item d-flex justify-content-between">
          <span class="fw-semibold">Available</span>
          <span>
            <?= $qtyAvl ?>
            <?php if ($qtyAvl === 0): ?>
              <span class="badge bg-secondary ms-2">Out of stock</span>
            <?php endif; ?>
          </span>
        </li>
        <li class="list-group-item d-flex justify-content-between">
          <span class="fw-semibold">Total</span>
          <span id="total">$<?= number_format($price, 2) ?></span>
        </li>
      </ul>

      <form method="post" action="/products/<?= $id ?>/purchase" id="purchaseForm" class="needs-validation" novalidate>
        <div class="mb-3">
          <label for="qty" class="form-label">Quantity</label>
          <input
            type="number"
            class="form-control"
            id="qty"
            name="quantity"
            min="1"
            max="<?= $qtyAvl ?>"
            value="1"
            required
            <?= ($qtyAvl === 0) ? 'disabled' : '' ?>
          >
          <div class="invalid-feedback">Please enter a quantity between 1 and <?= $qtyAvl ?>.</div>
        </div>

        <div class="d-flex justify-content-between">
          <a href="/products" class="btn btn-outline-secondary">Back</a>
          <button
            type="submit"
            class="btn btn-primary"
            <?= ($qtyAvl === 0) ? 'disabled' : '' ?>
          >Confirm Purchase</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(() => {
  const form  = document.getElementById('purchaseForm');
  const qtyEl = document.getElementById('qty');
  const total = document.getElementById('total');
  const unit  = <?= json_encode($price) ?>;

  function updateTotal() {
    const q = Math.max(1, parseInt(qtyEl.value || '1', 10));
    total.textContent = '$' + (q * unit).toFixed(2);
  }

  if (qtyEl) {
    qtyEl.addEventListener('input', updateTotal);
    updateTotal();
  }

  form?.addEventListener('submit', (e) => {
    if (!form.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    form.classList.add('was-validated');
  });
})();
</script>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/base.php'; ?>
