<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Tambah Permission
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('permissions') ?>">Manajemen Permission</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Permission</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Tambah Permission</h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Form Tambah Permission</div>
            </div>
            <div class="card-body">
                <form action="<?= base_url('permissions/store') ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="form-floating mb-3">
                        <input type="text" name="permission_name" class="form-control" id="permissionName" 
                               value="<?= old('permission_name') ?>" required>
                        <label for="permissionName">Nama Permission</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" name="alias" class="form-control" id="alias" 
                               value="<?= old('alias') ?>" required>
                        <label for="alias">Alias Permission</label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Simpan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
