<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\DependencyInjection;

use ProjetNormandie\ArticleBundle\DependencyInjection\Configuration;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Exception;

class ProjetNormandieArticleExtension extends Extension
{
    /**
     * @param array<mixed> $configs
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('projet_normandie_article.default_locale', $config['default_locale']);
        $container->setParameter('projet_normandie_article.supported_locales', $config['supported_locales']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yml');
        $bundles = $container->getParameter('kernel.bundles');
        if (array_key_exists('SonataAdminBundle', $bundles)) {
            $loader->load('sonata_admin.yml');
        }
    }
}
