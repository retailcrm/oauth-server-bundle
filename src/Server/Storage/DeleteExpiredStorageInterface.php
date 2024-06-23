<?php

declare(strict_types=1);

namespace OAuth\Server\Storage;

interface DeleteExpiredStorageInterface
{
    public function deleteExpired(): int;
}
