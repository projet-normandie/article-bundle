<?php

namespace ProjetNormandie\ArticleBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableMethodsTrait;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatablePropertiesTrait;
use Knp\DoctrineBehaviors\Contract\Entity\SluggableInterface;
use Knp\DoctrineBehaviors\Model\Sluggable\SluggableTrait;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use DateTime;

/**
 * Article
 *
 * @ORM\Table(name="article")
 * @ORM\Entity(repositoryClass="ProjetNormandie\ArticleBundle\Repository\ArticleRepository")
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *          "status": "exact",
 *     }
 * )
 * @ApiFilter(
 *     OrderFilter::class,
 *     properties={
 *          "id":"ASC",
 *          "publishedAt": "DESC"
 *     },
 *     arguments={"orderParameterName"="order"}
 * )
 */
class Article implements SluggableInterface, TimestampableInterface, TranslatableInterface
{
    use TimestampableTrait;
    use TranslatablePropertiesTrait;
    use TranslatableMethodsTrait;
    use SluggableTrait;

    public const STATUS_UNDER_CONSTRUCTION= 'UNDER CONSTRUCTION';
    public const STATUS_PUBLISHED = 'PUBLISHED';
    public const STATUS_CANCELED = 'CANCELED';


    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(name="status", type="string", nullable=false)
     */
    private string $status = self::STATUS_UNDER_CONSTRUCTION;

    /**
     * @ORM\Column(name="nbComment", type="integer", nullable=false, options={"default":0})
     */
    private int $nbComment = 0;

    /**
     * @ORM\ManyToOne(targetEntity="ProjetNormandie\ArticleBundle\Entity\UserInterface", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idAuthor", referencedColumnName="id", nullable=false)
     * })
     */
    private $author;

    /**
     * @ORM\Column(name="published_at", type="datetime", nullable=true)
     */
    private ?DateTime $publishedAt = null;

    /**
     * @ORM\OneToMany(targetEntity="ProjetNormandie\ArticleBundle\Entity\Comment", mappedBy="article")
     */
    private Collection $comments;


    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s [%s]', $this->getDefaultTitle(), $this->id);
    }

    /**
     * @return string
     */
    public function getDefaultTitle(): string
    {
        return $this->translate('en', false)->getTitle();
    }

    /**
     * @return string
     */
    public function getDefaultText(): string
    {
        return $this->translate('en', false)->getText();
    }

    /**
     * @param integer $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return integer
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
        if ($this->status == self::STATUS_PUBLISHED AND $this->getPublishedAt() === null) {
            $this->setPublishedAt(new DateTime());
        }
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param integer $nbComment
     */
    public function setNbComment(int $nbComment): void
    {
        $this->nbComment = $nbComment;
    }

    /**
     * Get nbComment
     *
     * @return integer
     */
    public function getNbComment(): int
    {
        return $this->nbComment;
    }

    /**
     * @return UserInterface
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param $author
     */
    public function setAuthor($author): void
    {
        $this->author = $author;
    }

    /**
     * @return DateTime
     */
    public function getPublishedAt(): ?DateTime
    {
        return $this->publishedAt;
    }

    /**
     * @param DateTime|null $publishedAt
     */
    public function setPublishedAt(?DateTime $publishedAt = null)
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->translate(null, false)->setTitle($title);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->translate(null, false)->getTitle();
    }

    /**
     * @param string $text
     */
    public function setText(string $text)
    {
        $this->translate(null, false)->setText($text);
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->translate(null, false)->getText();
    }

    /**
     * @return mixed
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    /**
     * @return array
     */
    public static function getStatusChoices(): array
    {
        return [
            self::STATUS_UNDER_CONSTRUCTION => self::STATUS_UNDER_CONSTRUCTION,
            self::STATUS_PUBLISHED => self::STATUS_PUBLISHED,
            self::STATUS_CANCELED => self::STATUS_CANCELED
        ];
    }

    /**
     * Returns an array of the fields used to generate the slug.
     *
     * @return string[]
     */
    public function getSluggableFields(): array
    {
        return ['defaultTitle'];
    }
}
