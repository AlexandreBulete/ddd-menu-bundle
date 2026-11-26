<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Query\FindMenus;

use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\Repository\MenuRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Handler\QueryCollectionHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;
use AlexandreBulete\DddFoundation\Domain\Repository\RepositoryInterface;

/**
 * @extends QueryCollectionHandler<Menu>
 */
#[AsQueryHandler]
final readonly class FindMenusHandler extends QueryCollectionHandler
{
    public function __construct(MenuRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    /**
     * @return RepositoryInterface<Menu>
     */
    public function __invoke(FindMenusQuery $query): RepositoryInterface
    {
        return $this->build($query);
    }
}
