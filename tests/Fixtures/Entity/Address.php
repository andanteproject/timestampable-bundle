<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Fixtures\Entity;

use Andante\TimestampableBundle\Timestampable\TimestampableInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Address implements TimestampableInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private ?string $name = null;

    private ?\DateTimeImmutable $created = null;
    private ?\DateTimeImmutable $updated = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created;
    }

    public function setCreatedAt(\DateTimeImmutable $created): void
    {
        $this->created = $created;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated): void
    {
        $this->updated = $updated;
    }
}
