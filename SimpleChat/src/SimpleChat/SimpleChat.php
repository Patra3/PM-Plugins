<?php

namespace SimpleChat;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerChatEvent;

class SimpleChat extends PluginBase implements Listener {
  public function onEnable(){
    if (!is_dir($this->getDataFolder())){
      mkdir($this->getDataFolder());
      //MAKES THE SETTING.JSON
      $settings = array();
      $words = array();
      $settings["filterlevel"] = 2;
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
      foreach ($messagearg as $word){
        $current_lev = 0;
        do {
          similar_text($word, $word_array[$current_lev], $percent);
          if ($percent >= 50){
            $result = $result + 1;
            $current_lev = $current_lev + 1;
          }
          else{
            $current_lev = $current_lev + 1;
          }
        }
        while ($total_am > $current_lev);
      }
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
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
    	if(strtolower($command->getName()) === "simplechat"){
    	  if (!isset($args[0])){
    	    $sender->sendMessage(TextFormat::RED."/simplechat help");
    	    return true;
    	  }
    	  elseif ($args[0] === "help"){
    	    $sender->sendMessage("/simplechat help : Lists all simplechat commands.");
    	    $sender->sendMessage("/simplechat add <word> : Adds a word into the filter.");
    	    $sender->sendMessage("/simplechat remove <word> : Removes a word from the filter.");
    	    $sender->sendMessage("/simplechat set <1:2> : Sets filter level to 1, 2.");
    	    $sender->sendMessage("/simplechat exclude <player> : Adds a player to the exclusion list.");
    	    $sender->sendMessage("/simplechat unexclude <player> : Removes a player from the exclusion list.");
    	    $sender->sendMessage("/simplechat mode <replace:warn> : Replaces word with ****, or warns sender.");
    	  }
    	  elseif ($args[0]){}
    	}
    }
}
