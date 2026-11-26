<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Domain\ValueObject;

use AlexandreBulete\DddFoundation\Domain\ValueObject\StringVO;

/**
 * Where a template prints menus (`main`, `footer`…), as the key the
 * application declares. Which zones exist is the application's business.
 */
final readonly class Zone extends StringVO
{
    public const int MAX_LENGTH = 50;
    public const string PATTERN = '[a-z][a-z0-9_]*';

    protected function normalize(string $value): string
    {
        return trim($value);
    }

    protected function validate(string $value): void
    {
        if (strlen($value) > self::MAX_LENGTH || preg_match('/^' . self::PATTERN . '$/', $value) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid zone "%s": lowercase letters, digits and underscores, %d characters at most.', $value, self::MAX_LENGTH));
        }
    }
}
