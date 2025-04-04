<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Transaksi <?= ucfirst($platform) ?><?= $this->endSection() ?>
<?= $this->section('content') ?>

<!-- Breadcrumb -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
  <div>
    <nav>
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item active">Transaksi Marketplace</li>
      </ol>
    </nav>
    <h1 class="page-title fw-medium fs-18 mb-0">
      <i class="fas fa-shopping-bag me-2"></i> <?= ucfirst($platform) ?> Transactions
    </h1>
  </div>
</div>

<!-- Flash Message -->
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Filter Periode -->
<?= $date_filter ?>

<!-- Filter Brand -->
<div class="row mb-3">
  <div class="col-md-4">
    <label for="filter_brand" class="form-label">Brand</label>
    <select id="filter_brand" class="form-select">
      <option value="">-- Semua Brand --</option>
      <?php foreach ($brands as $brand): ?>
        <option value="<?= $brand['id'] ?>"><?= esc($brand['brand_name']) ?></option>
      <?php endforeach ?>
    </select>
  </div>
</div>

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

<!-- Tombol Import & Template -->
<div class="btn-list mb-4">
  <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal"><i class="ti ti-upload"></i> Import Excel</button>
  <a href="<?= base_url('marketplace-transactions/template/' . $platform) ?>" class="btn btn-secondary"><i class="ti ti-download"></i> Download Template</a>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="formImport" class="modal-content" enctype="multipart/form-data" method="POST">
      <?= csrf_field() ?>
      <div class="modal-header"><h5 class="modal-title">Import Excel Transaksi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body"><input type="file" name="file_excel" class="form-control" accept=".xlsx" required></div>
      <div class="modal-footer"><button type="submit" class="btn btn-success">Import</button></div>
    </form>
  </div>
</div>

<!-- Tabel -->
<div class="card custom-card shadow-sm">
  <div class="card-body table-responsive">
    <table id="transactionTable" class="table table-hover table-borderless mb-0">
      <thead class="bg-light">
        <tr>
          <th>Tanggal</th>
          <th>Order</th>
          <th>Tracking</th>
          <th>Brand</th>
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
const platform = "<?= $platform ?>";
const csrfName = '<?= csrf_token() ?>';
let csrfHash = '<?= csrf_hash() ?>';

const formatter = new Intl.NumberFormat('id-ID', {
  style: 'currency',
  currency: 'IDR'
});

// 🧠 Ambil elemen-elemen filter
const jenisFilter = document.getElementById('jenisFilter');
const periodeSelect = document.getElementById('periodeSelect');
const startDate = document.getElementById('startDate');
const endDate = document.getElementById('endDate');
const filterBrand = document.getElementById('filter_brand');

// 🧩 Fungsi sinkronisasi tanggal dari periode
function updateTanggalDariPeriode() {
  const selectedOption = document.querySelector('select[name="periode"] option:checked');
  if (!selectedOption) return;

  const start = selectedOption.getAttribute('data-start');
  const end = selectedOption.getAttribute('data-end');

  if (jenisFilter.value === 'periode' && start && end) {
    startDate.value = start;
    endDate.value = end;
  }

  // reset kalau bukan custom atau periode
  if (jenisFilter.value === 'semua') {
    startDate.value = '';
    endDate.value = '';
  }
}

console.log('🎯 Filter aktif:', {
  jenis_filter: jenisFilter.value,
  periode: periodeSelect.value,
  start_date: startDate.value,
  end_date: endDate.value
});

// 🧼 Reset tanggal kalau pilih Semua
function handleJenisFilterChange() {
  if (jenisFilter.value === 'custom') {
    startDate.value = '';
    endDate.value = '';
  } else if (jenisFilter.value === 'periode') {
    updateTanggalDariPeriode();
  } else {
    // Semua periode
    startDate.value = '';
    endDate.value = '';
    periodeSelect.value = '';
  }
}

// 📊 Ambil statistik
function fetchStatistics() {
  const data = {
    [csrfName]: csrfHash,
    brand_id: filterBrand.value,
    jenis_filter: jenisFilter.value,
    periode: periodeSelect.value,
    start_date: startDate.value,
    end_date: endDate.value
  };

  $.ajax({
    url: "<?= base_url('marketplace-transactions/get-statistics/' . $platform) ?>",
    type: "POST",
    headers: { [csrfName]: csrfHash },
    data: data,
    success: res => {
      if (res[csrfName]) csrfHash = res[csrfName];

      $('#stat_total_sales').text(res.total_sales || 0);
      $('#stat_total_omzet').text(formatter.format(res.total_omzet || 0));
      $('#stat_total_expenses').text(formatter.format(res.total_expenses || 0));
      $('#stat_gross_profit').text(formatter.format(res.gross_profit || 0));
    },
    error: xhr => {
      console.error('❌ Gagal fetch statistik:', xhr.responseText);
    }
  });
}

// 📦 Inisialisasi DataTables
const table = $('#transactionTable').DataTable({
  processing: true,
  serverSide: true,
  ajax: {
    url: "<?= base_url('marketplace-transactions/get-data/' . $platform) ?>",
    type: "POST",
    headers: { [csrfName]: csrfHash },
    data: d => {
      d[csrfName] = csrfHash;
      d.brand_id = filterBrand.value;
      d.jenis_filter = jenisFilter.value;
      d.periode = periodeSelect.value;
      d.start_date = startDate.value;
      d.end_date = endDate.value;
    },
    dataSrc: json => {
      if (json[csrfName]) csrfHash = json[csrfName];
      return json.data || [];
    },
    error: xhr => {
      console.error('❌ Error loading table:', xhr.responseText);
    }
  },
  columns: [
    {
      data: 'date',
      render: data => {
        if (!data) return '-';
        const d = new Date(data);
        const hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        return `${hari[d.getDay()]}, ${d.getDate()} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
      }
    },
    { data: 'order_number' },
    { data: 'tracking_number' },
    {
      data: 'brand_name',
      render: (data, type, row) =>
        `<span class="badge" style="background-color:${row.primary_color}">${data}</span>`
    },
    {
  data: 'products',
  className: 'align-middle',
  render: function(data, type, row, meta) {
    return data; // langsung return HTML
  }
},
{
  data: 'total_qty',
  className: 'text-end',
  render: function (data) {
    return `${parseInt(data).toLocaleString('id-ID')} pcs`;
  }
},
    { data: 'selling_price', render: d => formatter.format(d), className: 'text-end' },
    { data: 'hpp', render: d => formatter.format(d), className: 'text-end' },
    { data: 'discount', render: d => formatter.format(d), className: 'text-end' },
    { data: 'admin_fee', render: d => formatter.format(d), className: 'text-end' },
    {
      data: 'gross_profit',
      render: d => `<strong>${formatter.format(d)}</strong>`,
      className: 'text-end'
    },
    {
      data: 'status',
      render: d => {
        const color = {
          completed: 'success',
          pending: 'warning',
          canceled: 'danger'
        }[d] || 'secondary';
        return `<span class="badge bg-${color}">${d}</span>`;
      }
    }
  ]
});

// ⏱ Debounced handler untuk semua filter
function handleFilterChange() {
  updateTanggalDariPeriode();
  table.ajax.reload();
  fetchStatistics();
}

// 🚀 Init on DOM ready
document.addEventListener('DOMContentLoaded', () => {
  updateTanggalDariPeriode();
  fetchStatistics();

  jenisFilter.addEventListener('change', () => {
    handleJenisFilterChange();
    handleFilterChange();
  });

  periodeSelect.addEventListener('change', handleFilterChange);
  startDate.addEventListener('change', handleFilterChange);
  endDate.addEventListener('change', handleFilterChange);
  filterBrand.addEventListener('change', handleFilterChange);
});

$('#formImport').on('submit', function (e) {
  e.preventDefault(); // ⛔ Biar ga redirect form

  const formData = new FormData(this);
  formData.append(csrfName, csrfHash);

  $.ajax({
    url: "<?= base_url('marketplace-transactions/import/' . $platform) ?>",
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    beforeSend: () => {
      Swal.fire({
        title: 'Mengunggah...',
        html: 'Mohon tunggu sebentar ya, Laey!',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
      });
    },
    success: function (res) {
      if (res[csrfName]) csrfHash = res[csrfName];

      if (res.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: res.message
        }).then(() => {
          window.location.href = res.redirect;
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Gagal Import',
          html: res.message
        });
      }
    },
    error: function (xhr) {
      Swal.fire({
        icon: 'error',
        title: 'Gagal Import',
        text: 'Terjadi kesalahan saat mengunggah file.'
      });
    }
  });
});
</script>
<style>
  /* Untuk styling produk format */
.product-list .badge {
  font-family: monospace;
  min-width: 70px;
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
}

.product-list small {
  font-size: 0.8rem;
}
</style>

<?= $this->endSection() ?>
