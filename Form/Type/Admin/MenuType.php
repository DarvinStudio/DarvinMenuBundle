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

use Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface;
use Darvin\MenuBundle\Configuration\MenuConfiguration;
use Darvin\MenuBundle\Entity\Menu\Item;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Menu admin form type
 */
class MenuType extends AbstractType
{
    /**
     * @var \Darvin\MenuBundle\Configuration\MenuConfiguration
     */
    private $menuConfig;

    /**
     * @var \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @param \Darvin\MenuBundle\Configuration\MenuConfiguration         $menuConfig      Menu configuration
     * @param \Darvin\AdminBundle\Metadata\AdminMetadataManagerInterface $metadataManager Metadata manager
     * @param \Symfony\Component\HttpFoundation\RequestStack             $requestStack    Request stack
     */
    public function __construct(MenuConfiguration $menuConfig, AdminMetadataManagerInterface $metadataManager, RequestStack $requestStack)
    {
        $this->menuConfig = $menuConfig;
        $this->metadataManager = $metadataManager;
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $metadataManager = $this->metadataManager;
        $requestStack = $this->requestStack;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($metadataManager, $requestStack) {
            $data = $event->getData();

            if (!empty($data)) {
                return;
            }

            $request = $requestStack->getCurrentRequest();

            if (empty($request)) {
                return;
            }

            $filterData = $request->query->get($metadataManager->getMetadata(Item::class)->getFilterFormTypeName());

            if (isset($filterData['menu'])) {
                $event->setData($filterData['menu']);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => $this->buildChoices(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
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
}
