<?= $this->extend('layouts/main'); ?>

<?= $this->section('content'); ?>

<!-- 📌 BREADCRUMB -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('/') ?>">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('permissions') ?>">Manajemen Permission</a></li>
                <li class="breadcrumb-item active" aria-current="page">Assign Permission ke User</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">🔐 Assign Permission ke User</h1>
    </div>
</div>

<!-- Flash Messages -->
<?= $this->include('layouts/components/flashMessage') ?>

<!-- 📦 Card Wrapper -->
<div class="card custom-card">
    <div class="card-body">

    <?php
    $selectedUserId = $selectedUserId ?? null;
    $userPermissions = $userPermissions ?? [];
?>

        <form action="<?= base_url('permissions/assign-user') ?>" method="post">
            <?= csrf_field() ?>

            <!-- 👤 Pilih User -->
            <div class="mb-3">
                <label for="user_id" class="form-label">Pilih User</label>
                <select name="user_id" id="user_id" class="form-select" required>
                    <option value="">-- Pilih User --</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" <?= ($selectedUserId == $user['id']) ? 'selected' : '' ?>>
    <?= esc($user['name']) ?> (<?= esc($user['email']) ?>)
</option>

                    <?php endforeach ?>
                </select>
            </div>

            <!-- ✅ Permissions Switches -->
            <div id="permissionsBox" class="mb-3" style="display: <?= isset($selectedUserId) ? 'block' : 'none' ?>;">
                <label class="form-label">Pilih Permissions</label>
                <div class="row">
                    <?php foreach ($permissions as $perm): ?>
                        <?php
    log_message('debug', '🔁 Cek permission ID: ' . $perm['id'] . ' -> ' . (in_array((int)$perm['id'], $userPermissions ?? []) ? '✅ Checked' : '❌ Not Checked'));
?>

                        <div class="col-md-4 mb-2">
                        <div class="form-check form-switch">
    <input class="form-check-input permission-checkbox"
           type="checkbox"
           name="permissions[]"
           value="<?= $perm['id'] ?>"
           id="perm<?= $perm['id'] ?>"
           <?= in_array((int)$perm['id'], $userPermissions ?? []) ? 'checked' : '' ?>>
    <label class="form-check-label" for="perm<?= $perm['id'] ?>">
        <?= esc($perm['alias']) ?>
        <small class="text-muted">(<?= $perm['permission_name'] ?>)</small>
    </label>
</div>


                        </div>
                    <?php endforeach ?>
                </div>
            </div>

            <!-- 🔘 Submit -->
            <button type="submit" class="btn btn-primary">
                💾 Simpan Permission
            </button>
        </form>

    </div>
</div>

<!-- 🧠 Scripts -->
<script>
    const userSelect = document.getElementById('user_id');
    const box = document.getElementById('permissionsBox');

    function fetchPermissions(userId) {
        fetch(`/permissions/user-permissions/${userId}`)
            .then(res => res.json())
            .then(data => {
                document.querySelectorAll('.permission-checkbox').forEach(cb => {
                    cb.checked = data.includes(parseInt(cb.value));
                });
                box.style.display = 'block';
            });
    }

    // Panggil saat user change
    userSelect.addEventListener('change', function () {
        const userId = this.value;
        if (!userId) {
            box.style.display = 'none';
            return;
        }
        fetchPermissions(userId);
    });

    // Auto load if selectedUserId sudah ada (pas reload)
window.addEventListener('DOMContentLoaded', () => {
    const selected = "<?= $selectedUserId ?>";
    if (selected) {
        fetchPermissions(selected);
    } else {
        box.style.display = 'none';
    }
});
</script>

<?= $this->endSection(); ?>
