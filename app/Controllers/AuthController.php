<?php
use App\Auth\SessionAuth;
use App\Database\Db;

class AuthController {
  #[Route('GET','/login')]
  public function loginForm() {
    view('auth/login');
  }

  #[Route('POST','/login')]
  public function login() {
    SessionAuth::start();
    $email = trim($_POST['email'] ?? '');
    $pass  = (string)($_POST['password'] ?? '');

    $pdo = Db::get();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($pass, $user['password_hash'])) {
      $error = 'Invalid credentials';
      view('auth/login');
      return;
    }
    SessionAuth::login($user);
    header('Location: /products'); exit;
  }

  #[Route('POST','/logout')]
  public function logout() {
    SessionAuth::logout();
    header('Location: /login'); exit;
  }
}
