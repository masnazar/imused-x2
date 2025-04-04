<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header-breadcrumb d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div>
    <nav>
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('marketplace-transactions/' . $platform) ?>">Marketplace Transactions</a></li>
        <li class="breadcrumb-item active">Konfirmasi Import</li>
      </ol>
    </nav>
    <h1 class="page-title fw-semibold fs-18 mb-0">📦 Konfirmasi Import Transaksi <?= ucfirst($platform) ?></h1>
  </div>
</div>

<div class="card custom-card">
  <div class="card-body">
    <form method="post" action="<?= base_url('marketplace-transactions/save-imported-data/' . $platform) ?>">
      <?= csrf_field() ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-nowrap">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Tanggal</th>
              <th>Order</th>
              <th>Resi</th>
              <th>SKU</th>
              <th>Qty</th>
              <th>Harga Jual</th>
              <th>Diskon</th>
              <th>Admin Fee</th>
              <th>Store</th>
              <th>Brand</th>
              <th>Warehouse</th>
              <th>Courier</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($importedData as $i => $row): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td><?= esc($row['date']) ?></td>
                <td><?= esc($row['order_number']) ?></td>
                <td><?= esc($row['tracking_number']) ?></td>
                <td><span class="badge bg-dark"><?= esc($row['sku']) ?></span></td>
                <td class="text-end"><?= esc($row['quantity']) ?></td>
                <td class="text-end"><?= number_format($row['selling_price'], 0, ',', '.') ?></td>
                <td class="text-end"><?= number_format($row['discount'], 0, ',', '.') ?></td>
                <td class="text-end"><?= number_format($row['admin_fee'], 0, ',', '.') ?></td>
                <td><?= esc($row['store_name']) ?></td>
                <td><?= esc($row['brand_id']) ?></td>
                <td><?= esc($row['warehouse_code']) ?> (<?= esc($row['warehouse_id']) ?>)</td>
                <td><?= esc($row['courier_code']) ?> (<?= esc($row['courier_id']) ?>)</td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>

      <div class="mt-4 d-flex justify-content-between">
        <a href="<?= base_url('marketplace-transactions/' . $platform) ?>" class="btn btn-secondary">
          <i class="ti ti-arrow-left"></i> Kembali
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="ti ti-device-floppy"></i> Simpan Semua Transaksi
        </button>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
