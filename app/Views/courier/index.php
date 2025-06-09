<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Data Kurir<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb & Button -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
  <div>
    <nav>
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item active">Data Kurir</li>
      </ol>
    </nav>
    <h1 class="page-title fw-medium fs-18 mb-0">
      <i class="fas fa-truck me-2"></i> Data Kurir
    </h1>
  </div>

  <!-- Tombol Tambah -->
  <div>
    <a href="<?= base_url('courier/create') ?>" class="btn btn-primary">
      <i class="fas fa-plus me-1"></i> Tambah Kurir
    </a>
  </div>
</div>

<?= $this->include('layouts/components/flashMessage') ?>

<!-- Tabel Data -->
<div class="card custom-card">
  <div class="card-body table-responsive">
    <table id="courierTable" class="table table-hover table-borderless mb-0 w-100">
      <thead class="bg-light">
        <tr>
          <th>No</th>
          <th>Nama Kurir</th>
          <th>Kode Kurir</th>
          <th>Dibuat</th>
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
  $('#courierTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: '<?= base_url('api/couriers') ?>',
    columns: [
      { data: 'no', className: 'text-center' },
      { data: 'courier_name' },
      { data: 'courier_code' },
      { data: 'created_at' },
      {
        data: 'id',
        orderable: false,
        render: id => `<a href="<?= base_url('courier/edit') ?>/${id}" class="btn btn-sm btn-warning">Edit</a>`
      }
    ]
  });
});
</script>
<?= $this->endSection() ?>
