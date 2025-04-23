<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Konfirmasi Import Transaksi<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="page-header mb-4">
  <h1 class="page-title">✅ Konfirmasi Import Transaksi Soscom</h1>
</div>

<form action="<?= base_url('soscom-transactions/save-import') ?>" method="post">
  <?= csrf_field() ?>
  <div class="alert alert-warning">Pastikan semua data sudah benar sebelum disimpan.</div>
  <button type="submit" class="btn btn-primary mb-3">Simpan Semua Transaksi</button>

  <table class="table table-bordered">
    <thead class="table-light">
      <tr>
        <th>Tanggal</th>
        <th>Order #</th>
        <th>Customer</th>
        <th>WA</th>
        <th>Kota/Prov</th>
        <th>Leads</th>
        <th>Order Type</th>
        <th>Qty</th>
        <th>Total</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($importedData as $d): ?>
        <tr>
          <td><?= esc($d['date']) ?></td>
          <td><?= esc($d['order_number']) ?></td>
          <td><?= esc($d['customer_name']) ?></td>
          <td><?= esc($d['phone_number']) ?></td>
          <td><?= esc($d['city']) ?> / <?= esc($d['province']) ?></td>
          <td><?= esc($d['lead_source']) ?></td>
          <td><?= esc($d['order_type']) ?></td>
          <td><?= esc($d['total_qty']) ?> pcs</td>
          <td>Rp<?= number_format($d['selling_price'], 0, ',', '.') ?></td>
          <td><span class="badge bg-success"><?= esc($d['status']) ?></span></td>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</form>

<?= $this->endSection() ?>
