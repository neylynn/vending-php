<?php
declare(strict_types=1);

// -----------------------------------------
// Error reporting (turn off in production)
// -----------------------------------------
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// -----------------------------------------
// Autoload & core helpers
// -----------------------------------------
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/Helpers.php';
require __DIR__ . '/../app/Validation.php';          
require __DIR__ . '/../app/Validation/Validator.php';
require __DIR__ . '/../app/Auth.php';
require __DIR__ . '/../app/Router.php';
require __DIR__ . '/../app/Database.php';

// Repositories
require __DIR__ . '/../app/Repositories/UserRepository.php';
require __DIR__ . '/../app/Repositories/ProductRepository.php';
require __DIR__ . '/../app/Repositories/TransactionRepository.php';

// Controllers
require __DIR__ . '/../app/Controllers/ProductsController.php';
require __DIR__ . '/../app/Controllers/AuthController.php';
require __DIR__ . '/../app/Controllers/ApiController.php';

// -----------------------------------------
// Config & DB
// -----------------------------------------
$config = require __DIR__ . '/../app/Config.php';

try {
    $db  = new Database($config);
    $pdo = $db->pdo();
} catch (Throwable $e) {
    http_response_code(500);
    echo "Database connection error.<br>";
    echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}

// -----------------------------------------
// Services & repositories
// -----------------------------------------
$userRepo    = new UserRepository($pdo);
$auth        = new Auth($userRepo);
$productRepo = new ProductRepository($pdo);
$txRepo      = new TransactionRepository($pdo);

// -----------------------------------------
// Controllers & router
// -----------------------------------------
$router = new Router();

$products       = new ProductsController($productRepo, $txRepo, $auth, $pdo);
$authController = new AuthController($auth, $userRepo, $config['jwt']);
$api            = new ApiController($productRepo, $txRepo, $userRepo, $config['jwt']);

$router->registerController($products);
$router->registerController($authController);
$router->registerController($api);

// -----------------------------------------
// Routing
// -----------------------------------------
$uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Manual routes for auth (optional, but keeps /login working)
if ($uri === '/login' && $method === 'GET') {
    $authController->loginForm();
    exit;
}

if ($uri === '/login' && $method === 'POST') {
    $authController->login();
    exit;
}

if ($uri === '/logout') {
    $authController->logout();
    exit;
}

// Default route: if root, go to /products
if ($uri === '/') {
    header('Location: /products');
    exit;
}

// Fallback to router
try {
    $router->dispatch($method, $uri);
} catch (Throwable $e) {
    http_response_code(500);
    echo "Application error.<br>";
    echo htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    exit;
}
