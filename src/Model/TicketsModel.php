<?php
namespace Model;

use Silex\Application;

class QueueDoesntExistException extends \Exception 
{
}

class TicketException extends \Exception
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

    public function getAllTickets()
    {
        $sql = '
              SELECT
                  *
              FROM
                  TICKET
              INNER JOIN
                  QUEUE ON TICKET.QUE_QUEUE = QUEUE.QUE_ID
              INNER JOIN
                  PRIORITY ON TICKET.PRT_TCK_PRIORITY = PRIORITY.PRT_ID
              ';

        $res = $this->_db->fetchAll($sql);

        return $res;
    }

    public function getAuthorsTickets($authorId)
    {
        $sql = '
            SELECT
            	*
            FROM
            	TICKET
            WHERE
                USR_TCK_AUTHOR = ?
            ';

        return $this->_db->fetchAll($sql, array((string) $authorId));
    }

    public function getOwnersTickets($ownerId)
    {
        $sql = '
            SELECT
            	*
            FROM
            	TICKET
            WHERE
                USR_TCK_OWNER = ?
            ';

        return $this->_db->fetchAll($sql, array((string) $ownerId));
    }

    public function addTicket($data, $userId)
    {
        if(empty($data)) {
            throw new TicketException();
        }else{
            $date = date('Y-m-d H:i:s');

            $sql = 'INSERT INTO TICKET (TCK_CREATION_DATE, TCK_CLOSED_DATE, TCK_TITLE, TCK_DESC, USR_TCK_OWNER, USR_TCK_AUTHOR, STS_TCK_STATUS, PRT_TCK_PRIORITY, QUE_QUEUE) VALUES (?,?,?,?,?,?,?,?,?)';
            return $this->_db->executeQuery($sql, array($date, NULL, $data['title'], $data['desc'], $userId, $userId, 1, $data['priority'], $data['queue']));
        }
    }

    public function changeTicketStatus($data){
        if(empty($data)) {
            throw new TicketException();
        }else{
            $sql = "UPDATE TICKET SET STS_TCK_STATUS = ? WHERE TCK_ID = ?";
            return $this->_db->executeQuery($sql, array($data['status'], $data['id']));
        }
    }

/**
* Get possible priorities.
*
* @access public
*/
    public function getPossiblePriorities()
    {
        $sql = 'SELECT
                    *
                FROM
                    PRIORITY';

        $res = $this->_db->fetchAll($sql);

        $priorities = array();

        foreach ($res as $priority) {
            $priorities[$priority['PRT_ID']] = $priority['PRT_VALUE'];
        }
        return $priorities;
    }

    /**
     * Get possible queues.
     *
     * @access public
     */
    public function getPossibleQueues()
    {
        $sql = 'SELECT
                    *
                FROM
                    QUEUE';

        $res = $this->_db->fetchAll($sql);

        $queues = array();

        foreach ($res as $queue) {
            $queues[$queue['QUE_ID']] = $queue['QUE_NAME'];
        }
        return $queues;
    }

    public function getPossibleStatuses()
    {
        $sql = 'SELECT
                    *
                FROM
                    STATUS';

        return $this->_db->fetchAll($sql);
    }

    public function addStatus($data)
    {
        if(empty($data)) {
            throw new TicketException();
        }else{
            $date = date('Y-m-d H:i:s');

            $sql = 'INSERT INTO STATUS (STS_VALUE, STS_IS_CLOSED) VALUES (?,?)';
            return $this->_db->executeQuery($sql, array($data['value'], $data['isClosed']));
        }
    }
}