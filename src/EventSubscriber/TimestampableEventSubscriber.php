<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\EventSubscriber;

use Andante\TimestampableBundle\Timestampable\CreatedAtTimestampableInterface;
use Andante\TimestampableBundle\Timestampable\Registry;
use Andante\TimestampableBundle\Timestampable\UpdatedAtTimestampableInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Psr\Clock\ClockInterface;

class TimestampableEventSubscriber implements EventSubscriber
{
    private Registry $registry;
    private ClockInterface $clock;

    public function __construct(
        Registry $registry,
        ClockInterface $clock,
    ) {
        $this->registry = $registry;
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

    public function prePersist(PrePersistEventArgs $onFlushEventArgs): void
    {
        $entity = $onFlushEventArgs->getObject();
        if ($entity instanceof CreatedAtTimestampableInterface && null === $entity->getCreatedAt()) {
            $entity->setCreatedAt($this->clock->now());
        }
    }

    public function preUpdate(PreUpdateEventArgs $onFlushEventArgs): void
    {
        $entity = $onFlushEventArgs->getObject();
        $metadata = $this->registry->getTimestampableMetadata(\get_class($entity));
        if (null === $metadata || null === $metadata->getUpdatedAt()) {
            return;
        }
        if (!$entity instanceof UpdatedAtTimestampableInterface) {
            return;
        }
        // Skipping update of updatedAt property if it has been changed manually
        $updatedAtPropertyName = $metadata->getUpdatedAt()->getPropertyName();
        $entityUpdatedPropertiesNames = \array_keys($onFlushEventArgs->getEntityChangeSet());
        if (!\in_array($updatedAtPropertyName, $entityUpdatedPropertiesNames, true)) {
            $entity->setUpdatedAt($this->clock->now());
        }
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        /** @var \ReflectionClass<object>|null $rClass */
        $rClass = $classMetadata->reflClass;
        if (null === $rClass) {
            return;
        }

        $className = $rClass->getName();
        $metadata = $this->registry->getTimestampableMetadata($className);
        if (null === $metadata) {
            return;
        }

        $createdAt = $metadata->getCreatedAt();
        if (null !== $createdAt && !$classMetadata->hasField($createdAt->getPropertyName())) {
            $classMetadata->mapField([
                'fieldName' => $createdAt->getPropertyName(),
                'type' => Types::DATETIME_IMMUTABLE,
                'nullable' => false,
                'columnName' => $createdAt->getColumnName(),
            ]);
        }
        $updatedAt = $metadata->getUpdatedAt();
        if (null !== $updatedAt && !$classMetadata->hasField($updatedAt->getPropertyName())) {
            $classMetadata->mapField([
                'fieldName' => $updatedAt->getPropertyName(),
                'type' => Types::DATETIME_IMMUTABLE,
                'nullable' => true,
                'columnName' => $updatedAt->getColumnName(),
            ]);
        }
    }
}
