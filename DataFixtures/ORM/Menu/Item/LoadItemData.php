<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\DataFixtures\ORM\Menu\Item;

use Andyftw\Faker\ImageProvider;
use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\MenuBundle\Configuration\Menu;
use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\MenuBundle\Entity\Menu\ItemTranslation;
use Darvin\MenuBundle\Entity\Menu\MenuItemImage;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Menu item data fixture
 */
class LoadItemData implements FixtureInterface, ContainerAwareInterface
{
    private const COUNT_PER_MENU = 30;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Faker\Generator[]
     */
    private $fakers;

    /**
     * @var array
     */
    private $parents;

    /**
     * @var int[]
     */
    private $slugMapItemIds;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->fakers = $this->parents = [];
        $this->slugMapItemIds = null;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $fakerLocales = $this->getFakerLocales();

        foreach ($this->getMenuAliases() as $menu) {
            $this->parents[$menu] = [];

            for ($i = 0; $i < self::COUNT_PER_MENU; $i++) {
                $manager->persist($this->createMenuItem($menu, $fakerLocales));
            }
        }

        $manager->flush();
    }

    /**
     * @param string $menu         Menu alias
     * @param array  $fakerLocales Faker locales
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Item
     */
    private function createMenuItem($menu, array $fakerLocales)
    {
        $faker = $this->getFaker();

        $item = (new Item())
            ->setHoverImage($faker->boolean(80) ? $this->createMenuItemImage() : null)
            ->setImage($faker->boolean(80) ? $this->createMenuItemImage() : null)
            ->setMenu($menu)
            ->setParent($faker->boolean(90) ? $this->getRandomParent($menu) : null)
            ->setSlugMapItem($faker->boolean(90) ? $this->getRandomSlugMapItem() : null);

        $this->parents[$menu][] = $item;

        foreach ($fakerLocales as $locale => $fakerLocale) {
            $item->addTranslation($this->createMenuItemTranslation($locale, $fakerLocale));
        }

        return $item;
    }

    /**
     * @return \Darvin\MenuBundle\Entity\Menu\MenuItemImage
     */
    private function createMenuItemImage()
    {
        $pathname = $this->getFaker()->imageFile();

        return (new MenuItemImage())->setFile(new UploadedFile($pathname, $pathname, null, null, null, true));
    }

    /**
     * @param string $locale      Locale
     * @param string $fakerLocale Faker locale
     *
     * @return \Darvin\MenuBundle\Entity\Menu\ItemTranslation|\Knp\DoctrineBehaviors\Model\Translatable\Translation
     */
    private function createMenuItemTranslation($locale, $fakerLocale)
    {
        $faker = $this->getFaker($fakerLocale);

        return (new ItemTranslation())
            ->setLocale($locale)
            ->setTitle($faker->realText(20))
            ->setUrl($faker->boolean(10) ? $faker->url : null);
    }

    /**
     * @param string $fakerLocale Faker locale
     *
     * @return \Faker\Generator
     */
    private function getFaker($fakerLocale = Factory::DEFAULT_LOCALE)
    {
        if (!isset($this->fakers[$fakerLocale])) {
            $faker = Factory::create($fakerLocale);
            $faker->addProvider(new ImageProvider($faker));
            $this->fakers[$fakerLocale] = $faker;
        }

        return $this->fakers[$fakerLocale];
    }

    /**
     * @return array
     */
    private function getFakerLocales()
    {
        if ($this->container->hasParameter('faker_locales')) {
            return $this->container->getParameter('faker_locales');
        }

        $locales = [];

        foreach ($this->container->getParameter('locales') as $locale) {
            $locales[$locale] = $locale.'_'.strtoupper($locale);
        }

        return $locales;
    }

    /**
     * @return string[]
     */
    private function getMenuAliases()
    {
        return array_map(function (Menu $menu) {
            return $menu->getAlias();
        }, $this->getMenuConfig()->getMenus());
    }

    /**
     * @param string $menu Menu alias
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Item
     */
    private function getRandomParent($menu)
    {
        return !empty($this->parents[$menu]) ? $this->parents[$menu][array_rand($this->parents[$menu])] : null;
    }

    /**
     * @return \Darvin\ContentBundle\Entity\SlugMapItem
     */
    private function getRandomSlugMapItem()
    {
        $em = $this->getEntityManager();

        if (null === $this->slugMapItemIds) {
            $this->slugMapItemIds = array_column(
                $em->getRepository(SlugMapItem::class)->createQueryBuilder('o')->select('o.id')->getQuery()->getScalarResult(),
                'id'
            );
        }

        return $em->getReference(SlugMapItem::class, $this->slugMapItemIds[array_rand($this->slugMapItemIds)]);
    }

    /**
     * @return \Darvin\MenuBundle\Configuration\MenuConfiguration
     */
    private function getMenuConfig()
    {
        return $this->container->get('darvin_menu.configuration.menu');
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }
}
