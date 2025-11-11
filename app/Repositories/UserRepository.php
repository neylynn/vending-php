<?php
class UserRepository {
  public function __construct(private PDO $db) {}

  public function findByEmail(string $email): ?array {
    $st=$this->db->prepare("SELECT * FROM users WHERE email=?");
    $st->execute([$email]); $u=$st->fetch(); return $u ?: null;
  }

  public function find(int $id): ?array {
    $st=$this->db->prepare("SELECT * FROM users WHERE id=?");
    $st->execute([$id]); $u=$st->fetch(); return $u ?: null;
  }

  public function create(string $email, string $passwordHash, string $role='User'): int {
    $st=$this->db->prepare("INSERT INTO users (email,password_hash,role) VALUES (?,?,?)");
    $st->execute([$email,$passwordHash,$role]);
    return (int)$this->db->lastInsertId();
  }
}
