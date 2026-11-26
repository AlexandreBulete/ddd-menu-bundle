<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Twig;

use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddMenuBundle\Application\Query\FindZoneMenus\FindZoneMenusQuery;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;
use AlexandreBulete\DddMenuBundle\Infrastructure\Link\LinkResolver;
use AlexandreBulete\DddMenuBundle\Infrastructure\Link\ResolvedLink;
use Symfony\Contracts\Service\ResetInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * `menus('footer')` lists a zone's menus ({title, links}), for columns;
 * `menu_links('main')` gives the links of a zone's menus in one list, for a
 * bar. A link whose destination is gone or not public is left out. Each zone
 * is asked once per request.
 */
final class MenuExtension extends AbstractExtension implements ResetInterface
{
    /** @var array<string, list<array{title: string, links: list<ResolvedLink>}>> */
    private array $zones = [];

    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly LinkResolver $links,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('menus', $this->menus(...)),
            new TwigFunction('menu_links', $this->menuLinks(...)),
        ];
    }

    /**
     * @return list<array{title: string, links: list<ResolvedLink>}>
     */
    public function menus(string $zone): array
    {
        if (!isset($this->zones[$zone])) {
            /** @var list<Menu> $menus */
            $menus = $this->queryBus->ask(new FindZoneMenusQuery(Zone::fromString($zone)));
            $this->zones[$zone] = [];
            foreach ($menus as $menu) {
                $links = [];
                foreach ($menu->items as $item) {
                    $link = $this->links->resolve($item);
                    if ($link !== null) {
                        $links[] = $link;
                    }
                }
                $this->zones[$zone][] = ['title' => $menu->title, 'links' => $links];
            }
        }

        return $this->zones[$zone];
    }

    /**
     * @return list<ResolvedLink>
     */
    public function menuLinks(string $zone): array
    {
        return array_merge([], ...array_column($this->menus($zone), 'links'));
    }

    public function reset(): void
    {
        $this->zones = [];
    }
}
