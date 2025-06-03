<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Tests;

use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use ProjetNormandie\ArticleBundle\ProjetNormandieArticleBundle;
use Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/**
 * Kernel spécifique pour les tests
 * Configure un environnement minimal avec les bundles nécessaires
 */
class TestKernel extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(), // Requis par API Platform
            new DoctrineBundle(),
            new StofDoctrineExtensionsBundle(), // Pour Gedmo (timestampable, sluggable)
            new SecurityBundle(),
            new ApiPlatformBundle(), // API Platform pour les tests d'API
            new ProjetNormandieArticleBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) {
            // Configuration Framework
            $container->loadFromExtension('framework', [
                'test' => true,
                'secret' => 'test-secret',
                'router' => [
                    'utf8' => true,
                    'strict_requirements' => null,
                    'resource' => '%kernel.project_dir%/config/routes_test.yaml',
                ],
                'session' => ['storage_factory_id' => 'session.storage.factory.mock_file'],
                'http_method_override' => true,
                'php_errors' => ['log' => true],
                'serializer' => ['enabled' => true],
                'property_access' => ['enabled' => true],
                'validation' => ['enabled' => true],
                'annotations' => false,
            ]);

            // Configuration Twig (requis par API Platform)
            $container->loadFromExtension('twig', [
                'default_path' => '%kernel.project_dir%/templates',
                'exception_controller' => null,
                'strict_variables' => false, // Plus permissif pour les tests
            ]);

            // Configuration API Platform
            $container->loadFromExtension('api_platform', [
                'title' => 'Test API',
                'version' => '1.0.0',
                'mapping' => [
                    'paths' => [
                        '%kernel.project_dir%/src/Entity',
                        '%kernel.project_dir%/tests/Fixtures',
                    ],
                ],
                'formats' => [
                    'jsonld' => ['application/ld+json'],
                    'json' => ['application/json'],
                ],
                'docs_formats' => [
                    'jsonld' => ['application/ld+json'],
                    'json' => ['application/json'],
                ],
                'patch_formats' => [
                    'json' => ['application/merge-patch+json'],
                ],
                'swagger' => [
                    'versions' => [3],
                ],
                // Désactiver les routes de documentation pour les tests
                'enable_docs' => false,
                'enable_entrypoint' => true,
                // Désactiver la sécurité complexe pour les tests
                'defaults' => [
                    'security' => null,
                ],
            ]);

            // Configuration Doctrine
            $container->loadFromExtension('doctrine', [
                'dbal' => [
                    'url' => 'sqlite:///:memory:',
                    'driver' => 'pdo_sqlite',
                ],
                'orm' => [
                    'auto_generate_proxy_classes' => true,
                    'resolve_target_entities' => [
                        'ProjetNormandie\ArticleBundle\Entity\UserInterface' => 'ProjetNormandie\ArticleBundle\Tests\Fixtures\User'
                    ],
                    'mappings' => [
                        'ProjetNormandieArticleBundle' => [
                            'type' => 'attribute',
                            'dir' => __DIR__ . '/../src/Entity',
                            'prefix' => 'ProjetNormandie\ArticleBundle\Entity',
                        ],
                        'ProjetNormandieArticleBundleTestFixtures' => [
                            'type' => 'attribute',
                            'dir' => __DIR__ . '/Fixtures',
                            'prefix' => 'ProjetNormandie\ArticleBundle\Tests\Fixtures',
                            'is_bundle' => false,
                        ],
                    ],
                ],
            ]);

            // Configuration Security
            $container->loadFromExtension('security', [
                'password_hashers' => [
                    'ProjetNormandie\ArticleBundle\Tests\Fixtures\User' => 'plaintext',
                ],
                'providers' => [
                    'test_provider' => [
                        'memory' => [
                            'users' => [
                                'testuser' => ['password' => 'test', 'roles' => ['ROLE_USER']],
                                'admin' => ['password' => 'admin', 'roles' => ['ROLE_ADMIN']],
                            ],
                        ],
                    ],
                ],
                'firewalls' => [
                    'dev' => [
                        'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                        'security' => false,
                    ],
                    'api' => [
                        'pattern' => '^/api/',
                        'stateless' => true,
                        'provider' => 'test_provider',
                        'http_basic' => true,
                    ],
                    'main' => [
                        'lazy' => true,
                        'provider' => 'test_provider',
                    ],
                ],
                'access_control' => [
                    ['path' => '^/api/docs', 'roles' => 'PUBLIC_ACCESS'],
                    ['path' => '^/api/', 'roles' => 'PUBLIC_ACCESS'],
                ],
            ]);

            // Configuration du bundle Article
            $container->loadFromExtension('projet_normandie_article', [
                'default_locale' => 'en',
                'supported_locales' => ['en', 'fr'],
            ]);

            // Configuration StofDoctrineExtensions (Gedmo)
            $container->loadFromExtension('stof_doctrine_extensions', [
                'default_locale' => 'en_US',
                'orm' => [
                    'default' => [
                        'timestampable' => true,
                        'sluggable' => true,
                    ],
                ],
            ]);

            // Services de test
            $container->register('logger', \Psr\Log\NullLogger::class);
        });
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/projet_normandie_article_bundle_test/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/projet_normandie_article_bundle_test/logs';
    }
}
