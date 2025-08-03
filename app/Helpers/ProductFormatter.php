<?php

namespace App\Helpers;

class ProductFormatter
{
    /**
     * Format array produk menjadi HTML terstruktur.
     *
     * @param array $products Daftar produk mentah
     */
    public static function format(array $products): string
    {
        if (empty($products)) {
            return '-';
        }

        $html = '';
        foreach ($products as $product) {
            $name  = esc($product['nama_produk'] ?? $product['name'] ?? '');
            $qty   = number_format((int) ($product['quantity'] ?? 0), 0, ',', '.');
            $priceValue = $product['unit_selling_price'] ?? $product['unit_price'] ?? $product['hpp'] ?? 0;
            $price = number_format((float) $priceValue, 0, ',', '.');
            $sku   = esc($product['sku'] ?? '');

            $html .= "<div class='d-flex align-items-center mb-2'>";
            $html .= "<div class='flex-grow-1'>";
            $html .= "<div class='fw-medium'>{$name}</div>";
            $html .= "<small class='text-muted'>{$qty} pcs × Rp {$price}</small>";
            $html .= "</div>";

            if ($sku !== '') {
                $html .= "<span class='badge bg-light text-muted border ms-2'>{$sku}</span>";
            }

            $html .= "</div>";
        }

        return $html;
    }
}
