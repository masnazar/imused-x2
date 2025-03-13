<form action="<?= $action ?>" method="post">
    <?= csrf_field() ?>
    
    <!-- Nama Supplier -->
    <div class="form-floating mb-3">
        <input type="text" name="supplier_name" class="form-control <?= (session('errors.supplier_name')) ? 'is-invalid' : '' ?>"
            id="supplier_name" placeholder="Nama Supplier" value="<?= old('supplier_name', $supplier['supplier_name'] ?? '') ?>" required>
        <label for="supplier_name">Nama Supplier</label>
        <div class="invalid-feedback"><?= session('errors.supplier_name') ?></div>
    </div>

    <!-- Alamat -->
    <div class="form-floating mb-3">
        <textarea name="supplier_address" class="form-control <?= (session('errors.supplier_address')) ? 'is-invalid' : '' ?>"
            id="supplier_address" placeholder="Alamat Supplier" required><?= old('supplier_address', $supplier['supplier_address'] ?? '') ?></textarea>
        <label for="supplier_address">Alamat Supplier</label>
        <div class="invalid-feedback"><?= session('errors.supplier_address') ?></div>
    </div>

    <!-- Nama PIC -->
    <div class="form-floating mb-3">
        <input type="text" name="supplier_pic_name" class="form-control <?= (session('errors.supplier_pic_name')) ? 'is-invalid' : '' ?>"
            id="supplier_pic_name" placeholder="Nama PIC" value="<?= old('supplier_pic_name', $supplier['supplier_pic_name'] ?? '') ?>" required>
        <label for="supplier_pic_name">Nama PIC</label>
        <div class="invalid-feedback"><?= session('errors.supplier_pic_name') ?></div>
    </div>

    <!-- Kontak PIC -->
    <div class="form-floating mb-3">
        <input type="text" name="supplier_pic_contact" class="form-control <?= (session('errors.supplier_pic_contact')) ? 'is-invalid' : '' ?>"
            id="supplier_pic_contact" placeholder="Kontak PIC" value="<?= old('supplier_pic_contact', $supplier['supplier_pic_contact'] ?? '') ?>" required>
        <label for="supplier_pic_contact">Kontak PIC</label>
        <div class="invalid-feedback"><?= session('errors.supplier_pic_contact') ?></div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="ri-save-line"></i> Simpan</button>
</form>
