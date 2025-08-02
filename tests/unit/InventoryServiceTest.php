<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use App\Services\InventoryService;
use App\Repositories\InventoryRepository;

final class InventoryServiceTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Services::reset();
        session()->set('user_id', 99);
    }

    public function testIncreaseStockUpdatesRepositoryAndLogs(): void
    {
        $repo = new class extends InventoryRepository {
            public array $updateArgs;
            public array $logData;
            public function __construct() {}
            public function updateStock($warehouseId, $productId, $quantity)
            {
                $this->updateArgs = func_get_args();
            }
            public function insertLog($data)
            {
                $this->logData = $data;
            }
        };

        $service = new InventoryService($repo);
        $service->increaseStock(1, 2, 5, 'test');

        $this->assertSame([1, 2, 5], $repo->updateArgs);
        $this->assertSame(99, $repo->logData['user_id']);
        $this->assertSame('in', $repo->logData['type']);
        $this->assertSame(5, $repo->logData['quantity']);
        $this->assertSame('test', $repo->logData['note']);
    }

    public function testDecreaseStockUpdatesRepositoryAndLogs(): void
    {
        $repo = new class extends InventoryRepository {
            public array $updateArgs;
            public array $logData;
            public function __construct() {}
            public function updateStock($warehouseId, $productId, $quantity)
            {
                $this->updateArgs = func_get_args();
            }
            public function insertLog($data)
            {
                $this->logData = $data;
            }
        };

        $service = new InventoryService($repo);
        $service->decreaseStock(1, 2, 3, 'less');

        $this->assertSame([1, 2, -3], $repo->updateArgs);
        $this->assertSame(99, $repo->logData['user_id']);
        $this->assertSame('out', $repo->logData['type']);
        $this->assertSame(3, $repo->logData['quantity']);
        $this->assertSame('less', $repo->logData['note']);
    }
}

