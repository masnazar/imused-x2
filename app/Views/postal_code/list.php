<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="card p-4 shadow rounded-2xl">
  <h2 class="text-xl font-bold mb-4">📌 Data Wilayah Indonesia + Kode Pos</h2>

  <div class="overflow-x-auto">
    <table id="tableWilayah" class="table table-bordered w-full">
      <thead class="bg-gray-100">
        <tr>
          <th>Provinsi</th>
          <th>Kabupaten</th>
          <th>Kecamatan</th>
          <th>Desa</th>
          <th>Kode Pos</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    $('#tableWilayah').DataTable({
        processing: true,
        serverSide: true,
        ajax: "<?= base_url('postalcode/ajaxList') ?>",
        columns: [
            { data: 'province' },
            { data: 'regency' },
            { data: 'district' },
            { data: 'village' },
            { data: 'postal_code' }
        ]
    });
});
</script>
<?= $this->endSection() ?>
