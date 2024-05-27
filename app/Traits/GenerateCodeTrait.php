<?php

namespace App\Traits;

use App\Models\admin;

trait GenerateCodeTrait
{
    public function generatSmallLettersCode($length)
    {
        try {
            $characters = 'abcdefghijklmnopqrstuvwxyz';
            $digits = '123456789';
            $code = '';

            // Ensure the code contains at least one digit
            $code .= $digits[rand(0, strlen($digits) - 1)];

            // Fill the rest of the code with random characters
            while (strlen($code) < $length) {
                $char = $characters[rand(0, strlen($characters) - 1)];
                // Ensure no character appears more than three times
                if (substr_count($code, $char) < 3) {
                    $code .= $char;
                }
            }

            // Shuffle the code to ensure the digit is not always at the start
            $code = str_shuffle($code);
            if (admin::where('_id', $code)->exists()) {
                return $this->generatSmallLettersCode($length); // Recursively generate a new code if a duplicate is found
            }


            return $code;
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
    }
}
