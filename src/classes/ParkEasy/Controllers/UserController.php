<?php

namespace ParkEasy\Controllers;


use ParkEasy\Models\UserDAO;
use ParkEasy\Models\DAO\GenericDAO;

class UserController extends BaseController
{

    public function testAction(){

        /**
         * @var \ParkEasy\Security
         */
        $security = $this->getSecurity();
        $data = [];

        $userInfo = $security->getUserInfo();
        $data['userInfo'] = $userInfo;

        $this->jsonSuccessResponse($data, 'OK');
    }


    public function autoLoginAction()
    {
        $security = $this->getSecurity();
        $userInfo = $security->getUserInfo();

        $deviceDao = $this->getUserDeviceDAO();
        $rs = $deviceDao->find([
            'id' => $security->getUserId(),
            'device_key' => $security->getDeviceKey()

        ]);

        if(count($rs)>0){
            $response = ['id' => $userInfo['id']];

            $this->jsonSuccessResponse($response);
        } else {
            //unauthorized
        }


    }

    public function authenticateAction()
    {

        $login = $this->request->post('login', null);
        $password = $this->request->post('password', null);
        $dao = $this->getUserDAO();
        $rs = $dao->findUser($login, $password);

        $message = 'error';

        if(count($rs) >0){
            $userId = $rs[0]['id'];
            $security = $this->getSecurity();
            $security->setUserId($userId);
            $message = 'user found';

            $this->saveUserDevice($userId);
        }

        $this->jsonSuccessResponse($rs, $message);
    }

    public function addUserAction()
    {
        $postedData = [
            'username' => $this->request->params('email'),
            'phone_number' => $this->request->params('phone'),
            'password' => $this->request->params('password')
        ];

        try {
            $dao = $this->getUserDAO();
            $dao->save($postedData);
            $userId = $dao->getLastInsertId();
            $data = ['id' => $userId];

            $security = $this->getSecurity();
            $security->setUserId($userId);

            $this->jsonSuccessResponse($data, 'test new user');
        } catch (\Exception $e) {
            $this->jsonErrorResponse($e->getMessage());
        }

    }

    public function updateUserAction()
    {
        $postedData = [
            'username' => $this->request->params('email'),
            'phone_number' => $this->request->params('password'),
            'password' => $this->request->params('phone')
        ];

        try {
            $security = $this->getSecurity();
            $userId = $security->getUserId();
            $postedData['id'] = $userId;

            $dao = $this->getUserDAO();
            $dao->update($postedData);
            $data = [];
            $this->jsonSuccessResponse($data, 'test new user');
        } catch (\Exception $e) {
            $this->jsonErrorResponse($e->getMessage());
        }

    }


    private function saveUserDevice($userId){
        $security = $this->getSecurity();
        $params = [
            'user_id'       => $userId,
            'device_key'    => $security->getUserInfo()['deviceKey']
        ];
        $this->getUserDeviceDAO()->save($params);
    }


    /**
     * @return UserDAO
     */
    private function getUserDAO()
    {
        $dao = $this->app->container->get('UserDAO');
        return $dao;
    }

    /**
     * @return GenericDAO
     */
    private function getUserDeviceDAO()
    {
        $dao = $this->app->container->get('UserDeviceDAO');
        return $dao;
    }

}