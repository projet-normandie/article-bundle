<?php

namespace ProjetNormandie\ArticleBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ArticleController
 * @Route("/article")
 */
class ArticleController extends Controller
{
    /**
     * @Route("/rss", name="article_rss")
     * @Method("GET")
     * @Cache(smaxage="10")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function rssAction()
    {
        $articles = $this->getDoctrine()->getRepository('ProjetNormandieArticleBundle:Article')->findBy(
            array(
                'status' => 'PUBLISHED'
            ),
            array('publishedAt' => 'DESC'),
            20
        );

        $feed = $this->get('eko_feed.feed.manager')->get('article');

        // Add prefixe link
        foreach ($articles as $article) {
            if ($article->getLink() != null) {
                $article->setLink($feed->get('link') . $article->getLink());
            }
        }

        $feed->addFromArray($articles);

        return new Response($feed->render('rss'));
    }
}
