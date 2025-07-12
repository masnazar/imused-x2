<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Daftar Toko<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <!-- Header & Breadcrumb -->
  <div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
    <div>
      <nav>
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item">
            <a href="<?= base_url() ?>">
              <i class="fas fa-home"></i> Dashboard
            </a>
          </li>
          <li class="breadcrumb-item active">Daftar Toko</li>
        </ol>
      </nav>
      <h1 class="page-title fw-medium fs-18 mb-0">
        <i class="ti ti-building"></i> Daftar Toko
      </h1>
    </div>
  </div>

  <?= $this->include('layouts/components/flashMessage') ?>

  <div class="mb-3">
    <a href="<?= site_url('stores/create') ?>" class="btn btn-primary">
      <i class="ri-add-line me-1"></i> Tambah Toko
    </a>
  </div>

  <div class="card custom-card">
    <div class="card-body">
      <div class="table-responsive">
        <table id="storesTable" class="table table-bordered text-nowrap">
          <thead>
            <tr>
              <th>No</th>
              <th>Kode Toko</th>
              <th>Nama Toko</th>
              <th>Brand</th>
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
$(function(){
  // CSRF token name & hash
  var csrfName = '<?= csrf_token() ?>',
      csrfHash = '<?= csrf_hash() ?>';

  var table = $('#storesTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "<?= site_url('stores/get-data') ?>",
      type: 'POST',
      data: function(d) {
        // add CSRF, then return the payload
        d[csrfName] = csrfHash;
        return d;
      },
      dataSrc: function(json) {
        // update CSRF for next request
        if (json[csrfName]) {
          csrfHash = json[csrfName];
        }
        return json.data || [];
      }
    },
    columns: [
      { data: null,
        render: function(data, type, row, meta) {
          return meta.row + meta.settings._iDisplayStart + 1;
        }
      },
      { data: 'store_code' },
      { data: 'store_name' },
      { data: 'brand_name' },
      { data: 'id',
        orderable: false,
        className: 'text-center',
        render: function(id) {
          return `
            <a href="<?= site_url('stores/edit') ?>/${id}"
               class="btn btn-sm btn-warning me-1">Edit</a>
            <button class="btn btn-sm btn-danger btn-del" data-id="${id}">
              Del
            </button>`;
        }
      }
    ]
  });

  // Delete handler
  $('#storesTable').on('click', '.btn-del', function(){
    var id = $(this).data('id');
    if (!confirm('Yakin hapus toko ini?')) return;

    $.ajax({
      url: `<?= site_url('stores/delete') ?>/${id}`,
      type: 'DELETE',
      data: { [csrfName]: csrfHash },
      success: function(res) {
        // refresh CSRF and table
        if (res[csrfName]) {
          csrfHash = res[csrfName];
        }
        table.ajax.reload(null, false);
      },
      error: function(){
        alert('Gagal menghapus toko.');
      }
    });
  });
});
</script>
<?= $this->endSection() ?>
