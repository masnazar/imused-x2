<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Tambah Purchase Order
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Page Header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= base_url('purchase-orders') ?>">Manajemen PO</a></li>
                <li class="breadcrumb-item active" aria-current="page">Tambah Purchase Order</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Tambah Purchase Order</h1>
    </div>
</div>

<div class="card custom-card">
    <div class="card-body">
        <form action="<?= base_url('purchase-orders/store') ?>" method="POST">
            <?= csrf_field() ?>

            <!-- Supplier Selection -->
            <div class="mb-3">
                <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                <select id="supplier_id" name="supplier_id" class="form-select" required>
                    <option value="">Pilih Supplier</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?= $supplier['id']; ?>" <?= old('supplier_id') == $supplier['id'] ? 'selected' : ''; ?>>
    <?= esc($supplier['supplier_name']); ?>
</option>

                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">
                    Pilih supplier terlebih dahulu.
                </div>
            </div>

            <!-- Tabel Produk -->
            <div class="mb-3">
                <label class="form-label">Produk <span class="text-danger">*</span></label>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>SKU</th>
                            <th>Jumlah</th>
                            <th>Harga Satuan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="product-list">
                        <tr>
                            <td>
                                <select name="products[0][product_id]" class="form-select product-select" required>
                                    <option value="">Pilih Produk</option>
                                    <?php foreach ($products as $product): ?>
                                        <<option value="<?= esc($product['id']) ?>" <?= old("products[0][product_id]") == $product['id'] ? 'selected' : '' ?>>
    <?= esc($product['nama_produk']) ?>
</option>

                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="text" class="form-control sku-input" name="products[0][sku]" readonly></td>
                            <td><input type="number" class="form-control" name="products[0][quantity]" required min="1"></td>
                            <td><input type="number" class="form-control" name="products[0][unit_price]" required min="1"></td>
                            <td><button type="button" class="btn btn-danger remove-product">Hapus</button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn btn-primary mt-2" id="add-product">+ Tambah Produk</button>
            </div>

            <button type="submit" class="btn btn-success btn-wave">Simpan Purchase Order</button>
            <a href="<?= base_url('purchase-orders') ?>" class="btn btn-secondary btn-wave">Batal</a>
        </form>
    </div>
</div>

<script>
    let productIndex = 1;

    document.getElementById('add-product').addEventListener('click', function () {
        const productList = document.getElementById('product-list');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <select name="products[${productIndex}][product_id]" class="form-select product-select" required>
                    <option value="">Pilih Produk</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= esc($product['id']) ?>">
                            <?= esc($product['nama_produk']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="text" class="form-control sku-input" name="products[${productIndex}][sku]" readonly></td>
            <td><input type="number" class="form-control" name="products[${productIndex}][quantity]" required min="1"></td>
            <td><input type="number" class="form-control" name="products[${productIndex}][unit_price]" required min="0"></td>
            <td><button type="button" class="btn btn-danger remove-product">Hapus</button></td>
        `;
        productList.appendChild(newRow);
        productIndex++;
    });

    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-product')) {
            event.target.closest('tr').remove();
        }
    });

    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('product-select')) {
            const selectedProduct = event.target;
            const selectedProductId = selectedProduct.value;
            const skuInput = selectedProduct.closest('tr').querySelector('.sku-input');

            fetch("<?= base_url('api/get-product-sku/') ?>" + selectedProductId)
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        skuInput.value = data.sku;
                    } else {
                        skuInput.value = "SKU Tidak Ditemukan";
                    }
                })
                .catch(error => console.error('❌ Error fetching SKU:', error));
        }
    });
</script>

<?= $this->endSection() ?>
