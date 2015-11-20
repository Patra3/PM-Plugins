<?php

namespace BetaPort;

use pocketmine\plugin\PluginBase;
use pocketmine\Server;

class BetaPort extends PluginBase{
  public function onEnable(){
    $yy = new Server();
    runkit_method_redefine(
      'Server',
      'getVersion',
      '',
      'return "v0.13.0 alpha";';
    );
    $this->getLogger()->info("BetaPort was successful!");
  }
}
