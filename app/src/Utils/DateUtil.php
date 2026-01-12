<?php

namespace App\Utils;

use DateTime;
use Exception;

final class DateUtil
{
    /**
     * * Transforms various date formats into 'Y-m-d H:i:s' format.
     */
    public static function transform(string $date, string $format = "Y-m-d H:i:s"): ?string
    {
        try {
            if (preg_match('/^\d{14}$/', $date)) {
                $dateTime = DateTime::createFromFormat('YmdHis', $date);
            } elseif (strtotime($date) !== false) {
                $dateTime = new DateTime($date);
            } else {
                throw new Exception("Invalid date format");
            }

            return $dateTime->format($format);
        } catch (Exception $e) {
            return null;
        }
    }

    public static function parseDate(string $input): ?\DateTimeImmutable
    {
        $d = \DateTimeImmutable::createFromFormat('Y-m-d', $input);
        $errors = \DateTimeImmutable::getLastErrors();
        if ($d === false || ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }
        return $d;
    }
}
