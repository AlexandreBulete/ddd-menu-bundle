<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Command\DeleteMenu;

use AlexandreBulete\DddFoundation\Application\Command\AsCommandHandler;
use AlexandreBulete\DddFoundation\Domain\Exception\EntityNotFoundException;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\Repository\MenuRepositoryInterface;

#[AsCommandHandler]
final readonly class DeleteMenuHandler
{
    public function __construct(
        private MenuRepositoryInterface $menus,
    ) {}

    public function __invoke(DeleteMenuCommand $command): void
    {
        $menu = $this->menus->findById($command->id)
            ?? throw new EntityNotFoundException(Menu::class, $command->id);
        $menu->delete();
        $this->menus->remove($menu);
    }
}
