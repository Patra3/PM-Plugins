<?php

namespace BetaPort;

use pocketmine\plugin\PluginBase;

class BetaPort extends PluginBase{
  public function onEnable(){
    if (is_file("PocketMine-MP.phar")){
      $this->getLogger()->info("Yahoo!");
    }
    else{
      $this->getLogger()->info(":(");
    }
  }
}
