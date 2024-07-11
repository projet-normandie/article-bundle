<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use ProjetNormandie\ArticleBundle\Repository\ArticleRepository;
use ProjetNormandie\ArticleBundle\ValueObject\ArticleStatus;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableMethodsTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatablePropertiesTrait;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

#[ORM\Table(name:'pna_article')]
#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\EntityListeners(["ProjetNormandie\ArticleBundle\EventListener\Entity\ArticleListener"])]
#[ApiResource(
    order: ['publishedAt' => 'DESC'],
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['article:read', 'article:author', 'user:read']]
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'status' => 'exact',
    ]
)]
class Article implements SluggableInterface, TranslatableInterface
{
    use TimestampableEntity;
    use TranslatablePropertiesTrait;
    use TranslatableMethodsTrait;
    use SluggableTrait;

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

    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'article')]
    private Collection $comments;

    public function __toString()
    {
        return sprintf('%s [%s]', $this->getDefaultTitle(), $this->id);
    }

    public function getDefaultTitle(): string
    {
        return $this->translate('en', false)->getTitle();
    }

    public function getDefaultText(): string
    {
        return $this->translate('en', false)->getText();
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

    public function setTitle(string $title): void
    {
        $this->translate(null, false)->setTitle($title);
    }

    #[Groups(['article:read'])]
    public function getTitle(): string
    {
        return $this->translate(null, false)->getTitle();
    }

    public function setText(string $text): void
    {
        $this->translate(null, false)->setText($text);
    }

    #[Groups(['article:read'])]
    public function getText(): string
    {
        return $this->translate(null, false)->getText();
    }

    public function setComments(Collection $comments): void
    {
        $this->comments = $comments;
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getSluggableFields(): array
    {
        return ['defaultTitle'];
    }
}
