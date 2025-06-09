<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card p-4 rounded-2xl shadow">
  <h2 class="text-xl font-bold mb-4">📦 Referensi Data Kodepos</h2>

  <div class="overflow-x-auto">
    <table class="table-auto w-full text-sm">
      <thead>
        <tr class="bg-gray-100 text-left">
          <th class="p-2">#</th>
          <th class="p-2">Kelurahan</th>
          <th class="p-2">Kecamatan</th>
          <th class="p-2">Kabupaten</th>
          <th class="p-2">Provinsi</th>
          <th class="p-2">Kodepos</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($kodepos as $i => $row): ?>
        <tr class="border-b hover:bg-gray-50">
          <td class="p-2"><?= $i + 1 ?></td>
          <td class="p-2"><?= esc($row->kelurahan) ?></td>
          <td class="p-2"><?= esc($row->kecamatan) ?></td>
          <td class="p-2"><?= esc($row->kabupaten) ?></td>
          <td class="p-2"><?= esc($row->provinsi) ?></td>
          <td class="p-2 font-semibold text-blue-600"><?= esc($row->kodepos) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
