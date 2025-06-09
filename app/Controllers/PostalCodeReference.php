<?php

namespace App\Controllers;

class PostalCodeReference extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $data['kodepos'] = $db->table('tbl_kodepos')
            ->select('kelurahan, kecamatan, kabupaten, provinsi, kodepos')
            ->orderBy('provinsi')
            ->limit(100000)
            ->get()
            ->getResult();

        return view('postal_code/kodepos_list', $data);
    }
}
