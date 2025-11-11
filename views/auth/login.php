<?php ob_start(); $title='Login'; ?>

<div class="d-flex justify-content-center align-items-center" style="min-height:70vh;">
  <div class="card shadow-sm w-100" style="max-width: 420px;">
    <div class="card-body p-4">
      <h1 class="h4 text-center mb-3">Sign in</h1>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= htmlspecialchars($error) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <form method="post" action="/login" novalidate>
        <div class="form-floating mb-3">
          <input
            type="email"
            class="form-control"
            id="email"
            name="email"
            placeholder="name@example.com"
            value="<?= htmlspecialchars($old['email'] ?? '') ?>"
            required
          >
          <label for="email">Email address</label>
          <div class="invalid-feedback">Please enter a valid email.</div>
        </div>

        <div class="form-floating mb-3">
          <input
            type="password"
            class="form-control"
            id="password"
            name="password"
            placeholder="Password"
            required
          >
          <label for="password">Password</label>
          <div class="invalid-feedback">Password is required.</div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
            <label class="form-check-label" for="remember">Remember me</label>
          </div>
          <a class="small text-decoration-none" href="/forgot">Forgot password?</a>
        </div>

        <button class="btn btn-primary w-100" type="submit">Login</button>
      </form>
    </div>
  </div>
</div>

<script>
// Use Bootstrap's client-side styling for required fields
(() => {
  const form = document.querySelector('form');
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
