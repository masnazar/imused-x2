<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
  <?= ($mode==='edit') ? 'Edit Brand Expense' : 'Tambah Brand Expense' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Breadcrumb & Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb mb-3">
  <div>
    <nav>
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item">
          <a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a>
        </li>
        <li class="breadcrumb-item">
          <a href="<?= site_url('brand-expenses') ?>">Brand Expenses</a>
        </li>
        <li class="breadcrumb-item active">
          <?= ($mode==='edit') ? 'Edit' : 'Tambah' ?> Brand Expense
        </li>
      </ol>
    </nav>
    <h1 class="page-title fw-medium fs-18">
      <?= ($mode==='edit') ? 'Edit Brand Expense' : 'Tambah Brand Expense' ?>
    </h1>
  </div>
</div>

<form id="brandExpenseForm"
      method="post"
      action="<?= ($mode==='edit')
                 ? site_url("brand-expenses/update/{$brandExpense['id']}")
                 : site_url('brand-expenses/store') ?>">
  <?= csrf_field() ?>

  <div class="row mb-3">
    <div class="col-md-6">
      <label class="form-label">Brand</label>
      <select name="brand_id" id="brand_id" class="form-select" required>
        <option value="">Pilih Brand</option>
        <?php foreach($brands as $b): ?>
        <option value="<?= $b['id'] ?>"
          <?= set_select('brand_id',$b['id'], isset($brandExpense['brand_id']) && $brandExpense['brand_id']==$b['id']) ?>>
          <?= esc($b['brand_name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Toko (opsional)</label>
      <select name="store_id" id="store_id" class="form-select">
        <option value="">Pilih Toko</option>
        <?php foreach($stores as $s): ?>
        <option value="<?= $s['id'] ?>"
                data-brand-id="<?= $s['brand_id'] ?>"
          <?= set_select('store_id',$s['id'], isset($brandExpense['store_id']) && $brandExpense['store_id']==$s['id']) ?>>
          <?= esc($s['store_code']) ?> – <?= esc($s['store_name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label">Tanggal</label>
    <input type="date"
           name="date"
           class="form-control"
           required
           value="<?= old('date', $brandExpense['date'] ?? date('Y-m-d')) ?>">
  </div>

  <div class="mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea name="description"
              class="form-control"
              rows="3"><?= old('description', $brandExpense['description'] ?? '') ?></textarea>
  </div>

  <div class="row mb-3">
    <div class="col-md-4">
      <label class="form-label">COA</label>
      <select name="account_id" class="form-select" required>
        <option value="">Pilih COA</option>
        <?php foreach($coas as $c): ?>
        <option value="<?= $c['id'] ?>"
          <?= set_select('account_id',$c['id'], isset($brandExpense['account_id']) && $brandExpense['account_id']==$c['id']) ?>>
          <?= esc($c['code']) ?> – <?= esc($c['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Platform (opsional)</label>
      <select name="platform_id" class="form-select">
        <option value="">Pilih Platform</option>
        <?php foreach($platforms as $p): ?>
        <option value="<?= $p['id'] ?>"
          <?= set_select('platform_id',$p['id'], isset($brandExpense['platform_id']) && $brandExpense['platform_id']==$p['id']) ?>>
          <?= esc($p['code']) ?> – <?= esc($p['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Jumlah (Rp)</label>
      <input type="text"
             id="amount"
             name="amount"
             class="form-control text-end"
             required
             value="<?= old('amount',
               isset($brandExpense['amount'])
                 ? number_format($brandExpense['amount'],2,',','.')
                 : ''
             ) ?>">
    </div>
  </div>

  <div class="mb-4">
    <label class="form-label">Tipe</label>
    <select name="type" class="form-select" required>
      <option value="">Pilih Tipe</option>
      <?php foreach(['Request','Debit Saldo Akun'] as $t): ?>
      <option value="<?= $t ?>"
        <?= set_select('type',$t, isset($brandExpense['type']) && $brandExpense['type']===$t) ?>>
        <?= esc($t) ?>
      </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="text-end">
    <a href="<?= site_url('brand-expenses') ?>" class="btn btn-secondary me-2">Batal</a>
    <button type="submit" class="btn btn-primary">
      <?= ($mode==='edit') ? 'Perbarui' : 'Simpan' ?>
    </button>
  </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// 1) Format ribuan Indonesia
const amt  = document.getElementById('amount'),
      form = document.getElementById('brandExpenseForm');

function fmt(v){
  const num = v.replace(/\D/g,'');
  return num ? new Intl.NumberFormat('id-ID').format(num) : '';
}
amt.addEventListener('input', e => {
  const pos = e.target.selectionStart;
  e.target.value = fmt(e.target.value);
  e.target.setSelectionRange(pos,pos);
});
form.addEventListener('submit', () => {
  amt.value = amt.value.replace(/\D/g,'');
});

// 2) Filter dropdown Toko berdasarkan Brand
const brandSelect = document.getElementById('brand_id');
const storeSelect = document.getElementById('store_id');
const storeOptions = Array.from(storeSelect.options);

function filterStores() {
  const selectedBrand = brandSelect.value;
  // reset pilihan
  storeSelect.value = '';
  // tampilkan/sempunyikan opsi
  storeOptions.forEach(opt => {
    if (!opt.value) {
      // opsi default "Pilih Toko"
      opt.hidden = false;
    } else {
      opt.hidden = (opt.dataset.brandId !== selectedBrand);
    }
  });
}

brandSelect.addEventListener('change', filterStores);
// saat halaman edit load, langsung filter
window.addEventListener('DOMContentLoaded', filterStores);
</script>
<?= $this->endSection() ?>
