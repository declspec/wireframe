<?php
class PermissionStore implements IPermissionStore {
    const PERMISSION_TABLE  = "permission_store";
    
    private $_db = null;
    
    public function __construct($db) {
        $this->_db = $db;
    }

    public function store($userId, $token, array $roles, $ttl) {
        $json = json_encode($roles);
        $sql = "INSERT INTO `" . self::PERMISSION_TABLE . "` (user_id,token,permissions,ttl,date_created) VALUES(?,?,?,?,NOW())";
        return $this->_db->exec($sql, array($userId, $token, $json, $ttl)) !== false;
    }
    
    public function fetch($userId, $token) {    
        $sql = "SELECT permissions FROM `" . self::PERMISSION_TABLE . "` WHERE user_id = ? AND token = ?";
        $permissions = $this->_db->execSingle($sql, array($userId, $token));
        return is_array($permissions)
            ? json_decode($permissions["permissions"], true)
            : $permissions;
    }
    
    public function revoke($userId, $token) {
        $sql = "DELETE FROM `" . self::PERMISSION_TABLE . "` WHERE user_id = ? AND token = ?";
        return $this->_db->execSingle($sql, array($userId, $token)) !== false;
    }
    
    public function revokeAll($userId) {    
        $sql = "DELETE FROM `" . self::PERMISSION_TABLE . "` WHERE user_id = ?";
        return $this->_db->exec($sql, array($userId)) !== false;
    }
};
