<?php

use CodeIgniter\Test\CIUnitTestCase;
use Config\Autoload;

final class HelperCleanupTest extends CIUnitTestCase
{
    public function testRemovedHelpersAreUnavailable(): void
    {
        $this->assertFalse(function_exists('logtrail'));
        $this->assertFalse(function_exists('hasRole'));
        $this->assertFalse(function_exists('has_permission'));
    }

    public function testRemovedHelpersNotAutoloaded(): void
    {
        $config = new Autoload();
        $this->assertNotContains('logtrail', $config->helpers);
        $this->assertNotContains('role', $config->helpers);
        $this->assertNotContains('permission', $config->helpers);
        $this->assertNotContains('periode', $config->helpers);
    }
}
