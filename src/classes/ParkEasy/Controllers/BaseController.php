<?php

namespace ParkEasy\Controllers;

use RKA\Slim;
use Slim\Http\Request;
use Slim\Http\Response;
use ParkEasy\Security;

abstract class BaseController
{
    // Optional properties
    /**
     * @var Slim
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;


    // Optional setters
    public function setApp($app)
    {
        $this->app = $app;
    }

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    // Init
    public function init()
    {
        // do things now that app, request and response are set.
    }

    protected function jsonResponse($data, $message = '', $error = false){
        $response = [
            'error'     => $error,
            'message'   => $message,
            'data'      => $data,
            'token'     => $this->getSecurity()->getToken()
        ];

        echo json_encode($response);
    }

    protected function jsonSuccessResponse($data, $message = ''){
        $response = [
            'error'     => false,
            'message'   => $message,
            'data'      => $data,
            'hasData'   => count($data) >0,
            'token'     => $this->getSecurity()->getToken()
        ];

        echo json_encode($response);
    }

    protected function jsonErrorResponse($message = ''){
        $response = [
            'error'     => true,
            'message'   => $message,
            'data'      => [],
            'token'     => $this->getSecurity()->getToken()
        ];

        echo json_encode($response);
    }

    /**
     * @return Security
     */
    protected function getSecurity(){
        $security = $this->app->container->get('security');
        return $security;
    }

    protected function arrayAddPrefix($array, $prefix = 'p_'){
        $array = array_combine(
            array_map(function($k){ return 'p_'.$k; }, array_keys($array)),
            $array
        );

        return $array;
    }
}