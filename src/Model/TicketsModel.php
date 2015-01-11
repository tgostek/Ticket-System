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

    public function addTicket($data)
    {
        $sql = 'INSERT INTO TICKET (TCK_CREATION_DATE, TCK_CLOSED_DATE, TCK_TITLE, TCK_DESC, USR_TCK_OWNER, USR_TCK_AUTHOR, STS_TCK_STATUS, PRT_TCK_PRIORITY, QUE_QUEUE) VALUES (?,?,?,?,?,?,?,?,?,?)';
        return $this->_db->executeQuery($sql, array(NOW(), NOW(), $data['title'], $data['description'], 1,1,1, $data['priority'], $data['queue']));
    }

}