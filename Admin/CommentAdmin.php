<?php

namespace ProjetNormandie\ArticleBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use DateTime;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Filter\ModelAutocompleteFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use ProjetNormandie\ArticleBundle\Entity\Article;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Form\Type\ModelListType;

/**
 * Administration manager for the Article Bundle.
 */
class CommentAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'pnarticlebundle_admin_comment';

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('export');
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('id', TextType::class, ['label' => 'id', 'attr' => ['readonly' => true]])
            ->add('user', ModelListType::class, [
                'btn_add' => false,
                'btn_list' => true,
                'btn_edit' => false,
                'btn_delete' => true,
                'btn_catalogue' => true,
                'label' => 'User',
             ])
            ->add('article', ModelListType::class, [
                'btn_add' => false,
                'btn_list' => true,
                'btn_edit' => false,
                'btn_delete' => true,
                'btn_catalogue' => true,
                'label' => 'Article',
            ])
            ->add(
                'text'
            );
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('article')
            ->add('user', ModelAutocompleteFilter::class, [], null, [
                'property' => 'username',
            ]);
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('id')
            ->add('getDefaultTitle', null, ['label' => 'Title'])
            ->add('user')
            ->add('article')
            ->add('createdAt', null, ['label' => 'Created At'])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                ]
            ]);
    }

    /**
     * @param ShowMapper $showMapper
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('user')
            ->add('article')
            ->add('text');
    }
}
