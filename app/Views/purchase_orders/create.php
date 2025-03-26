<?= $this->extend('layouts/main'); ?>

<?= $this->section('styles'); ?>
<!-- Tambahkan CSS khusus jika diperlukan -->
<?= $this->endSection('styles'); ?>

<?= $this->section('content'); ?>

<!-- Start::page-header -->
<div class="d-flex align-items-center justify-content-between page-header-breadcrumb flex-wrap gap-2">
    <div>
        <nav>
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="javascript:void(0);">Purchasing</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url('purchase-orders') ?>">Purchase Order</a></li>
                <li class="breadcrumb-item active" aria-current="page">Buat PO Baru</li>
            </ol>
        </nav>
        <h1 class="page-title fw-medium fs-18 mb-0">Buat Purchase Order</h1>
    </div>
    <div class="btn-list">
        <a href="<?= base_url('purchase-orders') ?>" class="btn btn-primary btn-wave">
            <i class="ri-arrow-left-line align-middle me-1"></i> Kembali ke PO
        </a>
    </div>
</div>
<!-- End::page-header -->

<!-- Flash Messages -->
<?= $this->include('layouts/components/flashMessage') ?>

<!-- Start::row-1 -->
<div class="row">
    <div class="col-xl-12">
        <div class="card custom-card">
            <div class="card-header d-md-flex d-block">
                <div class="d-flex align-items-center w-100 gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="bg-light rounded-pill p-2 px-3 text-muted fs-14">
                            <i class="ri-file-add-line me-1 align-middle"></i> Form PO
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form action="<?= base_url('purchase-orders/store') ?>" method="POST">
                    <?= csrf_field() ?>

                    <!-- Supplier Selection -->
                    <div class="row gy-3 mb-4">
                        <div class="col-xl-6">
                            <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                            <select id="supplier_id" name="supplier_id" class="form-control form-control-light" required>
                                <option value="">Pilih Supplier</option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= $supplier['id'] ?>" <?= old('supplier_id') == $supplier['id'] ? 'selected' : '' ?>>
                                        <?= esc($supplier['supplier_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover text-nowrap border">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Produk</th>
                                    <th>SKU</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-end">Harga Satuan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="product-list">
                                <tr>
                                    <td>
                                        <select name="products[0][product_id]" class="form-select product-select" required>
                                            <option value="">Pilih Produk</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control sku-input" name="products[0][sku]" readonly></td>
                                    <td><input type="number" class="form-control form-control text-end" name="products[0][quantity]" required min="1"></td>
                                    <td><input type="number" class="form-control form-control text-end" name="products[0][unit_price]" required min="0"></td>
                                    <td><button type="button" class="btn btn-danger btn-icon remove-product"><i class="ri-delete-bin-line"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary mt-3" id="add-product">
                            <i class="ri-add-line align-middle me-1"></i> Tambah Produk
                        </button>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2 mt-5">
                        <button type="reset" class="btn btn-light">Reset</button>
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line align-middle me-1"></i> Simpan PO
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End::row-1 -->

<?= $this->endSection('content'); ?>

<?= $this->section('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const supplierSelect = document.getElementById('supplier_id');
    let currentProducts = [];
    let productIndex = 1;

    // Fungsi untuk mengambil produk berdasarkan supplier
    // Di dalam fetchProductsBySupplier()
async function fetchProductsBySupplier(supplierId) {
    if (!supplierId) {
        currentProducts = [];
        updateProductDropdowns();
        return;
    }

    try {
        const response = await fetch(`/purchase-orders/get_products_by_supplier/${supplierId}`);
        const data = await response.json();
        
        if (data.status === 'success') {
            currentProducts = data.products;
            updateProductDropdowns();
        } else {
            console.error('Gagal mengambil produk:', data.message);
            currentProducts = [];
            updateProductDropdowns();
        }
    } catch (error) {
        console.error('Error:', error);
        currentProducts = [];
        updateProductDropdowns();
    }
}

    // Update semua dropdown produk
    function updateProductDropdowns() {
        document.querySelectorAll('.product-select').forEach(select => {
            const selectedValue = select.value;
            const options = currentProducts.map(product => `
                <option value="${product.id}" ${product.id == selectedValue ? 'selected' : ''}>
                    ${product.nama_produk}
                </option>
            `).join('');
            
            select.innerHTML = '<option value="">Pilih Produk</option>' + options;
        });
    }

    // Inisialisasi awal jika supplier sudah dipilih
    if (supplierSelect.value) {
        fetchProductsBySupplier(supplierSelect.value);
    }

    // Event saat supplier berubah
    supplierSelect.addEventListener('change', function() {
        fetchProductsBySupplier(this.value);
    });

    // Tambah baris produk
    document.getElementById('add-product').addEventListener('click', function() {
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                <select name="products[${productIndex}][product_id]" class="form-select product-select" required>
                    <option value="">Pilih Produk</option>
                    ${currentProducts.map(p => `
                        <option value="${p.id}">${p.nama_produk}</option>
                    `).join('')}
                </select>
            </td>
            <td><input type="text" class="form-control sku-input" name="products[${productIndex}][sku]" readonly></td>
            <td><input type="number" class="form-control" name="products[${productIndex}][quantity]" required min="1"></td>
            <td><input type="number" class="form-control" name="products[${productIndex}][unit_price]" required min="0"></td>
            <td><button type="button" class="btn btn-danger remove-product">Hapus</button></td>
        `;
        document.getElementById('product-list').appendChild(newRow);
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

});
</script>

<?= $this->endSection('scripts'); ?>

