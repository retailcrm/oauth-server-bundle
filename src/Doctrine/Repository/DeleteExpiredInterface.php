<?php

declare(strict_types=1);

namespace OAuth\Doctrine\Repository;

interface DeleteExpiredInterface
{
    public function deleteTokenExpired(): int;
}
