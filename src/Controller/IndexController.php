<?php

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

use Model\TicketsModel;

class IndexController implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $indexController = $app['controllers_factory'];
        $indexController->get('/', array($this, 'index'));
        $indexController->match('/add', array($this, 'add'));
        return $indexController;
    }

    public function index(Application $app)
    {
        return 'Tu powinna być strona główna';
    }

    public function add(Application $app, Request $request)
    {

        // default values:
        $data = array(
        );

        $ticketModel = new TicketsModel($app);

        $priorities = $ticketModel->getPriorities();
        $queue = $ticketModel->getQueue();

        $form = $app['form.factory']->createBuilder('form', $data)
            ->add('title', 'text', array(
                'label' => 'Tytuł',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 5))
                )
            ))

            ->add('description', 'textarea', array(
                'label' => 'Opis',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 5))
                )))

            ->add('priority', 'choice', array(
                'label' => 'Priorytet',
                'choices' => $priorities,
                'constraints' => array(new Assert\NotBlank()),
            ))

            ->add('queue', 'choice', array(
                'label' => 'Kolejka',
                'choices' => $queue,
                'constraints' => array(new Assert\NotBlank()),
            ))

            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            print_r($ticketModel->addTicket($data));
            die();
        }

        return $app['twig']->render('index/add.twig', array('form' => $form->createView()));
    }
}