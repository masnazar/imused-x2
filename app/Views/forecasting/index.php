<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Forecasting Stok<?= $this->endSection() ?>
<?= $this->section('content') ?>

<!-- Start:: Breadcrumbs & Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('warehouse') ?>">Forecast</a></li>
                <li class="breadcrumb-item active" aria-current="page">Forecast Engine</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">
            📦 Forecasting Stok Produk
        </h1>
    </div>
</div>
<!-- End:: Breadcrumbs & Page Header -->

<!-- Flash -->
<?= $this->include('layouts/components/flashMessage') ?>

<?= $this->include('partials/forecast_filter') ?>

<!-- Tabel Forecast -->
<div class="card">
  <div class="card-body table-responsive">
  <table class="table table-bordered align-middle text-center" id="forecastTable">
  <thead class="table-light">
    <tr>
      <th>SKU</th>
      <th>Nama Produk</th>
      <th>Stok Saat Ini</th>
      <th>Re-Order Point</th>
      <th>Total Sales</th>
      <th>Avg Daily</th>
      <th>Forecast</th>
      <th>Min</th>
      <th>Max</th>
      <th>Rekomendasi PO</th>
      <th>Status</th>
      <th></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($products as $p): ?>
      <tr data-product-id="<?= $p['id'] ?>" data-safety="<?= $p['safety_stock'] ?>">
        <td><?= esc($p['sku']) ?></td>
        <td class="text-start"><?= esc($p['nama_produk']) ?></td>
        <td><?= number_format($p['stock'], 0, ',', '.') ?></td>
        <td class="rop-col">-</td>
        <td class="total-sales-col">-</td>
        <td class="avg-col">-</td>
        <td class="forecast-col fw-bold">-</td>
        <td class="min-col">-</td>
        <td class="max-col">-</td>
        <td class="recommended-col fw-bold text-success">-</td>
        <td class="status-col">-</td>
        <td>
          <button class="btn btn-sm btn-primary btn-forecast">
            <i class="ti ti-sparkles"></i> Forecast
          </button>
        </td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>

<!-- 🔁 Forecast Loading Overlay -->
<div id="forecastLoader" style="display:none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.8); z-index:9999; display:flex; align-items:center; justify-content:center; flex-direction:column;">
  <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
    <span class="visually-hidden">Loading...</span>
  </div>
  <div class="mt-3 text-primary fw-bold fs-5">🔮 Forecast Engine Sedang Berjalan...</div>
</div>

  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
const csrfName = '<?= csrf_token() ?>';
let csrfHash = '<?= csrf_hash() ?>';

// ⏳ Forecast semua produk
$('#btnForecastAll').click(function () {
  showForecastLoader();

  const rows = $('#forecastTable tbody tr');
  let completed = 0;

  rows.each(function () {
    const row = $(this);
    const productId = row.data('product-id');

    forecastProduct(row, productId, () => {
      completed++;
      if (completed === rows.length) {
        hideForecastLoader();
      }
    });
  });
});

// 🔮 Forecast 1 produk
$(document).on('click', '.btn-forecast', function () {
  const row = $(this).closest('tr');
  const productId = row.data('product-id');

  showForecastLoader();
  forecastProduct(row, productId, hideForecastLoader); // callback ditaruh di dalam AJAX success
});

// 🔢 Ambil jumlah hari dari dropdown forecast
function getForecastDays() {
  const mode = $('#forecast_mode').val();
  if (mode === 'custom') {
    const end = new Date($('#forecast_custom_end').val());
    const now = new Date();
    const diff = Math.ceil((end - now) / (1000 * 60 * 60 * 24));
    return diff > 0 ? diff : 1;
  }
  return parseInt(mode);
}

// 🌪 Fungsi utama
function forecastProduct(row, productId, callback = () => {}) {
  $.ajax({
    url: "<?= base_url('forecast/predict-single') ?>",
    type: 'POST',
    data: {
      [csrfName]: csrfHash,
      product_id: productId,
      periode: $('#periode').val(),
      forecast_days: getForecastDays(),
      recommendation_mode: $('#recommendation_mode').val(),
      multiplier: $('#multiplier').val()
    },
    success: function (res) {
      if (res[csrfName]) csrfHash = res[csrfName];

      if (res.error) {
        row.find('.status-col').html(`<span class="badge bg-danger">Error</span>`);
        console.warn(res.error);
        return callback();
      }

      row.find('.forecast-col').text(formatNumber(res.forecast_demand));
      row.find('.avg-col').text(formatNumber(res.avg_daily_sales));
      row.find('.total-sales-col').text(formatNumber(res.total_sales));
      row.find('.recommended-col').text(formatNumber(res.recommended_restock));
      row.find('.rop-col').text(formatNumber(res.rop));
      row.find('.min-col').text(formatNumber(res.min_stock));
      row.find('.max-col').text(formatNumber(res.max_stock));

      let badgeClass = 'secondary';
      if (res.status === 'Understock') badgeClass = 'danger';
      else if (res.status === 'Overstock') badgeClass = 'warning';
      else if (res.status === 'Reorder Needed') badgeClass = 'info';
      else if (res.status === 'Ideal') badgeClass = 'success';

      row.find('.status-col').html(`<span class="badge bg-${badgeClass}">${res.status}</span>`);
      callback();
    },
    error: function (xhr) {
      console.error(xhr.responseText);
      row.find('.status-col').html(`<span class="badge bg-danger">Error</span>`);
      callback();
    }
  });
}

// 🔢 Format angka ribuan
function numberFormat(num) {
  return num?.toLocaleString('id-ID', { minimumFractionDigits: 0 }) ?? '-';
}

function formatNumber(n) {
  return n?.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) ?? '-';
}

// 🔁 Loader
function showForecastLoader() {
  $('#forecastLoader').fadeIn(150).addClass('show');
}

function hideForecastLoader() {
  $('#forecastLoader').fadeOut(200).removeClass('show');
}

// 🔄 Auto-hide loader kalau reload
$(document).ready(function () {
  hideForecastLoader();
});
</script>
<?= $this->endSection() ?>
