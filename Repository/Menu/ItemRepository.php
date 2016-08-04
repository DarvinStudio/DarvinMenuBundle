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

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Menu item entity repository
 */
class ItemRepository extends EntityRepository
{
    /**
     * @param string $menu   Menu alias
     * @param string $locale Locale
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getByMenuEnabledBuilder($menu, $locale)
    {
        $qb = $this->createDefaultQueryBuilder()
            ->addSelect('translation')
            ->innerJoin('o.translations', 'translation');
        $this
            ->addMenuFilter($qb, $menu)
            ->addTranslationEnabledFilter($qb)
            ->addTranslationLocaleFilter($qb, $locale);

        return $qb;
    }

    /**
     * @param string $associatedClass   Associated class
     * @param string $associatedId      Associated ID
     * @param string $currentMenu       Current menu alias
     * @param int    $currentMenuItemId Current menu item ID
     *
     * @return bool
     */
    public function isAssociatedInOtherMenus($associatedClass, $associatedId, $currentMenu, $currentMenuItemId)
    {
        $qb = $this->getByAssociatedBuilder($associatedClass, $associatedId)
            ->select('COUNT(o)');
        $this
            ->addNotMenuFilter($qb, $currentMenu)
            ->addNotIdFilter($qb, $currentMenuItemId);

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @param string $associatedClass Associated class
     * @param string $associatedId    Associated ID
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getByAssociatedBuilder($associatedClass, $associatedId)
    {
        $qb = $this->createDefaultQueryBuilder();
        $this
            ->addAssociatedClassFilter($qb, $associatedClass)
            ->addAssociatedIdFilter($qb, $associatedId);

        return $qb;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb              Query builder
     * @param string                     $associatedClass Associated class
     *
     * @return ItemRepository
     */
    private function addAssociatedClassFilter(QueryBuilder $qb, $associatedClass)
    {
        $qb->andWhere('o.associatedClass = :associated_class')->setParameter('associated_class', $associatedClass);

        return $this;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb           Query builder
     * @param string                     $associatedId Associated ID
     *
     * @return ItemRepository
     */
    private function addAssociatedIdFilter(QueryBuilder $qb, $associatedId)
    {
        $qb->andWhere('o.associatedId = :associated_id')->setParameter('associated_id', $associatedId);

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
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder
     * @param int                        $id Menu item ID
     *
     * @return ItemRepository
     */
    private function addNotIdFilter(QueryBuilder $qb, $id)
    {
        $qb->andWhere('o.id != :id')->setParameter('id', $id);

        return $this;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb   Query builder
     * @param string                     $menu Menu alias
     *
     * @return ItemRepository
     */
    private function addNotMenuFilter(QueryBuilder $qb, $menu)
    {
        $qb->andWhere('o.menu != :menu')->setParameter('menu', $menu);

        return $this;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder
     *
     * @return ItemRepository
     */
    private function addTranslationEnabledFilter(QueryBuilder $qb)
    {
        $qb->andWhere('translation.enabled = :translation_enabled')->setParameter('translation_enabled', true);

        return $this;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb     Query builder
     * @param string                     $locale Locale
     *
     * @return ItemRepository
     */
    private function addTranslationLocaleFilter(QueryBuilder $qb, $locale)
    {
        $qb->andWhere('translation.locale = :translation_locale')->setParameter('translation_locale', $locale);

        return $this;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function createDefaultQueryBuilder()
    {
        return $this->createQueryBuilder('o')->addOrderBy('o.position');
    }
}
