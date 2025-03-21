<?php

namespace Jsadways\ScopeFilter\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceException extends Exception
{
    public array $payload = [];
    public string $error = '';
    public int $error_code = 50000;
    public string $msg = '伺服器內部錯誤';

    public function __construct($message=Null, $error='未知錯誤', $code=Null, $previous=null, array $payload=[]) {
        $this->payload = $payload;
        $this->error = $error;
        $this->error_code = $code ?? $this->error_code;
        $msg = $message ?? $this->msg;
        parent::__construct($msg, $code, $previous);
    }

    public function to_array(): array
    {
        return ['error_code' => $this->error_code, 'payload' => $this->payload, 'error' => $this->error];
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->fail($this->getMessage(), ...$this->to_array());
    }
}
