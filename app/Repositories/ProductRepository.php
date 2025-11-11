<?php
class ProductRepository {
  public function __construct(private PDO $db) {}

  public function paginate(int $page, int $perPage, string $sortBy='name', string $dir='asc'): array {
    $allowed = ['name','price','quantity_available','created_at'];
    if(!in_array($sortBy, $allowed)) $sortBy = 'name';
    $dir = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';
    $offset = ($page-1)*$perPage;

    $total = (int)$this->db->query("SELECT COUNT(*) FROM products")->fetchColumn();

    $stmt = $this->db->prepare("SELECT * FROM products ORDER BY $sortBy $dir LIMIT :lim OFFSET :off");
    $stmt->bindValue(':lim',  $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':off', ($page-1)*$perPage, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    return ['data'=>$rows,'total'=>$total];
  }

  public function find(int $id): ?array {
    $stmt = $this->db->prepare("SELECT * FROM products WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
  }

  public function create(string $name, float $price, int $qty): int {
    $stmt = $this->db->prepare("INSERT INTO products (name, price, quantity_available) VALUES (?,?,?)");
    $stmt->execute([$name, $price, $qty]);
    return (int)$this->db->lastInsertId();
  }

  public function update(int $id, string $name, float $price, int $qty): bool {
    $stmt = $this->db->prepare("UPDATE products SET name=?, price=?, quantity_available=? WHERE id=?");
    return $stmt->execute([$name, $price, $qty, $id]);
  }

  public function delete(int $id): bool
  {
      try {
          $this->db->beginTransaction();

          $delTx = $this->db->prepare("DELETE FROM transactions WHERE product_id = ?");
          $delTx->execute([$id]);

          $delProduct = $this->db->prepare("DELETE FROM products WHERE id = ? LIMIT 1");
          $delProduct->execute([$id]);

          $ok = $delProduct->rowCount() === 1;
          if ($ok) {
              $this->db->commit();
              return true;
          }
          $this->db->rollBack();
          return false;
      } catch (\PDOException $e) {
          if ($this->db->inTransaction()) $this->db->rollBack();
          throw $e;
      }
  }


  public function decreaseStock(int $productId, int $quantity): bool
  {
    try {
        $this->db->beginTransaction();

        $sel = $this->db->prepare("SELECT quantity_available FROM products WHERE id = :id FOR UPDATE");
        $sel->execute([':id' => $productId]);
        $row = $sel->fetch(\PDO::FETCH_ASSOC);

        if (!$row || (int)$row['quantity_available'] < $quantity) {
            $this->db->rollBack();
            return false;
        }

        $upd = $this->db->prepare("
            UPDATE products
            SET quantity_available = quantity_available - :q
            WHERE id = :id
            LIMIT 1
        ");
        $upd->execute([':q' => $quantity, ':id' => $productId]);

        $this->db->commit();
        return $upd->rowCount() === 1;
    } catch (\Throwable $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        return false;
    }
  }

}
