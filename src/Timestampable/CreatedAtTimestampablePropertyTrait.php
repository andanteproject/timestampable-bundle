<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Timestampable;

trait CreatedAtTimestampablePropertyTrait
{
    private ?\DateTimeImmutable $createdAt = null;
}
