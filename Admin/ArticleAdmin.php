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

/**
 * Administration manager for the Article Bundle.
 */
class ArticleAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'pnforumbundle_admin_article';

    /**
     * @inheritdoc
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('export');
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
     * @inheritdoc
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
     * @inheritdoc
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('translations.title')
            ->add('status');
    }

    /**
     * @inheritdoc
     * @throws \RuntimeException When defining wrong or duplicate field names.
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
                ]
            ]);
    }

    /**
     * @inheritdoc
     * @throws \RuntimeException When defining wrong or duplicate field names.
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
     * @param \ProjetNormandie\ArticleBundle\Entity\Article $object
     * @throws \Exception
     */
    public function preUpdate($object)
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getModelManager()->getEntityManager($this->getClass());
        $originalObject = $em->getUnitOfWork()->getOriginalEntityData($object);

        // PUBLISHED
        if ($originalObject['status'] === Article::STATUS_UNDER_CONSTRUCTION && $object->getStatus() === Article::STATUS_PUBLISHED) {
            $object->setPublishedAt(new DateTime());
        }
    }
}
