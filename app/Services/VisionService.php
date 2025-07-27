<?php

namespace App\Services;

use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Image;
use Google\Cloud\Vision\V1\Likelihood;
use Illuminate\Support\Facades\Log;

class VisionService
{
    protected $client;

    public function __construct()
    {
        $this->client = new ImageAnnotatorClient();
    }


    public function detectText(string $imagePath)
    {
        if (!file_exists($imagePath)) {
            throw new \Exception("File not found: " . $imagePath);
        }

        $content = file_get_contents($imagePath);
        $image = (new Image())->setContent($content);
        $feature = (new Feature())->setType(Feature\Type::TEXT_DETECTION);
        $request = (new AnnotateImageRequest())->setImage($image)->setFeatures([$feature]);

        $batchRequest = (new BatchAnnotateImagesRequest())
            ->setRequests([$request]);

        $batchResponse = $this->client->batchAnnotateImages($batchRequest);

        $results = [];

        foreach ($batchResponse->getResponses() as $response) {
            if ($response->getError()) {
                return [
                    'error' => 'Vision API Error',
                    'message' => $response->getError()->getMessage(),
                    'code' => $response->getError()->getCode()
                ];
            }

            foreach ($response->getTextAnnotations() as $textAnnotation) {
                $results[] = $textAnnotation->getDescription();
            }
        }

        return $results;
    }

    public function detectTextFromUrl(string $imageUrl)
    {
        $content = file_get_contents($imageUrl);
        if ($content === false) {
            throw new \Exception("Could not fetch image from URL: " . $imageUrl);
        }

        $image = (new Image())->setContent($content);
        $feature = (new Feature())->setType(Feature\Type::TEXT_DETECTION);
        $request = (new AnnotateImageRequest())->setImage($image)->setFeatures([$feature]);

        $batchRequest = (new BatchAnnotateImagesRequest())
            ->setRequests([$request]);

        $batchResponse = $this->client->batchAnnotateImages($batchRequest);

        $results = [];

        foreach ($batchResponse->getResponses() as $response) {
            if ($response->getError()) {
                return [
                    'error' => 'Vision API Error',
                    'message' => $response->getError()->getMessage(),
                    'code' => $response->getError()->getCode()
                ];
            }

            foreach ($response->getTextAnnotations() as $textAnnotation) {
                $results[] = $textAnnotation->getDescription();
            }
        }

        return $results;
    }
}
