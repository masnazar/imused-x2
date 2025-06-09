<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Transaksi Soscom<?= $this->endSection() ?>
<?= $this->section('content') ?>

<!-- Breadcrumb -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
  <div>
    <nav>
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item active">Transaksi Soscom</li>
      </ol>
    </nav>
    <h1 class="page-title fw-medium fs-18 mb-0">
      <i class="fas fa-users me-2"></i> Soscom Transactions
    </h1>
  </div>
</div>

<!-- Flash Message -->
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Filter Periode -->
<?= $date_filter ?>

<!-- Statistik -->
<div class="row row-cards mb-4">
  <div class="col-md-3">
    <div class="card custom-card">
      <div class="card-body d-flex gap-3 align-items-center">
        <div class="avatar bg-primary-transparent p-3 rounded-2"><i class="ti ti-shopping-cart fs-20"></i></div>
        <div>
          <div class="text-muted">Total Penjualan</div>
          <h4 class="fw-semibold" id="stat_total_sales">0</h4>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card custom-card">
      <div class="card-body d-flex gap-3 align-items-center">
        <div class="avatar bg-success-transparent p-3 rounded-2"><i class="ti ti-currency-dollar fs-20"></i></div>
        <div>
          <div class="text-muted">Total Omzet</div>
          <h4 class="fw-semibold" id="stat_total_omzet">Rp 0</h4>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card custom-card">
      <div class="card-body d-flex gap-3 align-items-center">
        <div class="avatar bg-warning-transparent p-3 rounded-2"><i class="ti ti-report-money fs-20"></i></div>
        <div>
          <div class="text-muted">Total Biaya</div>
          <h4 class="fw-semibold" id="stat_total_expenses">Rp 0</h4>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card custom-card">
      <div class="card-body d-flex gap-3 align-items-center">
        <div class="avatar bg-info-transparent p-3 rounded-2"><i class="ti ti-chart-line fs-20"></i></div>
        <div>
          <div class="text-muted">Laba Kotor</div>
          <h4 class="fw-semibold" id="stat_gross_profit">Rp 0</h4>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Tabel -->
<div class="card custom-card shadow-sm">
  <div class="card-body table-responsive">

<!-- Tombol Import & Template -->
<div class="btn-list mb-4">
  <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal"><i class="ti ti-upload"></i> Import Excel</button>
  <a href="<?= base_url('soscom-transactions/download-template') ?>" class="btn btn-secondary"><i class="ti ti-download"></i> Download Template</a>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
  <div class="modal-dialog">
  <form id="formImport" class="modal-content" enctype="multipart/form-data">
    <?= csrf_field() ?>
      <div class="modal-header"><h5 class="modal-title">Import Excel Transaksi Soscom</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body"><input type="file" name="file_excel" class="form-control" accept=".xlsx" required></div>
      <div class="modal-footer"><button type="submit" class="btn btn-success">Import</button></div>
    </form>
  </div>
</div>

<table id="transactionTable" class="table table-hover table-borderless mb-0">
<thead class="bg-light">
<tr>
<th>Tanggal</th>
<th>Order</th>
<th>Tracking</th>
<th>Customer</th>
<th>Produk</th>
<th class="text-end">Qty</th>
<th class="text-end">Harga Jual</th>
<th class="text-end">HPP</th>
<th class="text-end">Diskon</th>
<th class="text-end">Fee Admin</th>
<th class="text-end">Laba</th>
<th>Status</th>
</tr>
</thead>
</table>

  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const csrfName = '<?= csrf_token() ?>';
let csrfHash = '<?= csrf_hash() ?>';
const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });

const table = $('#transactionTable').DataTable({
  processing: true,
  serverSide: true,
  ajax: {
    url: "<?= base_url('soscom-transactions/get-data') ?>",
    type: "POST",
    headers: { [csrfName]: csrfHash },
    data: function (d) {
      d[csrfName] = csrfHash;
    },
    dataSrc: function (json) {
      if (json[csrfName]) csrfHash = json[csrfName];
      return json.data || [];
    },
    error: function (xhr) {
      console.error('❌ Error loading DataTables:', xhr.responseText);
    }
  },
  columns: [
    { data: 'date', className: 'align-middle' },
    { data: 'order_number' },
    { data: 'tracking_number' },
    { data: 'customer_name' },
    { data: 'products' },
    { data: 'total_qty', render: d => `${parseInt(d).toLocaleString('id-ID')} pcs`, className: 'text-end' },
    { data: 'selling_price', render: formatter.format, className: 'text-end' },
    { data: 'hpp', render: formatter.format, className: 'text-end' },
    { data: 'discount', render: formatter.format, className: 'text-end' },
    { data: 'admin_fee', render: formatter.format, className: 'text-end' },
    { data: 'gross_profit', render: d => `<strong>${formatter.format(d)}</strong>`, className: 'text-end' },
    { data: 'status', render: d => `<span class="badge bg-${d === 'Processed' ? 'success' : 'secondary'}">${d}</span>` }
  ]
});

$('#formImport').on('submit', function (e) {
  e.preventDefault();
  const formData = new FormData(this);
  $.ajax({
    url: "<?= base_url('soscom-transactions/import') ?>",
    method: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    success(res) {
      if (res.status === 'success') {
        window.location.href = res.redirect;
      } else {
        Swal.fire('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
      }
    },
    error(err) {
      console.error(err);
      Swal.fire('Error', 'Gagal memproses file.', 'error');
    }
  });
});
</script>
<?= $this->endSection() ?>
