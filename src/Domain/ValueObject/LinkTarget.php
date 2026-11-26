<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Domain\ValueObject;

/**
 * Where a link goes: an address typed in (`url`), or a destination the
 * application offers (`route` + its name, `page` + an id…), resolved to an
 * address when the menu is printed. The menu never stores a resolved URL:
 * a page that moves keeps its links.
 */
final readonly class LinkTarget
{
    public const string URL = 'url';
    public const int REFERENCE_MAX_LENGTH = 255;
    public const string KIND_PATTERN = '[a-z][a-z0-9_]{0,29}';

    public string $kind;
    public string $reference;

    public function __construct(string $kind, string $reference)
    {
        $reference = trim($reference);
        if (preg_match('/^' . self::KIND_PATTERN . '$/', $kind) !== 1) {
            throw new \InvalidArgumentException(sprintf('Invalid link kind "%s".', $kind));
        }
        if ($reference === '' || strlen($reference) > self::REFERENCE_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('A link has a target of %d characters at most.', self::REFERENCE_MAX_LENGTH));
        }
        if ($kind === self::URL && !self::isAddress($reference)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not an address: http(s)://…, mailto:…, or a path starting with /.', $reference));
        }

        $this->kind = $kind;
        $this->reference = $reference;
    }

    public static function url(string $address): self
    {
        return new self(self::URL, $address);
    }

    public static function isAddress(string $value): bool
    {
        return preg_match('~^(?:https?://[^\s/]+\S*|mailto:\S+@\S+|/(?!/)\S*)$~i', $value) === 1;
    }

    public function isUrl(): bool
    {
        return $this->kind === self::URL;
    }

    public function equals(self $other): bool
    {
        return $this->kind === $other->kind && $this->reference === $other->reference;
    }
}
