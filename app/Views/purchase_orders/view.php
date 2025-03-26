<?= $this->extend('layouts/main'); ?>

<?= $this->section('title') ?> Detail Purchase Order <?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Flash Messages -->
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Start::page-header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('purchasing') ?>">Manajemen Pembelian</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('purchase-orders') ?>">Purchase Orders</a></li>
                <li class="breadcrumb-item active" aria-current="page">Detail PO #<?= esc($po['po_number']) ?></li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">
            <i class="fas fa-file-invoice-dollar me-2"></i> Detail Purchase Order
        </h1>
    </div>
    <div class="btn-list">
        <a href="<?= base_url('purchase-orders/receive/' . $po['id']) ?>" class="btn btn-success btn-wave">
            <i class="fas fa-truck-loading me-1"></i> Terima PO
        </a>
        <button onclick="window.print()" class="btn btn-primary btn-wave">
            <i class="ri-printer-line me-1"></i> Print
        </button>
    </div>
</div>
<!-- End::page-header -->

<!-- Start::row-1 -->
<div class="row">
    <div class="col-xl-9">
        <div class="card custom-card">
            <div class="card-header d-md-flex d-block">
                <div class="h5 mb-0 d-sm-flex d-block align-items-center">
                    <div class="avatar avatar-sm">
                    <img src="<?= service('settings')->getLogo('desktop') ?>" alt="logo" class="desktop-logo">
                    </div>
                    <div class="ms-sm-2 ms-0 mt-sm-0 mt-2">
                        <div class="h6 fw-medium mb-0">PURCHASE ORDER : <span class="text-primary">#<?= esc($po['po_number']) ?></span></div>
                    </div>
                </div>
                <div class="ms-auto mt-md-0 mt-2">
                    <a href="<?= base_url('purchase-orders/edit/' . $po['id']) ?>" class="btn btn-warning btn-wave">
                        <i class="ri-edit-line me-1"></i> Edit PO
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row gy-3">
                    <div class="col-xl-6">
                        <div class="border p-3 rounded-3">
                            <p class="text-muted mb-2 fs-14">Supplier:</p>
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-user-3-line fs-18 text-primary"></i>
                                <div>
                                    <p class="fw-medium mb-0"><?= esc($po['supplier_name']) ?></p>
                                    <!-- Di bagian tampilan supplier address -->
                                    <p class="text-muted mb-0 fs-12"><?= esc($po['supplier_address'] ?? 'Alamat belum diisi') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-6">
                        <div class="border p-3 rounded-3">
                            <div class="row">
                                <div class="col-6">
                                    <p class="text-muted fs-14 mb-1">Tanggal PO:</p>
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="ri-calendar-line fs-16 text-primary"></i>
                                        <span class="fw-medium"><?= date('d M Y', strtotime($po['created_at'])) ?></span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <p class="text-muted fs-14 mb-1">Status:</p>
                                    <span class="badge bg-<?= $po['status'] == 'completed' ? 'success' : 'warning' ?>-transparent"><?= esc($po['status']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Produk -->
                    <div class="col-xl-12">
                        <div class="table-responsive">
                            <table class="table table-bordered text-nowrap border mt-4">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" width="50">#</th>
                                        <th>Produk</th>
                                        <th class="text-end">Qty PO</th>
                                        <th class="text-end">Diterima</th>
                                        <th class="text-end">Sisa</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($po['products'] as $index => $product): ?>
                                        <tr>
                                            <td class="text-center"><?= $index + 1 ?></td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="lh-1">
                                                        <span class="d-block fw-medium"><?= esc($product['nama_produk']) ?></span>
                                                        <span class="text-muted fs-12">SKU: <?= esc($product['sku']) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end"><?= esc($product['quantity']) ?> pcs</td>
                                            <td class="text-end"><?= esc($product['received_quantity']) ?> pcs</td>
                                            <td class="text-end"><?= esc($product['remaining_quantity']) ?> pcs</td>
                                            <td class="text-end">Rp <?= number_format($product['unit_price'], 0, ',', '.') ?></td>
                                            <td class="text-end fw-medium">Rp <?= number_format($product['total_price'], 0, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot class="border-top">
                                    <tr>
                                        <td colspan="5" class="text-end fw-medium">Grand Total</td>
                                        <td colspan="2" class="text-end fw-medium text-success">
                                            Rp <?= number_format(array_sum(array_column($po['products'], 'total_price')), 0, ',', '.') ?>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info -->
    <div class="col-xl-3">
        <div class="card custom-card">
            <div class="card-header">
                <div class="card-title">
                    <i class="ri-information-line me-2"></i> Informasi PO
                </div>
            </div>
            <div class="card-body">
                <div class="row gy-3">
                    <div class="col-xl-12">
                        <p class="fw-medium text-muted mb-1">Dibuat Pada:</p>
                        <p class="fs-14 mb-2"><?= date('d M Y H:i', strtotime($po['created_at'])) ?></p>
                        
                        <p class="fw-medium text-muted mb-1">Terakhir Update:</p>
                        <p class="fs-14 mb-2"><?= date('d M Y H:i', strtotime($po['updated_at'])) ?></p>
                        
                        <div class="alert alert-info mt-3">
                            <i class="ri-alert-line me-2"></i>
                            Status penerimaan: <?= $po['status'] == 'completed' ? 'Lengkap' : 'Belum lengkap' ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-center">
                <form action="<?= base_url('purchase-orders/delete/' . $po['id']) ?>" method="post" class="d-inline" onsubmit="return confirmDelete(event, this)">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger btn-wave w-100">
                        <i class="ri-delete-bin-5-line me-1"></i> Hapus PO
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End::row-1 -->

<script>
function confirmDelete(event, form) {
    event.preventDefault();
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data PO ini akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}
</script>

<?= $this->endSection() ?>