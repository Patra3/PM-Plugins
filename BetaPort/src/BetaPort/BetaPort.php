<?php

namespace BetaPort;

use pocketmine\plugin\PluginBase;
use pocketmine;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\Server;

//class Process extends QueryRegenerateEvent{
  //$
//}

class BetaPort extends QueryRegenerateEvent{
  public function onEnable(){
    $server = $this->getServer();
    $yy = new QueryRegenerateEvent(Server $server, $timeout = 5);
    /*
    runkit_method_redefine(
      'Server',
      'getVersion',
      '',
      'return "v0.13.0 alpha";',
      RUNKIT_ACC_PUBLIC
    );*/
    $yy->version = "v0.13.0 alpha";
    
    $this->getLogger()->info("BetaPort was successful!");
  }
}
