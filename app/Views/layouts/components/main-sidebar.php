<?php
$userRoleId = session()->get('role_id') ?? 0;
$menuService = service('menu');
$menus = $menuService->getSidebarMenusForRole($userRoleId);
?>
<aside class="app-sidebar sticky" id="sidebar">
  <div class="main-sidebar-header">
    <a href="<?= base_url('index') ?>" class="header-logo">
      <img src="<?= service('settings')->getLogo('desktop') ?>" alt="logo" class="desktop-logo">
      <img src="<?= service('settings')->getLogo('toggle-dark') ?>" alt="logo" class="toggle-dark">
      <img src="<?= service('settings')->getLogo('desktop-dark') ?>" alt="logo" class="desktop-dark">
      <img src="<?= service('settings')->getLogo('toggle-logo') ?>" alt="logo" class="toggle-logo">
      <img src="<?= service('settings')->getLogo('toggle-white') ?>" alt="logo" class="toggle-white">
      <img src="<?= service('settings')->getLogo('desktop-white') ?>" alt="logo" class="desktop-white">
    </a>
  </div>
  <div class="main-sidebar" id="sidebar-scroll">
    <nav class="main-menu-container nav nav-pills flex-column sub-open">
      <div class="slide-left" id="slide-left">
        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
          <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
        </svg>
      </div>
      <ul class="main-menu">
        <?php foreach ($menus as $menu): ?>
          <?php if (!empty($menu['is_section'])): ?>
            <!-- Section Header -->
            <li class="slide__category">
              <span class="category-name"><?= esc($menu['name']) ?></span>
            </li>
          <?php elseif (empty($menu['children'])): ?>
            <!-- Menu Tanpa Submenu -->
            <li class="slide">
              <a href="<?= $menu['route'] ? base_url($menu['route']) : '#' ?>" class="side-menu__item">
                <i class="<?= esc($menu['icon']) ?> side-menu__icon"></i>
                <span class="side-menu__label"><?= esc($menu['name']) ?></span>
              </a>
            </li>
          <?php else: ?>
            <!-- Menu Dengan Submenu -->
            <li class="slide has-sub">
              <a href="javascript:void(0);" class="side-menu__item">
                <i class="<?= esc($menu['icon']) ?> side-menu__icon"></i>
                <span class="side-menu__label"><?= esc($menu['name']) ?></span>
                <i class="ri-arrow-down-s-line side-menu__angle"></i>
              </a>
              <ul class="slide-menu child1">
                <?php foreach ($menu['children'] as $child): ?>
                  <li class="slide">
                    <a href="<?= base_url($child['route']) ?>" class="side-menu__item"><?= esc($child['name']) ?></a>
                  </li>
                <?php endforeach; ?>
              </ul>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
      <div class="slide-right" id="slide-right">
        <svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191" width="24" height="24" viewBox="0 0 24 24">
          <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
        </svg>
      </div>
    </nav>
  </div>
</aside>
