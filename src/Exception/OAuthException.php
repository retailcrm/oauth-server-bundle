<?php

declare(strict_types=1);

namespace OAuth\Exception;

class OAuthException extends \Exception
{
    private const UNKNOWN_CODE = 0;
    private const UNKNOWN_ERROR = 'Unknown Error.';

    protected array $result;

    public function __construct(array $result)
    {
        $this->result = $result;

        $code = $result['code'] ?? self::UNKNOWN_CODE;
        $message = $result['error'] ?? $result['message'] ?? self::UNKNOWN_ERROR;

        parent::__construct($message, $code);
    }

    public function getResult(): array
    {
        return $this->result;
    }

    public function getType(): string
    {
        return is_string($this->result['error'] ?? null) ? $this->result['error'] : 'Exception';
    }
}
