<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Application\Query\FindMenu;

use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\Repository\MenuRepositoryInterface;
use AlexandreBulete\DddFoundation\Application\Handler\QuerySingleHandler;
use AlexandreBulete\DddFoundation\Application\Query\AsQueryHandler;

/**
 * @extends QuerySingleHandler<Menu>
 */
#[AsQueryHandler]
final readonly class FindMenuHandler extends QuerySingleHandler
{
    public function __construct(MenuRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function __invoke(FindMenuQuery $query): Menu
    {
        return $this->build($query);
    }
}
