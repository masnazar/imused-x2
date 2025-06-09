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
