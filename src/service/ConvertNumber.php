<?php

namespace App\service;

use NumberToWords\NumberToWords;

class ConvertNumber
{
    public function convertDecimalToWords($number, $lang = 'fr')
    {
        $numberToWords = new NumberToWords();
        $numberTransformer = $numberToWords->getNumberTransformer($lang);

        // Convert the number to a string with exactly 2 decimal places
        $numberString = number_format($number, 2, ',', '');
        $parts = explode(',', $numberString);

        $integerPart = (int) $parts[0]; // Whole number
        $fractionalPart = isset($parts[1]) ? (int) $parts[1] : 0; // Always ensure two decimal places

        // Convert integer and decimal parts to words
        $integerWords = $numberTransformer->toWords($integerPart);
        $fractionalWords = ($fractionalPart > 0) ? $numberTransformer->toWords($fractionalPart) : '';

        // Format output
        if ($fractionalPart === 0) {
            $result = $integerWords . ' dirhams';
        } else {
            $result = $integerWords . ' dirhams et ' . $fractionalWords . ' centimes';
        }

        // Capitalize first letter and return
        return ucwords($result);
    }


    public function formatMontant($number) {
        $numberString = strval($number); // Convert number to string

        // Check if there is a decimal point
        if (strpos($numberString, '.') !== false) {
            $decimalPart = explode('.', $numberString)[1]; // Get decimal part

            if (strlen($decimalPart) === 1) {
                // If only one decimal place, append a zero
                return number_format($number, 2, '.', '');
            }
        } else {
            // If no decimal part, ensure two decimal places
            return number_format($number, 2, '.', '');
        }

        return $numberString; // Return original if it already has two decimals
    }

}