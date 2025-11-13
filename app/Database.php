<?php
class Database {
  private PDO $pdo;

  /**
   * Accepts EITHER the whole config (with ['db'=>...]) OR just the ['db'] subarray.
   */
  public function __construct(array $config) {
    // Allow both shapes: ['db'=>...] or just the db subarray itself
    $db = $config['db'] ?? $config;

    // Validate required keys (user/pass can still be empty string)
    $host = $db['host'] ?? null;
    $name = $db['name'] ?? null;
    $user = $db['user'] ?? 'root';
    $pass = $db['pass'] ?? '';

    // Build DSN if not provided
    if (!empty($db['dsn'])) {
      $dsn = $db['dsn'];
    } else {
      if ($host === null || $name === null) {
        throw new RuntimeException("Database config missing 'host' or 'name' (or provide 'dsn').");
      }
      $port = isset($db['port']) ? (int)$db['port'] : 3307;
      $dsn  = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $name);
    }

    // Default PDO options if not provided
    $options = $db['options'] ?? [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Create PDO
    $this->pdo = new PDO($dsn, $user, $pass, $options);
  }

  public function pdo(): PDO { return $this->pdo; }
}
