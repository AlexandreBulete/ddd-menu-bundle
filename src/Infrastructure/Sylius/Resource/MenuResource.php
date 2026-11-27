<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\Resource;

use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\LinkTarget;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\MenuItem;
use AlexandreBulete\DddMenuBundle\Infrastructure\Link\LinkResolver;
use AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\Grid\MenuGrid;
use AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\State\Processor\CreateMenuProcessor;
use AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\State\Processor\DeleteMenuProcessor;
use AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\State\Processor\UpdateMenuProcessor;
use AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\State\Provider\MenuItemProvider;
use AlexandreBulete\DddMenuBundle\Infrastructure\Symfony\Form\MenuType;
use AlexandreBulete\DddSyliusBundle\Admin\View\AdminRecordInterface;
use AlexandreBulete\DddSyliusBundle\Admin\View\StatusBadge;
use Sylius\Resource\Metadata\AsResource;
use Sylius\Resource\Metadata\Create;
use Sylius\Resource\Metadata\Delete;
use Sylius\Resource\Metadata\Index;
use Sylius\Resource\Metadata\Update;
use Sylius\Resource\Model\ResourceInterface;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Back-office form of a menu: its zone, its rank, its title and its links,
 * one row per link. A link is "kind:reference" (a destination the
 * application offers) or `url` with the address typed in.
 */
#[AsResource(
    alias: 'menu.menu',
    section: 'admin',
    formType: MenuType::class,
    templatesDir: '@SyliusAdminUi/crud',
    routePrefix: '/admin',
    driver: false,
    operations: [
        new Create(processor: CreateMenuProcessor::class),
        new Update(provider: MenuItemProvider::class, processor: UpdateMenuProcessor::class),
        new Delete(provider: MenuItemProvider::class, processor: DeleteMenuProcessor::class),
        new Index(grid: MenuGrid::NAME),
    ],
)]
final class MenuResource implements ResourceInterface, AdminRecordInterface
{
    public const string URL_CHOICE = LinkTarget::URL;

    /**
     * @param array<int, array{label?: ?string, link?: ?string, url?: ?string}> $items
     */
    public function __construct(
        public ?AbstractUid $id = null,
        #[Assert\Length(max: Menu::TITLE_MAX_LENGTH)]
        public ?string $title = null,
        #[Assert\NotBlank]
        public ?string $zone = null,
        #[Assert\Range(min: 0, max: 999)]
        public int $position = 0,
        #[Assert\Count(max: Menu::MAX_ITEMS)]
        public array $items = [],
        public ?string $zoneLabel = null,
        public int $itemCount = 0,
        public ?\DateTimeImmutable $updatedAt = null,
    ) {}

    public function getId(): ?AbstractUid
    {
        return $this->id;
    }

    /**
     * Every row has a destination; an address is a valid one, and comes with
     * a label.
     */
    #[Assert\Callback]
    public function validateItems(ExecutionContextInterface $context): void
    {
        foreach (array_values($this->items) as $i => $row) {
            $link = $row['link'] ?? null;
            $label = trim($row['label'] ?? '');
            if (mb_strlen($label) > MenuItem::LABEL_MAX_LENGTH) {
                $context->buildViolation('menu.item.label_too_long')->atPath("items[$i].label")->addViolation();
            }
            if ($link === null || $link === '') {
                $context->buildViolation('menu.item.link_required')->atPath("items[$i].link")->addViolation();
                continue;
            }
            if ($link !== self::URL_CHOICE) {
                continue;
            }
            if (!LinkTarget::isAddress(trim($row['url'] ?? ''))) {
                $context->buildViolation('menu.item.url_invalid')->atPath("items[$i].url")->addViolation();
            }
            if ($label === '') {
                $context->buildViolation('menu.item.label_required')->atPath("items[$i].label")->addViolation();
            }
        }
    }

    /**
     * @return list<MenuItem>
     */
    public function menuItems(): array
    {
        $items = [];
        foreach (array_values($this->items) as $row) {
            $link = $row['link'] ?? '';
            $target = $link === self::URL_CHOICE ? LinkTarget::url($row['url'] ?? '') : LinkResolver::target($link);
            $items[] = new MenuItem($target, $row['label'] ?? null);
        }

        return $items;
    }

    public static function fromModel(Menu $menu, ?string $zoneLabel = null): self
    {
        $items = [];
        foreach ($menu->items as $item) {
            $url = $item->target->isUrl();
            $items[] = [
                'label' => $item->label,
                'link' => $url ? self::URL_CHOICE : $item->target->kind . ':' . $item->target->reference,
                'url' => $url ? $item->target->reference : null,
            ];
        }

        return new self(
            id: $menu->id->value(),
            title: $menu->title,
            zone: $menu->zone->value(),
            position: $menu->position,
            items: $items,
            zoneLabel: $zoneLabel ?? $menu->zone->value(),
            itemCount: count($items),
            updatedAt: $menu->updatedAt,
        );
    }

    public function adminLabel(): ?string
    {
        return $this->title !== null && $this->title !== '' ? $this->title : $this->zoneLabel;
    }

    public function adminStatus(): ?StatusBadge
    {
        return null;
    }

    public function adminPublicUrl(): ?string
    {
        return null;
    }
}
