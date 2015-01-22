<?php

namespace Model;

use Doctrine\DBAL\DBALException;
use Silex\Application;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Class UsersModel
 *
 * @class UsersModel
 * @package Model
 * @author Tomasz Gostek <tomasz.gostek@uj.edu.pl>
 * @uses Silex\Application
 * @uses Symfony\Component\Security\Core\Exception\UnsupportedUserException
 * @uses Symfony\Component\Security\Core\Exception\UsernameNotFoundException
 */
class UsersModel
{
     /**
     * Application access object.
     *
     * @access protected
     * @var $_app Silex\Application
     */
    protected $_app;
    
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
        $this->_app = $app;
        $this->_db = $app['db'];
    }

     /**
     * Load user by login.
     *
     * @access public
     * @param String $login
     */
    public function loadUserByLogin($login)
    {
        $data = $this->getUserByLogin($login);
        
        if (!$data) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $login)
            );
        }

        $roles = array($this->getUserRole($data['USER_ID']));

        if (!$roles) {
            throw new UsernameNotFoundException(
                sprintf('Username "%s" does not exist.', $login)
            );
        }

        $user = array(
            'id' => $data['USER_ID'],
            'login' => $data['USER_LOGIN'],
            'password' => $data['USER_PASSWORD'],
            'roles' => $roles
        );

        return $user;
    }

     /**
     * Get users login.
     *
     * @access public
     * @param String $login
     */
    public function getUserByLogin($login)
    {
        $sql = 'SELECT * FROM USER WHERE USER_LOGIN = ?';
        return $this->_db->fetchAssoc($sql, array((string) $login));
    }

     /**
     * Get users role.
     *
     * @access public
     * @param int $userId
     */
    public function getUserRoles($userId)
    {
        $sql = '
            SELECT 
            	ROLE_NAME 
            FROM 
            	USER, ROLE 
            WHERE
                ROLE_ID = USER_ROLE AND USER_ID = ?
            ';

        $result = $this->_db->fetchAll($sql, array((string) $userId));

        $roles = array();
        foreach ($result as $row) {
            $roles[] = $row['ROLE_NAME'];
        }

        return $roles;
    }
    
    /**
     * Get user role.
     *
     * @access public
     * @param int $userId
     */
    public function getUserRole($userId)
    {
        $sql = '
            SELECT 
            	ROLE_NAME 
            FROM 
            	USER, ROLE 
            WHERE
                ROLE_ID = USER_ROLE AND USER_ID = ?
            ';

        $result = $this->_db->fetchAssoc($sql, array((string) $userId));

        return $result['ROLE_NAME'];
    }
    
    /**
     * Load user data by id.
     *
     * @access public
     * @param int $idUser
     */
    public function getUserDataById($idUser)
    {
        $sql = '
            SELECT 
            	* 
            FROM 
            	USER
            WHERE
                USER_ID = ?
            ';
        return $this->_db->fetchAssoc($sql, array((string) $idUser));
    }

     /**
     * Add new user.
     *
     * @access public
     * @param array $data
     */
    public function addUser($data)
    {
        $login = $this->getUserByLogin($data['login']);
        
        if ($login) {
            return false;
        }
        $sql = 'INSERT INTO USER 
               (USER_NAME, USER_SURNAME, USER_LOGIN, USER_PASSWORD) 
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
    
    /**
     * Check if user exists
     *
     * @access protected
     * @param int $id
     */
    public function checkUserExist($id)
    {
        $sql = 'SELECT USER_ID FROM USER WHERE USER_ID = ?';
        $res = $this->_db->fetchAssoc($sql, array( $id));
        if (!$res) {
            return false;
        } else {
            return $res['USER_ID'];
        }
    }
    
    /**
     * Change users password.
     *
     * @access public
     * @param array $data
     * @param int $idUser
     */
    public function changePassword($data, $idUser)
    {
        $login = $this->getUserByLogin($data['login']);
        
        if ($login) {
            return false;
        }
        $sql = 'UPDATE USER SET USER_PASSWORD = ? WHERE USER_ID = ?';
        return $this->_db->executeQuery(
            $sql, array($data['password'], $idUser)
        );
    }

    public function getAllUsers()
    {
        $sql = '
              SELECT
                  *
              FROM
                  USER
              ';

        $res = $this->_db->fetchAll($sql);

        $users = array();

        $users['nobody'] = 'nobody';
        foreach ($res as $user) {
            $users[$user['USER_ID']] = $user['USER_NAME'] . ' ' . $user['USER_SURNAME'];
        }
        return $users;
    }
}