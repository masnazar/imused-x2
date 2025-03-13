<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Edit Permission
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('permissions') ?>">Manajemen Permission</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Permission</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Edit Permission</h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Form Edit Permission</div>
            </div>
            <div class="card-body">
                <form action="<?= base_url('permissions/update/' . $permission['id']) ?>" method="post">
                    <?= csrf_field() ?>

                    <div class="form-floating mb-3">
                        <input type="text" name="permission_name" class="form-control" id="permissionName" 
                               value="<?= old('permission_name', $permission['permission_name']) ?>" required>
                        <label for="permissionName">Nama Permission</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" name="alias" class="form-control" id="alias" 
                               value="<?= old('alias', $permission['alias']) ?>" required>
                        <label for="alias">Alias Permission</label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Perbarui
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
