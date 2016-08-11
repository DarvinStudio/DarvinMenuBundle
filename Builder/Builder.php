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
use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\MenuBundle\Item\ItemFactoryInterface;
use Darvin\MenuBundle\Repository\Menu\ItemRepository;
use Darvin\Utils\CustomObject\CustomObjectLoaderInterface;
use Doctrine\ORM\QueryBuilder;
use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Builder
 */
class Builder
{
    const ADD_ITEM_FACTORY_METHOD = 'addItemFactory';

    const BUILD_METHOD = 'buildMenu';

    /**
     * @var \Darvin\Utils\CustomObject\CustomObjectLoaderInterface
     */
    protected $customObjectLoader;

    /**
     * @var \Knp\Menu\FactoryInterface
     */
    protected $genericItemFactory;

    /**
     * @var \Darvin\MenuBundle\Repository\Menu\ItemRepository
     */
    protected $menuItemRepository;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslationJoinerInterface
     */
    protected $translationJoiner;

    /**
     * @var string
     */
    protected $menuAlias;

    /**
     * @var \Darvin\MenuBundle\Item\ItemFactoryInterface[]
     */
    protected $itemFactories;

    /**
     * @param \Darvin\Utils\CustomObject\CustomObjectLoaderInterface        $customObjectLoader Custom object loader
     * @param \Knp\Menu\FactoryInterface                                    $genericItemFactory Generic item factory
     * @param \Darvin\MenuBundle\Repository\Menu\ItemRepository             $menuItemRepository Menu item entity repository
     * @param \Symfony\Component\HttpFoundation\RequestStack                $requestStack       Request stack
     * @param \Darvin\ContentBundle\Translatable\TranslationJoinerInterface $translationJoiner  Translation joiner
     * @param string                                                        $menuAlias          Menu alias
     */
    public function __construct(
        CustomObjectLoaderInterface $customObjectLoader,
        FactoryInterface $genericItemFactory,
        ItemRepository $menuItemRepository,
        RequestStack $requestStack,
        TranslationJoinerInterface $translationJoiner,
        $menuAlias
    ) {
        $this->customObjectLoader = $customObjectLoader;
        $this->genericItemFactory = $genericItemFactory;
        $this->menuItemRepository = $menuItemRepository;
        $this->requestStack = $requestStack;
        $this->translationJoiner = $translationJoiner;
        $this->menuAlias = $menuAlias;

        $this->itemFactories = [];
    }

    /**
     * @param string                                       $associationClass Association class
     * @param \Darvin\MenuBundle\Item\ItemFactoryInterface $itemFactory      Item factory
     *
     * @throws \Darvin\MenuBundle\Builder\BuilderException
     */
    public function addItemFactory($associationClass, ItemFactoryInterface $itemFactory)
    {
        if (isset($this->itemFactories[$associationClass])) {
            throw new BuilderException(
                sprintf('Item factory for association class "%s" already added to builder.', $associationClass)
            );
        }

        $this->itemFactories[$associationClass] = $itemFactory;
    }

    /**
     * @param array $options Options
     *
     * @return \Knp\Menu\ItemInterface
     * @throws \Darvin\MenuBundle\Builder\BuilderException
     */
    public function buildMenu(array $options = [])
    {
        $root = $this->genericItemFactory->createItem($this->menuAlias);

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        try {
            $options = $resolver->resolve($options);
        } catch (InvalidArgumentException $ex) {
            throw new BuilderException(sprintf('Menu "%s" builder options are invalid: "%s".', $this->menuAlias, $ex->getMessage()));
        }
        if (null !== $options['depth'] && $options['depth'] <= 0) {
            return $root;
        }

        $locale = $this->getLocale();

        foreach ($this->getMenuItems($locale) as $menuItem) {
            $item = $this->createItem($menuItem, $locale, $options);

            if (!empty($item)) {
                $root->addChild($item);
            }
        }

        return $root;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $menuItem Menu item
     * @param string                              $locale   Locale
     * @param array                               $options  Options
     *
     * @return \Knp\Menu\ItemInterface
     */
    protected function createItem(Item $menuItem, $locale, array $options)
    {
        $title = $menuItem->getTitle();
        $url = $menuItem->getUrl();

        if (!empty($url)) {
            return $this->genericItemFactory->createItem(!empty($title) ? $title : $url, [
                'uri' => $url,
            ]);
        }

        $associated = $menuItem->getAssociatedInstance();

        if (empty($associated) || !isset($this->itemFactories[$menuItem->getAssociatedClass()])) {
            return null;
        }

        $itemFactory = $this->itemFactories[$menuItem->getAssociatedClass()];

        if (!$itemFactory->canCreateItem($associated, $options)) {
            return null;
        }

        $item = $itemFactory->createItem(
            $associated,
            $locale,
            $menuItem->isShowChildren() && (null === $options['depth'] || $options['depth'] > 1),
            $options
        );

        if (!empty($title)) {
            $item->setName($title);
        }

        return $item;
    }

    /**
     * @param string $locale Locale
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Item[]
     */
    protected function getMenuItems($locale)
    {
        $menuItems = $this->menuItemRepository->getByMenuEnabledBuilder($this->menuAlias, $locale)->getQuery()->getResult();

        $translationJoiner = $this->translationJoiner;

        $this->customObjectLoader->loadCustomObjects($menuItems, function (QueryBuilder $qb) use ($locale, $translationJoiner) {
            if ($translationJoiner->isTranslatable($qb->getRootEntities()[0])) {
                $translationJoiner->joinTranslation($qb, true, $locale, null, true);
            }
        });

        return $menuItems;
    }

    /**
     * @return string
     * @throws \Darvin\MenuBundle\Builder\BuilderException
     */
    protected function getLocale()
    {
        $request = $this->requestStack->getCurrentRequest();

        if (empty($request)) {
            throw new BuilderException('Unable to build menu: current request is empty.');
        }

        return $request->getLocale();
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver Options resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'depth'        => null,
                'hidden_items' => false,
            ])
            ->setAllowedTypes('depth', [
                'integer',
                'null',
            ])
            ->setAllowedTypes('hidden_items', 'boolean');
    }
}
