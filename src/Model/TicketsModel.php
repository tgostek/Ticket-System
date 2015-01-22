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

    public function getTicket($id)
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
              INNER JOIN
                  STATUS ON TICKET.STS_TCK_STATUS = STATUS.STS_ID
              WHERE
                  TCK_ID = ?
              ';

        $res = $this->_db->fetchAll($sql, array((string) $id));
        if (empty($res)) {
            throw new TicketException('Ticket doesn\'t exist');
        }
        return $res;
    }

    public function getTicketAttachment($id)
    {
        $sql = '
        SELECT
            *
        FROM
            ATTACHMENT
        INNER JOIN
            TICKET_has_ATTACHMENT ON TICKET_has_ATTACHMENT.TCK_TICKET = ?
        WHERE
            ATTACHMENT.ATT_ID = TICKET_has_ATTACHMENT.ATT_ATTACHMENT
        ';

        $res = $this->_db->fetchAll($sql, array((string) $id));
        if (empty($res)) {
            throw new TicketException('Ticket has no attachment');
        }
        return $res;
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

            $sql = 'INSERT INTO TICKET (TCK_CREATION_DATE, TCK_CLOSED_DATE, TCK_TITLE, TCK_DESC, USR_TCK_AUTHOR, STS_TCK_STATUS, PRT_TCK_PRIORITY, QUE_QUEUE) VALUES (?,?,?,?,?,?,?,?)';
            $this->_db->executeQuery($sql, array($date, NULL, $data['title'], $data['desc'], $userId, 1, $data['priority'], $data['queue']));

            return $this->_db->lastInsertId();
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

    public function getPriorities()
    {
        $sql = 'SELECT
                    *
                FROM
                    PRIORITY';

        return $this->_db->fetchAll($sql);
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

    public function getQueues()
    {
        $sql = 'SELECT
                    *
                FROM
                    QUEUE';

        return $this->_db->fetchAll($sql);
    }

    public function getPossibleStatuses()
    {
        $sql = 'SELECT
                    *
                FROM
                    STATUS';

        $res = $this->_db->fetchAll($sql);

        $statuses = array();

        foreach ($res as $status) {
            $statuses[$status['STS_ID']] = $status['STS_VALUE'];
        }
        return $statuses;
    }

    public function getStatuses()
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
            $sql = 'INSERT INTO STATUS (STS_VALUE, STS_IS_CLOSED) VALUES (?,?)';
            return $this->_db->executeQuery($sql, array($data['value'], $data['isClosed']));
        }
    }

    public function addPriority($data)
    {
        if(empty($data)) {
            throw new TicketException();
        }else{
            $sql = 'INSERT INTO PRIORITY (PRT_VALUE) VALUES (?)';
            return $this->_db->executeQuery($sql, array($data['value']));
        }
    }

    public function addQueue($data)
    {
        if(empty($data)) {
            throw new TicketException();
        }else{
            $sql = 'INSERT INTO QUEUE (QUE_NAME) VALUES (?)';
            return $this->_db->executeQuery($sql, array($data['name']));
        }
    }

    public function addComment($data, $idUser, $idTicket)
    {
        $date = date('Y-m-d H:i:s');
        $sql = 'INSERT INTO COMMENT
                (CMT_VALUE, CMT_CREATION_DATE, TCK_CMT_TICKET, USR_CMT_AUTHOR)
                VALUES (?,?,?,?)';
        $this->_db->executeQuery(
            $sql, array($data['comment'], $date, $idTicket, $idUser)
        );
    }

    public function getComments($idTicket)
    {
        $sql = 'SELECT
                    CMT_CREATION_DATE, CMT_VALUE,
                    USER_ID, USER_NAME, USER_SURNAME
                FROM
                    COMMENT, USER
                WHERE
                    USR_CMT_AUTHOR = USER_ID AND TCK_CMT_TICKET = ?';
        return $this->_db->fetchAll($sql, array((int)$idTicket));
    }


    public function changeTicketStatus($data){
        if(empty($data)) {
            throw new TicketException();
        }else{
            $sql = "UPDATE TICKET SET STS_TCK_STATUS = ? WHERE TCK_ID = ?";
            return $this->_db->executeQuery($sql, array($data['status'], $data['id']));
        }
    }

    public function changeTicketQueue($idTicket, $newQueue, $oldQueue, $idUser)
    {
        $sql = "UPDATE TICKET SET QUE_QUEUE = ? WHERE TCK_ID = ?";
        return $this->_db->executeQuery($sql, array($newQueue, $idTicket));
    }
}