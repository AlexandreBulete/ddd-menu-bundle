<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddDoctrineBridge\Type\GuidType;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuId;

final class MenuIdType extends GuidType
{
    public const string NAME = 'menu_id';

    protected string $name = self::NAME;
    protected string $voClass = MenuId::class;
}
