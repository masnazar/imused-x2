<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Detail Customer<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
  <div>
    <nav>
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('customers') ?>">Data Customer</a></li>
        <li class="breadcrumb-item active">Detail Customer</li>
      </ol>
    </nav>
    <h1 class="page-title fw-medium fs-18 mb-0">
      <i class="fas fa-user-circle me-2"></i> <?= ucwords(strtolower($customer['name'])) ?>
    </h1>
  </div>
</div>

<div class="row">
  <!-- 👤 Profil Customer -->
  <div class="col-md-4">
    <div class="card custom-card shadow-sm">
      <div class="card-body text-center position-relative">
        <div class="avatar avatar-xxl bg-primary text-white mb-3">
          <?= strtoupper($customer['name'][0]) ?>
        </div>
        <h4 class="fw-semibold"><?= ucwords(strtolower($customer['name'])) ?></h4>
        <p class="text-muted mb-1"><?= $customer['phone_number'] ?></p>
        <div class="text-muted small"><?= $customer['city'] ?>, <?= $customer['province'] ?></div>

        <!-- ✅ Tombol Edit -->
        <a href="<?= base_url("customers/edit/{$customer['id']}") ?>" class="btn btn-sm btn-outline-primary mt-3">
          <i class="ti ti-edit"></i> Edit Profil
        </a>

        <hr>
        <ul class="list-group list-group-flush text-start">
          <li class="list-group-item">📦 Total Order: <strong><?= $customer['order_count'] ?></strong></li>
          <li class="list-group-item">💰 LTV: <strong>Rp <?= number_format($customer['ltv'], 0, ',', '.') ?></strong></li>
          <li class="list-group-item">📅 First Order: <strong><?= date('D, d M Y', strtotime($customer['first_order_date'])) ?></strong></li>
          <li class="list-group-item">⏱️ Last Order: <strong><?= date('D, d M Y', strtotime($customer['last_order_date'])) ?></strong></li>
          <li class="list-group-item">🔄 Rata-rata Jeda Order: <strong><?= $customer['average_days_between_orders'] ?? '-' ?> hari</strong></li>
          <li class="list-group-item">🏷️ Segment: <strong><?= $customer['segment'] ?? '-' ?></strong></li>
        </ul>
      </div>
    </div>
  </div>

  <!-- 🧾 Histori Transaksi -->
  <div class="col-md-8">
    <div class="card custom-card shadow-sm">
      <div class="card-body table-responsive">
        <h5 class="mb-3">📄 Riwayat Transaksi</h5>
        <table id="historyTable" class="table table-hover table-borderless w-100">
          <thead class="bg-light">
            <tr>
              <th>Tanggal</th>
              <th>Channel</th>
              <th>Produk</th>
              <th>Qty</th>
              <th>Total</th>
              <th>Kurir</th>
              <th>Resi</th>
            </tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });

function formatTanggalIndonesia(tgl) {
  if (!tgl) return '-';
  const hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  const d = new Date(tgl);
  return `${hari[d.getDay()]}, ${d.getDate().toString().padStart(2, '0')} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
}

$('#historyTable').DataTable({
  processing: true,
  serverSide: true,
  ajax: {
    url: "<?= base_url('customers/history/' . $customer['id']) ?>",
    type: "POST",
    data: { <?= csrf_token() ?>: "<?= csrf_hash() ?>" }
  },
  columns: [
    { data: 'date', render: formatTanggalIndonesia },
    { data: 'channel', render: d => d.toUpperCase() },
    { data: 'product_names' },
    { data: 'total_qty', className: 'text-center' },
    { data: 'total_payment', className: 'text-end', render: d => formatter.format(d || 0) },
    { data: 'courier_name' },
    { data: 'tracking_number' }
  ]
});
</script>
<?= $this->endSection() ?>
