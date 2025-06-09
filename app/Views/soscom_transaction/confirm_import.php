<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header-breadcrumb d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div>
    <nav>
      <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('soscom-transactions') ?>">Soscom Transactions</a></li>
        <li class="breadcrumb-item active">Konfirmasi Import</li>
      </ol>
    </nav>
    <h1 class="page-title fw-semibold fs-18 mb-0">🛍️ Konfirmasi Import Transaksi Soscom</h1>
  </div>
</div>

<div class="card custom-card">
  <div class="card-body">
    <form method="post" action="<?= base_url('soscom-transactions/save-import') ?>">
      <?= csrf_field() ?>
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle text-nowrap">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Tanggal</th>
              <th>Nama Customer</th>
              <th>No HP</th>
              <th>Kota</th>
              <th>Provinsi</th>
              <th>SKU</th>
              <th class="text-end">Qty</th>
              <th class="text-end">Harga</th>
              <th class="text-end">HPP</th>
              <th>Metode Bayar</th>
              <th class="text-end">Ongkir</th>
              <th class="text-end">COD Fee</th>
              <th class="text-end">Total Bayar</th>
              <th class="text-end text-success">Profit</th>
              <th>Resi</th>
              <th>Courier</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($importedData as $i => $row): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td>
                  <?php
                    $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                    $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                    $dateObj = date_create($row['date']);
                    echo $dateObj ? $hari[$dateObj->format('w')] . ', ' . $dateObj->format('d') . ' ' . $bulan[$dateObj->format('n') - 1] . ' ' . $dateObj->format('Y') : '-';
                  ?>
                </td>
                <td><?= esc($row['customer_name']) ?></td>
                <td><?= esc($row['phone_number']) ?></td>
                <td><?= esc($row['city']) ?></td>
                <td><?= esc($row['province']) ?></td>
                <td><span class="badge bg-dark"><?= esc($row['sku']) ?></span></td>
                <td class="text-end"><?= number_format((float) $row['quantity'], 0, ',', '.') ?></td>
                <td class="text-end"><?= number_format((float) $row['selling_price'], 0, ',', '.') ?></td>
                <td class="text-end"><?= number_format((float) $row['hpp'], 0, ',', '.') ?></td>
                <td><?= esc($row['payment_method']) ?></td>
                <td class="text-end"><?= number_format((float) $row['shipping_cost'], 0, ',', '.') ?></td>
                <td class="text-end"><?= number_format((float) $row['cod_fee'], 0, ',', '.') ?></td>
                <td class="text-end"><?= number_format((float) $row['total_payment'], 0, ',', '.') ?></td>
                <td class="text-end text-success"><?= number_format((float) $row['estimated_profit'], 0, ',', '.') ?></td>
                <td><?= esc($row['tracking_number']) ?></td>
                <td><?= esc($row['courier_code']) ?> (<?= esc($row['courier_id']) ?>)</td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>

      <div class="mt-4 d-flex justify-content-between">
        <a href="<?= base_url('soscom-transactions') ?>" class="btn btn-secondary">
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
