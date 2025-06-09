<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CourierModel;

/**
 * Controller API untuk datatables kurir
 */
class CourierApi extends BaseController
{
    public function list()
    {
        $request = service('request');
        $model = new CourierModel();

        $draw  = $request->getGet('draw');
        $start = $request->getGet('start');
        $length = $request->getGet('length');
        $search = $request->getGet('search')['value'];

        $query = $model;

        if (!empty($search)) {
            $query = $query->groupStart()
                ->like('courier_name', $search)
                ->orLike('courier_code', $search)
                ->groupEnd();
        }

        $totalFiltered = $query->countAllResults(false);
        $data = $query->select('id, courier_name, courier_code, created_at')
                      ->orderBy('created_at', 'DESC')
                      ->findAll($length, $start);

        $output = [
            'draw' => intval($draw),
            'recordsTotal' => $model->countAll(),
            'recordsFiltered' => $totalFiltered,
            'data' => [],
        ];

        $no = $start + 1;
        foreach ($data as $row) {
            $output['data'][] = [
                'no' => $no++,
                'courier_name' => esc($row['courier_name']),
                'courier_code' => esc($row['courier_code']),
                'created_at' => date('D, d F Y', strtotime($row['created_at'])),
                'id' => $row['id']
            ];
        }

        return $this->response->setJSON($output);
    }
}
