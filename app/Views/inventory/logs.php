<?= $this->extend('layouts/main'); ?>

<!-- Bagian untuk menambahkan CSS khusus -->
<?= $this->section('styles'); ?>
<!-- Flatpickr CSS kalau nanti mau filter tanggal -->
<link rel="stylesheet" href="<?= base_url('assets/libs/flatpickr/flatpickr.min.css'); ?>">
<?= $this->endSection('styles'); ?>

<!-- Konten Utama -->
<?= $this->section('content'); ?>

<!-- Start::Breadcrumb -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('inventory') ?>">Inventory</a></li>
                <li class="breadcrumb-item active" aria-current="page">Riwayat Stok</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">📦 Riwayat Stok Produk</h1>
    </div>
</div>
<!-- End::Breadcrumb -->

<!-- Flash Message -->
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Start::Filter Form -->
<div class="card custom-card mt-3">
    <div class="card-body">
        <form method="get" class="row gy-3 align-items-end">
            <div class="col-md-4">
                <label for="product_id" class="form-label">Produk</label>
                <select name="product_id" class="form-select" required>
                    <option value="">-- Pilih Produk --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= ($product_id == $p['id']) ? 'selected' : '' ?>>
                            <?= esc($p['nama_produk']) ?> (<?= esc($p['sku']) ?>)
                        </option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="warehouse_id" class="form-label">Gudang</label>
                <select name="warehouse_id" class="form-select" required>
                    <option value="">-- Pilih Gudang --</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= $w['id'] ?>" <?= ($warehouse_id == $w['id']) ? 'selected' : '' ?>>
                            <?= esc($w['name']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="ri-search-line align-middle me-1"></i> Tampilkan Log
                </button>
            </div>
        </form>
    </div>
</div>
<!-- End::Filter Form -->

<!-- Start::Tabel Log -->
<?php if (isset($logs) && count($logs) > 0): ?>
    <div class="card custom-card mt-3">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                <thead class="table-light">
    <tr>
        <th>Tanggal</th>
        <th>Jenis</th>
        <th class="text-end">Jumlah</th>
        <th>User</th> <!-- 🧑 Tambah ini -->
        <th>Keterangan</th>
    </tr>
</thead>
<tbody>
    <?php foreach ($logs as $log): ?>
        <tr>
        <td><?= indo_datetime($log['created_at']) ?></td>
            <td>
                <span class="badge bg-<?= $log['type'] === 'in' ? 'success' : ($log['type'] === 'out' ? 'danger' : 'warning') ?>">
                    <?= ucfirst($log['type']) ?>
                </span>
            </td>
            <td class="text-end"><?= number_format($log['quantity']) ?> pcs</td>
            <td><?= esc($log['user_name'] ?? '-') ?></td> <!-- 🧑 Ini juga -->
            <td><?= esc($log['note'] ?? '-') ?></td>
        </tr>
    <?php endforeach ?>
</tbody>

                </table>
            </div>
        </div>
    </div>
<?php elseif (isset($logs)): ?>
    <div class="alert alert-warning mt-3">Tidak ada data log ditemukan.</div>
<?php endif ?>
<!-- End::Tabel Log -->

<?= $this->endSection(); ?>

<!-- Scripts -->
<?= $this->section('scripts'); ?>
<script src="<?= base_url('assets/libs/flatpickr/flatpickr.min.js'); ?>"></script>
<?= $this->endSection(); ?>
