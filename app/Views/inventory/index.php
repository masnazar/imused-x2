<?= $this->extend('layouts/main'); ?>

<!-- ⬅️ Styles Section -->
<?= $this->section('styles'); ?>
<link rel="stylesheet" href="<?= base_url('assets/libs/datatables/datatables.min.css'); ?>">
<?= $this->endSection(); ?>

<!-- ⬇️ Konten -->
<?= $this->section('content'); ?>

<!-- Start::Breadcrumb -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Inventory</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">📦 Manajemen Inventory</h1>
    </div>
</div>
<!-- End::Breadcrumb -->

<!-- Flash Message -->
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Filter Gudang -->
<div class="card custom-card mt-3">
    <div class="card-body">
        <form method="get" id="filterForm" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="warehouse_id" class="form-label">Pilih Gudang</label>
                <select name="warehouse_id" id="warehouse_id" class="form-select">
                    <option value="">-- Semua Gudang --</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= $w['id'] ?>" <?= ($warehouse_id == $w['id']) ? 'selected' : '' ?>>
                            <?= esc($w['name']) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="ri-search-line align-middle me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Summary -->
<div class="alert alert-primary d-flex align-items-center gap-2 mt-3" role="alert">
    <i class="ri-archive-line fs-20"></i>
    <div>
        Total Stok Keseluruhan: <strong><?= number_format($total_stock ?? 0) ?> pcs</strong>
    </div>
</div>

<?php if (in_array('view_inventory_logs', session('user_permissions') ?? [])) : ?>
    <div class="d-flex justify-content-end mb-2">
        <a href="<?= base_url('inventory/logs') ?>" class="btn btn-outline-dark">
            <i class="ri-time-line align-middle me-1"></i> Riwayat Stok
        </a>
    </div>
<?php endif; ?>

<!-- Data Table -->
<div class="card custom-card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="inventoryTable" class="table table-hover table-bordered nowrap w-100">
                <thead class="table-light">
                    <tr>
                        <th>SKU</th>
                        <th>Nama Produk</th>
                        <th>Gudang</th>
                        <th class="text-end">Stok</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<!-- Scripts -->
<?= $this->section('scripts'); ?>
<script src="<?= base_url('assets/libs/datatables/datatables.min.js'); ?>"></script>

<script>
    const table = $('#inventoryTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url("inventory/datatable") ?>',
            type: 'GET',
            data: function (d) {
                d.warehouse_id = $('#warehouse_id').val();
            }
        },
        columns: [
            { data: 'sku' },
            { data: 'nama_produk' },
            { data: 'warehouse_name' },
            { data: 'stock', className: 'text-end' },
        ],
        order: [[1, 'asc']],
    });

    // Refresh table on filter submit
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        table.ajax.reload();
    });
</script>

<?= $this->endSection(); ?>
