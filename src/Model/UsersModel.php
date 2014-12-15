<?php

namespace Model;

use Doctrine\DBAL\DBALException;
use Silex\Application;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UsersModel
{

    protected $_app;
    protected $_db;

    public function __construct(Application $app)
    {
        $this->_app = $app;
        $this->_db = $app['db'];
    }

    public function loadUserByLogin($login)
    {
        $data = $this->getUserByLogin($login);

        if (!$data) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $login));
        }

        $roles = $this->getUserRoles($data['id']);

        if (!$roles) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $login));
        }

        $user = array(
            'login' => $data['login'],
            'password' => $data['password'],
            'roles' => $roles
        );

        return $user;
    }

    public function getUserByLogin($login)
    {
        $sql = 'SELECT * FROM USER WHERE USR_LOGIN = ?';
        return $this->_db->fetchAssoc($sql, array((string) $login));
    }

    public function getUserRoles($userId)
    {
        $sql = '
            SELECT
            	ROL_VALUE
            FROM
            	USER, USER_ROLE
            WHERE
                ROL_ID = ROL_USR_ROLE AND USR_ID = ?
            ';

        $result = $this->_db->fetchAll($sql, array((string) $userId));

        $roles = array();
        foreach ($result as $row) {
            $roles[] = $row['ROLE_NAME'];
        }

        return $roles;
    }

    public function addUser($data)
    {
        $login = $this->getUserByLogin($data['login']);

        if ($login) {
            return false;
        }
        $sql = 'INSERT INTO USER
               (USR_NAME, USR_SURNAME, USR_LOGIN, USR_PASSWORD)
               VALUES (?,?,?,?)';
        return $this
            ->_db
            ->executeQuery(
                $sql,
                array(
                    $data['name'],
                    $data['surname'],
                    $data['login'],
                    $data['password']
                )
            );
    }

}