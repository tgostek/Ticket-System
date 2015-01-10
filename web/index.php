<?php
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;
$app->before(
    function ($request) {
    $request->getSession()->start();
    }
);
$app->register(
    new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../src/views',
    )
);

$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(
    new Silex\Provider\TranslationServiceProvider(), array(
    'translator.domains' => array(),
    )
);

$app->register(
    new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'    => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => 'TICKET_SYSTEM',
        'user'      => 'root',
        'password'  => 'root',
        'charset'   => 'utf8',
    ),
    )
);

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());


$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(
    new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'admin' => array(
            'pattern' => '^.*$',
            'form' => array(
                'login_path' => '/auth/login',
                'check_path' => '/tickets/login_check',
                'default_target_path'=> '/tickets/',
                'username_parameter' => 'form[username]',
                'password_parameter' => 'form[password]',
            ),
            'logout'  => true,
            'anonymous' => true,
            'logout' => array('logout_path' => '/auth/logout'),
            'users' => $app->share(
                function() use ($app) {
                    return new User\UserProvider($app);
                }
            ),
        ),
    ),
    'security.access_rules' => array(
        array('^/auth.+$', 'IS_AUTHENTICATED_ANONYMOUSLY'),
        array('^/.+$', 'ROLE_USER'),
        array('^/.+$', 'ROLE_ADMIN')
    ),
    'security.role_hierarchy' => array(
        'ROLE_ADMIN' => array('ROLE_USER'),
    ),
    )
);



$app->mount('/auth/', new Controller\AuthController());

$app->mount('/tickets/', new Controller\TicketsController());
$app->mount('/', new Controller\IndexController());

$app->run();