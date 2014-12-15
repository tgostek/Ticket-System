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
        $authController->match('/register', array($this, 'register'))->bind('/auth/register');
        return $authController;
    }

    public function login(Application $app, Request $request)
    {
        $data = array();

        $form = $app['form.factory']->createBuilder('form')
            ->add('username', 'text', array('label' => 'Username', 'data' => $app['session']->get('_security.last_username')))
            ->add('password', 'password', array('label' => 'Password'))
            ->add('login', 'submit')
            ->getForm();

        return $app['twig']->render('auth/login.twig', array(
            'form' => $form->createView(),
            'error' => $app['security.last_error']($request)
        ));
    }

    public function logout(Application $app, Request $request)
    {
        $app['session']->clear();
        return $app['twig']->render('auth/logout.twig');
    }

    public function register(Application $app, Request $request)
    {
        $data = array();

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add(
                'login', 'text', array(
                    'label' => 'Login',
                    'constraints' => array(
                        new Assert\NotBlank(), new Assert\Length(array('min' => 5))
                    ),
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'name', 'text', array(
                    'label' => 'Name',
                    'constraints' => array(
                        new Assert\NotBlank(), new Assert\Length(array('min' => 3))
                    ),
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'surname', 'text', array(
                    'label' => 'Surname',
                    'constraints' => array(
                        new Assert\NotBlank(), new Assert\Length(array('min' => 3))
                    ),
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'password', 'repeated', array(
                    'type' => 'password',
                    'first_options' => array(
                        'label' => 'Password',
                        'attr' => array('class' => 'form-control')
                    ),
                    'second_options' => array(
                        'label' => 'Repeat Password',
                        'attr' => array('class' => 'form-control')
                    ),
                    'constraints' => array(
                        new Assert\NotBlank(), new Assert\Length(array('min' => 5))
                    ),
                    'attr' => array('class' => 'form-control')
                )
            )
            ->add(
                'register', 'submit', array(
                    'label' => 'Register',
                    'attr' => array('class' => 'btn btn-default btn-lg')
                )
            )
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data['password'] = $app['security.encoder.digest']
                ->encodePassword($data['password'], '');

            $usersModel = new UsersModel($app);
            $res = $usersModel->addUser($data);
            if (!$res) {
                $app['session']->getFlashBag()->add(
                    'message', array(
                        'type' => 'error',
                        'content' => 'User with that login exists'
                    )
                );
                return $app->redirect(
                    $app['url_generator']->generate(
                        '/auth/register'
                    ),
                    301
                );
            } else {
                return $app->redirect(
                    $app['url_generator']->generate(
                        '/auth/login'
                    ),
                    301
                );
            }
        }

        return $app['twig']->render(
            'auth/register.twig', array('form' => $form->createView(),
                'error' => $app['security.last_error']($request))
        );
    }
}