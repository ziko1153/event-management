<?php

namespace App\Service;

class BaseService
{
    public function sendValidationError(string $message, int $code)
    {
        return [
            'success' => false,
            'validation_error' => true,
            'message' => $message,
            'code' => $code
        ];
    }
}
