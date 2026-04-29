<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Functional\EventSubscriber;

use Andante\TimestampableBundle\Tests\App\Clock\ClockMock;
use Andante\TimestampableBundle\Tests\App\TimestampableAppKernel;
use Andante\TimestampableBundle\Tests\Fixtures\Entity\Address;
use Andante\TimestampableBundle\Tests\Fixtures\Entity\Organization;
use Andante\TimestampableBundle\Tests\Functional\TimestampableConfigTrait;
use Andante\TimestampableBundle\Tests\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class TimestampableTest extends KernelTestCase
{
    use TimestampableConfigTrait;

    private const SLEEP_BETWEEN_UPDATES_SECONDS = 1;

    /** @param array<string, mixed> $options */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TimestampableAppKernel('test', true, self::getTimestampableConfig());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSchema();
    }

    /**
     * Persist sets createdAt; updatedAt stays null until first update.
     * Asserts both default (Organization) and custom property names (Address).
     */
    public function testCreatedAtSetOnPersistUpdatedAtNullUntilFirstUpdate(): void
    {
        $em = $this->getEntityManager();
        $em->persist($address = (new Address())->setName('Address1'));
        $em->persist($organization = (new Organization())->setName('Organization1'));
        $em->flush();

        $address = $this->reload($em, Address::class, ['name' => 'Address1']);
        $organization = $this->reload($em, Organization::class, ['name' => 'Organization1']);

        self::assertNotNull($address->getCreatedAt(), 'Address: createdAt should be set on persist.');
        self::assertNotNull($organization->getCreatedAt(), 'Organization: createdAt should be set on persist.');
        self::assertNull($address->getUpdatedAt(), 'Address: updatedAt should be null before any update.');
        self::assertNull($organization->getUpdatedAt(), 'Organization: updatedAt should be null before any update.');
    }

    /**
     * First update sets updatedAt; createdAt is unchanged.
     */
    public function testUpdatedAtSetOnFirstUpdateCreatedAtUnchanged(): void
    {
        $em = $this->getEntityManager();
        $em->persist((new Address())->setName('Address1'));
        $em->persist((new Organization())->setName('Organization1'));
        $em->flush();

        $address = $this->reload($em, Address::class, ['name' => 'Address1']);
        $organization = $this->reload($em, Organization::class, ['name' => 'Organization1']);
        $createdAtAddress = $address->getCreatedAt();
        $createdAtOrganization = $organization->getCreatedAt();
        self::assertNotNull($createdAtAddress);
        self::assertNotNull($createdAtOrganization);

        $address->setName('Address1-updated');
        $organization->setName('Organization1-updated');
        \sleep(self::SLEEP_BETWEEN_UPDATES_SECONDS);
        $em->flush();

        $address = $this->reload($em, Address::class, ['name' => 'Address1-updated']);
        $organization = $this->reload($em, Organization::class, ['name' => 'Organization1-updated']);

        $addressCreatedAt = $address->getCreatedAt();
        $organizationCreatedAt = $organization->getCreatedAt();
        $addressUpdatedAt = $address->getUpdatedAt();
        $organizationUpdatedAt = $organization->getUpdatedAt();
        self::assertNotNull($addressCreatedAt);
        self::assertNotNull($organizationCreatedAt);
        self::assertNotNull($addressUpdatedAt, 'Address: updatedAt should be set on first update.');
        self::assertNotNull($organizationUpdatedAt, 'Organization: updatedAt should be set on first update.');
        self::assertSame(
            $createdAtAddress->format(\DateTimeInterface::ATOM),
            $addressCreatedAt->format(\DateTimeInterface::ATOM),
            'Address: createdAt must not change after update.'
        );
        self::assertSame(
            $createdAtOrganization->format(\DateTimeInterface::ATOM),
            $organizationCreatedAt->format(\DateTimeInterface::ATOM),
            'Organization: createdAt must not change after update.'
        );
        self::assertNotEquals(
            $addressCreatedAt->getTimestamp(),
            $addressUpdatedAt->getTimestamp(),
            'Address: updatedAt should differ from createdAt after update.'
        );
        self::assertNotEquals(
            $organizationCreatedAt->getTimestamp(),
            $organizationUpdatedAt->getTimestamp(),
            'Organization: updatedAt should differ from createdAt after update.'
        );
    }

    /**
     * Second update changes updatedAt again.
     */
    public function testUpdatedAtChangesOnSubsequentUpdate(): void
    {
        $em = $this->getEntityManager();
        $em->persist((new Address())->setName('Address1'));
        $em->flush();
        $address = $this->reload($em, Address::class, ['name' => 'Address1']);
        $address->setName('Address1-updated');
        \sleep(self::SLEEP_BETWEEN_UPDATES_SECONDS);
        $em->flush();
        $address = $this->reload($em, Address::class, ['name' => 'Address1-updated']);
        $firstUpdatedAt = $address->getUpdatedAt();
        self::assertNotNull($firstUpdatedAt, 'updatedAt should be set after first update.');

        $address->setName('Address1-updated2');
        \sleep(self::SLEEP_BETWEEN_UPDATES_SECONDS);
        $em->flush();
        $address = $this->reload($em, Address::class, ['name' => 'Address1-updated2']);
        $addressUpdatedAtAfterSecondUpdate = $address->getUpdatedAt();
        self::assertNotNull($addressUpdatedAtAfterSecondUpdate);
        self::assertNotEquals(
            $firstUpdatedAt->format(\DateTimeInterface::ATOM),
            $addressUpdatedAtAfterSecondUpdate->format(\DateTimeInterface::ATOM),
            'updatedAt should change on second update.'
        );
    }

    /**
     * Subscriber uses ClockInterface from the container: freeze time, persist and update,
     * then assert createdAt and updatedAt match the times returned by the clock.
     */
    public function testCreatedAtAndUpdatedAtUseClockServiceFromContainer(): void
    {
        $clock = $this->getClockMock();
        $em = $this->getEntityManager();

        $createdTime = new \DateTimeImmutable('2024-06-15 10:30:00');
        $clock->setFrozenTime($createdTime);
        $em->persist($address = (new Address())->setName('AddressWithFrozenTime'));
        $em->flush();

        $address = $this->reload($em, Address::class, ['name' => 'AddressWithFrozenTime']);
        $addressCreatedAt = $address->getCreatedAt();
        self::assertNotNull($addressCreatedAt);
        self::assertSame(
            $createdTime->format(\DateTimeInterface::ATOM),
            $addressCreatedAt->format(\DateTimeInterface::ATOM),
            'createdAt must equal the time returned by the Clock service.'
        );
        self::assertNull($address->getUpdatedAt(), 'updatedAt should be null until first update.');

        $updatedTime = new \DateTimeImmutable('2024-06-15 14:45:00');
        $clock->setFrozenTime($updatedTime);
        $address->setName('AddressWithFrozenTime-updated');
        $em->flush();

        $address = $this->reload($em, Address::class, ['name' => 'AddressWithFrozenTime-updated']);
        $addressUpdatedAt = $address->getUpdatedAt();
        self::assertNotNull($addressUpdatedAt);
        self::assertSame(
            $updatedTime->format(\DateTimeInterface::ATOM),
            $addressUpdatedAt->format(\DateTimeInterface::ATOM),
            'updatedAt must equal the time returned by the Clock service on update.'
        );
        $addressCreatedAtAfterUpdate = $address->getCreatedAt();
        self::assertNotNull($addressCreatedAtAfterUpdate);
        self::assertSame(
            $createdTime->format(\DateTimeInterface::ATOM),
            $addressCreatedAtAfterUpdate->format(\DateTimeInterface::ATOM),
            'createdAt must remain unchanged after update.'
        );
    }

    /**
     * Manually set updatedAt is preserved on flush; subscriber does not overwrite it.
     */
    public function testManualUpdatedAtIsNotOverwrittenBySubscriber(): void
    {
        $em = $this->getEntityManager();
        $em->persist($organization = (new Organization())->setName('Organization1'));
        $em->flush();
        $organization = $this->reload($em, Organization::class, ['name' => 'Organization1']);
        $createdAt = $organization->getCreatedAt();
        self::assertNotNull($createdAt, 'createdAt should be set after persist.');
        $manualUpdatedAt = new \DateTimeImmutable('2023-01-01 00:00:00');
        $organization->setUpdatedAt($manualUpdatedAt);
        $organization->setName('Organization1-updated');
        $em->flush();
        $organization = $this->reload($em, Organization::class, ['name' => 'Organization1-updated']);
        $organizationCreatedAtAfterFlush = $organization->getCreatedAt();
        $organizationUpdatedAtAfterFlush = $organization->getUpdatedAt();
        self::assertNotNull($organizationCreatedAtAfterFlush, 'createdAt must remain set.');
        self::assertNotNull($organizationUpdatedAtAfterFlush, 'updatedAt must remain set.');
        self::assertSame(
            $createdAt->format(\DateTimeInterface::ATOM),
            $organizationCreatedAtAfterFlush->format(\DateTimeInterface::ATOM),
            'createdAt must remain unchanged.'
        );
        self::assertSame(
            $manualUpdatedAt->format(\DateTimeInterface::ATOM),
            $organizationUpdatedAtAfterFlush->format(\DateTimeInterface::ATOM),
            'Manually set updatedAt must not be overwritten by the subscriber.'
        );
    }

    private function getEntityManager(): EntityManagerInterface
    {
        $em = self::getTestContainer()->get('doctrine.orm.default_entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $em);

        return $em;
    }

    private function getClockMock(): ClockMock
    {
        $clock = self::getTestContainer()->get(ClockInterface::class);
        self::assertInstanceOf(ClockMock::class, $clock);

        return $clock;
    }

    /**
     * @template T of object
     *
     * @param class-string<T>      $entityClass
     * @param array<string, mixed> $criteria
     *
     * @return T
     */
    private function reload(EntityManagerInterface $em, string $entityClass, array $criteria): object
    {
        $repository = $em->getRepository($entityClass);
        $entity = $repository->findOneBy($criteria);
        self::assertNotNull($entity, \sprintf('Entity %s with criteria %s should exist.', $entityClass, \json_encode($criteria)));

        return $entity;
    }
}
