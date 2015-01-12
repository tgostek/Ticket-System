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
            '/core/addStatus', array($this, 'addStatus')
        )->bind(
            '/tickets/core/addStatus'
        );

        $ticketsController->match(
            '/core/addPriority', array($this, 'addPriority')
        )->bind(
            '/tickets/core/addPriority'
        );

        $ticketsController->match(
            '/core/addQueue', array($this, 'addQueue')
        )->bind(
            '/tickets/core/addQueue'
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


        $statuses = $ticketsModel->getStatuses();
        $priorities = $ticketsModel->getPriorities();
        $queues = $ticketsModel->getQueues();

        return $app['twig']->render(
            'tickets/core.twig',
            array('statuses' => $statuses, 'priorities' => $priorities, 'queues' => $queues)
        );
    }

    public function addStatus(Application $app, Request $request)
    {
        $ticketsModel = new TicketsModel($app);

        $statusData = array();
        $statusForm = $app['form.factory']->createBuilder('form', $statusData)
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
                'isClosed', 'checkbox', array(
                    'label' => 'Is Closed',
                    'attr' => array('class'=>'form-control'),
                    'required'  => false
                )
            )
            ->add(
                'Create', 'submit', array(
                    'attr' => array('class'=>'btn btn-default btn-lg')
                )
            )
            ->getForm();

        $statusForm->handleRequest($request);

        if ($statusForm->isValid()) {
            $statusData = $statusForm->getData();
            try {
                $ticketsModel->addStatus($statusData);
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
                        'content' => 'Created new status'
                    )
                );

            return $app->redirect(
                $app['url_generator']->generate('/tickets/core'),
                301
            );
        }

        return $app['twig']->render(
            'tickets/addStatus.twig',
            array('statusForm' => $statusForm->createView())
        );
    }

    public function addPriority(Application $app, Request $request)
    {
        $ticketsModel = new TicketsModel($app);

        $priorityData = array();
        $priorityForm = $app['form.factory']->createBuilder('form', $priorityData)
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

        $priorityForm->handleRequest($request);

        if ($priorityForm->isValid()) {
            $priorityData = $priorityForm->getData();
            try {
                $ticketsModel->addPriority($priorityData);
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
                        'content' => 'Created new priority'
                    )
                );

            return $app->redirect(
                $app['url_generator']->generate('/tickets/core'),
                301
            );
        }

        return $app['twig']->render(
            'tickets/addPriority.twig',
            array('priorityForm' => $priorityForm->createView())
        );
    }

    public function addQueue(Application $app, Request $request)
    {
        $ticketsModel = new TicketsModel($app);

        $queueData = array();
        $queueForm = $app['form.factory']->createBuilder('form', $queueData)
            ->add(
                'name', 'text', array(
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

        $queueForm->handleRequest($request);

        if ($queueForm->isValid()) {
            $queueData = $queueForm->getData();
            try {
                $ticketsModel->addQueue($queueData);
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
                        'content' => 'Created new queue'
                    )
                );

            return $app->redirect(
                $app['url_generator']->generate('/tickets/core'),
                301
            );
        }

        return $app['twig']->render(
            'tickets/addQueue.twig',
            array('queueForm' => $queueForm->createView())
        );
    }
}
