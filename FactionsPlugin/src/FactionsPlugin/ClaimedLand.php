<?php

namespace FactionsPlugin;

class ClaimedLand {
    
    public $faction;
    public $x;
    public $y;
    public $z;
    
    public function __construct($faction, $x, $y, $z){
        $this->faction = $faction;
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }
}