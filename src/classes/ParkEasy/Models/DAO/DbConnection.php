<?php

namespace ParkEasy\Models\DAO;

class DbConnection
{

    /**
     * @var \PDO
     */
    private static  $instance;

    public static function getInstance(){

        if(self::$instance == null){
            self::$instance = self::getPDO();
        }
    }

    private static  function getPDO(){
        $pdoOptions = [
            \PDO::MYSQL_ATTR_INIT_COMMAND  => 'SET NAMES \'UTF8\'',
            \PDO::ATTR_ERRMODE              => \PDO::ERRMODE_EXCEPTION
        ];

        $host = $conf;
        $dsn = 'mysql:host=';
        $pdo = new PDO('mysql:host=');

        return $pdo;
    }



}