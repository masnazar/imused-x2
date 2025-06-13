<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?>Manajemen Menu<?= $this->endSection() ?>
<?= $this->section('styles') ?>
<link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2 mb-3">
  <div>
    <nav>
      <ol class="breadcrumb mb-1">
        <li class="breadcrumb-item"><a href="<?= base_url() ?>"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="breadcrumb-item active">Manajemen Menu</li>
      </ol>
    </nav>
    <h1 class="page-title fw-medium fs-18 mb-0">
      <i class="ri-menu-line me-2"></i> Menu Management
    </h1>
  </div>
</div>

<?= $this->include('layouts/components/flashMessage') ?>

<div class="card custom-card mb-4">
  <div class="card-body">
    <form action="<?= base_url('settings/menus/save') ?>" method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= esc($menu['id'] ?? '') ?>">

      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Nama Menu</label>
          <input type="text" name="name" class="form-control" value="<?= esc($menu['name'] ?? '') ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Route</label>
          <input type="text" name="route" class="form-control" value="<?= esc($menu['route'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">Ikon</label>
          <div class="input-group">
            <input type="text" id="icon" name="icon" class="form-control" value="<?= esc($menu['icon'] ?? '') ?>" readonly>
            <button type="button" class="btn btn-outline-secondary" onclick="openIconPicker()">Pilih</button>
          </div>
        </div>
        <div class="col-md-2">
          <label class="form-label">Urutan</label>
          <input type="number" name="sort_order" class="form-control" value="<?= esc($menu['sort_order'] ?? 1) ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label d-block">Kategori</label>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_section" value="1" id="is_section"
              <?= !empty($menu['is_section']) ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_section">
              Jadikan Kategori / Section
            </label>
          </div>
        </div>

        <div class="col-md-4">
          <label class="form-label">Parent Menu</label>
          <select name="parent_id" class="form-select">
            <option value="">-- Tidak ada --</option>
            <?php foreach ($menus as $m): ?>
              <?php if (!isset($menu['id']) || $menu['id'] != $m['id']): ?>
                <option value="<?= $m['id'] ?>" <?= isset($menu['parent_id']) && $menu['parent_id'] == $m['id'] ? 'selected' : '' ?>><?= esc($m['name']) ?></option>
              <?php endif ?>
            <?php endforeach ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select name="is_active" class="form-select">
            <option value="1" <?= (isset($menu['is_active']) && $menu['is_active']) ? 'selected' : '' ?>>Aktif</option>
            <option value="0" <?= (isset($menu['is_active']) && !$menu['is_active']) ? 'selected' : '' ?>>Nonaktif</option>
          </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">Simpan</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Icon Picker -->
<div class="modal fade" id="iconPickerModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pilih Ikon</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="text" class="form-control mb-3" id="iconSearch" placeholder="Cari icon...">
        <div class="d-flex flex-wrap" id="iconList"></div>
      </div>
    </div>
  </div>
</div>

<div class="card custom-card">
  <div class="card-body">
    <h5 class="card-title mb-3">Daftar Menu</h5>
    <?php
      $renderMenuTree = function(array $items, int $parentId = null, int $level = 0) use (&$renderMenuTree) {
        $childs = array_filter($items, fn($m) => $m['parent_id'] == $parentId);
        usort($childs, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);

        foreach ($childs as $m): ?>
          <div class="p-3 border rounded mb-2 d-flex justify-content-between align-items-center bg-light ms-<?= $level * 3 ?>">
            <div>
              <i class="<?= esc($m['icon']) ?> me-2"></i>
              <?= esc($m['name']) ?>
              <small class="text-muted ms-2">[<?= esc($m['route'] ?: '-') ?>]</small>
            </div>
            <div>
              <a href="<?= base_url('settings/menus?id=' . $m['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
              <a href="<?= base_url('settings/menus/delete/' . $m['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus menu ini?')">Hapus</a>
            </div>
          </div>
          <?php $renderMenuTree($items, $m['id'], $level + 1); ?>
    <?php endforeach;
      };

      $renderMenuTree($menus);
    ?>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
let allIcons = [];

async function openIconPicker() {
  const modal = new bootstrap.Modal(document.getElementById('iconPickerModal'));
  const iconList = document.getElementById("iconList");
  const searchInput = document.getElementById("iconSearch");

  iconList.innerHTML = '<div class="text-muted">Loading icon list...</div>';
  searchInput.value = "";

  if (allIcons.length === 0) {
    const res = await fetch('https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css');
    const css = await res.text();
    const matches = [...css.matchAll(/\.((ri-[\w-]+))(?:\:before)?\s*\{/g)];
    allIcons = matches.map(m => m[1]).filter((v, i, a) => a.indexOf(v) === i).sort();
  }

  function renderIcons(filter = '') {
    iconList.innerHTML = '';
    const filtered = allIcons.filter(i => i.includes(filter.toLowerCase()));
    if (filtered.length === 0) {
      iconList.innerHTML = '<div class="text-muted">Tidak ada ikon ditemukan.</div>';
      return;
    }
    filtered.forEach(icon => {
      const btn = document.createElement('button');
      btn.className = 'btn btn-outline-dark m-1';
      btn.innerHTML = `<i class="${icon} fs-5"></i>`;
      btn.onclick = () => {
        document.getElementById("icon").value = icon;
        modal.hide();
      };
      iconList.appendChild(btn);
    });
  }

  searchInput.oninput = () => renderIcons(searchInput.value);
  renderIcons();
  modal.show();
}
</script>
<?= $this->endSection() ?>
