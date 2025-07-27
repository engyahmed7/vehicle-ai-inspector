<?php  

namespace App\Services;

class OcrService
{
    public function extractText(string $imagePath): string
    {
        $output = shell_exec("tesseract " . escapeshellarg($imagePath) . " stdout");
        return trim($output);
    }
}
