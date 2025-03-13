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
                    <p class="h5 mb-2 text-center">Sign In</p>
                    <p class="mb-4 text-muted op-7 fw-normal text-center">Welcome back!</p>

                    <!-- 🔥 Flash Messages -->
                    <?php if (session()->has('error')) : ?>
                        <div class="alert alert-danger"><?= session('error') ?></div>
                    <?php elseif (session()->has('success')) : ?>
                        <div class="alert alert-success"><?= session('success') ?></div>
                    <?php endif; ?>

                    <form action="<?= base_url('/login') ?>" method="post">
                        <?= csrf_field() ?>

                        <input type="hidden" name="_charset_" value="UTF-8">
                        <div class="row gy-3">
                            <!-- 🔹 Email -->
                            <div class="col-xl-12">
                                <label for="signin-email" class="form-label text-default">Email<sup class="fs-12 text-danger">*</sup></label>
                                <input type="email" name="email" class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" id="signin-email" 
                                       value="<?= old('email', isset($_COOKIE['user_email']) ? $_COOKIE['user_email'] : ''); ?>" 
                                       placeholder="Enter your email" required>
                                <div class="invalid-feedback"><?= session('errors.email') ?></div>
                            </div>

                            <!-- 🔹 Password -->
                            <div class="col-xl-12 mb-2">
                                <label for="signin-password" class="form-label text-default d-block">
                                    Password<sup class="fs-12 text-danger">*</sup>
                                    <a href="<?= base_url('forgot-password'); ?>" class="float-end fw-normal text-muted">Forgot password?</a>
                                </label>
                                <div class="position-relative">
                                    <input type="password" name="password" class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>" id="signin-password" placeholder="Enter password" required>
                                    <a href="javascript:void(0);" class="show-password-button text-muted" onclick="togglePassword('signin-password', this)">
                                        <i class="ri-eye-off-line align-middle"></i>
                                    </a>
                                </div>
                                <div class="invalid-feedback"><?= session('errors.password') ?></div>

                                <!-- 🔥 Remember Me -->
                                <div class="mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="defaultCheck1" <?= isset($_COOKIE['remember_token']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label text-muted fw-normal" for="defaultCheck1">
                                            Remember me?
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 🔥 Submit Button -->
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Sign In</button>
                        </div>
                    </form>

                    <!-- 🔥 Register Link -->
                    <div class="text-center">
                        <p class="text-muted mt-3 mb-0">Don't have an account? <a href="<?= base_url('register'); ?>" class="text-primary">Sign Up</a></p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    function togglePassword(fieldId, el) {
        let input = document.getElementById(fieldId);
        if (input.type === "password") {
            input.type = "text";
            el.innerHTML = '<i class="ri-eye-line align-middle"></i>';
        } else {
            input.type = "password";
            el.innerHTML = '<i class="ri-eye-off-line align-middle"></i>';
        }
    }
</script>
<?= $this->endSection(); ?>
