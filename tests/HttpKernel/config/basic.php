<?php

declare(strict_types=1);

use Andante\TimestampableBundle\Tests\Fixtures\Entity\Address;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('andante_timestampable', [
        'entity' => [
            Address::class => [
                'created_at_property_name' => 'created',
                'updated_at_property_name' => 'updated',
            ],
        ],
    ]);
};
