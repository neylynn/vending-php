<?php
class TransactionRepository {
  public function __construct(private PDO $db) {}

  public function log(int $userId, int $productId, int $qty, float $unitPrice): int {
    $total = $unitPrice * $qty;
    $st=$this->db->prepare(
      "INSERT INTO transactions (user_id,product_id,quantity,unit_price,total_price) VALUES (?,?,?,?,?)"
    );
    $st->execute([$userId,$productId,$qty,$unitPrice,$total]);
    return (int)$this->db->lastInsertId();
  }
}
