<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Timestampable;

trait UpdatedAtTimestampableMethodsTrait
{
    public function setUpdatedAt(\DateTimeImmutable $dateTime): void
    {
        $this->updatedAt = $dateTime;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
