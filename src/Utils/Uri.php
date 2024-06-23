<?php

declare(strict_types=1);

namespace OAuth\Utils;

class Uri
{
    public static function build(string $uri, array $params): string
    {
        $parseUrl = parse_url($uri);

        foreach ($params as $name => $value) {
            if (isset($parseUrl[$name])) {
                $parseUrl[$name] .= '&' . http_build_query($value);
            } else {
                $parseUrl[$name] = http_build_query($value);
            }
        }

        return
            ((isset($parseUrl['scheme'])) ? $parseUrl['scheme'] . '://' : '')
            . ((isset($parseUrl['user'])) ? $parseUrl['user'] . ((isset($parseUrl['pass'])) ? ':' . $parseUrl['pass'] : '') . '@' : '')
            . ($parseUrl['host'] ?? '')
            . ((isset($parseUrl['port'])) ? ':' . $parseUrl['port'] : '')
            . ($parseUrl['path'] ?? '')
            . ((isset($parseUrl['query'])) ? '?' . $parseUrl['query'] : '')
            . ((isset($parseUrl['fragment'])) ? '#' . $parseUrl['fragment'] : '');
    }
}
