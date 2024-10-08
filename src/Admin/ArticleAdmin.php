<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use ProjetNormandie\ArticleBundle\ValueObject\ArticleStatus;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class ArticleAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'pna_article_admin';

    /**
     * @param RouteCollectionInterface $collection
     */
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection->remove('export');
    }

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues['_page'] = 1;
        $sortValues['_sort_order'] = 'DESC';
        $sortValues['_sort_by'] = 'id';
    }

    /**
     * @param ProxyQueryInterface $query
     * @return ProxyQueryInterface
     */
    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query = parent::configureQuery($query);
        $query->leftJoin($query->getRootAliases()[0]  . '.translations', 't')
            ->addSelect('t');
        return $query;
    }


    /**
     * @param FormMapper $form
     */
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('id', TextType::class, ['label' => 'label.id', 'attr' => ['readonly' => true]])
            ->add(
                'status',
                ChoiceType::class,
                [
                    'label' => 'label.status',
                    'choices' => ArticleStatus::getStatusChoices(),
                ]
            )
            ->add('publishedAt', DateTimeType::class, [
                'label' => 'label.publishedAt',
                'required' => false,
                'years' => range(2004, date('Y'))
            ])
            ->add('translations', TranslationsType::class, [
                'required' => true,
                'fields' => [
                    'title' => [
                        'field_type' => TextType::class,
                        'label' => 'label.title',
                    ],
                    'text' => [
                        'field_type' => CKEditorType::class,
                        'label' => 'label.text',
                    ]
                ]
            ]);
    }

    /**
     * @param DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add(
                'author',
                ModelFilter::class,
                [
                    'label' => 'label.author',
                    'field_type' => ModelAutocompleteType::class,
                    'field_options' => ['property' => 'username'],
                ]
            )
            ->add('translations.title', null, ['label' => 'label.title'])
            ->add('status', null, ['label' => 'label.status']);
    }

    /**
     * @param ListMapper $list
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id', null, ['label' => 'label.id'])
            ->add('getDefaultTitle', null, ['label' => 'label.title'])
            ->add('author', null, ['label' => 'label.author'])
            ->add(
                'status',
                'choice',
                [
                    'label' => 'label.status',
                    'editable' => false,
                    'choices' => ArticleStatus::getStatusChoices(),
                ]
            )
            ->add('createdAt', null, ['label' => 'label.createdAt'])
            ->add('published_at', 'datetime', ['label' => 'label.publishedAt'])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'groups' => [
                        'template' => '@ProjetNormandieArticle/Admin/article_comments_link.html.twig'
                    ],
                ]
            ]);
    }

    /**
     * @param ShowMapper $show
     */
    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id', null, ['label' => 'label.id'])
            ->add('status', null, ['label' => 'label.status'])
            ->add('getDefaultTitle', null, ['label' => 'label.title'])
            ->add('getDefaultText', null, ['label' => 'label.text', 'safe' => true]);
    }
}
