<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Daftar Platform<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<div class="card custom-card">
  <div class="card-header d-flex justify-content-between">
    <h5 class="card-title">Daftar Platform</h5>
    <a href="<?= site_url('platforms/create') ?>" class="btn btn-primary btn-wave">
      <i class="ri-add-line me-1"></i> Tambah Platform
    </a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="platformsTable" class="table text-nowrap table-bordered">
        <thead>
          <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama</th>
            <th>Dibuat</th>
            <th>Aksi</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// 🗓️ Format tanggal: Senin, 06 Mei 2025
function formatTanggalIndonesia(tgl) {
  if (!tgl) return '-';
  const hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  const d = new Date(tgl);
  return `${hari[d.getDay()]}, ${String(d.getDate()).padStart(2,'0')} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
}

const csrfName = '<?= csrf_token() ?>';
let   csrfHash = '<?= csrf_hash() ?>';

$('#platformsTable').DataTable({
  processing: true,
  serverSide: true,
  order: [[1,'asc']],
  ajax: {
    url: "<?= site_url('platforms/get-data') ?>",
    type: "POST",
    data(d) {
      d[csrfName] = csrfHash;
    },
    dataSrc(json) {
      if (json[csrfName]) csrfHash = json[csrfName];
      return json.data || [];
    },
    error(xhr) {
      console.error(xhr.responseText);
      Swal.fire('Gagal!', 'Tidak dapat memuat data.', 'error');
    }
  },
  columns: [
    // No (menghitung offset + 1)
    {
      data: null,
      orderable: false,
      className: 'text-center',
      render: (_,__,___,meta) =>
        meta.row + meta.settings._iDisplayStart + 1
    },
    { data: 'code' },
    { data: 'name' },
    { data: 'created_at', render: formatTanggalIndonesia },
    {
      data: 'id',
      orderable: false,
      className: 'text-center',
      render: id => `
        <a href="<?= site_url('platforms/edit') ?>/${id}" class="btn btn-sm btn-warning">Edit</a>
        <button class="btn btn-sm btn-danger btn-del" data-id="${id}">Del</button>
      `
    }
  ]
});

// Delete handler
$('#platformsTable').on('click','.btn-del',function(){
  if (!confirm('Yakin ingin menghapus?')) return;
  $.ajax({
    url: `<?= site_url('platforms/delete') ?>/${$(this).data('id')}`,
    type: 'DELETE',
    data: { [csrfName]: csrfHash },
    success() {
      $('#platformsTable').DataTable().ajax.reload();
    }
  });
});
</script>
<?= $this->endSection() ?>
