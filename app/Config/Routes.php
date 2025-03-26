<?php

use CodeIgniter\Router\RouteCollection;

/**
 * File Routes
 * 
 * File ini berisi semua definisi routing untuk aplikasi.
 * Setiap grup route dilengkapi dengan middleware dan docBlock
 * untuk mempermudah pemahaman.
 */

/**
 * 📌 Routes untuk Autentikasi
 * 
 * Menangani proses:
 * - Registrasi
 * - Login
 * - Logout
 * - Aktivasi akun
 * - Lupa & Reset Password
 */
$routes->group('', function ($routes) {
    // Registrasi
    $routes->get('/register', 'Auth::register');
    $routes->post('/register', 'Auth::processRegister');

    // Login
    $routes->get('/login', 'Auth::login');
    $routes->post('/login', 'Auth::processLogin');

    // Logout
    $routes->get('/logout', 'Auth::logout');

    // Aktivasi akun
    $routes->get('/activate/(:any)/(:any)', 'Auth::activate/$1/$2');

    // Lupa & Reset Password
    $routes->get('forgot-password', 'Auth::forgotPassword');
    $routes->post('forgot-password', 'Auth::processForgotPassword');
    $routes->get('reset-password/(:any)', 'Auth::resetPasswordForm/$1');
    $routes->post('reset-password/(:any)', 'Auth::processResetPassword/$1');
});

/**
 * 📌 Routes untuk Halaman yang Membutuhkan Login
 * 
 * Middleware `auth` memastikan hanya user yang sudah login
 * yang dapat mengakses halaman ini.
 */
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Home::index');
    $routes->get('/dashboard', 'Dashboard::index');
    $routes->get('/profile', 'Profile::edit');
    $routes->post('/profile/update', 'Profile::update');
    $routes->post('profile/update-password', 'Profile::updatePassword');
});

/**
 * 📌 Routes untuk Modul Supplier
 * 
 * Middleware `auth` dan `permission` memastikan user memiliki
 * izin yang sesuai untuk mengakses setiap endpoint.
 */
$routes->group('suppliers', ['filter' => 'auth'], function ($routes) {
    // 📄 Melihat daftar supplier
    $routes->get('/', 'Supplier::index', [
        'as' => 'suppliers.index',
        'filter' => 'permission:view_supplier'
    ]);

    // ➕ Menambahkan supplier
    $routes->get('create', 'Supplier::create', [
        'as' => 'suppliers.create',
        'filter' => 'permission:create_supplier'
    ]);
    $routes->post('store', 'Supplier::store', [
        'as' => 'suppliers.store',
        'filter' => 'permission:create_supplier'
    ]);

    // ✏️ Mengedit supplier
    $routes->get('edit/(:num)', 'Supplier::edit/$1', [
        'as' => 'suppliers.edit',
        'filter' => 'permission:edit_supplier'
    ]);
    $routes->post('update/(:num)', 'Supplier::update/$1', [
        'as' => 'suppliers.update',
        'filter' => 'permission:edit_supplier'
    ]);

    // ❌ Menghapus supplier
    $routes->post('delete/(:num)', 'Supplier::delete/$1', [
        'as' => 'suppliers.delete',
        'filter' => 'permission:delete_supplier'
    ]);
});


/**
 * 📌 Routes untuk Halaman Admin
 * 
 * Middleware `admin` memastikan hanya user dengan role_id = 1
 * (Administrator) yang dapat mengakses halaman ini.
 */
$routes->group('', ['filter' => 'admin'], function ($routes) {
    $routes->get('settings', 'Setting::index', ['as' => 'settings.index']);

    $routes->group('settings', function ($routes) {
        $routes->get('system', 'Setting::systemConfig', ['as' => 'settings.system']);
        $routes->post('system/update', 'Setting::updateSystemConfig', ['as' => 'settings.system.update']);
        $routes->get('email', 'Setting::emailConfig', ['as' => 'settings.email']);
        $routes->post('email/update', 'Setting::updateEmailConfig', ['as' => 'settings.email.update']);
        $routes->get('security', 'Setting::securityConfig', ['as' => 'settings.security']);
        $routes->post('security/update', 'Setting::updateSecurityConfig', ['as' => 'settings.security.update']);
    });
});

/**
 * 📌 Routes untuk Manage Permissions
 * 
 * Middleware `admin` memastikan hanya Administrator yang dapat
 * mengakses halaman ini.
 */
$routes->group('permissions', ['filter' => 'admin'], function ($routes) {
    $routes->get('/', 'Permission::index', ['as' => 'permissions.index']);
    $routes->get('create', 'Permission::create', ['as' => 'permissions.create']);
    $routes->post('store', 'Permission::store', ['as' => 'permissions.store']);
    $routes->get('edit/(:num)', 'Permission::edit/$1', ['as' => 'permissions.edit']);
    $routes->post('update/(:num)', 'Permission::update/$1', ['as' => 'permissions.update']);
    $routes->post('delete/(:num)', 'Permission::delete/$1', ['as' => 'permissions.delete']);
    $routes->get('get_assigned_permissions/(:num)', 'Permission::getAssignedPermissions/$1', ['as' => 'permissions.getAssigned']);
    $routes->get('roles', 'Permission::manageRoles', ['as' => 'permissions.roles']);
    $routes->post('roles/update', 'Permission::updateRolePermissions', ['as' => 'permissions.roles.update']);
    $routes->get('assign', 'Permission::assign', ['as' => 'permissions.assign']);
    $routes->post('assign_process', 'Permission::assignProcess', ['as' => 'permissions.assign_process']);
});

/**
 * 📌 Routes untuk Modul Role
 * 
 * Middleware `role` memastikan hanya user dengan role tertentu
 * yang dapat mengakses halaman ini.
 */
$routes->group('roles', ['filter' => 'role:Administrator,Manager'], function ($routes) {
    $routes->get('/', 'Role::index', ['as' => 'roles.index']);
    $routes->get('create', 'Role::create', ['as' => 'roles.create']);
    $routes->post('store', 'Role::store', ['as' => 'roles.store']);
    $routes->get('edit/(:num)', 'Role::edit/$1', ['as' => 'roles.edit']);
    $routes->post('update/(:num)', 'Role::update/$1', ['as' => 'roles.update']);
    $routes->post('delete/(:num)', 'Role::delete/$1', ['as' => 'roles.delete']);
});

/**
 * 📌 Routes untuk Modul Brand
 * 
 * Middleware `auth` dan `permission` memastikan user memiliki
 * izin yang sesuai untuk mengakses setiap endpoint.
 */
$routes->group('brands', ['filter' => 'auth'], function ($routes) {
    // 📄 Melihat daftar brand
    $routes->get('/', 'Brand::index', [
        'as' => 'brands.index',
        'filter' => 'permission:view_brand'
    ]);

    // ➕ Menambahkan brand
    $routes->get('create', 'Brand::create', [
        'as' => 'brands.create',
        'filter' => 'permission:create_brand'
    ]);
    $routes->post('store', 'Brand::store', [
        'as' => 'brands.store',
        'filter' => 'permission:create_brand'
    ]);

    // ✏️ Mengedit brand
    $routes->get('edit/(:num)', 'Brand::edit/$1', [
        'as' => 'brands.edit',
        'filter' => 'permission:edit_brand'
    ]);
    $routes->post('update/(:num)', 'Brand::update/$1', [
        'as' => 'brands.update',
        'filter' => 'permission:edit_brand'
    ]);

    // ❌ Menghapus brand
    $routes->post('delete/(:num)', 'Brand::delete/$1', [
        'as' => 'brands.delete',
        'filter' => 'permission:delete_brand'
    ]);
});


/**
 * 📌 Routes untuk Modul Produk
 * 
 * Middleware `role` & `permission` memastikan user punya role & izin yang sesuai.
 */
$routes->group('products', ['filter' => 'role:Administrator,Manager'], function ($routes) {
    // 📄 Melihat daftar produk
    $routes->get('/', 'Product::index', [
        'as' => 'products.index',
        'filter' => 'permission:view_product'
    ]);

    // ➕ Menambahkan produk
    $routes->get('create', 'Product::create', [
        'as' => 'products.create',
        'filter' => 'permission:create_product'
    ]);
    $routes->post('store', 'Product::store', [
        'as' => 'products.store',
        'filter' => 'permission:create_product'
    ]);

    // ✏️ Mengedit produk
    $routes->get('edit/(:num)', 'Product::edit/$1', [
        'as' => 'products.edit',
        'filter' => 'permission:edit_product'
    ]);
    $routes->post('update/(:num)', 'Product::update/$1', [
        'as' => 'products.update',
        'filter' => 'permission:edit_product'
    ]);

    // ❌ Menghapus produk
    $routes->post('delete/(:num)', 'Product::delete/$1', [
        'as' => 'products.delete',
        'filter' => 'permission:delete_product'
    ]);
});


/**
 * 📌 Routes untuk Modul Gudang
 * 
 * Middleware `role` & `permission` memastikan user punya akses sesuai perannya.
 */
$routes->group('warehouse', ['filter' => 'role:Administrator,Manager'], function ($routes) {
    // 📄 Melihat daftar gudang
    $routes->get('/', 'Warehouse::index', [
        'as' => 'warehouse.index',
        'filter' => 'permission:view_warehouse'
    ]);

    // ➕ Menambahkan gudang
    $routes->get('create', 'Warehouse::create', [
        'as' => 'warehouse.create',
        'filter' => 'permission:create_warehouse'
    ]);
    $routes->post('store', 'Warehouse::store', [
        'as' => 'warehouse.store',
        'filter' => 'permission:create_warehouse'
    ]);

    // ✏️ Mengedit gudang
    $routes->get('edit/(:num)', 'Warehouse::edit/$1', [
        'as' => 'warehouse.edit',
        'filter' => 'permission:edit_warehouse'
    ]);
    $routes->post('update/(:num)', 'Warehouse::update/$1', [
        'as' => 'warehouse.update',
        'filter' => 'permission:edit_warehouse'
    ]);

    // ❌ Menghapus gudang
    $routes->post('delete/(:num)', 'Warehouse::delete/$1', [
        'as' => 'warehouse.delete',
        'filter' => 'permission:delete_warehouse'
    ]);
});

/**
 * 📌 Routes untuk Modul Inventory
 * 
 * Middleware `auth` + `permission` untuk otorisasi setiap aksi.
 */
$routes->group('inventory', ['filter' => 'auth'], function ($routes) {
    // 📄 Lihat daftar inventory
    $routes->get('/', 'Inventory::index', [
        'as' => 'inventory.index',
        'filter' => 'permission:view_inventory'
    ]);

    // 🔄 Update stok manual (adjustment, koreksi, dll)
    $routes->post('update-stock', 'Inventory::updateStock', [
        'as' => 'inventory.updateStock',
        'filter' => 'permission:edit_inventory'
    ]);

    // 📜 Riwayat pergerakan stok
    $routes->get('logs', 'Inventory::logs', [
        'as' => 'inventory.logs',
        'filter' => 'permission:view_inventory_logs'
    ]);

    // ⚙️ DataTable server-side
    $routes->get('datatable', 'Inventory::datatable', [
        'as' => 'inventory.datatable',
        'filter' => 'permission:view_inventory'
    ]);
});


/**
 * 📌 Routes untuk Modul Purchase Order
 * 
 * Middleware `auth` + `permission` untuk mengamankan tiap endpoint PO.
 */
$routes->group('purchase-orders', ['filter' => 'auth'], function ($routes) {
    // 📄 Lihat daftar PO
    $routes->get('/', 'PurchaseOrder::index', [
        'as' => 'purchase_orders.index',
        'filter' => 'permission:view_purchase_order'
    ]);

    // ➕ Buat PO baru
    $routes->get('create', 'PurchaseOrder::create', [
        'as' => 'purchase_orders.create',
        'filter' => 'permission:create_purchase_order'
    ]);
    $routes->post('store', 'PurchaseOrder::store', [
        'as' => 'purchase_orders.store',
        'filter' => 'permission:create_purchase_order'
    ]);

    // ✏️ Edit PO
    $routes->get('edit/(:num)', 'PurchaseOrder::edit/$1', [
        'as' => 'purchase_orders.edit',
        'filter' => 'permission:edit_purchase_order'
    ]);
    $routes->post('update/(:num)', 'PurchaseOrder::update/$1', [
        'as' => 'purchase_orders.update',
        'filter' => 'permission:edit_purchase_order'
    ]);

    // 📥 Proses penerimaan barang dari PO
    $routes->get('receive/(:num)', 'PurchaseOrder::receive/$1', [
        'as' => 'purchase_orders.receive_form',
        'filter' => 'permission:receive_purchase_order'
    ]);
    $routes->post('processReceive/(:num)', 'PurchaseOrder::processReceive/$1', [
        'as' => 'purchase_orders.receive_process',
        'filter' => 'permission:receive_purchase_order'
    ]);
    $routes->post('store-receive', 'PurchaseOrder::storeReceive', [
        'as' => 'purchase_orders.store_receive',
        'filter' => 'permission:receive_purchase_order'
    ]);

    // ❌ Hapus PO
    $routes->post('delete/(:num)', 'PurchaseOrder::delete/$1', [
        'as' => 'purchase_orders.delete',
        'filter' => 'permission:delete_purchase_order'
    ]);

    // 🔍 View detail PO
    $routes->get('view/(:num)', 'PurchaseOrder::view/$1', [
        'as' => 'purchase_orders.view',
        'filter' => 'permission:view_purchase_order'
    ]);

    // 🔄 Ajax data dan produk
    $routes->post('getData', 'PurchaseOrder::getData', [
        'as' => 'purchase_orders.get_data',
        'filter' => 'permission:view_purchase_order'
    ]);
    $routes->get('get_products_by_supplier/(:num)', 'PurchaseOrder::get_products_by_supplier/$1', [
        'as' => 'purchase_orders.get_products',
        'filter' => 'permission:view_purchase_order'
    ]);
});

/**
 * 📌 API Routes
 */
$routes->get('api/get-product-sku/(:num)', 'PurchaseOrder::get_product_sku/$1');

$routes->group('permissions', ['filter' => 'admin'], function ($routes) {
    $routes->get('user-assign', 'UserPermission::index');
    $routes->get('user-permissions/(:num)', 'UserPermission::getUserPermissions/$1');
    $routes->post('assign-user', 'UserPermission::assign');
});

