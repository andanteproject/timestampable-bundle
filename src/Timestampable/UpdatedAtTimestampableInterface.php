<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Timestampable;

interface UpdatedAtTimestampableInterface
{
    public function getUpdatedAt(): ?\DateTimeImmutable;

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void;
}
