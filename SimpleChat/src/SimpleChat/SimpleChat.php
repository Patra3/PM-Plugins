<?php

namespace SimpleChat;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;

class SimpleChat extends PluginBase implements Listener {
  public function onEnable(){
    if (!is_dir($this->getDataFolder())){
      mkdir($this->getDataFolder());
      //MAKES THE SETTING.JSON
      $settings = array();
      $words = array();
      $settings["filterlevel"] = 1;
      $settings["words"] = $words;
      $settings["exclusionlist"] = "off";
      $encoded = json_encode($settings);
      $handle = fopen($this->getDataFolder()."/settings.json", "w+");
      fwrite($handle, $encoded);
      fclose($handle);
    }
  }
  public function onChat(PlayerChatEvent $event){
    //query basic information in event.
    $player = $event->getPlayer();
    $message = $event->getMessage();
    $messagearg = explode(" ", $message);
    //optional : $amword = count($messagearg);
    //grabs needed measurements in settings.json
    $jsons = file_get_contents($this->getDataFolder()."/settings.json");
    $decoded_json = json_decode($jsons, true);
    $word_array = $decoded_json["words"];
    
    
    if ($decoded_json["filterlevel"] === 1){
      
    }
    elseif ($decoded_json["filterlevel"] === 2){
      
    }
    elseif ($decoded_json["filterlevel"] === 3){
      
    }
    
  }
}
