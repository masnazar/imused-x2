<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?> Detail Transaksi <?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Flash Messages -->
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Start::page-header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item">
                    <a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= base_url('marketplace-transactions') ?>">Transaksi Marketplace</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    Detail Pesanan
                </li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">
            <i class="fas fa-file-invoice-dollar me-2"></i> Detail Transaksi Marketplace
        </h1>
    </div>
    <div class="btn-list">
        <button onclick="fetchTrackingStatus('<?= $transaction['courier_code'] ?>', '<?= $transaction['tracking_number'] ?>')" 
                class="btn btn-warning btn-wave">
            <i class="fas fa-shipping-fast me-1"></i> Update Resi
        </button>
        <button onclick="window.print()" class="btn btn-primary btn-wave">
            <i class="ri-printer-line me-1"></i> Print
        </button>
    </div>
</div>
<!-- End::page-header -->

<div class="card custom-card mt-4">
    <div class="card-body">
        <div class="row gy-3">
            <div class="col-xl-6">
                <div class="border p-3 rounded-3">
                    <p class="text-muted mb-2 fs-14">Nama Toko | Platform:</p>
                    <div class="d-flex align-items-center gap-2">
                        <i class="ri-store-2-line fs-18 text-primary"></i>
                        <div>
                            <p class="fw-medium mb-0"><?= esc($transaction['store_name']) ?> | <?= ucfirst($transaction['platform']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="border p-3 rounded-3">
                    <div class="row">
                        <div class="col-6">
                            <p class="text-muted fs-14 mb-1">Tanggal Pesanan:</p>
                            <div class="d-flex align-items-center gap-2">
                                <i class="ri-calendar-line fs-16 text-primary"></i>
                                <span class="fw-medium"><?= date('d M Y', strtotime($transaction['date'])) ?></span>
                            </div>
                        </div>
                        <div class="col-6">
                            <p class="text-muted fs-14 mb-1">Status:</p>
                            <span class="badge bg-<?= $transaction['status'] == 'Terkirim' ? 'success' : ($transaction['status'] == 'Returned' ? 'danger' : 'warning') ?>">
                                <?= esc($transaction['status']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-12">
                <div class="h5 fw-medium mb-2">
                    Nomor Pesanan: 
                    <span class="text-primary" id="orderNumber"><?= esc($transaction['order_number']) ?></span>
                    <button onclick="copyOrderNumber()" class="btn btn-sm btn-light ms-2">
                        <i class="ri-file-copy-line"></i> Salin
                    </button>
                </div>
            </div>

            <div class="col-xl-12">
                <h6 class="mt-4 mb-3">Produk</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Produk</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Harga Satuan</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($products as $i => $item): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td>
                                    <div>
                                        <div class="fw-medium"><?= esc($item['nama_produk']) ?></div>
                                        <small class="text-muted">SKU: <?= esc($item['sku']) ?></small>
                                    </div>
                                </td>
                                <td class="text-end"><?= number_format($item['quantity'], 0, ',', '.') ?> pcs</td>
                                <td class="text-end">Rp <?= number_format($item['unit_selling_price'], 0, ',', '.') ?></td>
                                <td class="text-end fw-medium">Rp <?= number_format($item['quantity'] * $item['unit_selling_price'], 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-end fw-medium">Total</td>
                                <td class="text-end text-success fw-bold">Rp <?= number_format($transaction['selling_price'], 0, ',', '.') ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Timeline Tracking -->
            <div class="col-xl-12">
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="card-title"><i class="ri-map-pin-time-line me-2"></i>Status Pesanan Terakhir</h6>
                    </div>
                    <div class="card-body">
                        <ul id="trackingTimeline" class="timeline"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script>
function copyOrderNumber() {
    const text = document.getElementById("orderNumber").innerText;
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire("Tersalin!", "Nomor pesanan berhasil disalin!", "success");
    });
}

function fetchTrackingStatus(courier, awb) {
    Swal.fire({
        title: "Tunggu sebentar...",
        text: "Mengambil status pengiriman...",
        didOpen: () => Swal.showLoading()
    });

    $.ajax({
        url: "<?= base_url('marketplace-transactions/track-resi') ?>",
        method: "POST",
        data: {
            courier,
            awb,
            <?= csrf_token() ?>: "<?= csrf_hash() ?>"
        },
        success: function (res) {
            Swal.close();

            if (res.status === "success") {
                const timeline = $("#trackingTimeline");
                timeline.empty();
                res.data.history.forEach((log) => {
                    timeline.append(`<li class="timeline-item"><strong>${log.date}</strong> - ${log.desc}</li>`);
                });
                Swal.fire("Sukses!", "Status pesanan berhasil diperbarui", "success");
            } else {
                Swal.fire("Gagal", res.message, "error");
            }
        },
        error: function () {
            Swal.fire("Error", "Gagal menghubungi API tracking", "error");
        }
    });
}
</script>

<?= $this->endSection() ?>