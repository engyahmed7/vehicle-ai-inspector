<?php

namespace App\Services;

class InsuranceCardParser
{
    public function extractInsuranceDetails($ocrText): array
    {
        $raw = is_array($ocrText) ? implode("\n", $ocrText) : (string)$ocrText;
        $lines = $this->prepLines($raw);
        $flat  = $this->flatten($lines);

        $provider     = $this->matchProvider($flat, $lines);
        $policyNumber = $this->matchPolicyNumber($lines);
        $expiryDate   = $this->matchExpiryDate($lines, $flat);

        return array_filter([
            'provider'      => $provider ? $this->prettyCaseProvider($provider) : null,
            'policy_number' => $policyNumber ? strtoupper($policyNumber) : null,
            'expiry_date'   => $expiryDate,
        ]);
    }

    /* ---------- helpers ---------- */

    private function prepLines(string $raw): array
    {
        $raw = preg_replace("/[ \t\r]+/u", ' ', $raw);
        $raw = preg_replace("/\n{2,}/", "\n", $raw);
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R/', $raw))));
        return $lines;
    }

    private function flatten(array $lines): string
    {
        $s = trim(implode(' ', $lines));
        $s = preg_replace('/\s{2,}/', ' ', $s);
        return $s;
    }

    /* ---------- Provider ---------- */

    private function matchProvider(string $flat, array $lines): ?string
    {
        if (preg_match('/\bState Farm\b/i', $flat)) {
            return 'State Farm';
        }

        $corp = '(?:Company|Co\.?|Corp\.?|Corporation|Inc\.?|LLC|Mutual|Group|Assurance|Indemnity|Property|Automobile|Auto)';
        if (preg_match('/\b([A-Z][A-Za-z&\'\. ]{1,40})\s+Insurance\s+(?:' . $corp . ')(?:\s+(?:' . $corp . '))*\b/i', $flat, $m)) {
            return trim($m[0]);
        }

        if (preg_match('/\b([A-Z][A-Za-z&\'\. ]{1,40})\s+Mutual\s+Automobile\s+Insurance\s+Co\.?\b/i', $flat, $m)) {
            return trim($m[0]);
        }

        if (preg_match('/\b([A-Z][A-Za-z&\'\. ]{1,40})\s+Insurance\b(?!\s+(?:Information|Identification|ID\s*Card))\b/i', $flat, $m)) {
            return trim($m[0]);
        }

        foreach (array_slice($lines, 0, 6) as $ln) {
            if (preg_match('/\bInsurance\b/i', $ln) && !preg_match('/\bInformation|Identification\b/i', $ln)) {
                return trim($ln);
            }
        }

        return null;
    }

    private function prettyCaseProvider(string $s): string
    {
        $s = strtolower($s);
        $s = preg_replace_callback('/\b([a-z])([a-z\']*)\b/', fn($m) => strtoupper($m[1]) . $m[2], $s);
        $preserve = ['USA', 'LLC', 'LP', 'LLP', 'INC', 'CO', 'CO.', 'CORP', 'MUTUAL', 'AUTO', 'ID'];
        foreach ($preserve as $p) {
            $s = preg_replace('/\b' . strtolower($p) . '\b/u', $p, $s);
        }
        $s = preg_replace('/\binsurance\b/i', 'Insurance', $s);
        return trim($s);
    }

    /* ---------- Policy Number ---------- */

    public function matchPolicyNumber(array $lines): ?string
    {
        $labelRegex = '/\b(?:Policy(?:\s*(?:Number|No\.?|#))?|Policy\s*ID|Policy\s*Num(?:ber)?)\b/i';

        foreach ($lines as $i => $ln) {
            if (preg_match($labelRegex, $ln)) {
                if ($cand = $this->extractPolicyFromLine($ln)) {
                    return $cand;
                }
                for ($k = 1; $k <= 2; $k++) {
                    if (isset($lines[$i + $k]) && ($cand = $this->extractPolicyFromLine($lines[$i + $k]))) {
                        return $cand;
                    }
                }
                if ($i > 0 && ($cand = $this->extractPolicyFromLine($lines[$i - 1]))) {
                    return $cand;
                }
            }
        }

        foreach ($lines as $i => $ln) {
            if (preg_match('/\bINSURED\b|\bINSURANCE\b/i', $ln)) {
                for ($k = -1; $k <= 2; $k++) {
                    $idx = $i + $k;
                    if ($idx >= 0 && $idx < count($lines)) {
                        if ($cand = $this->extractPolicyFromLine($lines[$idx])) {
                            return $cand;
                        }
                    }
                }
            }
        }

        foreach ($lines as $ln) {
            if (preg_match('/(\d{1,4}\s+[A-Z0-9]{2,}(?:-[A-Z0-9]{2,})+)/', $ln, $m)) {
                if (!preg_match('/\(.*\)|PHONE/i', $ln)) {
                    return strtoupper($m[1]);
                }
            }
        }

        foreach ($lines as $ln) {
            if (preg_match('/([A-Z0-9]{2,}(?:-[A-Z0-9]{2,})+)/', $ln, $m)) {
                if (!preg_match('/\(.*\)|PHONE/i', $ln)) {
                    return strtoupper($m[1]);
                }
            }
        }

        foreach ($lines as $ln) {
            if ($cand = $this->extractPolicyFromLine($ln)) {
                return $cand;
            }
        }

        return null;
    }

    private function extractPolicyFromLine(string $line): ?string
    {
        $noise = preg_replace('/\b(EFFECTIVE|MAKE|MODEL|YR|YEAR|VIN|AGENT|PHONE|INSURED|POLICY|NUMBER|NO\.?|#|INSURANCE|INFORMATION|TENNESSEE|FLORIDA|CALIFORNIA|TEXAS|STATE|FARM|GEICO|ALLSTATE)\b/i', ' ', $line);

        if (preg_match('/\b([A-Z]?\d{1,4}\s+[A-Z0-9]{2,}(?:-[A-Z0-9]{2,})+)\b/u', $noise, $m)) {
            return strtoupper($m[1]);
        }

        if (
            preg_match('/([A-Z]?\d{1,4}\s+[A-Z0-9]{2,}(?:-[A-Z0-9]{2,})+)/u', $line, $m)
            && !preg_match('/\(.*\)|PHONE/i', $line)
        ) {
            return strtoupper($m[1]);
        }


        if (preg_match('/\b([A-Z0-9]{2,}(?:-[A-Z0-9]{2,})+)\b/u', $noise, $m)) {
            return strtoupper($m[1]);
        }

        if (preg_match('/([A-Z0-9]{2,}(?:-[A-Z0-9]{2,})+)/u', $line, $m) && !preg_match('/\(.*\)|PHONE/i', $line)) {
            return strtoupper($m[1]);
        }

        if (preg_match('/\b([A-Z0-9]{8,})\b/u', $noise, $m) && !preg_match('/\b(INFORMATION|TENNESSEE|INSURANCE)\b/i', $m[1])) {
            return strtoupper($m[1]);
        }

        return null;
    }

    /* ---------- Expiry Date ---------- */

    private function matchExpiryDate(array $lines, string $flat): ?string
    {
        $label = '(?:Exp(?:iration)?(?:\s*Date)?|Expires?|Expiry|Valid\s*Thru|Valid\s*Until|Good\s*Thru|Good\s*Until)';
        foreach ($lines as $ln) {
            if (preg_match('/' . $label . '/i', $ln)) {
                if ($d = $this->findDateOnOrNextLines($lines, $ln)) {
                    return $d;
                }
            }
        }

        if ($d = $this->rangeExpiryFromFlat($flat)) {
            return $d;
        }

        if ($d = $this->lastDateInFlat($flat)) {
            return $d;
        }

        return null;
    }

    private function findDateOnOrNextLines(array $lines, string $currentLine): ?string
    {
        $date = $this->dateRegex();
        if (preg_match('/' . $date . '/i', $currentLine, $m)) {
            return $m[0];
        }
        $i = array_search($currentLine, $lines, true);
        for ($k = 1; $k <= 2; $k++) {
            if (isset($lines[$i + $k]) && preg_match('/' . $date . '/i', $lines[$i + $k], $m2)) {
                return $m2[0];
            }
        }
        return null;
    }

    private function rangeExpiryFromFlat(string $flat): ?string
    {
        $date = $this->dateRegex();
        if (preg_match('/(' . $date . ')\s*(?:to|thru|through|until|\-|\–|\—)\s*(' . $date . ')/i', $flat, $m)) {
            return $m[2];
        }
        if (preg_match('/\b(?:Effective|Eff|Coverage\s*Effective)\b.*?(' . $date . ').*?(?:to|thru|through|until|\-|\–|\—)\s*(' . $date . ')/i', $flat, $m2)) {
            return $m2[2];
        }
        return null;
    }

    private function lastDateInFlat(string $flat): ?string
    {
        $date = $this->dateRegex();
        if (preg_match_all('/' . $date . '/i', $flat, $m)) {
            $all = $m[0];
            return $all ? end($all) : null;
        }
        return null;
    }

    private function dateRegex(): string
    {
        $mm = '(?:0?[1-9]|1[0-2])';
        $dd = '(?:0?[1-9]|[12][0-9]|3[01])';
        $yyyy = '(?:19|20)\d{2}';
        $yy = '\d{2}';
        $mon = '(?:Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:t(?:ember)?)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)';

        $numeric1 = $mm . '[\/\-.]' . $dd . '[\/\-.]' . $yyyy;
        $numeric2 = $yyyy . '[\/\-.]' . $mm . '[\/\-.]' . $dd;
        $numeric3 = $mm . '[\/\-.]' . $yyyy;
        $numeric4 = $mm . '[\/\-.]' . $dd . '[\/\-.]' . $yy;
        $textual1 = $mon . '\s+' . $dd . ',?\s+' . $yyyy;
        $textual2 = $dd . '\s+' . $mon . ',?\s+' . $yyyy;

        return '(?:' . $numeric1 . '|' . $numeric2 . '|' . $numeric3 . '|' . $numeric4 . '|' . $textual1 . '|' . $textual2 . ')';
    }
}
