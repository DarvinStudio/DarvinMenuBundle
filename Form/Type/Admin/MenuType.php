<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2016, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\Type\Admin;

use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\MenuBundle\Configuration\MenuConfiguration;
use Darvin\MenuBundle\Entity\Menu\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Menu admin form type
 */
class MenuType extends AbstractType
{
    const MENU_TYPE_CLASS = __CLASS__;

    /**
     * @var \Darvin\MenuBundle\Configuration\MenuConfiguration
     */
    private $menuConfig;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @param \Darvin\MenuBundle\Configuration\MenuConfiguration $menuConfig      Menu configuration
     * @param \Darvin\AdminBundle\Metadata\MetadataManager       $metadataManager Metadata manager
     * @param \Symfony\Component\HttpFoundation\RequestStack     $requestStack    Request stack
     */
    public function __construct(MenuConfiguration $menuConfig, MetadataManager $metadataManager, RequestStack $requestStack)
    {
        $this->menuConfig = $menuConfig;
        $this->metadataManager = $metadataManager;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices'           => $this->buildChoices(),
            'choices_as_values' => true,
            'data'              => $this->getData(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\ChoiceType';
    }

    /**
     * @return array
     */
    private function buildChoices()
    {
        $choices = [];

        foreach ($this->menuConfig->getMenus() as $menu) {
            $choices[$menu->getTitle()] = $menu->getAlias();
        }

        return $choices;
    }

    /**
     * @return string
     */
    private function getData()
    {
        $request = $this->requestStack->getCurrentRequest();

        if (empty($request)) {
            return null;
        }

        $filterData = $request->query->get($this->metadataManager->getMetadata(Item::ITEM_CLASS)->getFilterFormTypeName());

        return isset($filterData['menu']) ? $filterData['menu'] : null;
    }
}
