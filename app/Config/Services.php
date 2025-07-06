<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Services\SettingService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
            public static function settings($getShared = true)
        {
            if ($getShared) {
                return static::getSharedInstance('settings');
            }

            return new SettingService();
        }

        public static function MarketplaceTransactionService($getShared = true)
        {
            if ($getShared) {
                return static::getSharedInstance('MarketplaceTransactionService');
            }
        
            return new \App\Services\MarketplaceTransactionService(
                new \App\Repositories\MarketplaceTransactionRepository()
            );
        }

        public static function ForecastService(): \App\Services\ForecastService
        {
            return new \App\Services\ForecastService(
                new \App\Repositories\MarketplaceTransactionRepository(),
                new \App\Repositories\ProductRepository()
            );
        }

        public static function binderbyteClient()
{
    $client = \Config\Services::curlrequest([
        'baseURI' => 'https://api.binderbyte.com/',
    ]);
    return $client;
}

public static function menu($getShared = true)
{
    if ($getShared) {
        return static::getSharedInstance('menu');
    }

    return new \App\Services\MenuService();
}

public static function brandExpenseService($getShared = true)
{
    if ($getShared) {
        return static::getSharedInstance('brandExpenseService');
    }

    return new \App\Services\BrandExpenseService(
        new \App\Repositories\BrandExpenseRepository(new \App\Models\BrandExpenseModel()),
        new \App\Repositories\ExpenseRepository(new \App\Models\ExpenseModel())
    );
}

}
