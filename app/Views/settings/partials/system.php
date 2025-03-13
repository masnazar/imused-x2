<div class="card custom-card">
    <div class="card-body">
        <h5 class="card-title mb-4">Pengaturan Sistem</h5>
        
        <form action="<?= base_url('/settings/system/update') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Sistem</label>
                    <input type="text" name="system_name" class="form-control" 
                        value="<?= esc($system['system_name'] ?? '') ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Logo</label>
                    <input type="file" name="logo" class="form-control">
                    <?php if(!empty($system['logo'])): ?>
                        <img src="<?= base_url($system['logo']) ?>" class="mt-2 img-thumbnail" style="max-height: 80px;">
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Favicon</label>
                    <input type="file" name="favicon" class="form-control">
                    <?php if(!empty($system['favicon'])): ?>
                        <img src="<?= base_url($system['favicon']) ?>" class="mt-2 img-thumbnail" style="max-height: 40px;">
                    <?php endif; ?>
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