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

    public function addAlbum($data)
    {
        $sql = 'INSERT INTO albums (title, artist) VALUES (?,?)';
        $this->_db->executeQuery($sql, array($data['title'], $data['artist']));
    }

}