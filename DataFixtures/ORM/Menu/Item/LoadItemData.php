<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\DataFixtures\ORM\Menu\Item;

use Darvin\MenuBundle\Association\Associated;
use Darvin\MenuBundle\Entity\Menu\Item;
use Darvin\MenuBundle\Entity\Menu\ItemTranslation;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Menu item data fixture
 */
class LoadItemData implements ContainerAwareInterface, FixtureInterface
{
    const COUNT = 20;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var array[]
     */
    private $associatedIds;

    /**
     * @var \Faker\Generator[]
     */
    private $fakers;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->associatedIds = $this->fakers = [];
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

        foreach ($this->getMenuConfig()->getMenus() as $menu) {
            for ($i = 0; $i < self::COUNT; $i++) {
                $manager->persist($this->createMenuItem($fakerLocales, $menu->getAlias()));
            }
        }

        $manager->flush();
    }

    /**
     * @param array  $fakerLocales Faker locales
     * @param string $menuAlias    Menu alias
     *
     * @return \Darvin\MenuBundle\Entity\Menu\Item
     */
    private function createMenuItem(array $fakerLocales, $menuAlias)
    {
        $faker = $this->getFaker();

        $item = (new Item())
            ->setShowChildren($faker->boolean());

        foreach ($fakerLocales as $locale => $fakerLocale) {
            $item->addTranslation($this->createMenuItemTranslation($locale, $fakerLocale));
        }
        if ($faker->boolean(80)) {
            $item->setAssociated($this->getRandomAssociated());
        }

        return $item->setMenu($menuAlias);
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
            ->setEnabled($faker->boolean(80))
            ->setTitle($faker->boolean() ? $faker->realText(30) : null)
            ->setUrl($faker->boolean() ? $faker->url : null)
            ->setLocale($locale);
    }

    /**
     * @param string $fakerLocale Faker locale
     *
     * @return \Faker\Generator
     */
    private function getFaker($fakerLocale = Factory::DEFAULT_LOCALE)
    {
        if (!isset($this->fakers[$fakerLocale])) {
            $this->fakers[$fakerLocale] = Factory::create($fakerLocale);
        }

        return $this->fakers[$fakerLocale];
    }

    /**
     * @return array
     */
    private function getFakerLocales()
    {
        $locales = [];

        foreach ($this->container->getParameter('locales') as $locale) {
            $locales[$locale] = $locale.'_'.strtoupper($locale);
        }

        return $locales;
    }

    /**
     * @return \Darvin\MenuBundle\Association\Associated
     */
    private function getRandomAssociated()
    {
        $associations = $this->getAssociationConfig()->getAssociations();
        $association = $associations[array_rand($associations)];
        $class = $association->getClass();

        if (!isset($this->associatedIds[$class])) {
            $em = $this->getEntityManager();
            $identifiers = $em->getClassMetadata($class)->getIdentifier();
            $identifier = reset($identifiers);
            $qb = $em->getRepository($class)->createQueryBuilder('o');
            $this->associatedIds[$class] = array_map(function (array $row) use ($identifier) {
                return $row[$identifier];
            }, $qb->select('o.'.$identifier)->getQuery()->getScalarResult());
        }

        $ids = $this->associatedIds[$class];

        return new Associated($class, $ids[array_rand($ids)]);
    }

    /**
     * @return \Darvin\MenuBundle\Configuration\AssociationConfiguration
     */
    private function getAssociationConfig()
    {
        return $this->container->get('darvin_menu.configuration.association');
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * @return \Darvin\MenuBundle\Configuration\MenuConfiguration
     */
    private function getMenuConfig()
    {
        return $this->container->get('darvin_menu.configuration.menu');
    }
}
