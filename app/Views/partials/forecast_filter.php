<!-- 🎛️ Filter Forecast -->
<div class="card mb-4">
  <div class="card-body row align-items-end">
    <div class="col-md-3">
      <label for="forecast_mode" class="form-label">Forecast Kebutuhan Selama:</label>
      <select id="forecast_mode" class="form-select">
        <option value="7">7 Hari</option>
        <option value="14">14 Hari</option>
        <option value="30" selected>30 Hari</option>
      </select>
    </div>

    <div class="col-md-3">
      <label for="periode" class="form-label">Hitung Data Sales Periode:</label>
      <select name="periode" id="periode" class="form-select">
        <option value="">-- Pilih Periode --</option>
        <?php foreach (range(0, 5) as $offset): ?>
          <?php $val = date('m-Y', strtotime("-$offset months")); ?>
          <option value="<?= $val ?>"><?= date('M Y', strtotime("-$offset months")) ?></option>
        <?php endforeach ?>
      </select>
    </div>

    <div class="col-md-3">
      <label for="recommendation_mode" class="form-label">Hitung Kebutuhan Berdasarkan:</label>
      <select id="recommendation_mode" class="form-select">
        <option value="rop_based">ROP</option>
        <option value="max_based">Max Stock</option>
        <option value="hybrid" selected>Hybrid (ROP + Max)</option>
      </select>
    </div>

    <div class="col-md-1">
      <label for="multiplier" class="form-label">Stock Level</label>
      <input type="number" step="0.1" min="1" value="3" id="multiplier" class="form-control" />
    </div>

    <div class="col-md-2 d-grid">
      <button class="btn btn-success" id="btnForecastAll">
        <i class="ti ti-robot"></i> Forecast All
      </button>
    </div>
  </div>
</div>

<script>
  $('#forecast_mode').on('change', function () {
    $('#forecast_custom_range').toggleClass('d-none', $(this).val() !== 'custom');
  });
</script>
