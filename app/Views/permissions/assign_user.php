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
        // Default values for variables if not set
        $selectedUserId = $selectedUserId ?? null;
        $userPermissions = $userPermissions ?? [];
        ?>

        <!-- Form untuk Assign Permission -->
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
                    <?php 
                    // Ensure userPermissions is an array of integers
                    $userPermissions = array_map('intval', $userPermissions ?? []); 
                    ?>
                    <?php foreach ($permissions as $perm): ?>
                        <?php
                        // Debug log for permission checks
                        log_message('debug', '🔁 Cek permission ID: ' . $perm['id'] . ' -> ' . (in_array((int)$perm['id'], $userPermissions) ? '✅ Checked' : '❌ Not Checked'));
                        ?>
                        <div class="col-md-4 mb-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input permission-checkbox"
                                       type="checkbox"
                                       name="permissions[]"
                                       value="<?= $perm['id'] ?>"
                                       id="perm<?= $perm['id'] ?>"
                                       <?= in_array((int)$perm['id'], $userPermissions) ? 'checked' : '' ?>>
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
    // DOM Elements
    const userSelect = document.getElementById('user_id');
    const box = document.getElementById('permissionsBox');

    /**
     * Fetch permissions for a specific user and update the UI.
     * @param {number} userId - The ID of the selected user.
     */
    function fetchPermissions(userId) {
        fetch(`/permissions/user-permissions/${userId}`)
            .then(res => res.json())
            .then(data => {
                // Convert all permission IDs to integers
                const intData = data.map(Number);

                // Update checkboxes based on fetched permissions
                document.querySelectorAll('.permission-checkbox').forEach(cb => {
                    cb.checked = intData.includes(parseInt(cb.value));
                });

                // Show the permissions box
                box.style.display = 'block';
            });
    }

    // Event listener for user selection change
    userSelect.addEventListener('change', function () {
        const userId = this.value;
        if (!userId) {
            box.style.display = 'none';
            return;
        }
        fetchPermissions(userId);
    });

    // Auto-load permissions if a user is already selected (e.g., on page reload)
    window.addEventListener('DOMContentLoaded', () => {
        const selected = <?= json_encode($selectedUserId) ?>;
        if (selected) {
            fetchPermissions(selected);
        } else {
            box.style.display = 'none';
        }
    });
</script>

<?= $this->endSection(); ?>
