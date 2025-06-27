<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Data Customer<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
  <div>
    <nav>
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item active">Data Customer</li>
      </ol>
    </nav>
    <h1 class="page-title fw-medium fs-18 mb-0">
      <i class="fas fa-users me-2"></i> Data Customer
    </h1>
  </div>
</div>



<?= $this->include('layouts/components/flashMessage') ?>

<!-- Statistik -->
<div class="row mb-3">
  <div class="col-md-4">
    <div class="card bg-primary text-white p-3 rounded shadow-sm">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h6>Total Customer</h6>
          <h3 class="mb-0" id="stat-total">0</h3>
        </div>
        <i class="fas fa-users fa-2x"></i>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card bg-success text-white p-3 rounded shadow-sm">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h6>Total LTV</h6>
          <h3 class="mb-0" id="stat-ltv">Rp 0</h3>
        </div>
        <i class="fas fa-coins fa-2x"></i>
      </div>
    </div>
  </div>
</div>

<!-- Filter -->
<div class="card mb-3">
  <div class="card-body row g-2 align-items-end">
    <div class="col-md-3">
      <label for="filter-nama">Nama</label>
      <input type="text" id="filter-nama" class="form-control" placeholder="Cari Nama...">
    </div>
    <div class="col-md-3">
      <label for="filter-wa">Nomor WA</label>
      <input type="text" id="filter-wa" class="form-control" placeholder="08xxxx...">
    </div>
    <div class="col-md-3">
      <label for="filter-kota">Kota/Kab</label>
      <select id="filter-kota" class="form-select">
        <option value="">Semua</option>
      </select>
    </div>
    <div class="col-md-3">
      <label for="filter-provinsi">Provinsi</label>
      <select id="filter-provinsi" class="form-select">
        <option value="">Semua</option>
      </select>
    </div>
  </div>
</div>


<div class="card custom-card">
  <div class="card-body table-responsive">
    <table id="customerTable" class="table table-hover table-borderless mb-0">
      <thead class="bg-light">
        <tr>
          <th>Nama</th>
          <th>Nomor WA</th>
          <th>Kota/Kab</th>
          <th>Provinsi</th>
          <th>Total Order</th>
          <th>LTV</th>
          <th>First Order</th>
          <th>Last Order</th>
          <th>Segment</th>
          <th></th>
        </tr>
      </thead>
    </table>
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

$(function () {
  $('#customerTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "<?= base_url('customers/data') ?>",
      type: "POST",
      data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' }
    },
    columns: [
      {
  data: 'name',
  render: function (d, t, r) {
    if (!d) return '-';
    const nameCapitalized = d.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
    return `
      <div class="d-flex align-items-center">
        <div class="avatar avatar-sm bg-primary text-white me-2">${nameCapitalized.charAt(0)}</div>
        <span class="fw-semibold">${nameCapitalized}</span>
      </div>`;
  }
},
      { data: 'phone_number' },
      { data: 'city' },
      { data: 'province' },
      { data: 'order_count', className: 'text-center' },
      { data: 'ltv', className: 'text-end', render: d => formatter.format(d || 0) },
      { data: 'first_order_date', render: formatTanggalIndonesia },
      { data: 'last_order_date', render: formatTanggalIndonesia },
      {
        data: 'segment',
        render: d => d ? `<span class="badge bg-info text-dark">${d}</span>` : '<span class="text-muted fst-italic">-</span>'
      },
      {
        data: null,
        orderable: false,
        render: row => `<a href="<?= base_url('customers/detail/') ?>${row.id}" class="btn btn-sm btn-primary">Detail</a>`
      }
    ]
  });
});
</script>
<?= $this->endSection() ?>
