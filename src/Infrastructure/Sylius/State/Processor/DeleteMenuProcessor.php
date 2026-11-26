<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\State\Processor;

use AlexandreBulete\DddMenuBundle\Application\Command\DeleteMenu\DeleteMenuCommand;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuId;
use AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\Resource\MenuResource;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Sylius\Resource\Context\Context;
use Sylius\Resource\Metadata\Operation;
use Sylius\Resource\State\ProcessorInterface;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

final readonly class DeleteMenuProcessor implements ProcessorInterface
{
    public function __construct(
        private CommandBusInterface $commandBus,
    ) {}

    public function process(mixed $data, Operation $operation, Context $context): mixed
    {
        Assert::isInstanceOf($data, MenuResource::class);
        Assert::isInstanceOf($data->id, Ulid::class);
        $this->commandBus->dispatch(new DeleteMenuCommand(MenuId::fromUlid($data->id)));

        return null;
    }
}
