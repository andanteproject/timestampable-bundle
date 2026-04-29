<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Functional\DependencyInjection;

use Andante\TimestampableBundle\Tests\App\TimestampableAppKernel;
use Andante\TimestampableBundle\Tests\Fixtures\Entity\Address;
use Andante\TimestampableBundle\Tests\Fixtures\Entity\Organization;
use Andante\TimestampableBundle\Tests\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class DefaultConfigTest extends KernelTestCase
{
    /** @param array<string, mixed> $options */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TimestampableAppKernel('test', true, [
            'andante_timestampable' => [
                'entity' => [
                    Address::class => [
                        'created_at_property_name' => 'created',
                        'updated_at_property_name' => 'updated',
                    ],
                ],
            ],
        ]);
    }

    public function testDefaultPropertyNamesWorkWithNoConfig(): void
    {
        $this->createSchema();
        /** @var EntityManagerInterface $em */
        $em = self::getTestContainer()->get('doctrine.orm.default_entity_manager');

        $organization = (new Organization())->setName('Org1');
        $em->persist($organization);
        $em->flush();

        $repository = $em->getRepository(Organization::class);
        /** @var Organization|null $loaded */
        $loaded = $repository->findOneBy(['name' => 'Org1']);
        self::assertNotNull($loaded);
        self::assertNotNull($loaded->getCreatedAt(), 'Default createdAt should be set on persist.');
        self::assertNull($loaded->getUpdatedAt(), 'updatedAt should be null before update.');

        $loaded->setName('Org1-updated');
        $em->flush();

        /** @var Organization|null $reloaded */
        $reloaded = $repository->findOneBy(['name' => 'Org1-updated']);
        self::assertNotNull($reloaded);
        self::assertNotNull($reloaded->getUpdatedAt(), 'Default updatedAt should be set on update.');
    }
}
