<?php

class AuthController
{
    public function __construct(
        private Auth $auth,
        private UserRepository $users,
        private array $jwtConfig = []   // keep this if you use JWT later
    ) {
    }

    #[Route('GET', '/login')]
    public function loginForm()
    {
        // You can pass data like ['error' => $error] if your view() helper supports it
        view('auth/login');
    }

    #[Route('POST', '/login')]
    public function login()
    {
        $email = trim($_POST['email'] ?? '');
        $pass  = (string)($_POST['password'] ?? '');

        // Use the Auth service, which already talks to UserRepository
        if (!$this->auth->attempt($email, $pass)) {
            $error = 'Invalid credentials';

            // If your view helper accepts data, you can do:
            // view('auth/login', ['error' => $error, 'old' => ['email' => $email]]);
            // To minimize changes, keep it simple:
            view('auth/login');
            return;
        }

        header('Location: /products');
        exit;
    }

    #[Route('GET', '/logout')] // or POST if you prefer, manual route in index.php will still work
    public function logout()
    {
        $this->auth->logout();
        header('Location: /login');
        exit;
    }
}
