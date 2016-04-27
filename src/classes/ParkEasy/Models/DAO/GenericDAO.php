<?php
/**
 * Cette classe est une implémentation du pattern DAO
 * Elle offre un service générique d'accès à une base de données
 * Pour ce faire elle admet deux paramètres impératifs :
 *  - Une instance de PDO
 *  - Le nom de la table ou de la vue
 */

namespace ParkEasy\Models\DAO;

use \PDO;
use \PDOStatement;
use \Exception;

class GenericDAO implements IDAO {

    /**
     * @var \PDO
     */
    protected $_dbConnection;
    /**
     * @var string
     */
    protected $_tableName;

    /**
     * @var array
     */
    protected $_pkNames = [];
    /**
     * @var bool
     */
    protected $_reuseStatement = false;
    /**
     * @var \PDOStatement
     */
    protected $_statement = null;



    /**
     * @param \PDO $pdoInstance
     * @param $tableName
     * @param array $pkNames
     */
    public function __construct(\PDO $pdoInstance, $tableName, array $pkNames = []) {
        $this->_dbConnection = $pdoInstance;
        $this->_tableName = $this->securizeInput($tableName);

        //Définition de la clé primaire
        if (count($pkNames) == 0) {
            $this->_pkNames = $this->findPkNames();
        } else {
            $this->_pkNames = $pkNames;
        }
    }

    /**
     * Sécurise une chaîne de caractère pour éviter les injections sql
     * @param string $input
     * @return string
     */
    private function securizeInput($input) {
        return str_replace(';', '', $input);
    }

    /**
     * retourne un tableau associatif
     * corresondant au champs constituant la clé primaire
     * @return array
     */
    protected function findPkNames() {
        $sql = "SHOW KEYS FROM " . $this->_tableName . " WHERE Key_name = 'PRIMARY';";
        $stm = $this->_dbConnection->query($sql);
        $rs = $stm->fetchAll(\PDO::FETCH_ASSOC);
        $pkNames = [];

        foreach ($rs as $record) {
            array_push($pkNames, $record['Column_name']);
        }
        return $pkNames;
    }

    /**
     *
     */
    public function getPkNames() {
        return $this->_pkNames;
    }

    public function setPkNames(array $pk){
        $this->_pkNames = $pk;
    }

    /**
     * @return array
     */
    public function findAll() {
        $sql = "SELECT * FROM " . $this->_tableName . ";";
        return $this->querySQL($sql);
    }

    /**
     * @param $obj
     * @return array
     */
    public function findOneByPk(array $obj) {
        $sql = "SELECT * FROM " . $this->_tableName;
        $sql .= $this->getWhereClause($obj, true);
        $params = $this->filterParams($obj);
        $rs =  $this->querySQL($sql, $params);
        if(count($rs)>1) {
            throw new Exception("La méthode findeOneByPk ne peut retourner plus d'un enregistrement");
        } elseif(count($rs) == 0){
            return false;
        } else {
            return $rs[0];
        }
    }

    /**
     * @param $obj
     * @return array
     */
    public function find(array $obj) {
        $sql = "SELECT * FROM " . $this->_tableName;
        $sql .= $this->getWhereClause($obj, false);
        $params = $obj;
        return $this->querySQL($sql, $params);
    }

    /**
     * Insère un nouvel enregistrement
     * @param $obj
     * @return string
     * @throws \Exception
     */
    public function save(array $obj) {
        $fieldsName = array_keys($obj);
        $paramNames = array_map(function ($item) {
            return ':' . $item;
        }, $fieldsName);
        $sql = "INSERT INTO " . $this->_tableName;
        $sql .= " (" . implode(',', $fieldsName) . ") ";
        $sql .= " VALUES (" . implode(',', $paramNames) . ")";

        $success = $this->executeSQL($sql, $obj);
        if($success){
            return $this->_dbConnection->lastInsertId();
        } else {
            throw new \Exception('Impossible de sauvegarder cet enregistrement');
        }

    }

    /**
     * Met à jour des enregistrements
     * @param $obj
     * @return bool
     */
    public function update(array $obj) {
        $fieldsName = array_keys($obj);
        $paramNames = array_map(function ($item) {
            return $item . '=:' . $item;
        }, $fieldsName);
        $whereParams = $this->filterParams($obj);
        $sql = "UPDATE " . $this->_tableName;
        $sql .= " SET " . implode(',', $paramNames);
        $sql .= $this->getWhereClause($whereParams, true);

        $success = $this->executeSQL($sql, $obj);
        return $success;
    }

    /**
     * @param $pk
     * @return bool
     */
    public function deleteOneByPk( array $pk) {
        $sql = "DELETE FROM " . $this->_tableName;
        $sql .= $this->getWhereClause($pk, true);
        $params = $this->filterParams($pk);
        $success = $this->executeSQL($sql, $params);
        return $success;

    }

    /**
     * Supprime un enregistrement
     * @param array $obj
     * @return bool
     */
    public function delete(array $obj) {
        $sql = "DELETE FROM " . $this->_tableName;
        $sql .= $this->getWhereClause($obj, false);
        $success = $this->executeSQL($sql, $obj);
        return $success;

    }

    /**
     * Exécute une procèdure stockée
     * @param $procName
     * @param array $params
     * @return bool
     */
    public function executeStoredProc($procName, array $params = []){
        $placeholders = '';
        if(count($params)>0){
            $placeholders = join(',', array_fill(0, count($params), '?'));
        }
        $sql = 'CALL '.$procName.'('.$placeholders. ');';
        return $this->executeSQL($sql, $params);
    }

    public function getResultSetFromStoredProcedure($procName, array $params = []){
        $placeholders = '';
        if(count($params)>0){
            if($this->isAssociativeArray($params)){
                $keys = array_keys($params);
                $keys = array_map(function($item){
                    return ':'.$item;
                }, $keys);
                $placeholders = join(',', $keys);
            } else {
                $placeholders = join(',', array_fill(0, count($params), '?'));
            }
        }
        $sql = 'CALL '.$procName.'('.$placeholders. ');';
        
        return $this->querySQL($sql, $params);
    }

    private function isAssociativeArray(array $array){
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Retourne une nouvelle requête préparée
     * ou la dernière requête préparée si $reuseStatement est vrai
     * et que le code sql est identique à la dernière requête
     * @param $sql
     * @return \PDOStatement
     */
    protected function getStatement($sql){
        if( $this->_reuseStatement
            && $this->_statement instanceof PDOStatement
            && $sql == $this->_statement->queryString )
        {
           $statement = $this->_statement;
        } else {
            $statement = $this->_dbConnection->prepare($sql);
            $this->_statement = $statement;
        }
        return $statement;
    }

    /**
     * Exécute une requête sql
     * en passant un tableau associatif en paramètre
     * @param $sql
     * @param array $params
     * @return bool
     */
    public function executeSQL($sql, array $params = []){
        $statement = $this->getStatement($sql);
        $success = $statement->execute($params);
        return $success;
    }

    /**
     * Exécute une requête sql de séléction
     * en passant un tableau associatif en paramètre
     * @param $sql
     * @param array $params
     * @return array
     */
    public function querySQL($sql, array $params = []) {
        $statement = $this->getStatement($sql);
        $statement->execute($params);
        $rs = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return $rs;
    }

    /**
     * Constitue une chaine de caractère représentant la clause Where d'une requête sql
     * à partir d'un tableau associatif dont les clés représentent les noms de champs
     * Le paramètre only pk filtre le tableau des paramètres
     * pour ne conserver que ceux qui correspondent à la clé primaire
     * @param $obj
     * @param bool $onlyPk
     * @return string
     */
    protected function getWhereClause($obj, $onlyPk = false) {

        if($onlyPk){
            $obj = $this->filterParams($obj);
        }

        $crit = array_keys($obj);
        $whereString = '';
        $nbKeys = count($crit);

        for ($i = 0; $i < $nbKeys; $i++) {
            $key = $crit[$i];
            $crit[$i] = $key . '=:' . $key;
        }

        if (count($crit) > 0) {
            $whereString = ' WHERE ' . implode(' AND ', $crit);
        }

        return $whereString;

    }


    /**
     * Filtre un tableau associatif de champs
     * pour ne conserver que ceux qui correspondent à la clé primaire
     * @param $obj
     * @return array
     */
    public function filterParams($obj) {
        $params = [];
        foreach ($obj as $key => $val) {
            if (in_array($key, $this->_pkNames)) {
                $params[$key] = $val;
            }

        }
        return $params;
    }

    /**
     * Retourne le dernier identifiant automatiquement généré par la base de données
     * @return string
     */
    public function getLastInsertId() {
        return $this->_dbConnection->lastInsertId();
    }

    /**
     * @return boolean
     */
    public function isReuseStatement() {
        return $this->_reuseStatement;
    }

    /**
     * @return PDO
     */
    public function getPDO(){
        return $this->_dbConnection;
    }

    /**
     * @param boolean $reuseStatement
     */
    public function setReuseStatement($reuseStatement) {
        $this->_reuseStatement = $reuseStatement;
    }


    public function startTransaction(){
        $this->_dbConnection->setAttribute(PDO::ATTR_AUTOCOMMIT, false);
        $this->_dbConnection->beginTransaction();
    }

    public function rollback(){
        $this->_dbConnection->rollBack();
        $this->_dbConnection->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
    }

    public function commit(){
        $this->_dbConnection->commit();
        $this->_dbConnection->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
    }
}