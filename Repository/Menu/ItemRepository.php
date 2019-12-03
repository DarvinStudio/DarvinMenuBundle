<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Repository\Menu;

use Darvin\ContentBundle\Traits\TranslatableRepositoryTrait;
use Darvin\ImageBundle\Traits\ImageableRepositoryTrait;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Menu item entity repository
 */
class ItemRepository extends EntityRepository
{
    use ImageableRepositoryTrait;
    use TranslatableRepositoryTrait;

    /**
     * @param string|null $menu   Menu alias
     * @param string|null $locale Locale
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAdminBuilder(?string $menu = null, ?string $locale = null): QueryBuilder
    {
        $qb = $this->createDefaultBuilder()
            ->orderBy('o.menu')
            ->addOrderBy('o.level')
            ->addOrderBy('o.position');
        $this->joinTranslations($qb, $locale);
        $this->joinSlugMapItem($qb);

        if (null !== $menu) {
            $this->addMenuFilter($qb, $menu);
        }

        return $qb;
    }

    /**
     * @param string[]    $classes Object classes
     * @param mixed       $id      Object ID
     * @param string|null $menu    Menu alias
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]
     */
    public function getByObject(array $classes, $id, ?string $menu = null): array
    {
        if (empty($classes)) {
            throw new \InvalidArgumentException('Array of object classes is empty.');
        }

        $classes = array_values(array_unique($classes));

        $qb = $this->createDefaultBuilder()
            ->andWhere('slug_map_item.objectId = :object_id')
            ->setParameter('object_id', $id);
        $this->joinSlugMapItem($qb);

        if (null !== $menu) {
            $this->addMenuFilter($qb, $menu);
        }

        $orX = $qb->expr()->orX();

        foreach ($classes as $i => $class) {
            $param = sprintf('object_class_%d', $i);

            $orX->add(sprintf('slug_map_item.objectClass = :%s', $param));

            $qb->setParameter($param, $class);
        }

        $qb->andWhere($orX);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string      $menu   Menu alias
     * @param int|null    $depth  Depth
     * @param string|null $locale Locale
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]
     */
    public function getForMenuBuilder(string $menu, ?int $depth = null, ?string $locale = null): array
    {
        $qb = $this->createDefaultBuilder()
            ->andWhere('o.slugMapItem IS NOT NULL OR translations.title IS NOT NULL OR translations.url IS NOT NULL')
            ->orderBy('o.level')
            ->addOrderBy('o.position');
        $this
            ->joinImage($qb, $locale)
            ->joinImage($qb, $locale, true, 'o.hoverImage', 'hover_image')
            ->joinSlugMapItem($qb)
            ->joinTranslations($qb, $locale)
            ->addEnabledFilter($qb)
            ->addMenuFilter($qb, $menu);

        if (null !== $depth) {
            $qb
                ->andWhere('o.level <= :depth')
                ->setParameter('depth', $depth);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getForMenuSwitcher(): array
    {
        $qb = $this->createDefaultBuilder()
            ->select('o.menu')
            ->addSelect('slug_map_item.objectClass class')
            ->addSelect('slug_map_item.objectId id');
        $this->joinSlugMapItem($qb, false, true);

        $items = [];

        foreach ($qb->getQuery()->getScalarResult() as $row) {
            $menu  = $row['menu'];
            $class = $row['class'];
            $id    = $row['id'];

            if (!isset($items[$menu])) {
                $items[$menu] = [];
            }
            if (!isset($items[$menu][$class])) {
                $items[$menu][$class] = [];
            }

            $items[$menu][$class][$id] = $id;
        }

        return $items;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb        Query builder
     * @param bool                       $addSelect Whether to add select
     * @param bool                       $inner     Whether to use inner join
     *
     * @return ItemRepository
     */
    private function joinSlugMapItem(QueryBuilder $qb, bool $addSelect = true, bool $inner = false): ItemRepository
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
     * @return ItemRepository
     */
    private function addEnabledFilter(QueryBuilder $qb)
    {
        $qb->andWhere('translations.enabled = :enabled')->setParameter('enabled', true);

        return $this;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb   Query builder
     * @param string                     $menu Menu alias
     *
     * @return ItemRepository
     */
    private function addMenuFilter(QueryBuilder $qb, string $menu): ItemRepository
    {
        $qb->andWhere('o.menu = :menu')->setParameter('menu', $menu);

        return $this;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createDefaultBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('o');
    }
}
