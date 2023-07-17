<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Functional;

use Andante\TimestampableBundle\Tests\Fixtures\Entity\Address;
use Andante\TimestampableBundle\Tests\Fixtures\Entity\Organization;
use Andante\TimestampableBundle\Tests\HttpKernel\AndanteTimestampableKernel;
use Andante\TimestampableBundle\Tests\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;

class TimestampableTest extends KernelTestCase
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

    public function testShouldSetTimestamps(): void
    {
        $this->createSchema();
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get('doctrine.orm.default_entity_manager');

        $address1 = (new Address())->setName('Address1');
        $organization1 = (new Organization())->setName('Organization1');

        $em->persist($address1);
        $em->persist($organization1);
        $em->flush();

        $addressRepository = $em->getRepository(Address::class);
        $organizationRepository = $em->getRepository(Organization::class);

        /** @var Address|null $address1 */
        $address1 = $addressRepository->findOneBy(['name' => 'Address1']);
        /** @var Organization|null $organization1 */
        $organization1 = $organizationRepository->findOneBy(['name' => 'Organization1']);
        self::assertNotNull($address1);
        self::assertNotNull($organization1);
        self::assertNotNull($address1->getCreatedAt());
        self::assertNotNull($organization1->getCreatedAt());
        self::assertNull($address1->getUpdatedAt());
        self::assertNull($organization1->getUpdatedAt());

        $createdAtAddress1 = $address1->getCreatedAt();
        $createdAtOrganization1 = $organization1->getCreatedAt();

        $address1->setName('Address1-updated');
        $organization1->setName('Organization1-updated');

        \sleep(2);
        $em->flush();
        \sleep(2); // Giving time to mysqlite to update file

        /** @var Address|null $address1 */
        $address1 = $addressRepository->findOneBy(['name' => 'Address1-updated']);
        /** @var Organization|null $organization1 */
        $organization1 = $organizationRepository->findOneBy(['name' => 'Organization1-updated']);
        self::assertNotNull($address1);
        self::assertNotNull($organization1);
        self::assertNotNull($address1->getCreatedAt());
        self::assertNotNull($organization1->getCreatedAt());
        self::assertNotNull($address1->getUpdatedAt());
        self::assertNotNull($organization1->getUpdatedAt());

        self::assertSame(
            $createdAtAddress1->format(\DateTimeInterface::ATOM),
            $address1->getCreatedAt()->format(\DateTimeInterface::ATOM)
        );
        self::assertSame(
            $createdAtOrganization1->format(\DateTimeInterface::ATOM),
            $organization1->getCreatedAt()->format(\DateTimeInterface::ATOM)
        );

        self::assertNotSame(
            $address1->getCreatedAt()->format(\DateTimeInterface::ATOM),
            $address1->getUpdatedAt()->format(\DateTimeInterface::ATOM)
        );
        self::assertNotSame(
            $organization1->getCreatedAt()->format(\DateTimeInterface::ATOM),
            $organization1->getUpdatedAt()->format(\DateTimeInterface::ATOM)
        );

        $updatedAtAddress1 = $address1->getUpdatedAt();
        $updatedAtOrganization1 = $organization1->getUpdatedAt();

        $address1->setName('Address1-updated2');
        $organization1->setName('Organization1-updated2');

        \sleep(2);
        $em->flush();
        \sleep(2); // Giving time to mysqlite to update file

        /** @var Address|null $address1 */
        $address1 = $addressRepository->findOneBy(['name' => 'Address1-updated2']);
        /** @var Organization|null $organization1 */
        $organization1 = $organizationRepository->findOneBy(['name' => 'Organization1-updated2']);
        self::assertNotNull($address1);
        self::assertNotNull($organization1);
        self::assertNotNull($address1->getCreatedAt());
        self::assertNotNull($organization1->getCreatedAt());
        self::assertNotNull($address1->getUpdatedAt());
        self::assertNotNull($organization1->getUpdatedAt());

        self::assertNotSame(
            $updatedAtAddress1->format(\DateTimeInterface::ATOM),
            $address1->getUpdatedAt()->format(\DateTimeInterface::ATOM)
        );
        self::assertNotSame(
            $updatedAtOrganization1->format(\DateTimeInterface::ATOM),
            $organization1->getUpdatedAt()->format(\DateTimeInterface::ATOM)
        );
    }
}
