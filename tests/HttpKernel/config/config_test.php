<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()
        ->set('kernel.secret', 'secret')
        ->set('locale', 'en');

    $containerConfigurator->extension('framework', [
        'test' => true,
    ]);

    $services = $containerConfigurator->services();
    $services->defaults()
        ->autowire()
        ->public()
        ->autoconfigure();

    $containerConfigurator->extension('doctrine', [
        'dbal' => [
            'url' => '%env(resolve:DATABASE_URL)%',
        ],
        'orm' => [
            'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
            'auto_mapping' => true,
            'mappings' => [
                'Fixtures' => [
                    'is_bundle' => false,
                    'dir' => '%kernel.project_dir%/tests/Fixtures/Entity/',
                    'prefix' => 'Andante\TimestampableBundle\Tests\Fixtures\Entity',
                    'alias' => 'Fixtures',
                ],
            ],
        ],
    ]);
};
