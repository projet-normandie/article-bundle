<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name:'pna_article_translation')]
#[ORM\Entity]
#[ORM\EntityListeners(["ProjetNormandie\ArticleBundle\EventListener\Entity\ArticleTranslationListener"])]
#[ORM\UniqueConstraint(name: 'article_translation_unique', columns: ['translatable_id', 'locale'])]
class ArticleTranslation
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'translatable_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Article $translatable = null;

    #[ORM\Column(length: 5)]
    private string $locale;

    #[Assert\NotBlank]
    #[ORM\Column(length: 255, nullable: false)]
    private string $title = '';

    #[Assert\NotBlank]
    #[ORM\Column(type: 'text', nullable: false)]
    private string $text = '';

    public function __toString(): string
    {
        return $this->title ?: '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTranslatable(): ?Article
    {
        return $this->translatable;
    }

    public function setTranslatable(?Article $translatable): void
    {
        $this->translatable = $translatable;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }
}
