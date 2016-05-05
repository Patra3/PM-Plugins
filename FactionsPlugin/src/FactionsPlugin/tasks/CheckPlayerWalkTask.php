<?php

namespace FactionsPlugin\tasks;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class CheckPlayerWalkTask extends PluginTask {
    
    public $plugin;
    
    public function __construct($plugin){
        $this->plugin = $plugin;
        $this->run = false;
        parent::__construct($plugin);
    }
    public function onRun($ticks){
        if ($this->run){
            if ($this->plugin->getConfig()->get("land")["notifyLand"]){
                foreach($this->plugin->getServer()->getOnlinePlayers() as $pp){
                    $land = $this->plugin->isLandClaimed($pp->getLocation()->getX(), $pp->getPosition()->getZ());
                    if (!$land["claimed"]){
                        $pp->sendPopup("Currently, this land is not claimed.");
                    }
                    elseif ($this->plugin->isPlayerInFaction($pp) === $land["claimed_by"]){
                        $pp->sendPopup(TextFormat::GREEN."You are in your faction's land.");
                    }
                    else{
                        $pp->sendPopup(TextFormat::RED."This land is claimed by: ".$land["claimed_by"]);
                    }
                }
            }
        }
        else{
            $this->run = true;
        }
    }
}
