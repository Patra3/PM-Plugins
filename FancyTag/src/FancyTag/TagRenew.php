<?php

namespace FancyTag;

use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

class TagRenew extends PluginTask {
    public function __construct($plugin){
        $this->plugin = $plugin;
        $this->start = true;
        parent::__construct();
    }
    public function run($ticks){
        if ($this->start){
            $this->plugin->updateAllPlayers();
        }
        else{
            $this->start = true;
        }
    }
}