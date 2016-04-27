<?php

if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

define('ROOT_DIR', dirname(__DIR__));

require ROOT_DIR . '/vendor/autoload.php';
require ROOT_DIR . '/src/config/conf.php';

session_start();

$app = new \RKA\Slim($slimConfig);

$app->contentType('application/json');

$app->config('publicRoutes', [
    '/user/new',
    '/user/authenticate',
    '/user/auto-login'
]);



/****************************************
 * SECURITE GESTION DE LA CLEF API
 * ET DU TOKEN D'AUTHENTIFICATION
 ****************************************/


/*
$app->hook(
    'slim.before.dispatch',
    function () use ($app) {
        $token = $app->request->headers->get('Authorization');
        $token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWUsImFwaUtleSI6IjUwNTgyNWJkNjE1MDlmYzYzMWU1OTI4ZGNlZTdlMzU0MTRiMjZmZmMifQ.xf22C8UJybP7x66yJpIsugCirn35jkQZTAPA1R5YHp4";

        $security = $app->container->get('security');
        $security->setToken($token);

        // Test de la clef d'API
        if(! $security->isApiKeyValid()){
            $response = [
                'error' => true,
                'message' => 'unauthorized'
            ];
            $app->status(401);
            echo json_encode($response);
            $app->stop();
        }

        // Test de l'autorisation
        //récupération de la route en cours
        $currentRoute = $app->router->getCurrentRoute()->getPattern();



        if(! in_array($currentRoute, $app->config('publicRoutes'))){
            $userInfo = $security->getUserInfo();

            if(! array_key_exists('userId', $userInfo)){
                $response = [
                    'error' => true,
                    'message' => 'unauthorized'
                ];
                $app->status(401);
                echo json_encode($response);
                $app->stop();
            }
        }

    }
);*/


/****************************************
 * GESTION DES ERREURS
 ****************************************/

// Erreur 404
$app->notFound(
    function () use ($app) {
        $response = [
            'error' => true,
            'message' => 'not found'
        ];
        $app->status(404);
        echo json_encode($response);
        $app->stop();
    }
);

// Exceptions
$app->error(
    function (\Exception $e) use ($app) {
        $app->getLog()->error($e->getMessage());

        if(
            $e instanceof \Firebase\JWT\ExpiredException
            || $e instanceof \Firebase\JWT\BeforeValidException
            || $e instanceof \Firebase\JWT\SignatureInvalidException
        ){
            $app->status(401);
        } else {
            $app->status(500);
        }

        $response = [
            'error' => true,
            'message' => $e->getMessage()
        ];


        echo json_encode($response);
        $app->stop();
    }
);

//Configuration de l'injection de dépendance
require ROOT_DIR . '/src/config/dic.php';


/********************************************
 * ROUTES
 ********************************************/
$app->get('/test', 'UserController:testAction');
$app->get('/', 'UserController:indexAction');
$app->get('', 'UserController:indexAction');
$app->post('/user/auto-login', 'UserController:autoLoginAction');
$app->post('/user/authenticate', 'UserController:authenticateAction');
$app->post('/user/new', 'UserController:addUserAction');
$app->post('/user/update', 'UserController:updateUserAction');

$app->post('/offer/search', 'OfferController:searchAction');
$app->post('/offer/new', 'OfferController:addAction');

$app->post('/demand/validate', 'DemandController:validateAction');

$app->run();