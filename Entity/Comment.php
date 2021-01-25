<?php

namespace ProjetNormandie\ArticleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Comment
 * @ORM\Table(name="article_comment")
 * @ORM\Entity(repositoryClass="ProjetNormandie\ArticleBundle\Repository\CommentRepository")
 * @ApiResource(attributes={"order"={"id"}})
 */
class Comment implements TimestampableInterface
{
    use TimestampableTrait;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Article
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="ProjetNormandie\ArticleBundle\Entity\Article", inversedBy="comments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idArticle", referencedColumnName="id", nullable=false)
     * })
     */
    private $article;

    /**
     * @var UserInterface
     * @Assert\NotNull
     * @ORM\ManyToOne(targetEntity="ProjetNormandie\ArticleBundle\Entity\UserInterface", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idUser", referencedColumnName="id", nullable=false)
     * })
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=false)
     */
    private $text;

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('comment [%s]', $this->id);
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
     * Get article
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * Set article
     *
     * @param Article $article
     * @return $this
     */
    public function setArticle(Article $article)
    {
        $this->article = $article;
        return $this;
    }

    /**
     * Get user
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param string $text
     * @return $this
     */
    public function setText(string $text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }
}
