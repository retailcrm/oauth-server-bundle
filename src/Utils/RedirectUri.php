<?php

declare(strict_types=1);

namespace OAuth\Utils;

class RedirectUri
{
    /**
     * @param array<int, string> $storedUris
     */
    public static function validate(string $inputUri, array $storedUris): bool
    {
        if (!$inputUri || !$storedUris) {
            return false;
        }

        $parsed = parse_url($inputUri);

        if (!$parsed) {
            return false;
        }

        if (isset($parsed['path'])) {
            $path = urldecode($parsed['path']);
            if (preg_match('#/\.\.?(/|$)#', $path)) {
                return false;
            }
        }

        foreach ($storedUris as $storedUri) {
            if (parse_url($inputUri, PHP_URL_HOST) === parse_url($storedUri, PHP_URL_HOST)
                && parse_url($inputUri, PHP_URL_PORT) === parse_url($storedUri, PHP_URL_PORT)
                && (0 === strcasecmp(substr($inputUri, 0, strlen($storedUri)), $storedUri))) {
                return true;
            }
        }

        return false;
    }
}
