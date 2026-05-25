<?php

declare(strict_types=1);

namespace Ksfraser\CRM\GEDCOM;

class GedcomDateConverter
{
    private const MONTHS = [
        'JAN' => '01', 'FEB' => '02', 'MAR' => '03', 'APR' => '04',
        'MAY' => '05', 'JUN' => '06', 'JUL' => '07', 'AUG' => '08',
        'SEP' => '09', 'OCT' => '10', 'NOV' => '11', 'DEC' => '12',
    ];

    public function parseGedcomDate(string $dateStr): ?string
    {
        $dateStr = trim($dateStr);

        $dateStr = preg_replace('/^(ABT|CAL|EST|INT|BEF|AFT|FROM|TO)\s+/i', '', $dateStr);

        if (preg_match('/^(\d{1,2})\s+(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\s+(\d{4})$/i', $dateStr, $m)) {
            return sprintf('%04d-%s-%02d', (int)$m[3], self::MONTHS[strtoupper($m[2])], (int)$m[1]);
        }

        if (preg_match('/^(JAN|FEB|MAR|APR|MAY|JUN|JUL|AUG|SEP|OCT|NOV|DEC)\s+(\d{4})$/i', $dateStr, $m)) {
            return sprintf('%04d-%s-01', (int)$m[2], self::MONTHS[strtoupper($m[1])]);
        }

        if (preg_match('/^(\d{4})$/', $dateStr, $m)) {
            return $m[1] . '-01-01';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) {
            return $dateStr;
        }

        return null;
    }

    public function formatGedcomDate(string $dateStr): string
    {
        $ts = strtotime($dateStr);
        if ($ts === false) {
            return $dateStr;
        }
        return strtoupper(date('d M Y', $ts));
    }
}
