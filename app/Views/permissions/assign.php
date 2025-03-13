<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Kelola Permissions per Role
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('permissions') ?>">Manajemen Permission</a></li>
                <li class="breadcrumb-item active" aria-current="page">Kelola Permissions</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Kelola Permissions per Role</h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card custom-card">
            <div class="card-header justify-content-between">
                <div class="card-title">Assign Permission ke Role</div>
            </div>
            <div class="card-body">
                <form action="<?= base_url('permissions/assign_process') ?>" method="post">
                    <?= csrf_field() ?>

                    <!-- Pilih Role -->
                    <div class="form-floating mb-3">
                        <select name="role_id" class="form-select" id="roleSelect" required>
                            <option value="">Pilih Role</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>">
                                    <?= esc($role['role_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <label for="roleSelect">Pilih Role</label>
                    </div>

                    <!-- Checkbox Permissions -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Permissions:</label>
                        <div class="row" id="permissionsContainer">
                            <?php foreach ($permissions as $permission): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox" type="checkbox" 
                                               name="permissions[]" value="<?= $permission['id'] ?>" 
                                               id="perm_<?= $permission['id'] ?>">
                                        <label class="form-check-label" for="perm_<?= $permission['id'] ?>">
                                            <?= esc($permission['alias']) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line"></i> Simpan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
jQuery(document).ready(function () {
    console.log("🟢 jQuery Ready!");

    jQuery('#roleSelect').change(function () {
        var roleId = jQuery(this).val();
        console.log("🔄 Role Dipilih:", roleId); // Debug Role yang dipilih

        jQuery('.permission-checkbox').prop('checked', false); // Reset dulu semua checkbox

        if (roleId) {
            console.log("🔍 Mengambil permissions untuk Role ID:", roleId);

            jQuery.ajax({
                url: "<?= base_url('permissions/get_assigned_permissions') ?>/" + roleId,
                type: "GET",
                dataType: "json",
                success: function (data) {
                    console.log('🟢 Data dari server:', data);

                    if (!Array.isArray(data)) {
                        console.error("❌ Data bukan array, cek response:", data);
                        return;
                    }

                    if (data.length === 0) {
                        console.warn("⚠️ Tidak ada permissions yang ditemukan untuk role ini.");
                    }

                    jQuery.each(data, function (index, perm) {
                        console.log("✅ Menandai checkbox:", perm.permission_id);
                        let checkbox = jQuery("input[name='permissions[]'][value='" + perm.permission_id + "']");
                        
                        if (checkbox.length > 0) {
                            checkbox.prop('checked', true);
                        } else {
                            console.warn("⚠️ Checkbox tidak ditemukan untuk:", perm.permission_id);
                        }
                    });
                },
                error: function (xhr, status, error) {
                    console.error("❌ AJAX Error!", error);
                    console.log(xhr.responseText);
                }
            });
        }
    });
});
</script>
<?= $this->endSection() ?>

