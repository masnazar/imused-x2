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

<?= $this->include('layouts/components/flashMessage') ?>
<?= $date_filter ?>

<!-- Statistik -->
<div class="row row-cards mb-4">
  <?php foreach ([
    ['title' => 'Total Penjualan', 'id' => 'stat_total_sales', 'icon' => 'ti-shopping-cart', 'bg' => 'primary'],
    ['title' => 'Total Omzet', 'id' => 'stat_total_omzet', 'icon' => 'ti-currency-dollar', 'bg' => 'success'],
    ['title' => 'Total Biaya', 'id' => 'stat_total_expenses', 'icon' => 'ti-report-money', 'bg' => 'warning'],
    ['title' => 'Laba Kotor', 'id' => 'stat_gross_profit', 'icon' => 'ti-chart-line', 'bg' => 'info'],
  ] as $stat): ?>
    <div class="col-md-3">
      <div class="card custom-card">
        <div class="card-body d-flex gap-3 align-items-center">
          <div class="avatar bg-<?= $stat['bg'] ?>-transparent p-3 rounded-2">
            <i class="ti <?= $stat['icon'] ?> fs-20"></i>
          </div>
          <div>
            <div class="text-muted"><?= $stat['title'] ?></div>
            <h4 class="fw-semibold" id="<?= $stat['id'] ?>">Rp 0</h4>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach ?>
</div>


<!-- Import Excel -->
<div class="card custom-card shadow-sm">
  <div class="card-body table-responsive">
    <div class="btn-list mb-4">
      <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
        <i class="ti ti-upload"></i> Import Excel
      </button>
      <a href="<?= base_url('soscom-transactions/download-template') ?>" class="btn btn-secondary">
        <i class="ti ti-download"></i> Download Template
      </a>
    </div>

    <!-- Modal Import -->
    <div class="modal fade" id="importModal" tabindex="-1">
      <div class="modal-dialog">
        <form id="formImport" class="modal-content" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="modal-header">
            <h5 class="modal-title">Import Excel Transaksi Soscom</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="file" name="file_excel" class="form-control" accept=".xlsx" required>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success">Import</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Tabel Transaksi -->
    <table id="transactionTable" class="table table-hover table-borderless mb-0">
      <thead class="bg-light">
        <tr>
          <th>Tanggal</th>
          <th>Nomor Telepon</th>
          <th>Nama Customer</th>
          <th>Province</th>
          <th>Brand</th>
          <th>Produk</th>
          <th class="text-end">Qty</th>
          <th class="text-end">Harga Jual</th>
          <th class="text-end">HPP</th>
          <th class="text-end">Metode</th>
          <th class="text-end">COD Fee</th>
          <th class="text-end">Ongkir</th>
          <th class="text-end">Total</th>
          <th class="text-end">Estimasi Profit</th>
          <th>Tracking</th>
          <th>Kurir</th>
          <th>Team</th>
          <th>Channel</th>
          <th class="text-nowrap">Info & Petugas</th>
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


// 🗓️ Format Tanggal ke format: Senin, 06 Mei 2025
function formatTanggalIndonesia(tgl) {
  if (!tgl) return '-';
  const hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  const d = new Date(tgl);
  return `${hari[d.getDay()]}, ${d.getDate().toString().padStart(2, '0')} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
}

// 🔠 Capitalize tiap kata
const capitalize = s => s?.toLowerCase().replace(/\b\w/g, l => l.toUpperCase()) ?? '-';

// Init DataTables
const table = $('#transactionTable').DataTable({
  processing: true,
  serverSide: true,
  order: [[0, 'desc']],
  ajax: {
    url: "<?= base_url('soscom-transactions/get-data') ?>",
    type: "POST",
    headers: { [csrfName]: csrfHash },
    data: function(d) {
  d[csrfName] = csrfHash;
  return d;
},

    dataSrc: json => {
      if (json[csrfName]) csrfHash = json[csrfName];
      return json.data || [];
    },
    error: xhr => {
      console.error('❌ DataTables Error:', xhr.responseText);
      Swal.fire('Gagal!', 'Terjadi kesalahan saat memuat data.', 'error');
    }
  },
  columns: [
    { data: 'date', render: formatTanggalIndonesia },
    { data: 'phone_number' },
    { data: 'customer_name', render: capitalize },
    { data: 'province' },
    {
  data: 'brand_name',
  render: (data, type, row) =>
    `<span class="badge text-white fw-medium" style="background-color:${row.primary_color ?? '#666'}">${data}</span>`
},
{
  data: 'products',
  render: function (products, type, row) {
    if (!products) return '-';
    
    let parsed;
    try {
      parsed = typeof products === 'string' ? JSON.parse(products) : products;
    } catch (e) {
      return '-';
    }

    if (!Array.isArray(parsed) || parsed.length === 0) return '-';

    return parsed.map(p => {
      const sku = p.sku ?? '-';
      const nama = p.name ?? '-';
      const qty = parseInt(p.qty) || 0;
      const harga = parseInt(p.price) || 0;

      return `
        <div class='d-flex align-items-center mb-2'>
          <div class='flex-grow-1'>
            <div class='fw-medium'>${nama}</div>
            <small class='text-muted'>${qty} pcs × Rp ${harga.toLocaleString('id-ID')}</small>
          </div>
          <span class='badge bg-light text-muted border ms-2'>${sku}</span>
        </div>
      `;
    }).join('');
  }
},
    { data: 'total_qty', className: 'text-end', render: d => `${d} pcs` },
    { data: 'selling_price', className: 'text-end', render: formatter.format },
    { data: 'hpp', className: 'text-end', render: formatter.format },
    { data: 'payment_method', className: 'text-end' },
    { data: 'cod_fee', className: 'text-end', render: formatter.format },
    { data: 'shipping_cost', className: 'text-end', render: formatter.format },
    { data: 'total_payment', className: 'text-end', render: formatter.format },
    {
  data: 'estimated_profit',
  className: 'text-end',
  render: d => formatter.format(parseFloat(d) || 0)
},
    { data: 'tracking_number' },
    { data: 'courier_name' },
    {
  data: 'soscom_team_name',
  render: d => d ?? '<span class="text-muted fst-italic">-</span>'
},
{
  data: 'channel',
  render: function (d) {
    if (d === 'crm') {
      return `<span class="badge bg-purple">CRM</span>`;
    } else if (d === 'soscom') {
      return `<span class="badge bg-teal">Soscom</span>`;
    }
    return '-';
  }
},
    {
      data: null,
      className: 'text-nowrap',
      render: function (d) {
        return `
          <div style="font-size: 0.85em">
            <div>🟢 Diinput pada: <strong>${formatTanggalIndonesia(d.created_at)}</strong></div>
            <div>🔄 Diperbarui: <strong>${formatTanggalIndonesia(d.updated_at)}</strong></div>
            <div>👤 Oleh: <strong>${capitalize(d.processed_by)}</strong></div>
          </div>`;
      }
    }
  ]
});

// Statistik on table load
table.on('xhr.dt', fetchStatistics);

// Fetch statistik
function fetchStatistics() {
  $.post("<?= base_url('soscom-transactions/get-statistics') ?>", {
    [csrfName]: csrfHash
  }, function (res) {
    if (res[csrfName]) csrfHash = res[csrfName];
    $('#stat_total_sales').text(res.total_sales || 0);
    $('#stat_total_omzet').text(formatter.format(res.total_omzet || 0));
    $('#stat_total_expenses').text(formatter.format(res.total_hpp || 0));
    $('#stat_gross_profit').text(formatter.format(res.total_profit || 0));
  });
}

// Import Excel
$('#formImport').on('submit', function (e) {
  e.preventDefault();

  const formData = new FormData(this);
  formData.append(csrfName, csrfHash); // ✅ Sisipkan token CSRF manual

  $.ajax({
    url: "<?= base_url('soscom-transactions/import') ?>",
    method: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    success(res) {
      if (res[csrfName]) csrfHash = res[csrfName]; // ✅ Refresh token dari response
      if (res.status === 'success') {
        window.location.href = res.redirect;
      } else {
        Swal.fire('Gagal!', res.message || 'Terjadi kesalahan.', 'error');
      }
    },
    error(err) {
      console.error('❌ Import Error:', err);
      Swal.fire('Error', 'Gagal memproses file.', 'error');
    }
  });
});
</script>
<?= $this->endSection() ?>
