<?php
require_once(dirname(__FILE__) . "/RiotEndpoint.php");

class RiotApi {
    private $_endpoints = array();
    
    public function __construct($host, $key) {
        $config = require(dirname(__FILE__) . "/config.php");
        foreach($config as $name=>$settings) {
            $this->_endpoints[$name] = new RiotEndpoint($host, $settings["base"], $settings["version"], $key);
        }
    }
    
    public static function normalize($str) {
        return mb_strtolower(preg_replace('/\s+/', '', $str), 'UTF-8');
    }
    
    private function findSingle($endpoint, $path, $region, $id) {
        if ($id === null)
            throw new InvalidArgumentException("\$id cannot be null");
            
        $mappings = array(
            "id" => $id,
            "region" => self::normalize($region)
        );
        
        $response = $this->_endpoints[$endpoint]->get($path, $mappings);
        return $response["status"] === 200 && isset($response["object"][$id])
            ? $response["object"][$id]
            : (($response["status"] !== 404 && $response["status"] !== 200) ? false : null);
    }
    
    // Summoner API
    public function findSummonerByName($region, $name) {
        return $this->findSingle("summoner", "/by-name/:id", $region, self::normalize($name));
    }
    
    public function findSummonerById($region, $id) {
        return $this->findSingle("summoner", "/:id", $region, $id);
    }
    
    public function findSummonerRunesById($region, $id) {
        return $this->findSingle("summoner", "/:id/runes", $region, $id);
    }
    
    public function findSummonerMasteriesById($region, $id) {
        return $this->findSingle("summoner", "/:id/masteries", $region, $id);
    }
    
    public function findSummonerNameById($region, $id) {
        return $this->findSingle("summoner", "/:id/name", $region, $id);
    }
    
    // League API
    public function findLeagueBySummonerId($region, $id) {
        return $this->findSingle("league", "/by-summoner/:id", $region, $id);
    }
    
    public function findLeagueByTeamId($region, $id) {
        return $this->findSingle("league", "/by-team/:id", $region, $id);
    }
    
    public function findLeagueEntryBySummonerId($region, $id) {
        return $this->findSingle("league", "/by-summoner/:id/entry", $region, $id);
    }
    
    public function findLeagueEntryByTeamId($region, $id) {
        return $this->findSingle("league", "/by-team/:id/entry", $region, $id);
    }
    
    public function findChallengerLeague($region, $queue) {
        return $this->_endpoints["league"]->get("/challenger?type=:queue", array(
            "queue" => strtoupper($queue),
            "region" => self::normalize($region)
        ));
    }
};