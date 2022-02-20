<?php

namespace ProjetNormandie\ArticleBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Eko\FeedBundle\Item\Writer\ItemInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;
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
 * @method ArticleTranslation translate(string $locale, bool $fallbackToDefault)
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
    use TranslatableTrait;
    use SluggableTrait;

    public const STATUS_UNDER_CONSTRUCTION= 'UNDER CONSTRUCTION';
    public const STATUS_PUBLISHED = 'PUBLISHED';
    public const STATUS_CANCELED = 'CANCELED';


    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    /**
     * @ORM\Column(name="status", type="string", nullable=false)
     */
    private string $status = self::STATUS_UNDER_CONSTRUCTION;

    /**
     * @ORM\Column(name="nbComment", type="integer", nullable=false, options={"default":0})
     */
    private int $nbComment = 0;

    /**
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="ProjetNormandie\ArticleBundle\Entity\UserInterface", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idAuthor", referencedColumnName="id", nullable=false)
     * })
     */
    private $author;

    /**
     * @ORM\Column(name="published_at", type="datetime", nullable=true)
     */
    private DateTime $publishedAt;

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
     * Set id
     *
     * @param integer $id
     * @return $this
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return string
     */
    public function setStatus(string $status): string
    {
        $this->status = $status;
        if ($this->status == self::STATUS_PUBLISHED AND $this->getPublishedAt() === null) {
            $this->setPublishedAt(new DateTime());
        }
        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }


    /**
     * Set nbComment
     *
     * @param integer $nbComment
     * @return $this
     */
    public function setNbComment(int $nbComment): self
    {
        $this->nbComment = $nbComment;

        return $this;
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
     * Get author
     * @return UserInterface
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set author
     *
     * @param $author
     * @return $this
     */
    public function setAuthor($author): self
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Get publishedAt
     * @return DateTime
     */
    public function getPublishedAt(): DateTime
    {
        return $this->publishedAt;
    }

    /**
     * Set publishedAt
     *
     * @param DateTime|null $publishedAt
     * @return $this
     */
    public function setPublishedAt(DateTime $publishedAt = null): self
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->translate(null, false)->setTitle($title);

        return $this;
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
     * @return $this
     */
    public function setText(string $text): self
    {
        $this->translate(null, false)->setText($text);

        return $this;
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
