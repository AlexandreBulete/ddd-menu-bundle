<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Command\RenameMenu;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\Repository\MenuRepositoryInterface;
use Psr\Clock\ClockInterface;

#[AsCommandHandler]
final readonly class RenameMenuHandler
{
    public function __construct(
        private MenuRepositoryInterface $menus,
        private ClockInterface $clock,
    ) {}

    public function __invoke(RenameMenuCommand $command): Menu
    {
        $menu = $this->menus->findById($command->id)
            ?? throw new EntityNotFoundException(Menu::class, $command->id);
        $menu->rename($command->title, $this->clock->now());
        $this->menus->save($menu);

        return $menu;
    }
}
