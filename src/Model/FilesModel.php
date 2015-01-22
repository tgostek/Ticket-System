<?php

namespace Model;

use Doctrine\DBAL\DBALException;
use Silex\Application;

class FilesModel
{

    protected $_db;

    public function __construct(Application $app)
    {
        $this->_db = $app['db'];
    }

    public function saveFile($name)
    {
        $sql = 'INSERT INTO `ATTACHMENT` (`ATT_NAME`) VALUES (?)';
        $this->_db->executeQuery($sql, array($name));

        return $this->_db->lastInsertId();
    }

    public function createName($name)
    {
        $newName = '';
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $newName = $this->_randomString(32) . '.' . $ext;

        while(!$this->_isUniqueName($newName)) {
            $newName = $this->_randomString(32) . '.' . $ext;
        }

        return $newName;
    }

    public function addFileToTicket($fileId, $ticketId)
    {
        $sql = 'INSERT INTO `TICKET_has_ATTACHMENT` (`TCK_TICKET`, `ATT_ATTACHMENT`) VALUES (?,?)';
        $this->_db->executeQuery($sql, array($ticketId, $fileId));
    }

    public function addTicketToComment($fileId, $commentId)
    {
        $sql = 'INSERT INTO `COMMENT_has_ATTACHMENT` (`CMT_COMMENT`, `ATT_ATTACHMENT`) VALUES (?,?)';
        $this->_db->executeQuery($sql, array($commentId, $fileId));
    }

    protected function _randomString($length)
    {
        $string = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));
        for ($i = 0; $i < $length; $i++) {
            $string .= $keys[array_rand($keys)];
        }
        return $string;
    }

    protected function _isUniqueName($name)
    {
        $sql = 'SELECT COUNT(*) AS files_count FROM ATTACHMENT WHERE ATT_NAME = ?';
        $result = $this->_db->fetchAssoc($sql, array($name));
        return !$result['files_count'];
    }

}