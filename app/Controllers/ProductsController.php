<?php
use App\Auth\SessionAuth;
use App\Database\Db;
use App\Validation\Validator;

class ProductsController {
  #[Route('GET','/products')]
  public function index() {
      SessionAuth::start();
      $pdo = Db::get();

      $page = max(1, (int)($_GET['page'] ?? 1));
      $per  = max(1, (int)($_GET['perPage'] ?? 10));
      $sort = $_GET['sort'] ?? 'created_at';
      $dirQ = strtolower($_GET['dir'] ?? 'desc');
      $dir  = ($dirQ === 'desc') ? 'DESC' : 'ASC';
      $dirForView = ($dir === 'DESC') ? 'desc' : 'asc';

      $from = $_GET['from'] ?? null;
      $to   = $_GET['to']   ?? null;

      $allowed = ['name','price','quantity_available','id','created_at'];
      if (!in_array($sort, $allowed, true)) $sort = 'name';

      $offset = ($page - 1) * $per;

      // Build the WHERE clause dynamically
      $where = [];
      $params = [];

      if ($from) {
          $where[] = "created_at >= :from";
          $params[':from'] = $from . " 00:00:00";
      }
      if ($to) {
          $where[] = "created_at <= :to";
          $params[':to'] = $to . " 23:59:59";
      }

      $whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

      // Get total with filter
      $countStmt = $pdo->prepare("SELECT COUNT(*) FROM products $whereSql");
      foreach ($params as $k => $v) $countStmt->bindValue($k, $v);
      $countStmt->execute();
      $total = (int)$countStmt->fetchColumn();

      // Fetch items with filter + pagination
      $sql = "SELECT * FROM products $whereSql ORDER BY $sort $dir LIMIT :per OFFSET :off";
      $stmt = $pdo->prepare($sql);

      foreach ($params as $k => $v) $stmt->bindValue($k, $v);

      $stmt->bindValue(':per', $per, \PDO::PARAM_INT);
      $stmt->bindValue(':off', $offset, \PDO::PARAM_INT);
      $stmt->execute();

      $items = $stmt->fetchAll();

      view('products/index', [
          'rows'    => $items,
          'total'   => $total,
          'page'    => $page,
          'perPage' => $per,
          'sort'    => $sort,
          'dir'     => $dirForView,
          'from'    => $from,
          'to'      => $to,
          'user'    => SessionAuth::user(),
      ]);
  }


  #[Route('GET','/products/create')]
  public function createForm() {
    SessionAuth::requireRole('Admin');
    view('products/create', [
      'product' => ['name'=>'','price'=>'','quantity_available'=>''],
      'errors'  => [],
      'user'    => SessionAuth::user(),
    ]);
  }

  #[Route('POST','/products')]
  public function create() {
    SessionAuth::requireRole('Admin');
    [$errors, $data] = Validator::product($_POST);
    if ($errors) {
      view('products/create', [
        'product' => $_POST,
        'errors'  => $errors,
        'user'    => SessionAuth::user(),
      ]);
      return;
    }
    $pdo = Db::get();
    $stmt = $pdo->prepare("INSERT INTO products (name, price, quantity_available) VALUES (?,?,?)");
    $stmt->execute([$data['name'], $data['price'], $data['quantity_available']]);
    flash('success', '✅ Product created successfully.');
    header('Location: /products'); exit;
  }

  #[Route('GET','/products/{id}')]
  public function show($params) {
    SessionAuth::start();
    $pdo = Db::get();

    $id = (int)$params['id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) { http_response_code(404); echo 'Not Found'; return; }

    // pass as `$p` to match your view code
    view('products/show', [
      'p'    => $product,
      'user' => SessionAuth::user(),
    ]);
  }


  #[Route('GET','/products/{id}/edit')]
  public function editForm($params) {
    SessionAuth::requireRole('Admin');
    $pdo = Db::get();

    $id = (int)$params['id'];
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) { http_response_code(404); echo 'Not found'; return; }

    // IMPORTANT: pass 'p' (what the view reads) and an empty 'old'
    view('products/edit', [
      'p'      => $product,
      'errors' => [],
      'old'    => [],
    ]);
  }

  #[Route('POST','/products/{id}/edit')]
  public function update($params) {
    SessionAuth::requireRole('Admin');
    [$errors, $data] = Validator::product($_POST);
    $id = (int)$params['id'];

    if ($errors) {
      // Fetch current product so the view has an id and baseline values
      $pdo = Db::get();
      $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
      $stmt->execute([$id]);
      $product = $stmt->fetch();

      view('products/edit', [
        'p'      => $product ?: ['id' => $id],
        'errors' => $errors,
        'old'    => $_POST,     // <-- re-populate fields with user input
      ]);
      return;
    }

    $pdo = Db::get();
    $stmt = $pdo->prepare(
      "UPDATE products SET name=?, price=?, quantity_available=? WHERE id=?"
    );
    $stmt->execute([$data['name'], $data['price'], $data['quantity_available'], $id]);
    flash('success', '✅ Product updated successfully.');
    header('Location: /products'); exit;
  }



  #[Route('POST','/products/{id}/delete')]
  public function delete($params) {
    SessionAuth::requireRole('Admin');
    $pdo = Db::get();
    $stmt = $pdo->prepare("DELETE FROM products WHERE id=?");
    $stmt->execute([(int)$params['id']]);
    flash('success', '✅ Product deleted successfully.');
    header('Location: /products'); exit;
  }

  #[Route('GET','/products/{id}/purchase')]
  public function purchaseForm($params) {
    SessionAuth::start();

    // ❗ Redirect guests to login (remember where they were going)
    if (!SessionAuth::user()) {
      $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/products';
      header('Location: /login');
      exit;
    }

    $pdo = Db::get();
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([(int)$params['id']]);
    $product = $stmt->fetch();
    if (!$product) { http_response_code(404); echo 'Not found'; return; }

    view('products/purchase', [
      'product' => $product,
      'errors'  => [],
      'user'    => SessionAuth::user(),
    ]);
  }

  #[Route('POST','/products/{id}/purchase')]
  public function purchase($params) {
    SessionAuth::start();

    // ❗ Redirect guests to login before processing
    if (!SessionAuth::user()) {
      $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/products';
      header('Location: /login');
      exit;
    }

    $qty = max(1, (int)($_POST['quantity'] ?? 1));
    $pdo = Db::get();
    $pdo->beginTransaction();
    try {
      $stmt = $pdo->prepare("SELECT * FROM products WHERE id=? FOR UPDATE");
      $stmt->execute([(int)$params['id']]);
      $p = $stmt->fetch();
      if (!$p) { throw new \Exception('Product not found'); }
      if ((int)$p['quantity_available'] < $qty) { throw new \Exception('Insufficient stock'); }

      $stmt = $pdo->prepare("UPDATE products SET quantity_available = quantity_available - ? WHERE id=?");
      $stmt->execute([$qty, (int)$params['id']]);

      $u = SessionAuth::user();
      $uid = $u['id'] ?? 0;
      $unit = (float)$p['price'];
      $total = $unit * $qty;

      $stmt = $pdo->prepare("INSERT INTO transactions (user_id, product_id, quantity, unit_price, total_price) VALUES (?,?,?,?,?)");
      $stmt->execute([$uid, (int)$params['id'], $qty, $unit, $total]);

      $pdo->commit();
      flash('success', '✅ Purchase product successfully.');
      header('Location: /products'); exit;
    } catch (\Throwable $e) {
      $pdo->rollBack();
      view('products/purchase', [
        'product' => $p ?? null,
        'errors'  => ['purchase' => $e->getMessage()],
        'user'    => SessionAuth::user(),
      ]);
    }
  }

}
