<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class PostalCodeService
{
    protected $db;
    protected $limit = 1000;

    public function __construct()
    {
        $this->db = db_connect();
    }

   public function runBatch(): array
{
    helper('text');

    $villages = $this->db->table('villages v')
        ->select('v.id, v.name AS village, d.name AS district')
        ->join('districts d', 'v.district_id = d.id')
        ->where('v.postal_code IS NULL')
        ->limit($this->limit)
        ->get()
        ->getResult();

    $kodeposRaw = $this->db->table('tbl_kodepos')
        ->select('kodepos, kelurahan, kecamatan')
        ->get()
        ->getResult();

    $kodeposList = [];
    foreach ($kodeposRaw as $k) {
        $slugKel = strtolower(url_title($k->kelurahan, '-', true));
        $slugKec = strtolower(url_title($k->kecamatan, '-', true));
        $kodeposList[] = [
            'kodepos' => $k->kodepos,
            'kel' => $slugKel,
            'kec' => $slugKec
        ];
    }

    $updated = 0;
    $successList = [];

    foreach ($villages as $village) {
        $vSlugKel = strtolower(url_title($village->village, '-', true));
        $vSlugKec = strtolower(url_title($village->district, '-', true));

        $match = null;

        foreach ($kodeposList as $item) {
            similar_text($item['kel'], $vSlugKel, $kelSim);
            similar_text($item['kec'], $vSlugKec, $kecSim);

            if ($kelSim >= 85 && $kecSim >= 85) {
                $match = $item;
                break;
            }
        }

        if ($match) {
            $this->db->table('villages')
                ->where('id', $village->id)
                ->update(['postal_code' => $match['kodepos']]);

            $updated++;
            $successList[] = [
                'village' => $village->village,
                'district' => $village->district,
                'postal_code' => $match['kodepos']
            ];
        }
    }

    return [
        'updated' => $updated,
        'list' => $successList,
    ];
}



    public function countUpdated(): int
    {
        return $this->db->table('villages')->where('postal_code IS NOT NULL')->countAllResults();
    }

    public function countNotUpdated(): int
    {
        return $this->db->table('villages')->where('postal_code IS NULL')->countAllResults();
    }
}
