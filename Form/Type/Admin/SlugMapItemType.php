<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017-2018, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\Type\Admin;

use Darvin\AdminBundle\EntityNamer\EntityNamerInterface;
use Darvin\AdminBundle\Metadata\MetadataManager;
use Darvin\AdminBundle\Metadata\SortCriteriaDetector;
use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\ContentBundle\Repository\SlugMapItemRepository;
use Darvin\MenuBundle\Exception\DarvinMenuException;
use Darvin\MenuBundle\Form\DataTransformer\Admin\SlugMapItemToArrayTransformer;
use Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;
use Gedmo\Tree\TreeListener;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Slug map item admin form type
 */
class SlugMapItemType extends AbstractType
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\AdminBundle\EntityNamer\EntityNamerInterface
     */
    private $entityNamer;

    /**
     * @var \Darvin\AdminBundle\Metadata\MetadataManager
     */
    private $metadataManager;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var \Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader
     */
    private $slugMapItemCustomObjectLoader;

    /**
     * @var \Darvin\AdminBundle\Metadata\SortCriteriaDetector
     */
    private $sortCriteriaDetector;

    /**
     * @var \Gedmo\Tree\TreeListener
     */
    private $treeListener;

    /**
     * @var array
     */
    private $entityConfig;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface   $container                     DI container
     * @param \Doctrine\ORM\EntityManager                                 $em                            Entity manager
     * @param \Darvin\AdminBundle\EntityNamer\EntityNamerInterface        $entityNamer                   Entity namer
     * @param \Darvin\AdminBundle\Metadata\MetadataManager                $metadataManager               Metadata manager
     * @param \Symfony\Component\PropertyAccess\PropertyAccessorInterface $propertyAccessor              Property accessor
     * @param \Darvin\MenuBundle\SlugMap\SlugMapItemCustomObjectLoader    $slugMapItemCustomObjectLoader Slug map item custom object loader
     * @param \Darvin\AdminBundle\Metadata\SortCriteriaDetector           $sortCriteriaDetector          Sort criteria detector
     * @param \Gedmo\Tree\TreeListener                                    $treeListener                  Tree event listener
     * @param array                                                       $entityConfig                  Entity configuration
     */
    public function __construct(
        ContainerInterface $container,
        EntityManager $em,
        EntityNamerInterface $entityNamer,
        MetadataManager $metadataManager,
        PropertyAccessorInterface $propertyAccessor,
        SlugMapItemCustomObjectLoader $slugMapItemCustomObjectLoader,
        SortCriteriaDetector $sortCriteriaDetector,
        TreeListener $treeListener,
        array $entityConfig
    ) {
        $this->container = $container;
        $this->em = $em;
        $this->entityNamer = $entityNamer;
        $this->metadataManager = $metadataManager;
        $this->propertyAccessor = $propertyAccessor;
        $this->slugMapItemCustomObjectLoader = $slugMapItemCustomObjectLoader;
        $this->sortCriteriaDetector = $sortCriteriaDetector;
        $this->treeListener = $treeListener;
        $this->entityConfig = $entityConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $propertiesByClasses = $this->getPropertiesByClasses();

        $classPropertyChoices = $this->buildClassPropertyChoices($propertiesByClasses);

        $builder->add('class_property', ChoiceType::class, [
            'label'    => 'menu_item.entity.slug_map_item',
            'choices'  => $classPropertyChoices,
            'required' => false,
            'attr'     => [
                'class' => 'class_property',
            ],
        ]);

        $classPropertyChoiceLabels = array_keys($classPropertyChoices);
        $classPropertyChoiceValues = array_values($classPropertyChoices);

        $i = 0;

        foreach ($propertiesByClasses as $class => $properties) {
            foreach ($properties as $property) {
                if (!isset($classPropertyChoiceValues[$i])) {
                    throw new DarvinMenuException(<<<MESSAGE
Content slug map is invalid: please make sure you've replaced all overridden object classes in column "object_class" of
table "content_slug_map" in database.
MESSAGE
                    );
                }

                $builder->add($classPropertyChoiceValues[$i], EntityType::class, [
                    'label'         => $classPropertyChoiceLabels[$i],
                    'class'         => SlugMapItem::class,
                    'choice_label'  => 'id',
                    'required'      => false,
                    'query_builder' => function (SlugMapItemRepository $repository) use ($class, $property) {
                        return $repository->createQueryBuilder('o')
                            ->where('o.objectClass = :object_class')
                            ->setParameter('object_class', $class)
                            ->andWhere('o.property = :property')
                            ->setParameter('property', $property);
                    },
                    'attr' => [
                        'class'        => 'slave_input',
                        'data-master'  => '.class_property',
                        'data-show-on' => $classPropertyChoiceValues[$i],
                    ],
                ]);

                $i++;
            }
        }

        $builder->addModelTransformer(new SlugMapItemToArrayTransformer($this->entityNamer));
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $slugMapItems = [];

        foreach ($view->children as $field) {
            if ('entity' !== $field->vars['block_prefixes'][2]) {
                continue;
            }
            /** @var \Symfony\Component\Form\ChoiceList\View\ChoiceView $choice */
            foreach ($field->vars['choices'] as $choice) {
                $slugMapItems[] = $choice->data;
            }
        }

        $this->slugMapItemCustomObjectLoader->loadCustomObjects($slugMapItems);

        foreach ($view->children as $field) {
            if ('entity' !== $field->vars['block_prefixes'][2]) {
                continue;
            }

            /** @var \Symfony\Component\Form\ChoiceList\View\ChoiceView[] $choices */
            $choices = $entities = [];

            foreach ($field->vars['choices'] as $key => $choice) {
                /** @var \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem */
                $slugMapItem = $choice->data;

                if (null === $slugMapItem->getObject()) {
                    continue;
                }

                $choices[$key] = $choice;

                $entities[] = $slugMapItem->getObject();
            }
            if (empty($entities)) {
                continue;
            }

            $class = ClassUtils::getClass(reset($entities));

            $entities = $this->sortEntities($entities, $class);

            $treeConfig = $this->treeListener->getConfiguration($this->em, $class);
            $levelProperty = !empty($treeConfig) ? $treeConfig['level'] : null;

            $field->vars['choices'] = [];

            foreach ($entities as $entity) {
                foreach ($choices as $key => $choice) {
                    /** @var \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem */
                    $slugMapItem = $choice->data;

                    if ($slugMapItem->getObject() !== $entity) {
                        continue;
                    }

                    $choice->label = '';

                    if (!empty($levelProperty)) {
                        $choice->label .= str_repeat('---', $this->propertyAccessor->getValue($entity, $levelProperty) - 1);
                    }

                    $choice->label .= (string)$entity;

                    $field->vars['choices'][$key] = $choice;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('label', false);
    }

    /**
     * @param object[] $entities Entities
     * @param string   $class    Entity class
     *
     * @return object[]
     */
    private function sortEntities(array $entities, $class)
    {
        if (empty($entities) || !$this->metadataManager->hasMetadata($class)) {
            return $entities;
        }

        $sortCriteria = $this->sortCriteriaDetector->detect($class);

        if (!empty($sortCriteria)) {
            $propertyAccessor = $this->propertyAccessor;

            usort($entities, function ($entityA, $entityB) use ($propertyAccessor, $sortCriteria) {
                foreach ($sortCriteria as $property => $direction) {
                    $valueA = $propertyAccessor->getValue($entityA, $property);
                    $valueB = $propertyAccessor->getValue($entityB, $property);

                    if ($valueA === $valueB) {
                        continue;
                    }

                    $result = $valueA > $valueB ? 1 : -1;

                    if ('desc' === $direction) {
                        $result *= -1;
                    }

                    return $result;
                }

                return 0;
            });
        }

        $config = $this->metadataManager->getConfiguration($class);

        if (isset($config['sorter'])) {
            $sortCallback = [$this->container->get($config['sorter']['id']), $config['sorter']['method']];
            $entities = $sortCallback($entities);
        }

        return $entities;
    }

    /**
     * @param array $propertiesByClasses Slug map item properties by classes
     *
     * @return array
     */
    private function buildClassPropertyChoices(array $propertiesByClasses)
    {
        $choices = [];

        foreach ($propertiesByClasses as $class => $properties) {
            foreach ($properties as $property) {
                $entityName = $this->entityNamer->name($class);
                $choices[sprintf('slug_map_item.%s.%s', $entityName, $property)] = $entityName.'_'.$property;
            }
        }

        return $choices;
    }

    /**
     * @return array
     */
    private function getPropertiesByClasses()
    {
        $classBlacklist = [];

        foreach ($this->entityConfig as $class => $config) {
            if (!$config['admin']) {
                $classBlacklist[] = $class;
            }
        }

        $qb = $this->getSlugMapItemRepository()->createQueryBuilder('o')
            ->select('o.objectClass')
            ->addSelect('o.property');

        if (!empty($classBlacklist)) {
            $qb
                ->andWhere($qb->expr()->notIn('o.objectClass', ':class_blacklist'))
                ->setParameter('class_blacklist', $classBlacklist);
        }

        $properties = [];

        foreach ($qb->getQuery()->getScalarResult() as $row) {
            $class = $row['objectClass'];
            $property = $row['property'];

            if (!isset($properties[$class])) {
                $properties[$class] = [];
            }
            if (!in_array($property, $properties[$class])) {
                $properties[$class][] = $property;
            }
        }

        return $properties;
    }

    /**
     * @return \Darvin\ContentBundle\Repository\SlugMapItemRepository
     */
    private function getSlugMapItemRepository()
    {
        return $this->em->getRepository(SlugMapItem::class);
    }
}
