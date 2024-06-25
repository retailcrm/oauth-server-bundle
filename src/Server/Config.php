<?php

declare(strict_types=1);

namespace OAuth\Server;

class Config
{
    public const CONFIG_ACCESS_LIFETIME = 'access_token_lifetime'; // The lifetime of access token in seconds.
    public const CONFIG_REFRESH_LIFETIME = 'refresh_token_lifetime'; // The lifetime of refresh token in seconds.
    public const CONFIG_AUTH_LIFETIME = 'auth_code_lifetime'; // The lifetime of auth code in seconds.
    public const CONFIG_SUPPORTED_SCOPES = 'supported_scopes'; // Array of scopes you want to support
    public const CONFIG_TOKEN_TYPE = 'token_type'; // Token type to respond with. Currently only "Bearer" supported.
    public const CONFIG_WWW_REALM = 'realm';
    public const CONFIG_ENFORCE_INPUT_REDIRECT = 'enforce_redirect'; // Set to true to enforce redirect_uri on input for both authorize and token steps.
    public const CONFIG_ENFORCE_STATE = 'enforce_state'; // Set to true to enforce state to be passed in authorization (see http://tools.ietf.org/html/draft-ietf-oauth-v2-21#section-10.12)
    public const CONFIG_RESPONSE_EXTRA_HEADERS = 'response_extra_headers'; // Add extra headers to the response

    private const DEFAULT_ACCESS_TOKEN_LIFETIME = 3600;
    private const DEFAULT_REFRESH_TOKEN_LIFETIME = 1209600;
    private const DEFAULT_AUTH_CODE_LIFETIME = 30;
    private const DEFAULT_WWW_REALM = 'Service';
    private const DEFAULT_TOKEN_TYPE = 'bearer';
    private const DEFAULT_ENFORCE_INPUT_REDIRECT = true;
    private const DEFAULT_ENFORCE_STATE = false;
    private const DEFAULT_SUPPORTED_SCOPES = null;
    private const DEFAULT_RESPONSE_EXTRA_HEADERS = [];

    /** @var array<string, mixed> */
    protected array $conf;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->conf = [
            self::CONFIG_ACCESS_LIFETIME => self::DEFAULT_ACCESS_TOKEN_LIFETIME,
            self::CONFIG_REFRESH_LIFETIME => self::DEFAULT_REFRESH_TOKEN_LIFETIME,
            self::CONFIG_AUTH_LIFETIME => self::DEFAULT_AUTH_CODE_LIFETIME,
            self::CONFIG_WWW_REALM => self::DEFAULT_WWW_REALM,
            self::CONFIG_TOKEN_TYPE => self::DEFAULT_TOKEN_TYPE,
            self::CONFIG_ENFORCE_INPUT_REDIRECT => self::DEFAULT_ENFORCE_INPUT_REDIRECT,
            self::CONFIG_ENFORCE_STATE => self::DEFAULT_ENFORCE_STATE,
            self::CONFIG_SUPPORTED_SCOPES => self::DEFAULT_SUPPORTED_SCOPES,
            self::CONFIG_RESPONSE_EXTRA_HEADERS => self::DEFAULT_RESPONSE_EXTRA_HEADERS,
        ];

        foreach ($config as $name => $value) {
            $this->setVariable($name, $value);
        }
    }

    public function getVariable(string $name, mixed $default = null): mixed
    {
        $name = strtolower($name);

        return $this->conf[$name] ?? $default;
    }

    public function setVariable(string $name, mixed $value): self
    {
        $name = strtolower($name);

        $this->conf[$name] = $value;

        return $this;
    }
}
