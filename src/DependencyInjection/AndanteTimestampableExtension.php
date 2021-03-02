<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\DependencyInjection;

use Andante\TimestampableBundle\Config\Configuration;
use Andante\TimestampableBundle\DependencyInjection\Configuration as BundleConfiguration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AndanteTimestampableExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new BundleConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        $container
            ->setDefinition('andante_timestampable.configuration', new Definition(Configuration::class))
            ->setFactory([Configuration::class, 'createFromArray'])
            ->setArguments([$config]);
    }
}
