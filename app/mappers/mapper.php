<?php
interface IDataMapper {
    public function getTable();
    public function getPk();
    public function hasAutoIncrementKey();

    public function mapEntityToRecord($entity);
    public function mapEntitiesToRecords(array $entities);
    public function mapRecordToEntity(array $record, $prefix);
    public function mapRecordsToEntities(array $records, $prefix);
    public function mapColumnToField($column);
    
    public function newEntity();
};

abstract class SqlDataMapper implements IDataMapper {
    protected $_table;
    protected $_pk;
    protected $_aik;
    protected $_mappings;
    
    public function getTable() { return $this->_table; }
    public function getPk() { return $this->_pk; }
    public function hasAutoIncrementKey() { return $this->_aik; }
    
    public function __construct($table, $primaryKey, $autoKey, $mappings) {
        $this->_table = $table;
        $this->_pk = $primaryKey;
        $this->_aik = $autoKey;
        $this->_mappings = $mappings;
    }

    public function newEntity() {
        return $this->newEntity_impl();
    }
    
    public function mapColumnToField($column) {
        return isset($this->_mappings[$column])
            ? $this->_mappings[$column]
            : null;
    }

    public function mapEntityToRecord($entity) {
        $record = array();
        foreach($this->_mappings as $column=>$prop) {
            if (property_exists($entity, $prop))
                $record[$column] = $entity->$prop;
        }
        return $record;
    }
    
    public function mapEntitiesToRecords(array $entities) {
        return array_map(array($this, "mapEntityToRecord"), $entities);
    }
    
    public function mapRecordToEntity(array $record, $prefix=null) {
        return $this->newEntity_impl($record, $prefix);
    }
    
    public function mapRecordsToEntities(array $records, $prefix=null) {
        if ($prefix === "" || $prefix === null) 
            return array_map(array($this, "mapRecordToEntity"), $records);
            
        $entities = array();
        foreach($records as $record)
            $entities[] = $this->mapRecordToEntity($record, $prefix);
        return $entities;
    }

    /**
     * @param string $alias
     * @return array
     */
    public function getColumns($alias="") {
        $rename = empty($alias) ? null : ($alias . "_");
        $alias = empty($alias) ? "" : ($alias . ".");
        
        return array_map(function($c) use ($alias, $rename) {
            return "{$alias}`{$c}`" . ($rename !== null ? " AS `{$rename}{$c}`" : "");
        }, array_keys($this->_mappings));
    }

    /**
     * @param array $initial
     * @return null
     */
    private function newEntity_impl(array $initial=null, $prefix=null) {
        $entity = new stdClass();
        $prefix = empty($prefix) ? "" : $prefix."_";

        foreach($this->_mappings as $column=>$prop) {
            $entity->$prop = ($initial !== null && isset($initial[$prefix.$column]))
                ? $initial[$prefix.$column]
                : null;
        }
        return $entity;
    }
};