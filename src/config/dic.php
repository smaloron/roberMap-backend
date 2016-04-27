<?php

use \ParkEasy\Security;
use Firebase\JWT\JWT;

use ParkEasy\Models\DAO\GenericDAO;
use ParkEasy\Models\UserDAO;
use ParkEasy\Models\OfferDAO;
use ParkEasy\Models\DemandDAO;

use ParkEasy\Controllers\UserController;
use ParkEasy\Controllers\OfferController;
use ParkEasy\Controllers\DemandController;

/*********************************************
 * PDO Service
 *********************************************/
$app->container->singleton('PDO', function ($container) {
    $dbConfig = $container['settings']['dataBase'];

    $pdo = new PDO(
        'mysql:host='.$dbConfig['host'].';dbname='.$dbConfig['dbName'],
        $dbConfig['userName'],
        $dbConfig['password'],
        [
            \PDO::MYSQL_ATTR_INIT_COMMAND  => 'SET NAMES \'UTF8\'',
            \PDO::ATTR_ERRMODE              => \PDO::ERRMODE_EXCEPTION
        ]
    );
    
    return $pdo;
});

/*********************************************
 * DAO Services
 *********************************************/
$app->container->singleton('UserDAO', function() use ($app){
    $pdo = $app->container['PDO'];
    $dao = new UserDAO($pdo);
    return $dao;
});

$app->container->singleton('OfferDAO', function() use ($app){
    $pdo = $app->container['PDO'];
    $dao = new OfferDAO($pdo);
    return $dao;
});

$app->container->singleton('DemandDAO', function() use ($app){
    $pdo = $app->container['PDO'];
    $dao = new GenericDAO($pdo, 'requests');
    return $dao;
});

$app->container->singleton('UserTransactionDAO', function() use ($app){
    $pdo = $app->container['PDO'];
    $dao = new GenericDAO($pdo, 'user_transaction');
    return $dao;
});

$app->container->singleton('UserVehicleDAO', function() use ($app){
    $pdo = $app->container['PDO'];
    $dao = new GenericDAO($pdo, 'user_vehicle');
    return $dao;
});

$app->container->singleton('UserDeviceDAO', function() use ($app){
    $pdo = $app->container['PDO'];
    $dao = new GenericDAO($pdo, 'user_devices');
    return $dao;
});



/*********************************************
 * Security Services
 *********************************************/
$app->container->singleton('jwt', function() use ($app){
    return new JWT();
});

$app->container->singleton('security', function() use ($app){
    /**
     * @var \ParkEasy\Security
     */
    $security = new Security($app->container->get('jwt'));
    $security->setApiKey($app->config('key.api'));
    $security->setJwtSecretKey($app->config('key.jwt'));

    return $security;
});



/*********************************************
 * Controllers Service
 *********************************************/
$app->container->singleton('UserController', function() use ($app){
    $controller = new UserController();
    return $controller;
});

$app->container->singleton('OfferController', function() use ($app){
    return new OfferController();
});

$app->container->singleton('OfferController', function() use ($app){
    return new DemandController();
});



