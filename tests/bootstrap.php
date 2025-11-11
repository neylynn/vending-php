<?php
// tests/bootstrap.php
define('PHPUNIT_RUNNING', true);
// declare(strict_types=1);

// error_reporting(E_ALL);
// ini_set('display_errors', '1');

if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

// Composer autoload (loads your app too)
require __DIR__ . '/../vendor/autoload.php';

/**
 * Optional: tiny fallback so tests that call view() don't crash
 * if your real view() helper isn't loaded in this context.
 * You can delete this block if your app already defines view().
 */
if (!function_exists('view')) {
  function view($template, array $data = []) {
    if (!empty($data['errors'])) {
      foreach ($data['errors'] as $msg) echo (string)$msg, "\n";
    } else {
      echo "[VIEW:$template]";
    }
  }
}
