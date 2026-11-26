<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddMenuBundle\Application\Command\ChangeMenuItems\ChangeMenuItemsCommand;
use AlexandreBulete\DddMenuBundle\Application\Command\MoveMenu\MoveMenuCommand;
use AlexandreBulete\DddMenuBundle\Application\Command\RenameMenu\RenameMenuCommand;
use AlexandreBulete\DddMenuBundle\Application\Query\FindMenu\FindMenuQuery;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuId;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;
use AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\Resource\MenuResource;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

/**
 * One form, several intents: each part that changed is its own use case.
 */
final readonly class UpdateMenuProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
        private QueryBusInterface $queryBus,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): MenuResource
    {
        Assert::isInstanceOf($data, MenuResource::class);
        Assert::isInstanceOf($data->id, Ulid::class);
        Assert::stringNotEmpty($data->zone);

        $id = MenuId::fromUlid($data->id);
        /** @var Menu $menu */
        $menu = $this->queryBus->ask(new FindMenuQuery($id));

        $title = trim($data->title ?? '');
        if ($title !== $menu->title) {
            /** @var Menu $menu */
            $menu = $this->commandBus->dispatch(new RenameMenuCommand($id, $title));
        }
        $zone = Zone::fromString($data->zone);
        if (!$zone->equals($menu->zone) || $data->position !== $menu->position) {
            /** @var Menu $menu */
            $menu = $this->commandBus->dispatch(new MoveMenuCommand($id, $zone, $data->position));
        }
        /** @var Menu $menu */
        $menu = $this->commandBus->dispatch(new ChangeMenuItemsCommand($id, $data->menuItems()));

        return MenuResource::fromModel($menu);
    }
}
