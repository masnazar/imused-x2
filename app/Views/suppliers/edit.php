<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?> Edit Supplier <?= $this->endSection() ?>
<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('suppliers') ?>">Manajemen Supplier</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit Supplier</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Edit Supplier</h1>
    </div>
</div>

<!-- Card Form -->
<div class="card custom-card">
    <div class="card-header justify-content-between">
        <h5 class="card-title">Edit Supplier</h5>
    </div>
    <div class="card-body">
        <?= view('suppliers/_form', ['action' => base_url('suppliers/update/' . $supplier['id']), 'supplier' => $supplier]) ?>
    </div>
</div>

<?= $this->endSection() ?>
