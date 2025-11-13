<?php
use App\Auth\SessionAuth;
SessionAuth::start();
$user = SessionAuth::user(); // ['id','email','role'] or null
?>


<?php ob_start(); $title='Products'; ?>
<h1 class="mb-4 fw-bold text-dark text-center">Products</h1>

<?php
  $pp           = isset($perPage) ? (int)$perPage : 10;
  $totalInt     = (int)$total;
  $currentPage  = (int)$page;
  $pageCount    = (int)ceil($totalInt / max(1, $pp));
  $hasPrev      = $currentPage > 1;
  $hasNext      = $currentPage < $pageCount;
  $rowNo = ($currentPage - 1) * $pp + 1; 

  function s($col){
    global $sort, $dir, $page, $perPage;
    $next = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
    $qs = http_build_query([
      'page'    => (int)$page,
      'sort'    => $col,
      'dir'     => $next,
      'perPage' => isset($perPage) ? (int)$perPage : 10,
    ]);
    return "/products?$qs";
  }

  $prevQs = http_build_query([
    'page'    => max(1, $currentPage - 1),
    'sort'    => $sort,
    'dir'     => $dir,
    'perPage' => $pp,
  ]);
  $nextQs = http_build_query([
    'page'    => min(max(1,$pageCount), $currentPage + 1),
    'sort'    => $sort,
    'dir'     => $dir,
    'perPage' => $pp,
  ]);
?>

<style>
  table th, table td {
    text-align: center;
    vertical-align: middle;
  }
</style>

<form method="get" class="mb-3 d-flex gap-3 align-items-end">
    <div>
        <label class="form-label">From</label>
        <input type="date" name="from" value="<?= htmlspecialchars($from ?? '') ?>" class="form-control">
    </div>
    <div>
        <label class="form-label">To</label>
        <input type="date" name="to" value="<?= htmlspecialchars($to ?? '') ?>" class="form-control">
    </div>
    <div>
        <label class="form-label d-block">&nbsp;</label>
        <button class="btn btn-primary">Filter</button>
    </div>
</form>


<div class="table-responsive">
  <table class="table table-striped table-hover table-bordered align-middle w-100 shadow-sm">
    <thead class="table-primary">
      <tr>
        <th>No.</th>
        <th><a href="<?= s('name') ?>" class="text-dark text-decoration-none fw-semibold">Name</a></th>
        <th><a href="<?= s('price') ?>" class="text-dark text-decoration-none fw-semibold">Price</a></th>
        <th><a href="<?= s('quantity_available') ?>" class="text-dark text-decoration-none fw-semibold">Qty</a></th>
        <th>Actions</th>
        <th><a href="<?= s('created_at') ?>" class="text-dark text-decoration-none fw-semibold">Date</a></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($rows)): ?>
        <tr>
          <td colspan="4" class="text-center text-muted py-4">No products found.</td>
        </tr>
      <?php else: ?>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= $rowNo++ ?></td>
            <td>
              <?= htmlspecialchars($r['name']) ?>
              <?php if ((int)$r['quantity_available'] === 0): ?>
                <span class="badge bg-secondary ms-2">Out of stock</span>
              <?php endif; ?>
            </td>
            <td>$<?= number_format((float)$r['price'], 2) ?></td>
            <td><?= (int)$r['quantity_available'] ?></td>
            <td>
              <div class="d-flex justify-content-center flex-wrap gap-2">
                <a href="/products/<?= (int)$r['id'] ?>" class="btn btn-outline-secondary btn-sm">View</a>
                <a href="/products/<?= (int)$r['id'] ?>/purchase"
                   class="btn btn-outline-primary btn-sm <?= ((int)$r['quantity_available'] === 0) ? 'disabled' : '' ?>">Buy</a>
                <?php if (strtolower($user['role'] ?? '') === 'admin'): ?>
                  <a href="/products/<?= (int)$r['id'] ?>/edit" class="btn btn-outline-dark btn-sm">Edit</a>
                  <form action="/products/<?= (int)$r['id'] ?>/delete"
                        method="post"
                        onsubmit="return confirm('Delete this item?');"
                        style="display:inline;">
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                  </form>
                <?php endif; ?>
              </div>
            </td>
            <td><?= htmlspecialchars(date('Y-m-d', strtotime($r['created_at']))) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($pageCount > 1): ?>
  <nav class="mt-3">
    <ul class="pagination justify-content-between">
      <li class="page-item <?= $hasPrev ? '' : 'disabled' ?>">
        <a class="page-link" href="<?= $hasPrev ? '/products?'.$prevQs : '#' ?>">« Prev</a>
      </li>
      <li class="page-item disabled">
        <span class="page-link">Page <?= $currentPage ?> of <?= $pageCount ?></span>
      </li>
      <li class="page-item <?= $hasNext ? '' : 'disabled' ?>">
        <a class="page-link" href="<?= $hasNext ? '/products?'.$nextQs : '#' ?>">Next »</a>
      </li>
    </ul>
  </nav>
<?php endif; ?>

<?php $content = ob_get_clean(); include __DIR__.'/../layouts/base.php'; ?>
