<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Functional;

use Andante\TimestampableBundle\Tests\App\TimestampableAppKernel;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class FunctionalTestCase extends KernelTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        self::ensureKernelShutdown();
    }

    protected static function getKernelClass(): string
    {
        return TimestampableAppKernel::class;
    }

    /**
     * Return the test container. Uses KernelTestCase::getContainer() on Symfony 5.3+,
     * or the kernel's container on older versions.
     */
    protected static function getTestContainer(): ContainerInterface
    {
        if (KernelTestCase::class === static::class) {
            $kernel = static::$kernel;
            \assert(null !== $kernel, 'Kernel must be booted');

            return $kernel->getContainer();
        }

        return self::getContainer();
    }

    protected function createSchema(): void
    {
        /** @var ManagerRegistry $manager */
        $manager = self::getTestContainer()->get('doctrine');
        /** @var EntityManagerInterface[] $ems */
        $ems = $manager->getManagers();
        /** @var EntityManagerInterface $em */
        $em = \reset($ems);
        /** @var list<ClassMetadata<object>> $metadatas */
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema($metadatas);
        $schemaTool->createSchema($metadatas);
    }
}
