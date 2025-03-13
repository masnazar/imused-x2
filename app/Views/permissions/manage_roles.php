<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?> Kelola Role Permissions <?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<div class="card custom-card">
    <div class="card-header justify-content-between">
        <div class="card-title">
            Kelola Hak Akses Role
        </div>
    </div>
    <div class="card-body">
        <form action="<?= base_url('permissions/roles/update') ?>" method="post">
            <?= csrf_field() ?>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <?php foreach ($permissions as $perm) : ?>
                                <th><?= esc($perm['alias']) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role) : ?>
                            <tr>
                                <td><?= esc($role['role_name']) ?></td>
                                <?php foreach ($permissions as $perm) : ?>
                                    <td class="text-center">
                                        <input type="checkbox" name="permissions[<?= $role['id'] ?>][]" 
                                            value="<?= $perm['id'] ?>"
                                            <?= in_array($perm['id'], $role['permissions']) ? 'checked' : '' ?>>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Simpan Perubahan</button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
