<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Admin;

use A2lix\TranslationFormBundle\Form\Type\TranslationsType;
use ProjetNormandie\ArticleBundle\Enum\ArticleStatus;
use ProjetNormandie\ArticleBundle\Form\Type\RichTextEditorType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelAutocompleteType;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ModelFilter;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class ArticleAdmin extends AbstractAdmin
{
    protected function generateBaseRouteName(bool $isChildAdmin = false): string
    {
        return 'pna_article_admin';
    }

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

    protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
    {
        $query = parent::configureQuery($query);
        $query->leftJoin($query->getRootAliases()[0]  . '.translations', 't')
            ->addSelect('t');
        return $query;
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->add('id', TextType::class, [
                'label' => 'article.form.id',
                'attr' => ['readonly' => true],
                'required' => false,
                'disabled' => true
            ])
            ->add('status', EnumType::class, [
                'label' => 'article.form.status',
                'required' => true,
                'class' => ArticleStatus::class,
                'choice_label' => fn(ArticleStatus $status) => $status->value,
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('publishedAt', DateTimeType::class, [
                'label' => 'article.form.published_at',
                'required' => false,
                'years' => range(2004, date('Y'))
            ])
            ->add('translations', TranslationsType::class, [
                'label' => false,
                'fields' => [
                    'title' => [
                        'field_type' => TextType::class,
                        'label' => 'Title',
                    ],
                    'content' => [
                        'field_type' => RichTextEditorType::class,
                        'label' => 'Content',
                    ]
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add(
                'author',
                ModelFilter::class,
                [
                    'label' => 'article.filter.author',
                    'field_type' => ModelAutocompleteType::class,
                    'field_options' => ['property' => 'username'],
                ]
            )
            ->add('translations.title', null, ['label' => 'article.filter.title'])
            ->add('status', null, ['label' => 'article.filter.status']);
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list->addIdentifier('id', null, ['label' => 'article.list.id'])
            ->add('getDefaultTitle', null, ['label' => 'article.list.title'])
            ->add('author', null, ['label' => 'article.list.author'])
            ->add('status', null, ['label' => 'article.list.status'])
            ->add('createdAt', null, ['label' => 'article.list.created_at'])
            ->add('publishedAt', 'datetime', ['label' => 'article.list.published_at'])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'groups' => [
                        'template' => '@ProjetNormandieArticle/admin/article_comments_link.html.twig'
                    ],
                ]
            ]);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id', null, ['label' => 'article.show.id'])
            ->add('status', null, ['label' => 'article.show.status'])
            ->add('title', null, ['label' => 'article.show.title'])
            ->add('content', null, ['label' => 'article.show.content', 'safe' => true]);
    }
}
