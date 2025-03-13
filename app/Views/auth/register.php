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
                    <p class="h5 mb-2 text-center">Buat Akun Baru</p>
                    <p class="mb-4 text-muted op-7 fw-normal text-center">Gabung dengan kami sekarang!</p>

                    <form action="<?= base_url('/register') ?>" method="post">
                    <?= csrf_field() ?>

                    <input type="hidden" name="_charset_" value="UTF-8">
                        <div class="row gy-3">
                            
                            <!-- Nama Lengkap -->
                            <div class="col-xl-12">
                                <div class="form-floating">
                                    <input type="text" name="name" class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>" id="name" value="<?= set_value('name'); ?>" placeholder="Nama Lengkap" required>
                                    <label for="name">Nama Lengkap</label>
                                    <div class="invalid-feedback"><?= session('errors.name') ?></div>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="col-xl-12">
                                <div class="form-floating">
                                    <input type="email" name="email" class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>" id="email" value="<?= set_value('email'); ?>" placeholder="Email" required>
                                    <label for="email">Email</label>
                                    <div class="invalid-feedback"><?= session('errors.email') ?></div>
                                </div>
                            </div>

                            <!-- Nomor WhatsApp -->
                            <div class="col-xl-12">
                                <div class="form-floating">
                                    <input type="text" name="whatsapp" class="form-control <?= session('errors.whatsapp') ? 'is-invalid' : '' ?>" id="whatsapp" value="<?= set_value('whatsapp'); ?>" placeholder="62xxxxxxxxxx" required>
                                    <label for="whatsapp">Nomor WhatsApp</label>
                                    <div class="invalid-feedback"><?= session('errors.whatsapp') ?></div>
                                </div>
                            </div>

                            <!-- Tanggal Lahir -->
                            <div class="col-xl-12">
                                <div class="form-floating">
                                    <input type="date" name="birth_date" class="form-control <?= session('errors.birth_date') ? 'is-invalid' : '' ?>" id="birth_date" value="<?= set_value('birth_date'); ?>" required>
                                    <label for="birth_date">Tanggal Lahir</label>
                                    <div class="invalid-feedback"><?= session('errors.birth_date') ?></div>
                                </div>
                            </div>

                            <!-- ✅ Pilihan Gender -->
                            <div class="col-xl-12">
                                <div class="form-floating">
                                    <select name="gender" class="form-select <?= session('errors.gender') ? 'is-invalid' : '' ?>" id="gender" required>
                                        <option value="" selected disabled>Pilih Jenis Kelamin</option>
                                        <option value="L" <?= set_select('gender', 'L'); ?>>Laki-laki</option>
                                        <option value="P" <?= set_select('gender', 'P'); ?>>Perempuan</option>
                                    </select>
                                    <label for="gender">Jenis Kelamin</label>
                                    <div class="invalid-feedback"><?= session('errors.gender') ?></div>
                                </div>
                            </div>

                            <!-- Role ID (Hidden, Default User) -->
                            <input type="hidden" name="role_id" value="2">

                            <!-- Password -->
                            <div class="col-xl-12">
                                <div class="form-floating position-relative">
                                    <input type="password" name="password" class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>" id="password" placeholder="Kata Sandi" required>
                                    <label for="password">Kata Sandi</label>
                                    <div class="invalid-feedback"><?= session('errors.password') ?></div>
                                    <i class="ri-eye-off-line toggle-password" data-target="#password"></i>
                                </div>
                            </div>

                            <!-- Konfirmasi Password -->
                            <div class="col-xl-12">
                                <div class="form-floating position-relative">
                                    <input type="password" name="confirm_password" class="form-control <?= session('errors.confirm_password') ? 'is-invalid' : '' ?>" id="confirm_password" placeholder="Konfirmasi Kata Sandi" required>
                                    <label for="confirm_password">Konfirmasi Kata Sandi</label>
                                    <div class="invalid-feedback"><?= session('errors.confirm_password') ?></div>
                                    <i class="ri-eye-off-line toggle-password" data-target="#confirm_password"></i>
                                </div>
                            </div>

                        </div>
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">Daftar</button>
                        </div>
                    </form>

                    <div class="text-center">
                        <p class="text-muted mt-3 mb-0">Sudah punya akun? <a href="<?= base_url('login'); ?>" class="text-primary">Masuk</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const togglePassword = document.querySelectorAll(".toggle-password");

        togglePassword.forEach(button => {
            button.addEventListener("click", function() {
                let input = document.querySelector(this.dataset.target);
                if (input.type === "password") {
                    input.type = "text";
                    this.classList.remove("ri-eye-off-line");
                    this.classList.add("ri-eye-line");
                } else {
                    input.type = "password";
                    this.classList.remove("ri-eye-line");
                    this.classList.add("ri-eye-off-line");
                }
            });
        });
    });
</script>

<style>
    .toggle-password {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 1.2rem;
        color: #aaa;
    }
    .toggle-password:hover {
        color: #333;
    }
</style>

<?= $this->endSection(); ?>
