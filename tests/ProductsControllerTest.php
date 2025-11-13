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
    private int $productId; // store inserted product id

    protected function setUp(): void
    {
        $this->pdo = \App\Database\Db::get();
        $this->pdo->beginTransaction();

        // minimal seed for the test
        $this->pdo->exec("DELETE FROM transactions");
        $this->pdo->exec("DELETE FROM products");
        $stmt = $this->pdo->prepare(
            "INSERT INTO products (name,price,quantity_available) VALUES (?,?,?)"
        );
        $stmt->execute(['Coke', 2.99, 2]);

        // get the actual id of the inserted product
        $this->productId = (int)$this->pdo->lastInsertId();

        // reset redirect memory
        \ProductsController::$lastRedirect = null;

        // ensure clean session
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        if ($this->pdo->inTransaction()) {
            $this->pdo->rollBack(); // DB returns to pre-test state
        }
        header_remove(); // clear headers between tests
        $_SESSION = [];
    }

    private function assertRedirect(string $expected): void
    {
        $this->assertSame(
            $expected,
            \ProductsController::$lastRedirect,
            'Expected redirect to ' . $expected
        );
    }

    public function testPurchaseFormRedirectsWhenGuest(): void
    {
        $ctrl = new \ProductsController(); // global class (no namespace)

        // id doesn't really matter here (guest is redirected before DB),
        // but use the real one anyway:
        $ctrl->purchaseForm(['id' => $this->productId]);

        $this->assertRedirect('/login');
    }

    public function testPurchaseInsufficientStock(): void
    {
        // logged-in user
        $_SESSION['user'] = ['id'=>1,'email'=>'u@x.com','role'=>'User'];

        $ctrl = new \ProductsController();
        $_POST = ['quantity'=>99999];

        ob_start();
        // use the actual product id we inserted in setUp()
        $ctrl->purchase(['id' => $this->productId]);
        $out = ob_get_clean();

        $this->assertStringContainsString('Insufficient stock', $out);
    }

    public function testCreateValidationFails(): void
    {
        // If create() requires auth, mimic admin
        $_SESSION['user'] = ['id'=>1,'email'=>'a@b.com','role'=>'Admin'];

        $ctrl = new \ProductsController();
        $_POST = ['name'=>'','price'=>'-1','quantity_available'=>'-5'];

        ob_start();
        $ctrl->create();
        $out = ob_get_clean();

        $this->assertMatchesRegularExpression(
            '/Name is required|Price must be positive|non-negative/',
            $out
        );
    }
}
