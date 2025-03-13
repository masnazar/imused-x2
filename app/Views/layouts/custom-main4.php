<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-vertical-style="overlay" data-theme-mode="light" data-header-styles="light" data-menu-styles="light" data-toggled="close">

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
        <?= $this->include('layouts/components/custom-styles'); ?>
        <!-- End styles -->

    </head>    
    
    <body class="">

        <!-- Start::custom-switcher -->
        <?= $this->include('layouts/components/custom-switcher'); ?>
        <!-- End::custom-switcher -->

        <?= $this->renderSection('content'); ?>         

        <!-- Start::main-scripts -->
        <?= $this->include('layouts/components/custom-scripts'); ?>
        <!-- End::main-scripts -->

    </body>

</html>