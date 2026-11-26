<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\Admin\Menu;

use AlexandreBulete\DddSyliusBundle\Admin\Menu\MenuContributorInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * "Menus": the menus of the site.
 */
#[AutoconfigureTag('app.menu_contributor', ['priority' => 17])]
final readonly class MenusMenuContributor implements MenuContributorInterface
{
    public function __construct(
        private AuthorizationCheckerInterface $authorization,
    ) {}

    public function contribute(ItemInterface $menu): void
    {
        if (!$this->authorization->isGranted('menu.find_menus')) {
            return;
        }

        $menu->addChild('menus', ['route' => 'menu_admin_menu_index'])
            ->setLabel('menu.menu.index')
            ->setLabelAttribute('icon', 'tabler:menu-2');
    }
}
