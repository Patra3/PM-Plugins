<?php

namespace OpKick;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class OpKick extends PluginBase implements Listener {
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    public function onJoin(PlayerJoinEvent $event){
        if ($event->getPlayer()->isOp()){
            $event->getPlayer()->kick();
        }
    }
}