<?= $this->extend('layouts/main'); ?>

<?= $this->section('styles'); ?>
<link rel="stylesheet" href="<?= base_url('assets/libs/glightbox/css/glightbox.min.css'); ?>">
<?= $this->endSection('styles'); ?>

<?= $this->section('content'); ?>
<?= $this->include('layouts/components/flashMessage') ?>
<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url(); ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Profil</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Profil Pengguna</h1>
    </div>
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card profile-card">
            <div class="profile-banner-img">
                <img src="<?= base_url('assets/images/media/media-3.jpg'); ?>" class="card-img-top" alt="Banner Profil">
            </div>
            
            <div class="card-body pb-0 position-relative">
                <div class="row profile-content">
                    <!-- Sidebar Profil -->
                    <div class="col-xl-3">
                        <?= view('profile/partials/_sidebar', [
                            'user' => $user,
                            'userRole' => $userRole,
                            'age' => $age
                        ]) ?>
                    </div>
                    
                    <!-- Konten Tab -->
                    <div class="col-xl-9">
                        <div class="card custom-card overflow-hidden border">
                            <div class="card-body">
                                <ul class="nav nav-tabs tab-style-6 mb-3 p-0" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link w-100 text-start active" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button">Tentang</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link w-100 text-start" id="edit-tab" data-bs-toggle="tab" data-bs-target="#edit" type="button">Edit Profil</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link w-100 text-start" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button">Keamanan</button>
                                    </li>
                                </ul>
                                
                                <div class="tab-content" id="profile-tabs">
                                    <?= view('profile/partials/_about', ['user' => $user]) ?>
                                    <?= view('profile/partials/_edit', ['user' => $user]) ?>
                                    <?= view('profile/partials/_security') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection('content'); ?>

<?= $this->section('scripts'); ?>
<script src="<?= base_url('assets/libs/glightbox/js/glightbox.min.js'); ?>"></script>
<script src="<?= base_url('assets/js/profile.js'); ?>"></script>
<?= $this->endSection('scripts'); ?>