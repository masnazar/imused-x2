<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Edit Brand - <?= esc($brand['brand_name']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('brands') ?>">Manajemen Brand</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Brand</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Edit Brand - <?= esc($brand['brand_name']) ?></h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Form Edit Brand</div>
            </div>
            <div class="card-body">
                <form action="<?= base_url('brands/update/' . $brand['id']) ?>" method="post">
                    <?= csrf_field() ?>

                    <!-- Pilih Supplier -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">Pilih Supplier</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= $supplier['id'] ?>" <?= ($supplier['id'] == $brand['supplier_id']) ? 'selected' : '' ?>>
                                    <?= esc($supplier['supplier_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Nama Brand -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Brand</label>
                        <input type="text" name="brand_name" class="form-control" value="<?= esc($brand['brand_name']) ?>" required>
                    </div>

                    <!-- Warna Primer -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Warna Primer</label>
                        <input type="color" name="primary_color" class="form-control form-control-color"
                               value="<?= esc($brand['primary_color']) ?>" required>
                    </div>

                    <!-- Warna Sekunder -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Warna Sekunder</label>
                        <input type="color" name="secondary_color" class="form-control form-control-color"
                               value="<?= esc($brand['secondary_color']) ?>" required>
                    </div>

                    <!-- Warna Aksen -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Warna Aksen</label>
                        <input type="color" name="accent_color" class="form-control form-control-color"
                               value="<?= esc($brand['accent_color']) ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Simpan Perubahan
                    </button>
                    <a href="<?= base_url('brands') ?>" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
