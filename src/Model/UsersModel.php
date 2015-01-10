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
        $sql = 'SELECT * FROM USER WHERE USR_LOGIN = ?';
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
            	ROL_VALUE
            FROM 
            	USER, USER_ROLE
            WHERE
                ROL_ID = ROL_USR_ROLE AND USR_ID = ?
            ';

        $result = $this->_db->fetchAll($sql, array((string) $userId));

        $roles = array();
        foreach ($result as $row) {
            $roles[] = $row['ROL_VALUE'];
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
            	ROL_VALUE
            FROM 
            	USER, USER_ROLE
            WHERE
                ROL_ID = ROL_USR_ROLE AND USR_ID = ?
            ';

        $result = $this->_db->fetchAssoc($sql, array((string) $userId));

        return $result['ROL_VALUE'];
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
                USR_ID = ?
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
    
    /**
     * Check if user exists
     *
     * @access protected
     * @param int $id
     */
    public function checkUserExist($id)
    {
        $sql = 'SELECT USR_ID FROM USER WHERE USR_ID = ?';
        $res = $this->_db->fetchAssoc($sql, array( $id));
        if (!$res) {
            return false;
        } else {
            return $res['USR_ID'];
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
        $sql = 'UPDATE USER SET USR_PASSWORD = ? WHERE USR_ID = ?';
        return $this->_db->executeQuery(
            $sql, array($data['password'], $idUser)
        );
    }
}