<?php

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Model\UsersModel;

class AuthController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $authController = $app['controllers_factory'];
        $authController->match('/login', array($this, 'login'))->bind('/auth/login');
        $authController->match('/logout', array($this, 'logout'))->bind('/auth/logout');
        return $authController;
    }

    public function login(Application $app, Request $request)
    {
        $data = array();

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add('login', 'text', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
            ))
            ->add('password', 'password', array(
                'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 5)))
            ))
            ->add('Enter', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $usersModel = new UsersModel($app);
            $user = $usersModel->login($form->getData());

            if (count($user)) {
                $app['session']->set('user', $user);
                return $app->redirect($app['url_generator']->generate('/albums/'), 301);
            }
        }

        return $app['twig']->render('auth/login.twig', array('form' => $form->createView()));
    }

    public function logout(Application $app, Request $request)
    {
        if (($user = $app['session']->get('user')) !== null) {
            $app['session']->remove('user');
        }

        return $app['twig']->render('auth/logout.twig');
    }
}