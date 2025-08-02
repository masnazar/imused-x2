<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>
  <?= ($mode==='edit')?'Edit CS':'Tambah CS' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
  <?= $this->include('layouts/components/flashMessage') ?>

  <h1 class="h4 mb-3"><?= ($mode==='edit')?'Edit':'Tambah' ?> Customer Service</h1>
  <form method="post"
        action="<?= ($mode==='edit')
                    ? site_url("customer-services/update/{$cs['id']}")
                    : site_url('customer-services/store') ?>">
    <?= csrf_field() ?>

    <div class="mb-3">
      <label for="kode_cs" class="form-label">Kode CS</label>
      <input type="text"
             name="kode_cs" id="kode_cs"
             class="form-control"
             value="<?= old('kode_cs', $cs['kode_cs'] ?? '') ?>"
             required>
    </div>

    <div class="mb-3">
      <label for="nama_cs" class="form-label">Nama CS</label>
      <input type="text"
             name="nama_cs" id="nama_cs"
             class="form-control"
             value="<?= old('nama_cs', $cs['nama_cs'] ?? '') ?>"
             required>
    </div>

    <div class="text-end">
      <a href="<?= site_url('customer-services') ?>" class="btn btn-secondary me-2">Batal</a>
      <button type="submit" class="btn btn-primary">
        <?= ($mode==='edit')?'Perbarui':'Simpan' ?>
      </button>
    </div>
  </form>
<?= $this->endSection() ?>
