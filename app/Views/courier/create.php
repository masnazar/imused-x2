<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Tambah Kurir<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Breadcrumb -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
  <div>
    <nav>
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('courier') ?>">Data Kurir</a></li>
        <li class="breadcrumb-item active">Tambah Kurir</li>
      </ol>
    </nav>
    <h1 class="page-title fw-medium fs-18 mb-0">
      <i class="fas fa-plus me-2"></i> Tambah Kurir
    </h1>
  </div>
</div>

<?= $this->include('layouts/components/flashMessage') ?>

<div class="card custom-card">
  <div class="card-body">
    <form action="<?= base_url('courier/store') ?>" method="post">
      <?= csrf_field() ?>

      <div class="form-group mb-3">
        <label for="courier_name">Nama Kurir</label>
        <input type="text" class="form-control" name="courier_name" value="<?= old('courier_name') ?>" required>
      </div>

      <div class="form-group mb-3">
        <label for="courier_code">Kode Kurir</label>
        <input type="text" class="form-control" name="courier_code" value="<?= old('courier_code') ?>" required>
      </div>

      <button type="submit" class="btn btn-success mt-2">Simpan</button>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
