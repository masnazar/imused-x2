<?php
helper('period');
?>

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <div class="row g-3 align-items-center">
            <div class="col-md-3">
                <select name="jenis_filter" class="form-select" id="jenisFilter">
                    <option value="semua">Semua Periode</option>
                    <option value="custom">Custom Date</option>
                    <option value="periode">Periode</option>
                </select>
            </div>

            <div class="col-md-4" id="customDate" style="display: none;">
                <div class="input-group">
                    <input type="date" name="start_date" id="startDate" class="form-control">
                    <span class="input-group-text">s/d</span>
                    <input type="date" name="end_date" id="endDate" class="form-control">
                </div>
            </div>

            <div class="col-md-3" id="periodeSelect" style="display: none;">
                <select name="periode" id="periodeSelect" class="form-select">
                    <?php foreach (generate_period_options() as $periode): ?>
                        <option 
                            value="<?= $periode['value'] ?>" 
                            data-start="<?= $periode['start'] ?>" 
                            data-end="<?= $periode['end'] ?>">
                            <?= $periode['label'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- ❌ Hapus tombol filter, karena lo udah pakai debounce -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const jenisFilter = document.getElementById('jenisFilter');
    const customDate = document.getElementById('customDate');
    const periodeSelectWrapper = document.getElementById('periodeSelect');

    function toggleVisibility() {
        customDate.style.display = jenisFilter.value === 'custom' ? 'flex' : 'none';
        periodeSelectWrapper.style.display = jenisFilter.value === 'periode' ? 'block' : 'none';
    }

    jenisFilter.addEventListener('change', toggleVisibility);
    toggleVisibility();
});
</script>
