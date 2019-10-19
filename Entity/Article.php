<?php

namespace ProjetNormandie\ArticleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;
use Knp\DoctrineBehaviors\Model\Translatable\Translatable;
use ApiPlatform\Core\Annotation\ApiResource;

/**
 * Article
 *
 * @ORM\Table(name="article")
 * @ORM\Entity(repositoryClass="ProjetNormandie\ArticleBundle\Repository\ArticleRepository")
 * @method ArticleTranslation translate(string $locale, bool $fallbackToDefault)
 * @ApiResource(attributes={"order"={"id": "DESC"}})
 *
 */
class Article
{
    use Timestampable;
    use Translatable;

    const STATUS_UNDER_CONSTRUCTION= 'UNDER CONSTRUCTION';
    const STATUS_PUBLISHED = 'PUBLISHED';
    const STATUS_CANCELED = 'CANCELED';


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
     * @var UserInterface
     *
     * @ORM\ManyToOne(targetEntity="ProjetNormandie\ArticleBundle\Entity\UserInterface", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idAuthor", referencedColumnName="id", nullable=false)
     * })
     */
    private $author;

    /**
     * @var datetime
     *
     * @ORM\Column(name="published_at", type="datetime", nullable=true)
     */
    private $publishedAt;


    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s [%s]', $this->getTitle(), $this->id);
    }


    /**
     * Set id
     *
     * @param integer $id
     * @return $this
     */
    public function setId($id)
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
    public function setStatus($status)
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
     * @param UserInterface $author
     * @return $this
     */
    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Get publishedAt
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set publishedAt
     *
     * @param \DateTime $publishedAt
     * @return $this
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;
        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
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
    public function setText($text)
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
}
