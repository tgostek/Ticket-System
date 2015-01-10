<?php
namespace Model;

use Silex\Application;

class QueueDoesntExistException extends \Exception 
{
}
/**
 * Class TicketsModel
 *
 * @class TicketsModel
 * @package Model
 * @author Tomasz Gostek <tomasz.gostek@uj.edu.pl>
 * @uses Silex\Application
 */
class TicketsModel
{
    
    /**
     * Database access object.
     *
     * @access protected
     * @var $_db Doctrine\DBAL
     */
    protected $_db;
    
    /**
     * Class constructor.
     *
     * @access public
     * @param Appliction $app Silex application object
     */
    public function __construct(Application $app)
    {
        $this->_db = $app['db'];
    }
}