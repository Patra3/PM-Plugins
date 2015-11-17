<?php

namespace AchievementPlus;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

class AchievementPlus extends PluginBase implements Listener {
  public function onEnable(){
    if (!is_dir($this->getDataFolder())){
      mkdir($this->getDataFolder());
      $this->saveDefaultConfig();
    }
    $data = array();
  }
}
