<?php

namespace App\service;

class DateFormatterService
{
    public function formatDateTimeInFrench(?\DateTimeInterface $dateTime): string
    {
        if (!$dateTime) {
            return '';
        }

        // Define textual representations for numbers and months
        $months = [
            1 => "JANVIER", "FÉVRIER", "MARS", "AVRIL", "MAI", "JUIN",
            "JUILLET", "AOÛT", "SEPTEMBRE", "OCTOBRE", "NOVEMBRE", "DÉCEMBRE"
        ];

        $numbers = [
            1 => "UN", 2 => "DEUX", 3 => "TROIS", 4 => "QUATRE", 5 => "CINQ",
            6 => "SIX", 7 => "SEPT", 8 => "HUIT", 9 => "NEUF", 10 => "DIX",
            11 => "ONZE", 12 => "DOUZE", 13 => "TREIZE", 14 => "QUATORZE",
            15 => "QUINZE", 16 => "SEIZE", 17 => "DIX-SEPT", 18 => "DIX-HUIT",
            19 => "DIX-NEUF", 20 => "VINGT", 21 => "VINGT-ET-UN", 22 => "VINGT-DEUX",
            23 => "VINGT-TROIS", 24 => "VINGT-QUATRE", 25 => "VINGT-CINQ",
            26 => "VINGT-SIX", 27 => "VINGT-SEPT", 28 => "VINGT-HUIT",
            29 => "VINGT-NEUF", 30 => "TRENTE", 31 => "TRENTE-ET-UN"
        ];

        // Extract date and time parts
        $day = (int)$dateTime->format('d');
        $month = (int)$dateTime->format('m');
        $year = (int)$dateTime->format('Y');
        $hour = (int)$dateTime->format('H');
        $minute = (int)$dateTime->format('i');

        // Convert parts to text
        $dayText = $numbers[$day] ?? '';
        $monthText = $months[$month] ?? '';
        $yearText = "DEUX MILLE " . ($numbers[$year % 100] ?? '');
        $hourText = $numbers[$hour] ?? '';
        $minuteText = $numbers[$minute] ?? '';

        // Create the textual representation
        $textualDate = "Le $dayText $monthText $yearText";

        // Create the numeric representation
        $numericDate = $dateTime->format('d/m/Y à H\Hi');

        // Combine both
        return "$textualDate $numericDate";

    }
}