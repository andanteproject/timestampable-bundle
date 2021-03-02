<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Timestampable;

trait CreatedAtTimestampableMethodsTrait
{
    public function setCreatedAt(\DateTimeImmutable $dateTime): void
    {
        $this->createdAt = $dateTime;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
