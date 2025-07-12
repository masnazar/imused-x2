<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
  <?= ($mode==='edit') ? 'Edit Toko' : 'Tambah Toko' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Header & Breadcrumb -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
  <div>
    <nav>
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
          <a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item"><a href="<?= site_url('stores') ?>">Toko</a></li>
        <li class="breadcrumb-item active">
          <?= ($mode==='edit') ? 'Edit' : 'Tambah' ?> Toko
        </li>
      </ol>
    </nav>
    <h1 class="page-title fw-medium fs-18 mb-0">
      <?= ($mode==='edit') ? 'Edit Toko' : 'Tambah Toko' ?>
    </h1>
  </div>
</div>

<div class="row">
  <div class="col-lg-6">
    <div class="card custom-card">
      <div class="card-header">
        <div class="card-title">
          <?= ($mode==='edit') ? 'Form Edit' : 'Form Tambah' ?> Toko
        </div>
      </div>
      <div class="card-body">
        <form id="storeForm"
              action="<?= ($mode==='edit')
                  ? site_url("stores/update/{$store['id']}")
                  : site_url('stores/store') ?>"
              method="post"
        >
          <?= csrf_field() ?>

          <!-- Brand -->
          <div class="mb-3">
            <label for="brand_id" class="form-label fw-semibold">Brand</label>
            <select name="brand_id" id="brand_id" class="form-select" required>
              <option value="">Pilih Brand</option>
              <?php foreach($brands as $b): ?>
              <option value="<?= $b['id'] ?>"
                <?= set_select('brand_id',$b['id'],
                    isset($store['brand_id']) && $store['brand_id']==$b['id']
                ) ?>>
                <?= esc($b['brand_name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Store Code -->
          <div class="mb-3">
            <label for="store_code" class="form-label fw-semibold">Kode Toko</label>
            <input
              type="text"
              id="store_code"
              name="store_code"
              class="form-control"
              required
              value="<?= old('store_code',$store['store_code'] ?? '') ?>"
            >
          </div>

          <!-- Store Name -->
          <div class="mb-3">
            <label for="store_name" class="form-label fw-semibold">Nama Toko</label>
            <input
              type="text"
              id="store_name"
              name="store_name"
              class="form-control"
              required
              value="<?= old('store_name',$store['store_name'] ?? '') ?>"
            >
          </div>

          <!-- Buttons -->
          <div class="d-flex justify-content-end">
            <a href="<?= site_url('stores') ?>" class="btn btn-secondary me-2">
              <i class="ri-arrow-left-line"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="ri-save-line"></i>
              <?= ($mode==='edit') ? 'Perbarui' : 'Simpan' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
