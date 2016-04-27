<?php
/**
 * Created by PhpStorm.
 * User: seb
 * Date: 02/02/2015
 * Time: 10:56
 */

namespace ParkEasy\Models\DAO;


interface IDAO {

    public function getPkNames();

    public function setPkNames(array $pk);

    public function findAll();

    public function findOneByPk(array $obj);

    public function find(array $obj);

    public function save(array $obj);

    public function update(array $obj);

    public function deleteOneByPk(array $pk);

    public function delete(array $obj);

    public function executeStoredProc($procName, array $params = []);

    public function executeSQL($sql, array $params = []);

    public function querySQL($sql, array $params = []);

    public function startTransaction();

    public function rollback();

    public function commit();

}