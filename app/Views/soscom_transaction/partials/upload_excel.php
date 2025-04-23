<div class="card mb-4">
  <div class="card-body">
    <form id="excelImportForm" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <div class="row align-items-end">
        <div class="col-md-4">
          <label for="file_excel" class="form-label">Import File Excel</label>
          <input type="file" name="file_excel" id="file_excel" class="form-control" accept=".xls,.xlsx" required>
        </div>
        <div class="col-md-4">
          <button type="submit" class="btn btn-success">
            <i class="ti ti-file-import"></i> Upload & Cek Data
          </button>
          <a href="<?= base_url('soscom-transactions/download-template') ?>" class="btn btn-outline-secondary">
            <i class="ti ti-download"></i> Download Template
          </a>
        </div>
      </div>
    </form>
  </div>
</div>
