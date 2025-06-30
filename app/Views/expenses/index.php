<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Daftar Expenses<?= $this->endSection() ?>
<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

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
// 📌 Buat formatter tanggal
function formatTanggalIndonesia(tgl) {
  if (!tgl) return '-';
  const hari  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
  const d     = new Date(tgl);
  return `${hari[d.getDay()]}, ${d.getDate().toString().padStart(2,'0')} ${bulan[d.getMonth()]} ${d.getFullYear()}`;
}

// 📌 Formatter angka
const formatNumber = $.fn.dataTable.render.number('.', ',', 2, '').display;

// ambil CSRF name & hash dari PHP
const csrfName = '<?= csrf_token() ?>';
let   csrfHash = '<?= csrf_hash() ?>';

$(document).ready(function(){
  const table = $('#expensesTable').DataTable({
    processing: true,
    serverSide: true,
    order: [[1, 'desc']], // urut by tanggal
    ajax: {
      url: "<?= site_url('expenses/get-data') ?>",
      type: "POST",
      data: function(d) {
        d[csrfName] = csrfHash;
      },
      dataSrc: function(json) {
        if (json[csrfName]) {
          csrfHash = json[csrfName];
        }
        return json.data || [];
      },
      error: function(xhr) {
        console.error('❌ DataTables Error:', xhr.responseText);
        Swal.fire('Gagal!', 'Tidak dapat memuat data.', 'error');
      }
    },
    columns: [
      { // No
        data: null,
        render: function(_, _t, _r, meta){
          return meta.row + 1;
        }
      },
      { // Tanggal
        data: 'date',
        render: formatTanggalIndonesia
      },
      { data: 'description' }, // Deskripsi
      { // COA
        data: 'coa_code',
        render: function(code, _, row){
          return `${code} – ${row.coa_name}`;
        }
      },
      { data: 'brand_name', defaultContent: '-' }, // Brand
      { // Jumlah
        data: 'amount',
        className: 'text-end',
        render: formatNumber
      },
      { data: 'type' },          // Tipe Proses
      { data: 'processed_by' },  // Diproses Oleh
      { // Aksi
        data: 'id',
        orderable: false,
        render: function(id){
          return `
            <a href="<?= site_url('expenses/edit') ?>/${id}" class="btn btn-sm btn-warning">Edit</a>
            <button class="btn btn-sm btn-danger btn-del" data-id="${id}">Del</button>
          `;
        }
      }
    ]
  });

  // handle delete
  $('#expensesTable').on('click', '.btn-del', function(){
    if (!confirm('Yakin dihapus?')) return;
    const id = $(this).data('id');
    $.ajax({
      url: `<?= site_url('expenses/delete') ?>/${id}`,
      type: 'DELETE',
      data: { [csrfName]: csrfHash },
      success: function(){
        table.ajax.reload();
      },
      error: function(err){
        console.error('❌ Hapus Error:', err.responseText);
        Swal.fire('Error', 'Gagal menghapus.', 'error');
      }
    });
  });
});
</script>
<?= $this->endSection() ?>
