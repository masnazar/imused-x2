<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Daftar Team Soscom<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="page-title fs-18"><i class="ti ti-users me-2"></i> Daftar Team Soscom</h1>
  <a href="<?= base_url('soscom-teams/create') ?>" class="btn btn-primary"><i class="ti ti-plus"></i> Tambah Team</a>
</div>

<?= $this->include('layouts/components/flashMessage') ?>

<div class="card custom-card">
  <div class="card-body table-responsive">
    <table class="table table-hover table-bordered mb-0" id="teamsTable">
      <thead>
        <tr class="bg-light">
          <th width="10">#</th>
          <th>Kode Team</th>
          <th>Nama Team</th>
          <th>Dibuat</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($teams as $index => $team): ?>
          <tr>
            <td><?= $index + 1 ?></td>
            <td><span class="badge bg-info"><?= esc($team['team_code']) ?></span></td>
            <td><?= esc($team['team_name']) ?></td>
            <td><?= date('D, d M Y H:i', strtotime($team['created_at'])) ?></td>
            <td>
              <a href="<?= base_url("soscom-teams/edit/{$team['id']}") ?>" class="btn btn-sm btn-warning">
                <i class="ti ti-edit"></i> Edit
              </a>
              <a href="<?= base_url("soscom-teams/delete/{$team['id']}") ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus team ini?')">
                <i class="ti ti-trash"></i> Hapus
              </a>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
  $('#teamsTable').DataTable();
</script>
<?= $this->endSection() ?>
