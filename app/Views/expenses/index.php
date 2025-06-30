<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Daftar Expenses<?= $this->endSection() ?>

<?= $this->section('content') ?>
  
  <!-- Breadcrumbs -->
  <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
    <div>
      <nav>
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item">
            <a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a>
          </li>
          <li class="breadcrumb-item active">Daftar Expenses</li>
        </ol>
      </nav>
      <h1 class="page-title fw-medium fs-18 mb-0">
        <i class="ti ti-file-text"></i> Daftar Expenses
      </h1>
    </div>
  </div>

  <?= $this->include('layouts/components/flashMessage') ?>

  <!-- Filter Periode -->
  <div class="mb-4">
    <?= $date_filter /* controller harus mengirim: 'date_filter' => view('partials/date_filter') */ ?>
  </div>

  <div class="card custom-card">
    <div class="card-header d-flex justify-content-between">
      <h5 class="card-title">Daftar Expenses</h5>
      <a href="<?= site_url('expenses/create') ?>" class="btn btn-primary btn-wave">
        <i class="ri-add-line me-1"></i> Tambah Expense
      </a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table id="expensesTable" class="table text-nowrap table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>Tanggal</th>
              <th>Deskripsi</th>
              <th>COA</th>
              <th>Brand</th>
              <th>Platform</th>
              <th class="text-end">Jumlah</th>
              <th>Tipe Proses</th>
              <th>Diproses Oleh</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>

<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
  // Formatter tanggal
  const formatTanggal = tgl => {
    if (!tgl) return '-';
    const d    = new Date(tgl),
          hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'],
          bln  = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return `${hari[d.getDay()]}, ${String(d.getDate()).padStart(2,'0')} ${bln[d.getMonth()]} ${d.getFullYear()}`;
  };

  const csrfName = '<?= csrf_token() ?>';
  let   csrfHash = '<?= csrf_hash() ?>';

  // Ambil elemen filter sesuai id di partial date_filter.php
  const jenisFilter   = document.getElementById('jenisFilter');
  const periodeSelect = document.getElementById('periodeSelect');
  const startDate     = document.getElementById('startDate');
  const endDate       = document.getElementById('endDate');

  $(document).ready(() => {
    const table = $('#expensesTable').DataTable({
      processing: true,
      serverSide: true,
      order: [[1, 'desc']],
      ajax: {
        url: "<?= site_url('expenses/get-data') ?>",
        type: "POST",
        data: d => {
          d[csrfName]    = csrfHash;
          d.jenis_filter = jenisFilter.value;
          d.periode      = periodeSelect.value;
          d.start_date   = startDate.value;
          d.end_date     = endDate.value;
        },
        dataSrc: json => {
          if (json[csrfName]) csrfHash = json[csrfName];
          return json.data || [];
        },
        error: xhr => {
          console.error('DataTables Error:', xhr.responseText);
          Swal.fire('Gagal!', 'Tidak dapat memuat data.', 'error');
        }
      },
      columns: [
        {
          data: null, orderable: false, className: 'text-center',
          render: (_, __, ___, meta) => meta.row + 1
        },
        {
          data: 'date',
          render: formatTanggal
        },
        { data: 'description' },
        {
          data: 'coa_code',
          render: (c, _, row) => `${c} – ${row.coa_name}`
        },
        { data: 'brand_name',    defaultContent: '-' },
        { data: 'platform_name', defaultContent: '-' },
        {
          data: 'amount',
          className: 'text-end',
          render: $.fn.dataTable.render.number('.', ',', 2)
        },
        { data: 'type' },
        { data: 'processed_by' },
        {
          data: 'id', orderable: false, className: 'text-center',
          render: id => `
            <a href="<?= site_url('expenses/edit') ?>/${id}" class="btn btn-sm btn-warning">Edit</a>
            <button class="btn btn-sm btn-danger btn-del" data-id="${id}">Del</button>`
        }
      ]
    });

    // Reload table saat filter berubah
    [jenisFilter, periodeSelect, startDate, endDate].forEach(el =>
      el.addEventListener('change', () => table.ajax.reload())
    );

    // Hapus record
    $('#expensesTable').on('click', '.btn-del', function(){
      if (!confirm('Yakin ingin menghapus?')) return;
      const id = $(this).data('id');
      $.ajax({
        url: `<?= site_url('expenses/delete') ?>/${id}`,
        type: 'DELETE',
        data: { [csrfName]: csrfHash },
        success: () => table.ajax.reload()
      });
    });
  });
</script>
<?= $this->endSection() ?>
