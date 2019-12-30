<?php

namespace ProjetNormandie\ArticleBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController
 * @Route("/article")
 */
class ArticleController extends Controller
{
    /**
     * @Route("/rss", name="article_rss", methods={"GET"})
     * @Cache(smaxage="10")
     */
    public function rssAction(): Response
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
            if ($article->getLink() !== null) {
                $article->setLink($feed->get('link') . $article->getLink());
            }
        }

        $feed->addFromArray($articles);

        return new Response($feed->render('rss'));
    }
}
