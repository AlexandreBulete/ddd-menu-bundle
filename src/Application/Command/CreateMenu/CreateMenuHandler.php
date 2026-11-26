<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Command\CreateMenu;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddMenuBundle\Application\Service\ZoneGuard;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\Repository\MenuRepositoryInterface;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuId;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class CreateMenuHandler
{
    public function __construct(
        private MenuRepositoryInterface $menus,
        private ZoneGuard $zones,
        private ClockInterface $clock,
    ) {}

    public function __invoke(CreateMenuCommand $command): Menu
    {
        $this->zones->assertCanHold($command->zone);

        $now = $this->clock->now();
        $menu = Menu::create(MenuId::generate(), $command->zone, $command->title, $command->position, $now);
        $menu->changeItems($command->items, $now);
        $this->menus->save($menu);

        return $menu;
    }
}
