<?php declare(strict_types=1);
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2020, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\DataFixtures\ORM\Menu;

use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\MenuBundle\Configuration\MenuConfigurationInterface;
use Darvin\MenuBundle\Entity\MenuItem;
use Darvin\MenuBundle\Entity\MenuItemTranslation;
use Darvin\MenuBundle\Entity\MenuItemImage;
use Darvin\Utils\DataFixtures\ORM\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

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
    public function __construct(int $minCount, int $maxCount, int $maxLevel)
    {
        $this->minCount = $minCount;
        $this->maxCount = $maxCount;
        $this->maxLevel = $maxLevel;

        $this->items = [];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getMenuConfig()->getMenus() as $menu) {
            $this->items[$menu->getName()] = [];

            $count = $this->getFaker()->numberBetween($this->minCount, $this->maxCount);

            for ($i = 0; $i < $count; $i++) {
                $item = $this->createItem($menu->getName());

                $manager->persist($item);

                $this->items[$menu->getName()][] = $item;
            }
        }

        $manager->flush();
    }

    /**
     * @param string $menu Menu name
     *
     * @return \Darvin\MenuBundle\Entity\MenuItem
     */
    private function createItem(string $menu): MenuItem
    {
        /** @var \Darvin\MenuBundle\Entity\MenuItem $item */
        $item  = $this->instantiateEntity(MenuItem::class);
        $item->setLevel(1);
        $item->setMenu($menu);

        if ($this->getFaker()->boolean(70)) {
            $item->setParent($this->getParentItem($menu));

            if (null !== $item->getParent()) {
                $item->setLevel($item->getLevel() + $item->getParent()->getLevel());
            }
        }
        if (1 === $item->getLevel()) {
            $item->setShowChildren($this->getFaker()->boolean(50));
            $item->setSlugMapItem($this->getRandomEntity(SlugMapItem::class));
        }
        if ($this->getFaker()->boolean(80)) {
            $item->setImage($this->createImage(true));

            if ($this->getFaker()->boolean(90)) {
                $item->setHoverImage($this->createImage());
            }
        }
        foreach ($this->getFakerLocales() as $locale => $fakerLocale) {
            $item->addTranslation($this->createTranslation($item, $locale, $fakerLocale));
        }

        return $item;
    }

    /**
     * @param bool $grayscale Whether to generate grayscale image
     *
     * @return \Darvin\MenuBundle\Entity\MenuItemImage
     */
    private function createImage(bool $grayscale = false): MenuItemImage
    {
        /** @var \Darvin\MenuBundle\Entity\MenuItemImage $image */
        $image = $this->instantiateEntity(MenuItemImage::class);
        $image->setFile($this->generateImageFile(null, null, null, $grayscale ? 'fff' : null, $grayscale ? 'ccc' : null));

        return $image;
    }

    /**
     * @param \Darvin\MenuBundle\Entity\MenuItem $item        Menu item
     * @param string                             $locale      Locale
     * @param string                             $fakerLocale Faker locale
     *
     * @return \Darvin\MenuBundle\Entity\MenuItemTranslation|\Knp\DoctrineBehaviors\Model\Translatable\Translation
     */
    private function createTranslation(MenuItem $item, string $locale, string $fakerLocale): MenuItemTranslation
    {
        /** @var \Darvin\MenuBundle\Entity\MenuItemTranslation $translation */
        $translation = $this->instantiateTranslation(MenuItem::class);
        $faker       = $this->getFaker($fakerLocale);

        $translation->setLocale($locale);

        if (null === $item->getSlugMapItem()) {
            $translation->setTitle($faker->sentence(3));

            if ($faker->boolean(80)) {
                $translation->setUrl($faker->url);
            }
        }

        return $translation;
    }

    /**
     * @param string $menu Menu name
     *
     * @return \Darvin\MenuBundle\Entity\MenuItem|null
     */
    private function getParentItem(string $menu): ?MenuItem
    {
        /** @var \Darvin\MenuBundle\Entity\MenuItem[] $items */
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
