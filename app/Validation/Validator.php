<?php
namespace App\Validation;
class Validator {
  public static function product(array $in): array {
    $errors = [];
    $name = trim((string)($in['name'] ?? ''));
    $price = (float)($in['price'] ?? 0);
    $qty = (int)($in['quantity_available'] ?? -1);

    if ($name === '') $errors['name'] = 'Name is required';
    if (!is_numeric($in['price'] ?? null) || $price <= 0) $errors['price'] = 'Price must be positive';
    if (!is_numeric($in['quantity_available'] ?? null) || $qty < 0) $errors['quantity_available'] = 'Quantity must be non-negative';

    return [$errors, ['name'=>$name,'price'=>$price,'quantity_available'=>$qty]];
  }
}
