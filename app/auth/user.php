<?php
class UserComponent {
    private $_session;
    private $_manager;
    
    public function __construct($session, $manager) {
        $this->_session = $session;
        $this->_manager = $manager;
    }
    
    public function isAuthenticated() {
        return $this->_session->get("user.id") !== null;
    }
    
    public function login($userId, $summonerId, $friendly) {
        // Restart the session if there's already an authenticated session active (no carry-over session values pls)
        if ($this->isAuthenticated())
            $this->_session->restart();
            
        $this->_session->set("user.id", $userId);
        $this->_session->set("user.sid", $summonerId);
        $this->_session->set("user.friendly", $friendly);
        $this->fetchRoles();
    }
    
    public function getId() {
        return $this->isAuthenticated()
            ? $this->_session->get("user.id")
            : null;
    }
    
    public function getSummonerId() {
        return $this->isAuthenticated()
            ? $this->_session->get("user.sid")
            : null;
    }
    
    public function getDisplayName() {
        return $this->isAuthenticated()
            ? $this->_session->get("user.friendly", "Guest")
            : "Guest";
    }
    
    public function logout() {
        if ($this->isAuthenticated()) {
            // Revoke the user's auth token before destroying the session
            $this->_manager->revoke($this->getId(), $this->_session->get("user.token"));
            $this->_session->destroy();
        }
    }
    
    public function hasAnyRoles(array $roles) {
        return count(array_intersect($roles, $this->fetchRoles())) > 0;    
    }
    
    public function hasAllRoles(array $roles) {
        return count(array_diff($roles, $this->fetchRoles())) === 0;
    }
    
    public function hasRole($role) {
        return in_array($role, $this->fetchRoles());
    }
    
    public function fetchRoles() {
        if (!$this->isAuthenticated())
            return array("guest");
        
        if ($this->_session->exists("user.token") && null !== ($stored = $this->_manager->fetch($this->_session->get("user.token"))))
            return $stored;
    
        // At this point the token used is clearly invalid (i.e. revoked)
        // so a regenerated token is needed
        $this->regenerateToken();
        return $this->_manager->fetch($this->_session->get("user.token"));
    }
    
    protected function regenerateToken() {
        // note: do NOT use "getId" here, if "login" calls this method then isAuthenticated will
        // still return false and the userId will be "null", not gut.
        $token = $this->_manager->grant($this->_session->get("user.id"));
        $this->_session->set("user.token", $token);
    }
};