<?php
class RiotEndpoint {
    private $_host;
    private $_version;
    private $_key;
    private $_base;

    public function __construct($host, $base, $version, $key) {
        $this->_host = $host;
        $this->_version = $version;
        $this->_key = $key;
        $this->_base = $base;
    }
    
    private function buildUrl($path, $mappings=null) {
        $path = $this->_base . $path . (strpos($path, '?') !== FALSE ? "&api_key=:api_key" : "?api_key=:api_key");
        if ($mappings === null)
            $mappings = array();
            
        $mappings["host"] = $this->_host;
        $mappings["version"] = $this->_version;
        $mappings["api_key"] = $this->_key;
        
        return preg_replace_callback('/:([a-z_][a-z0-9_]+)/i', function($match) use(&$mappings) {
            return isset($mappings[$match[1]]) ? $mappings[$match[1]] : $match[0];
        }, ($this->_host . $path));
    }
    
    public function get($path, $mappings) {
        $url = $this->buildUrl($path, $mappings);
        $ch = curl_init($url);
        // Set any required cURL opts
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
        $json = curl_exec($ch);
        // Get the status and close the connection
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return array(
            "status" => $status,
            "object" => json_decode($json, true)
        );            
    }
};
?>