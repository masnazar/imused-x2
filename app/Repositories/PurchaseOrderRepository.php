<?php

namespace App\Repositories;

use App\Models\PurchaseOrderModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;

class PurchaseOrderRepository
{
    protected $model;
    protected $db;

    public function __construct()
    {
        $this->model = new PurchaseOrderModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * 📌 Ambil semua Purchase Orders
     *
     * @return array Daftar semua Purchase Orders
     */
    public function getAllPurchaseOrders()
    {
        return $this->model->getPurchaseOrders();
    }

    /**
     * 📌 Ambil kode brand berdasarkan produk yang dipilih
     *
     * @param int $productId ID produk
     * @return string|null Kode brand
     */
    public function getBrandCodeByProduct($productId)
    {
        return $this->db->table('brands')
            ->select('brands.kode_brand')
            ->join('products', 'products.brand_id = brands.id', 'left')
            ->where('products.id', $productId)
            ->get()
            ->getRowArray()['kode_brand'] ?? null;
    }

    /**
     * 📌 Ambil nomor urutan terakhir dalam bulan berjalan
     *
     * @param int $year Tahun
     * @param int $month Bulan
     * @param string $brandCode Kode brand
     * @return int Nomor urutan terakhir
     */
    public function getLastPoNumber($year, $month, $brandCode)
    {
        $prefix = "PO/$year/$brandCode/$month-";

        $query = $this->db->table('purchase_orders')
            ->select("MAX(SUBSTRING_INDEX(po_number, '-', -1)) AS last_number")
            ->like('po_number', $prefix, 'after')
            ->get()
            ->getRowArray();

        return isset($query['last_number']) ? (int) $query['last_number'] : 0;
    }

    /**
     * 📌 Konversi angka bulan ke angka Romawi
     *
     * @param int $month Bulan dalam angka
     * @return string|null Bulan dalam angka Romawi
     */
    public function convertToRoman($month)
    {
        $romanMonths = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];

        return $romanMonths[$month] ?? null;
    }

    /**
     * 📌 Simpan Purchase Order
     *
     * @param array $data Data Purchase Order
     * @return int ID Purchase Order yang baru disimpan
     */
    public function create($data)
    {
        $this->model->insert($data);
        return $this->model->getInsertID();
    }

    /**
     * 📌 Simpan Detail Purchase Order
     *
     * @param array $data Data detail Purchase Order
     * @return bool Status penyimpanan
     */
    public function createDetail($data)
    {
        return $this->db->table('purchase_order_details')->insert($data);
    }

    /**
     * 📌 Hapus Purchase Order (Soft Delete)
     *
     * @param int $id ID Purchase Order
     * @return bool Status penghapusan
     */
    public function delete($id)
{
    return $this->model->delete($id); // ✅ Akan otomatis update deleted_at
}

    /**
     * 📌 Ambil detail Purchase Order
     *
     * @param int $purchaseOrderId ID Purchase Order
     * @return array Detail Purchase Order
     */
    public function getPurchaseOrderDetails($purchaseOrderId)
    {
        return $this->db->table('purchase_order_details')
            ->select('purchase_order_details.*, products.nama_produk, products.sku')
            ->join('products', 'products.id = purchase_order_details.product_id')
            ->where('purchase_order_details.purchase_order_id', $purchaseOrderId)
            ->get()
            ->getResultArray();
    }

    /**
     * 📌 Ambil data Purchase Order dengan pagination
     *
     * @param int $start Offset data
     * @param int $length Jumlah data
     * @param string|null $search Kata kunci pencarian
     * @return array Data Purchase Order
     */
    public function getPurchaseOrderData($start, $length, $search, $filters = []): array
{
    $builder = $this->buildBaseQuery($search);

    if (!empty($filters)) {
        $builder = $this->applyDateFilter($builder, 'po.created_at', $filters);
    }

    return $builder->limit($length, $start)->get()->getResultArray();
}

    /**
     * 📌 Cari Purchase Order berdasarkan ID
     *
     * @param int $id ID Purchase Order
     * @return array|null Data Purchase Order
     */
    public function findById($id)
    {
        return $this->db->table('purchase_orders po')
            ->select('po.*, s.supplier_name, s.supplier_address')
            ->join('suppliers s', 's.id = po.supplier_id', 'left')
            ->where('po.id', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * 📌 Perbarui jumlah barang yang diterima
     *
     * @param int $poId ID Purchase Order
     * @param int $productId ID Produk
     * @param int $receivedQty Jumlah barang diterima
     * @return void
     */
    public function updateReceivedQuantity($poId, $productId, $receivedQty)
    {
        $this->db->table('purchase_order_details')
            ->where('purchase_order_id', $poId)
            ->where('product_id', $productId)
            ->set('received_quantity', "received_quantity + $receivedQty", false)
            ->set('remaining_quantity', "remaining_quantity - $receivedQty", false)
            ->update();
    }

    /**
     * 📌 Ambil semua gudang
     *
     * @return array Daftar gudang
     */
    public function getAllWarehouses()
    {
        return $this->db->table('warehouses')
            ->select('id, name')
            ->where('deleted_at', null)
            ->get()
            ->getResultArray();
    }

    /**
     * 📌 Catat log penerimaan barang
     *
     * @param int $poId ID Purchase Order
     * @param int $productId ID Produk
     * @param int $warehouseId ID Gudang
     * @param int $receivedQty Jumlah barang diterima
     * @param string $nomorSuratJalan Nomor surat jalan
     * @return bool Status penyimpanan log
     */
    public function logPoReceipt($poId, $productId, $warehouseId, $receivedQty, $nomorSuratJalan)
    {
        return $this->db->table('po_receipt_logs')->insert([
            'nomor_surat_jalan' => $nomorSuratJalan,
            'purchase_order_id' => $poId,
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
            'received_quantity' => $receivedQty,
            'received_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 📌 Ambil log penerimaan untuk Purchase Order tertentu
     *
     * @param int $purchaseOrderId ID Purchase Order
     * @return array Log penerimaan
     */
    public function getReceiptLogs($purchaseOrderId)
    {
        return $this->db->table('po_receipt_logs log')
            ->select('log.nomor_surat_jalan, log.*, w.name as warehouse_name, p.sku, p.nama_produk as product_name')
            ->join('warehouses w', 'w.id = log.warehouse_id')
            ->join('products p', 'p.id = log.product_id')
            ->where('log.purchase_order_id', $purchaseOrderId)
            ->orderBy('log.received_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * 📌 Hitung semua Purchase Orders
     *
     * @return int Jumlah Purchase Orders
     */
    public function countAllPurchaseOrders($builder = null)
{
    if ($builder) {
        return $builder->countAllResults(false);
    }

    return $this->db->table('purchase_orders')
        ->where('deleted_at', null)
        ->countAllResults();
}


    /**
     * 📌 Hitung Purchase Orders berdasarkan status
     *
     * @param string $status Status Purchase Order
     * @return int Jumlah Purchase Orders dengan status tertentu
     */
    public function countByStatus($status, $builder = null)
{
    if ($builder) {
        $builder = clone $builder; // jaga-jaga
        return $builder->where('po.status', $status)->countAllResults();
    }

    return $this->db->table('purchase_orders')
        ->where('status', $status)
        ->where('deleted_at', null)
        ->countAllResults();
}



    /**
     * 📌 Hitung total item dalam semua Purchase Orders
     *
     * @return int Total item
     */
    public function getTotalItems($builder = null)
{
    if (is_null($builder)) {
        $builder = $this->db->table('purchase_order_details pod')
            ->select('SUM(pod.quantity) as total_quantity') // Hanya ambil SUM
            ->join('purchase_orders po', 'po.id = pod.purchase_order_id')
            ->where('po.deleted_at', null);
    }

    // Reset select untuk memastikan tidak ada kolom lain
    $builder->select('SUM(pod.quantity) as total_quantity', false);
    
    $result = $builder->get()->getRowArray();

    return (int) ($result['total_quantity'] ?? 0);
}



    /**
     * 📌 Perbarui status Purchase Order
     *
     * @param int $poId ID Purchase Order
     * @return bool Status pembaruan
     */
    public function updatePoStatus($poId)
    {
        $query = $this->db->table('purchase_order_details')
            ->select('SUM(quantity) as total_ordered, SUM(received_quantity) as total_received')
            ->where('purchase_order_id', $poId)
            ->get()
            ->getRowArray();

        if (!$query || is_null($query['total_ordered'])) {
            log_message('error', '❌ Gagal ambil total PO: ' . print_r($query, true));
            return false;
        }

        $totalOrdered = (int)$query['total_ordered'];
        $totalReceived = (int)$query['total_received'];

        $status = 'Pending';
        if ($totalReceived > 0) {
            $status = ($totalReceived < $totalOrdered) ? 'Partial' : 'Completed';
        }

        log_message('info', "🔄 Update status PO #$poId: Status=$status");

        return $this->db->table('purchase_orders')
            ->where('id', $poId)
            ->update(['status' => $status]);
    }

    /**
     * 📌 Ambil brand berdasarkan supplier
     *
     * @param int $supplierId ID Supplier
     * @return array Daftar brand
     */
    public function getBrandsBySupplier($supplierId)
    {
        return $this->db->table('brands')
            ->select('id')
            ->where('supplier_id', $supplierId)
            ->get()
            ->getResultArray();
    }

    /**
     * 📌 Ambil produk berdasarkan brand
     *
     * @param array $brandIds Daftar ID brand
     * @return array Daftar produk
     */
    public function getProductsByBrands(array $brandIds)
    {
        if (empty($brandIds)) {
            return [];
        }

        return $this->db->table('products')
            ->select('id, nama_produk, sku')
            ->whereIn('brand_id', $brandIds)
            ->get()
            ->getResultArray();
    }

    /**
 * Ambil statistik PO berdasarkan filter (termasuk tanggal)
 *
 * @param array $filters
 * @return array
 */
public function getStatisticsWithFilter(array $filters = []): array
{
    $baseBuilder = $this->db->table('purchase_orders po')
        ->where('po.deleted_at', null);

    if (!empty($filters)) {
        $baseBuilder = $this->applyDateFilter($baseBuilder, 'po.created_at', $filters);
    }

    return [
        'total'        => $this->countAllPurchaseOrders($baseBuilder),
        'pending'      => $this->countByStatus('Pending', clone $baseBuilder),
        'completed'    => $this->countByStatus('Completed', clone $baseBuilder),
        'total_items'  => $this->getTotalItems(
    $this->buildFilteredItemsQuery($filters)
),
    ];
}

public function buildFilteredItemsQuery(array $filters = [])
{
    $builder = $this->db->table('purchase_order_details pod')
        ->join('purchase_orders po', 'po.id = pod.purchase_order_id')
        ->where('po.deleted_at', null)
        ->selectSum('pod.quantity', 'total_quantity'); // ✅ INI WAJIB, brokoli!

    // ⏳ Apply filter tanggal
    if (!empty($filters)) {
        $builder = $this->applyDateFilter($builder, 'po.created_at', $filters);
    }

    return $builder;
}





/**
 * Terapkan filter tanggal ke builder query
 *
 * @param object $builder
 * @param string $column
 * @param array $filters
 * @return object
 */
public function applyDateFilter($builder, string $column, array $filters): object
{
    if (isset($filters['jenis_filter']) && $filters['jenis_filter'] === 'custom') {
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $start = $filters['start_date'] . ' 00:00:00';
            $end   = $filters['end_date']   . ' 23:59:59';

            $builder->where("$column >=", $start);
            $builder->where("$column <=", $end);
        }
    } elseif (isset($filters['jenis_filter']) && $filters['jenis_filter'] === 'periode') {
        if (!empty($filters['periode'])) {
            list($month, $year) = explode('-', $filters['periode']);

            // ⏪ Geser mundur 1 bulan karena labelnya dimajukan 1 bulan
            $periodeDate = \DateTime::createFromFormat('Y-m-d', "$year-$month-01")->modify('-1 month');
            $startDate = $periodeDate->format('Y-m-25');
            $endDate = $periodeDate->modify('+1 month')->format('Y-m-24');

            $builder->where("$column >=", $startDate);
            $builder->where("$column <=", $endDate);
        }
    }

    return $builder;
}
    
/**
 * 📌 Build base query untuk DataTables dan statistik
 *
 * @param string|null $search
 * @return BaseBuilder
 */
public function buildBaseQuery(?string $search = null): BaseBuilder
{
    $builder = $this->db->table('purchase_orders po')
        ->select('po.*, s.supplier_name, 
            GROUP_CONCAT(
                CONCAT_WS("::", p.sku, p.nama_produk, pod.quantity, pod.unit_price)
                SEPARATOR "||"
            ) as products')
        ->join('suppliers s', 's.id = po.supplier_id', 'left')
        ->join('purchase_order_details pod', 'pod.purchase_order_id = po.id', 'left')
        ->join('products p', 'p.id = pod.product_id', 'left')
        ->where('po.deleted_at', null)
        ->groupBy('po.id');

    if ($search) {
        $builder->groupStart()
            ->like('po.po_number', $search)
            ->orLike('s.supplier_name', $search)
            ->orLike('p.nama_produk', $search)
            ->groupEnd();
    }

    return $builder;
}

}
