<?php

namespace BetaPort;

use pocketmine\plugin\PluginBase;
use pocketmine;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\Server;

//class Process extends QueryRegenerateEvent{
  //$
//}

class BetaPort extends PluginBase{
  public function onEnable(){
    \pocketmine\MINECRAFT_VERSION = "v0.13.0 alpha";
    $this->getLogger()->info("BetaPort was successful!");
  }
}
