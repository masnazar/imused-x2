<div class="card custom-card">
    <div class="card-body">
        <h5 class="card-title mb-4">Konfigurasi Email</h5>
        
        <form action="<?= base_url('/settings/email/update') ?>" method="post">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">SMTP Host</label>
                    <input type="text" name="email_smtp_host" class="form-control" 
                        value="<?= esc($email['email_smtp_host'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">SMTP User</label>
                    <input type="email" name="email_smtp_user" class="form-control" 
                        value="<?= esc($email['email_smtp_user'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">SMTP Password</label>
                    <div class="input-group">
                        <input type="password" name="email_smtp_pass" class="form-control" 
                            value="<?= esc($email['email_smtp_pass'] ?? '') ?>" required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="ri-eye-line"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">SMTP Port</label>
                    <input type="number" name="email_smtp_port" class="form-control" 
                        value="<?= esc($email['email_smtp_port'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Enkripsi</label>
                    <select name="email_smtp_crypto" class="form-select" required>
                        <option value="tls" <?= ($email['email_smtp_crypto'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                        <option value="ssl" <?= ($email['email_smtp_crypto'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                    </select>
                </div>

                <div class="col-12 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>