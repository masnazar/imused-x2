<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
  <?= ($mode==='edit')?'Edit Brand Expense':'Tambah Brand Expense' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header & Breadcrumb -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
  <div>
    <nav>
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= site_url('brand-expenses') ?>">Brand Expenses</a></li>
        <li class="breadcrumb-item active"><?= ($mode==='edit')?'Edit':'Tambah' ?> Brand Expense</li>
      </ol>
    </nav>
    <h1 class="page-title fw-medium fs-18 mb-0">
      <?= ($mode==='edit')?'Edit Brand Expense':'Tambah Brand Expense' ?>
    </h1>
  </div>
</div>

<div class="row">
  <div class="col-lg-12">
    <div class="card custom-card">
      <div class="card-header">
        <div class="card-title">
          <?= ($mode==='edit')?'Form Edit':'Form Tambah' ?> Brand Expense
        </div>
      </div>
      <div class="card-body">
        <form id="brandExpenseForm"
              action="<?= ($mode==='edit')
                ? site_url("brand-expenses/update/{$brandExpense['id']}")
                : site_url('brand-expenses/store') ?>"
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
                <?= set_select('brand_id',$b['id'], isset($brandExpense['brand_id'])&&$brandExpense['brand_id']==$b['id'])?>>
                <?= esc($b['brand_name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Tanggal -->
          <div class="mb-3">
            <label for="date" class="form-label fw-semibold">Tanggal</label>
            <input type="date" id="date" name="date" class="form-control" required
              value="<?= old('date',$brandExpense['date'] ?? date('Y-m-d')) ?>">
          </div>

          <!-- Deskripsi -->
          <div class="mb-3">
            <label for="description" class="form-label fw-semibold">Deskripsi</label>
            <textarea id="description" name="description" class="form-control" rows="3"><?= old('description',$brandExpense['description']??'')?></textarea>
          </div>

          <!-- COA -->
          <div class="mb-3">
            <label for="coa_id" class="form-label fw-semibold">COA</label>
            <select name="coa_id" id="coa_id" class="form-select" required>
              <option value="">Pilih COA</option>
              <?php foreach($coas as $c): ?>
              <option value="<?= $c['id'] ?>"
                <?= set_select('coa_id',$c['id'], isset($brandExpense['coa_id'])&&$brandExpense['coa_id']==$c['id'])?>>
                <?= esc($c['code']) ?> – <?= esc($c['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Platform -->
          <div class="mb-3">
            <label for="platform_id" class="form-label fw-semibold">Platform (opsional)</label>
            <select name="platform_id" id="platform_id" class="form-select">
              <option value="">Pilih Platform</option>
              <?php foreach($platforms as $p): ?>
              <option value="<?= $p['id'] ?>"
                <?= set_select('platform_id',$p['id'], isset($brandExpense['platform_id'])&&$brandExpense['platform_id']==$p['id'])?>>
                <?= esc($p['code']) ?> – <?= esc($p['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Jumlah -->
          <div class="mb-3">
            <label for="amount" class="form-label fw-semibold">Jumlah (Rp)</label>
            <input type="text" id="amount" name="amount" class="form-control text-end" required
              value="<?= old('amount', isset($brandExpense['amount'])?number_format($brandExpense['amount'],2,',','.'):'') ?>">
          </div>

          <!-- Tipe -->
          <div class="mb-3">
            <label for="type" class="form-label fw-semibold">Tipe</label>
            <select name="type" id="type" class="form-select" required>
              <option value="">Pilih Tipe</option>
              <?php foreach(['Request','Debit Saldo Akun'] as $_t): ?>
              <option value="<?= $_t ?>"
                <?= set_select('type',$_t, isset($brandExpense['type'])&&$brandExpense['type']===$_t) ?>>
                <?= esc($_t) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Tombol -->
          <div class="d-flex justify-content-end">
            <a href="<?= site_url('brand-expenses') ?>" class="btn btn-secondary me-2">
              <i class="ri-arrow-left-line"></i> Batal
            </a>
            <button type="submit" class="btn btn-primary">
              <i class="ri-save-line"></i> <?= ($mode==='edit')?'Perbarui':'Simpan' ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// format input jumlah
const amt = document.getElementById('amount'),
      form = document.getElementById('brandExpenseForm');
function fmt(v){ let n=v.replace(/\D/g,''); return n?new Intl.NumberFormat('id-ID').format(n):''; }
amt.addEventListener('input', e=>{
  const p=e.target.selectionStart;
  e.target.value = fmt(e.target.value);
  e.target.setSelectionRange(p,p);
});
form.addEventListener('submit', ()=>{ amt.value = amt.value.replace(/\D/g,''); });
</script>
<?= $this->endSection() ?>
