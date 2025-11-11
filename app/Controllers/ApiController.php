<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiController {
  public function __construct(
    private ProductRepository $products,
    private TransactionRepository $tx,
    private UserRepository $users,
    private array $jwtCfg
  ) {}

  /* -------------------- Helpers -------------------- */

  private function json($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
  }

  private function readRaw(): string {
    $raw = file_get_contents('php://input');
    return $raw === false ? '' : $raw;
  }

  private function parseJson(): array {
    $raw = $this->readRaw();
    if ($raw === '') return [];
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
  }

  private function getAuthHeader(): string {
    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) return (string)$_SERVER['HTTP_AUTHORIZATION'];
    if (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) return (string)$_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    if (function_exists('getallheaders')) {
      foreach (getallheaders() as $k => $v) {
        if (strcasecmp($k, 'Authorization') === 0) return (string)$v;
      }
    }
    return '';
  }

  private function bearerUser() : ?array {
    $hdr = $this->getAuthHeader();
    if (!preg_match('/Bearer\s+(.+)/i', $hdr, $m)) return null;
    try {
      $decoded = JWT::decode(trim($m[1]), new Key($this->jwtCfg['secret'],'HS256'));
      $uid = (int)($decoded->sub ?? 0);
      if ($uid <= 0) return null;
      return $this->users->find($uid) ?: null;
    } catch(Throwable $e){ return null; }
  }

  private function requireAuth(): array {
    $u = $this->bearerUser();
    if(!$u){ $this->json(['error'=>'unauthorized'], 401); }
    return $u;
  }

  private function requireRole(string $role): array {
    $u = $this->requireAuth();
    if(($u['role'] ?? '') !== $role){ $this->json(['error'=>'forbidden'], 403); }
    return $u;
  }

  private function debugPayload(array $data): array {
    if (!empty($this->jwtCfg['debug'])) {
      $ct = $_SERVER['CONTENT_TYPE'] ?? ($_SERVER['HTTP_CONTENT_TYPE'] ?? '(none)');
      $data['_debug'] = [
        'content_type' => $ct,
        'post_keys'    => array_keys($_POST ?? []),
        'raw_len'      => strlen($this->readRaw()),
      ];
    }
    return $data;
  }

  /* -------------------- AUTH API -------------------- */

  #[Route('POST','/api/auth/login')]
  public function apiLogin() {
  
    $in = $this->parseJson();
    if (!$in) $in = $_POST;

    $email = trim((string)($in['email'] ?? ''));
    $pass  = (string)($in['password'] ?? '');

    if ($email === '' || $pass === '') {
      $this->json($this->debugPayload(['error'=>'invalid_credentials']), 401);
    }

    $user = $this->users->findByEmail($email);
    if (!$user || !password_verify($pass, $user['password_hash'])) {
      $this->json($this->debugPayload(['error'=>'invalid_credentials']), 401);
    }

    $now = time();
    $payload = [
      'iss'  => $this->jwtCfg['issuer'],
      'sub'  => (int)$user['id'],
      'role' => $user['role'],
      'iat'  => $now,
      'exp'  => $now + (int)$this->jwtCfg['expiry_seconds'],
    ];
    $token = JWT::encode($payload, $this->jwtCfg['secret'], 'HS256');

    $this->json([
      'token'       => $token,
      'token_type'  => 'Bearer',
      'expires_in'  => (int)$this->jwtCfg['expiry_seconds'],
      'user'        => [
        'id'    => (int)$user['id'],
        'email' => $user['email'],
        'role'  => $user['role'],
      ],
    ], 200);
  }

  /* -------------------- PUBLIC API -------------------- */

  #[Route('GET','/api/products')]
  public function listProducts() {
    $page = max(1,(int)($_GET['page'] ?? 1));
    $sort = $_GET['sort'] ?? 'name';
    $dir  = $_GET['dir'] ?? 'asc';
    $res = $this->products->paginate($page, 10, $sort, $dir);
    $this->json($res, 200);
  }

  #[Route('GET','/api/products/{id}')]
  public function getProduct($params) {
    $p = $this->products->find((int)$params['id']);
    if(!$p){ $this->json(['error'=>'not_found'], 404); }
    $this->json($p, 200);
  }

  /* -------------------- PROTECTED API -------------------- */

  #[Route('POST','/api/products')]
  public function createProduct() {
    $this->requireRole('Admin');

    $input = $this->parseJson();
    if (!$input) $input = $_POST; // tolerate form-encoded

    [$errors, $data] = validateProduct($input);
    if($errors){ $this->json(['errors'=>$errors], 422); }

    $id = $this->products->create($data['name'], (float)$data['price'], (int)$data['quantity_available']);
    $this->json(['id'=>$id, 'message'=>'Product created'], 201);
  }


  #[Route('POST','/api/products/{id}/purchase')]
  public function apiPurchase($params) {
    $u = $this->requireAuth();

    $payload = $this->parseJson();
    if (!$payload) $payload = $_POST;

    $qty = (int)($payload['quantity'] ?? 1);
    if ($qty < 1) { $this->json(['error'=>'invalid_quantity'], 422); }

    $pid = (int)$params['id'];
    $p = $this->products->find($pid);
    if(!$p){ $this->json(['error'=>'not_found'], 404); }

    if(!$this->products->decreaseStock($pid, $qty)){
      $this->json(['error'=>'insufficient_stock'], 409);
    }
    $tid = $this->tx->log((int)$u['id'], $pid, $qty, (float)$p['price']);
    $this->json(['transaction_id'=>$tid,'total'=> (float)$p['price'] * $qty], 200);
  }

  #[Route('DELETE','/api/products/{id}')]
  public function deleteProduct($params) {
    $this->requireRole('Admin');

    $id = (int)($params['id'] ?? 0);
    if ($id <= 0) { $this->json(['error' => 'invalid_id'], 400); }

    $p = $this->products->find($id);
    if (!$p) { $this->json(['error' => 'not_found'], 404); }

    $ok = $this->products->delete($id); // implement in ProductRepository
    if (!$ok) { $this->json(['error' => 'delete_failed'], 500); }

    $this->json(['message' => 'deleted', 'id' => $id], 200);
  }

}
