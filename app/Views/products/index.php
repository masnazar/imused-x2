<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Daftar Produk
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('products') ?>">Manajemen Produk</a></li>
                <li class="breadcrumb-item active" aria-current="page">Daftar Produk</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Daftar Produk</h1>
    </div>
    <div class="btn-list">
        <a href="<?= base_url('products/create') ?>" class="btn btn-primary btn-wave">
            <i class="ri-add-line me-1"></i> Tambah Produk
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
                        <th>Nama Produk</th>
                        <th>Brand</th>
                        <th>SKU</th>
                        <th>Stock</th>
                        <th>HPP</th>
                        <th>Total Nilai Stok</th>
                        <th>No BPOM</th>
                        <th>No Halal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $index => $product): ?>
                        <tr>
                            <th scope="row"><?= $index + 1 ?></th>
                            <td><?= esc($product['nama_produk']) ?></td>
                            <td><?= esc($product['brand_name']) ?></td>
                            <td><?= esc($product['sku']) ?></td>
                            <td><?= esc($product['stock']) ?></td>
                            <td>Rp <?= number_format($product['hpp'], 2, ',', '.') ?></td>
                            <td>Rp <?= number_format($product['total_nilai_stok'], 2, ',', '.') ?></td>
                            <td><?= esc($product['no_bpom']) ?></td>
                            <td><?= esc($product['no_halal']) ?></td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="ri-more-2-fill"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item text-info" href="<?= base_url('products/edit/' . $product['id']) ?>">
                                                <i class="ri-edit-line"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <form action="<?= base_url('products/delete/' . $product['id']) ?>" method="post" class="d-inline" onsubmit="return confirmDelete(event, this)">
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
            text: "Data produk ini akan dihapus secara permanen!",
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
