<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\Tests\Functional\EventSubscriber;

use Andante\TimestampableBundle\EventSubscriber\TimestampableEventSubscriber;
use Andante\TimestampableBundle\Tests\App\TimestampableAppKernel;
use Andante\TimestampableBundle\Tests\Fixtures\Entity\Address;
use Andante\TimestampableBundle\Tests\KernelTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\KernelInterface;

class SetupTest extends KernelTestCase
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

    public function testSubscriberSetup(): void
    {
        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = self::getTestContainer()->get('doctrine');
        /** @var EntityManagerInterface $em */
        foreach ($managerRegistry->getManagers() as $em) {
            $evm = $em->getEventManager();
            /** @var array<object> $listeners */
            $allListeners = $evm->getAllListeners();
            foreach ($allListeners as $name => $listeners) {
                if (\in_array($name, [Events::prePersist, Events::preUpdate, Events::loadClassMetadata])) {
                    $listenerRegistered = \array_reduce($listeners, static fn (
                        bool $carry, $service,
                    ) => $carry ? $carry : $service instanceof TimestampableEventSubscriber, false);

                    self::assertTrue($listenerRegistered);
                }
            }
        }
    }
}
