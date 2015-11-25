<?php

namespace Annihilator;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;

class Annihilator extends PluginBase implements Listener {
  /*
  This is a commissioned plugin developed by HotFireyDeath. 
  License is under the Creative Commons Attribution-ShareAlike 4.0 International.
  
  A copy of the license is available in the LICENSE.txt file.
  */
  public function onEnable(){
    if (!is_dir($this->getDataFolder())){
      mkdir($this->getDataFolder());
    }
  }
  
}
