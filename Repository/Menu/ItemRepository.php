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
    public function getForMenuBuilder($menu, $locale)
    {
        $qb = $this->createDefaultQueryBuilder();
        $this
            ->joinTranslations($qb, $locale, false)
            ->addEnabledFilter($qb)
            ->addMenuFilter($qb, $menu);

        return $qb
            ->andWhere('o.slugMapItem IS NOT NULL OR (translations.url IS NOT NULL AND translations.title IS NOT NULL)')
            ->orderBy('o.level');
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
     * @param \Doctrine\ORM\QueryBuilder $qb        Query builder
     * @param string                     $locale    Locale
     * @param bool                       $addSelect Whether to add select
     *
     * @return ItemRepository
     */
    private function joinTranslations(QueryBuilder $qb, $locale, $addSelect = true)
    {
        $qb
            ->innerJoin('o.translations', 'translations')
            ->andWhere('translations.locale = :locale')
            ->setParameter('locale', $locale);

        if ($addSelect) {
            $qb->addSelect('translations');
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
