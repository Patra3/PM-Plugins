<?php

namespace FactionsPlugin\tasks;

use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class NametagTask extends PluginTask {
    
    public $plugin;
    
    public function __construct($plugin){
        $this->plugin = $plugin;
        $this->run = false;
        parent::__construct($plugin);
    }
    public function onRun($ticks){
        if ($this->run){
            foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
                if ($this->plugin->getConfig()->get("misc")["nametag"]){
                    if (!empty($this->plugin->isPlayerInFaction($player))){
                        if (!in_array($this->plugin->getPlayerRank($player), array("Officer", "Owner"))){
                            // Member.
                            $player->setNameTag("[".TextFormat::YELLOW.$this->plugin->isPlayerInFaction($player).TextFormat::RESET."] ".$player->getName());
                        }
                        else{
                            // Owner/officer.
                            $player->setNameTag("[".TextFormat::GREEN.$this->plugin->isPlayerInFaction($player).TextFormat::RESET."] ".$player->getName());
                        }
                    }
                }
            }
        }
        else{
            $this->run = true;
        }
    }
}