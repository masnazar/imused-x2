<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Data Customer<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header mb-4">
  <h1 class="page-title">👥 Data Customer</h1>
</div>

<!-- Flash Message -->
<?= $this->include('layouts/components/flashMessage') ?>

<div class="card">
  <div class="card-body table-responsive">
    <table id="customerTable" class="table table-bordered align-middle text-center">
      <thead class="table-light">
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
<script>
$(function () {
  $('#customerTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "<?= base_url('customers/data') ?>",
      type: "POST",
      data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' }
    },
    columns: [
      { data: 'name' },
      { data: 'phone_number' },
      { data: 'city' },
      { data: 'province' },
      { data: 'order_count' },
      { data: 'ltv', render: data => 'Rp ' + parseInt(data).toLocaleString('id-ID') },
      { data: 'first_order_date' },
      { data: 'last_order_date' },
      { data: 'segment' },
      {
        data: null,
        orderable: false,
        render: function (data, type, row) {
          return `<a href="<?= base_url('customers/detail/') ?>${row.id}" class="btn btn-sm btn-primary">Detail</a>`;
        }
      }
    ]
  });
});
</script>
<?= $this->endSection() ?>
