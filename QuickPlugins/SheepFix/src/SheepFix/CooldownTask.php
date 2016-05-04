<?php

namespace SheepFix;

use pocketmine\scheduler\PluginTask;
use pocketmine\level\particle\EnchantParticle;

class CooldownTask extends PluginTask {
    
    public $plugin;
    
    public function __construct($plugin){
        $this->plugin = $plugin;
        $this->start = false;
        parent::__construct($plugin);
    }
    public function onRun($ticks){
        if ($this->start){
            $cooldown = $this->plugin->cooldown;
            if(empty($cooldown)){
                return true;
            }
            foreach($cooldown as $objs){
                if ($objs->seconds > 1){
                    $objs->updateSeconds($objs->seconds - 1);
                    $objs->sheep->setNameTag("Sheep is on cooldown [".$objs->seconds."]");
                    new EnchantParticle($objs->sheep->getLocation());
                }
                else{
                    $objs->sheep->setNameTag("");
                    unset($cooldown[array_search($objs, $cooldown)]);
                }
            }
            $this->plugin->setCooldownArray($cooldown);
        }
        else{
            $this->start = true;
        }
    }
}