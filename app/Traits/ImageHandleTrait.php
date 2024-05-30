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
                    'imageData' => $imageData,
                ];
        } catch (\Exception $e) {
            $response = [
                'status_code' => 500,
                'status' => 'Fail',
                'message' => 'Image Upload Fail.'
            ];
            return response()->json($response, 500);
        }
    }
}
