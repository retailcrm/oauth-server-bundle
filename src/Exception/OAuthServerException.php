<?php

declare(strict_types=1);

namespace OAuth\Exception;

use Symfony\Component\HttpFoundation\Response;

class OAuthServerException extends \Exception
{
    protected int $httpCode;
    protected array $errorData = [];

    public function __construct(int $httpCode, string $error, ?string $errorDescription = null)
    {
        parent::__construct($error);

        $this->httpCode = $httpCode;

        $this->errorData['error'] = $error;
        $this->errorData['error_description'] = $errorDescription;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getDescription(): ?string
    {
        return $this->errorData['error_description'];
    }

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.1
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.2
     */
    public function getHttpResponse(): Response
    {
        return new Response(
            $this->getResponseBody(),
            $this->getHttpCode(),
            $this->getResponseHeaders()
        );
    }

    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-5.2
     *
     * @return array<string, string>
     */
    public function getResponseHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'no-store',
            'Pragma' => 'no-cache',
        ];
    }

    public function getResponseBody(): string
    {
        return json_encode($this->errorData, JSON_THROW_ON_ERROR);
    }
}
