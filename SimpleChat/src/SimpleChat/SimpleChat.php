<?php

namespace SimpleChat;

use pocketmine\plugin\PluginBase;

class SimpleChat extends PluginBase implements Listener {
  public function onEnable(){
    if (!is_dir($this->getDataFolder())){
      mkdir($this->getDataFolder());
      //MAKES THE SETTING.JSON
      $settings = array();
      $words = array();
      $settings["filterlevel"] = 2;
      $settings["words"] = $words;
      $settings["exclusionlist"] = "off";
      $encoded = json_encode($settings);
      $handle = fopen($this->getDataFolder."/settings.json", "w+");
      fwrite($handle, $encoded);
      fclose($handle);
      
    }
  }
}
