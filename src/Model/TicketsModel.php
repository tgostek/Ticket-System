<?php

namespace Model;

use Silex\Application;

class TicketsModel
{

    protected $_db;

    public function __construct(Application $app)
    {
        $this->_db = $app['db'];
    }

    public function addTicket($data)
    {
        $sql = 'INSERT INTO TICKET (TCK_CREATION_DATE, TCK_CLOSED_DATE, TCK_TITLE, TCK_DESC, USR_TCK_OWNER, USR_TCK_AUTHOR, STS_TCK_STATUS, PRT_TCK_PRIORITY, QUE_QUEUE) VALUES (?,?,?,?,?,?,?,?,?,?)';
        return $this->_db->executeQuery($sql, array(NOW(), NOW(), $data['title'], $data['description'], 1,1,1, $data['priority'], $data['queue']));
    }

    public function getPriorities(){
        $sql = 'SELECT *FROM PRIORITY';
        $result = $this->_db->fetchAll($sql);

        $priorities = array();
        foreach($result as $row){
            $priorities[] = $row['PRT_VALUE'];
        }

        return $priorities;
    }

    public function getQueue(){
        $sql = 'SELECT *FROM QUEUE';
        $result = $this->_db->fetchAll($sql);

        $queue = array();
        foreach($result as $row){
            $queue[] = $row['QUE_NAME'];
        }

        return $queue;
    }
}