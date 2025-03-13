<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Tambah Produk
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('products') ?>">Manajemen Produk</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Produk</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Tambah Produk</h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Form Tambah Produk</div>
            </div>
            <div class="card-body">
                <form action="<?= base_url('products/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Produk</label>
                        <input type="text" name="nama_produk" class="form-control" 
                               value="<?= old('nama_produk') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Brand</label>
                        <select name="brand_id" class="form-control" required>
                            <option value="">Pilih Brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?= $brand['id'] ?>" 
                                    <?= old('brand_id') == $brand['id'] ? 'selected' : '' ?>>
                                    <?= esc($brand['brand_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">SKU</label>
                        <input type="text" name="sku" class="form-control" 
                               value="<?= old('sku') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">HPP</label>
                        <input type="number" name="hpp" class="form-control" 
                               value="<?= old('hpp') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Stock</label>
                        <input type="number" name="stock" class="form-control" 
                               value="<?= old('stock') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">No BPOM</label>
                        <input type="text" name="no_bpom" class="form-control" 
                               value="<?= old('no_bpom') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">No Halal</label>
                        <input type="text" name="no_halal" class="form-control" 
                               value="<?= old('no_halal') ?>">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Simpan
                    </button>
                    <a href="<?= base_url('products') ?>" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
