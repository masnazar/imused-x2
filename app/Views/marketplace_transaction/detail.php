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
    <button onclick="fetchTrackingStatus('<?= $transaction['courier_code'] ?>', '<?= $transaction['tracking_number'] ?>')" class="btn btn-warning btn-wave">
    <i class="fas fa-sync-alt me-1"></i> Update Resi
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
                                <th class="text-end">Total HPP</th>
                                <th class="text-end">Diskon</th>
                                <th class="text-end">Admin Fee</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $totalHpp = 0;
                            $totalDiskon = 0;
                            $totalAdminFee = 0;

                            foreach ($products as $i => $item):
                                $rowHpp = $item['quantity'] * $item['hpp'];
                                $rowProfit = $item['unit_selling_price'] * $item['quantity'] - $rowHpp - $item['discount'] - $item['admin_fee'];

                                $totalHpp += $rowHpp;
                                $totalDiskon += $item['discount'];
                                $totalAdminFee += $item['admin_fee'];
                            ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td>
                                        <div class="fw-medium"><?= esc($item['nama_produk']) ?></div>
                                        <small class="text-muted">SKU: <?= esc($item['sku']) ?></small>
                                    </td>
                                    <td class="text-end"><?= number_format($item['quantity'], 0, ',', '.') ?> pcs</td>
                                    <td class="text-end">Rp <?= number_format($item['unit_selling_price'], 0, ',', '.') ?></td>
                                    <td class="text-end text-danger">Rp <?= number_format($rowHpp, 0, ',', '.') ?></td>
                                    <td class="text-end">Rp <?= number_format($item['discount'], 0, ',', '.') ?></td>
                                    <td class="text-end">Rp <?= number_format($item['admin_fee'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>

                        <tfoot>
                            <tr class="table-light">
                                <td colspan="4" class="text-end fw-medium">Estimasi Profit</td>
                                <td colspan="3" class="text-end fw-bold text-success">
                                    Rp <?= number_format(
                                        $transaction['selling_price'] - $totalHpp - $totalDiskon - $totalAdminFee,
                                        0, ',', '.'
                                    ) ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" class="text-end text-muted fst-italic">
                                    <small>Estimasi Profit = Harga Jual - HPP - Diskon - Admin Fee</small>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($trackingSummary)): ?>
<div class="card custom-card mt-4">
    <div class="card-header">
        <h6 class="card-title">
            <i class="ri-truck-line me-2"></i> Ringkasan Pengiriman
        </h6>
    </div>
    <div class="card-body">
        <div class="row gy-3">

            <!-- Nomor Resi + Kurir -->
            <div class="col-md-12 border-bottom pb-3">
                <div class="row gy-2">
                    <div class="col-md-6">
                        <div class="mb-1 text-muted">
                            <i class="ri-barcode-line me-1 text-primary"></i> Nomor Resi:
                        </div>
                        <div class="d-flex align-items-center">
                            <strong><?= esc($trackingSummary['awb'] ?? '-') ?></strong>
                            <button onclick="copyText('<?= esc($trackingSummary['awb']) ?>')" class="btn btn-sm btn-light ms-2">
                                <i class="ri-file-copy-line"></i> Salin
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-1 text-muted">
                            <i class="ri-truck-line me-1 text-primary"></i> Kurir:
                        </div>
                        <strong>
                            <?= esc($trackingSummary['courier'] ?? '-') ?>
                            <?= !empty($trackingSummary['service']) ? '(' . esc($trackingSummary['service']) . ')' : '' ?>
                        </strong>
                    </div>
                </div>
            </div>

            <!-- Status + Update Time + Berat -->
            <div class="col-md-12 border-bottom pb-3 pt-3">
                <div class="row gy-2">
                    <div class="col-md-4">
                        <div class="mb-1 text-muted">
                            <i class="ri-information-line me-1 text-primary"></i> Status Terkini:
                        </div>
                        <span class="badge bg-info">
                            <?= esc(ucwords(strtolower($trackingSummary['status'] ?? '-'))) ?>
                        </span>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-1 text-muted">
                            <i class="ri-time-line me-1 text-primary"></i> Waktu Update:
                        </div>
                        <strong><?= date('D, d M Y H:i', strtotime($trackingSummary['date'] ?? now())) ?></strong>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-1 text-muted">
                            <i class="ri-weight-line me-1 text-primary"></i> Berat Paket:
                        </div>
                        <strong><?= esc($trackingSummary['weight'] ?? '-') ?> gram</strong>
                    </div>
                </div>
            </div>

            <!-- Kota Asal + Tujuan -->
            <div class="col-md-12 border-bottom pb-3 pt-3">
                <div class="row gy-2">
                    <div class="col-md-6">
                        <div class="mb-1 text-muted">
                            <i class="ri-map-pin-line me-1 text-primary"></i> Kota Asal:
                        </div>
                        <p class="fw-medium mb-0"><?= esc($tracking['detail']['origin'] ?? '-') ?></p>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-1 text-muted">
                            <i class="ri-map-pin-user-line me-1 text-primary"></i> Kota Tujuan:
                        </div>
                        <p class="fw-medium mb-0"><?= esc($tracking['detail']['destination'] ?? '-') ?></p>
                    </div>
                </div>
            </div>

            <!-- Pengirim + Penerima -->
            <div class="col-md-12 pt-3">
                <div class="row gy-2">
                    <div class="col-md-6">
                        <div class="mb-1 text-muted">
                            <i class="ri-user-location-line me-1 text-primary"></i> Pengirim:
                        </div>
                        <p class="fw-medium mb-0"><?= esc($tracking['detail']['shipper'] ?? '-') ?></p>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-1 text-muted">
                            <i class="ri-user-received-2-line me-1 text-primary"></i> Penerima:
                        </div>
                        <p class="fw-medium mb-0"><?= esc($tracking['detail']['receiver'] ?? '-') ?></p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php endif; ?>

<button
  class="btn btn-outline-dark"
  onclick="showTrackingModal('<?= esc($transaction['courier_code']) ?>', '<?= esc($transaction['tracking_number']) ?>')">
  <i class="ri-map-pin-time-line me-1"></i> Lihat Timeline
</button>



<?php
$tracking = $transaction['last_tracking_data'] 
    ? json_decode($transaction['last_tracking_data'], true)
    : null;

// Fungsi format tanggal Indonesia
function indonesian_date($date) {
    $months = array(
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    );
    $days = array(
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    );
    
    $dateObj = date_create($date);
    return [
        'full_date' => $dateObj->format('d') . ' ' . $months[$dateObj->format('n')] . ' ' . $dateObj->format('Y'),
        'day_name' => $days[$dateObj->format('l')],
        'time' => $dateObj->format('H:i')
    ];
}
?>

<!-- Start:: row-1 -->
        <div class="card custom-card border overflow-hidden mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="card-title">
                    <i class="ri-truck-line me-1"></i> Riwayat Pengiriman
                </div>
                <button class="btn btn-sm btn-info" onclick="fetchTrackingStatus('<?= esc($transaction['courier_code']) ?>', '<?= esc($transaction['tracking_number']) ?>')">
                    <i class="ri-refresh-line"></i> Perbarui Status
                </button>
            </div>
            <div class="card-body bg-light">
                <?php if ($tracking && isset($tracking['history'])): ?>
                    <?php 
                    $groupedLogs = [];
                    foreach (array_reverse($tracking['history']) as $log) {
                        $date = date('Y-m-d', strtotime($log['date']));
                        $groupedLogs[$date][] = $log;
                    }
                    ?>
                    
                    <div class="timeline container">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="timeline-container">
                                    <?php foreach ($groupedLogs as $date => $logs): ?>
                                        <?php $formattedDate = indonesian_date($date); ?>
                                        
                                        <div class="timeline-end mb-4">
                                            <span class="p-1 fs-11 bg-primary2 text-fixed-white backdrop-blur text-center border border-primary2 border-opacity-10 rounded-1 lh-1 fw-medium">
                                                <?= $formattedDate['full_date'] ?>
                                            </span>
                                        </div>
                                        
                                        <?php foreach ($logs as $log): ?>
                                            <?php $logTime = indonesian_date($log['date']); ?>
                                            <div class="timeline-continue">
                                                <div class="timeline-right">
                                                    <div class="timeline-content">
                                                        <p class="timeline-date text-muted mb-2">
                                                            <?= $logTime['time'] ?>, <?= $logTime['day_name'] ?>
                                                        </p>
                                                        <div class="timeline-box border border-primary2 border-opacity-25">
                                                            <p class="text-muted mb-0">
                                                                <?= $log['desc'] ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                    
                                    <?php
                                        $status = strtolower($trackingSummary['status'] ?? '');
                                        $badgeClass = 'bg-secondary';

                                        if (str_contains($status, 'delivered')) {
                                            $badgeClass = 'bg-success';
                                        } elseif (str_contains($status, 'return')) {
                                            $badgeClass = 'bg-danger';
                                        } elseif ($status) {
                                            $badgeClass = 'bg-warning';
                                        }
                                        ?>
                                        <div class="timeline-end mb-5">
                                            <span class="p-1 fs-11 <?= $badgeClass ?> text-fixed-white backdrop-blur text-center border border-primary2 border-opacity-10 rounded-1 lh-1 fw-medium">
                                                Status Terakhir: <?= esc(ucwords($status)) ?>
                                            </span>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <p class="text-muted mb-3">
                            <i class="ri-inbox-line fs-2"></i>
                        </p>
                        <p class="text-muted">Belum ada riwayat pengiriman</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

<!-- End:: row-1 -->
<script>
function formatIndonesianDate(dateString) {
    const months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    
    const date = new Date(dateString);
    return {
        fullDate: `${date.getDate()} ${months[date.getMonth()]} ${date.getFullYear()}`,
        day: days[date.getDay()],
        time: date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
    };
}

function copyOrderNumber() {
    const text = document.getElementById("orderNumber").innerText;
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire("Tersalin!", "Nomor pesanan berhasil disalin!", "success");
    });
}

function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        Swal.fire("Tersalin!", "Nomor resi berhasil disalin!", "success");
    });
}

function showTrackingModal(courier, awb) {
  $('#trackingModal').modal('show');
  fetchTrackingStatus(courier, awb, true); // showInModal = true
}

function fetchTrackingStatus(courier, awb) {
    Swal.fire({
        title: "Memuat...",
        html: `<div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
               </div>
               <div class="mt-2">Memperbarui status pengiriman</div>`,
        allowOutsideClick: false,
        showConfirmButton: false
    });

    $.ajax({
        url: "<?= base_url('marketplace-transactions/track-resi') ?>",
        method: "POST",
        data: {
            courier,
            awb,
            <?= csrf_token() ?>: "<?= csrf_hash() ?>"
        },
        success: function(res) {
    Swal.close();

    if (res.status === "success") {
        Swal.fire({
            icon: 'success',
            title: 'Status Diperbarui',
            text: 'Status resi berhasil diperbarui!',
            timer: 1200,
            showConfirmButton: false
        }).then(() => {
            location.reload(); // ✅ RELOAD halaman
        });
    } else {
        Swal.fire("Gagal", "Gagal memperbarui status resi.", "error");
    }
}

    });
}
</script>

<!-- Modal Timeline Tracking -->
<div class="modal fade" id="modalTracking" tabindex="-1" aria-labelledby="modalTrackingLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="ri-map-pin-time-line me-2"></i> 
                    <div>
                        Perjalanan Paket
                        <div class="fs-12 text-muted fw-normal">Nomor Resi: <?= $transaction['tracking_number'] ?></div>
                    </div>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="timeline container">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="timeline-container" id="modalTrackingTimeline">
                                <!-- Timeline items akan dimasukkan disini -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>