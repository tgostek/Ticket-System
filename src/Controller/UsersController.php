<?php

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

use Model\UsersModel;

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
 * @uses Model\UsersModel
 */
class UsersController implements ControllerProviderInterface
{
    /**
     * Controller connect
     *
     * @access public
     * @param Application $app
     */
    public function connect(Application $app)
    {
        $usersController = $app['controllers_factory'];
        $usersController->match(
            '/', array($this, 'index')
        )->bind(
            '/users/'
        );
        $usersController->match(
            '/show/{id}', array($this, 'show')
        )->bind(
            '/users/show/'
        );
        return $usersController;
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
            $app['url_generator']->generate(
                '/tickets/'
            ), 
            301
        );
    }
    
    /**
     * Show single user data.
     *
     * @access public
     * @param Application $app
     * @param Request $request
     */
    public function show(Application $app, Request $request)
    {
        $usersModel = new UsersModel($app);
        $id = (int) $request->get('id', 0);
        
        if (!$usersModel->checkUserExist($id)) {
            $app['session']
            ->getFlashBag()
            ->add(
                'message', 
                array(
                    'type' => 'error',
                     'content' => 'Użytkownik nie istnieje'
                )
            );
            return $app->redirect(
                $app['url_generator']->generate(
                    '/tickets/'
                ), 
                301
            );
        }
        $userData = $usersModel->getUserDataById($id);
        return $app['twig']->render(
            'users/show.twig', array('userData' => $userData)
        );
    }
}
