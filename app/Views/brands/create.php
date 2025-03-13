<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Tambah Brand Baru
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('brands') ?>">Manajemen Brand</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Brand</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Tambah Brand Baru</h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Form Tambah Brand</div>
            </div>
            <div class="card-body">
                <form action="<?= base_url('brands/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">Pilih Supplier</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?= $supplier['id'] ?>"><?= esc($supplier['supplier_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Brand</label>
                        <input type="text" name="brand_name" class="form-control" value="<?= old('brand_name') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Warna Utama</label>
                        <input type="color" name="primary_color" class="form-control form-control-color" value="#000000" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Warna Sekunder</label>
                        <input type="color" name="secondary_color" class="form-control form-control-color" value="#000000" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Warna Aksen</label>
                        <input type="color" name="accent_color" class="form-control form-control-color" value="#000000" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Simpan
                    </button>
                    <a href="<?= base_url('brands') ?>" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
