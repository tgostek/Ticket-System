<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors','on');

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
        array('^/tickets/core.+$', 'ROLE_ADMIN')
    ),
    'security.role_hierarchy' => array(
        'ROLE_ADMIN' => array('ROLE_USER'),
    ),
    )
);

$app->register(
    new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallbacks' => array('pl_PL'),
    )
);

$app['translator.domains'] = array(
    'messages' => array(
        'pl' => array(
        'Bad credentials'     => 'Błędny login lub hasło',
        'This value is too short. It should have 3 characters or more.' => 'Pole musi zawierać przynajmniej 3 znaki',
        )
    ),
    'validators' => array(
        'pl' => array(
        'Bad credentials'     => 'Błędny login lub hasło',
        'This value is too short. It should have 3 characters or more.' => 'Pole musi zawierać przynajmniej 3 znaki',
        )
    ),
);
$app->mount('/auth/', new Controller\AuthController());

$app->mount('/tickets/', new Controller\TicketsController());
$app->mount('/', new Controller\IndexController());

$app->before(
    function ($request) use ($app) {
    $tmp = false;
    $user = $app['session']->get('user');
    
    if (!empty($user)) {
        $roles = $user['roles'];
        foreach ($roles as $role) {
            if ($role == 'ROLE_ADMIN') {
                $tmp = true;
            }
        }
    }
    $app["twig"]->addGlobal("isAdmin", $tmp);
    }
);

$app->run();