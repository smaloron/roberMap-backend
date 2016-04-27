<?php


namespace ParkEasy\Models;


use ParkEasy\Models\DAO\GenericDAO;

class UserDAO extends GenericDAO
{

    public function __construct(\PDO $pdo){
        $tableName = 'users';
        parent::__construct($pdo, $tableName);
    }

    /**
     * Find a user by username and password
     *
     * @param string $userName
     * @param string $password
     * @return array
     */
    public function findUser($userName, $password){
        $rs = $this->find([
                'username' => $userName,
                'password' => $password
            ]);

        return $rs;
    }

    /**
     * Get the use associated to a device key
     * this method is used to implements the auto login feature
     * when a user launch his app and is automatically authenticated
     *
     * @param string $key
     * @return array
     */
    public function autoLogin($key){
        $sql = "SELECT u.id,
                    u.username,
                    u.phone_number
                FROM user_devices ud
                INNER JOIN users u
                ON ud.user_id = u.id
                WHERE ud.device_key=?";

        $rs = $this->querySQL($sql, [$key]);

        return $rs;
    }



}