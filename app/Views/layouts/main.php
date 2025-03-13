<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light" data-menu-styles="light" data-toggled="close">

    <head>

        <!-- Meta Data -->
        <meta charset="UTF-8">
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="Description" content="Codeigniter Bootstrap Responsive Admin Web Dashboard Template">
        <meta name="Author" content="Spruko Technologies Private Limited">
        <meta name="keywords" content="admin panel template, codeigniter, codeigniter download, admin panel bootstrap, admin dashboard ui, bootstrap component, admin, bootstrap framework template, bootstrap 5 templates, template bootstrap, dashboard, bootstrap dashboard, bootstrap admin, codeigniter framework, dashboard css.">
        
        <!-- Title-->
        <title><?= $this->renderSection('title') ?: 'IMUSED Group' ?></title>
        
		<!-- Favicon Dinamis -->
        <?php $favicon = service('settings')->get('favicon'); ?>
        <link rel="icon" href="<?= $favicon ? base_url($favicon) : base_url('assets/images/brand-logos/favicon.ico') ?>">
       
        <!-- styles code -->
        <?= $this->include('layouts/components/styles'); ?>
        <!-- End styles -->

    </head>    
    
    <body class="">

        <!-- Start::main-switcher -->
        <?= $this->include('layouts/components/switcher'); ?>
        <!-- End::main-switcher -->

        <!-- Loader -->
        <div id="loader" >
            <img src="<?php echo base_url('assets/images/media/loader.svg'); ?>" alt="">
        </div>
        <!-- Loader -->

		<!-- page -->
        <div class="page">

			<!-- Start::main-header -->
            <?= $this->include('layouts/components/main-header'); ?>
			<!-- End::main-header -->

			<!-- Start::main-sidebar -->
            <?= $this->include('layouts/components/main-sidebar'); ?>
			<!-- End::main-sidebar -->

            <!-- Start::app-content -->
            <div class="main-content app-content">
                <div class="container-fluid">

                    <?= $this->renderSection('content'); ?>

                </div>
            </div>
            <!-- End::app-content -->

            <!-- Start::main-modal -->
            <?= $this->include('layouts/components/modal'); ?>
            <!-- End::main-modal -->

            <!-- Start::main-footer -->
            <?= $this->include('layouts/components/footer'); ?>
            <!-- End::main-footer -->

        </div> 
        <!-- End Page -->          

        <!-- Start::main-scripts -->
        <?= $this->include('layouts/components/scripts'); ?>
        <!-- End::main-scripts -->

    </body>

</html>