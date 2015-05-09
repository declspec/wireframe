<?php
abstract class Policy {
    private $_name;
    private $_chain;
    
    public function getName() { return $this->_name; }
    public function getChain() { return $this->_chain; }
    
    public abstract function run($app, $params);
    
    public function __construct($name, $chain) {
        $this->_name = $name;  
        $this->_chain = $chain;
        
        if ($this->_chain !== null) {
            $this->_chain = array_map('strtolower', (array)$this->_chain);
        }
    }
};

class PolicyManager {
    private $_policies;
    
    public function __construct() {
        $this->_policies = array();
    }

    /**
     * @param $name
     * @param $policy
     * @return $this
     */
    public function registerPolicy($name, $policy) {
        if ($policy === null || (!($policy instanceof Policy) && !is_callable($policy)))
            throw new InvalidArgumentException("\$policy must be a valid Policy instance or a callable function that returns an instance.");

        $this->_policies[strtolower($name)] = $policy;
        return $this;
    }

    public function removePolicy($policy) {
        $name = strtolower(($policy instanceof Policy ? $policy->getName() : $policy));
        unset($this->_policies[$name]);
        return $this;
    }

    public function run($policies, $app, $params) {
        if (!is_array($policies))
            return $this->run_impl(strtolower($policies), $app, $params);
        else {
            $cache = array();
            foreach($policies as $name) {
                if (!$this->run_impl(strtolower($name), $app, $params, $cache))
                    return false;
            }
            return true;
        }
    }

    /**
     * @param $policyName
     * @param $app
     * @param array $cache
     * @return bool
     */
    private function run_impl($policyName, $app, $params, array $cache=array()) {
        if (isset($cache[$policyName]))
            return true;

        if (!isset($this->_policies[$policyName]))
            throw new InvalidArgumentException("No policy named '$policyName' exists in the policy store");

        $policy = $this->_policies[$policyName];

        // If the policy isn't a valid Policy instance then it's an instantiator that needs to be invoked to
        // get a valid Policy.
        if (!($policy instanceof Policy)) {
            $policy = $this->_policies[$policyName] = call_user_func($policy);
            if (!($policy instanceof Policy))
                throw new RuntimeException("\"$policyName\"'s instantiator did not return a valid Policy instance");
        }


        $chain = $policy->getChain();

        if($chain !== null && count($chain) > 0) {
            // Attempt to run the whole chain
            foreach($chain as $pn) {
                if (!isset($cache[$pn]) && !$this->run_impl($pn, $app, $params, $cache))
                    return false;
            }
        }

        // Run the actual policy
        return $policy->run($app, $params)
            ? ($cache[$policyName] = true)
            : false;
    }
};