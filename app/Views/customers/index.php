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
<!-- Start:: row-4 Statistik Customer -->
<div class="row">
  <div class="col-xxl-6 col-xl-12">
    <div class="card custom-card">
      <div class="card-header">
        <div class="card-title">
          Customer Growth Chart
        </div>
      </div>
      <div class="card-body">
        <div id="customerChart"></div> <!-- Placeholder ApexCharts -->
      </div>
    </div>
  </div>

  <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-12">
    <div class="card custom-card">
      <div class="card-header pb-0">
        <div class="card-title">Total Customer</div>
      </div>
      <div class="card-body p-3">
        <h3 id="stat-total-ui" class="mb-0 fw-semibold">0</h3>
        <p class="text-muted fs-12 mt-1">Jumlah keseluruhan customer aktif</p>
      </div>
    </div>
  </div>

  <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6 col-12">
    <div class="card custom-card">
      <div class="card-header pb-0">
        <div class="card-title">Total LTV</div>
      </div>
      <div class="card-body p-3">
        <h3 id="stat-ltv-ui" class="mb-0 fw-semibold">Rp 0</h3>
        <p class="text-muted fs-12 mt-1">Lifetime Value seluruh customer</p>
      </div>
    </div>
  </div>
</div>
<!-- End:: row-4 -->


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
<!-- Pastikan kamu sudah include ApexCharts di layout (misal di scripts.php) -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
  // Formatter Rupiah
  const formatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' });

  // Format tanggal ke Bahasa Indonesia
  function formatTanggalIndonesia(tgl) {
    if (!tgl) return '-';
    const hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const d     = new Date(tgl);
    return `${hari[d.getDay()]}, ${d.getDate().toString().padStart(2,'0')} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
  }

  // Global chart instance
  let customerChart;

  // Inisialisasi chart dengan data kosong
  function initCustomerChart() {
    const options = {
      chart: {
        type: 'area',
        height: 300,
        toolbar: { show: false }
      },
      series: [{
        name: 'Customer Baru',
        data: []            // nanti di-update
      }],
      xaxis: {
        categories: [],     // nanti di-update
        labels: { style: { fontSize: '12px' } }
      },
      yaxis: {
        labels: { formatter: val => Math.floor(val) }
      },
      dataLabels: { enabled: false },
      stroke: { curve: 'smooth', width: 2 },
      colors: ['#845ADF'],
      fill: {
        type: 'gradient',
        gradient: {
          shade: 'light',
          type: "vertical",
          shadeIntensity: 0.5,
          opacityFrom: 0.7,
          opacityTo: 0.2,
          stops: [0, 90, 100]
        }
      }
    };

    customerChart = new ApexCharts(
      document.querySelector("#customerChart"),
      options
    );
    customerChart.render();
  }

  $(function () {
    // Dynamic CSRF token setup
    $.ajaxSetup({
      headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Inisialisasi chart kosong
    initCustomerChart();

    // Inisialisasi DataTable
    const table = $('#customerTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: "<?= base_url('customers/data') ?>",
        type: "POST",
        data: function(d) {
          d.name         = $('#filter-nama').val();
          d.phone_number = $('#filter-wa').val();
          d.city         = $('#filter-kota').val();
          d.province     = $('#filter-provinsi').val();
        },
        dataSrc: function(json) {
          // Update CSRF token jika ada yang baru
          const newToken = json.csrfHash || $('meta[name="csrf-token"]').attr('content');
          $('meta[name="csrf-token"]').attr('content', newToken);

          // Update angka statistik
          $('#stat-total-ui').text(json.stats?.total_customers ?? 0);
          $('#stat-ltv-ui').text(formatter.format(json.stats?.total_ltv ?? 0));

          // Update chart jika data chart tersedia
          if (json.stats?.chart) {
            customerChart.updateOptions({
              xaxis: { categories: json.stats.chart.labels }
            });
            customerChart.updateSeries([{
              name: 'Customer Baru',
              data: json.stats.chart.series
            }]);
          }

          return json.data;
        },
        error: function(xhr, status, error) {
          console.error('AJAX Error:', status, error);
        }
      },
      columns: [
        {
          data: 'name',
          render: function(d) {
            if (!d) return '-';
            const cap = d.toLowerCase().replace(/\b\w/g, c => c.toUpperCase());
            return `
              <div class="d-flex align-items-center">
                <div class="avatar avatar-sm bg-primary text-white me-2">${cap.charAt(0)}</div>
                <span class="fw-semibold">${cap}</span>
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
          render: d => d
            ? `<span class="badge bg-info text-dark">${d}</span>`
            : '<span class="text-muted fst-italic">-</span>'
        },
        {
          data: null,
          orderable: false,
          render: row =>
            `<a href="<?= base_url('customers/detail/') ?>${row.id}" class="btn btn-sm btn-primary">Detail</a>`
        }
      ]
    });

    // Trigger filter
    $('#filter-nama, #filter-wa').on('keyup', () => table.draw());
    $('#filter-kota, #filter-provinsi').on('change', () => table.draw());

    // Load dropdown filter
    $.getJSON("<?= base_url('customers/filters') ?>", res => {
      res.cities?.forEach(city =>
        $('#filter-kota').append(`<option value="${city}">${city}</option>`)
      );
      res.provinces?.forEach(prov =>
        $('#filter-provinsi').append(`<option value="${prov}">${prov}</option>`)
      );
    });
  });
</script>


<?= $this->endSection() ?>
