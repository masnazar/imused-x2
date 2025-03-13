<?= $this->extend('layouts/custom-main2'); ?>

<?= $this->section('styles'); ?>
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<div class="container-lg">
    <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
        <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
            <div class="card custom-card my-4">
                <div class="card-body p-5">
                    <div class="mb-3 d-flex justify-content-center">
                        <a href="<?= base_url(); ?>">
                            <img src="<?= base_url('assets/images/brand-logos/desktop-logo.png'); ?>" alt="logo" class="desktop-logo">
                            <img src="<?= base_url('assets/images/brand-logos/desktop-white.png'); ?>" alt="logo" class="desktop-white">
                        </a>
                    </div>
                    <p class="h5 mb-2 text-center">Reset Password</p>
                    <p class="mb-4 text-muted op-7 fw-normal text-center fs-14">Silakan buat password baru.</p>

                    <!-- 🔥 Flash Messages -->
                    <?php if (session()->has('error')) : ?>
                        <div class="alert alert-danger"><?= session('error') ?></div>
                    <?php elseif (session()->has('success')) : ?>
                        <div class="alert alert-success"><?= session('success') ?></div>
                    <?php endif; ?>

                    <!-- ✅ Form Reset Password -->
                    <form action="<?= base_url('/reset-password/' . $token) ?>" method="post">
                        <?= csrf_field() ?>

                        <input type="hidden" name="token" value="<?= $token ?>">

                        <div class="row gy-3">
                            <!-- Password Baru -->
                            <div class="col-xl-12">
                                <label for="reset-newpassword" class="form-label text-default">New Password <sup class="fs-12 text-danger">*</sup></label>
                                <div class="position-relative">
                                    <input type="password" name="password" class="form-control create-password-input <?= session('errors.password') ? 'is-invalid' : '' ?>" id="reset-newpassword" placeholder="New password" required>
                                    <a href="javascript:void(0);" class="show-password-button text-muted" onclick="createpassword('reset-newpassword', this)">
                                        <i class="ri-eye-off-line align-middle"></i>
                                    </a>
                                    <div class="invalid-feedback"><?= session('errors.password') ?></div>
                                </div>
                            </div>

                            <!-- Konfirmasi Password -->
                            <div class="col-xl-12">
                                <label for="reset-confirmpassword" class="form-label text-default">Confirm Password <sup class="fs-12 text-danger">*</sup></label>
                                <div class="position-relative">
                                    <input type="password" name="confirm_password" class="form-control create-password-input <?= session('errors.confirm_password') ? 'is-invalid' : '' ?>" id="reset-confirmpassword" placeholder="Confirm password" required>
                                    <a href="javascript:void(0);" class="show-password-button text-muted" onclick="createpassword('reset-confirmpassword', this)">
                                        <i class="ri-eye-off-line align-middle"></i>
                                    </a>
                                    <div class="invalid-feedback"><?= session('errors.confirm_password') ?></div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                        </div>
                    </form>

                    <div class="text-center">
                        <p class="text-muted mt-3">Ingat password? <a href="<?= base_url('login'); ?>" class="text-primary">Masuk</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= base_url('assets/js/show-password.js'); ?>"></script>
<?= $this->endSection(); ?>
