<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\DataFixtures\ORM\Menu;

use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\MenuBundle\Configuration\MenuConfigurationInterface;
use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\MenuBundle\Entity\Menu\ItemTranslation;
use Darvin\MenuBundle\Entity\Menu\MenuItemImage;
use Darvin\Utils\DataFixtures\ORM\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Menu item data fixture
 */
class LoadItemData extends AbstractFixture
{
    /**
     * @var int
     */
    private $minCount;

    /**
     * @var int
     */
    private $maxCount;

    /**
     * @var int
     */
    private $maxLevel;

    /**
     * @var array
     */
    private $items;

    /**
     * @param int $minCount Minimum count
     * @param int $maxCount Maximum count
     * @param int $maxLevel Maximum level
     */
    public function __construct(int $minCount = 10, int $maxCount = 20, int $maxLevel = 3)
    {
        $this->minCount = $minCount;
        $this->maxCount = $maxCount;
        $this->maxLevel = $maxLevel;

        $this->items = [];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getMenuConfig()->getMenus() as $menu) {
            $this->items[$menu->getAlias()] = [];

            $count = $this->getFaker()->numberBetween($this->minCount, $this->maxCount);

            for ($i = 0; $i < $count; $i++) {
                $item = $this->createItem($menu->getAlias());

                $manager->persist($item);

                $this->items[$menu->getAlias()][] = $item;
            }
        }

        $manager->flush();
    }

    /**
     * @param string $menu Menu alias
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Item
     */
    private function createItem(string $menu): Item
    {
        /** @var \Darvin\MenuBundle\Entity\Menu\Item $item */
        $item  = $this->instantiateEntity(Item::class);
        $item->setLevel(1);
        $item->setMenu($menu);

        if ($this->getFaker()->boolean(80)) {
            $item->setImage($this->createImage(true));

            if ($this->getFaker()->boolean(80)) {
                $item->setHoverImage($this->createImage());
            }
        }
        if ($this->getFaker()->boolean(90)) {
            $item->setParent($this->getParentItem($menu));

            if (null !== $item->getParent()) {
                $item->setLevel($item->getLevel() + $item->getParent()->getLevel());
            }
        }
        if ($this->getFaker()->boolean(75)) {
            $item->setSlugMapItem($this->getRandomEntity(SlugMapItem::class));
        }
        foreach ($this->getFakerLocales() as $locale => $fakerLocale) {
            $item->addTranslation($this->createTranslation($item, $locale, $fakerLocale));
        }

        return $item;
    }

    /**
     * @param bool $grayscale Whether to generate grayscale image
     *
     * @return \Darvin\MenuBundle\Entity\Menu\MenuItemImage
     */
    private function createImage(bool $grayscale = false): MenuItemImage
    {
        /** @var \Darvin\MenuBundle\Entity\Menu\MenuItemImage $image */
        $image = $this->instantiateEntity(MenuItemImage::class);
        $image->setFile($this->generateImageFile(320, 240, null, $grayscale ? 'fff' : null, $grayscale ? 'ccc' : null));

        return $image;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\Menu\Item $item        Menu item
     * @param string                              $locale      Locale
     * @param string                              $fakerLocale Faker locale
     *
     * @return \Darvin\MenuBundle\Entity\Menu\ItemTranslation|\Knp\DoctrineBehaviors\Model\Translatable\Translation
     */
    private function createTranslation(Item $item, string $locale, string $fakerLocale): ItemTranslation
    {
        /** @var \Darvin\MenuBundle\Entity\Menu\ItemTranslation $translation */
        $translation = $this->instantiateTranslation(Item::class);
        $faker       = $this->getFaker($fakerLocale);

        $translation->setLocale($locale);

        if (null === $item->getSlugMapItem()) {
            $translation->setTitle($faker->sentence(3));

            if ($faker->boolean(90)) {
                $translation->setUrl($faker->url);
            }
        }

        return $translation;
    }

    /**
     * @param string $menu Menu alias
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Item|null
     */
    private function getParentItem(string $menu): ?Item
    {
        /** @var \Darvin\MenuBundle\Entity\Menu\Item[] $items */
        $items = $this->items[$menu];

        shuffle($items);

        foreach ($items as $item) {
            if ($item->getLevel() < $this->maxLevel) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @return \Darvin\MenuBundle\Configuration\MenuConfigurationInterface
     */
    private function getMenuConfig(): MenuConfigurationInterface
    {
        return $this->container->get('darvin_menu.configuration.menu');
    }
}
