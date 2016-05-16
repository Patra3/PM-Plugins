<?php

namespace FactionsPlugin;

class FactionMember {
    
    public $player;
    public $rank;
    public $faction;
    
    public function __construct($player, $rank, $faction){
        $this->player = $player;
        $this->rank = $rank;
        $this->faction = $faction;
    }
}