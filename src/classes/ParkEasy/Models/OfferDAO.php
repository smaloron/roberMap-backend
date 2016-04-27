<?php

namespace ParkEasy\Models;


use ParkEasy\Models\DAO\GenericDAO;

class OfferDAO extends GenericDAO
{
    public function __construct(\PDO $pdo)
    {
        $tableName = 'offers';
        parent::__construct($pdo, $tableName);
    }

    /**
     *
     * @param array $params
     * @return array
     */
    public function search($params)
    {
        $rs = $this->getResultSetFromStoredProcedure(
            'GET_ONE_MATCHING_OFFER',
            $params
        );

        return $rs;
    }


}