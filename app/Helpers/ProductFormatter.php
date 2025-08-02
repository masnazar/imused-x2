<?php

namespace App\Helpers;

class ProductFormatter
{
    public static function format(array $products): string
    {
        $html = '<div class="product-list">';
        foreach ($products as $product) {
            $html .= '<div class="fw-semibold">' . esc($product['nama_produk']) . '</div>';
            $html .= '<small class="text-muted">' . number_format($product['quantity']) . ' pcs × Rp ' . number_format($product['hpp'], 0, ',', '.') . '</small><br>';
        }
        $html .= '</div>';

        return $html;
    }
}
