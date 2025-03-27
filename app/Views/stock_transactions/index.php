<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header-breadcrumb d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <nav>
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                <li class="breadcrumb-item active">Stock Transactions</li>
            </ol>
        </nav>
        <h1 class="page-title fw-semibold fs-18 mb-0">📦 Stock Transactions</h1>
    </div>
</div>

<div class="card custom-card">
    <div class="card-body">
        <!-- Filter -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <label>Gudang</label>
                <select class="form-select" id="filter_warehouse">
                    <option value="">-- Semua Gudang --</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= $w['id'] ?>"><?= esc($w['name']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-md-3">
                <label>Jenis Transaksi</label>
                <select class="form-select" id="filter_type">
                    <option value="">-- Semua --</option>
                    <option value="Inbound">Inbound</option>
                    <option value="Outbound">Outbound</option>
                    <option value="Transfer">Transfer</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Tanggal</label>
                <input type="date" class="form-control" id="filter_start">
            </div>
            <div class="col-md-3">
                <label>s.d</label>
                <input type="date" class="form-control" id="filter_end">
            </div>
        </div>

        <!-- Statistik -->
        <div class="row mb-4" id="statistikWrapper">
            <div class="col-md-6 col-xl-3">
                <div class="card custom-card">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <div class="avatar avatar-rounded bg-success text-white">
                            <i class="ti ti-box fs-20"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted">Total Inbound</p>
                            <h5 class="mb-0" id="stat_total_inbound">0</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card custom-card">
                    <div class="card-body d-flex gap-3 align-items-center">
                        <div class="avatar avatar-rounded bg-danger text-white">
                            <i class="ti ti-box fs-20"></i>
                        </div>
                        <div>
                            <p class="mb-0 text-muted">Total Outbound</p>
                            <h5 class="mb-0" id="stat_total_outbound">0</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grafik ApexChart -->

        <div class="d-flex justify-content-end mb-2">
        <button class="btn btn-danger btn-sm" id="btnExportChart">
                <i class="ti ti-download"></i> Export PDF
            </button>
        </div>
        <div id="chartExportWrapper">
    <div class="card custom-card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0">📊 Grafik Transaksi Produk per Tanggal</h6>
        </div>
        <div class="card-body">
            <div id="apexChartProduct"></div>
        </div>
    </div>
</div>


        <!-- Table -->
        <table class="table table-bordered" id="stockTable">
            <thead>
                <tr>
                    <th>Gudang</th>
                    <th>Produk</th>
                    <th>Jenis</th>
                    <th>Qty</th>
                    <th>Status</th>
                    <th>Source</th>
                    <th>Gudang Tujuan</th>
                    <th>Waktu</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script>
$(document).ready(function () {
    let csrfName = '<?= csrf_token() ?>';
    let csrfHash = '<?= csrf_hash() ?>';
    let chart;

    function formatTanggalChart(dateString) {
    if (!dateString || typeof dateString !== 'string') {
        console.warn('Invalid date input:', dateString);
        return 'Tanggal Tidak Valid';
    }

    const cleanDate = dateString.split('T')[0];
    const [tahun, bulanRaw, tanggalRaw] = cleanDate.split('-');

    const bulan = parseInt(bulanRaw); // ← Biar aman walau bulan "03" jadi 3
    const tanggal = parseInt(tanggalRaw);

    const namaBulan = [
        'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
        'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'
    ];

    return `${tanggal} ${namaBulan[bulan - 1] || '???'} ${tahun}`;
}


    function renderApexChart(dates, inboundData, outboundData) {
        // Handle empty data
        if (!dates || dates.length === 0) {
            $("#apexChartProduct").html('<div class="text-center text-muted py-4">📭 Tidak ada data</div>');
            if (chart) chart.destroy();
            return;
        }

        const options = {
            chart: {
                type: 'area',
                height: 300,
                toolbar: { show: true,
                    offsetX: 0,
                    offsetY: 0,
                    tools: {
          download: true,
          selection: true,
          zoom: true,
          zoomin: true,
          zoomout: true,
          pan: true,
          reset: true | '<img src="/static/icons/reset.png" width="20">',
          customIcons: []
        },
                 }
            },
            colors: ['#28a745', '#dc3545'], // ✅ Hijau (Inbound), Merah (Outbound)
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth' },
            series: [
                { name: 'Inbound', data: inboundData },
                { name: 'Outbound', data: outboundData }
            ],
            xaxis: {
    categories: dates.map(d => formatTanggalChart(d)),
    labels: {
        style: {
            fontSize: '12px'
        }
    }
},
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return parseInt(val).toLocaleString('id-ID');
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: val => parseInt(val).toLocaleString('id-ID') + ' pcs'
                }
            }
        };

        if (chart) chart.destroy();
        chart = new ApexCharts(document.querySelector("#apexChartProduct"), options);
        chart.render();
    }

    function fetchStatistik() {
        $.ajax({
            url: "<?= base_url('stock-transactions/statistics') ?>",
            type: "POST",
            data: {
                warehouse_id: $('#filter_warehouse').val(),
                transaction_type: $('#filter_type').val(),
                start_date: $('#filter_start').val(),
                end_date: $('#filter_end').val(),
                [csrfName]: csrfHash
            },
            success: function (res) {
                csrfHash = res[csrfName];
                
                // Handle NaN
                $('#stat_total_inbound').text(parseInt(res.total_inbound || 0).toLocaleString('id-ID'));
                $('#stat_total_outbound').text(parseInt(res.total_outbound || 0).toLocaleString('id-ID'));
                
                // Handle chart data
                if (res.chart && res.chart.labels) {
                    renderApexChart(res.chart.labels, res.chart.inbound, res.chart.outbound);
                } else {
                    $("#apexChartProduct").html('<div class="text-center text-muted py-4">📭 Tidak ada data</div>');
                    if (chart) chart.destroy();
                }
                // Validasi response chart
    if (res.chart && Array.isArray(res.chart.labels)) {
        const safeInbound = res.chart.inbound?.map(Number) || [];
        const safeOutbound = res.chart.outbound?.map(Number) || [];
        renderApexChart(res.chart.labels, safeInbound, safeOutbound);
    } else {
        console.error("Invalid chart data structure", res);
        $("#apexChartProduct").html('<div class="text-danger">Error: Format data tidak valid</div>');
    }
            },
            error: function (xhr, status, error) {
                console.error("❌ Statistik Error:", error);
                $("#apexChartProduct").html('<div class="text-center text-danger py-4">⚠️ Gagal memuat data</div>');
            }

            
        });
    }

    const table = $('#stockTable').DataTable({
        processing: true,
        serverSide: true,
        dom: '<"d-flex justify-content-between align-items-center mb-2"Bf><"table-responsive"t><"d-flex justify-content-between align-items-center mt-2"lip>',
     buttons: [
    {
        extend: 'excelHtml5',
        text: '📥 Export Excel',
        className: 'btn btn-success mb-3',
        filename: 'Stock_Transactions',
        exportOptions: {
    columns: ':visible',
    format: {
        body: function (data, row, column, node) {
            // Strip HTML
            let div = document.createElement("div");
            div.innerHTML = data;
            let text = div.textContent || div.innerText || "";

            // Format angka: hapus titik ribuan
            if (!isNaN(text.replace(/\./g, ''))) {
                return text.replace(/\./g, '');
            }

            // Format tanggal: dari "Kam, 27 Maret 2025" → "27-03-2025"
            const monthMap = {
                Januari: '01', Februari: '02', Maret: '03', April: '04',
                Mei: '05', Juni: '06', Juli: '07', Agustus: '08',
                September: '09', Oktober: '10', November: '11', Desember: '12'
            };

            const matchTanggal = text.match(/(\d{1,2}) (\w+) (\d{4})/);
            if (matchTanggal) {
                const tgl = matchTanggal[1].padStart(2, '0');
                const bln = monthMap[matchTanggal[2]] || '00';
                const thn = matchTanggal[3];
                return `${tgl}-${bln}-${thn}`;
            }

            return text;
        }
    }
}
    }
],
        ajax: {
            url: "<?= base_url('stock-transactions/get-stock-transactions') ?>",
            type: "POST",
            data: function (d) {
                d.warehouse_id = $('#filter_warehouse').val();
                d.transaction_type = $('#filter_type').val();
                d.start_date = $('#filter_start').val();
                d.end_date = $('#filter_end').val();
                d[csrfName] = csrfHash;
            },
            dataSrc: function (json) {
                csrfHash = json[csrfName];
                return json.data;
            }
        },
        columns: [
            { data: 'warehouse_name' },
            { data: 'product_name' },
            {
                data: 'transaction_type',
                render: function (data) {
                    let badgeClass = 'badge bg-secondary';
                    if (data === 'Inbound') badgeClass = 'badge bg-success';
                    else if (data === 'Outbound') badgeClass = 'badge bg-danger';
                    else if (data === 'Transfer') badgeClass = 'badge bg-primary';
                    return `<span class="${badgeClass}">${data}</span>`;
                }
            },
            {
                data: 'quantity',
                render: d => parseInt(d).toLocaleString('id-ID')
            },
            { data: 'status' },
            { data: 'transaction_source' },
            { data: 'related_warehouse_name' },
            {
                data: 'created_at',
                render: function (data) {
                    if (!data) return '-';
                    const date = new Date(data);
                    const bulan = [
                        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];
                    // Format: 24 Mei 2022
                    return `${date.getDate()} ${bulan[date.getMonth()]} ${date.getFullYear()}`;
                }
            }
        ]
    });

    $('#filter_warehouse, #filter_type, #filter_start, #filter_end').on('change', function () {
        table.ajax.reload();
        fetchStatistik();
    });

    // Initial load
    fetchStatistik();
});

$('#btnExportChart').on('click', function () {
    const element = document.querySelector('#chartExportWrapper'); // ID baru yang wrap chart + judul

    const opt = {
        margin: 0.5,
        filename: 'grafik-transaksi-produk.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: {
            scale: 2,
            useCORS: true
        },
        jsPDF: {
            unit: 'in',
            format: 'a4',
            orientation: 'landscape'
        }
    };

    html2pdf().set(opt).from(element).save();
});

</script>
<?= $this->endSection() ?>
