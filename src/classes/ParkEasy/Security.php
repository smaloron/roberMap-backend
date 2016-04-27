<?php

namespace ParkEasy;

use Firebase\JWT\JWT;

class Security
{

    /**
     * Json Web Token implementation class
     * @var JWT
     */
    private $jwt;

    /**
     * The secret key used to encode JWT tokens
     * @var string
     */
    private $jwtSecretKey;

    /**
     * The API key used to ensure that the http requests are authorized
     * @var string
     */
    private $apiKey;


    /**
     * The JWT token
     * @var string
     */
    private $token;

    /**
     * An associative array storing the user informations (id, role ...)
     * @var array
     */
    private $userInfo = [];

    /**
     * The default duration of the token in seconds
     * @var int
     */
    private $tokenDuration = 60;


    public function __construct(JWT $jwt){
        $this->jwt = $jwt;
    }

    /**
     * @param string $jwtSecretKey
     * @return Security
     */
    public function setJwtSecretKey($jwtSecretKey)
    {
        $this->jwtSecretKey = $jwtSecretKey;
    }

    /**
     * @param string $apiKey
     * @return Security
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * @param string $token
     */
    public function setToken($token)
    {
        $this->token = $token;
        $this->decodeToken();
    }



    /**
     * @return array
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * @param array $userInfo
     */
    public function setUserInfo(array $userInfo)
    {
        $this->userInfo = $userInfo;
    }

    public function setUserId($userId){
        $this->userInfo['userId'] = $userId;
    }

    public function getUserId(){
        $userId = null;
        if(isset($this->userInfo['userId'])){
            $userId = $this->userInfo['userId'];
        }
        return $userId;
    }

    public function getDeviceKey(){
        $key = null;
        if(isset($this->userInfo['deviceKey'])){
            $key = $this->userInfo['deviceKey'];
        }
        return $key;
    }

    private function decodeToken()
    {

        $this->userInfo = (array) $this->jwt->decode(
            $this->token,
            $this->jwtSecretKey,
            array('HS256')
        );
    }

    private function encodeToken($duration = 60)
    {
        $payload = (array)$this->userInfo;
        $payload['apiKey'] = $this->apiKey;
        $payload['iat'] = time();
        $payload['exp'] = time()+ $duration;

        return $this->jwt->encode(
            $payload,
            $this->jwtSecretKey
        );
    }

    public function isApiKeyValid($token = null){
        if(!isset($this->token)){
            $this->token = $token;
        }

        $this->decodeToken();

        $valid =    array_key_exists('apiKey', $this->userInfo)
                    && $this->apiKey === $this->userInfo['apiKey'];

        return $valid;
    }

    public function getToken($duration = null){
        if(!isset($duration)) $duration = $this->tokenDuration;
        $token = $this->encodeToken($duration);
        return $token;
    }

    public function isAuthenticated(){
        return true;
    }





}