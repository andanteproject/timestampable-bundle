<?php

declare(strict_types=1);

namespace Andante\TimestampableBundle\DependencyInjection;

use Andante\TimestampableBundle\Config\EntityConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('andante_timestampable');

        // @formatter:off
        /** @var ArrayNodeDefinition $node */
        $node = $treeBuilder->getRootNode();
        $node->children()
            ->arrayNode('default')
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode('created_at_property_name')
                        ->defaultValue(EntityConfiguration::DEFAULT_CREATED_AT_PROPERTY_NAME)
                        ->info('Entity property to be mapped as createdAt property')
                    ->end()
                    ->scalarNode('created_at_column_name')
                        ->defaultNull()
                        ->info('Database column name to be used. Set to "null" to use default doctrine naming strategy')
                    ->end()
                    ->scalarNode('updated_at_property_name')
                        ->defaultValue(EntityConfiguration::DEFAULT_UPDATED_AT_PROPERTY_NAME)
                        ->info('Entity property to be mapped as updatedAt property')
                        ->end()
                    ->scalarNode('updated_at_column_name')
                        ->defaultNull()
                        ->info('Database column name to be used. Set to "null" to use default doctrine naming strategy')
                    ->end()
                ->end()
            ->end()
            ->arrayNode('entity')
                ->arrayPrototype()
                ->children()
                    ->scalarNode('entity')
                    ->end()
                    ->scalarNode('created_at_property_name')
                        ->defaultValue(EntityConfiguration::DEFAULT_CREATED_AT_PROPERTY_NAME)
                        ->info('Entity property to be mapped as createdAt property')
                    ->end()
                    ->scalarNode('created_at_column_name')
                        ->info('Database column name to be used. Set to "null" to use default doctrine naming strategy')
                    ->end()
                    ->scalarNode('updated_at_property_name')
                        ->defaultValue(EntityConfiguration::DEFAULT_UPDATED_AT_PROPERTY_NAME)
                        ->info('Entity property to be mapped as updatedAt property')
                    ->end()
                    ->scalarNode('updated_at_column_name')
                        ->defaultNull()
                        ->info('Database column name to be used. Set to "null" to use default doctrine naming strategy')
                    ->end()
                ->end()
            ->end()
        ->end();
        // @formatter:on

        return $treeBuilder;
    }
}
