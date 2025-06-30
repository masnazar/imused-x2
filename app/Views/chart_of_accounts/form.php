<?= $this->extend('layouts/main') ?>
<?= $this->section('title') ?><?= $mode==='create'?'New':'Edit' ?> Account<?= $this->endSection() ?>
<?= $this->section('content') ?>

<h3><?= $mode==='create'?'Create':'Edit' ?> Account</h3>
<form action="<?= $mode==='create'
    ? site_url('chart-of-accounts/store')
    : site_url("chart-of-accounts/update/{$account->id}") ?>"
  method="post">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label>Code</label>
    <input type="text" name="code" class="form-control" 
      value="<?= set_value('code', $account->code ?? '') ?>" required>
  </div>
  <div class="mb-3">
    <label>Name</label>
    <input type="text" name="name" class="form-control"
      value="<?= set_value('name', $account->name ?? '') ?>" required>
  </div>
  <div class="mb-3">
    <label>Type</label>
    <select name="type" class="form-select" required>
      <?php foreach(['Asset','Liability','Equity','Revenue','Expense'] as $t): ?>
        <option value="<?= $t ?>" <?= ($account->type??'')===$t?'selected':'' ?>><?= $t ?></option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="mb-3">
    <label>Normal Balance</label>
    <select name="normal_balance" class="form-select" required>
      <?php foreach(['Debit','Credit'] as $b): ?>
        <option value="<?= $b ?>" <?= ($account->normal_balance??'')===$b?'selected':'' ?>><?= $b ?></option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="mb-3">
    <label>Parent (optional)</label>
    <select name="parent_id" class="form-select">
      <option value="">— None —</option>
      <?php foreach($parents as $p): ?>
        <option value="<?= $p['id'] ?>" <?= ($account->parent_id??null)===$p['id']?'selected':'' ?>>
          <?= esc($p['code'].' – '.$p['name']) ?>
        </option>
      <?php endforeach ?>
    </select>
  </div>
  <button class="btn btn-success"><?= $mode==='create'?'Create':'Update' ?></button>
  <a class="btn btn-secondary" href="<?= site_url('chart-of-accounts') ?>">Cancel</a>
</form>

<?= $this->endSection() ?>
