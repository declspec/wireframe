<?php
class SqlStore {
    protected $_db;
    protected $_mapper;
    
    public function __construct($mapper, $db) {
        $this->_db = $db;
        $this->_mapper = $mapper;
    }
    
    public function insert($entity) {
        if (property_exists($entity, "dateCreated") && $entity->dateCreated === null)
            $entity->dateCreated = $this->now();
    
        $record = $this->_mapper->mapEntityToRecord($entity);
        $params = $this->mapParams($record);
        
        $sql = "INSERT INTO `{$this->_mapper->getTable()}` (`" . implode("`,`", array_keys($record)) . "`)
                VALUES (" . implode(",", array_keys($params)) . ")";

        $success = $this->_db->exec($sql, $params) === 1;
        if ($success && $this->_mapper->hasAutoIncrementKey()) {
            $pk = $this->_mapper->getPk();
            $entity->$pk = $this->_db->lastInsertId();
        }
        
        return $success;
    }
    
    
    
    public function update($entity) {
        if (false === ($pk = $this->extractPk($entity)))
            return false;
            
        if (property_exists($entity, "dateModified") && $entity->dateModified === null)
            $entity->dateModified = $this->now();
        
        $record = $this->formatEntity($entity);
        $updates = implode(", ", $this->mapValues($record));
        $clause = implode(" AND ", $this->mapValues($pk));
        $params = array_merge($this->mapParams($record), $this->mapParams($pk));
        
        $sql = "UPDATE `{$this->_mapper->getTable()}` SET {$updates} WHERE {$clause}";
        return $this->_db->exec($sql, $params) !== false;
    }
    
    public function delete($entity) {
        if (false === ($pk = $this->extractPk($entity)))
            return false;
            
        $clause = implode(" AND ", $this->mapValues($pk));
        $sql = "DELETE FROM `{$this->_mapper->getTable()}` WHERE {$clause}";
        
        return $this->_db->exec($sql, $this->mapParams($pk)) !== false;
    }
    
    public function insertAll(array $entities) {
        return $this->executeAll_impl($entities, "insert");
    }
    
    public function updateAll(array $entities) {
        return $this->executeAll_impl($entities, "update");
    }
    
    public function deleteAll(array $entities) {
        return $this->executeAll_impl($entities, "delete");
    }
    
    public function insertWith($entity, $continue) {
        return $this->executeWith_impl($entity, "insert", $continue);
    }
    
    public function updateWith($entity, $continue) {
        return $this->executeWith_impl($entity, "update", $continue);
    }
    
    public function deleteWith($entity, $continue) {
        return $this->executeWith_impl($entity, "delete", $continue);
    }
    
    public function insertAllWith(array $entities, $continue) {
        return $this->executeWith_impl($entities, "insertAll", $continue);
    }
    
    public function updateAllWith(array $entities, $continue) {
        return $this->executeWith_impl($entities, "updateAll", $continue);
    }
    
    public function deleteAllWith(array $entities, $continue) {
        return $this->executeWith_impl($entities, "deleteAll", $continue);
    }
    
    public function newEntity() {
        return $this->_mapper->newEntity();
    }
    
    public function findByPk($pk) {
        $fields = $this->_mapper->getPk();
        if ((is_string($fields) && is_array($pk)) || (is_array($fields) && (!is_array($pk) || count($pk) !== count($fields))))
            throw new Exception("Mismatched primary key fields provided");
        
        $params = array();
        if (is_string($fields))
            $params[$fields] = $pk;
        else {
            for($i=0; $i<count($fields); ++$i)
                $params[$fields[$i]] = $pk[$i];
        }

        return $this->findOneWhere($this->mapValues($params), $this->mapParams($params));
    }
    
    protected function executeWith_impl($entity, $fn, $continue) {
        if (!is_callable($continue))
            throw new InvalidArgumentException('$continue must be a callable object');
        
        $this->_db->begin();
        try {
            $success = $this->$fn($entity) !== false;
            if ($success && !call_user_func($continue, $entity))
                throw new Exception("continue failed");
            
            $this->_db->commit();
            return $success;
        }
        catch(Exception $ex) {
            $this->_db->rollback();
            return false;
        }
    }
    
    protected function executeAll_impl(array $entities, $fn) {
        $this->_db->begin();
        try {
            foreach($entities as $entity) {
                if (false === $this->$fn($entity))
                    throw new Exception("Failed to {$fn} entity");
            }
            
            $this->_db->commit();
            return true;
        }
        catch(Exception $ex) {
            $this->_db->rollback();
            return false;
        }
    }
    
    protected function findOneWhere($criteria, array $params, $op='AND') {
        $sql = "SELECT " . implode(", ", $this->_mapper->getColumns()) . " FROM `{$this->_mapper->getTable()}` WHERE " .
                implode(" {$op} ", (array)$criteria);

        $result = $this->_db->execSingle($sql, $params);
        return is_array($result)
            ? $this->_mapper->mapRecordToEntity($result)
            : $result;
    }
    
    protected function findWhere($criteria, array $params, $op='AND') {
        $sql = "SELECT " . implode(", ", $this->_mapper->getColumns()) . " FROM `{$this->_mapper->getTable()}` WHERE " .
                implode(" {$op} ", (array)$criteria);
                
        $result = $this->_db->exec($sql, $params);
        return is_array($result)
            ? $this->_mapper->mapRecordsToEntities($result)
            : $result;
    }
    
    protected function deleteWhere($criteria, array $params, $op='AND') {
        $sql = "DELETE FROM `{$this->_mapper->getTable()}` WHERE " .
            implode(" {$op} ", (array)$criteria);
        
        return $this->_db->exec($sql, $params) !== false;
    }
    
    protected function formatEntity($entity) {
        $record = $this->_mapper->mapEntityToRecord($entity);
        $pk = $this->_mapper->getPk();
        
        // Easier to work with an array from here
        if (!is_array($pk))
            $pk = (array)$pk;
        
        foreach($pk as $column)
            unset($record[$column]);
            
        return $record;
    }
    
    protected function extractPk($entity) {
        $pk = $this->_mapper->getPk();
        
        if (is_string($pk)) {
            $field = $this->_mapper->mapColumnToField($pk);
            return empty($entity->$pk) ? false : array($pk => $entity->$field);
        }
            
        $extracted = array();
        foreach($pk as $column) {
            $field = $this->_mapper->mapColumnToField($column);
            if (empty($entity->$field))
                return false;
            $extracted[$column] = $entity->$field;
        }
        return $extracted;
    }
    
    protected function mapValues(array $mappings) {
        $values = array();
        foreach($mappings as $col=>$val) {
            $values[] = "`{$col}` = :{$this->escapeColumnName($col)}";
        }
        return $values;
    }
    
    protected function mapParams(array $mappings) {
        $params = array();
        foreach($mappings as $column=>$value) {
            $params[":{$this->escapeColumnName($column)}"] = $value;
        }
        return $params;
    }
    
    protected function escapeColumnName($col) {
        return preg_replace('/\s+/', '_', $col);
    }

    protected function now() {
        return date("Y-m-d H:i:s");
    }
};