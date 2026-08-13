<?php

declare(strict_types=1);

final class PagBankApiException extends RuntimeException
{
    public function __construct(
        string $message,
        private int $httpStatus = 0,
        private array $response = []
    ) {
        parent::__construct($message);
    }

    public function httpStatus(): int
    {
        return $this->httpStatus;
    }

    public function response(): array
    {
        return $this->response;
    }
}
