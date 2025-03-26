<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Manajemen Purchase Orders<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Start:: Breadcrumbs & Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('purchasing') ?>">Manajemen Pembelian</a></li>
                <li class="breadcrumb-item active" aria-current="page">Purchase Orders</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">
            <i class="fas fa-file-invoice-dollar me-2"></i> Purchase Orders
        </h1>
    </div>
</div>
<!-- End:: Breadcrumbs & Page Header -->

<!-- Flash Messages -->
<?= $this->include('layouts/components/flashMessage') ?>

 <!-- Start:: Statistik PO -->
 <div class="row row-cards mb-4">
    <div class="col-xl-3">
        <div class="card custom-card overflow-hidden border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex gap-3 align-items-center">
                    <div class="avatar avatar-lg bg-primary-transparent rounded-2 p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M14 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11zm-3-9H7v-2h8v2zm0 4H7v-2h8v2zm-5-8H7V7h3V5z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="fs-13 text-muted">Total PO</div>
                        <div class="fs-21 fw-semibold"><?= number_format($totalPOs) ?></div>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-primary-transparent text-primary fs-10">
                            <i class="ri-arrow-up-line fs-11"></i> <?= $growthTotal ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3">
        <div class="card custom-card overflow-hidden border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex gap-3 align-items-center">
                    <div class="avatar avatar-lg bg-warning-transparent rounded-2 p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-4h2v2h-2zm0-10h2v6h-2z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="fs-13 text-muted">Pending PO</div>
                        <div class="fs-21 fw-semibold"><?= number_format($pendingPOs) ?></div>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-warning-transparent text-warning fs-10">
                            <i class="ri-arrow-up-line fs-11"></i> <?= $growthPending ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3">
        <div class="card custom-card overflow-hidden border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex gap-3 align-items-center">
                    <div class="avatar avatar-lg bg-success-transparent rounded-2 p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="fs-13 text-muted">Completed PO</div>
                        <div class="fs-21 fw-semibold"><?= number_format($completedPOs) ?></div>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-success-transparent text-success fs-10">
                            <i class="ri-arrow-up-line fs-11"></i> <?= $growthCompleted ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3">
        <div class="card custom-card overflow-hidden border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex gap-3 align-items-center">
                    <div class="avatar avatar-lg bg-info-transparent rounded-2 p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M13 2.05v2.02c3.95.49 7 3.85 7 7.93c0 3.21-1.92 6-4.72 7.28l-1.43-2.34c1.27-.89 2.15-2.33 2.15-3.94c0-2.64-2.05-4.78-4.65-4.96L13 10V2.05M12 19c-3.87 0-7-3.13-7-7c0-3.53 2.61-6.43 6-6.92V2.05C5.94 2.55 2 6.81 2 12c0 5.52 4.48 10 10 10c4.31 0 8-2.74 9.43-6.66l-1.91-.66C18.33 17.22 15.38 19 12 19z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="fs-13 text-muted">Total Items</div>
                        <div class="fs-21 fw-semibold"><?= number_format($totalItems) ?></div>
                    </div>
                    <div class="ms-auto">
                        <span class="badge bg-info-transparent text-info fs-10">
                            <i class="ri-arrow-up-line fs-11"></i> <?= $growthItems ?>%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End:: Statistik PO -->

<!-- Bagian atas file -->
<?= $date_filter ?>

<!-- Tambah PO -->
<div class="btn-list">
        <a href="<?= base_url('purchase-orders/create') ?>" class="btn btn-primary btn-wave">
            <i class="ri-add-line me-1"></i> Buat PO Baru
        </a>
</div>

<!-- Start:: Tabel Purchase Orders -->
<div class="card custom-card mt-4 border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="purchaseOrdersTable" class="table table-hover table-borderless mb-0">
                <thead class="bg-light border-bottom">
                    <tr>
                        <th class="text-center" width="50">#</th>
                        <th>Purchase Order</th>
                        <th>Produk</th>
                        <th>Tanggal</th>
                        <th class="text-center" width="120">Status</th>
                        <th class="text-center" width="80">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
<!-- End:: Tabel Purchase Orders -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// 🎨 Status Badge Configuration
const statusConfig = {
    pending: { 
        class: 'bg-danger-soft', 
        icon: 'ri-time-line', 
        label: 'Pending' 
    },
    partial: { 
        class: 'bg-warning-soft', 
        icon: 'ri-progress-4-line', 
        label: 'Partial' 
    },
    completed: { 
        class: 'bg-success-soft', 
        icon: 'ri-checkbox-circle-line', 
        label: 'Completed' 
    }
};

$(document).ready(function() {
    const table = $('#purchaseOrdersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
    url: "<?= base_url('purchase-orders/getData') ?>",
    type: "POST",
    data: function(d) {
        d.jenis_filter = $('#jenisFilter').val();
        d.start_date = $('input[name="start_date"]').val();
        d.end_date = $('input[name="end_date"]').val();
        d.periode = $('select[name="periode"]').val();
    },
    error: function(xhr, error, code) {
        console.error('Error loading data:', xhr.responseText);
    }
},
        columns: [
            { 
                data: null,
                className: 'text-center align-middle',
                render: (data, type, row, meta) => 
                    `<span class="text-muted">${meta.row + meta.settings._iDisplayStart + 1}</span>`
            },
            { 
                data: 'po_number',
                className: 'align-middle',
                render: function(data, type, row) {
                    return `
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">${data}</div>
                                <small class="text-muted">${row.supplier_name}</small>
                            </div>
                        </div>
                    `;
                }
            },
            { 
    data: 'products',
    className: 'align-middle',
    render: function(data) {
        try {
            if (!data || data === "-") return '<div class="text-muted">-</div>';
            
            // Format angka dalam list produk
            const formattedProducts = data.split('||').map(product => {
                const parts = product.split('::');
                if (parts.length >= 4) {
                    // Format quantity dan harga
                    parts[2] = parseInt(parts[2]).toLocaleString('id-ID') + ' pcs';
                    parts[3] = 'Rp ' + parseInt(parts[3]).toLocaleString('id-ID');
                }
                return parts.join('::');
            }).join('||');
            
            return `<div class="product-list">${formattedProducts}</div>`;
        } catch (e) {
            console.error('Error rendering products:', e);
            return '<div class="text-danger">Error memuat data</div>';
        }
    }
},
            { 
                data: 'created_at',
                className: 'align-middle',
                render: function(data) {
                    const date = new Date(data);
                    return `
                        <div class="text-nowrap">
                            <div>${date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}</div>
                            <small class="text-muted">${date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}</small>
                        </div>
                    `;
                }
            },
            { 
                data: 'status',
                className: 'align-middle text-center',
                render: function(data) {
                    const status = data.toLowerCase();
                    const config = statusConfig[status] || statusConfig.pending;
                    return `
                        <div class="badge ${config.class} rounded-pill p-2 px-3">
                            <i class="${config.icon} me-1"></i>${config.label}
                        </div>
                    `;
                }
            },
            { 
                data: 'id',
                className: 'align-middle text-center',
                render: function(data) {
                    return `
                        <div class="dropdown">
                            <button class="btn btn-ghost btn-sm" type="button" data-bs-toggle="dropdown">
                                <i class="ri-more-2-fill fs-16"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('purchase-orders/view/') ?>${data}">
                                        <i class="ri-eye-line me-2"></i>Detail
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url('purchase-orders/edit/') ?>${data}">
                                        <i class="ri-edit-line me-2"></i>Edit
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="<?= base_url('purchase-orders/delete/') ?>${data}" method="post" 
                                        onsubmit="return confirmDelete(event, this)">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="ri-delete-bin-5-line me-2"></i>Hapus
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    `;
                }
            }
        ]
    });
});
</script>

<style>
/* Custom Styling */
#purchaseOrdersTable {
    border-collapse: separate;
    border-spacing: 0 8px;
}

#purchaseOrdersTable thead th {
    background-color: #f8f9fa;
    border: none;
    padding: 12px 16px;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

#purchaseOrdersTable tbody td {
    background-color: white;
    border-top: 1px solid #f1f3f5;
    border-bottom: 1px solid #f1f3f5;
    padding: 16px;
    vertical-align: middle;
    transition: all 0.2s ease;
}

#purchaseOrdersTable tbody tr:hover td {
    background-color: #f8fafc;
    transform: translateX(4px);
}

#purchaseOrdersTable tbody tr td:first-child {
    border-left: 1px solid #f1f3f5;
    border-radius: 8px 0 0 8px;
}

#purchaseOrdersTable tbody tr td:last-child {
    border-right: 1px solid #f1f3f5;
    border-radius: 0 8px 8px 0;
}

.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
}

.bg-danger-soft {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.bg-warning-soft {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.bg-success-soft {
    background-color: rgba(25, 135, 84, 0.1);
    color: #198754;
}

.product-list {
    max-width: 300px;
}
.product-list small.text-muted {
    font-size: 0.8em;
}

.product-list .badge {
    font-family: monospace;
    min-width: 70px;
}
</style>

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