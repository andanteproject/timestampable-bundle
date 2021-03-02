<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\DependencyInjection\Compiler;

use Andante\TimestampableBundle\EventSubscriber\TimestampableEventSubscriber;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineEventSubscriberPass implements CompilerPassInterface
{
    public const TIMESTAMPABLE_SUBSCRIBER_SERVICE_ID = 'andante_timestampable.doctrine.timestampable_subscriber';

    public function process(ContainerBuilder $container): void
    {
        $container
            ->register(
                self::TIMESTAMPABLE_SUBSCRIBER_SERVICE_ID,
                TimestampableEventSubscriber::class
            )
            ->addArgument(new Reference('andante_timestampable.configuration'))
            ->addTag('doctrine.event_subscriber');
    }
}
