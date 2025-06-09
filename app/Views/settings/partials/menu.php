<?php foreach ($menus as $menu): ?>
  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
          <strong><?= esc($menu['name']) ?></strong>
          <small class="text-muted ms-2">[<?= esc($menu['slug']) ?>]</small>
        </div>
      </div>

      <form method="post" action="<?= base_url('settings/menu/roles') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="menu_id" value="<?= $menu['id'] ?>">

        <select name="role_ids[]" class="form-select js-role-select" multiple>
          <?php foreach ($roles as $role): ?>
            <option value="<?= $role['id'] ?>"
              <?= in_array($role['id'], $accessMap[$menu['id']] ?? []) ? 'selected' : '' ?>>
              <?= esc($role['role_name']) ?>
            </option>
          <?php endforeach ?>
        </select>

        <div class="text-end mt-2">
          <button class="btn btn-primary btn-sm">Simpan Akses</button>
        </div>
      </form>
    </div>
  </div>
<?php endforeach ?>
