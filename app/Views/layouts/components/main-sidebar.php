<?php
$userRoleId = session()->get('role_id') ?? 0; // Set default 0 jika tidak ada
?>
			<aside class="app-sidebar sticky" id="sidebar">

				<!-- Start::main-sidebar-header -->
<div class="main-sidebar-header">
    <a href="<?= base_url('index') ?>" class="header-logo">
        <img src="<?= service('settings')->getLogo('desktop') ?>" alt="logo" class="desktop-logo">
        <img src="<?= service('settings')->getLogo('toggle-dark') ?>" alt="logo" class="toggle-dark">
        <img src="<?= service('settings')->getLogo('desktop-dark') ?>" alt="logo" class="desktop-dark">
        <img src="<?= service('settings')->getLogo('toggle-logo') ?>" alt="logo" class="toggle-logo">
        <img src="<?= service('settings')->getLogo('toggle-white') ?>" alt="logo" class="toggle-white">
        <img src="<?= service('settings')->getLogo('desktop-white') ?>" alt="logo" class="desktop-white">
    </a>
</div>
<!-- End::main-sidebar-header -->

				    <!-- Start::main-sidebar -->
					<div class="main-sidebar" id="sidebar-scroll">
        <!-- Start::nav -->
        <nav class="main-menu-container nav nav-pills flex-column sub-open">
            <div class="slide-left" id="slide-left">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
                </svg>
            </div>
            <ul class="main-menu">

                <!-- Dashboard -->
                <li class="slide">
                    <a href="<?php echo base_url('dashboard'); ?>" class="side-menu__item">
                        <i class="ri-dashboard-line side-menu__icon"></i>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>

                <!-- Section Operation -->
<li class="slide__category"><span class="category-name">Operation</span></li>

<!-- Daftar Brand -->
<li class="slide">
    <a href="<?php echo base_url('brands'); ?>" class="side-menu__item">
        <i class="ri-price-tag-3-line side-menu__icon"></i>
        <span class="side-menu__label">Daftar Brand</span>
    </a>
</li>

<!-- Daftar Produk -->
<li class="slide">
    <a href="<?php echo base_url('products'); ?>" class="side-menu__item">
        <i class="ri-shopping-cart-2-line side-menu__icon"></i>
        <span class="side-menu__label">Daftar Produk</span>
    </a>
</li>

<!-- Warehouse -->
<li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
        <i class="ri-store-line side-menu__icon"></i>
        <span class="side-menu__label">Warehouse</span>
        <i class="ri-arrow-down-s-line side-menu__angle"></i>
    </a>
    <ul class="slide-menu child1">
        <li class="slide"><a href="<?php echo base_url('inventory'); ?>" class="side-menu__item">Data Stok Gudang</a></li>
        <li class="slide"><a href="<?php echo base_url('warehouse'); ?>" class="side-menu__item">Daftar Gudang</a></li>
        <li class="slide"><a href="<?php echo base_url('warehouse/transaksi-stok'); ?>" class="side-menu__item">Transaksi Stok</a></li>
        <li class="slide"><a href="<?php echo base_url('warehouse/data-stok-gudang'); ?>" class="side-menu__item">Data Stok Gudang</a></li>
    </ul>
</li>

<!-- Purchasing -->
<li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
        <i class="ri-file-list-3-line side-menu__icon"></i>
        <span class="side-menu__label">Purchasing</span>
        <i class="ri-arrow-down-s-line side-menu__angle"></i>
    </a>
    <ul class="slide-menu child1">
        <li class="slide"><a href="<?php echo base_url('purchase-orders'); ?>" class="side-menu__item">Purchase Order</a></li>
        <li class="slide"><a href="<?php echo base_url('suppliers'); ?>" class="side-menu__item">Data Supplier</a></li>
    </ul>
</li>

<!-- Return Management -->
<li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
        <i class="ri-exchange-line side-menu__icon"></i>
        <span class="side-menu__label">Return Management</span>
        <i class="ri-arrow-down-s-line side-menu__angle"></i>
    </a>
    <ul class="slide-menu child1">
        <li class="slide"><a href="<?php echo base_url('return/input-mp'); ?>" class="side-menu__item">Input Return MP</a></li>
        <li class="slide"><a href="<?php echo base_url('return/input-soscom'); ?>" class="side-menu__item">Input Return Soscom</a></li>
        <li class="slide"><a href="<?php echo base_url('return/input-claim'); ?>" class="side-menu__item">Input Claim Return</a></li>
    </ul>
</li>

<!-- Daftar Ekspedisi -->
<li class="slide">
    <a href="<?php echo base_url('ekspedisi'); ?>" class="side-menu__item">
        <i class="ri-truck-line side-menu__icon"></i>
        <span class="side-menu__label">Daftar Ekspedisi</span>
    </a>
</li>

<!-- Section Pembukuan -->
<li class="slide__category"><span class="category-name">Pembukuan</span></li>

<!-- Laporan -->
<?php if (in_array($userRoleId, [1, 3, 4])): // Role: Administrator, Manager, Finance ?>
<li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
        <i class="ri-file-chart-line side-menu__icon"></i>
        <span class="side-menu__label">Laporan</span>
        <i class="ri-arrow-down-s-line side-menu__angle"></i>
    </a>
    <ul class="slide-menu child1">
        <li class="slide"><a href="<?php echo base_url('laporan/keuangan'); ?>" class="side-menu__item">Laporan Keuangan</a></li>
        <li class="slide"><a href="<?php echo base_url('laporan/laba-rugi'); ?>" class="side-menu__item">Laporan Laba/Rugi</a></li>
        <li class="slide"><a href="<?php echo base_url('laporan/cashflow'); ?>" class="side-menu__item">Cashflow</a></li>
        <li class="slide"><a href="<?php echo base_url('laporan/hutang'); ?>" class="side-menu__item">Daftar Hutang</a></li>
        <li class="slide"><a href="<?php echo base_url('laporan/piutang'); ?>" class="side-menu__item">Daftar Piutang</a></li>
    </ul>
</li>
<?php endif; ?>

<!-- Transaksi Marketplace -->
<li class="slide">
    <a href="<?php echo base_url('transaksi/marketplace'); ?>" class="side-menu__item">
        <i class="ri-shopping-cart-line side-menu__icon"></i>
        <span class="side-menu__label">Transaksi Marketplace</span>
    </a>
</li>

<!-- Transaksi Soscom -->
<li class="slide">
    <a href="<?php echo base_url('transaksi/soscom'); ?>" class="side-menu__item">
        <i class="ri-chat-3-line side-menu__icon"></i>
        <span class="side-menu__label">Transaksi Soscom</span>
    </a>
</li>

<!-- Transaksi CRM -->
<li class="slide">
    <a href="<?php echo base_url('transaksi/crm'); ?>" class="side-menu__item">
        <i class="ri-customer-service-2-line side-menu__icon"></i>
        <span class="side-menu__label">Transaksi CRM</span>
    </a>
</li>

<!-- Expenses -->
<li class="slide">
    <a href="<?php echo base_url('expenses'); ?>" class="side-menu__item">
        <i class="ri-money-dollar-circle-line side-menu__icon"></i>
        <span class="side-menu__label">Expenses</span>
    </a>
</li>

<!-- Cash In -->
<?php if (in_array($userRoleId, [1, 3, 4, 5])): // Role: Administrator, Manager, Finance, Akuntan ?>
<li class="slide">
    <a href="<?php echo base_url('cash-in'); ?>" class="side-menu__item">
        <i class="ri-bank-line side-menu__icon"></i>
        <span class="side-menu__label">Cash In</span>
    </a>
</li>
<?php endif; ?>

<!-- COA -->
<?php if (in_array($userRoleId, [1, 3, 4, 5])): // Role: Administrator, Manager, Finance, Akuntan ?>
<li class="slide">
    <a href="<?php echo base_url('coa'); ?>" class="side-menu__item">
        <i class="ri-file-text-line side-menu__icon"></i>
        <span class="side-menu__label">Chart of Account</span>
    </a>
</li>
<?php endif; ?>

<!-- Section Marketing & Sales -->
<li class="slide__category"><span class="category-name">Marketing & Sales</span></li>

<!-- Laporan Brand (Tampil hanya untuk role tertentu) -->
<?php if (in_array($userRoleId, [1, 3, 4, 5, 6])): ?>
<li class="slide">
    <a href="<?php echo base_url('marketing/laporan-brand'); ?>" class="side-menu__item">
        <i class="ri-bar-chart-line side-menu__icon"></i>
        <span class="side-menu__label">Laporan Brand</span>
    </a>
</li>
<?php endif; ?>

<!-- Data Brand -->
<li class="slide">
    <a href="<?php echo base_url('marketing/data-brand'); ?>" class="side-menu__item">
        <i class="ri-price-tag-3-line side-menu__icon"></i>
        <span class="side-menu__label">Data Brand</span>
    </a>
</li>

<!-- KOL Management -->
<li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
        <i class="ri-user-star-line side-menu__icon"></i>
        <span class="side-menu__label">KOL Management</span>
        <i class="ri-arrow-down-s-line side-menu__angle"></i>
    </a>
    <ul class="slide-menu child1">
        <li class="slide"><a href="<?php echo base_url('kol/data-kol'); ?>" class="side-menu__item">Data KOL</a></li>
        <li class="slide"><a href="<?php echo base_url('kol/list-endorse'); ?>" class="side-menu__item">List Endorse</a></li>
        <li class="slide"><a href="<?php echo base_url('kol/data-media'); ?>" class="side-menu__item">Data Media</a></li>
        <li class="slide"><a href="<?php echo base_url('kol/list-publikasi'); ?>" class="side-menu__item">List Publikasi Media</a></li>
    </ul>
</li>

<!-- Soscom & CRM -->
<li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
        <i class="ri-group-line side-menu__icon"></i>
        <span class="side-menu__label">Soscom & CRM</span>
        <i class="ri-arrow-down-s-line side-menu__angle"></i>
    </a>
    <ul class="slide-menu child1">
        <li class="slide"><a href="<?php echo base_url('soscom/daftar-tim'); ?>" class="side-menu__item">Daftar Tim</a></li>
        <li class="slide"><a href="<?php echo base_url('soscom/data-customer'); ?>" class="side-menu__item">Data Customer</a></li>
    </ul>
</li>

<!-- Partnership (Tampil hanya untuk role tertentu) -->
<?php if (in_array($userRoleId, [1, 3, 4, 5, 6, 7])): ?>
<li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
        <i class="ri-shake-hands-fill side-menu__icon"></i>
        <span class="side-menu__label">Partnership</span>
        <i class="ri-arrow-down-s-line side-menu__angle"></i>
    </a>
    <ul class="slide-menu child1">
        <li class="slide"><a href="<?php echo base_url('partnership/data-partner'); ?>" class="side-menu__item">Data Partner</a></li>
        <li class="slide"><a href="<?php echo base_url('partnership/input-transaksi'); ?>" class="side-menu__item">Input Transaksi Partner</a></li>
    </ul>
</li>
<?php endif; ?>




<!-- Section HR -->
<?php if (in_array($userRoleId, [1, 3, 4, 6])): // Role: Administrator, Manager, Finance, HR ?>
<li class="slide__category"><span class="category-name">HR</span></li>
<!-- Data Karyawan -->
<li class="slide">
    <a href="<?php echo base_url('hr/karyawan'); ?>" class="side-menu__item">
        <i class="ri-team-line side-menu__icon"></i>
        <span class="side-menu__label">Data Karyawan</span>
    </a>
</li>
<?php endif; ?>

<!-- Absensi -->
<?php if (in_array($userRoleId, [1, 3, 4, 6])): ?>
<li class="slide">
    <a href="<?php echo base_url('hr/absensi'); ?>" class="side-menu__item">
        <i class="ri-calendar-check-line side-menu__icon"></i>
        <span class="side-menu__label">Absensi</span>
    </a>
</li>
<?php endif; ?>

<!-- Payroll -->
<?php if (in_array($userRoleId, [1, 3, 4, 6])): ?>
<li class="slide">
    <a href="<?php echo base_url('hr/payroll'); ?>" class="side-menu__item">
        <i class="ri-money-dollar-box-line side-menu__icon"></i>
        <span class="side-menu__label">Payroll</span>
    </a>
</li>
<?php endif; ?>

<!-- Dokumen -->
<?php if (in_array($userRoleId, [1, 3, 4, 6])): ?>
<li class="slide">
    <a href="<?php echo base_url('hr/dokumen'); ?>" class="side-menu__item">
        <i class="ri-folder-line side-menu__icon"></i>
        <span class="side-menu__label">Dokumen</span>
    </a>
</li>
<?php endif; ?>

<!-- Section Lainnya -->
<li class="slide__category"><span class="category-name">Lainnya</span></li>

<!-- Knowledge Base -->
<li class="slide">
    <a href="<?php echo base_url('lainnya/knowledge-base'); ?>" class="side-menu__item">
        <i class="ri-book-open-line side-menu__icon"></i>
        <span class="side-menu__label">Knowledge Base</span>
    </a>
</li>

<!-- File Manager -->
<li class="slide">
    <a href="<?php echo base_url('lainnya/file-manager'); ?>" class="side-menu__item">
        <i class="ri-folder-open-line side-menu__icon"></i>
        <span class="side-menu__label">File Manager</span>
    </a>
</li>

<!-- Data Akun -->
<?php if (in_array($userRoleId, [1, 3, 4, 5, 7, 8, 9, 10])): // Role: Admin, Manager, Finance, Akuntan, Head of Brand, Business Partner, HR, Leader ?>
<li class="slide">
    <a href="<?php echo base_url('lainnya/data-akun'); ?>" class="side-menu__item">
        <i class="ri-user-line side-menu__icon"></i>
        <span class="side-menu__label">Data Akun</span>
    </a>
</li>
<?php endif; ?>

<!-- Section Pengaturan -->
<li class="slide__category"><span class="category-name">Pengaturan</span></li>

<!-- Pengaturan Akun -->
<li class="slide">
    <a href="<?php echo base_url('settings/system'); ?>" class="side-menu__item">
        <i class="ri-user-settings-line side-menu__icon"></i>
        <span class="side-menu__label">System Setting</span>
    </a>
</li>

<!-- Daftar User (Hanya Administrator) -->
<?php if ($userRoleId == 1): ?>
<li class="slide">
    <a href="<?php echo base_url('pengaturan/user'); ?>" class="side-menu__item">
        <i class="ri-user-line side-menu__icon"></i>
        <span class="side-menu__label">Daftar User</span>
    </a>
</li>
<?php endif; ?>

<!-- Daftar Roles (Hanya Administrator) -->
<?php if ($userRoleId == 1): ?>
<li class="slide">
    <a href="<?php echo base_url('roles'); ?>" class="side-menu__item">
        <i class="ri-shield-user-line side-menu__icon"></i>
        <span class="side-menu__label">Daftar Roles</span>
    </a>
</li>
<?php endif; ?>

<!-- Daftar Permissions (Hanya Administrator) -->
<?php if ($userRoleId == 1): ?>
<li class="slide">
    <a href="<?php echo base_url('permissions'); ?>" class="side-menu__item">
        <i class="ri-lock-password-line side-menu__icon"></i>
        <span class="side-menu__label">Daftar Permissions</span>
    </a>
</li>
<?php endif; ?>

<!-- Pengaturan Bisnis -->
<?php if (in_array($userRoleId, [1, 3, 4])): // Role: Admin, Manager, Finance ?>
<li class="slide has-sub">
    <a href="javascript:void(0);" class="side-menu__item">
        <i class="ri-settings-2-line side-menu__icon"></i>
        <span class="side-menu__label">Pengaturan Bisnis</span>
        <i class="ri-arrow-down-s-line side-menu__angle"></i>
    </a>
    <ul class="slide-menu child1">
        <li class="slide"><a href="<?php echo base_url('pengaturan-bisnis/perusahaan'); ?>" class="side-menu__item">Daftar Perusahaan</a></li>
    </ul>
</li>
<?php endif; ?>

            </ul>
            <div class="slide-right" id="slide-right">
                <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
                </svg>
            </div>
        </nav>
        <!-- End::nav -->
    </div>
    <!-- End::main-sidebar -->
</aside>