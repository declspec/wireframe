<?php
class Session {
    protected static $_metaKey = "__meta";
    
    private $_destroyed;
    private $_sessionName;
    private $_timeout;
    private $_autoExpire;

    public function __construct($sessionName=null, $timeout=0, $autoExpire=false) {
        $this->_timeout = $timeout;
        $this->_autoExpire = $autoExpire;
        $this->_sessionName = $sessionName;
        $this->_destroyed = false;
    
        // Safely start the session
        $this->start();
        
        // Check auto-expiration
        if ($this->_autoExpire && $this->expired()) 
            $this->restart();
        
        $this->meta("last_activity", time());
    }
    
    public function restart() {
        $this->destroy();
        $this->start();
    }
    
    public function destroy() {
        $this->_destroyed = true;
    
        setcookie(session_name(),'', -48000);
		unset($_COOKIE[session_name()]);
        
        $_SESSION = array();
        session_destroy();
    }
    
    public function expired() {
        if ($this->_destroyed)
            return false;

        return $this->_timeout !== 0 && ((time() - $this->meta("last_activity")) >= $this->_timeout);
    }
    
    public function set($name, $value) {
        $_SESSION[$name] = $value;
    }
    
    public function get($name, $defaultValue=null) {
        return isset($_SESSION[$name])
            ? $_SESSION[$name]
            : $defaultValue;
    }
    
    public function delete($name) {
        $val = $this->get($name);
        if ($name !== self::$_metaKey)
            unset($_SESSION[$name]);
        return $val;
    }
    
    public function exists($name) {
        return isset($_SESSION[$name]);
    }
    
    protected function start() {
        if ($this->_sessionName !== null)
            session_name($this->_sessionName);
        session_start();
        
        // Construct the session
        if (!isset($_SESSION[self::$_metaKey])) 
            $this->init();
        
        $this->_destroyed = false;
    }
    
    protected function meta($key, $value=null) {
        return ($value !== null)
            ? ($_SESSION[self::$_metaKey][$key] = $value)
            : (isset($_SESSION[self::$_metaKey][$key]) ? $_SESSION[self::$_metaKey][$key] : null);
    }
    
    private function init() {
        $now = time();
        $_SESSION[self::$_metaKey] = array(
            'ip' => $_SERVER['REMOTE_ADDR'],
            'created'  => $now,
            'last_activity' => $now
        );
    }
}
?>