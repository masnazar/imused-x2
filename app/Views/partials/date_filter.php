<?php
helper('period');
$request = service('request');
?>

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-center">
            <div class="col-md-3">
                <select name="jenis_filter" class="form-select" id="jenisFilter">
                    <option value="semua" <?= $request->getGet('jenis_filter') === 'semua' ? 'selected' : '' ?>>Semua Periode</option>
                    <option value="custom" <?= $request->getGet('jenis_filter') === 'custom' ? 'selected' : '' ?>>Custom Date</option>
                    <option value="periode" <?= $request->getGet('jenis_filter') === 'periode' ? 'selected' : '' ?>>Periode</option>
                </select>
            </div>

            <div class="col-md-4" id="customDate" style="display: none;">
                <div class="input-group">
                    <input type="date" name="start_date" id="startDate" class="form-control" 
                           value="<?= $request->getGet('start_date') ?>">
                    <span class="input-group-text">s/d</span>
                    <input type="date" name="end_date" id="endDate" class="form-control" 
                           value="<?= $request->getGet('end_date') ?>">
                </div>
            </div>

            <div class="col-md-3" id="periodeSelect" style="display: none;">
                <select name="periode" id="periodeSelect" class="form-select">
                    <?php foreach (generate_period_options() as $periode): ?>
                        <option value="<?= $periode['value'] ?>" 
                            <?= $request->getGet('periode') === $periode['value'] ? 'selected' : '' ?>>
                            <?= $periode['label'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterType = document.getElementById('jenisFilter');
    const toggleVisibility = () => {
        document.getElementById('customDate').style.display = 
            filterType.value === 'custom' ? 'flex' : 'none';
        document.getElementById('periodeSelect').style.display = 
            filterType.value === 'periode' ? 'block' : 'none';
    };
    
    filterType.addEventListener('change', toggleVisibility);
    toggleVisibility(); // Initialize
});
</script>