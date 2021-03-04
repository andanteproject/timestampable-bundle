<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Config;

class EntityConfiguration
{
    public const DEFAULT_CREATED_AT_PROPERTY_NAME = 'createdAt';
    public const DEFAULT_UPDATED_AT_PROPERTY_NAME = 'updatedAt';

    private string $createdAtPropertyName = self::DEFAULT_CREATED_AT_PROPERTY_NAME;
    private string $updatedAtPropertyName = self::DEFAULT_UPDATED_AT_PROPERTY_NAME;
    private ?string $createdAtColumnName = null;
    private ?string $updatedAtColumnName = null;

    public function getCreatedAtPropertyName(): string
    {
        return $this->createdAtPropertyName;
    }

    public function setCreatedAtPropertyName(string $createdAtPropertyName): void
    {
        $this->createdAtPropertyName = $createdAtPropertyName;
    }

    public function getUpdatedAtPropertyName(): string
    {
        return $this->updatedAtPropertyName;
    }

    public function setUpdatedAtPropertyName(string $updatedAtPropertyName): void
    {
        $this->updatedAtPropertyName = $updatedAtPropertyName;
    }

    public function getCreatedAtColumnName(): ?string
    {
        return $this->createdAtColumnName;
    }

    public function setCreatedAtColumnName(?string $createdAtColumnName): void
    {
        $this->createdAtColumnName = $createdAtColumnName;
    }

    public function getUpdatedAtColumnName(): ?string
    {
        return $this->updatedAtColumnName;
    }

    public function setUpdatedAtColumnName(?string $updatedAtColumnName): void
    {
        $this->updatedAtColumnName = $updatedAtColumnName;
    }

    public static function createFromArray(array $config, EntityConfiguration $fallbackConfig = null): self
    {
        $entityConfiguration = new self();
        if (\array_key_exists('created_at_property_name', $config)) {
            $entityConfiguration->setCreatedAtPropertyName($config['created_at_property_name']);
        } elseif (null !== $fallbackConfig) {
            $entityConfiguration->setCreatedAtPropertyName($fallbackConfig->getCreatedAtPropertyName());
        }
        if (\array_key_exists('created_at_column_name', $config)) {
            $entityConfiguration->setCreatedAtColumnName($config['created_at_column_name']);
        } elseif (null !== $fallbackConfig) {
            $entityConfiguration->setCreatedAtColumnName($fallbackConfig->getCreatedAtColumnName());
        }

        if (\array_key_exists('updated_at_property_name', $config)) {
            $entityConfiguration->setUpdatedAtPropertyName($config['updated_at_property_name']);
        } elseif (null !== $fallbackConfig) {
            $entityConfiguration->setUpdatedAtPropertyName($fallbackConfig->getUpdatedAtPropertyName());
        }
        if (\array_key_exists('updated_at_column_name', $config)) {
            $entityConfiguration->setUpdatedAtColumnName($config['updated_at_column_name']);
        } elseif (null !== $fallbackConfig) {
            $entityConfiguration->setUpdatedAtColumnName($fallbackConfig->getUpdatedAtColumnName());
        }

        return $entityConfiguration;
    }
}
