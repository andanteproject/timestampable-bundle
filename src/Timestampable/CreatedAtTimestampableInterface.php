<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Timestampable;

interface CreatedAtTimestampableInterface
{
    public function getCreatedAt(): ?\DateTimeImmutable;

    public function setCreatedAt(\DateTimeImmutable $createdAt): void;
}
