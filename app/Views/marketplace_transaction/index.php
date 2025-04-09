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
<!-- Filter Brand End -->
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
<!-- Filter Brand End -->

<!-- Tombol Import & Template -->
<div class="btn-list mb-4">
  <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal"><i class="ti ti-upload"></i> Import Excel</button>
  <a href="<?= base_url('marketplace-transactions/template/' . $platform) ?>" class="btn btn-secondary"><i class="ti ti-download"></i> Download Template</a>
</div>

<!-- Modal Import -->
<div class="modal fade" id="importModal" tabindex="-1">
  <div class="modal-dialog">
  <form id="formImport" class="modal-content" enctype="multipart/form-data">
    <?= csrf_field() ?>
      <div class="modal-header"><h5 class="modal-title">Import Excel Transaksi</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
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
const FILTER_STORAGE_KEY = `mp_filter_${platform}`;
const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });

// 🧩 DOM Refs
const jenisFilter = document.getElementById('jenisFilter');
const periodeSelect = document.querySelector('select[name="periode"]');
const startDate = document.querySelector('input[name="start_date"]');
const endDate = document.querySelector('input[name="end_date"]');
const filterBrand = document.getElementById('filter_brand');

// 🚀 Init saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', function () {
  // 🔁 Load filter dari localStorage
  loadFilterFromLocalStorage();

  // 🔄 Pasang event listener ke semua filter
  $('#jenisFilter, select[name="periode"], input[name="start_date"], input[name="end_date"], #filter_brand')
    .on('change', function () {
      reloadTableAndStats();
    });
});

// 📦 DataTables Init
const table = $('#transactionTable').DataTable({
  processing: true,
  serverSide: true,
  ajax: {
    url: "<?= base_url('marketplace-transactions/get-data/' . $platform) ?>",
    type: "POST",
    headers: { [csrfName]: csrfHash },
    data: function (d) {
      d[csrfName] = csrfHash;
      d.jenis_filter = jenisFilter?.value;
      d.periode = periodeSelect?.value;
      d.start_date = startDate?.value;
      d.end_date = endDate?.value;
      d.brand_id = filterBrand?.value;
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
    {
      data: 'date',
      className: 'align-middle',
      render: data => {
        if (!data) return '-';
        const d = new Date(data);
        const hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        return `${hari[d.getDay()]}, ${d.getDate()} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
      }
    },
    {
      data: 'order_number',
      render: (data, type, row) => `<a href="<?= base_url('marketplace-transactions/detail') ?>/${row.platform}/${row.id}" class="fw-medium text-primary text-decoration-underline">${data}</a>`
    },
    {
      data: 'tracking_number',
      render: (data, type, row) => {
        if (!data) return '-';
        return `<a href="javascript:void(0)" onclick="showTrackingModal('${row.courier_code}', '${data}')">
                  <span class="badge bg-info text-white">${data}</span>
                </a>`;
      }
    },
    {
      data: 'brand_name',
      render: (data, type, row) =>
        `<span class="badge text-white fw-medium" style="background-color:${row.primary_color ?? '#666'}">${data}</span>`
    },
    { data: 'products' },
    { data: 'total_qty', render: d => `${parseInt(d).toLocaleString('id-ID')} pcs`, className: 'text-end' },
    { data: 'selling_price', render: formatter.format, className: 'text-end' },
    { data: 'hpp', render: formatter.format, className: 'text-end' },
    { data: 'discount', render: formatter.format, className: 'text-end' },
    { data: 'admin_fee', render: formatter.format, className: 'text-end' },
    { data: 'gross_profit', render: d => `<strong>${formatter.format(d)}</strong>`, className: 'text-end' },
    {
  data: 'status',
  render: function (d) {
    if (!d) return '<span class="badge bg-secondary">-</span>';
    
    const map = {
      terkirim: 'success',
      processed: 'primary',
      returned: 'danger',
      pending: 'warning',
      'dalam perjalanan': 'info'
    };

    const color = map[d.toLowerCase()] ?? 'secondary';
    return `<span class="badge bg-${color} text-capitalize">${d}</span>`;
  }
}
  ]
});

// 🔁 Reload Table + Statistik
function reloadTableAndStats() {
  saveFilterToLocalStorage();
  table.ajax.reload();
  fetchStatistics();
}

// 📊 Fetch Statistik
function fetchStatistics() {
  $.post("<?= base_url('marketplace-transactions/get-statistics/' . $platform) ?>", {
    [csrfName]: csrfHash,
    jenis_filter: jenisFilter?.value,
    periode: periodeSelect?.value,
    start_date: startDate?.value,
    end_date: endDate?.value,
    brand_id: filterBrand?.value
  }, function (res) {
    if (res[csrfName]) csrfHash = res[csrfName];

    $('#stat_total_sales').text(res.total_sales || 0);
    $('#stat_total_omzet').text(formatter.format(res.total_omzet || 0));
    $('#stat_total_expenses').text(formatter.format(res.total_expenses || 0));
    $('#stat_gross_profit').text(formatter.format(res.gross_profit || 0));
  });
}

// 💾 Simpan ke localStorage
function saveFilterToLocalStorage() {
  const filterData = {
    jenis_filter: jenisFilter?.value,
    periode: periodeSelect?.value,
    start_date: startDate?.value,
    end_date: endDate?.value,
    brand_id: filterBrand?.value,
    timestamp: new Date().getTime()
  };
  localStorage.setItem(FILTER_STORAGE_KEY, JSON.stringify(filterData));
}

// 📥 Load dari localStorage
function loadFilterFromLocalStorage() {
  const saved = JSON.parse(localStorage.getItem(FILTER_STORAGE_KEY));
  if (!saved) return;

  const age = new Date().getTime() - saved.timestamp;
  const oneYear = 365 * 24 * 60 * 60 * 1000;

  if (age > oneYear) {
    localStorage.removeItem(FILTER_STORAGE_KEY); // expired
    return;
  }

  if (saved.jenis_filter) jenisFilter.value = saved.jenis_filter;
  if (saved.periode && saved.jenis_filter === 'periode') periodeSelect.value = saved.periode;
  if (saved.start_date && saved.end_date && saved.jenis_filter === 'custom') {
    startDate.value = saved.start_date;
    endDate.value = saved.end_date;
  }
  if (saved.brand_id) filterBrand.value = saved.brand_id;

  // ✅ Trigger reload otomatis
  reloadTableAndStats();
}

function showTrackingModal(courier, tracking) {
  if (!courier || !tracking) return;

  $('#resiNumberPlaceholder').text(tracking); // ⬅️ Ini juga biar placeholder update
  $('#modalTracking .modal-body').html(`
    <div class="text-muted"><i class="ri-loader-4-line spin me-2"></i> Mengambil data tracking...</div>
  `);
  $('#modalTracking').modal('show');

  $.post("<?= base_url('marketplace-transactions/track-resi') ?>", {
    courier: courier,
    awb: tracking,
    <?= csrf_token() ?>: csrfHash
  }, function (res) {
    if (res[csrfName]) csrfHash = res[csrfName];

    if (res.status === 'success') {
      const summary = res.data.summary;
      const history = res.data.history ?? [];

      let timeline = history.map(item => `
        <li class="mb-2">
          <strong>${item.date}</strong><br>
          ${item.desc}
        </li>
      `).join('');

      $('#modalTracking .modal-body').html(`
        <h5>Status: ${summary.status}</h5>
        <p>
          <strong>Kurir:</strong> ${summary.courier}<br>
          <strong>Resi:</strong> ${summary.awb}
        </p>
        <hr>
        <ul class="ps-3">${timeline}</ul>
      `);
    } else {
      $('#modalTracking .modal-body').html(`<div class="text-danger">❌ ${res.message || 'Resi tidak ditemukan.'}</div>`);
    }
  }).fail(function () {
    $('#modalTracking .modal-body').html(`<div class="text-danger">❌ Gagal mengambil data tracking.</div>`);
  });
}

$('#formImport').on('submit', function (e) {
  e.preventDefault();

  const formData = new FormData(this);
  const url = "<?= base_url('marketplace-transactions/import/' . $platform) ?>";

  $.ajax({
    url,
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

.spin {
    animation: spin 1s linear infinite;
  }
  @keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
</style>

<!-- Modal Timeline Tracking -->
<div class="modal fade" id="modalTracking" tabindex="-1" aria-labelledby="modalTrackingLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="ri-map-pin-time-line me-2"></i> 
                    <div>
                        Perjalanan Paket
                        <div class="fs-12 text-muted fw-normal">
                            Nomor Resi: <span id="resiNumberPlaceholder">-</span>
                        </div>
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
