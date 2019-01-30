<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Repository\Menu;

use Darvin\ImageBundle\Entity\Image\AbstractImage;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Menu item entity repository
 */
class ItemRepository extends EntityRepository
{
    /**
     * @param string $locale Locale
     * @param string $menu   Menu alias
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAdminBuilder($locale, $menu = null)
    {
        $qb = $this->createDefaultQueryBuilder()
            ->orderBy('o.menu')
            ->addOrderBy('o.level')
            ->addOrderBy('o.position');
        $this
            ->joinSlugMapItem($qb)
            ->joinTranslations($qb, $locale);

        if (!empty($menu)) {
            $this->addMenuFilter($qb, $menu);
        }

        return $qb;
    }

    /**
     * @param string[]    $entityClasses Entity classes
     * @param mixed       $entityId      Entity ID
     * @param string|null $menu          Menu alias
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]
     */
    public function getByEntity(array $entityClasses, $entityId, $menu = null)
    {
        if (empty($entityClasses)) {
            throw new \InvalidArgumentException('Array of entity classes is empty.');
        }

        $entityClasses = array_values(array_unique($entityClasses));

        $qb = $this->createDefaultQueryBuilder()
            ->andWhere('slug_map_item.objectId = :entity_id')
            ->setParameter('entity_id', $entityId);
        $this->joinSlugMapItem($qb);

        if (!empty($menu)) {
            $this->addMenuFilter($qb, $menu);
        }

        $orX = $qb->expr()->orX();

        foreach ($entityClasses as $i => $entityClass) {
            $param = sprintf('object_class_%d', $i);

            $orX->add(sprintf('slug_map_item.objectClass = :%s', $param));

            $qb->andWhere($param, $entityClass);
        }

        $qb->andWhere($orX);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getForMenuSwitcher()
    {
        $qb = $this->createDefaultQueryBuilder()
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
     * @param string $menu   Menu alias
     * @param string $locale Locale
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getForMenuBuilder($menu, $locale)
    {
        $qb = $this->createDefaultQueryBuilder();
        $this
            ->joinHoverImage($qb)
            ->joinImage($qb)
            ->joinSlugMapItem($qb)
            ->joinTranslations($qb, $locale)
            ->addEnabledFilter($qb)
            ->addMenuFilter($qb, $menu);

        return $qb
            ->andWhere('o.slugMapItem IS NOT NULL OR translations.title IS NOT NULL')
            ->orderBy('o.level')
            ->addOrderBy('o.position');
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder
     *
     * @return ItemRepository
     */
    private function joinImage(QueryBuilder $qb)
    {
        $qb
            ->addSelect('image')
            ->leftJoin('o.image', 'image');

        if ($this->_em->getClassMetadata(AbstractImage::class)->hasAssociation('sizes')) {
            $qb
                ->addSelect('image_sizes')
                ->leftJoin('image.sizes', 'image_sizes');
        }

        return $this;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder
     *
     * @return ItemRepository
     */
    private function joinHoverImage(QueryBuilder $qb)
    {
        $qb
            ->addSelect('hover_image')
            ->leftJoin('o.hoverImage', 'hover_image');

        if ($this->_em->getClassMetadata(AbstractImage::class)->hasAssociation('sizes')) {
            $qb
                ->addSelect('hover_image_sizes')
                ->leftJoin('hover_image.sizes', 'hover_image_sizes');
        }

        return $this;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb        Query builder
     * @param bool                       $addSelect Whether to add select
     * @param bool                       $inner     Whether to use inner join
     *
     * @return ItemRepository
     */
    private function joinSlugMapItem(QueryBuilder $qb, $addSelect = true, $inner = false)
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
     * @param \Doctrine\ORM\QueryBuilder $qb     Query builder
     * @param string                     $locale Locale
     *
     * @return ItemRepository
     */
    private function joinTranslations(QueryBuilder $qb, $locale)
    {
        $qb
            ->addSelect('translations')
            ->innerJoin('o.translations', 'translations')
            ->andWhere('translations.locale = :locale')
            ->setParameter('locale', $locale);

        return $this;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder
     *
     * @return ItemRepository
     */
    private function addEnabledFilter(QueryBuilder $qb)
    {
        $qb->andWhere('translations.enabled = :translations_enabled')->setParameter('translations_enabled', true);

        return $this;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb   Query builder
     * @param string                     $menu Menu alias
     *
     * @return ItemRepository
     */
    private function addMenuFilter(QueryBuilder $qb, $menu)
    {
        $qb->andWhere('o.menu = :menu')->setParameter('menu', $menu);

        return $this;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createDefaultQueryBuilder()
    {
        return $this->createQueryBuilder('o');
    }
}
