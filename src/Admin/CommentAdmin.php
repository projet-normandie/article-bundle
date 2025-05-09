<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Admin;

use ProjetNormandie\ArticleBundle\Form\Type\RichTextEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\AdminBundle\Form\Type\ModelListType;

class CommentAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'pna_comment_admin';

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollectionInterface $collection): void
    {
        $collection
            ->remove('create')
            ->remove('export');
    }

    /**
     * @param FormMapper $form
     */
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('id', TextType::class, ['label' => 'label.id', 'attr' => ['readonly' => true]])
            ->add('user', ModelListType::class, [
                'btn_add' => false,
                'btn_list' => false,
                'btn_edit' => false,
                'btn_delete' => false,
                'btn_catalogue' => false,
                'label' => 'label.user',
             ])
            ->add('article', ModelListType::class, [
                'btn_add' => false,
                'btn_list' => false,
                'btn_edit' => false,
                'btn_delete' => false,
                'btn_catalogue' => false,
                'label' => 'label.article',
            ])
            ->add('content', RichTextEditorType::class, [
                'label' => 'label.content',
                'required' => true,
            ]);
    }

    /**
     * @param DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('article.translations.title', null, ['label' => 'label.title'])
            ->add(
                'article',
                ModelFilter::class,
                [
                    'label' => 'label.article',
                    'field_type' => ModelAutocompleteType::class,
                    'field_options' => ['property' => 'title'],
                ]
            )
            ->add('user', ModelFilter::class, [
                'label' => 'label.user',
                'field_type' => ModelAutocompleteType::class,
                'field_options' => ['property' => 'username'],
            ])
        ;
    }

    /**
     * @param ListMapper $list
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id', null, ['label' => 'label.id'])
            ->add('article', null, ['label' => 'label.article'])
            ->add('user', null, ['label' => 'label.user'])
            ->add('createdAt', null, ['label' => 'label.createdAt'])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
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
            ->add('user', null, ['label' => 'label.user'])
            ->add('article', null, ['label' => 'label.article'])
            ->add('content', null, ['label' => 'label.content', 'safe' => true]);
    }
}
