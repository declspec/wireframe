<?php
require(__DIR__ . "/lib/DependencyContainer.php");
require(__DIR__ . "/lib/framework/fatfree.php");

interface IModule {
    public function build($app, $config, $ioc);
};

interface IRenderer {
    public function render($view, array $data, $mime, $ttl, $return);
    public function set($var, $data);
};

class Application extends FatFree {
    private $_config = null;
    private $_components = array();

    public function __construct($config) { 
        parent::__construct();
        $this->_config = $config;
        $this->configure();
    }
    
    public function __call($fn, $arguments) {
        if (isset($this->_components[$fn])) 
            return $this->getComponent($fn);
        return parent::__call($fn, $arguments);
    }
    
    public function run() {
        $ioc = new DependencyContainer();
        $this->compileModules($ioc);

        parent::run();
    }
    
    public function registerComponent($name, $component) {
        $this->_components[$name] = $component;
        return $this;
    }
    
    public function getComponent($name, $defaultValue=null) {
        return isset($this->_components[$name])
            ? (is_callable($this->_components[$name])
                ? ($this->_components[$name] = call_user_func($this->_components[$name], $this))
                : $this->_components[$name])
            : $defaultValue;
    }

    protected function configure() {
        $stateMappings = array(
            "debugLevel" => "DEBUG",
            "autoEscapeHtml" => "ESCAPE"
        );

        foreach($stateMappings as $cfg=>$hiveKey) {
            if (isset($this->_config[$cfg])) 
                $this->set($hiveKey, $this->_config[$cfg]);
        }
    }

    protected function compileModules($ioc) {
        foreach($this->_config["modules"] as $module) {
            $moduleClass = ucfirst($module) . "Module";
            require(__DIR__ . "/modules/{$module}.php");
            $module = new $moduleClass();
            $module->build($this, $this->_config, $ioc);
        }
    }
};