<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
    <?= ($mode === 'create') ? 'Tambah Akun' : 'Edit Akun' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('chart-of-accounts') ?>">Daftar Akun</a></li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= ($mode === 'create') ? 'Tambah Akun' : 'Edit Akun' ?>
                </li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">
            <?= ($mode === 'create') ? 'Tambah Akun' : 'Edit Akun' ?>
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">
                    Form <?= ($mode === 'create') ? 'Tambah' : 'Edit' ?> Akun
                </div>
            </div>
            <div class="card-body">
                <form
                    action="<?= ($mode === 'create') 
                        ? site_url('chart-of-accounts/store')
                        : site_url("chart-of-accounts/update/{$account->id}") ?>"
                    method="post"
                >
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="code" class="form-label fw-semibold">Kode Akun</label>
                        <input
                            type="text"
                            id="code"
                            name="code"
                            class="form-control"
                            required
                            value="<?= set_value('code', $account->code ?? '') ?>"
                        >
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Nama Akun</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control"
                            required
                            value="<?= set_value('name', $account->name ?? '') ?>"
                        >
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label fw-semibold">Tipe</label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="">Pilih Tipe</option>
                            <?php foreach (['Asset','Liability','Equity','Revenue','Expense'] as $t): ?>
                                <option value="<?= $t ?>"
                                    <?= set_select('type', $t, (isset($account) && $account->type === $t)) ?>>
                                    <?= esc($t) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="subtype" class="form-label fw-semibold">Subtipe</label>
                        <select name="subtype" id="subtype" class="form-select">
                            <option value="">— None —</option>
                            <?php foreach (['Operasional','Marketing','Human Resource','Lain-Lain'] as $s): ?>
                                <option value="<?= $s ?>"
                                    <?= set_select('subtype', $s, (isset($account) && $account->subtype === $s)) ?>>
                                    <?= esc($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="normal_balance" class="form-label fw-semibold">Saldo Normal</label>
                        <select name="normal_balance" id="normal_balance" class="form-select" required>
                            <option value="">Pilih Saldo Normal</option>
                            <?php foreach (['Debit','Credit'] as $b): ?>
                                <option value="<?= $b ?>"
                                    <?= set_select('normal_balance', $b, (isset($account) && $account->normal_balance === $b)) ?>>
                                    <?= esc($b) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="parent_id" class="form-label fw-semibold">Parent (opsional)</label>
                        <select name="parent_id" id="parent_id" class="form-select">
                            <option value="">— None —</option>
                            <?php foreach ($parents as $p): ?>
                                <option value="<?= $p['id'] ?>"
                                    <?= set_select('parent_id', $p['id'], (isset($account) && $account->parent_id == $p['id'])) ?>>
                                    <?= esc($p['code'].' – '.$p['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="ri-save-line"></i>
                            <?= ($mode === 'create') ? 'Simpan' : 'Perbarui' ?>
                        </button>
                        <a href="<?= site_url('chart-of-accounts') ?>" class="btn btn-secondary">
                            <i class="ri-arrow-left-line"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
