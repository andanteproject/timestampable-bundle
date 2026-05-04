<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\DependencyInjection;

use Andante\TimestampableBundle\Cache\Adapter\ArrayAdapter;
use Andante\TimestampableBundle\CacheClearer\TimestampableCacheClearer;
use Andante\TimestampableBundle\CacheWarmer\TimestampableCacheWarmer;
use Andante\TimestampableBundle\Config\Configuration;
use Andante\TimestampableBundle\DependencyInjection\Configuration as BundleConfiguration;
use Andante\TimestampableBundle\Timestampable\Metadata\MetadataFactory;
use Andante\TimestampableBundle\Timestampable\Registry;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class AndanteTimestampableExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new BundleConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('andante_timestampable.metadata_cache_warmer_enabled', $config['metadata_cache_warmer_enabled']);

        $container
            ->setDefinition('andante_timestampable.configuration', new Definition(Configuration::class))
            ->setFactory([Configuration::class, 'createFromArray'])
            ->setArguments([$config]);

        $metadataPhpArrayFile = '%kernel.cache_dir%/timestampable_metadata.php';

        $container->register(MetadataFactory::class, MetadataFactory::class)
            ->addArgument(new Reference('andante_timestampable.configuration'));

        $container->register(ArrayAdapter::class, ArrayAdapter::class);

        $container->register(Registry::class, Registry::class)
            ->addArgument(new Reference(MetadataFactory::class))
            ->addArgument(new Definition(
                PhpArrayAdapter::class,
                [
                    $metadataPhpArrayFile,
                    new Reference(ArrayAdapter::class),
                ]
            ));

        $container->register(TimestampableCacheWarmer::class, TimestampableCacheWarmer::class)
            ->addArgument(new Reference(MetadataFactory::class))
            ->addArgument(new Reference('doctrine'))
            ->addArgument($metadataPhpArrayFile)
            ->addTag('kernel.cache_warmer', ['priority' => -10]);

        $container->register(TimestampableCacheClearer::class, TimestampableCacheClearer::class)
            ->addTag('kernel.cache_clearer');
    }
}
