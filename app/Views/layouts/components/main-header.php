<?php 
    $userName = session('user_name') ?? 'Pengguna';
    $userRole = session('user_role') ?? 'Role tidak tersedia'; ?>

<header class="app-header sticky" id="header">

    <!-- Start::main-header-container -->
    <div class="main-header-container container-fluid">

        <!-- Start::header-content-left -->
        <div class="header-content-left">

            <!-- Start::header-element -->
            <div class="header-element">
                <div class="horizontal-logo">
                    <a href="<?php echo base_url('index'); ?>" class="header-logo">
					<img src="<?= service('settings')->getLogo('desktop') ?>" alt="logo" class="desktop-logo">
					<img src="<?= service('settings')->getLogo('toggle-dark') ?>" alt="logo" class="toggle-dark">
					<img src="<?= service('settings')->getLogo('desktop-dark') ?>" alt="logo" class="desktop-dark">
					<img src="<?= service('settings')->getLogo('toggle-logo') ?>" alt="logo" class="toggle-logo">
					<img src="<?= service('settings')->getLogo('toggle-white') ?>" alt="logo" class="toggle-white">
					<img src="<?= service('settings')->getLogo('desktop-white') ?>" alt="logo" class="desktop-white">
                    </a>
                </div>
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element mx-lg-0 mx-2">
                <a aria-label="Hide Sidebar" class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle" data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element header-search d-md-block d-none my-auto auto-complete-search">
                <input type="text" class="header-search-bar form-control" id="header-search" placeholder="Cari sesuatu..." spellcheck=false autocomplete="off" autocapitalize="off">
                <a href="javascript:void(0);" class="header-search-icon border-0">
                    <i class="ri-search-line"></i>
                </a>
            </div>
            <!-- End::header-element -->

        </div>
        <!-- End::header-content-left -->

        <!-- Start::header-content-right -->
        <ul class="header-content-right">

            <!-- Start::header-element -->
            <li class="header-element d-md-none d-block">
                <a href="javascript:void(0);" class="header-link" data-bs-toggle="modal" data-bs-target="#header-responsive-search">
                    <i class="bi bi-search header-link-icon"></i>
                </a>  
            </li>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <li class="header-element header-theme-mode">
                <a href="javascript:void(0);" class="header-link layout-setting">
                    <span class="light-layout">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 header-link-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                        </svg>
                    </span>
                    <span class="dark-layout">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 header-link-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                        </svg>
                    </span>
                </a>
            </li>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <li class="header-element header-fullscreen">
                <a onclick="openFullscreen();" href="javascript:void(0);" class="header-link">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 full-screen-open header-link-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15" />
                    </svg>
                </a>
            </li>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <li class="header-element dropdown">
                <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <div>
                        <?php 
    $profileImage = session('profile_image') 
        ? base_url('uploads/' . session('profile_image')) 
        : base_url('assets/images/faces/15.jpg');
?>

<img src="<?= esc($profileImage) ?>" alt="Foto Profil" class="avatar avatar-sm">

                        </div>
                    </div>
                </a>
                <ul class="main-header-dropdown dropdown-menu dropdown-menu-end">
                    <li>
                    <div class="dropdown-item text-center border-bottom">
						<!-- Nama User -->
						<span><?= esc($userName) ?></span>
						<span class="d-block fs-12 text-muted">
							<!-- Role User -->
							<?= esc(session('role_name') ?? 'Role tidak tersedia') ?>
						</span>
					</div>
                    </li>
                    <li><a class="dropdown-item d-flex align-items-center" href="<?php echo base_url('profile'); ?>"><i class="fe fe-user p-1 rounded-circle bg-primary-transparent me-2 fs-16"></i>Profil</a></li>
                    <li class="border-top bg-light">
                        <a class="dropdown-item d-flex align-items-center" href="<?php echo base_url('logout'); ?>">
                            <i class="fe fe-lock p-1 rounded-circle bg-primary-transparent me-2 fs-16"></i>Keluar
                        </a>
                    </li>
                </ul>
            </li>  
            <!-- End::header-element -->

        </ul>
        <!-- End::header-content-right -->

    </div>
    <!-- End::main-header-container -->

</header>