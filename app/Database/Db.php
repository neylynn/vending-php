<?php
namespace App\Database;
use PDO;

class Db {
  private static ?PDO $pdo = null;

  public static function get(): PDO {
    if (self::$pdo === null) {
      // ✅ Use environment variables if set (for PHPUnit)
      $dsn  = getenv('DB_DSN');
      $user = getenv('DB_USER');
      $pass = getenv('DB_PASS');

      if ($dsn && $user !== false && $pass !== false) {
        // PHPUnit or custom env uses this
        self::$pdo = new PDO($dsn, $user, $pass, [
          PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
          PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return self::$pdo;
      }

      // ✅ Otherwise fall back to your real app config
      $cfg = require __DIR__ . '/../Config.php';
      if (!isset($cfg['db']) || !is_array($cfg['db'])) {
        throw new \RuntimeException("Database config 'db' is missing in app/Config.php");
      }
      $db = $cfg['db'];
      foreach (['host','name','user','pass'] as $k) {
        if (!array_key_exists($k, $db)) {
          throw new \RuntimeException("Database config missing key: '{$k}'");
        }
      }

      $host = $db['host'];
      $port = isset($db['port']) ? (int)$db['port'] : 3307;
      $dsn  = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $db['name']);

      self::$pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
      ]);
    }
    return self::$pdo;
  }
}
