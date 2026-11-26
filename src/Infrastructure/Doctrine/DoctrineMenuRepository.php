<?php

declare(strict_types=1);

namespace AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine;

use AlexandreBulete\DddDoctrineBridge\DoctrineRepository;
use AlexandreBulete\DddFoundation\Domain\ValueObject\IdentifierVO;
use AlexandreBulete\DddMenuBundle\Domain\Model\Menu;
use AlexandreBulete\DddMenuBundle\Domain\Repository\MenuRepositoryInterface;
use AlexandreBulete\DddMenuBundle\Domain\Service\DomainEventPublisherInterface;
use AlexandreBulete\DddMenuBundle\Domain\ValueObject\Zone;
use AlexandreBulete\DddMenuBundle\Infrastructure\Doctrine\Type\ZoneType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends DoctrineRepository<Menu>
 */
final class DoctrineMenuRepository extends DoctrineRepository implements MenuRepositoryInterface
{
    private const string ALIAS = 'menu';

    public function __construct(
        EntityManagerInterface $em,
        private readonly DomainEventPublisherInterface $eventPublisher,
    ) {
        parent::__construct($em, Menu::class, self::ALIAS);
    }

    public function save(Menu $menu): void
    {
        $this->em->wrapInTransaction(function () use ($menu): void {
            $this->em->persist($menu);
            $this->em->flush();
            $this->eventPublisher->publishAll($menu->releaseEvents());
        });
    }

    public function remove(Menu $menu): void
    {
        $this->em->wrapInTransaction(function () use ($menu): void {
            $this->em->remove($menu);
            $this->em->flush();
            $this->eventPublisher->publishAll($menu->releaseEvents());
        });
    }

    public function findById(IdentifierVO $id): ?Menu
    {
        return $this->em->find(Menu::class, $id->value());
    }

    public function inZone(Zone $zone): array
    {
        /** @var list<Menu> $menus */
        $menus = $this->em->createQueryBuilder()
            ->select('m')
            ->from(Menu::class, 'm')
            ->where('m.zone = :zone')
            ->setParameter('zone', $zone, ZoneType::NAME)
            ->orderBy('m.position', 'ASC')
            ->addOrderBy('m.title', 'ASC')
            ->getQuery()
            ->getResult();

        return $menus;
    }
}
