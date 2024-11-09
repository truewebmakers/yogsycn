<?php

namespace App\Traits;

trait ImageHandleTrait
{
    public function decodeBase64Image($base64Image)
    {
        try {
            list($type, $data) = explode(';', $base64Image);
            list(, $data)      = explode(',', $data);
            $imageData = base64_decode($data);
            list(, $extension) = explode('/', $type);
            return [
                'extension' => $extension,
                'data' => $imageData,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Image Upload Fail.');
        }
    }
}
