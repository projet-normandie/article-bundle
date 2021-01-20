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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use ProjetNormandie\ArticleBundle\Entity\Article;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Doctrine\ORM\EntityManager;

/**
 * Administration manager for the Article Bundle.
 */
class ArticleAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'pnarticlebundle_admin_article';

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
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
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('id', TextType::class, ['label' => 'id', 'attr' => ['readonly' => true]])
            ->add('author', ModelListType::class, [
                'btn_add' => false,
                'btn_list' => true,
                'btn_edit' => false,
                'btn_delete' => true,
                'btn_catalogue' => true,
                'label' => 'Author',
             ])
            ->add(
                'status',
                ChoiceType::class,
                [
                    'label' => 'Status',
                    'choices' => Article::getStatusChoices(),
                ]
            )
            ->add('publishedAt', DateType::class, [
                'label' => 'Published At',
                'required' => false,
                'years' => range(2004, date('Y'))
            ])
            ->add('translations', TranslationsType::class, [
                'required' => true,
                'fields' => [
                    'title' => [
                        'field_type' => TextType::class,
                        'label' => ' Title',
                    ],
                    'text' => [
                        'field_type' => CKEditorType::class,
                        'label' => ' Text',
                    ]
                ]
            ]);
    }

    /**
     * @param DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('translations.title')
            ->add('status');
    }

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper->addIdentifier('id')
            ->add('getDefaultTitle', null, ['label' => 'Title'])
            ->add('author')
            ->add(
                'status',
                'choice',
                [
                    'label' => 'Status',
                    'editable' => false,
                    'choices' => Article::getStatusChoices(),
                ]
            )
            ->add('createdAt', null, ['label' => 'Created At'])
            ->add('published_at', 'datetime', ['label' => 'Published At'])
            ->add('_action', 'actions', [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'groups' => [
                        'template' => 'ProjetNormandieArticleBundle:Admin:article_comments_link.html.twig'
                    ],
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
            ->add('status')
            ->add('getDefaultTitle', null, ['label' => 'Title'])
            ->add('getDefaultText', null, ['label' => 'Text', 'safe' => true]);
    }

    /**
     * @param object $object
     */
    public function preUpdate($object)
    {
        /** @var EntityManager $em */
        $em = $this->getModelManager()->getEntityManager($this->getClass());
        $originalObject = $em->getUnitOfWork()->getOriginalEntityData($object);

        // PUBLISHED
        if ($originalObject['status'] === Article::STATUS_UNDER_CONSTRUCTION
            && $object->getStatus() === Article::STATUS_PUBLISHED) {
            $object->setPublishedAt(new DateTime());
        }
    }
}
