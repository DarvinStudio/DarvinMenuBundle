<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Builder;

use Darvin\ContentBundle\Translatable\TranslationJoinerInterface;
use Darvin\MenuBundle\Item\ItemFactoryInterface;
use Darvin\MenuBundle\Repository\Menu\ItemRepository;
use Darvin\Utils\CustomObject\CustomObjectLoaderInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Menu\FactoryInterface;

/**
 * Builder
 */
class Builder
{
    const BUILD_METHOD = 'buildMenu';

    /**
     * @var \Darvin\Utils\CustomObject\CustomObjectLoaderInterface
     */
    private $customObjectLoader;

    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $genericItemFactory;

    /**
     * @var \Darvin\MenuBundle\Repository\Menu\ItemRepository
     */
    private $menuItemRepository;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslationJoinerInterface
     */
    private $translationJoiner;

    /**
     * @var string
     */
    private $menuAlias;

    /**
     * @var \Darvin\MenuBundle\Item\ItemFactoryInterface[]
     */
    private $itemFactories;

    /**
     * @param \Darvin\Utils\CustomObject\CustomObjectLoaderInterface        $customObjectLoader Custom object loader
     * @param \Knp\Menu\FactoryInterface                                    $genericItemFactory Generic item factory
     * @param \Darvin\MenuBundle\Repository\Menu\ItemRepository             $menuItemRepository Menu item entity repository
     * @param \Darvin\ContentBundle\Translatable\TranslationJoinerInterface $translationJoiner  Translation joiner
     * @param string                                                        $menuAlias          Menu alias
     */
    public function __construct(
        CustomObjectLoaderInterface $customObjectLoader,
        FactoryInterface $genericItemFactory,
        ItemRepository $menuItemRepository,
        TranslationJoinerInterface $translationJoiner,
        $menuAlias
    ) {
        $this->customObjectLoader = $customObjectLoader;
        $this->genericItemFactory = $genericItemFactory;
        $this->menuItemRepository = $menuItemRepository;
        $this->translationJoiner = $translationJoiner;
        $this->menuAlias = $menuAlias;

        $this->itemFactories = [];
    }

    /**
     * @param string                                       $associationClass Association class
     * @param \Darvin\MenuBundle\Item\ItemFactoryInterface $itemFactory      Item factory
     */
    public function addItemFactory($associationClass, ItemFactoryInterface $itemFactory)
    {
        $this->itemFactories[$associationClass] = $itemFactory;
    }

    /**
     * @return \Knp\Menu\ItemInterface
     */
    public function buildMenu()
    {
        $root = $this->genericItemFactory->createItem($this->menuAlias);

        foreach ($this->getMenuItems() as $menuItem) {
            $associated = $menuItem->getAssociatedInstance();

            if (!empty($associated)
                && isset($this->itemFactories[$menuItem->getAssociatedClass()])
                && null !== $item = $this->itemFactories[$menuItem->getAssociatedClass()]->createItem($associated)
            ) {
                $title = $menuItem->getTitle();

                if (!empty($title)) {
                    $item->setName($title);
                }

                $root->addChild($item);
            }
        }

        return $root;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]
     */
    private function getMenuItems()
    {
        $menuItems = $this->menuItemRepository->getByMenuEnabledBuilder($this->menuAlias)->getQuery()->getResult();

        $translationJoiner = $this->translationJoiner;

        $this->customObjectLoader->loadCustomObjects($menuItems, function (QueryBuilder $qb) use ($translationJoiner) {
            if ($translationJoiner->isTranslatable($qb->getRootEntities()[0])) {
                $translationJoiner->joinTranslation($qb, true, null, null, true);
            }
        });

        return $menuItems;
    }
}
