<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Repository;

use Darvin\ContentBundle\Traits\TranslatableRepositoryTrait;
use Darvin\ImageBundle\Traits\ImageableRepositoryTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Menu entry entity repository
 */
class MenuEntryRepository extends EntityRepository
{
    use ImageableRepositoryTrait;
    use TranslatableRepositoryTrait;

    /**
     * @param string|null $menu   Menu name
     * @param string|null $locale Locale
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createBuilderForAdminForm(?string $menu = null, ?string $locale = null): QueryBuilder
    {
        $qb = $this->createDefaultBuilder();
        $qb
            ->orderBy('o.menu')
            ->addOrderBy('o.level')
            ->addOrderBy('o.position');
        $this
            ->joinSlugMapItem($qb)
            ->joinTranslations($qb, $locale);

        if (null !== $menu) {
            $this->addMenuFilter($qb, $menu);
        }

        return $qb;
    }

    /**
     * @param string      $menu   Menu name
     * @param int|null    $depth  Depth
     * @param string|null $locale Locale
     *
     * @return \Darvin\MenuBundle\Entity\MenuEntry[]
     */
    public function getForMenuBuilder(string $menu, ?int $depth = null, ?string $locale = null): array
    {
        $qb = $this->createDefaultBuilder();
        $qb
            ->andWhere('o.slugMapItem IS NOT NULL OR translations.title IS NOT NULL OR translations.url IS NOT NULL')
            ->orderBy('o.level')
            ->addOrderBy('o.position');
        $this
            ->joinImage($qb, $locale)
            ->joinSlugMapItem($qb)
            ->joinTranslations($qb, $locale);
        $this
            ->addEnabledFilter($qb)
            ->addMenuFilter($qb, $menu);

        if (null !== $depth) {
            $qb
                ->andWhere('o.level <= :depth')
                ->setParameter('depth', $depth);
        }

        return $qb->getQuery()->enableResultCache()->getResult();
    }

    /**
     * @return array
     */
    public function getForMenuSwitcher(): array
    {
        $qb = $this->createDefaultBuilder();
        $qb
            ->select('o.menu')
            ->addSelect('slug_map_item.objectClass class')
            ->addSelect('slug_map_item.objectId id');
        $this->joinSlugMapItem($qb, false, true);

        $entries = [];

        foreach ($qb->getQuery()->getScalarResult() as $row) {
            $menu  = $row['menu'];
            $class = $row['class'];
            $id    = $row['id'];

            if (!isset($entries[$menu])) {
                $entries[$menu] = [];
            }
            if (!isset($entries[$menu][$class])) {
                $entries[$menu][$class] = [];
            }

            $entries[$menu][$class][$id] = $id;
        }

        return $entries;
    }

    /**
     * @param string[]    $classes Object classes
     * @param mixed       $id      Object ID
     * @param string|null $menu    Menu name
     *
     * @return \Darvin\MenuBundle\Entity\MenuEntry[]
     */
    public function getForSwitchMenuSubscriber(array $classes, $id, ?string $menu = null): array
    {
        if (empty($classes)) {
            throw new \InvalidArgumentException('Array of object classes is empty.');
        }

        $qb = $this->createDefaultBuilder();
        $this->joinSlugMapItem($qb);

        if (null !== $menu) {
            $this->addMenuFilter($qb, $menu);
        }

        $qb
            ->andWhere('slug_map_item.objectId = :object_id')
            ->setParameter('object_id', $id);

        $orX = $qb->expr()->orX();

        foreach (array_values(array_unique($classes)) as $i => $class) {
            $param = sprintf('object_class_%d', $i);

            $orX->add(sprintf('slug_map_item.objectClass = :%s', $param));

            $qb->setParameter($param, $class);
        }

        $qb->andWhere($orX);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb        Query builder
     * @param bool                       $addSelect Whether to add select
     * @param bool                       $inner     Whether to use inner join
     *
     * @return MenuEntryRepository
     */
    protected function joinSlugMapItem(QueryBuilder $qb, bool $addSelect = true, bool $inner = false): MenuEntryRepository
    {
        $inner
            ? $qb->innerJoin('o.slugMapItem', 'slug_map_item')
            : $qb->leftJoin('o.slugMapItem', 'slug_map_item');

        if ($addSelect) {
            $qb->addSelect('slug_map_item');
        }

        return $this;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder
     *
     * @return MenuEntryRepository
     */
    protected function addEnabledFilter(QueryBuilder $qb): MenuEntryRepository
    {
        $qb->andWhere('translations.enabled = :enabled')->setParameter('enabled', true);

        return $this;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb   Query builder
     * @param string                     $menu Menu name
     *
     * @return MenuEntryRepository
     */
    protected function addMenuFilter(QueryBuilder $qb, string $menu): MenuEntryRepository
    {
        $qb->andWhere('o.menu = :menu')->setParameter('menu', $menu);

        return $this;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createDefaultBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('o');
    }
}
