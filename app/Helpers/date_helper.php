<?php

/**
 * Format tanggal ke gaya Indonesia: Sen, 18 Januari 2025
 */
function indo_datetime($datetime): string
{
    $formatter = new IntlDateFormatter(
        'id_ID',
        IntlDateFormatter::FULL,
        IntlDateFormatter::SHORT,
        'Asia/Jakarta',
        IntlDateFormatter::GREGORIAN,
        "EEE, dd MMMM yyyy HH:mm"
    );

    return $formatter->format(new DateTime($datetime));
}
