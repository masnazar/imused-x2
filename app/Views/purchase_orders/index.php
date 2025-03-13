<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="page-header">
    <h1>Purchase Orders</h1>
    <a href="<?= base_url('purchase-orders/create') ?>" class="btn btn-primary">Tambah PO</a>
</div>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>No</th>
                <th>Nomor PO</th>
                <th>Supplier</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($purchaseOrders as $index => $po) : ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= esc($po['po_number']) ?></td>
                    <td><?= esc($po['supplier_name']) ?></td>
                    <td><?= esc($po['status']) ?></td>
                    <td>
                        <a href="<?= base_url('purchase-orders/edit/' . $po['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                        <form action="<?= base_url('purchase-orders/delete/' . $po['id']) ?>" method="post" onsubmit="return confirm('Yakin ingin hapus?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
