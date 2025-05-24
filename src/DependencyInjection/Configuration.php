<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('projet_normandie_article');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->scalarNode('default_locale')
            ->defaultValue('en')
            ->info('Default locale for fallback translations')
            ->end()
            ->arrayNode('supported_locales')
            ->defaultValue(['en', 'fr'])
            ->prototype('scalar')->end()
            ->info('List of supported locales for translations')
            ->end()
            ->end();

        return $treeBuilder;
    }
}
