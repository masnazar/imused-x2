<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Semua Transaksi Marketplace<?= $this->endSection() ?>
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
      <i class="fas fa-shopping-bag me-2"></i> Semua Transaksi Marketplace
    </h1>
  </div>
</div>

<!-- Flash Message -->
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Filter Periode -->
<?= $date_filter ?>

<!-- Statistik -->
<div class="row row-cards mb-4">
  <?php
    $statCards = [
      ['title' => 'Total Penjualan', 'id' => 'stat_total_sales', 'icon' => 'ti-shopping-cart', 'color' => 'primary'],
      ['title' => 'Total Omzet', 'id' => 'stat_total_omzet', 'icon' => 'ti-currency-dollar', 'color' => 'success'],
      ['title' => 'Total Biaya', 'id' => 'stat_total_expenses', 'icon' => 'ti-report-money', 'color' => 'warning'],
      ['title' => 'Laba Kotor', 'id' => 'stat_gross_profit', 'icon' => 'ti-chart-line', 'color' => 'info'],
    ];
  ?>
  <?php foreach ($statCards as $card): ?>
    <div class="col-md-3">
      <div class="card custom-card">
        <div class="card-body d-flex gap-3 align-items-center">
          <div class="avatar bg-<?= $card['color'] ?>-transparent p-3 rounded-2">
            <i class="ti <?= $card['icon'] ?> fs-20"></i>
          </div>
          <div>
            <div class="text-muted"><?= $card['title'] ?></div>
            <h4 class="fw-semibold" id="<?= $card['id'] ?>">Rp 0</h4>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach ?>
</div>

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
          <th>Diproses Oleh</th>
          <th>Created / Updated</th>
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
  const FILTER_STORAGE_KEY = `mp_filter_all`;
  const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });

  const jenisFilter = document.getElementById('jenisFilter');
  const periodeSelect = document.querySelector('select[name="periode"]');
  const startDate = document.querySelector('input[name="start_date"]');
  const endDate = document.querySelector('input[name="end_date"]');
  const filterBrand = document.getElementById('filter_brand');

  $(function () {
    loadFilterFromLocalStorage();
    $('#jenisFilter, select[name="periode"], input[name="start_date"], input[name="end_date"], #filter_brand').on('change', reloadTableAndStats);
  });

  const table = $('#transactionTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "<?= base_url('marketplace-transactions/get-data/all') ?>",
      type: "POST",
      headers: { [csrfName]: csrfHash },
      data: d => {
        d[csrfName] = csrfHash;
        d.jenis_filter = jenisFilter?.value;
        d.periode = periodeSelect?.value;
        d.start_date = startDate?.value;
        d.end_date = endDate?.value;
        d.brand_id = filterBrand?.value;
      },
      dataSrc: json => {
        if (json[csrfName]) csrfHash = json[csrfName];
        return json.data || [];
      }
    },
    columns: [
      { data: 'date' },
      { data: 'order_number' },
      { data: 'tracking_number' },
      { data: 'brand_name' },
      { data: 'products' },
      { data: 'total_qty', className: 'text-end' },
      { data: 'selling_price', render: formatter.format, className: 'text-end' },
      { data: 'hpp', render: formatter.format, className: 'text-end' },
      { data: 'discount', render: formatter.format, className: 'text-end' },
      { data: 'admin_fee', render: formatter.format, className: 'text-end' },
      { data: 'gross_profit', render: d => `<strong>${formatter.format(d)}</strong>`, className: 'text-end' },
      { data: 'processed_by' },
      { data: 'created_at' },
      { data: 'status' }
    ]
  });

  function reloadTableAndStats() {
    saveFilterToLocalStorage();
    table.ajax.reload();
    fetchStatistics();
  }

  function fetchStatistics() {
    $.post("<?= base_url('marketplace-transactions/get-statistics/all') ?>", {
      [csrfName]: csrfHash,
      jenis_filter: jenisFilter?.value,
      periode: periodeSelect?.value,
      start_date: startDate?.value,
      end_date: endDate?.value,
      brand_id: filterBrand?.value
    }, res => {
      if (res[csrfName]) csrfHash = res[csrfName];
      $('#stat_total_sales').text(res.total_sales || 0);
      $('#stat_total_omzet').text(formatter.format(res.total_omzet || 0));
      $('#stat_total_expenses').text(formatter.format(res.total_expenses || 0));
      $('#stat_gross_profit').text(formatter.format(res.gross_profit || 0));
    });
  }

  function saveFilterToLocalStorage() {
    const data = {
      jenis_filter: jenisFilter?.value,
      periode: periodeSelect?.value,
      start_date: startDate?.value,
      end_date: endDate?.value,
      brand_id: filterBrand?.value,
      timestamp: new Date().getTime()
    };
    localStorage.setItem(FILTER_STORAGE_KEY, JSON.stringify(data));
  }

  function loadFilterFromLocalStorage() {
    const saved = JSON.parse(localStorage.getItem(FILTER_STORAGE_KEY));
    if (!saved) return;
    if (new Date().getTime() - saved.timestamp > 31536000000) return;

    jenisFilter.value = saved.jenis_filter;
    periodeSelect.value = saved.periode;
    startDate.value = saved.start_date;
    endDate.value = saved.end_date;
    filterBrand.value = saved.brand_id;
  }
</script>
<?= $this->endSection() ?>
