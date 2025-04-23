<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Detail Transaksi<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="page-header mb-4">
  <h1 class="page-title">📄 Detail Transaksi Soscom</h1>
</div>

<div class="card">
  <div class="card-body row">
    <div class="col-md-6">
      <h5>Info Transaksi</h5>
      <p><strong>Order:</strong> <?= esc($transaction['order_number']) ?></p>
      <p><strong>Tanggal:</strong> <?= date('d M Y', strtotime($transaction['date'])) ?></p>
      <p><strong>Customer:</strong> <?= esc($transaction['customer_name']) ?> (<?= esc($transaction['phone_number']) ?>)</p>
      <p><strong>Kota/Prov:</strong> <?= esc($transaction['city']) ?>, <?= esc($transaction['province']) ?></p>
      <p><strong>Leads:</strong> <?= esc($transaction['lead_source']) ?> | <?= esc($transaction['order_type']) ?></p>
    </div>

    <div class="col-md-6">
      <h5>Ringkasan</h5>
      <p><strong>Qty:</strong> <?= $transaction['total_qty'] ?> pcs</p>
      <p><strong>Harga Jual:</strong> Rp<?= number_format($transaction['selling_price'], 0, ',', '.') ?></p>
      <p><strong>COD Fee:</strong> Rp<?= number_format($transaction['cod_fee'], 0, ',', '.') ?></p>
      <p><strong>Admin Fee:</strong> Rp<?= number_format($transaction['admin_fee'], 0, ',', '.') ?></p>
      <p><strong>Diskon:</strong> Rp<?= number_format($transaction['discount'], 0, ',', '.') ?></p>
      <p><strong>Net Revenue:</strong> Rp<?= number_format($transaction['net_revenue'], 0, ',', '.') ?></p>
    </div>
  </div>
</div>

<!-- 🧾 Produk -->
<div class="card mt-4">
  <div class="card-body">
    <h5>Detail Produk</h5>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>SKU</th>
          <th>Nama Produk</th>
          <th>Qty</th>
          <th>Harga Satuan</th>
          <th>Total HPP</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($products as $p): ?>
          <tr>
            <td><?= esc($p['sku']) ?></td>
            <td><?= esc($p['nama_produk']) ?></td>
            <td><?= $p['quantity'] ?></td>
            <td>Rp<?= number_format($p['unit_selling_price'], 0, ',', '.') ?></td>
            <td>Rp<?= number_format($p['total_hpp'], 0, ',', '.') ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
