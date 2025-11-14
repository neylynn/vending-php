<?php

class Auth {
  public function __construct(private UserRepository $users) {
    if (session_status() === PHP_SESSION_NONE) {
      session_start();
    }
  }

  public function attempt(string $email, string $password): bool {
    $user = $this->users->findByEmail($email);
    if (!$user) return false;
    if (!password_verify($password, $user['password_hash'])) return false;

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role']    = $user['role'];

    return true;
  }

  public function user(): ?array {
    if (!isset($_SESSION['user_id'])) return null;
    return $this->users->find((int)$_SESSION['user_id']);
  }

  public function logout(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
      $params = session_get_cookie_params();
      setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
      );
    }
    session_destroy();
  }

  public function check(): bool {
    return isset($_SESSION['user_id']);
  }

  public function isAdmin(): bool {
    return ($_SESSION['role'] ?? '') === 'Admin';
  }
}
