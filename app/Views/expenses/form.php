<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
    <?= ($mode === 'edit') ? 'Edit Pengeluaran' : 'Tambah Pengeluaran' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('expenses') ?>">Pengeluaran</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= ($mode === 'edit') ? 'Edit Pengeluaran' : 'Tambah Pengeluaran' ?>
                </li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">
            <?= ($mode === 'edit') ? 'Edit Pengeluaran' : 'Tambah Pengeluaran' ?>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">
                    <?= ($mode === 'edit') ? 'Form Edit Pengeluaran' : 'Form Tambah Pengeluaran' ?>
                </div>
            </div>
            <div class="card-body">
                <form id="expenseForm"
                      action="<?= ($mode === 'edit')
                        ? site_url("expenses/update/{$expense['id']}")
                        : site_url('expenses/store') ?>"
                      method="post"
                >
                    <?= csrf_field() ?>

                    <!-- Tanggal -->
                    <div class="mb-3">
                        <label for="date" class="form-label fw-semibold">Tanggal</label>
                        <input type="date" id="date" name="date" class="form-control" required
                               value="<?= old('date', $expense['date'] ?? date('Y-m-d')) ?>">
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Deskripsi</label>
                        <input type="text" id="description" name="description" class="form-control" required
                               value="<?= old('description', $expense['description'] ?? '') ?>">
                    </div>

                    <!-- COA -->
                    <div class="mb-3">
                        <label for="account_id" class="form-label fw-semibold">COA (Chart of Accounts)</label>
                        <select name="account_id" id="account_id" class="form-select" required>
                            <option value="">Pilih COA</option>
                            <?php foreach ($coas as $coa): ?>
                                <option value="<?= $coa['id'] ?>"
                                    <?= set_select('account_id', $coa['id'], (isset($expense) && $expense['account_id']==$coa['id'])) ?>>
                                    <?= esc($coa['code']) ?> – <?= esc($coa['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Brand -->
                    <div class="mb-3">
                        <label for="brand_id" class="form-label fw-semibold">Brand (opsional)</label>
                        <select name="brand_id" id="brand_id" class="form-select">
                            <option value="">Pilih Brand</option>
                            <?php foreach ($brands as $brand): ?>
                                <option value="<?= $brand['id'] ?>"
                                    <?= set_select('brand_id', $brand['id'], (isset($expense) && $expense['brand_id']==$brand['id'])) ?>>
                                    <?= esc($brand['brand_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Platform -->
                    <div class="mb-3">
                        <label for="platform_id" class="form-label fw-semibold">Platform (opsional)</label>
                        <select name="platform_id" id="platform_id" class="form-select">
                            <option value="">Pilih Platform</option>
                            <?php foreach ($platforms as $plat): ?>
                                <option value="<?= $plat['id'] ?>"
                                    <?= set_select('platform_id', $plat['id'], (isset($expense) && $expense['platform_id']==$plat['id'])) ?>>
                                    <?= esc($plat['code']) ?> – <?= esc($plat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tipe Proses -->
                    <div class="mb-3">
                        <label for="type" class="form-label fw-semibold">Tipe Proses</label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="">Pilih Tipe Proses</option>
                            <?php foreach (['Request','Debit Saldo Akun'] as $t): ?>
                                <option value="<?= $t ?>"
                                    <?= set_select('type', $t, (isset($expense) && $expense['type']===$t)) ?>>
                                    <?= esc($t) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Jumlah dengan pemisah ribuan -->
                    <div class="mb-3">
                        <label for="amount" class="form-label fw-semibold">Jumlah (Rp)</label>
                        <input type="text" id="amount" name="amount"
                               class="form-control text-end"
                               required
                               value="<?= old('amount',
                                   isset($expense['amount'])
                                       ? number_format($expense['amount'], 2, ',', '.')
                                       : ''
                               ) ?>">
                    </div>

                    <!-- Tombol -->
                    <div class="d-flex justify-content-end">
                        <a href="<?= site_url('expenses') ?>" class="btn btn-secondary me-2">
                            <i class="ri-arrow-left-line"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-line"></i>
                            <?= ($mode === 'edit') ? 'Perbarui' : 'Simpan' ?>
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
// ambil elemen
const amountField = document.getElementById('amount'),
      form         = document.getElementById('expenseForm');

// format ribuan ala Indonesia
function formatRibuan(v) {
  // ambil digit saja
  const num = v.replace(/\D/g, '');
  return num
    ? new Intl.NumberFormat('id-ID').format(num)
    : '';
}

// saat user ketik di jumlah
amountField.addEventListener('input', e => {
  const pos = e.target.selectionStart;
  e.target.value = formatRibuan(e.target.value);
  // posisinya tetap di cursor
  e.target.setSelectionRange(pos, pos);
});

// sebelum submit: buang semua non-digit
form.addEventListener('submit', () => {
  amountField.value = amountField.value.replace(/\D/g, '');
});
</script>
<?= $this->endSection() ?>
