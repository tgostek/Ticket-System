<?php

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Model\UsersModel;

/**
 * Class AuthController
 *
 * @class AuthController
 * @package Controller
 * @author Tomasz Gostek <tomasz.gostek@uj.edu.pl>
 * @uses Silex\Application
 * @uses Silex\ControllerProviderInterface
 * @uses Symfony\Component\HttpFoundation\Request
 * @uses Symfony\Component\Validator\Constraints as Assert
 * @uses Model\UsersModel
 */
 
class AuthController implements ControllerProviderInterface
{
    /**
     * Controller connect
     *
     * @access public
     * @param Application $app
     */
    public function connect(Application $app)
    {
        $authController = $app['controllers_factory'];
        $authController->match(
            '/login', array($this, 'login')
        )->bind('/auth/login');
        $authController->match(
            '/logout', array($this, 'logout')
        )->bind('/auth/logout');
        $authController->match(
            '/register', array($this, 'register')
        )->bind('/auth/register');
        $authController->match(
            '/change/password',
            array($this, 'changePassword')
        )->bind('/auth/change/password');
        return $authController;
    }
    
    /**
     * User login 
     *
     * @access public
     * @param Application $app
     * @param Request $request
     */
    public function login(Application $app, Request $request)
    {
        $data = array();

        $form = $app['form.factory']->createBuilder('form')
            ->add(
                'username', 'text', array(
                'label' => 'Username',
                'data' => $app['session']->get('_security.last_username'),
                   'attr' => array('class'=>'form-control')
            )
            )
            ->add(
                'password', 'password', array(
                'label' => 'Password',
                   'attr' => array('class'=>'form-control')
            )
            )
            ->add(
                'login', 'submit', array(
                'label' => 'Zaloguj się',
                  'attr' => array('class'=>'btn btn-default btn-lg')
            )
            )
            ->getForm();

        return $app['twig']->render(
            'auth/login.twig', array(
            'form' => $form->createView(),
            'error' => $app['security.last_error']($request)
            )
        );
    }
    
    /**
     * User registration 
     *
     * @access public
     * @param Application $app
     * @param Request $request
     */
    public function register(Application $app, Request $request)
    {
        $data = array();

        $form = $app['form.factory']->createBuilder('form', $data)
             ->add(
                 'login', 'text', array(
                 'label' => 'Login',
                 'invalid_message' => 
                     'Login musi zawierać przynajmniej 5 znaków',
                 'constraints' => array(
                     new Assert\NotBlank(), new Assert\Length(array('min' => 5))
                 ),
                   'attr' => array('class'=>'form-control')
            )
             )
             ->add(
                 'name', 'text', array(
                 'label' => 'Imię',
                 'invalid_message' => 
                     'Imię musi zawierać przynajmniej 3 znaki',
                 'constraints' => array(
                     new Assert\NotBlank(), new Assert\Length(array('min' => 3))
                 ),
                 'attr' => array('class'=>'form-control')
            )
             )
             ->add(
                 'surname', 'text', array(
                 'label' => 'Nazwisko',
                 'invalid_message' => 
                     'Nazwisko musi zawierać przynajmniej 3 znaki',
                 'constraints' => array(
                     new Assert\NotBlank(), new Assert\Length(array('min' => 3))
                 ),
                 'attr' => array('class'=>'form-control')
            )
             )
             ->add(
                 'password', 'repeated', array(
                 'type' => 'password',
                 'invalid_message' => 'Hasła różnią się od siebie',
                 'first_options'  => array(
                    'label' => 'Password',
                       'attr' => array('class'=>'form-control')
                    ),
                 'second_options' => array(
                    'label' => 'Repeat Password',
                       'attr' => array('class'=>'form-control')
                    ),
                 'constraints' => array(
                     new Assert\NotBlank(), new Assert\Length(array('min' => 5))
                 ),
                 'attr' => array('class'=>'form-control')
            )
             )
             ->add(
                 'register', 'submit', array(
                 'label' => 'Zarejestruj się',
                  'attr' => array('class'=>'btn btn-default btn-lg')
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
            if ( !$res ) {
                $app['session']->getFlashBag()->add(
                    'message', array(
                        'type' => 'error',
                        'content' => 'Dany login istnieje już w naszej bazie'
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
    
    /**
     * Change password 
     *
     * @access public
     * @param Application $app
     * @param Request $request
     */
    public function changePassword(Application $app, Request $request)
    {
        $data = array();

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add(
                'password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'Hasła różnią się od siebie',
                'first_options'  => array(
                    'label' => 'Password',
                       'attr' => array('class'=>'form-control')
                    ),
                'second_options' => array(
                    'label' => 'Repeat Password',
                       'attr' => array('class'=>'form-control')
                    ),
                'constraints' => array(
                    new Assert\NotBlank(), new Assert\Length(array('min' => 5))
                ),
                   'attr' => array('class'=>'form-control')
            )
            )
            ->add(
                'Zmień hasło', 'submit', array(
                'attr' => array('class'=>'btn btn-default btn-lg')
              )
            )
            ->getForm();
            
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data['password'] = $app['security.encoder.digest']
                                ->encodePassword($data['password'], '');
            $id = $app['session']->get('user');
            $id = $id['id'];
            $usersModel = new UsersModel($app);        
            $usersModel->changePassword($data, $id);
            
            $app['session']->getFlashBag()->add(
                'message', array(
                    'type' => 'success', 
                    'content' => 'Hasło zostało zmienione'
                )
            );
            return $app
                   ->redirect(
                       $app['url_generator']->generate(
                           '/tickets/'
                       ), 
                       301
                   );
        }
        
        return $app['twig']
               ->render(
                   'auth/changePassword.twig', 
                   array('form' => $form->createView())
               );
    }
    
    /**
     * User logout 
     *
     * @access public
     * @param Application $app
     * @param Request $request
     */
    public function logout(Application $app, Request $request)
    {
        $app['session']->clear();
        return $app['twig']->render('auth/logout.twig');
    }

}