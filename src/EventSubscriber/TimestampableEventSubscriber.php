<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\EventSubscriber;

use Andante\TimestampableBundle\Config\Configuration;
use Andante\TimestampableBundle\Timestampable\CreatedAtTimestampableInterface;
use Andante\TimestampableBundle\Timestampable\UpdatedAtTimestampableInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Clock\ClockInterface;

class TimestampableEventSubscriber implements EventSubscriber
{
    private Configuration $configuration;
    private ClockInterface $clock;

    public function __construct(
        Configuration $configuration,
        ClockInterface $clock
    ) {
        $this->configuration = $configuration;
        $this->clock = $clock;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
            Events::loadClassMetadata,
        ];
    }

    public function prePersist(LifecycleEventArgs $onFlushEventArgs): void
    {
        $entity = $onFlushEventArgs->getObject();
        if ($entity instanceof CreatedAtTimestampableInterface && null === $entity->getCreatedAt()) {
            $entity->setCreatedAt($this->clock->now());
        }
    }

    public function preUpdate(LifecycleEventArgs $onFlushEventArgs): void
    {
        $entity = $onFlushEventArgs->getObject();
        if ($entity instanceof UpdatedAtTimestampableInterface) {
            $entity->setUpdatedAt($this->clock->now());
        }
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        /** @var \ReflectionClass|null $rClass */
        $rClass = $classMetadata->reflClass;
        if (null === $rClass) {
            return;
        }

        $className = $rClass->getName();
        if (\is_a($className, CreatedAtTimestampableInterface::class, true)) {
            $createdAtPropertyName = $this->configuration->getCreatedAtPropertyNameForClass($className);
            if (!$classMetadata->hasField($createdAtPropertyName)) {
                // Map field
                $classMetadata->mapField([
                    'fieldName' => $createdAtPropertyName,
                    'type' => Types::DATETIME_IMMUTABLE,
                    'nullable' => false,
                    'columnName' => $this->configuration->getCreatedAtColumnNameForClass($className),
                ]);
            }
        }
        if (\is_a($className, UpdatedAtTimestampableInterface::class, true)) {
            $updatedAtPropertyName = $this->configuration->getUpdatedAtPropertyNameForClass($className);
            if (!$classMetadata->hasField($updatedAtPropertyName)) {
                // Map field
                $classMetadata->mapField([
                    'fieldName' => $updatedAtPropertyName,
                    'type' => Types::DATETIME_IMMUTABLE,
                    'nullable' => true,
                    'columnName' => $this->configuration->getUpdatedAtColumnNameForClass($className),
                ]);
            }
        }
    }
}
