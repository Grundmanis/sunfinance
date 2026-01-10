<?php

namespace App\Utils;

final class LoanNumberExtractor
{
    /**
     * * Extracts the loan number from the given test.
     */
    public static function extractLoanNumber(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        preg_match('/LN\d{8}/', $text, $matches);
        return $matches[0] ?? null;
    }
}
