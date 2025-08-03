<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Services\MarketplaceTransactionService;

final class MarketplaceTransactionServiceTest extends CIUnitTestCase
{
    public function testPlatformSpecificRuleApplied(): void
    {
        $service = new class extends MarketplaceTransactionService
        {
            public function __construct() {}
        };

        $rules = $service->getValidationRules('shopee');

        $this->assertArrayHasKey('tracking_number', $rules);
    }
}
