<?php
use Slim\Logger\DateTimeFileWriter;

include_once ROOT_DIR.'/src/config/secret_config.php';

date_default_timezone_set('Europe/Paris');



$slimConfig = [

    'dataBase' => $dbConfig,

    'key.api' => $apiKey,

    'key.jwt' => $jwtKey,

    'log.enabled' => true,

    'log.writer' => new DateTimeFileWriter(
        [
            'path' => ROOT_DIR . '/logs',
            'name_format' => 'Y-m-d',
            'message_format' => '%label% - %date% - %message%'
        ]
    ),
    'mode' => 'dev',
    'debug' => false
];

return $slimConfig;