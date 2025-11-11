<?php
namespace App\Auth;

class SessionAuth {
  public static function start(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
  }

  public static function login(array $user): void {
    self::start();
    $_SESSION['user'] = ['id'=>$user['id'],'email'=>$user['email'],'role'=>$user['role']];
  }

  public static function logout(): void {
    self::start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $params = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
  }

  public static function user(): ?array {
    self::start();
    return $_SESSION['user'] ?? null;
  }

  public static function requireLogin(): void {
    self::start();
    if (empty($_SESSION['user'])) {
      header('Location: /login');
      exit;
    }
  }

  public static function requireRole(string $role): void {
    $u = self::user();
    if (!$u) { http_response_code(302); header('Location: /login'); exit; }
    if (($u['role'] ?? '') !== $role) { http_response_code(403); echo 'Forbidden'; exit; }
  }
}
