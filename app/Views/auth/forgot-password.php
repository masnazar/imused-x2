<?= $this->extend('layouts/custom-main2'); ?>

<?= $this->section('styles'); ?>
<!-- Tambahkan CSS tambahan jika diperlukan -->
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<div class="container">
    <div class="row justify-content-center align-items-center authentication authentication-basic h-100">
        <div class="col-xxl-5 col-xl-5 col-lg-5 col-md-6 col-sm-8 col-12">
            <div class="card custom-card my-4">
                <div class="card-body p-5">
                    <div class="mb-3 d-flex justify-content-center">
                        <a href="<?= base_url('/'); ?>">
                            <img src="<?= base_url('assets/images/brand-logos/desktop-logo.png'); ?>" alt="logo" class="desktop-logo">
                            <img src="<?= base_url('assets/images/brand-logos/desktop-white.png'); ?>" alt="logo" class="desktop-white">
                        </a>
                    </div>
                    <p class="h5 mb-2 text-center">Forgot Password</p>
                    <p class="mb-4 text-muted op-7 fw-normal text-center">Enter your email to reset your password.</p>

                    <!-- 🔥 Flash Messages -->
                    <?php if (session()->has('error')) : ?>
                        <div class="alert alert-danger"><?= session('error') ?></div>
                    <?php elseif (session()->has('success')) : ?>
                        <div class="alert alert-success"><?= session('success') ?></div>
                    <?php endif; ?>

                    <form action="<?= base_url('/forgot-password') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row gy-3">
                            <!-- 🔹 Email -->
                            <div class="col-xl-12">
                                <label for="email" class="form-label text-default">Email<sup class="fs-12 text-danger">*</sup></label>
                                <input type="email" name="email" class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" id="email" 
                                       placeholder="Enter your email" required>
                                <div class="invalid-feedback"><?= session('errors.email') ?></div>
                            </div>
                        </div>

                        <!-- 🔥 Submit Button -->
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Send Reset Link</button>
                        </div>
                    </form>

                    <!-- 🔥 Back to Login -->
                    <div class="text-center">
                        <p class="text-muted mt-3 mb-0">Remembered your password? <a href="<?= base_url('login'); ?>" class="text-primary">Sign In</a></p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
