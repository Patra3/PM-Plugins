<?php

namespace SheepFix;

use pocketmine\scheduler\PluginTask;

class CooldownTask extends PluginTask {
    
    public static $plugin;
    
    public function __construct($plugin){
        self::$plugin = $plugin;
        $this->start = false;
        parent::__construct($plugin);
    }
    public function onRun($ticks){
        if ($this->start){
            sleep(1);
            $t = self::$plugin;
            $a = $t::$cooldown;
            if (empty($a)){
                return true;
            }
            unset($t);
            //$a is the $cooldown array from Main.
            foreach($a as $arra){
                if ($arra[1] === 0){
                    $arra[0]->setNameTag("");
                    unset($a[array_search($arra, $a)]);
                }
                else{
                    $arra[1] = $arra[1] - 1;
                    $arra[0]->setNameTag("Sheep is on cooldown... [".$arra[1]."]");
                }
            }
            self::$plugin->setCooldownArray($arra);
            unset($arra);
            unset($a);
        }
        else{
            $this->start = true;
        }
    }
}