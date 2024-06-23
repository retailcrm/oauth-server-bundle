<?php

declare(strict_types=1);

namespace OAuth\Exception;

use OAuth\Enum\TransportMethod;
use OAuth\Utils\Uri;
use Symfony\Component\HttpFoundation\Response;

/**
 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.1
 */
class OAuthRedirectException extends OAuthServerException
{
    protected string $redirectUri;
    protected TransportMethod $method;

    public function __construct(
        string $redirectUri,
        string $error,
        ?string $errorDescription = null,
        ?string $state = null,
        TransportMethod $method = TransportMethod::Query,
    ) {
        parent::__construct(Response::HTTP_FOUND, $error, $errorDescription);

        $this->redirectUri = $redirectUri;
        $this->method = $method;
        if ($state) {
            $this->errorData['state'] = $state;
        }
    }

    public function getResponseHeaders(): array
    {
        return [
            'Location' => Uri::build($this->redirectUri, [$this->method->value => $this->errorData]),
        ];
    }
}
