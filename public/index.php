<?php
require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../app/Helpers.php';
require __DIR__.'/../app/Validation.php';

foreach (glob(__DIR__.'/../app/**/*.php') as $file) {
  if (basename($file) !== 'Config.php') require_once $file;
}
$config = require __DIR__.'/../app/Config.php';

$db = new Database($config);
$pdo = $db->pdo();

$userRepo = new UserRepository($pdo);
$auth = new Auth($userRepo);

$productRepo = new ProductRepository($pdo);
$txRepo = new TransactionRepository($pdo);

$router = new Router();

$products = new ProductsController($productRepo, $txRepo, $auth);
$authController = new AuthController($auth, $userRepo, $config['jwt']);
$api = new ApiController($productRepo, $txRepo, $userRepo, $config['jwt']);

$router->registerController($products);
$router->registerController($authController);
$router->registerController($api);

if($_SERVER['REQUEST_URI'] === '/login' && $_SERVER['REQUEST_METHOD']==='GET'){ $authController->loginForm(); exit; }
if($_SERVER['REQUEST_URI'] === '/login' && $_SERVER['REQUEST_METHOD']==='POST'){ $authController->login(); exit; }
if($_SERVER['REQUEST_URI'] === '/logout'){ $authController->logout(); exit; }

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
