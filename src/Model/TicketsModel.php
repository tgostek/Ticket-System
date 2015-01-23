<?php
namespace Model;

use Silex\Application;
use Model\UsersModel;

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

    protected $_usersModel;
    /**
     * Class constructor.
     *
     * @access public
     * @param Appliction $app Silex application object
     */
    public function __construct(Application $app)
    {
        $this->_db = $app['db'];
        $this->_usersModel = new UsersModel($app);
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

    public function getAllTickets($limit = null)
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
              WHERE
                  USR_TCK_OWNER IS NULL
              ';
        if (!empty($limit)) {
            $sql .= 'LIMIT ' . $limit;
        }
        $res = $this->_db->fetchAll($sql);

        return $res;
    }

    public function getAuthorsTickets($authorId, $limit = null)
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
            WHERE
                USR_TCK_AUTHOR = ?
            ';
        if (!empty($limit)) {
            $sql .= 'LIMIT ' . $limit;
        }
        return $this->_db->fetchAll($sql, array((string) $authorId));
    }

    public function getOwnersTickets($ownerId, $limit = null)
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
            WHERE
                USR_TCK_OWNER = ?
            ';
        if (!empty($limit)) {
            $sql .= 'LIMIT ' . $limit;
        }
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

            $lastId = $this->_db->lastInsertId();

            $this->_addActionFlow($lastId, 'ADDITION', $userId);

            return $lastId;
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
            $select = 'SELECT
                          COUNT(*)
                       FROM
                          STATUS
                       WHERE
                          STS_VALUE = ?


                    ';
            $res = $this->_db->fetchAssoc($select, array((string) $data['value']));

            if($res['COUNT(*)'] > 0)
            {
                throw new TicketException('Status already exist');
            }
            else
            {
                $sql = 'INSERT INTO STATUS (STS_VALUE, STS_IS_CLOSED) VALUES (?,?)';
                return $this->_db->executeQuery($sql, array($data['value'], $data['isClosed']));
            }
        }
    }

    public function addPriority($data)
    {
        if(empty($data)) {
            throw new TicketException();
        }else{
            $select = 'SELECT
                          COUNT(*)
                       FROM
                          PRIORITY
                       WHERE
                          PRT_VALUE = ?


                    ';
            $res = $this->_db->fetchAssoc($select, array((string) $data['value']));

            if($res['COUNT(*)'] > 0)
            {
                throw new TicketException('Priority already exist');
            }
            else
            {
                $sql = 'INSERT INTO PRIORITY (PRT_VALUE) VALUES (?)';
                return $this->_db->executeQuery($sql, array($data['value']));
            }
        }
    }

    public function addQueue($data)
    {
        if(empty($data)) {
            throw new TicketException();
        }else{
            $select = 'SELECT
                          COUNT(*)
                       FROM
                          QUEUE
                       WHERE
                          QUE_NAME = ?


                    ';
            $res = $this->_db->fetchAssoc($select, array((string) $data['name']));

            if($res['COUNT(*)'] > 0)
            {
                throw new TicketException('Queue already exist');
            }
            else
            {
                $sql = 'INSERT INTO QUEUE (QUE_NAME) VALUES (?)';
                return $this->_db->executeQuery($sql, array($data['name']));
            }
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

        $lastId = $this->_db->lastInsertId();
        $this->_addActionFlow($idTicket, 'COMMENT', $idUser, null, null, $lastId);

        return $lastId;
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

    public function getComment($idComment)
    {
        $sql = 'SELECT
                    CMT_CREATION_DATE, CMT_VALUE,
                    USER_ID, USER_NAME, USER_SURNAME
                FROM
                    COMMENT, USER
                WHERE
                    USR_CMT_AUTHOR = USER_ID AND CMT_ID = ?';
        $comment = $this->_db->fetchAll($sql, array((int)$idComment));

        return $comment[0];
    }

    public function changeStatus($data, $idUser, $idTicket, $oldStatus)
    {
        if(empty($data)) {
            throw new TicketException();
        }else {
            $sql = "UPDATE TICKET SET STS_TCK_STATUS = ? WHERE TCK_ID = ?";
            $this->_db->executeQuery($sql, array($data['status'], $idTicket));

            $this->_addActionFlow($idTicket, 'STATUS', $idUser, $oldStatus, $data['status']);
        }
    }

    public function changeQueue($data, $idUser, $idTicket, $oldQueue)
    {
        if(empty($data)) {
            throw new TicketException();
        }else {
            $sql = "UPDATE TICKET SET QUE_QUEUE = ? WHERE TCK_ID = ?";
            $this->_db->executeQuery($sql, array($data['queue'], $idTicket));

            $this->_addActionFlow($idTicket, 'QUEUE', $idUser, $oldQueue, $data['queue']);
        }
    }

    public function changePriority($data, $idUser, $idTicket, $oldPriority)
    {
        if(empty($data)) {
            throw new TicketException();
        }else {
            $sql = "UPDATE TICKET SET PRT_TCK_PRIORITY = ? WHERE TCK_ID = ?";
            $this->_db->executeQuery($sql, array($data['priority'], $idTicket));
            $this->_addActionFlow($idTicket, 'PRIORITY', $idUser, $oldPriority, $data['priority']);
        }
    }

    private function _addActionFlow($idTicket, $type, $idChangeAuthor, $oldValue=null, $newValue=null, $idComment=null) {

        $types = array(
            'ADDITION' => 1,
            'STATUS' => 2,
            'QUEUE' => 3,
            'PRIORITY' => 4,
            'REPIN' => 5,
            'COMMENT' => 6
        );
        $date = date('Y-m-d H:i:s');
        $sql = 'INSERT INTO ACTION_FLOW (ACT_DATE, ACT_PREVIOUS_VALUE, ACT_ACTUAL_VALUE, TCK_TICKET, USR_CHANGE_AUTHOR, TYP_ACTION_TYPE,  CMT_COMMENT) VALUES (?,?,?,?,?,?,?)';
        return $this->_db->executeQuery($sql, array($date, $oldValue, $newValue, $idTicket, $idChangeAuthor, $types[$type], $idComment));
    }


    public function repinUser($data, $idUser, $idTicket, $oldOwner)
    {
        if(empty($data)) {
            throw new TicketException();
        }else {
            if ($oldOwner != $data['owner']) {
                if ($data['owner'] == 'nobody') {
                    $data['owner'] = NULL;
                }
                $sql = "UPDATE TICKET SET USR_TCK_OWNER = ? WHERE TCK_ID = ?";
                $this->_db->executeQuery($sql, array($data['owner'], $idTicket));
                $this->_addActionFlow($idTicket, 'REPIN', $idUser, $oldOwner, $data['owner']);
            }
        }
    }

    public function getActionFlow($idTicket) {
        $sql = 'SELECT
                    *
                FROM
                    ACTION_FLOW, ACTION_TYPE
                WHERE
                    TCK_TICKET = ? AND TYP_ACTION_TYPE=TYP_ID
                ORDER BY ACT_ID    ';
        $flow = $this->_db->fetchAll($sql, array((int)$idTicket));

        $actions = array();

        foreach ($flow as $action) {
            $tmp = array();
            $tmp['type'] = $action['TYP_VALUE'];
            $tmp['date'] = $action['ACT_DATE'];
            $tmp['comunicate'] = $action['TYP_COMUNICATE'];
            $tmp['author'] = $this->_usersModel->getUserById($action['USR_CHANGE_AUTHOR']);

            if ($tmp['type'] == 'ADDITION') {

            } elseif ($tmp['type'] == 'STATUS') {
                $tmp['oldStatus'] = $this->getStatusById($action['ACT_PREVIOUS_VALUE']);
                $tmp['newStatus'] = $this->getStatusById($action['ACT_ACTUAL_VALUE']);
            } elseif ($tmp['type'] == 'QUEUE') {
                $tmp['oldQueue'] = $this->getQueueById($action['ACT_PREVIOUS_VALUE']);
                $tmp['newQueue'] = $this->getQueueById($action['ACT_ACTUAL_VALUE']);
            } elseif ($tmp['type'] == 'PRIORITY') {
                $tmp['oldPriority'] = $this->getPriorityById($action['ACT_PREVIOUS_VALUE']);
                $tmp['newPriority'] = $this->getPriorityById($action['ACT_ACTUAL_VALUE']);
            } elseif ($tmp['type'] == 'REPIN') {
                if(empty($action['ACT_PREVIOUS_VALUE'])) {
                    $tmp['oldOwner'] = 'nobody';
                } else {
                    $tmp['oldOwner'] = $this->_usersModel->getUserById($action['ACT_PREVIOUS_VALUE']);
                }
                if (empty($action['ACT_ACTUAL_VALUE'])) {
                    $tmp['newOwner'] = 'nobody';
                } else {
                    $tmp['newOwner'] = $this->_usersModel->getUserById($action['ACT_ACTUAL_VALUE']);
                }


            } elseif ($tmp['type'] == 'COMMENT') {
                $tmp['comment'] = $this->getComment($action['CMT_COMMENT']);
            }

            $actions[] = $tmp;
            unset($tmp);
        }
        
        return $actions;
    }

    public function getStatusById($id)
    {
        $sql = 'SELECT STS_VALUE FROM STATUS WHERE STS_ID = ?';
        $res = $this->_db->fetchAssoc($sql, array((string) $id));

        return $res['STS_VALUE'];
    }

    public function getQueueById($id)
    {
        $sql = 'SELECT QUE_NAME FROM QUEUE WHERE QUE_ID = ?';
        $res = $this->_db->fetchAssoc($sql, array((string) $id));

        return $res['QUE_NAME'];
    }

    public function getPriorityById($id)
    {
        $sql = 'SELECT PRT_VALUE FROM PRIORITY WHERE PRT_ID = ?';
        $res = $this->_db->fetchAssoc($sql, array((string) $id));

        return $res['PRT_VALUE'];
    }

    public function acceptTicket($idTicket, $idUser)
    {
        $sql = "UPDATE TICKET SET USR_TCK_OWNER = ? WHERE TCK_ID = ?";
        $this->_db->executeQuery($sql, array($idUser, $idTicket));
        $this->_addActionFlow($idTicket, 'REPIN', $idUser, null, $idUser);

    }
}

