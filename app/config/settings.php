<?php
class SettingsComponent {
    private $_settings;
    
    public function __construct($settings) {
        $this->_settings = $settings;
        if (!is_array($this->_settings) && $this->_settings)
            $this->_settings = (array)$this->_settings;
    }
    
    public function get($name, $defaultValue=null) {
        return isset($this->_settings[$name])
            ? $this->_settings[$name]
            : $defaultValue;
    }
};