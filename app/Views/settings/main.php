<?= $this->extend('layouts/main'); ?>

<?= $this->section('styles') ?>
<!-- Includenya di layout/main atau section script -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<?= $this->endSection() ?>

<?= $this->section('content'); ?>

<!-- Start::page-header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Pengaturan Sistem</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Pengaturan Sistem</h1>
    </div>
</div>
<!-- End::page-header -->

<div class="row mb-5">
    <div class="col-xl-3">
        <div class="card custom-card">
            <div class="card-body">
                <ul class="nav nav-tabs flex-column nav-tabs-header mb-0">
                    <!-- Sistem -->
                    <li class="nav-item me-0">
                        <a class="nav-link <?= $activeTab === 'system' ? 'active' : '' ?>" 
                           href="<?= base_url('/settings/system') ?>">
                            <i class="ri-settings-5-line me-2"></i> Sistem
                        </a>
                    </li>
                    <!-- Email -->
                    <li class="nav-item me-0">
                        <a class="nav-link <?= $activeTab === 'email' ? 'active' : '' ?>" 
                           href="<?= base_url('/settings/email') ?>">
                            <i class="ri-mail-line me-2"></i> Email
                        </a>
                    </li>
                    <!-- Menu -->
                    <li class="nav-item me-0">
                        <a class="nav-link <?= $activeTab === 'menu' ? 'active' : '' ?>" 
                        href="<?= base_url('/settings/menu') ?>">
                            <i class="ri-lock-line me-2"></i> Menu Akses
                        </a>
                    </li>

                </ul>
            </div>
        </div>
    </div>

    <div class="col-xl-9">
        <?= $this->include('layouts/components/flashMessage') ?>
        <?= $this->include("settings/partials/{$activeTab}") ?>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
// Toggle Password Visibility
document.getElementById('togglePassword')?.addEventListener('click', function() {
    const passwordField = document.querySelector('input[name="email_smtp_pass"]');
    const icon = this.querySelector('i');
    
    if(passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.replace('ri-eye-line', 'ri-eye-off-line');
    } else {
        passwordField.type = 'password';
        icon.classList.replace('ri-eye-off-line', 'ri-eye-line');
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", function () {
    $('.js-role-select').select2({
      width: '100%',
      placeholder: "Pilih role...",
      allowClear: true
    });
  });
</script>

<?= $this->endSection(); ?>