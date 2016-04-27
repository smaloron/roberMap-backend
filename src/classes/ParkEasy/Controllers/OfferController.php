<?php
namespace ParkEasy\Controllers;

use ParkEasy\Models\OfferDAO;

class OfferController extends BaseController
{

    public function searchAction(){
        //get the post parameters sent by the ionic $http service as json
        $params = json_decode(file_get_contents('php://input'),true);
        
        //Add a prefix to the params keys
        $params = array_combine(
            array_map(function($k){ return 'p_'.$k; }, array_keys($params)),
            $params
        );

        try {
            $rs = $this->getOfferDAO()->search($params);
            $this->jsonSuccessResponse($rs);
        } catch (\Exception $e){
            $this->jsonErrorResponse($e->getMessage(). json_encode($params));
        }

    }

    public function addAction(){
        $params = $this->request->post();
        $success = $this->$this->getOfferDAO()->save($params);
    }

    /**
     * @return OfferDAO
     */
    private function getOfferDAO(){
        $dao = $this->app->container->get('OfferDAO');
        return $dao;
    }

}