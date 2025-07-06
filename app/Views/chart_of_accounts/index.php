<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Chart of Accounts<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
  <div>
    <nav>
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
          <a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Chart of Accounts</li>
      </ol>
    </nav>
    <h1 class="page-title fw-medium fs-18 mb-0">Chart of Accounts</h1>
  </div>
  <div class="btn-list">
    <a href="<?= site_url('chart-of-accounts/create') ?>" class="btn btn-primary btn-wave">
      <i class="ri-add-line me-1"></i> Tambah Akun
    </a>
  </div>
</div>

<!-- Table Card -->
<div class="card custom-card">
  <div class="card-header justify-content-between">
    <h5 class="card-title">Daftar Chart of Accounts</h5>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="coaTable" class="table text-nowrap table-bordered">
        <thead>
          <tr>
            <th>No</th>
            <th>Code</th>
            <th>Name</th>
            <th>Type</th>
            <th>Normal Balance</th>
            <th>Parent</th>
            <th>Created At</th>
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
// --- CSRF setup ---
const csrfName   = '<?= csrf_token() ?>';
let   csrfHash   = '<?= csrf_hash() ?>';
const csrfHeader = 'X-CSRF-TOKEN';

// tunggu dokumen siap
$(function(){
  // 1) Inisialisasi DataTable
  const table = $('#coaTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "<?= site_url('chart-of-accounts/get-data') ?>",
      type: "POST",
      data: d => { d[csrfName] = csrfHash },
      dataSrc: json => {
        if (json[csrfName]) csrfHash = json[csrfName];
        return json.data || [];
      }
    },
    columns: [
      { data:null, orderable:false, className:'text-center',
        render: (_,__,row,meta)=>
          meta.row + meta.settings._iDisplayStart + 1
      },
      { data:'code' },
      { data:'name' },
      { data:'type' },
      { data:'normal_balance' },
      { data:null, render: (_,__,r)=>
          r.parent_code
            ? `${r.parent_code} – ${r.parent_name}`
            : '—'
      },
      { data:'created_at' },
      { data:null, orderable:false, className:'text-center',
        render: d => `
          <a href="<?= site_url('chart-of-accounts/edit') ?>/${d.id}" 
             class="btn btn-sm btn-warning me-1">
            <i class="ri-edit-line"></i>
          </a>
          <button data-id="${d.id}" class="btn btn-sm btn-danger btn-del">
            <i class="ri-delete-bin-5-line"></i>
          </button>`
      }
    ]
  });

  // 2) Delete dengan SweetAlert
  $('#coaTable').on('click', '.btn-del', function(){
    const id = $(this).data('id');

    Swal.fire({
      title: 'Yakin ingin menghapus?',
      text: 'Data akun akan dihapus secara permanen.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal',
      reverseButtons: true
    }).then(result => {
      if (!result.isConfirmed) return;

      $.ajax({
        url: `<?= site_url('chart-of-accounts/delete') ?>/${id}`,
        type: 'DELETE',
        headers: { [csrfHeader]: csrfHash },
        success: res => {
          // ambil hash baru
          if (res[csrfName]) csrfHash = res[csrfName];
          // reload tanpa reset paging
          table.ajax.reload(null, false);

          Swal.fire({
            title: 'Terhapus!',
            text: 'Akun berhasil dihapus.',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
          });
        },
        error: (xhr, status, err) => {
          console.error('Hapus gagal:', status, err);
          Swal.fire({
            title: 'Gagal',
            text: 'Akun gagal dihapus. Silakan coba lagi.',
            icon: 'error'
          });
        }
      });
    });
  });
});
</script>
<?= $this->endSection() ?>
