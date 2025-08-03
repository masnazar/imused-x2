<?php
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use App\Controllers\MarketplaceTransaction;

if (!function_exists('auth')) {
    function auth()
    {
        return new class {
            public function userHasPermission(string $permission): bool
            {
                return true;
            }
        };
    }
}

final class MarketplaceTransactionStoreTest extends CIUnitTestCase
{
    public function testStoreRejectsWrongContentType(): void
    {
        $request = Services::request();
        $request->setMethod('POST');
        $request->setHeader('Content-Type', 'application/json');

        $controller = new MarketplaceTransaction();
        $controller->initController($request, Services::response(), Services::logger());

        $response = $controller->store('tokopedia');

        $this->assertSame(415, $response->getStatusCode());
    }
}
