<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\Grid;

use AlexandreBulete\DddMenuBundle\Infrastructure\Sylius\Resource\MenuResource;
use Sylius\Bundle\GridBundle\Builder\Action\CreateAction;
use Sylius\Bundle\GridBundle\Builder\Action\DeleteAction;
use Sylius\Bundle\GridBundle\Builder\Action\UpdateAction;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\ItemActionGroup;
use Sylius\Bundle\GridBundle\Builder\ActionGroup\MainActionGroup;
use Sylius\Bundle\GridBundle\Builder\Field\DateTimeField;
use Sylius\Bundle\GridBundle\Builder\Field\StringField;
use Sylius\Component\Grid\Attribute\AsGrid;
use Sylius\Component\Grid\Builder\GridBuilderInterface;

/**
 * Every menu, zone by zone, in the order a template prints them.
 */
#[AsGrid(resourceClass: MenuResource::class, name: self::NAME)]
final class MenuGrid
{
    public const string NAME = 'menu_menu';

    public function __invoke(GridBuilderInterface $gridBuilder): void
    {
        $gridBuilder
            ->setProvider(MenuGridProvider::class)
            ->orderBy('zone', 'asc')
            ->addOrderBy('position', 'asc')
            ->addField(StringField::create('zone')->setPath('zoneLabel')->setLabel('menu.menu.zone')->setSortable(true))
            ->addField(StringField::create('position')->setLabel('menu.menu.position')->setSortable(true))
            ->addField(StringField::create('title')->setLabel('menu.menu.title')->setSortable(true))
            ->addField(StringField::create('itemCount')->setLabel('menu.menu.item_count'))
            ->addField(DateTimeField::create('updatedAt')->setLabel('sylius.ui.updated_at'))
            ->addActionGroup(MainActionGroup::create(CreateAction::create()))
            ->addActionGroup(ItemActionGroup::create(UpdateAction::create(), DeleteAction::create()))
        ;
    }
}
