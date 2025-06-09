<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card p-4 shadow rounded-2xl">
  <h2 class="text-xl font-bold mb-4">📮 Postal Code Updater</h2>

  <?php if (!empty($message)) : ?>
    <div class="bg-green-100 p-2 rounded mb-3 border-l-4 border-green-500 text-green-700">
        <strong>Info:</strong> <?= esc($message) ?>
    </div>
  <?php endif; ?>

  <?php if (session()->getFlashdata('list')) : ?>
  <div class="bg-white shadow p-3 mt-4 rounded border text-sm">
    <strong>✅ Detail Desa yang Diupdate:</strong>
    <ul class="mt-2 list-disc list-inside space-y-1 max-h-[300px] overflow-auto text-gray-700">
      <?php foreach (session()->getFlashdata('list') as $item) : ?>
        <li><?= esc($item['village']) ?>, <?= esc($item['district']) ?> - <code><?= esc($item['postal_code']) ?></code></li>
      <?php endforeach ?>
    </ul>
  </div>
<?php endif ?>


  <?php if (!empty($log)) : ?>
    <div class="bg-gray-100 p-2 rounded mb-3 border-l-4 border-gray-400 text-sm text-gray-700">
        <strong>Debug:</strong> <?= esc($log) ?>
    </div>
  <?php endif; ?>

  <ul class="mb-4 space-y-1">
    <li>✅ <strong>Sudah terisi:</strong> <?= number_format($updated) ?></li>
    <li>❌ <strong>Belum terisi:</strong> <?= number_format($not_updated) ?></li>
  </ul>

  <form method="post" action="<?= base_url('postalcode/runBatch') ?>">
    <?= csrf_field() ?>
    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded w-full sm:w-auto">
      🚀 Jalankan Batch Update Sekarang
    </button>
  </form>
</div>

<?= $this->endSection() ?>
