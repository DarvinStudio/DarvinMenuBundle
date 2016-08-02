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
