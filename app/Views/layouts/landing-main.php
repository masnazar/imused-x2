<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="horizontal" data-nav-style="menu-click" data-menu-position="fixed" data-theme-mode="light" data-toggled="close">

    <head>

        <!-- Meta Data -->
        <meta charset="UTF-8">
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="Description" content="Codeigniter Bootstrap Responsive Admin Web Dashboard Template">
        <meta name="Author" content="Spruko Technologies Private Limited">
        <meta name="keywords" content="admin panel template, codeigniter, codeigniter download, admin panel bootstrap, admin dashboard ui, bootstrap component, admin, bootstrap framework template, bootstrap 5 templates, template bootstrap, dashboard, bootstrap dashboard, bootstrap admin, codeigniter framework, dashboard css.">
        
        <!-- Title-->
        <title> Xintra - Codeigniter Bootstrap 5 Premium Admin & Dashboard Template </title>
        
		<!-- Favicon -->
        <link rel="icon" href="<?php echo base_url('assets/images/brand-logos/favicon.ico'); ?>" type="image/x-icon">
       
        <!-- styles code -->
        <?= $this->include('layouts/components/landing/styles'); ?>
        <!-- End styles -->

    </head>    
    
    <body class="landing-body">

        <!-- Start::main-switcher -->
        <?= $this->include('layouts/components/landing/switcher'); ?>
        <!-- End::main-switcher -->

		<!-- page -->
        <div class="landing-page-wrapper">

			<!-- Start::main-header -->
            <?= $this->include('layouts/components/landing/main-header'); ?>
			<!-- End::main-header -->

			<!-- Start::main-sidebar -->
            <?= $this->include('layouts/components/landing/main-sidebar'); ?>
			<!-- End::main-sidebar -->

            <!-- Start::app-content -->
            <div class="main-content landing-main px-0">

                <?= $this->renderSection('content'); ?>

            </div>
            <!-- End::app-content -->

        </div> 
        <!-- End Page -->          

        <!-- Start::main-scripts -->
        <?= $this->include('layouts/components/landing/scripts'); ?>
        <!-- End::main-scripts -->

    </body>

</html>