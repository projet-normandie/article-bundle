<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use ProjetNormandie\ArticleBundle\Repository\CommentRepository;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name:'pna_comment')]
#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\EntityListeners(["ProjetNormandie\ArticleBundle\EventListener\Entity\CommentListener"])]
#[ApiResource(
    shortName: 'ArticleComment',
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            denormalizationContext: ['groups' => ['comment:insert']],
            security: 'is_granted("ROLE_USER")'
        ),
        new Put(
            denormalizationContext: ['groups' => ['comment:update']],
            security: 'is_granted("ROLE_USER") and (object.getUser() == user)'
        )
    ],
    normalizationContext: ['groups' => ['comment:read', 'user:read']],
    order: ['id' => 'ASC']
)]
#[ApiResource(
    uriTemplate: '/articles/{id}/comments',
    shortName: 'ArticleComment',
    operations: [ new GetCollection() ],
    uriVariables: [
        'id' => new Link(toProperty: 'article', fromClass: Article::class),
    ],
    normalizationContext: ['groups' => ['comment:read', 'user:read']],
)]

class Comment
{
    use TimestampableEntity;

    #[Groups(['comment:read'])]
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    private ?int $id = null;

    #[Groups(['comment:insert'])]
    #[ORM\ManyToOne(targetEntity: Article::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name:'article_id', referencedColumnName:'id', nullable:false)]
    private Article $article;

    #[Groups(['comment:read', 'comment:user'])]
    #[ORM\ManyToOne(targetEntity: UserInterface::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name:'user_id', referencedColumnName:'id', nullable:false)]
    private $user;

    #[Groups(['comment:read', 'comment:insert', 'comment:update'])]
    #[ORM\Column(type: 'text', nullable: false)]
    private string $content;

    public function __toString()
    {
        return sprintf('comment [%s]', $this->id);
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function setArticle(Article $article): void
    {
        $this->article = $article;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user): void
    {
        $this->user = $user;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}
