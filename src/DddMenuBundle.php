<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle;

use AlexandreBulete\DddMenuBundle\Infrastructure\Config\ConfiguredZoneCatalog;
use AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\Type\MenuIdType;
use AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\Type\MenuItemsType;
use AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\Type\ZoneType;
use AlexandreBulete\DddMenuBundle\Infrastructure\Link\LinkSourceInterface;
use AlexandreBulete\DddMenuBundle\Infrastructure\Link\RouteLinkSource;
use Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * The menus of a site — its main menu, the columns of its footer — composed
 * in a Sylius back office from the destinations the application offers.
 *
 * @phpstan-type MenuConfig array{admin: array{enabled: bool}, zones: array<string, array{label: string, single: bool}>, routes: array<string, string>}
 */
final class DddMenuBundle extends AbstractBundle
{
    protected string $extensionAlias = 'menu';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->arrayNode('admin')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                            ->info('Sylius back-office screens. Turn off for a headless deployment.')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('zones')
                    ->info('Where the templates print menus: key => label (or {label, single}). A single zone holds one menu.')
                    ->example(['main' => ['label' => 'Main menu', 'single' => true], 'footer' => 'Footer'])
                    ->useAttributeAsKey('key')
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(static fn (string $label): array => ['label' => $label])
                        ->end()
                        ->children()
                            ->scalarNode('label')->isRequired()->cannotBeEmpty()->end()
                            ->booleanNode('single')->defaultFalse()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('routes')
                    ->info('The site\'s own pages a link may point to: route name (without parameters) => label.')
                    ->example(['home' => 'Home', 'blog' => 'Blog'])
                    ->useAttributeAsKey('route')
                    ->scalarPrototype()->cannotBeEmpty()->end()
                ->end()
            ->end();
    }

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        /** @var MenuConfig $config */
        $container->import($this->getPath() . '/config/services.php');
        $services = $container->services();
        $services->get(ConfiguredZoneCatalog::class)->arg('$zones', $config['zones']);
        $services->get(RouteLinkSource::class)->arg('$routes', $config['routes']);
        if ($config['admin']['enabled']) {
            $container->import($this->getPath() . '/config/services_admin.php');
        }
        if (self::migrationsEnabled($builder)) {
            $container->import($this->getPath() . '/config/services_migrations.php');
        }
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerForAutoconfiguration(LinkSourceInterface::class)
            ->addTag('ddd_menu.link_source');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('doctrine', [
            'dbal' => ['types' => [MenuIdType::NAME => MenuIdType::class, ZoneType::NAME => ZoneType::class, MenuItemsType::NAME => MenuItemsType::class]],
            'orm' => [
                'mappings' => [
                    'Menu' => [
                        'type' => 'xml',
                        'is_bundle' => false,
                        'dir' => $this->getPath() . '/src/Infrastructure/Doctrine/Mapping',
                        'prefix' => 'AlexandreBulete\\DddMenuBundle\\Domain\\Model',
                        'alias' => 'Menu',
                    ],
                ],
            ],
        ]);
        if (self::migrationsEnabled($builder)) {
            $builder->prependExtensionConfig('doctrine_migrations', ['enable_service_migrations' => true]);
        }
        $builder->prependExtensionConfig('framework', [
            'translator' => ['paths' => [$this->getPath() . '/translations']],
        ]);

        if (self::adminEnabled($builder)) {
            // DddSyliusBundle only globs the application's own resources.
            $builder->prependExtensionConfig('sylius_resource', [
                'mapping' => ['paths' => [$this->getPath() . '/src/Infrastructure/Sylius/Resource']],
            ]);
            $builder->prependExtensionConfig('twig', [
                'form_themes' => ['@DddMenu/form/menu_items.html.twig'],
            ]);
        }
    }

    /**
     * Before the config tree is processed: the default (on) needs its own
     * fallback here.
     */
    private static function adminEnabled(ContainerBuilder $builder): bool
    {
        /** @var list<array<string, mixed>> $configs */
        $configs = $builder->getExtensionConfig('menu');
        $enabled = true;
        foreach ($configs as $config) {
            $admin = $config['admin'] ?? null;
            if (is_array($admin) && is_bool($admin['enabled'] ?? null)) {
                $enabled = $admin['enabled'];
            }
        }

        return $enabled;
    }

    private static function migrationsEnabled(ContainerBuilder $builder): bool
    {
        /** @var array<string, class-string> $bundles */
        $bundles = $builder->getParameter('kernel.bundles');

        return in_array(DoctrineMigrationsBundle::class, $bundles, true);
    }
}
