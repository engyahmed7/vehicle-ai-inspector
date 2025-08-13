<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class MvrParserService
{
    public function extractMvrDetails($ocrText)
    {
        try {

            $fullText = is_array($ocrText) ? implode(' ', $ocrText) : $ocrText;
            $fullText = preg_replace('/\s+/', ' ', trim($fullText));

            // dd($fullText);
            $details = [
                'name' => $this->matchName($fullText),
                'license_number' => $this->matchLicenseNumber($fullText),
                'license_class' => $this->matchLicenseClass($fullText),
                'dob' => $this->matchDob($fullText),
                'issue_date' => $this->matchIssueDate($fullText),
                'expiry_date' => $this->matchExpiryDate($fullText),
            ];

            // dd($details);
            return array_filter($details);
        } catch (\Exception $e) {
            Log::error('Error extracting MVR details: ' . $e->getMessage());
        }
    }

    private function matchName($text)
    {
        if (preg_match('/Name[:\s]+([A-Z ]{3,})/', $text, $m) && isset($m[1])) {
            return ucwords(strtolower(trim($m[1])));
        }
        // dd($text);
        return null;
    }

    private function matchLicenseNumber($text)
    {
        if (preg_match('/(?:DLN|DL|IDN)[:\s]+([A-Z0-9\-]+)/i', $text, $m) && isset($m[1])) {
            return $m[1];
        }

        if (preg_match('/(?:License\s*(?:No|Number)|Driver\s*License)[:\s]+([A-Z0-9\-]+)/i', $text, $m) && isset($m[1])) {
            return $m[1];
        }

        if (preg_match('/\b([A-Z]{1,3}\d{6,}|\d{8,}|[A-Z]\d{7,}|\d{2,4}[A-Z]{2,4}\d{2,})\b/i', $text, $m) && isset($m[1])) {
            return $m[1];
        }

        return null;
    }

    private function matchIssueDate($text)
    {
        if (preg_match('/(Issue\s*Date|ISS|Iss|iss|Issued)[:\s]+(\d{2}[\/\-]\d{2}[\/\-]\d{4})/i', $text, $m) && isset($m[2])) {
            return $m[2];
        }
        return null;
    }

    private function matchExpiryDate($text)
    {
        $patterns = [
            'Exp',
            'EXP',
            'exp',
            'Exp Date',
            'EXP DATE',
            'Expiration',
            'Expiration Date',
            'Expires',
            'Expiry',
            'Valid Thru',
            'Valid Until',
            'Good Thru'
        ];

        $labelRegex = implode('|', array_map('preg_quote', $patterns));

        $dateRegex = '(\d{2}[\/\-.]\d{2}[\/\-.]\d{4}|\d{2}[\/\-.]\d{4}|\d{2}[\/\-.]\d{2}|\d{4}[\/\-.]\d{2}[\/\-.]\d{2}|\d{2}\s+[A-Za-z]{3,}\s+\d{4})';

        if (preg_match("/($labelRegex)(?:[^\d]{0,50})($dateRegex(?:\s+$dateRegex)*)/i", $text, $m) && count($m) > 2) {
            $datesSection = $m[2];
            if (preg_match_all("/$dateRegex/", $datesSection, $dates)) {
                return trim(end($dates[0]));
            }
        }

        if (preg_match("/($labelRegex)(?:[^\d]{0,20})$dateRegex/i", $text, $m) && count($m) > 1) {
            return trim($m[count($m) - 1]);
        }

        return null;
    }



    private function matchLicenseClass($text)
    {
        if (preg_match('/Class[:\s]+([A-Z0-9]+)/i', $text, $m) && isset($m[1])) {
            return $m[1];
        }
        return null;
    }
    private function matchDob($text)
    {
        if (preg_match('/(DOB|Date of Birth)[:\s]+(\d{2}\/\d{2}\/\d{4})/i', $text, $m) && isset($m[2])) {
            return $m[2];
        }
        return null;
    }
}
