<?php
/**
 * @author    Igor Nikolaev <igor.sv.n@gmail.com>
 * @copyright Copyright (c) 2017, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Darvin\MenuBundle\Form\Type\Admin;

use Darvin\AdminBundle\EntityNamer\EntityNamerInterface;
use Darvin\ContentBundle\Entity\SlugMapItem;
use Darvin\ContentBundle\Repository\SlugMapItemRepository;
use Darvin\ContentBundle\Translatable\TranslationJoinerInterface;
use Darvin\MenuBundle\Form\DataTransformer\Admin\SlugMapItemToArrayTransformer;
use Darvin\Utils\CustomObject\CustomObjectLoaderInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Slug map item admin form type
 */
class SlugMapItemType extends AbstractType
{
    /**
     * @var \Darvin\Utils\CustomObject\CustomObjectLoaderInterface
     */
    private $customObjectLoader;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Darvin\AdminBundle\EntityNamer\EntityNamerInterface
     */
    private $entityNamer;

    /**
     * @var \Darvin\ContentBundle\Translatable\TranslationJoinerInterface
     */
    private $translationJoiner;

    /**
     * @param \Darvin\Utils\CustomObject\CustomObjectLoaderInterface        $customObjectLoader Custom object loader
     * @param \Doctrine\ORM\EntityManager                                   $em                 Entity manager
     * @param \Darvin\AdminBundle\EntityNamer\EntityNamerInterface          $entityNamer        Entity namer
     * @param \Darvin\ContentBundle\Translatable\TranslationJoinerInterface $translationJoiner  Translation joiner
     */
    public function __construct(
        CustomObjectLoaderInterface $customObjectLoader,
        EntityManager $em,
        EntityNamerInterface $entityNamer,
        TranslationJoinerInterface $translationJoiner
    ) {
        $this->customObjectLoader = $customObjectLoader;
        $this->em = $em;
        $this->entityNamer = $entityNamer;
        $this->translationJoiner = $translationJoiner;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $propertiesByClasses = $this->getPropertiesByClasses();

        $classPropertyChoices = $this->buildClassPropertyChoices($propertiesByClasses);

        $builder->add('class_property', ChoiceType::class, [
            'choices'           => $classPropertyChoices,
            'choices_as_values' => true,
            'required'          => false,
            'attr'              => [
                'class' => 'class_property',
            ],
        ]);

        $classPropertyChoiceLabels = array_keys($classPropertyChoices);
        $classPropertyChoiceValues = array_values($classPropertyChoices);

        $i = 0;

        foreach ($propertiesByClasses as $class => $properties) {
            foreach ($properties as $property) {
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

        $translationJoiner = $this->translationJoiner;

        $this->customObjectLoader->loadCustomObjects($slugMapItems, function (QueryBuilder $qb) use ($translationJoiner) {
            if ($translationJoiner->isTranslatable($qb->getRootEntities()[0])) {
                $translationJoiner->joinTranslation($qb, true, null, null, true);
            }
        });

        foreach ($view->children as $field) {
            if ('entity' !== $field->vars['block_prefixes'][2]) {
                continue;
            }
            /** @var \Symfony\Component\Form\ChoiceList\View\ChoiceView $choice */
            foreach ($field->vars['choices'] as $key => $choice) {
                /** @var \Darvin\ContentBundle\Entity\SlugMapItem $slugMapItem */
                $slugMapItem = $choice->data;

                if (null === $slugMapItem->getObject()) {
                    unset($field->vars['choices'][$key]);

                    continue;
                }

                $choice->label = $slugMapItem->getObject();
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
     * @param array $propertiesByClasses Slug map item properties by classes
     *
     * @return array
     */
    private function buildClassPropertyChoices(array $propertiesByClasses)
    {
        $choices = [];

        foreach ($propertiesByClasses as $class => $properties) {
            foreach ($properties as $property) {
                $choice = $this->entityNamer->name($class).'_'.$property;
                $choices['slug_map_item.'.$choice] = $choice;
            }
        }

        return $choices;
    }

    /**
     * @return array
     */
    private function getPropertiesByClasses()
    {
        $rows = $this->getSlugMapItemRepository()->createQueryBuilder('o')
            ->select('o.objectClass')
            ->addSelect('o.property')
            ->getQuery()
            ->getScalarResult();
        $properties = [];

        foreach ($rows as $row) {
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
