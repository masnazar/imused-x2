<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Daftar Brand Expenses<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <!-- Header & Breadcrumb -->
  <div class="d-flex align-items-center justify-content-between page-header-breadcrumb gap-2 mb-3">
    <div>
      <nav>
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item">
            <a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a>
          </li>
          <li class="breadcrumb-item active">Brand Expenses</li>
        </ol>
      </nav>
      <h1 class="page-title fw-medium fs-18 mb-0">
        <i class="ti ti-list"></i> Daftar Brand Expenses
      </h1>
    </div>
  </div>

  <?= $this->include('layouts/components/flashMessage') ?>

  <!-- Periode Filter -->
  <?= $date_filter ?>

  <div class="mb-3">
    <a href="<?= site_url('brand-expenses/create') ?>" class="btn btn-primary">
      <i class="ri-add-line me-1"></i> Tambah Brand Expense
    </a>
  </div>

  <div class="card custom-card">
    <div class="card-body table-responsive">
      <table id="brandExpensesTable" class="table table-bordered text-nowrap">
        <thead>
          <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Brand</th>
            <th>Toko</th>
            <th>COA</th>
            <th>Platform</th>
            <th class="text-end">Jumlah</th>
            <th>Tipe</th>
            <th>Diproses Oleh</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
<?= $this->endSection() ?>


<?= $this->section('scripts') ?>
<script>
$(function(){
  let csrfName = '<?= csrf_token() ?>',
      csrfHash = '<?= csrf_hash() ?>';

  const table = $('#brandExpensesTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "<?= site_url('brand-expenses/getData') ?>",
      type: 'POST',
      data: d => {
        d[csrfName]   = csrfHash;
        d.jenis_filter = $('#jenisFilter').val();
        d.periode      = $('select[name="periode"]').val();
        d.start_date   = $('#startDate').val();
        d.end_date     = $('#endDate').val();
      },
      dataSrc: json => {
        csrfHash = json[csrfName] || csrfHash;
        return json.data || [];
      }
    },
    columns:[
      { data:null, render:(_,__,___,m) => m.row + m.settings._iDisplayStart + 1 },
      { data:'date' },
      { data:'brand_name' },
      { data:null,
        render:(v,t,r) => r.store_code
          ? `${r.store_name}`
          : '-' 
      },
      { data:null,
        render:(v,t,r) => `${r.coa_code} – ${r.coa_name}` 
      },
      { data: 'platform_name', defaultContent: '-' },
      { data:'amount',
        className:'text-end',
        render: $.fn.dataTable.render.number('.',',',2)
      },
      { data:'type' },
      { data:'processed_by_name' },
      { data:'id',
        orderable:false,
        className:'text-center',
        render:id=>`
          <a href="<?= site_url('brand-expenses/edit') ?>/${id}"
             class="btn btn-sm btn-warning me-1">Edit</a>
          <button class="btn btn-sm btn-danger btn-del"
                  data-id="${id}">Del</button>`
      }
    ]
  });

  // reload saat filter periode berubah
  $('#jenisFilter,#startDate,#endDate').on('change', () => table.ajax.reload());

  // handler delete
  $('#brandExpensesTable').on('click','.btn-del', function(){
    if (!confirm('Yakin hapus data ini?')) return;
    const id = $(this).data('id');
    $.ajax({
      url: `<?= site_url('brand-expenses/delete') ?>/${id}`,
      type:'DELETE',
      data:{ [csrfName]: csrfHash },
      success: () => table.ajax.reload()
    });
  });
});
</script>
<?= $this->endSection() ?>
