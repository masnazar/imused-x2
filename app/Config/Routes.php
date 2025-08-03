<?php

use CodeIgniter\Router\RouteCollection;

/**
 * ---------------------------------------------------------------
 * ROUTES FILE
 * ---------------------------------------------------------------
 * Semua definisi routing aplikasi disimpan di sini.
 */

// ======================= AUTH =======================
$routes->group('', function ($routes) {
    $routes->get('register', 'Auth::register');
    $routes->post('register', 'Auth::processRegister');
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::processLogin');
    $routes->get('logout', 'Auth::logout');
    $routes->get('activate/(:any)/(:any)', 'Auth::activate/$1/$2');
    $routes->get('forgot-password', 'Auth::forgotPassword');
    $routes->post('forgot-password', 'Auth::processForgotPassword');
    $routes->get('reset-password/(:any)', 'Auth::resetPasswordForm/$1');
    $routes->post('reset-password/(:any)', 'Auth::processResetPassword/$1');
});

// ======================= DASHBOARD & PROFILE =======================
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Home::index');
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('profile', 'Profile::edit');
    $routes->post('profile/update', 'Profile::update');
    $routes->post('profile/update-password', 'Profile::updatePassword');
});

// ======================= SUPPLIER =======================
$routes->group('suppliers', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Supplier::index');
    $routes->get('create', 'Supplier::create');
    $routes->post('store', 'Supplier::store');
    $routes->get('edit/(:num)', 'Supplier::edit/$1');
    $routes->post('update/(:num)', 'Supplier::update/$1');
    $routes->post('delete/(:num)', 'Supplier::delete/$1');
});

// ======================= COURIER =======================
$routes->group('courier', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Courier::index');
    $routes->get('create', 'Courier::create');
    $routes->post('store', 'Courier::store');
    $routes->get('edit/(:num)', 'Courier::edit/$1');
    $routes->post('update/(:num)', 'Courier::update/$1');
});
$routes->get('api/couriers', 'Api\CourierApi::list', ['filter' => 'auth']);

// ======================= SETTINGS =======================
$routes->group('settings', ['filter' => 'admin'], function ($routes) {
    $routes->get('/', 'Setting::index');
    $routes->get('system', 'Setting::systemConfig');
    $routes->post('system/update', 'Setting::updateSystemConfig');
    $routes->get('email', 'Setting::emailConfig');
    $routes->post('email/update', 'Setting::updateEmailConfig');
    $routes->get('security', 'Setting::securityConfig');
    $routes->post('security/update', 'Setting::updateSecurityConfig');
    $routes->get('menu', 'Setting::menuConfig');
    $routes->post('menu/toggle', 'Setting::toggleMenuAccess');
    $routes->post('menu/save-matrix', 'Setting::saveMatrixAccess');
    $routes->post('menu/roles', 'Setting::updateMenuRoles');
});

$routes->group('settings/menus', ['filter' => 'admin'], function ($routes) {
    $routes->get('/', 'MenuManagement::index');
    $routes->post('save', 'MenuManagement::save');
    $routes->get('delete/(:num)', 'MenuManagement::delete/$1');
});

// ======================= PERMISSIONS =======================
$routes->group('permissions', ['filter' => 'admin'], function ($routes) {
    $routes->get('/', 'Permission::index');
    $routes->get('create', 'Permission::create');
    $routes->post('store', 'Permission::store');
    $routes->get('edit/(:num)', 'Permission::edit/$1');
    $routes->post('update/(:num)', 'Permission::update/$1');
    $routes->post('delete/(:num)', 'Permission::delete/$1');
    $routes->get('roles', 'Permission::manageRoles');
    $routes->post('roles/update', 'Permission::updateRolePermissions');
    $routes->get('assign', 'Permission::assign');
    $routes->post('assign_process', 'Permission::assignProcess');
    $routes->get('assign-user', 'UserPermission::index');
    $routes->post('assign-user', 'UserPermission::assign');
    $routes->get('user-permissions/(:num)', 'UserPermission::getUserPermissions/$1');
});

// ======================= ROLES =======================
$routes->group('roles', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Role::index');
    $routes->get('create', 'Role::create');
    $routes->post('store', 'Role::store');
    $routes->get('edit/(:num)', 'Role::edit/$1');
    $routes->post('update/(:num)', 'Role::update/$1');
    $routes->post('delete/(:num)', 'Role::delete/$1');
});

// ======================= BRANDS =======================
$routes->group('brands', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Brand::index');
    $routes->get('create', 'Brand::create');
    $routes->post('store', 'Brand::store');
    $routes->get('edit/(:num)', 'Brand::edit/$1');
    $routes->post('update/(:num)', 'Brand::update/$1');
    $routes->post('delete/(:num)', 'Brand::delete/$1');
});

// ======================= PRODUCTS =======================
$routes->group('products', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Product::index');
    $routes->get('create', 'Product::create');
    $routes->post('store', 'Product::store');
    $routes->get('edit/(:num)', 'Product::edit/$1');
    $routes->post('update/(:num)', 'Product::update/$1');
    $routes->post('delete/(:num)', 'Product::delete/$1');
});

// ======================= WAREHOUSE =======================
$routes->group('warehouse', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Warehouse::index');
    $routes->get('create', 'Warehouse::create');
    $routes->post('store', 'Warehouse::store');
    $routes->get('edit/(:num)', 'Warehouse::edit/$1');
    $routes->post('update/(:num)', 'Warehouse::update/$1');
    $routes->post('delete/(:num)', 'Warehouse::delete/$1');
});

// ======================= INVENTORY =======================
$routes->group('inventory', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Inventory::index');
    $routes->post('update-stock', 'Inventory::updateStock');
    $routes->get('logs', 'Inventory::logs');
    $routes->get('datatable', 'Inventory::datatable');
});

// ======================= PURCHASE ORDER =======================
$routes->group('purchase-orders', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'PurchaseOrder::index');
    $routes->get('create', 'PurchaseOrder::create');
    $routes->post('store', 'PurchaseOrder::store');
    $routes->get('edit/(:num)', 'PurchaseOrder::edit/$1');
    $routes->post('update/(:num)', 'PurchaseOrder::update/$1');
    $routes->get('receive/(:num)', 'PurchaseOrder::receive/$1');
    $routes->post('processReceive/(:num)', 'PurchaseOrder::processReceive/$1');
    $routes->post('store-receive', 'PurchaseOrder::storeReceive');
    $routes->post('delete/(:num)', 'PurchaseOrder::delete/$1');
    $routes->get('view/(:num)', 'PurchaseOrder::view/$1');
    $routes->post('getData', 'PurchaseOrder::getData');
    $routes->post('getStatistics', 'PurchaseOrder::getStatistics');
    $routes->get('get_products_by_supplier/(:num)', 'PurchaseOrder::get_products_by_supplier/$1');
});
$routes->get('api/get-product-sku/(:num)', 'PurchaseOrder::get_product_sku/$1');

// ======================= STOCK TRANSACTIONS =======================
$routes->group('stock-transactions', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'StockTransaction::index');
    $routes->post('get-stock-transactions', 'StockTransaction::getStockTransactions');
    $routes->post('statistics', 'StockTransaction::getStatistik');
});

// ======================= MARKETPLACE =======================
$routes->group('marketplace-transactions', ['filter' => 'auth'], function ($routes) {
    $routes->get('all', 'MarketplaceTransaction::all');

    // Unified endpoints for fetching data and statistics
    $routes->get('(:segment)', 'MarketplaceTransaction::index/$1');
    $routes->post('get-data/(:segment)', 'MarketplaceTransaction::getTransactions/$1');
    $routes->post('get-statistics/(:segment)', 'MarketplaceTransaction::getStatistics/$1');
    $routes->get('create/(:segment)', 'MarketplaceTransaction::create/$1');
    $routes->post('store/(:segment)', 'MarketplaceTransaction::store/$1');
    $routes->get('edit/(:segment)/(:num)', 'MarketplaceTransaction::edit/$1/$2');
    $routes->post('update/(:segment)/(:num)', 'MarketplaceTransaction::update/$1/$2');
    $routes->get('detail/(:segment)/(:num)', 'MarketplaceTransaction::detail/$1/$2');
    $routes->post('delete/(:segment)/(:num)', 'MarketplaceTransaction::delete/$1/$2');
    $routes->post('import/(:segment)', 'MarketplaceTransaction::importExcel/$1');
    $routes->get('template/(:segment)', 'MarketplaceTransaction::downloadTemplate/$1');
    $routes->get('confirm-import/(:segment)', 'MarketplaceTransaction::confirmImport/$1');
    $routes->post('save-imported-data/(:segment)', 'MarketplaceTransaction::saveImportedData/$1');
    $routes->post('track-resi', 'MarketplaceTransaction::trackResi');
    $routes->post('update-resi/(:segment)/(:num)', 'MarketplaceTransaction::updateResiStatus/$1/$2');
});

// ======================= FORECAST =======================
$routes->group('forecast', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Forecast::index');
    $routes->post('predict-single', 'Forecast::predictSingle');
    $routes->post('predict-all', 'Forecast::predictAll');
});

// ======================= SOSCOM TRANSAKSI =======================
$routes->group('soscom-transactions', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'SoscomTransaction::index');
    $routes->post('get-data', 'SoscomTransaction::getData');
    $routes->post('import', 'SoscomTransaction::importExcel');
    $routes->get('confirm-import', 'SoscomTransaction::confirmImport');
    $routes->post('save-import', 'SoscomTransaction::saveImportedData');
    $routes->post('track-resi', 'SoscomTransaction::trackResi');
    $routes->get('download-template', 'SoscomTransaction::downloadTemplate');
    $routes->post('get-statistics', 'SoscomTransaction::getStatistics');
});

// ======================= CRM TRANSAKSI =======================
$routes->group('crm-transactions', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'CrmTransaction::index');
    $routes->post('get-data', 'CrmTransaction::getData');
    $routes->post('import', 'CrmTransaction::importExcel');
    $routes->get('confirm-import', 'CrmTransaction::confirmImport');
    $routes->post('save-imported', 'CrmTransaction::saveImportedData');
    $routes->post('get-statistics', 'CrmTransaction::getStatistics');
    $routes->get('download-template', 'CrmTransaction::downloadTemplate');
});

// ======================= REGIONAL API =======================
$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->get('provinces', 'Region::provinces');
    $routes->get('cities/(:any)', 'Region::cities/$1');
    $routes->get('districts/(:any)', 'Region::districts/$1');
    $routes->get('villages/(:any)', 'Region::villages/$1');
    $routes->get('postal-code/(:segment)', 'Region::postalCode/$1');
});

// ======================= KODE POS =======================
$routes->group('postalcode', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'PostalCode::index');
    $routes->post('runBatch', 'PostalCode::runBatch');
    $routes->get('list', 'PostalCode::list');
    $routes->get('ajaxList', 'PostalCode::ajaxList');
});
$routes->get('postalcode/reference', 'PostalCodeReference::index');

// ======================= CUSTOMER =======================
$routes->group('customers', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Customer::index');
    $routes->post('data', 'Customer::getData');
    $routes->get('detail/(:num)', 'Customer::detail/$1');
    $routes->get('filters', 'Customer::filters');
    $routes->post('history/(:num)', 'Customer::history/$1');
    $routes->post('update/(:num)', 'Customer::update/$1');
    $routes->get('edit/(:num)', 'Customer::edit/$1');
});

// ======================= TEAM SOSCOM =======================
$routes->group('soscom-teams', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'SoscomTeam::index');
    $routes->get('create', 'SoscomTeam::create');
    $routes->post('store', 'SoscomTeam::store');
    $routes->get('edit/(:num)', 'SoscomTeam::edit/$1');
    $routes->post('update/(:num)', 'SoscomTeam::update/$1');
    $routes->get('delete/(:num)', 'SoscomTeam::delete/$1');
});

// ======================= CHART OF ACCOUNTS =======================
// Pastikan namespace sesuai dengan controller yang digunakan
$routes->group('chart-of-accounts', ['namespace'=>'App\Controllers'], function($r) {
    $r->get('/',             'ChartOfAccounts::index');
    $r->post('get-data',     'ChartOfAccounts::getData');
    $r->get('create',        'ChartOfAccounts::create');
    $r->post('store',        'ChartOfAccounts::store');
    $r->get('edit/(:num)',   'ChartOfAccounts::edit/$1');
    $r->post('update/(:num)', 'ChartOfAccounts::update/$1');
    $r->delete('delete/(:num)','ChartOfAccounts::delete/$1');
});

// ======================= EXPENSES =======================
$routes->group('expenses', ['namespace'=>'App\Controllers'], function($routes){
    $routes->get(   '/',             'Expenses::index');
    $routes->post(  'get-data',     'Expenses::getData');
    $routes->get(   'create',       'Expenses::create');
    $routes->post(  'store',        'Expenses::store');
    $routes->get(   'edit/(:num)',  'Expenses::edit/$1');
    $routes->post(  'update/(:num)','Expenses::update/$1');
    $routes->post(  'get-statistics',  'Expenses::getStatistics');
    $routes->delete('delete/(:num)','Expenses::delete/$1');
    $routes->post('analyze', 'Expenses::analyze');

});

// ======================= PLATFORMS =======================
$routes->group('platforms', function($routes) {
    $routes->get(   '',             'Platforms::index');
    $routes->post(  'get-data',     'Platforms::getData');
    $routes->get(   'create',       'Platforms::create');
    $routes->post(  'store',        'Platforms::store');
    $routes->get(   'edit/(:num)',  'Platforms::edit/$1');
    $routes->post(  'update/(:num)','Platforms::update/$1');
    $routes->delete('delete/(:num)','Platforms::delete/$1');
});


// ======================= BRAND EXPENSES =======================
$routes->group('brand-expenses', ['namespace' => 'App\Controllers'], function($r){
    $r->get('','BrandExpenses::index');
    $r->post('getData','BrandExpenses::getData');
    $r->get('create','BrandExpenses::create');
    $r->post('store','BrandExpenses::store');
    $r->get('edit/(:num)','BrandExpenses::edit/$1');
    $r->post('update/(:num)','BrandExpenses::update/$1');
    $r->delete('delete/(:num)','BrandExpenses::delete/$1');
});

// ======================= STORES =======================
$routes->group('stores', ['filter' => 'auth'], static function($routes){
    $routes->get('',              'Stores::index');
    $routes->post('get-data',     'Stores::getData');
    $routes->get('create',        'Stores::create');
    $routes->post('store',        'Stores::store');
    $routes->get('edit/(:num)',   'Stores::edit/$1');
    $routes->post('update/(:num)','Stores::update/$1');
    $routes->delete('delete/(:num)','Stores::delete/$1');
});

$routes->get(   'customer-services',           'CustomerServices::index');
$routes->post(  'customer-services/get-data',  'CustomerServices::getData');
$routes->get(   'customer-services/create',    'CustomerServices::create');
$routes->post(  'customer-services/store',     'CustomerServices::store');
$routes->get(   'customer-services/edit/(:num)','CustomerServices::edit/$1');
$routes->post(  'customer-services/update/(:num)','CustomerServices::update/$1');
$routes->delete('customer-services/delete/(:num)','CustomerServices::delete/$1');

