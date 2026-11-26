<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

/**
 * Loaded only when `page.admin.enabled` is true: everything Sylius, nothing
 * else. Resources are DTOs, not services.
 */
return static function (ContainerConfigurator $container): void {
    $src = dirname(__DIR__) . '/src';
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('AlexandreBulete\\DddMenuBundle\\Infrastructure\\Sylius\\', $src . '/Infrastructure/Sylius/')
        ->exclude([$src . '/Infrastructure/Sylius/Resource']);
    $services->load('AlexandreBulete\\DddMenuBundle\\Infrastructure\\Symfony\\', $src . '/Infrastructure/Symfony/');
};
