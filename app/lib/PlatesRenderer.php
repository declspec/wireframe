<?php
require(__DIR__ . "/../lib/plates/Engine.php");

class PlatesRenderer implements IRenderer {
    private $_plates = null;
    private $_scripts = array();
    private $_stylesheets = array();

    public function __construct($directory, $extension) {
        $this->_plates = new League\Plates\Engine($directory);
    }

    public function render($view, array $data=null, $mime="text/html", $ttl=0, $return=false) {
        if (!$return && $mime !== null)
            header("Content-type: {$mime}");

        $rendered = $data !== null
            ? $this->_plates->render($view, $data)
            : $this->_plates->render($view);
            
        if ($return)
            return $rendered;
        echo $rendered; 
    }
    
    public function addScript($src) {
        if ($src !== null)
            $this->_scripts[] = $src;
        return $this;
    }
    
    public function addStylesheet($href) {
        if ($href !== null)
            $this->_stylesheets[] = $href;
        return $this;
    }
    
    public function getScripts() {
        return $this->_scripts;
    }
    
    public function getStylesheets() {
        return $this->_stylesheets;
    }
    
    public function set($var, $value) {
        $this->_plates->addData(array($var => $value));
    }
    
    public function extend($extension) {
        $this->_plates->loadExtension($extension);
    }
};