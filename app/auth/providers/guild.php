<?php
class GuildRoleProvider implements IRoleProvider {
    private $_summonerRepository;

    public function __construct($summonerRepository) {
        $this->_summonerRepository = $summonerRepository;
    }

    public function fetchRoles($userId) {
        $summoner = $this->_summonerRepository->findByUser($userId);
        if (!$summoner)
            return null;
            
        
    }
};