<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Edit Role - <?= esc($role['role_name']) ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('roles') ?>">Manajemen Role</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Role</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Edit Role - <?= esc($role['role_name']) ?></h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Form Edit Role</div>
            </div>
            <div class="card-body">
                <form action="<?= base_url('roles/update/' . $role['id']) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nama Role</label>
                        <input type="text" name="role_name" class="form-control" value="<?= old('role_name', $role['role_name']) ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Update
                    </button>
                    <a href="<?= base_url('roles') ?>" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
