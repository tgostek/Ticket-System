<?php
require_once __DIR__.'/../vendor/autoload.php';
$app = new Silex\Application();
$app['debug'] = true;
$app->get('/hello/{name}', function ($name) use ($app) {
    return 'Hello '.$app->escape($name);
});
$data = array(
    0 => array(
        'name' => 'John',
        'email' => 'john@example.com',
    ),
    1 => array(
        'name' => 'Mark',
        'email' => 'mark@example.com',
    ),
);

$app->get('/data', function () use ($data) {
    $view = '';
    foreach ($data as $row) {
        $view .= $row['name'];
        $view .= ' : ';
        $view .= $row['email'];
        $view .= '<br />';
    }
    return $view;
});
$app->run();