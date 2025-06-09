<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Tambah Team Baru<?= $this->endSection() ?>
<?= $this->section('content') ?>

<h1 class="page-title fs-18 mb-3"><i class="ti ti-user-plus me-2"></i> Tambah Team Soscom</h1>

<div class="card custom-card">
  <div class="card-body">
    <form action="<?= base_url('soscom-teams/store') ?>" method="post">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label for="team_code" class="form-label">Kode Team</label>
        <input type="text" name="team_code" id="team_code" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="team_name" class="form-label">Nama Team</label>
        <input type="text" name="team_name" id="team_name" class="form-control" required>
      </div>
      <div class="d-flex justify-content-end">
        <a href="<?= base_url('soscom-teams') ?>" class="btn btn-secondary me-2">Kembali</a>
        <button type="submit" class="btn btn-success"><i class="ti ti-check"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
