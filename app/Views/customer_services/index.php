<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Daftar Customer Services<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4">Daftar Customer Services</h1>
    <a href="<?= site_url('customer-services/create') ?>" class="btn btn-primary">
      <i class="ri-add-line"></i> Tambah CS
    </a>
  </div>

  <?= $this->include('layouts/components/flashMessage') ?>

  <div class="table-responsive">
    <table id="csTable" class="table table-bordered">
      <thead>
        <tr>
          <th>No</th>
          <th>Kode CS</th>
          <th>Nama CS</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function(){
  let csrfName = '<?= csrf_token() ?>',
      csrfHash = '<?= csrf_hash() ?>';

  const table = $('#csTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "<?= site_url('customer-services/get-data') ?>",
      type:'POST',
      data: d => d[csrfName] = csrfHash,
      dataSrc: json => {
        csrfHash = json[csrfName] || csrfHash;
        return json.data || [];
      }
    },
    columns: [
      { data: null, render: (t,u,v,meta)=> meta.row + meta.settings._iDisplayStart +1 },
      { data: 'kode_cs' },
      { data: 'nama_cs' },
      { data: 'id', orderable:false, className:'text-center',
        render: id => `
          <a href="<?= site_url('customer-services/edit') ?>/${id}" class="btn btn-sm btn-warning me-1">Edit</a>
          <button class="btn btn-sm btn-danger btn-del" data-id="${id}">Hapus</button>`
      }
    ]
  });

  $('#csTable').on('click','.btn-del', function(){
    const id = $(this).data('id');
    if (!confirm('Yakin hapus CS ini?')) return;
    $.ajax({
      url: `<?= site_url('customer-services/delete') ?>/${id}`,
      type:'DELETE',
      data:{ [csrfName]: csrfHash },
      success:()=> table.ajax.reload()
    });
  });
});
</script>
<?= $this->endSection() ?>
