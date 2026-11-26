<?php

declare(strict_types=1);

use AlexandreBulete\DddMenuBundle\Domain\Repository\MenuRepositoryInterface;
use AlexandreBulete\DddMenuBundle\Domain\Service\DomainEventPublisherInterface;
use AlexandreBulete\DddMenuBundle\Domain\Service\ZoneCatalogInterface;
use AlexandreBulete\DddMenuBundle\Infrastructure\Config\ConfiguredZoneCatalog;
use AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\DoctrineMenuRepository;
use AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\ImmediateEventPublisher;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $src = dirname(__DIR__) . '/src';
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('AlexandreBulete\\DddMenuBundle\\', $src . '/')
        ->exclude([
            // Aggregates and value objects are never services.
            $src . '/Domain',
            $src . '/DddMenuBundle.php',
            $src . '/Infrastructure/Link/ResolvedLink.php',
            // Doctrine instantiates types itself.
            $src . '/Infrastructure/Doctrine/Type',
            $src . '/Infrastructure/Doctrine/Mapping',
            // Conditional: services_migrations.php, services_admin.php.
            $src . '/Infrastructure/Doctrine/Migrations',
            $src . '/Infrastructure/Sylius',
            $src . '/Infrastructure/Symfony',
        ]);

    // ── Ports → adapters ────────────────────────────────────────────────────
    $services->alias(MenuRepositoryInterface::class, DoctrineMenuRepository::class);
    $services->alias(DomainEventPublisherInterface::class, ImmediateEventPublisher::class);
    $services->alias(ZoneCatalogInterface::class, ConfiguredZoneCatalog::class);
};
