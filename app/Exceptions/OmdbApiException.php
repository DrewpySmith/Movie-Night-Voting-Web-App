<?php

namespace App\Exceptions;

use Exception;

class OmdbApiException extends Exception
{
    public function __construct(string $message = 'OMDb API error', int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error' => 'omdb_api_error',
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}
