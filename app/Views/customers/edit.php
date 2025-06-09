<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Edit Customer<?= $this->endSection() ?>
<?= $this->section('styles'); ?>

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">

<?= $this->endSection('styles'); ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
  <div>
    <nav>
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('customers') ?>">Data Customer</a></li>
        <li class="breadcrumb-item active">Edit Customer</li>
      </ol>
    </nav>
    <h1 class="page-title fw-medium fs-18 mb-0"><i class="fas fa-edit me-2"></i> Edit Customer</h1>
  </div>
</div>

<div class="card custom-card shadow-sm">
  <div class="card-body">
    <?php if (session('errors')): ?>
      <div class="alert alert-danger">
        <ul>
          <?php foreach (session('errors') as $error): ?>
            <li><?= esc($error) ?></li>
          <?php endforeach ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url("customers/update/{$customer['id']}") ?>">
      <?= csrf_field() ?>
      <div class="row g-4">
        <div class="col-md-6">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="name" class="form-control" value="<?= esc($customer['name']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Jenis Kelamin</label>
          <select name="gender" class="form-select" required>
            <option value="L" <?= $customer['gender'] === 'L' ? 'selected' : '' ?>>Laki-laki</option>
            <option value="P" <?= $customer['gender'] === 'P' ? 'selected' : '' ?>>Perempuan</option>
          </select>
        </div>

        <!-- Lokasi -->
        <?php
$selects = [
  'province' => 'Provinsi',
  'city'     => 'Kota / Kabupaten',
  'district' => 'Kecamatan',
  'village'  => 'Desa/Kelurahan'
];

foreach ($selects as $id => $label):
  $idField   = "{$id}_id";
  $nameField = $id; // kolom untuk label name (misal city, province, dll)
  $value     = $customer[$idField] ?? '';
  $labelText = $customer[$nameField] ?? '';
?>
<div class="col-md-6">
  <label class="form-label"><?= $label ?></label>
  <select id="<?= $id ?>Select" name="<?= $idField ?>" class="form-select select2" required data-placeholder="Pilih <?= $label ?>">
    <?php if ($value && $labelText): ?>
      <option value="<?= esc($value) ?>" selected><?= esc($labelText) ?></option>
    <?php else: ?>
      <option></option>
    <?php endif; ?>
  </select>
</div>
<?php endforeach; ?>


        <div class="col-md-6">
          <label class="form-label">Kode Pos</label>
          <input type="text" id="postalCode" name="postal_code" class="form-control" readonly value="<?= esc($customer['postal_code'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Tanggal Lahir</label>
          <input type="date" name="dob" id="dobInput" class="form-control" value="<?= $customer['dob'] ?? '' ?>">
          <small class="text-muted">Usia: <span id="ageDisplay">-</span> tahun</small>
        </div>
        <div class="col-md-12">
          <label class="form-label">Alamat Lengkap</label>
          <textarea name="address" rows="3" class="form-control" required><?= esc($customer['address'] ?? '') ?></textarea>
        </div>
        <div class="col-12 d-flex justify-content-between">
          <a href="<?= base_url('customers/detail/' . $customer['id']) ?>" class="btn btn-light">
            <i class="ti ti-arrow-left"></i> Batal
          </a>
          <button class="btn btn-primary">
            <i class="ti ti-device-floppy"></i> Simpan Perubahan
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function () {
  const selected = {
    province: "<?= esc($customer['province_id']) ?>",
    city: "<?= esc($customer['city_id']) ?>",
    district: "<?= esc($customer['district_id']) ?>",
    village: "<?= esc($customer['village_id']) ?>",
    postalCode: "<?= esc($customer['postal_code']) ?>"
  };

  function initSelect2() {
    $('.select2').select2({
      width: '100%',
      allowClear: true,
      placeholder: function() {
        return $(this).data('placeholder');
      }
    });
  }

  function loadSelect(url, el, selectedId, placeholder, callback) {
    if (!el.length) return;

    el.prop('disabled', true).html(`<option value="">${placeholder}</option>`);

    $.get(url)
      .done(data => {
        if (data && data.length > 0) {
          data.forEach(item => {
            const isSelected = (item.id == selectedId) ? 'selected' : '';
            el.append(`<option value="${item.id}" ${isSelected}>${item.name}</option>`);
          });
        } else {
          el.append(`<option value="">Data tidak tersedia</option>`);
        }
        if (typeof callback === 'function') callback();
      })
      .fail(err => {
        console.error(`❌ Gagal load ${url}`, err);
        el.append(`<option value="">Gagal load data</option>`);
      })
      .always(() => {
        el.prop('disabled', false);
      });
  }

  function restoreLocationSelections() {
    loadSelect(
      "<?= base_url('api/provinces') ?>",
      $('#provinceSelect'),
      selected.province,
      'Memuat provinsi...',
      function () {
        loadSelect(
          `<?= base_url('api/cities') ?>/${selected.province}`,
          $('#citySelect'),
          selected.city,
          'Memuat kota...',
          function () {
            loadSelect(
              `<?= base_url('api/districts') ?>/${selected.city}`,
              $('#districtSelect'),
              selected.district,
              'Memuat kecamatan...',
              function () {
                loadSelect(
                  `<?= base_url('api/villages') ?>/${selected.district}`,
                  $('#villageSelect'),
                  selected.village,
                  'Memuat desa...',
                  function () {
                    if (selected.village) {
                      $.get(`<?= base_url('api/postal-code') ?>/${selected.village}`)
                        .done(res => $('#postalCode').val(res?.postal_code || selected.postalCode));
                    } else {
                      $('#postalCode').val(selected.postalCode);
                    }
                  }
                );
              }
            );
          }
        );
      }
    );
  }

  // Event cascading
  $('#provinceSelect').on('change', function () {
    const val = $(this).val();
    $('#citySelect, #districtSelect, #villageSelect').html('<option value="">Loading...</option>');
    $('#postalCode').val('');
    if (val) {
      loadSelect(`<?= base_url('api/cities') ?>/${val}`, $('#citySelect'), null, 'Pilih Kota/Kabupaten');
    }
  });

  $('#citySelect').on('change', function () {
    const val = $(this).val();
    $('#districtSelect, #villageSelect').html('<option value="">Loading...</option>');
    $('#postalCode').val('');
    if (val) {
      loadSelect(`<?= base_url('api/districts') ?>/${val}`, $('#districtSelect'), null, 'Pilih Kecamatan');
    }
  });

  $('#districtSelect').on('change', function () {
    const val = $(this).val();
    $('#villageSelect').html('<option value="">Loading...</option>');
    $('#postalCode').val('');
    if (val) {
      loadSelect(`<?= base_url('api/villages') ?>/${val}`, $('#villageSelect'), null, 'Pilih Desa/Kelurahan');
    }
  });

  $('#villageSelect').on('change', function () {
    const val = $(this).val();
    $('#postalCode').val('');
    if (val) {
      $.get(`<?= base_url('api/postal-code') ?>/${val}`)
        .done(res => $('#postalCode').val(res?.postal_code || ''));
    }
  });

  function calculateAge() {
    const birthDate = new Date($('#dobInput').val());
    if (isNaN(birthDate)) {
      $('#ageDisplay').text('-');
      return;
    }

    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const m = today.getMonth() - birthDate.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) age--;

    $('#ageDisplay').text(age > 0 ? age : '-');
  }

  $('#dobInput').on('change', calculateAge);
  calculateAge();

  // Init
  initSelect2();
  restoreLocationSelections();
});
</script>
<?= $this->endSection() ?>
