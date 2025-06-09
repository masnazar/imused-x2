<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Edit Team<?= $this->endSection() ?>
<?= $this->section('content') ?>

<h1 class="page-title fs-18 mb-3"><i class="ti ti-edit me-2"></i> Edit Team</h1>

<div class="card custom-card">
  <div class="card-body">
    <form action="<?= base_url('soscom-teams/update/' . $team['id']) ?>" method="post">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label for="team_code" class="form-label">Kode Team</label>
        <input type="text" name="team_code" id="team_code" class="form-control" value="<?= esc($team['team_code']) ?>" required>
      </div>
      <div class="mb-3">
        <label for="team_name" class="form-label">Nama Team</label>
        <input type="text" name="team_name" id="team_name" class="form-control" value="<?= esc($team['team_name']) ?>" required>
      </div>
      <div class="d-flex justify-content-end">
        <a href="<?= base_url('soscom-teams') ?>" class="btn btn-secondary me-2">Batal</a>
        <button type="submit" class="btn btn-primary"><i class="ti ti-check"></i> Update</button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
