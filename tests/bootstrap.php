<?php

use ProjetNormandie\ArticleBundle\Tests\TestKernel;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Variables d'environnement pour les tests
$_ENV['KERNEL_CLASS'] = TestKernel::class;
$_ENV['APP_ENV'] = 'test';
$_ENV['DATABASE_URL'] = 'sqlite:///:memory:';

// Clear test cache if needed
if (isset($_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'])) {
    $kernel = new TestKernel($_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'], true);
    $kernel->boot();

    $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);
    $application->setAutoExit(false);

    // Clear cache
    $input = new \Symfony\Component\Console\Input\ArrayInput([
        'command' => 'cache:clear',
        '--no-warmup' => true,
    ]);
    $application->run($input);

    $kernel->shutdown();
}
