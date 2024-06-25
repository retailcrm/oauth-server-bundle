<?php

declare(strict_types=1);

namespace OAuth\Exception;

/**
 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-bearer-04#section-2.4
 */
class OAuthAuthenticateException extends OAuthServerException
{
    /** @var array<string, string> */
    protected array $header;

    public function __construct(
        int $httpCode,
        string $tokenType,
        string $realm,
        string $error,
        ?string $errorDescription = null,
        ?string $scope = null,
    ) {
        parent::__construct($httpCode, $error, $errorDescription);

        if ($scope) {
            $this->errorData['scope'] = $scope;
        }

        $header = sprintf('%s realm=%s', ucwords($tokenType), $this->quote($realm));

        foreach ($this->errorData as $key => $value) {
            $header .= sprintf(', %s=%s', $key, $this->quote($value));
        }

        $this->header = ['WWW-Authenticate' => $header];
    }

    public function getResponseHeaders(): array
    {
        return array_merge($this->header, parent::getResponseHeaders());
    }

    /**
     * @see http://tools.ietf.org/html/draft-ietf-httpbis-p1-messaging-17#section-3.2.3
     */
    private function quote(?string $text): string
    {
        if (!$text) {
            return '';
        }

        $text = preg_replace('~[^\\x21-\\x7E\\x80-\\xFF \\t]~x', '', $text);

        if (!$text) {
            return '';
        }

        return '"' . addcslashes($text, '"\\') . '"';
    }
}
