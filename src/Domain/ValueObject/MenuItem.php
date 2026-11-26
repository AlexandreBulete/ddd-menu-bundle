<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Domain\ValueObject;

/**
 * One link of a menu. Without a label, the link shows its destination's own
 * name (the page's title, the route's label).
 */
final readonly class MenuItem
{
    public const int LABEL_MAX_LENGTH = 100;

    public ?string $label;

    public function __construct(
        public LinkTarget $target,
        ?string $label = null,
    ) {
        $label = $label === null ? null : trim($label);
        if ($label !== null && mb_strlen($label) > self::LABEL_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('A link label has %d characters at most.', self::LABEL_MAX_LENGTH));
        }
        if ($label === null || $label === '') {
            if ($target->isUrl()) {
                throw new \InvalidArgumentException('A link to an address needs a label.');
            }
            $label = null;
        }
        $this->label = $label;
    }

    public function equals(self $other): bool
    {
        return $this->label === $other->label && $this->target->equals($other->target);
    }
}
