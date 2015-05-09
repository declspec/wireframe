<?php
interface IPermissionStore {
    public function store($userId, $token, array $roles, $ttl);
    public function fetch($userId, $token);
    public function revoke($userId, $token);
    public function revokeAll($userId);
};

interface IRoleProvider {
    public function fetchRoles($userId);
};

class PermissionManager {
    private $_cache;
    private $_store;
    private $_roleProviders;
    
    public function __construct(IPermissionStore $storage, array $roleProviders) {
        if ($roleProviders === null || $storage === null)
            throw new InvalidArgumentException("Parameter cannot be null: " . ($storage === null ? '$storage' : '$roleProviders'));
            
        $this->_cache = array();
        $this->_store = $storage;
        $this->_roleProviders = $roleProviders;
    }
    
    public function grant($userId, $ttl=0) {
        $salt = hash("sha256", (time() + $userId + rand()));
        $token = $userId . ":" . substr($salt, 0, strlen($salt) - strlen($userId) - 1);
        
        $roles = array_unique(array_reduce($this->_roleProviders, function($acc,$provider) use($userId) {
            $roles = $provider->fetchRoles($userId);
            if (is_array($roles))
                $acc += $roles;
            else if ($roles !== null)
                $acc[] = $roles;
            return $acc;
        }, array()));
        
        $this->_store->store($userId, $token, $roles, $ttl);
        $this->_cache[$token] = $roles;
        
        return $token;
    }
    
    public function revoke($userId, $token=null) {
        if ($token !== null) {
            $this->_store->revoke($userId, $token);
            unset($this->_cache, $token);
        }
        else {
            $this->_store->revokeAll($userId);
            foreach($this->_cache as $tok=>$data) {
                if (strpos($tok, $userId.":") === 0)
                    unset($this->_cache[$tok]);
            }
        }
    }
    
    public function fetch($token) {
        if (isset($this->_cache[$token]))
            return $this->_cache[$token];

        list($userId,$etc) = explode(":", $token, 2);
        return ($this->_cache[$token] = $this->_store->fetch($userId, $token));
    }
};