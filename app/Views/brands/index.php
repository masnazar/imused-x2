<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Daftar Brand
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('brands') ?>">Manajemen Brand</a></li>
                <li class="breadcrumb-item active" aria-current="page">Daftar Brand</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Daftar Brand</h1>
    </div>
    <div class="btn-list">
        <a href="<?= base_url('brands/create') ?>" class="btn btn-primary btn-wave">
            <i class="ri-add-line me-1"></i> Tambah Brand
        </a> 
    </div>
</div>

<div class="card custom-card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table text-nowrap table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Brand</th>
                        <th>Kode Brand</th>
                        <th>Supplier</th>
                        <th>Warna</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($brands as $index => $brand): ?>
                        <tr>
                            <th scope="row"><?= $index + 1 ?></th>
                            <td><?= esc($brand['brand_name']) ?></td>
                            <td><?= esc($brand['kode_brand']) ?></td>
                            <td><?= esc($brand['supplier_name']) ?></td>
                            <td>
                                <span class="badge" style="background-color: <?= esc($brand['primary_color']) ?>;">
                                    <?= esc($brand['primary_color']) ?>
                                </span>
                                <span class="badge" style="background-color: <?= esc($brand['secondary_color']) ?>;">
                                    <?= esc($brand['secondary_color']) ?>
                                </span>
                                <span class="badge" style="background-color: <?= esc($brand['accent_color']) ?>;">
                                    <?= esc($brand['accent_color']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item text-info" href="<?= base_url('brands/edit/' . $brand['id']) ?>">
                                                <i class="ri-edit-line"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <form action="<?= base_url('brands/delete/' . $brand['id']) ?>" method="post" class="d-inline" onsubmit="return confirmDelete(event, this)">
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
    </div>
</div>

<script>
    function confirmDelete(event, form) {
        event.preventDefault();
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data brand ini akan dihapus secara permanen!",
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
