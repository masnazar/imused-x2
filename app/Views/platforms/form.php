<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>
    <?= ($mode==='edit') ? 'Edit Platform' : 'Tambah Platform' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<div class="card custom-card">
  <div class="card-body">
    <form action="<?= ($mode==='edit')
            ? site_url("platforms/update/{$platform['id']}")
            : site_url('platforms/store') ?>"
          method="post">
      <?= csrf_field() ?>

      <div class="mb-3">
        <label for="code" class="form-label">Kode Platform</label>
        <input type="text"
               id="code"
               name="code"
               class="form-control"
               required
               value="<?= old('code', $platform['code'] ?? '') ?>">
      </div>

      <div class="mb-3">
        <label for="name" class="form-label">Nama Platform</label>
        <input type="text"
               id="name"
               name="name"
               class="form-control"
               required
               value="<?= old('name', $platform['name'] ?? '') ?>">
      </div>

      <div class="d-flex justify-content-end">
        <a href="<?= site_url('platforms') ?>" class="btn btn-light me-2">Batal</a>
        <button type="submit" class="btn btn-primary">
          <?= ($mode==='edit') ? 'Perbarui' : 'Simpan' ?>
        </button>
      </div>
    </form>
  </div>
</div>
<?= $this->endSection() ?>
