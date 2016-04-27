<?php

namespace ParkEasy\Controllers;

//use ParkEasy\Models\DemandDAO;
use ParkEasy\Models\DAO\GenericDAO;
use ParkEasy\Exception\OnlyOneDemandException;

class DemandController extends BaseController
{

    public function validateAction(){
        $params = json_decode(file_get_contents('php://input'),true);

        $transactionDAO = $this->getUserTransactionDAO();
        $demandDAO = $this->getDemandDAO();

        try {
            $rs = $transactionDAO->find(
                [
                    'user_id' => $params['userId'],
                    'status' => 1
                ]
            );

            if ($rs && count($rs) > 0) {
                throw new OnlyOneDemandException();
            }

            $procParams = $this->arrayAddPrefix($params, 'p_');
            $demandDAO->executeStoredProc('VALIDATE_DEMAND', $procParams);

        } catch (OnlyOneDemandException $e){
            $this->jsonErrorResponse("Vous avez déjà une demande en cours");
        } catch (\Exception $e){
            $demandDAO->rollback();
            $this->jsonErrorResponse();
        }
    }

    /**
     * @return GenericDAO
     */
    private function getDemandDAO(){
        $dao = $this->app->container->get('DemandDAO');
        return $dao;
    }

    /**
     * @return GenericDAO
     */
    private function getUserTransactionDAO(){
        $dao = $this->app->container->get('UserTransactionDAO');
        return $dao;
    }
    
    

}