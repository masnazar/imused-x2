<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class UpdatePostalCode extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'postalcode:update';
    protected $description = 'Update postal_code di villages dari tbl_kodepos_baru_fix dengan normalisasi dan similarity';

    public function run(array $params)
    {
        $db = db_connect();
        $totalUpdated = 0;
        $totalFailed  = 0;
        $batch        = 1;

        // 🔁 Ambil dan cache data dari tabel baru
        $kodeposList = $db->table('tbl_kodepos_baru_fix')
            ->select('kelurahan, kecamatan, kodepos')
            ->get()
            ->getResultArray();

        // 🔃 Normalisasi semua entri
        $normalizedKodepos = [];
        foreach ($kodeposList as $row) {
            $normalizedKodepos[] = [
                'kelurahan' => $this->normalize($row['kelurahan']),
                'kecamatan' => $this->normalize($row['kecamatan']),
                'kodepos'   => $row['kodepos'],
            ];
        }

        while (true) {
            $villages = $db->table('villages v')
                ->select('v.id, v.name AS village, d.name AS district')
                ->join('districts d', 'v.district_id = d.id')
                ->where('v.postal_code IS NULL')
                ->limit(500000)
                ->get()
                ->getResult();

            if (empty($villages)) {
                CLI::write("🎯 Semua desa telah dicoba. Total updated: {$totalUpdated}", 'green');
                break;
            }

            $updated = 0;
            CLI::write("🚀 Batch {$batch} dimulai (total: " . count($villages) . ")");

            foreach ($villages as $village) {
                $villageName   = $this->normalize($village->village);
                $districtName  = $this->normalize($village->district);
                $found         = null;

                foreach ($normalizedKodepos as $k) {
                    $kecMatch = $k['kecamatan'] === $districtName || similar_text($k['kecamatan'], $districtName, $kecPercent) && $kecPercent > 90;
                    $kelMatch = $k['kelurahan'] === $villageName || similar_text($k['kelurahan'], $villageName, $kelPercent) && $kelPercent > 85;

                    if ($kecMatch && $kelMatch) {
                        $found = $k;
                        break;
                    }
                }

                if ($found) {
                    $db->table('villages')
                        ->where('id', $village->id)
                        ->update(['postal_code' => $found['kodepos']]);

                    $updated++;
                    $totalUpdated++;
                    CLI::write("✔ {$village->village} → {$found['kodepos']}", 'green');
                } else {
                    $totalFailed++;
                    CLI::write("❌ Gagal match: {$village->village} ({$village->district})", 'red');
                    file_put_contents(WRITEPATH . 'logs/postalcode_not_found.log', "{$village->village}|{$village->district}\n", FILE_APPEND);
                }
            }

            CLI::write("✅ Batch {$batch} selesai. Diperbarui: {$updated}", 'yellow');

            if ($updated === 0) {
                CLI::write("🛑 Tidak ada yang berhasil di batch ini. Proses dihentikan.", 'light_red');
                break;
            }

            $batch++;
        }

        CLI::write("🏁 Selesai. Total updated: {$totalUpdated}", 'light_green');
        CLI::write("🧨 Gagal ditemukan: {$totalFailed}", 'red');
    }

    private function normalize($str)
    {
        $str = strtolower(trim($str));
        $str = preg_replace('/^(desa|kel\.?|gampong|kampung|p\.o\.)\s*/', '', $str); // + P.O.
        $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str); // translit aksen
        $str = preg_replace('/[^a-z0-9 ]/u', '', $str); // hanya huruf & angka
        return preg_replace('/\s+/', '', $str); // hapus spasi
    }
}
