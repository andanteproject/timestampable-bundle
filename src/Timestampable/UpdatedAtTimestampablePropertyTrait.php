<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Timestampable;

trait UpdatedAtTimestampablePropertyTrait
{
    private ?\DateTimeImmutable $updatedAt = null;
}
