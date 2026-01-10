<?php

namespace App\Utils;

use DateTime;
use Exception;

final class DateTransformer
{
    /**
     * * Transforms various date formats into 'Y-m-d H:i:s' format.
     */
    public static function transform(string $date): ?string
    {
        try {
            if (preg_match('/^\d{14}$/', $date)) {
                $dateTime = DateTime::createFromFormat('YmdHis', $date);
            } elseif (strtotime($date) !== false) {
                $dateTime = new DateTime($date);
            } else {
                throw new Exception("Invalid date format");
            }

            return $dateTime->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            return null;
        }
    }
}
