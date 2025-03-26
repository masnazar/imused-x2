<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Auth Routes
 * 
 * Menangani routing untuk proses autentikasi:
 * - Registrasi
 * - Login
 * - Logout
 * - Aktivasi akun
 */

// Registration routes
$routes->get('/register', 'Auth::register');          
$routes->post('/register', 'Auth::processRegister');  

// Login routes
$routes->get('/login', 'Auth::login');               
$routes->post('/login', 'Auth::processLogin');       

// Logout route
$routes->get('/logout', 'Auth::logout');             

// Account activation route
$routes->get('/activate/(:any)/(:any)', 'Auth::activate/$1/$2');  

// Forgot & Reset Password
$routes->get('forgot-password', 'Auth::forgotPassword');
$routes->post('forgot-password', 'Auth::processForgotPassword');
$routes->get('reset-password/(:any)', 'Auth::resetPasswordForm/$1'); 
$routes->post('reset-password/(:any)', 'Auth::processResetPassword/$1'); 

// ✅ Proteksi halaman yang butuh login
$routes->group('', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Home::index');
    $routes->get('/dashboard', 'Dashboard::index');
    $routes->get('/profile', 'Profile::edit');
    $routes->post('/profile/update', 'Profile::update');
    $routes->post('profile/update-password', 'Profile::updatePassword');
});


$routes->group('suppliers', ['filter' => 'auth'], function ($routes) {
    /**
     * 📌 Routes untuk modul Supplier dengan Middleware Permission
     * Setiap endpoint dicek dengan middleware `auth` dan `permission`
     */

    $routes->get('/', 'Supplier::index', [
        'as' => 'suppliers.index',
        'filter' => 'permission:view_supplier'
    ]); // Menampilkan daftar supplier

    $routes->get('create', 'Supplier::create', [
        'as' => 'suppliers.create',
        'filter' => 'permission:create_supplier'
    ]); // Form tambah supplier

    $routes->post('store', 'Supplier::store', [
        'as' => 'suppliers.store',
        'filter' => 'permission:create_supplier'
    ]); // Simpan supplier

    $routes->get('edit/(:num)', 'Supplier::edit/$1', [
        'as' => 'suppliers.edit',
        'filter' => 'permission:edit_supplier'
    ]); // Form edit supplier

    $routes->post('update/(:num)', 'Supplier::update/$1', [
        'as' => 'suppliers.update',
        'filter' => 'permission:edit_supplier'
    ]); // Update supplier

    $routes->post('delete/(:num)', 'Supplier::delete/$1', [
        'as' => 'suppliers.delete',
        'filter' => 'permission:delete_supplier'
    ]); // Hapus supplier
});


/**
 * ✅ Proteksi halaman admin (khusus role_id = 1)
 * Middleware `admin` memastikan hanya user dengan role_id = 1 (Administrator) yang dapat mengakses.
 */
$routes->group('', ['filter' => 'admin'], function ($routes) {
    // Redirect /settings ke /settings/system sebagai halaman default
    $routes->get('settings', 'Setting::index', ['as' => 'settings.index']);

    // Group settings dengan prefix `settings`
    $routes->group('settings', function ($routes) {
        // ✅ Konfigurasi Sistem
        $routes->get('system', 'Setting::systemConfig', ['as' => 'settings.system']);
        $routes->post('system/update', 'Setting::updateSystemConfig', ['as' => 'settings.system.update']);

        // ✅ Konfigurasi Email
        $routes->get('email', 'Setting::emailConfig', ['as' => 'settings.email']);
        $routes->post('email/update', 'Setting::updateEmailConfig', ['as' => 'settings.email.update']);

        // ✅ Konfigurasi Keamanan
        $routes->get('security', 'Setting::securityConfig', ['as' => 'settings.security']);
        $routes->post('security/update', 'Setting::updateSecurityConfig', ['as' => 'settings.security.update']);
    });
});

$routes->group('permissions', ['filter' => 'admin'], function ($routes) {
    /**
     * 📌 Routes untuk Manage Permissions
     * Hanya bisa diakses oleh role_id = 1 (Administrator)
     */

    // 📌 CRUD Permissions
    $routes->get('/', 'Permission::index', ['as' => 'permissions.index']); // List permissions
    $routes->get('create', 'Permission::create', ['as' => 'permissions.create']); // Form tambah permission
    $routes->post('store', 'Permission::store', ['as' => 'permissions.store']); // Simpan permission
    $routes->get('edit/(:num)', 'Permission::edit/$1', ['as' => 'permissions.edit']); // Form edit permission
    $routes->post('update/(:num)', 'Permission::update/$1', ['as' => 'permissions.update']); // Update permission
    $routes->post('delete/(:num)', 'Permission::delete/$1', ['as' => 'permissions.delete']); // Hapus permission
    $routes->get('get_assigned_permissions/(:num)', 'Permission::getAssignedPermissions/$1', ['as' => 'permissions.getAssigned']);


    // 📌 Manage Role Permissions
    $routes->get('roles', 'Permission::manageRoles', ['as' => 'permissions.roles']); // List roles & permissions
    $routes->post('roles/update', 'Permission::updateRolePermissions', ['as' => 'permissions.roles.update']); // Update permissions per role

    // 📌 Assign Permissions to Roles
    $routes->get('assign', 'Permission::assign', ['as' => 'permissions.assign']); // Form assign
    $routes->post('assign_process', 'Permission::assignProcess', ['as' => 'permissions.assign_process']); // Process assign
});


$routes->group('roles', ['filter' => 'role:Administrator,Manager'], function ($routes) {
    $routes->get('/', 'Role::index', ['as' => 'roles.index']); // List semua roles
    $routes->get('create', 'Role::create', ['as' => 'roles.create']); // Form tambah role
    $routes->post('store', 'Role::store', ['as' => 'roles.store']); // Simpan role
    $routes->get('edit/(:num)', 'Role::edit/$1', ['as' => 'roles.edit']); // Form edit role
    $routes->post('update/(:num)', 'Role::update/$1', ['as' => 'roles.update']); // Update role
    $routes->post('delete/(:num)', 'Role::delete/$1', ['as' => 'roles.delete']); // Hapus role
});

$routes->group('brands', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Brand::index', ['as' => 'brands.index']);
    $routes->get('create', 'Brand::create', ['as' => 'brands.create']);
    $routes->post('store', 'Brand::store', ['as' => 'brands.store']);
    $routes->get('edit/(:num)', 'Brand::edit/$1', ['as' => 'brands.edit']);
    $routes->post('update/(:num)', 'Brand::update/$1', ['as' => 'brands.update']);
    $routes->post('delete/(:num)', 'Brand::delete/$1', ['as' => 'brands.delete']);
});


$routes->group('products', ['filter' => 'role:Administrator,Manager'], function ($routes) {
    $routes->get('/', 'Product::index', ['as' => 'products.index']); // List semua produk
    $routes->get('create', 'Product::create', ['as' => 'products.create']); // Form tambah produk
    $routes->post('store', 'Product::store', ['as' => 'products.store']); // Simpan produk
    $routes->get('edit/(:num)', 'Product::edit/$1', ['as' => 'products.edit']); // Form edit produk
    $routes->post('update/(:num)', 'Product::update/$1', ['as' => 'products.update']); // Update produk
    $routes->post('delete/(:num)', 'Product::delete/$1', ['as' => 'products.delete']); // Hapus produk
});

$routes->group('warehouse', ['filter' => 'role:Administrator,Manager'], function ($routes) {
    $routes->get('/', 'Warehouse::index', ['as' => 'warehouse.index']); // List semua warehouse
    $routes->get('create', 'Warehouse::create', ['as' => 'warehouse.create']); // Form tambah warehouse
    $routes->post('store', 'Warehouse::store', ['as' => 'warehouse.store']); // Simpan warehouse
    $routes->get('edit/(:num)', 'Warehouse::edit/$1', ['as' => 'warehouse.edit']); // Form edit warehouse
    $routes->post('update/(:num)', 'Warehouse::update/$1', ['as' => 'warehouse.update']); // Update warehouse
    $routes->post('delete/(:num)', 'Warehouse::delete/$1', ['as' => 'warehouse.delete']); // Hapus warehouse
});


$routes->group('inventory', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'Inventory::index');
    $routes->post('update-stock', 'Inventory::updateStock');
});

$routes->group('purchase-orders', ['filter' => 'auth'], function ($routes) {
    $routes->get('/', 'PurchaseOrder::index');
    $routes->get('create', 'PurchaseOrder::create');
    $routes->post('store', 'PurchaseOrder::store');
    $routes->get('edit/(:num)', 'PurchaseOrder::edit/$1');
    $routes->post('update/(:num)', 'PurchaseOrder::update/$1');
    $routes->post('receive/(:num)', 'PurchaseOrder::processReceive/$1');
    $routes->get('delete/(:num)', 'PurchaseOrder::delete/$1');
    $routes->post('getData', 'PurchaseOrder::getData'); // 📌 Fetch data buat DataTables
    $routes->get('view/(:num)', 'PurchaseOrder::view/$1');
    $routes->get('receive/(:num)', 'PurchaseOrder::receive/$1');
    $routes->post('store-receive', 'PurchaseOrder::storeReceive');


});
