<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use AlexandreBulete\DddMenuBundle\Application\Command\CreateMenu\CreateMenuCommand;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;
use AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\Resource\MenuResource;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Webmozart\Assert\Assert;

final readonly class CreateMenuProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): MenuResource
    {
        Assert::isInstanceOf($data, MenuResource::class);
        Assert::stringNotEmpty($data->zone);

        /** @var Menu $menu */
        $menu = $this->commandBus->dispatch(new CreateMenuCommand(
            Zone::fromString($data->zone),
            $data->title ?? '',
            $data->position,
            $data->menuItems(),
        ));

        return MenuResource::fromModel($menu);
    }
}
