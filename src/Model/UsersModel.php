<?php

namespace Model;

use Doctrine\DBAL\DBALException;
use Silex\Application;

class UsersModel
{

    protected $_db;

    public function __construct(Application $app)
    {
        $this->_db = $app['db'];
    }

    public function login($data)
    {
        $user = $this->getUserByLogin($data['login']);

        if (count($user)) {
            if ($user['password'] == crypt($data['password'], $user['password'])) {
                return $user;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getUserByLogin($login)
    {
        $sql = 'SELECT * FROM users WHERE login = ?';
        return $this->_db->fetchAssoc($sql, array((string) $login));
    }

}