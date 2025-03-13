<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Manajemen Roles <?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Manajemen Pengguna</a></li>
                <li class="breadcrumb-item active" aria-current="page">Roles</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Manajemen Roles</h1>
    </div>
    <div class="btn-list">
        <a href="<?= base_url('roles/create') ?>" class="btn btn-primary btn-wave">
            <i class="ri-add-line me-1"></i> Tambah Role
        </a>
        <a href="<?= base_url('permissions/assign') ?>" class="btn btn-secondary btn-wave">
            <i class="ri-settings-4-line me-1"></i> Kelola Assign Permissions
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table text-nowrap table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $index => $role): ?>
                <tr>
                    <th scope="row"><?= $index + 1 ?></th>
                    <td><?= esc($role['role_name']) ?></td>
                    <td>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="ri-more-2-fill"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item text-info" href="<?= base_url('roles/edit/' . $role['id']) ?>">
                                        <i class="ri-edit-line"></i> Edit
                                    </a>
                                </li>
                                <li>
                                    <form action="<?= base_url('roles/delete/' . $role['id']) ?>" method="post" class="d-inline" onsubmit="return confirmDelete(event, this)">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="ri-delete-bin-5-line"></i> Hapus
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function confirmDelete(event, form) {
        event.preventDefault();
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data role ini akan dihapus secara permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
</script>

<?= $this->endSection() ?>
