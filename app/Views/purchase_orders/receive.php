<?= $this->extend('layouts/main'); ?>

<!-- Bagian untuk menambahkan CSS khusus -->
<?= $this->section('styles'); ?>
<!-- FlatPickr CSS -->
<link rel="stylesheet" href="<?= base_url('assets/libs/flatpickr/flatpickr.min.css'); ?>">
<?= $this->endSection('styles'); ?>

<!-- Bagian konten utama -->
<?= $this->section('content'); ?>

<!-- Start::Header Halaman -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Purchasing</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('purchase-orders') ?>">Purchase Order</a></li>
                <li class="breadcrumb-item active" aria-current="page">Terima PO #<?= esc($po['po_number']) ?></li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Terima Purchase Order</h1>
    </div>
    <div class="btn-list">
        <a href="<?= base_url('purchase-orders') ?>" class="btn btn-primary btn-wave">
            <i class="ri-arrow-left-line align-middle me-1"></i> Kembali ke PO
        </a>
    </div>
</div>
<!-- End::Header Halaman -->

<!-- Flash Messages -->
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Start::Form Penerimaan -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header d-md-flex d-block">
                <div class="d-flex align-items-center w-100 gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="bg-light rounded-pill p-2 px-3 text-muted fs-14">
                            <i class="ri-archive-line me-1 align-middle"></i> Penerimaan PO
                        </span>
                    </div>
                    <div class="flex-fill">
                        <input type="text" 
                               class="form-control form-control-lg bg-light rounded-pill p-2 px-3 text-muted fs-14" 
                               value="#<?= esc($po['po_number']) ?>" 
                               readonly 
                               style="font-weight: 600; color: var(--primary-color)">
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Form untuk menyimpan penerimaan -->
                <form action="<?= base_url('purchase-orders/store-receive') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="purchase_order_id" value="<?= $po['id'] ?>">

                    <!-- Pilihan Gudang dan Nomor Surat Jalan -->
                    <div class="row gy-3 mb-4">
                        <div class="col-xl-4">
                            <label for="nomor_surat_jalan" class="form-label">Nomor Surat Jalan</label>
                            <input type="text" 
                                   class="form-control form-control-light" 
                                   id="nomor_surat_jalan" 
                                   name="nomor_surat_jalan" 
                                   required
                                   placeholder="Masukkan nomor surat jalan"
                                   oninput="this.value = this.value.toUpperCase()"
                                   style="text-transform: uppercase">
                        </div>
                        <div class="col-xl-4">
                            <label for="warehouse_id" class="form-label">Pilih Gudang</label>
                            <select name="warehouse_id" id="warehouse_id" class="form-control form-control-light" required>
                                <option value="">-- Pilih Gudang --</option>
                                <?php foreach ($warehouses as $warehouse): ?>
                                    <option value="<?= $warehouse['id'] ?>"><?= esc($warehouse['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Tabel Produk -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-nowrap border mt-3">
                            <thead class="table-light">
                                <tr>
                                    <th>SKU</th>
                                    <th>Nama Produk</th>
                                    <th class="text-end">Jumlah PO</th>
                                    <th class="text-end">Sudah Diterima</th>
                                    <th class="text-end">Sisa PO</th>
                                    <th class="text-end">Diterima Sekarang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($po['products'] as $index => $product): ?>
                                    <tr>
                                        <td><?= esc($product['sku']) ?></td>
                                        <td><?= esc($product['nama_produk']) ?></td>
                                        <td class="text-end"><?= esc($product['quantity']) ?> pcs</td>
                                        <td class="text-end"><?= esc($product['received_quantity']) ?> pcs</td>
                                        <td class="text-end"><?= esc($product['remaining_quantity']) ?> pcs</td>
                                        <td class="text-end">
                                            <input type="hidden" name="products[<?= $index ?>][product_id]" value="<?= $product['product_id'] ?>">
                                            <input type="number" 
       name="products[<?= $index ?>][received_quantity]" 
       class="form-control form-control-light text-end border-0"
       min="0" 
       max="<?= $product['remaining_quantity'] ?>" 
       placeholder="0" 
       required
       oninput="validateQuantity(this, <?= $product['remaining_quantity'] ?>)">

<script>
function validateQuantity(input, max) {
    let value = parseInt(input.value) || 0;
    
    // Batasi nilai antara 0 dan max
    if (value < 0) {
        input.value = 0;
    } else if (value > max) {
        input.value = max;
    }
    
    // Update warna input
    input.classList.toggle('is-invalid', value < 0 || value > max);
}
</script>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="reset" class="btn btn-light">Reset</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-check-line align-middle me-1"></i> Simpan Penerimaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End::Form Penerimaan -->

<?= $this->endSection('content'); ?>

<!-- Bagian untuk menambahkan JavaScript khusus -->
<?= $this->section('scripts'); ?>
<!-- Flatpickr JS -->
<script src="<?= base_url('assets/libs/flatpickr/flatpickr.min.js'); ?>"></script>

<script>
    // Inisialisasi Flatpickr untuk input tanggal
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr('#date_received', {
            dateFormat: 'Y-m-d',
            allowInput: true
        });
    });
</script>
<?= $this->endSection('scripts'); ?>