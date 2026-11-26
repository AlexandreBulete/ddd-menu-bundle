<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddFoundation\Application\Query\QueryBusInterface;
use AlexandreBulete\DddMenuBundle\Application\Query\FindMenus\FindMenusQuery;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\Repository\MenuRepositoryInterface;
use AlexandreBulete\DddMenuBundle\Infrastructure\Config\ConfiguredZoneCatalog;
use AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\Resource\MenuResource;
use AlexandreBulete\DddSyliusBundle\Grid\GridPageResolver;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Pagerfanta\PagerfantaInterface;
use Sylius\Component\Grid\Data\DataProviderInterface;
use Sylius\Component\Grid\Definition\Grid;
use Sylius\Component\Grid\Parameters;

final readonly class MenuGridProvider implements DataProviderInterface
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private ConfiguredZoneCatalog $zones,
    ) {}

    /**
     * @return PagerfantaInterface<MenuResource>
     */
    public function getData(Grid $grid, Parameters $parameters): PagerfantaInterface
    {
        /** @var array<string, string> $sorting */
        $sorting = $parameters->get('sorting', $grid->getSorting());

        /** @var MenuRepositoryInterface $models */
        $models = $this->queryBus->ask(new FindMenusQuery(
            page: GridPageResolver::getCurrentPage($grid, $parameters),
            itemsPerPage: GridPageResolver::getItemsPerPage($grid, $parameters),
            withSorting: $sorting,
        ));

        $data = [];
        foreach ($models as $model) {
            /** @var Menu $model */
            $data[] = MenuResource::fromModel($model, $this->zones->label($model->zone->value()));
        }

        return new Pagerfanta(new FixedAdapter($models->count(), $data));
    }
}
