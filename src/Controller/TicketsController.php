<?php

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

use Model\TicketsModel;
use Model\UsersModel;
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

        $ticketsController->match(
            '/add', array($this, 'add')
        )->bind(
            '/tickets/add'
        );

        $ticketsController->match(
            '/core', array($this, 'core')
        )->bind(
            '/tickets/core'
        );


        $ticketsController->match(
            '/view/{id}', array($this, 'view')
        )->bind(
            '/tickets/view/'
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
        $ticketsModel = new TicketsModel($app);
        $allTickets = $ticketsModel->getAllTickets();
        
        return $app['twig']->render(
            'tickets/index.twig',
            array('allTickets' => $allTickets)
            );
    }

    /**
     * Add new ticket.
     *
     * @access public
     * @param Application $app
     * @param Request $request
     */
    public function add(Application $app, Request $request)
    {
        // default values:
        $data = array();

        $ticketsModel = new TicketsModel($app);
        try {
            $priorities = $ticketsModel->getPossiblePriorities();
            $queues = $ticketsModel->getPossibleQueues();
        } catch (Exception $e) {
            $app['session']
                ->getFlashBag()
                ->add(
                    'message',
                    array(
                        'type' => 'error',
                        'content' => 'Ups.. Something is wrong.'
                    )
                );
            return $app->redirect(
                $app['url_generator']->generate('/tickets/'),
                301
            );
        }

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add(
                'title', 'text', array(
                    'label' => 'Title',
                    'attr' => array('class'=>'form-control'),
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Length(array('min' => 5))
                    )
                )
            )
            ->add(
                'desc', 'textarea', array(
                    'label' => 'Description',
                    'attr' => array('class'=>'form-control'),
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Length(array('min' => 10))
                    )
                )
            )
            ->add(
                'priority', 'choice', array(
                    'label' => 'Priority',
                    'choices' => $priorities,
                    'attr' => array('class'=>'form-control'),
                    'constraints' => array(new Assert\NotBlank())
                )
            )
            ->add(
                'queue', 'choice', array(
                    'label' => 'Queue',
                    'choices' => $queues,
                    'attr' => array('class'=>'form-control'),
                    'constraints' => array(new Assert\NotBlank())
                )
            )
            ->add(
                'Create', 'submit', array(
                    'attr' => array('class'=>'btn btn-default btn-lg')
                )
            )
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            try {
                $userId = $app['session']->get('user');
                $userId = $userId['id'];
                $ticketsModel->addTicket($data, $userId);
            } catch (Exception $e) {
                $app['session']
                    ->getFlashBag()
                    ->add(
                        'message',
                        array(
                            'type' => 'error',
                            'content' => 'Ups.. Something is wrong.'
                        )
                    );
                return $app->redirect(
                    $app['url_generator']->generate('/tickets/'),
                    301
                );
            }
            $app['session']
                ->getFlashBag()
                ->add(
                    'message',
                    array(
                        'type' => 'success',
                        'content' => 'Created new ticket'
                    )
                );

            return $app->redirect(
                $app['url_generator']->generate('/tickets/'),
                301
            );
        }

        return $app['twig']->render(
            'tickets/add.twig',
            array('form' => $form->createView())
        );
    }

    public function view(Application $app, Request $request) {
        $ticketsModel = new TicketsModel($app);
        $id = (int) $request->get('id', 0);

        $ticket = $ticketsModel->getTicket($id);

        return $app['twig']->render(
            'tickets/view.twig',
            array('ticket' => $ticket[0])
        );
    }

    public function core(Application $app, Request $request) {
        $ticketsModel = new TicketsModel($app);
        $usersModel = new UsersModel($app);

        $id = $app['session']->get('user');
        $id = $id['id'];

        if($usersModel->getUserRole($id) != 'ROLE_ADMIN') {
            $app['session']
                ->getFlashBag()
                ->add(
                    'message',
                    array(
                        'type' => 'error',
                        'content' => 'Access denied'
                    )
                );

            return $app->redirect(
                $app['url_generator']->generate('/tickets/'),
                301
            );
        }


        $statuses = $ticketsModel->getPossibleStatuses();

        /*$statusForm = $app['form.factory']->createBuilder('form', $data)
            ->add(
                'value', 'text', array(
                    'label' => 'Value',
                    'attr' => array('class'=>'form-control'),
                    'constraints' => array(
                        new Assert\NotBlank(),
                        new Assert\Length(array('min' => 3))
                    )
                )
            )
            ->add(
                'Create', 'submit', array(
                    'attr' => array('class'=>'btn btn-default btn-lg')
                )
            )
            ->getForm();

        $ticketsModel->addStatus($data);*/

        return $app['twig']->render(
            'tickets/core.twig',
            array('statuses' => $statuses)
        );
    }
}
