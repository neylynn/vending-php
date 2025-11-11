<?php
// Force-load the needed app files so autoloading is not required
require __DIR__ . '/../app/Database/Db.php';
require __DIR__ . '/../app/Controllers/ProductsController.php';
require __DIR__ . '/../app/Auth/SessionAuth.php';
require __DIR__ . '/../app/Validation/Validator.php';

use PHPUnit\Framework\TestCase;

final class ProductsControllerTest extends TestCase
{
  private \PDO $pdo;

  protected function setUp(): void {
    $this->pdo = \App\Database\Db::get();
    $this->pdo->beginTransaction();

    // minimal seed for the test
    $this->pdo->exec("DELETE FROM transactions");
    $this->pdo->exec("DELETE FROM products");
    $stmt = $this->pdo->prepare("INSERT INTO products (name,price,quantity_available) VALUES (?,?,?)");
    $stmt->execute(['Coke', 2.99, 2]);
  }

  protected function tearDown(): void {
    if ($this->pdo->inTransaction()) {
      $this->pdo->rollBack(); // DB returns to pre-test state
    }
    header_remove(); // clear headers between tests
    $_SESSION = [];
  }

  private function assertRedirect(string $expected) {
    $loc = null;
    foreach (headers_list() as $h) {
      if (stripos($h, 'Location:') === 0) {
        $loc = trim(substr($h, 9));
      }
    }
    $this->assertSame($expected, $loc, 'Expected redirect to '.$expected);
  }

  public function testPurchaseFormRedirectsWhenGuest(): void {
    $ctrl = new \ProductsController(); // global class (no namespace)
    ob_start();
    try { $ctrl->purchaseForm(['id'=>1]); } catch (\Throwable $e) {}
    ob_end_clean();
    $this->assertRedirect('/login');

    $this->addToAssertionCount(1);
  }

  public function testPurchaseInsufficientStock(): void {
    // logged-in user
    $_SESSION['user'] = ['id'=>1,'email'=>'u@x.com','role'=>'User'];

    $ctrl = new \ProductsController();
    $_POST = ['quantity'=>99999];

    ob_start();
    try { $ctrl->purchase(['id'=>1]); } catch (\Throwable $e) {}
    $out = ob_get_clean();

    $this->assertStringContainsString('Insufficient stock', $out);

    $this->addToAssertionCount(1);
  }

  public function testCreateValidationFails(): void {
    // If create() requires auth, mimic admin
    $_SESSION['user'] = ['id'=>1,'email'=>'a@b.com','role'=>'Admin'];

    $ctrl = new \ProductsController();
    $_POST = ['name'=>'','price'=>'-1','quantity_available'=>'-5'];

    ob_start();
    try { $ctrl->create(); } catch (\Throwable $e) {}
    $out = ob_get_clean();

    $this->assertMatchesRegularExpression('/Name is required|Price must be positive|non-negative/', $out);

    $this->addToAssertionCount(1);
  }
}
