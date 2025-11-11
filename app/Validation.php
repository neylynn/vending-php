<?php
function validateProduct(array $in, ?PDO $pdo = null, ?int $ignoreId = null): array {
  $errors = [];

  $name  = trim($in['name'] ?? '');
  $price = $in['price'] ?? null;
  $qty   = $in['quantity_available'] ?? null;

  // Basic checks
  if ($name === '') {
    $errors[] = 'Name is required.';
  }
  if ($price === null || $price === '' || !is_numeric($price) || (float)$price <= 0) {
    $errors[] = 'Price must be greater than 0.';
  }
  if ($qty === null || $qty === '' || filter_var($qty, FILTER_VALIDATE_INT) === false || (int)$qty < 0) {
    $errors[] = 'Quantity must be a non-negative integer.';
  }

  // Optional UNIQUE(name) check (case-insensitive)
  if (!$errors && $pdo) {
    $sql = 'SELECT 1 FROM products WHERE LOWER(name) = LOWER(?)';
    $params = [$name];
    if ($ignoreId !== null) { $sql .= ' AND id <> ?'; $params[] = $ignoreId; }
    $st = $pdo->prepare($sql);
    $st->execute($params);
    if ($st->fetchColumn()) {
      $errors[] = 'Name already exists.';
    }
  }

  $clean = [
    'name'               => $name,
    'price'              => round((float)$price, 3),
    'quantity_available' => (int)$qty,
  ];
  return [$errors, $clean];
}
