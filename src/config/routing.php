<?php

$app->group('/user', function() use ($app) {
    $app->get('/auto-login/:key', function($key){
        //$userDAO = new \ParkEasy\Models\UserDAO($app->)
    });
});

$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});

$app->get('/search-offers/:latitude/:longitude/:vehicleSize/',
    function ($latitude, $longitude, $vehicleSize) {

    }
);

$app->post('/new-offer', function (){

});

$app->post('/update-offer', function (){

});