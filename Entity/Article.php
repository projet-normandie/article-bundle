<?php

namespace ProjetNormandie\ArticleBundle\Entity;

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
class Article implements ItemInterface, SluggableInterface, TimestampableInterface, TranslatableInterface
{
    use TimestampableTrait;
    use TranslatableTrait;
    use SluggableTrait;

    public const STATUS_UNDER_CONSTRUCTION= 'UNDER CONSTRUCTION';
    public const STATUS_PUBLISHED = 'PUBLISHED';
    public const STATUS_CANCELED = 'CANCELED';


    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", nullable=false)
     */
    private $status = self::STATUS_UNDER_CONSTRUCTION;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", nullable=true)
     */
    private $link;

    /**
     * @var integer
     *
     * @ORM\Column(name="nbComment", type="integer", nullable=false, options={"default":0})
     */
    private $nbComment = 0;

    /**
     * @var UserInterface
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="ProjetNormandie\ArticleBundle\Entity\UserInterface", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idAuthor", referencedColumnName="id", nullable=false)
     * })
     */
    private $author;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="published_at", type="datetime", nullable=true)
     */
    private $publishedAt;

    /**
     * @ORM\OneToMany(targetEntity="ProjetNormandie\ArticleBundle\Entity\Comment", mappedBy="article")
     */
    private $comments;


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
    public function getDefaultTitle()
    {
        return $this->translate('en', false)->getTitle();
    }

    /**
     * @return string
     */
    public function getDefaultText()
    {
        return $this->translate('en', false)->getText();
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return string
     */
    public function setStatus(string $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return string
     */
    public function setLink(string $link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set nbComment
     *
     * @param integer $nbComment
     * @return $this
     */
    public function setNbComment(int $nbComment)
    {
        $this->nbComment = $nbComment;

        return $this;
    }

    /**
     * Get nbComment
     *
     * @return integer
     */
    public function getNbComment()
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
    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Get publishedAt
     * @return DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set publishedAt
     *
     * @param DateTime $publishedAt
     * @return $this
     */
    public function setPublishedAt(DateTime $publishedAt)
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title)
    {
        $this->translate(null, false)->setTitle($title);

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->translate(null, false)->getTitle();
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setText(string $text)
    {
        $this->translate(null, false)->setText($text);

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->translate(null, false)->getText();
    }

    /**
     * @return mixed
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @return array
     */
    public static function getStatusChoices()
    {
        return [
            self::STATUS_UNDER_CONSTRUCTION => self::STATUS_UNDER_CONSTRUCTION,
            self::STATUS_PUBLISHED => self::STATUS_PUBLISHED,
            self::STATUS_CANCELED => self::STATUS_CANCELED
        ];
    }

    /**
     * @return string
     */
    public function getFeedItemTitle()
    {
        return $this->getTitle();
    }

    /**
     * @return string
     */
    public function getFeedItemDescription()
    {
        return $this->getText();
    }

    /**
     * @return DateTime
     */
    public function getFeedItemPubDate()
    {
        return $this->getPublishedAt();
    }

    /**
     * @return string
     */
    public function getFeedItemLink()
    {
        return $this->getLink();
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
