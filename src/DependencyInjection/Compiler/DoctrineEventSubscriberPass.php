<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\DependencyInjection\Compiler;

use Andante\TimestampableBundle\EventSubscriber\TimestampableEventSubscriber;
use Composer\InstalledVersions;
use Doctrine\ORM\Events;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\Clock;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineEventSubscriberPass implements CompilerPassInterface
{
    public const TIMESTAMPABLE_SUBSCRIBER_SERVICE_ID = 'andante_timestampable.doctrine.timestampable_subscriber';
    public const CLOCK_SERVICE_ID = 'andante_timestampable.clock';

    public function process(ContainerBuilder $container): void
    {
        $subscriberDefinition = $container
            ->register(
                self::TIMESTAMPABLE_SUBSCRIBER_SERVICE_ID,
                TimestampableEventSubscriber::class
            )
            ->addArgument(new Reference('andante_timestampable.configuration'))
            ->addTag('doctrine.event_subscriber');
        $symfonyDoctrineBridgeVersion = InstalledVersions::getVersion('symfony/doctrine-bridge');
        if (null !== $symfonyDoctrineBridgeVersion && \version_compare($symfonyDoctrineBridgeVersion, '6.3', '>=')) {
            $subscriberDefinition->addTag('doctrine.event_listener', [
                'event' => Events::prePersist,
            ]);
            $subscriberDefinition->addTag('doctrine.event_listener', [
                'event' => Events::preUpdate,
            ]);
            $subscriberDefinition->addTag('doctrine.event_listener', [
                'event' => Events::loadClassMetadata,
            ]);
        } else {
            $subscriberDefinition->addTag('doctrine.event_subscriber');
        }

        if ($container->has(ClockInterface::class)) {
            $subscriberDefinition->addArgument(new Reference(ClockInterface::class));
        } else {
            $container->register(self::CLOCK_SERVICE_ID, Clock::class);
            $subscriberDefinition->addArgument(new Reference(self::CLOCK_SERVICE_ID));
        }
    }
}
