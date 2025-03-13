<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Tambah Role Baru
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('roles') ?>">Manajemen Role</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Role</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Tambah Role Baru</h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Form Tambah Role</div>
            </div>
            <div class="card-body">
            <form action="<?= base_url('roles/store') ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">


                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Role</label>
                        <input type="text" name="role_name" class="form-control" value="<?= old('role_name') ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Simpan
                    </button>
                    <a href="<?= base_url('roles') ?>" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
