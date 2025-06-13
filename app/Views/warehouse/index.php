<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Daftar Warehouse
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('warehouse') ?>">Manajemen Warehouse</a></li>
                <li class="breadcrumb-item active" aria-current="page">Daftar Warehouse</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0"><i class="ri-building-4-line"></i> Daftar Warehouse</h1>
    </div>
    <div class="btn-list">
        <a href="<?= base_url('warehouse/create') ?>" class="btn btn-primary btn-wave">
            <i class="ri-add-line me-1"></i> Tambah Warehouse
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
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Alamat</th>
                        <th>Jenis</th>
                        <th>PIC</th>
                        <th>Kontak</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
    <?php foreach ($warehouses as $index => $warehouse): ?>
        <tr>
            <th scope="row"><?= $index + 1 ?></th>
            <td><?= esc($warehouse['code']) ?></td> <!-- ✅ Tambahan -->
            <td><?= esc($warehouse['name']) ?></td>
            <td><?= esc($warehouse['address']) ?></td>
            <td>
                <span class="badge bg-outline-<?= $warehouse['warehouse_type'] == 'Internal' ? 'primary' : 'success' ?>">
                    <?= esc($warehouse['warehouse_type']) ?>
                </span>
            </td>
            <td><?= esc($warehouse['pic_name']) ?></td>
            <td><?= esc($warehouse['pic_contact']) ?></td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="ri-more-2-fill"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item text-info" href="<?= base_url('warehouse/edit/' . $warehouse['id']) ?>">
                                <i class="ri-edit-line"></i> Edit
                            </a>
                        </li>
                        <li>
                            <form action="<?= base_url('warehouse/delete/' . $warehouse['id']) ?>" method="post" class="d-inline" onsubmit="return confirmDelete(event, this)">
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
            text: "Data warehouse ini akan dihapus secara permanen!",
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
