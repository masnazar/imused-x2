<?php

if (!function_exists('generate_period_options')) {
    function generate_period_options() {
        $periods = [];
        $date = new DateTime();
        $date->modify('first day of this month');
        
        for ($i = -12; $i <= 12; $i++) {
            $current = clone $date;
            $current->modify("$i months");
            
            // Label: Bulan + Tahun (dimajukan 1 bulan)
            $labelDate = (clone $current)->modify('+1 month');
            $label = $labelDate->format('F Y'); // Contoh: "November 2023"
            
            // Rentang: 25 bulan sebelumnya - 24 bulan label
            $start = $current->format('Y-m-25');
            $end = (clone $current)->modify('+1 month')->format('Y-m-24');

            $periods[] = [
                'label' => $label,
                'value' => $labelDate->format('m-Y'), // Format: "11-2023"
                'start' => $start,
                'end' => $end
            ];
        }

        return $periods;
    }
}

if (!function_exists('get_date_range_from_periode')) {
    function get_date_range_from_periode(string $periode): array
    {
        try {
            [$month, $year] = explode('-', $periode);
            $start = (new DateTime("$year-$month-25"))->modify('-1 month')->format('Y-m-d');
            $end   = (new DateTime("$year-$month-24"))->format('Y-m-d');

            log_message('debug', "📆 Filter Periode: $start s.d. $end");
            return [$start, $end];
        } catch (\Throwable $e) {
            log_message('error', '[🛑 get_date_range_from_periode] ' . $e->getMessage());
            log_message('warning', '[🛑 get_date_range_from_periode] hasil kosong untuk: ' . $periode);
            return [null, null];
        }
    }
}
