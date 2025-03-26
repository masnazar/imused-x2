<?php namespace App\Traits;

use CodeIgniter\Database\BaseBuilder;
use DateTime;


/**
 * Trait DateFilterTrait
 * Untuk menerapkan filter tanggal berdasarkan inputan request
 */
trait DateFilterTrait
{
    /**
     * Terapkan filter tanggal ke query builder
     *
     * @param BaseBuilder $builder
     * @param string $dateColumn
     * @return BaseBuilder
     */
    public function applyDateFilter(BaseBuilder $builder, string $dateColumn = 'created_at'): BaseBuilder
    {
        $request = service('request');
        $jenisFilter = $request->getVar('jenis_filter');

        if ($jenisFilter === 'custom') {
            $start = $request->getVar('start_date');
            $end = $request->getVar('end_date');
        
            if ($start && $end) {
                $start .= ' 00:00:00';
                $end .= ' 23:59:59';
        
                $builder->where("$dateColumn >=", $start)
                        ->where("$dateColumn <=", $end);
            }
        }
        

        elseif ($jenisFilter === 'periode') {
            $periode = $request->getVar('periode');
        
            if ($periode) {
                list($month, $year) = explode('-', $periode);
        
                // Geser mundur 1 bulan karena labelnya dimajukan 1 bulan
                $periodeDate = DateTime::createFromFormat('Y-m-d', "$year-$month-01")->modify('-1 month');
                $startDate = $periodeDate->format('Y-m-25');
                $endDate = $periodeDate->modify('+1 month')->format('Y-m-24');
        
                $builder->where("$dateColumn >=", $startDate)
                        ->where("$dateColumn <=", $endDate);
            }
        }

        return $builder;
    }
}
