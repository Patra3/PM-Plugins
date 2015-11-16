<?php

namespace SimpleChat;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
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
      $settings["filterYtype"] = "replace";
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
    $total_am = count($word_array);
    
    //final pointers 
    $result = 0;
    
    if ($decoded_json["filterlevel"] === 1){
      foreach ($messagearg as $word){
        $current_lev = 0;
        do {
          if ($word != $word_array[$current_lev]){
            $current_lev = $current_lev + 1;
          }
          elseif ($word === $word_array[$current_lev]){
            $result = $result + 1;
            $current_lev = $current_lev + 1;
          }
        }
        while ($total_am > $current_lev);
      }
    }
    elseif ($decoded_json["filterlevel"] === 2){
      
    }
    elseif ($decoded_json["filterlevel"] === 3){
      
    }
    
    if ($result >= 1){
      if ($decoded_json["filterYtype"] === "replace"){
        $current_fy = 0;
        do{
          $message = str_replace($word_array[$current_fy], "****", $message);
          $current_fy = $current_fy + 1;
        }
        while ($current_fy > $total_am);
        $event->setMessage($message);
        return true;
      }
      elseif ($decoded_json["filterYtype"] === "warn"){
        $player->sendMessage(TextFormat::RED."Please do not swear.");
        $event->setCancelled();
        return true;
      }
    }
    else{
      return true;
    }
    
  }
}
