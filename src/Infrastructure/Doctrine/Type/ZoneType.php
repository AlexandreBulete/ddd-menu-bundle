<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddDoctrineBridge\Type\VarcharType;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;

final class ZoneType extends VarcharType
{
    public const string NAME = 'menu_zone';

    protected string $name = self::NAME;
    protected int $length = Zone::MAX_LENGTH;
    protected string $voClass = Zone::class;
}
