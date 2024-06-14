<?php

declare(strict_types=1);

use Andante\TimestampableBundle\Tests\Fixtures\Entity\Address;
use Andante\TimestampableBundle\Tests\Fixtures\Entity\Organization;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('andante_timestampable', [
        'default' => [
            'created_at_property_name' => 'createdAt',
            'updated_at_property_name' => 'updatedAt',
        ],
        'entity' => [
            Organization::class => [
                'created_at_property_name' => 'createdAt',
            ],
            Address::class => [
                'created_at_property_name' => 'created',
                'updated_at_property_name' => 'updated',
                'created_at_column_name' => 'created_date',
                'updated_at_column_name' => 'updated_date',
            ],
        ],
    ]);
};
