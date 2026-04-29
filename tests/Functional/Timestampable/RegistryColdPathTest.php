<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Functional\Timestampable;

use Andante\TimestampableBundle\Tests\App\TimestampableAppKernel;
use Andante\TimestampableBundle\Tests\Fixtures\Entity\Address;
use Andante\TimestampableBundle\Tests\KernelTestCase;
use Andante\TimestampableBundle\Timestampable\Registry;
use Symfony\Component\HttpKernel\KernelInterface;

class RegistryColdPathTest extends KernelTestCase
{
    /** @param array<string, mixed> $options */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TimestampableAppKernel('test', true, [
            'andante_timestampable' => [
                'metadata_cache_warmer_enabled' => false,
                'entity' => [
                    Address::class => [
                        'created_at_property_name' => 'created',
                        'updated_at_property_name' => 'updated',
                    ],
                ],
            ],
        ]);
    }

    public function testRegistryReturnsMetadataFromFactoryWhenCacheIsCold(): void
    {
        $registry = self::getTestContainer()->get(Registry::class);
        self::assertInstanceOf(Registry::class, $registry);

        $metadata = $registry->getTimestampableMetadata(Address::class);
        self::assertNotNull($metadata);
        self::assertSame(Address::class, $metadata->getEntityClass());
        self::assertNotNull($metadata->getCreatedAt());
        self::assertNotNull($metadata->getUpdatedAt());
        self::assertSame('created', $metadata->getCreatedAt()->getPropertyName());
        self::assertSame('updated', $metadata->getUpdatedAt()->getPropertyName());
    }
}
