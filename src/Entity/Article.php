<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use ProjetNormandie\ArticleBundle\Repository\ArticleRepository;
use ProjetNormandie\ArticleBundle\ValueObject\ArticleStatus;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Table(name:'pna_article')]
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\EntityListeners(["ProjetNormandie\ArticleBundle\EventListener\Entity\ArticleListener"])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['article:read', 'article:author', 'user:read']],
    order: ['publishedAt' => 'DESC']
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'status' => 'exact',
    ]
)]
class Article
{
    use TimestampableEntity;

    // FALLBACK language
    private const string DEFAULT_LOCALE = 'en';

    #[Groups(['article:read'])]
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(length: 30, nullable: false)]
    private string $status = ArticleStatus::UNDER_CONSTRUCTION;

    #[Groups(['article:read'])]
    #[ORM\Column(nullable: false, options: ['default' => 0])]
    private int $nbComment = 0;

    #[Groups(['article:author'])]
    #[ORM\ManyToOne(targetEntity: UserInterface::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name:'author_id', referencedColumnName:'id', nullable:false)]
    private $author;

    #[Groups(['article:read'])]
    #[ORM\Column(nullable: true)]
    private ?DateTime $publishedAt = null;

    /**
     * @var Collection<Comment>
     */
    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Comment::class)]
    private Collection $comments;

    /** @var Collection<ArticleTranslation> */
    #[ORM\OneToMany(
        mappedBy: 'translatable',
        targetEntity: ArticleTranslation::class,
        cascade: ['persist', 'remove'],
        orphanRemoval: true,
        indexBy: 'locale'
    )]
    private Collection $translations;

    #[Groups(['article:read'])]
    #[ORM\Column(length: 255, unique: false)]
    private string $slug;

    private ?string $currentLocale = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->translations = new ArrayCollection();
    }

    public function __toString()
    {
        return sprintf('%s [%s]', $this->getDefaultTitle(), $this->id);
    }

    public function getDefaultTitle(): string
    {
        return $this->getTitle(self::DEFAULT_LOCALE) ?: 'Untitled';
    }

    public function getDefaultContent(): string
    {
        return $this->getContent(self::DEFAULT_LOCALE) ?: '';
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getArticleStatus(): ArticleStatus
    {
        return new ArticleStatus($this->status);
    }

    public function setNbComment(int $nbComment): void
    {
        $this->nbComment = $nbComment;
    }

    public function getNbComment(): int
    {
        return $this->nbComment;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author): void
    {
        $this->author = $author;
    }

    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTime $publishedAt = null): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function setComments(Collection $comments): void
    {
        $this->comments = $comments;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    // Translation methods for A2lix compatibility
    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function setTranslations(Collection $translations): void
    {
        $this->translations = $translations;
    }

    public function addTranslation(ArticleTranslation $translation): void
    {
        if (!$this->translations->contains($translation)) {
            $translation->setTranslatable($this);
            $this->translations->set($translation->getLocale(), $translation);
        }
    }

    public function removeTranslation(ArticleTranslation $translation): void
    {
        $this->translations->removeElement($translation);
    }

    /**
     * Retrieves a translation with intelligent fallback logic.
     * Ensures content quality by checking for non-empty translations.
     */
    public function translate(?string $locale = null, bool $fallbackToDefault = true): ?ArticleTranslation
    {
        $locale = $locale ?: $this->currentLocale ?: self::DEFAULT_LOCALE;

        // If translation exists for requested locale
        if ($this->translations->containsKey($locale)) {
            $translation = $this->translations->get($locale);
            // Check that translation is not empty
            if (!empty($translation->getTitle()) || !empty($translation->getContent())) {
                return $translation;
            }
        }

        // Fallback to default locale if enabled and different from requested locale
        if ($fallbackToDefault && $locale !== self::DEFAULT_LOCALE && $this->translations->containsKey(self::DEFAULT_LOCALE)) {
            $translation = $this->translations->get(self::DEFAULT_LOCALE);
            if (!empty($translation->getTitle()) || !empty($translation->getContent())) {
                return $translation;
            }
        }

        // If no valid translation exists, return first non-empty available
        foreach ($this->translations as $translation) {
            if (!empty($translation->getTitle()) || !empty($translation->getContent())) {
                return $translation;
            }
        }

        // Last resort: return first translation even if empty
        return $this->translations->first() ?: null;
    }

    /**
     * Vérifie si une traduction existe pour une locale donnée
     */
    public function hasTranslation(string $locale): bool
    {
        return $this->translations->containsKey($locale);
    }

    /**
     * Retourne toutes les locales disponibles pour cet article
     */
    public function getAvailableLocales(): array
    {
        return $this->translations->getKeys();
    }

    public function setCurrentLocale(string $locale): void
    {
        $this->currentLocale = $locale;
    }

    public function getCurrentLocale(): ?string
    {
        return $this->currentLocale;
    }

    public function setTitle(string $title, ?string $locale = null): void
    {
        $locale = $locale ?: $this->currentLocale ?: self::DEFAULT_LOCALE;

        if (!$this->translations->containsKey($locale)) {
            $translation = new ArticleTranslation();
            $translation->setTranslatable($this);
            $translation->setLocale($locale);
            $this->translations->set($locale, $translation);
        }

        $this->translations->get($locale)->setTitle($title);
    }

    #[Groups(['article:read'])]
    public function getTitle(?string $locale = null): ?string
    {
        $translation = $this->translate($locale);
        return $translation?->getTitle();
    }

    public function setContent(string $content, ?string $locale = null): void
    {
        $locale = $locale ?: $this->currentLocale ?: self::DEFAULT_LOCALE;

        if (!$this->translations->containsKey($locale)) {
            $translation = new ArticleTranslation();
            $translation->setTranslatable($this);
            $translation->setLocale($locale);
            $this->translations->set($locale, $translation);
        }

        $this->translations->get($locale)->setContent($content);
    }

    #[Groups(['article:read'])]
    public function getContent(?string $locale = null): ?string
    {
        $translation = $this->translate($locale);
        return $translation?->getContent();
    }

    // Old methods for backward compatibility
    public function mergeNewTranslations(): void
    {
        // Not needed anymore
    }
}
