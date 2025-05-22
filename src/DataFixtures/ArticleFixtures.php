<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Exception;
use ProjetNormandie\ArticleBundle\Entity\Article;
use ProjetNormandie\ArticleBundle\ValueObject\ArticleStatus;

class ArticleFixtures extends Fixture
{
    /**
     * @var array<string>
     */
    private array $entities = [
        'Article',
    ];


    /**
     * @var array<mixed>
     */
    private array $articles = [
        [
            'id'    => 1,
            'status'    => ArticleStatus::UNDER_CONSTRUCTION,
            'author_id' => 1,
            'transtations' => [
                'fr' => [
                    'title' => 'FR',
                    'text' => 'FR'
                ],
                'en' => [
                    'title' => 'EN',
                    'text' => 'EN'
                ]
            ]
        ],
    ];


    private function updateGeneratorType(ObjectManager $manager): void
    {
        foreach ($this->entities as $entity) {
            $metadata = $manager->getClassMetaData("VideoGamesRecords\\CoreBundle\\Entity\\" . $entity);
            $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        }
    }

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->updateGeneratorType($manager);
        $this->loadArticles($manager);
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     */
    private function loadArticles(ObjectManager $manager): void
    {
        foreach ($this->articles as $row) {
            $article = new Article();
            $article->setId($row['id']);
            $article->setStatus($row['status']);

            $manager->persist($article);
            $this->addReference('article' . $article->getId(), $article);
        }
    }
}
