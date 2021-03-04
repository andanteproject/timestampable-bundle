<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Functional;

use Andante\TimestampableBundle\Tests\Fixtures\Entity\Address;
use Andante\TimestampableBundle\Tests\Fixtures\Entity\Organization;
use Andante\TimestampableBundle\Tests\HttpKernel\AndanteTimestampableKernel;
use Andante\TimestampableBundle\Tests\KernelTestCase;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class MappingTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    protected static function createKernel(array $options = []): AndanteTimestampableKernel
    {
        /** @var AndanteTimestampableKernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->addConfig('/config/custom_mapping.yaml');

        return $kernel;
    }

    public function testMapping(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::$container->get('doctrine.orm.default_entity_manager');
        $classMetadata = $em->getClassMetadata(Organization::class);
        self::assertArrayHasKey('createdAt', $classMetadata->fieldMappings);
        self::assertArrayHasKey('updatedAt', $classMetadata->fieldMappings);
        self::assertSame('created_at', $classMetadata->getColumnName('createdAt'));
        self::assertSame('updated_at', $classMetadata->getColumnName('updatedAt'));
        self::assertSame(Types::DATETIME_IMMUTABLE, $classMetadata->fieldMappings['createdAt']['type']);
        self::assertSame(Types::DATETIME_IMMUTABLE, $classMetadata->fieldMappings['updatedAt']['type']);

        /** @var ClassMetadata $classMetadata */
        $classMetadata = $em->getClassMetadata(Address::class);
        self::assertArrayHasKey('created', $classMetadata->fieldMappings);
        self::assertArrayHasKey('updated', $classMetadata->fieldMappings);
        self::assertSame('created_date', $classMetadata->getColumnName('created'));
        self::assertSame('updated_date', $classMetadata->getColumnName('updated'));
        self::assertSame(Types::DATETIME_IMMUTABLE, $classMetadata->fieldMappings['created']['type']);
        self::assertSame(Types::DATETIME_IMMUTABLE, $classMetadata->fieldMappings['updated']['type']);
    }
}
