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
            ->addSelect('image_sizes')
            ->leftJoin('o.image', 'image')
            ->leftJoin('image.sizes', 'image_sizes');

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
            ->addSelect('hover_image_sizes')
            ->leftJoin('o.hoverImage', 'hover_image')
            ->leftJoin('hover_image.sizes', 'hover_image_sizes');

        return $this;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb Query builder
     *
     * @return ItemRepository
     */
    private function joinSlugMapItem(QueryBuilder $qb)
    {
        $qb
            ->addSelect('slug_map_item')
            ->leftJoin('o.slugMapItem', 'slug_map_item');

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
