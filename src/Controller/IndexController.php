<?php

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UsersController
 *
 * @class UsersController
 * @package Controller
 * @author Tomasz Gostek <tomasz.gostek@uj.edu.pl>
 * @uses Silex\Application
 * @uses Silex\ControllerProviderInterface
 * @uses Symfony\Component\HttpFoundation\Request
 * @uses Symfony\Component\Validator\Constraints as Assert
 */
class IndexController implements ControllerProviderInterface
{
    /**
     * Controller connect
     *
     * @access public
     * @param Application $app
     */
    public function connect(Application $app)
    {
        $ticketsController = $app['controllers_factory'];
        $ticketsController->match('/', array($this, 'index'))->bind('/');
        return $ticketsController;
    }    
    
    /**
     * User index page - redirect to main page.
     *
     * @access public
     * @param Application $app
     */
    public function index(Application $app)
    {
        return $app->redirect(
            $app['url_generator']->generate('/tickets/'), 
            301
        );
    }

}
