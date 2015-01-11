<?php

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

use Model\TicketsModel;
use Model\QueueDoesntExistException;
/**
 * Class TicketsController
 *
 * @class TicketsController
 * @package Controller
 * @author Tomasz Gostek <tomasz.gostek@uj.edu.pl>
 * @uses Silex\Application
 * @uses Silex\ControllerProviderInterface
 * @uses Symfony\Component\HttpFoundation\Request
 * @uses Symfony\Component\Validator\Constraints as Assert
 * @uses Model\TicketsModel
 */
 
class TicketsController implements ControllerProviderInterface
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
        $ticketsController->match(
            '/', array($this, 'index')
        )->bind(
            '/tickets/'
        );

        return $ticketsController;
    }    
    
    /**
     * Tickets index page.
     *
     * @access public
     * @param Application $app
     */
    public function index(Application $app)
    {
        return $app['twig']->render(
            'tickets/index.twig'
        );
    }

}
