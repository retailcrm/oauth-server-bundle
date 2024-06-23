<?php

declare(strict_types=1);

namespace OAuth\Server\BearerToken;

use Symfony\Component\HttpFoundation\Request;

class ChainExtractor implements ExtractorInterface
{
    /**
     * @param ExtractorInterface[] $extractors
     */
    public function __construct(private array $extractors = [])
    {
    }

    public function addExtractor(ExtractorInterface $extractor): void
    {
        $this->extractors[] = $extractor;
    }

    public function extract(Request $request, bool $removeFromRequest = false): ?string
    {
        foreach ($this->extractors as $extractor) {
            $token = $extractor->extract($request, $removeFromRequest);
            if (null !== $token) {
                return $token;
            }
        }

        return null;
    }
}
