<?php
class DependencyContainer {
    private $_bindings = array();
    private $_instances = array();
    
    public function singleton($typename, $resolver) {
        return $this->bind($typename, $resolver, true);
    }
    
    public function transient($typename, $resolver) {
        return $this->bind($typename, $resolver, false);
    }
    
    public function isBound($typename) {
        return isset($this->_bindings[$typename]);
    }
    
    public function isInstantiated($typename) {
        return isset($this->_instances[$typename]);
    }
    
    public function isSingleton($typename) {
        return $this->isBound($typename) && $this->_bindings[$typename]['singleton'] === true;
    }
    
    public function unbind($typename) {
        if ($this->isBound($typename)) {
            unset($this->_bindings[$typename]);
        }
    }
    
    public function bind($typename, $resolver, $singleton=false) {
        if (($resolver === null || !is_callable($resolver)) && !$singleton)
            throw new InvalidArgumentException('$resolver cannot be an object, or null, for non-singleton dependencies');
        
        // Clear the singleton instance cache in the event that this binding is overwriting an existing one
        $this->uncache($typename);
        
        $this->_bindings[$typename] = array(
            "resolver" => $resolver,
            "singleton" => !!$singleton
        );
        
        return $this;
    }
    
    public function resolve($typename) {
        if (!$this->isBound($typename))
            throw new InvalidArgumentException("'$typename' is not a recognized dependency");
        
        // Easiest case, the type is an already-instantiated singleton
        if (isset($this->_instances[$typename]))
            return $this->_instances[$typename];
            
        $resolver = $instance = $this->_bindings[$typename]["resolver"];
        
        if (is_callable($resolver)) {
            // Optimize depending on whether addition constructor arguments were passed to the function
            $argc = func_num_args();
            $argv = func_get_args();
            
            switch($argc) {
                case 1:
                    $instance = call_user_func($resolver, $this);
                    break;
                case 2:
                    $instance = call_user_func($resolver, $this, $argv[1]);
                    break;
                case 3:
                    $instance = call_user_func($resolver, $this, $argv[1], $argv[2]);
                    break;
                case 4:
                    $instance = call_user_func($resolver, $this, $argv[1], $argv[2], $argv[3]);
                    break;
                case 5:
                    $instance = call_user_func($resolver, $this, $argv[1], $argv[2], $argv[3], $argv[4]);
                    break;
                default:
                    $argv[0] = $this;
                    $instance = call_user_func_array($resolver, $argv);
                    break;
            }
        }
        
        if ($this->isSingleton($typename))
            $this->_instances[$typename] = $instance;
        
        return $instance;
    }
    
    private function uncache($typename) {
        if ($this->isInstantiated($typename))
            unset($this->_instances[$typename]);
    }
}
?>