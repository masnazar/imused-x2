<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Transaksi Sosial Commerce<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center mb-3">
  <h1 class="page-title">📱 Transaksi Soscom</h1>
  <a href="<?= base_url('soscom-transactions/import') ?>" class="btn btn-success">
    <i class="ti ti-upload"></i> Import Excel
  </a>
</div>

<?= $this->include('partials/date_filter') ?>

<div class="card">
  <div class="card-body">
    <table id="soscomTable" class="table table-bordered table-hover">
      <thead class="table-light">
        <tr>
          <th>Tanggal</th>
          <th>Order #</th>
          <th>Customer</th>
          <th>WA</th>
          <th>Kota</th>
          <th>Provinsi</th>
          <th>Qty</th>
          <th>Penjualan</th>
          <th>Diskon</th>
          <th>Admin</th>
          <th>Net</th>
          <th>Laba</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
    </table>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(function () {
  $('#soscomTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: "<?= base_url('soscom-transactions/get-data') ?>",
      type: "POST",
      data: function (d) {
        d.jenis_filter = $('#jenis_filter').val();
        d.periode = $('#periode').val();
        d.start_date = $('#start_date').val();
        d.end_date = $('#end_date').val();
        d.brand_id = $('#brand_id').val();
      }
    },
    columns: [
      { data: 'date' },
      { data: 'order_number' },
      { data: 'customer_name' },
      { data: 'phone_number' },
      { data: 'city' },
      { data: 'province' },
      { data: 'total_qty', render: $.fn.dataTable.render.number('.', ',', 0, '') },
      { data: 'selling_price', render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') },
      { data: 'discount', render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') },
      { data: 'admin_fee', render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') },
      { data: 'net_revenue', render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') },
      { data: 'gross_profit', render: $.fn.dataTable.render.number('.', ',', 0, 'Rp ') },
      { data: 'status' },
      {
        data: null,
        orderable: false,
        render: function (data, type, row) {
          return `<a href="<?= base_url('soscom-transactions/detail/') ?>${row.id}" class="btn btn-sm btn-primary">Detail</a>`;
        }
      }
    ]
  });
});
</script>

<script>
  $('#excelImportForm').on('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    Swal.fire({
      title: 'Sedang Mengupload...',
      text: 'Mohon tunggu sebentar ya laey~',
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading()
    });

    $.ajax({
      url: "<?= base_url('soscom-transactions/import-excel') ?>",
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      success: function (res) {
        Swal.close();
        if (res.status === 'success') {
          Swal.fire('Sukses', res.message, 'success').then(() => {
            window.location.href = res.redirect;
          });
        } else {
          Swal.fire('Gagal', res.message ?? 'Terjadi kesalahan saat mengupload', 'error');
        }
      },
      error: function () {
        Swal.close();
        Swal.fire('Gagal', 'Server error saat upload file.', 'error');
      }
    });
  });
</script>

<?= $this->endSection() ?>
