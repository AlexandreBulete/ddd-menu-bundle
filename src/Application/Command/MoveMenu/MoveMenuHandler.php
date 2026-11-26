<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Command\MoveMenu;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use AlexandreBulete\DddMenuBundle\Application\Service\ZoneGuard;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\Repository\MenuRepositoryInterface;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class MoveMenuHandler
{
    public function __construct(
        private MenuRepositoryInterface $menus,
        private ZoneGuard $zones,
        private ClockInterface $clock,
    ) {}

    public function __invoke(MoveMenuCommand $command): Menu
    {
        $menu = $this->menus->findById($command->id)
            ?? throw new EntityNotFoundException(Menu::class, $command->id);
        $this->zones->assertCanHold($command->zone, $menu->id);
        $menu->move($command->zone, $command->position, $this->clock->now());
        $this->menus->save($menu);

        return $menu;
    }
}
