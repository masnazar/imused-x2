<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Tambah Warehouse
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('warehouse') ?>">Manajemen Warehouse</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Warehouse</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Tambah Warehouse</h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card custom-card">
            <div class="card-body">
                <form action="<?= base_url('warehouse/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label">Nama Warehouse</label>
                        <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kode Gudang</label>
                        <input type="text" name="code" class="form-control" value="<?= old('code') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control"><?= old('address') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jenis Warehouse</label>
                        <select name="warehouse_type" class="form-select">
                            <option value="Internal" <?= old('warehouse_type') == 'Internal' ? 'selected' : '' ?>>Internal</option>
                            <option value="Third-Party" <?= old('warehouse_type') == 'Third-Party' ? 'selected' : '' ?>>Third-Party</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama PIC</label>
                        <input type="text" name="pic_name" class="form-control" value="<?= old('pic_name') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kontak PIC</label>
                        <input type="text" name="pic_contact" class="form-control" value="<?= old('pic_contact') ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Simpan
                    </button>
                    <a href="<?= base_url('warehouse') ?>" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
