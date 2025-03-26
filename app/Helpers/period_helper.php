<?php
if (!function_exists('generate_period_options')) {
    function generate_period_options() {
        $periods = [];
        $date = new DateTime();
        $date->modify('first day of this month');
        
        for ($i = -12; $i <= 12; $i++) {
            $current = clone $date;
            $current->modify("$i months");
            
            $start = $current->format('Y-m-25');
            $end = (clone $current)->modify('+1 month')->format('Y-m-24');

            // Labelnya dimajukan 1 bulan
            $labelDate = (clone $current)->modify('+1 month');

            $periods[] = [
                'label' => $labelDate->format('F Y'),
                'value' => $labelDate->format('m-Y'), // value tetap sesuai label
                'start' => $start,
                'end' => $end
            ];
        }

        return $periods;
    }
}
