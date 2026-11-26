<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\Type;

use AlexandreBulete\DddMenuBundle\Domain\ValueObject\LinkTarget;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuItem;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\ValueNotConvertible;
use Doctrine\DBAL\Types\Type;

/**
 * The links of a menu, in order, on a JSON column: a menu is read and
 * written whole with them. JSONB on PostgreSQL, JSON elsewhere.
 */
final class MenuItemsType extends Type
{
    public const string NAME = 'menu_items';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getJsonbTypeDeclarationSQL($column);
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }
        if (!is_array($value)) {
            throw new \InvalidArgumentException(sprintf('Expected a list of %s, got %s.', MenuItem::class, get_debug_type($value)));
        }

        $items = [];
        foreach ($value as $item) {
            if (!$item instanceof MenuItem) {
                throw new \InvalidArgumentException(sprintf('Expected %s, got %s.', MenuItem::class, get_debug_type($item)));
            }
            $items[] = ['label' => $item->label, 'kind' => $item->target->kind, 'reference' => $item->target->reference];
        }

        return json_encode($items, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return list<MenuItem>|null
     */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?array
    {
        if ($value === null) {
            return null;
        }
        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }
        if (!is_string($value)) {
            throw ValueNotConvertible::new($value, self::class, 'expected a JSON string');
        }

        try {
            $decoded = json_decode($value, true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw ValueNotConvertible::new($value, self::class, $e->getMessage(), $e);
        }
        if (!is_array($decoded) || !array_is_list($decoded)) {
            throw ValueNotConvertible::new($value, self::class, 'expected a JSON array of links');
        }

        $items = [];
        foreach ($decoded as $row) {
            if (!is_array($row) || !is_string($row['kind'] ?? null) || !is_string($row['reference'] ?? null)) {
                throw ValueNotConvertible::new($value, self::class, 'expected {label, kind, reference} links');
            }
            $label = $row['label'] ?? null;
            $items[] = new MenuItem(new LinkTarget($row['kind'], $row['reference']), is_string($label) ? $label : null);
        }

        return $items;
    }
}
