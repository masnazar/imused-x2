<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Daftar Produk<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Start:: Breadcrumbs & Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('products') ?>">Manajemen Produk</a></li>
                <li class="breadcrumb-item active" aria-current="page">Daftar Produk</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">
            <i class="ri-boxing-line me-2"></i> Daftar Produk
            <span class="text-muted fs-14">(Total <?= number_format(count($products)) ?> produk)</span>
        </h1>
    </div>
    <div class="btn-list">
        <a href="<?= base_url('products/create') ?>" class="btn btn-primary btn-wave">
            <i class="ri-add-line me-1"></i> Tambah Produk
        </a>
    </div>
</div>
<!-- End:: Breadcrumbs & Page Header -->

<!-- Flash Messages -->
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Start:: Tabel Produk -->
<div class="card custom-card mt-4 border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="productsTable" class="table table-hover table-borderless mb-0">
                <thead class="bg-light border-bottom">
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th>Produk</th>
                        <th>Brand</th>
                        <th>SKU</th>
                        <th class="text-end">Stok</th>
                        <th class="text-end">HPP</th>
                        <th class="text-end">Nilai Stok</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <!-- Nomor Urut -->
                        <td class="text-center align-middle"></td>
                        
                        <!-- Info Produk -->
                        <td class="align-middle">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar avatar-lg bg-light rounded-2 p-2">
                                    <i class="ri-box-3-line fs-18 text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold"><?= esc($product['nama_produk']) ?></div>
                                    <div class="d-flex gap-2 mt-1">
                                        <?php if ($product['no_bpom']): ?>
                                        <span class="badge bg-info-transparent">
                                            <i class="ri-government-line me-1"></i> BPOM: <?= esc($product['no_bpom']) ?>
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($product['no_halal']): ?>
                                        <span class="badge bg-success-transparent">
                                            <i class="ri-checkbox-circle-line me-1"></i> Halal
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Brand -->
                        <td class="align-middle">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-price-tag-3-line text-muted"></i>
                                <span class="fw-medium"><?= esc($product['brand_name']) ?></span>
                            </div>
                        </td>

                        <!-- Identifikasi -->
                        <td class="align-middle">
                            <div class="d-flex flex-column gap-1">
                                <span class="badge bg-primary-transparent">
                                    <i class="ri-barcode-line me-1"></i> <?= esc($product['sku']) ?>
                                </span>
                            </div>
                        </td>

                        <!-- Stok -->
                        <td class="align-middle text-end">
                            <div class="d-flex flex-column align-items-end">
                                <span class="fw-medium">
                                    <?= number_format($product['stock'], 0, ',', '.') ?> pcs
                                </span>
                                <?php $stockPercentage = ($product['stock'] / 100) * 100; // Contoh perhitungan stok ?>
                                <div class="progress w-100" style="height: 4px;">
                                    <div class="progress-bar 
                                        <?= $product['stock'] > 50 ? 'bg-success' : ($product['stock'] > 20 ? 'bg-warning' : 'bg-danger') ?>" 
                                        role="progressbar" 
                                        style="width: <?= min($stockPercentage, 100) ?>%">
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Harga -->
                        <td class="align-middle text-end">
                            <div class="d-flex flex-column">
                                <span class="fw-medium text-primary">
                                    Rp <?= number_format($product['hpp'], 0, ',', '.') ?>
                                </span>
                                <small class="text-muted">/pcs</small>
                            </div>
                        </td>

                        <!-- Nilai Stok -->
                        <td class="align-middle text-end">
                            <span>
                                Rp <?= number_format($product['total_nilai_stok'], 0, ',', '.') ?>
                            </span>
                        </td>

                        <!-- Aksi -->
                        <td class="align-middle text-center">
                            <div class="dropdown">
                                <button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="dropdown">
                                    <i class="ri-more-2-fill fs-16"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('products/edit/' . $product['id']) ?>">
                                            <i class="ri-edit-line me-2"></i>Edit
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="<?= base_url('products/view/' . $product['id']) ?>">
                                            <i class="ri-eye-line me-2"></i>Detail
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="<?= base_url('products/delete/' . $product['id']) ?>" method="post" 
                                            onsubmit="return confirmDelete(event, this)">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="ri-delete-bin-5-line me-2"></i>Hapus
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- End:: Tabel Produk -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#productsTable').DataTable({
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
        language: {
            search: "Cari Produk:",
            zeroRecords: "Tidak ada produk yang ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ produk",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 produk",
            infoFiltered: "(difilter dari _MAX_ total produk)",
            paginate: {
                previous: "<i class='ri-arrow-left-s-line'></i>",
                next: "<i class='ri-arrow-right-s-line'></i>"
            }
        },
        columnDefs: [
            { 
                orderable: false, 
                targets: [0, 7], // Kolom nomor urut dan aksi tidak bisa di-sort
                className: "text-nowrap"
            },
            { 
                visible: true, 
                targets: [3] // Sembunyikan kolom ID
            }
        ],
        order: [[1, 'asc']], // Default sorting berdasarkan kolom ke-2 (Nama Produk)
        createdRow: function(row, data, dataIndex) {
            // Kosongkan karena akan dihandle oleh drawCallback
        },
        drawCallback: function(settings) {
            var api = this.api();
            var startIndex = api.page.info().start;
            
            // Perbaikan: Gunakan nodes() untuk mengakses row DOM
            api.rows({page:'current'}).nodes().each(function(node, index) {
                $('td:eq(0)', node).html(startIndex + index + 1);
            });
        }
    });
});

function getStatusColor(status) {
  return {
    Understock: 'danger',
    Optimal: 'success',
    Overstock: 'warning'
  }[status] ?? 'secondary';
}

</script>

<style>
#productsTable tbody td {
    background-color: white;
    border-top: 1px solid #f1f3f5;
    border-bottom: 1px solid #f1f3f5;
    padding: 16px;
    vertical-align: middle;
    transition: all 0.2s ease;
}

#productsTable tbody tr:hover td {
    background-color: #f8fafc;
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.progress {
    max-width: 120px;
    background-color: #f1f3f5;
}

.avatar-lg {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary-transparent {
    background-color: rgba(13, 110, 253, 0.1);
}

.bg-success-transparent {
    background-color: rgba(25, 135, 84, 0.1);
}

.bg-info-transparent {
    background-color: rgba(13, 202, 240, 0.1);
}
</style>

<script>
function confirmDelete(event, form) {
    event.preventDefault();
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data produk ini akan dihapus secara permanen!",
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