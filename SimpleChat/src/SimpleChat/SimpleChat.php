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
    $name = $player->getName();
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
      $exli = $decoded_json["exclusionlist"];
      if (in_array($name, $exli)){
        return true;
      }
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
  public function updateJson($newjson){
    // NOT API
    unlink($this->getDataFolder()."/settings.json");
    $handle = fopen($this->getDataFolder()."/settings.json", "w+");
    fwrite($handle, $newjson);
    fclose($handle);
  }
  public function onCommand(CommandSender $sender, Command $command, $label, array $args){
    if(strtolower($command->getName()) === "simplechat"){
    	  
    	  //pre-grabs the file, in case.
    	  $jsons = file_get_contents($this->getDataFolder()."/settings.json");
    	  $decodes = json_decode($jsons);
    	  $word_array = $decodes["words"];
    	  
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
    	    $sender->sendMessage("/simplechat exclusion off : Turns off exclusion list.");
    	    $sender->sendMessage("/simplechat mode <replace:warn> : Replaces word with ****, or warns sender.");
    	    return true;
    	  }
    	  elseif ($args[0] === "add"){
    	    if (!isset($args[1])){
    	      $sender->sendMessage(TextFormat::RED."/simplechat add <word>");
    	      return true;
    	    }
    	    else{
    	      if (in_array($args[1], $word_array)){
    	        $sender->sendMessage(TextFormat::RED."Word is already in filter library.");
    	        return true;
    	      }
    	      else{
      	      array_push($word_array, $args[1]);
      	      $newjson = json_encode($decodes);
      	      $this->updateJson($newjson);
      	      $sender->sendMessage(TextFormat::GREEN."Word has been added to the filter successfully.");
      	      return true;
    	      }
    	    }
    	  }
    	  elseif ($args[0] === "remove"){
    	    if (!isset($args[1])){
    	      $sender->sendMessage(TextFormat::RED."/simplechat remove <word>");
    	      return true;
    	    }
    	    else{
    	      if (!in_array($args[1], $word_array)){
    	        $sender->sendMessage(TextFormat::RED."That word was not set in the filter.");
    	        return true;
    	      }
    	      else{
    	        unset($word_array[$args[1]]);
    	        $newjson = json_encode($decodes);
    	        $this->updateJson($newjson);
    	        $sender->sendMessage(TextFormat::GREEN."Word has been removed from the filter successfully.");
    	        return true;
    	      }
    	    }
    	  }
    	  elseif ($args[0] === "set"){
    	    if (!isset($args[1])){
    	      $sender->sendMessage(TextFormat::RED."/simplechat set <1:2>");
    	      return true;
    	    }
    	    else{
    	      if ($args[1] === 1){
    	        unset($decodes["filterlevel"]);
    	        $decodes["filterlevel"] = $args[1];
    	        $newjson = json_encode($decodes);
    	        $this->updateJson($newjson);
    	        $sender->sendMessage(TextFormat::GREEN."Filter mode set to 1");
    	        return true;
    	      }
    	      elseif ($args[1] === 2){
    	        unset($decodes["filterlevel"]);
    	        $decodes["filterlevel"] = $args[1];
    	        $newjson = json_encode($decodes);
    	        $this->updateJson($newjson);
    	        $sender->sendMessage(TextFormat::GREEN."Filter mode set to 2");
    	        return true;
    	      }
    	      else{
    	        $sender->sendMessage(TextFormat::RED."/simplechat set <1:2>");
    	        return true;
    	      }
    	    }
    	  }
    	  elseif ($args[0] === "exclude"){
    	    if (!isset($args[1])){
    	      $sender->sendMessage(TextFormat::RED."/simplechat exclude <player>");
    	      return true;
    	    }
    	    else{
    	      if ($decodes["exclusionlist"] === "off"){
      	      unset($decodes["exclusionlist"]);
      	      $decodes["exclusionlist"] = array();
      	      $exlist = $decodes["exclusionlist"];
      	      array_push($exlist, $args[1]);
      	      $newjson = json_encode($decodes);
      	      $this->updateJson($newjson);
      	      $sender->sendMessage(TextFormat::GREEN."Player has been added to the exclusion list.");
      	      return true;
    	      }
    	      else{
    	        $exlist = $decodes["exclusionlist"];
    	        if (in_array($args[1], $exlist)){
    	          $sender->sendMessage(TextFormat::RED."Player is already excluded.");
    	          return true;
    	        }
    	        else{
    	          array_push($exlist, $args[1]);
    	          $newjson = json_encode($decodes);
    	          $this->updateJson($newjson);
    	          $sender->sendMessage(TextFormat::GREEN."Player has been added to the exclusion list.");
    	          return true;
    	        }
    	      }
    	    }
    	  }
    	  elseif ($args[0] === "unexclude"){
    	    if (!isset($args[1])){
    	      $sender->sendMessage(TextFormat::RED."/simplechat unexclude <player>");
    	      return true;
    	    }
    	    else{
    	      $exlist = $decodes["exclusionlist"];
    	      if (!in_array($args[1], $exlist)){
    	        $sender->sendMessage(TextFormat::RED."Player is not in the exclusion list.");
    	        return true;
    	      }
    	      else{
    	        unset($exlist[$args[1]]);
    	        $newjson = json_encode($decodes);
    	        $this->updateJson($newJson);
    	        $sender->sendMessage(TextFormat::GREEN."Player has been removed from the exclusion list.");
    	        return true;
    	      }
    	    }
    	  }
    	  elseif ($args[0] === "exclusion"){
    	    if (!isset($args[1])){
    	      $sender->sendMessage(TextFormat::RED."/simplechat exclusion off");
    	      return true;
    	    }
    	    elseif ($args[0] === "off"){
    	      unset($decodes["exclusionlist"]);
    	      $decodes["exclusionlist"] = "off";
    	      $newjson = json_encode($decodes);
    	      $this->updateJson($newJson);
    	      $sender->sendMessage(TextFormat::GREEN."The exclusion list has been removed / off.");
    	      return true;
    	    }
    	    else{
    	      $sender->sendMessage(TextFormat::RED."/simplechat exclusion off");
    	      return true;
    	    }
    	  }
    	  elseif ($args[0] === "mode"){
    	    if (!isset($args[1])){
    	      $sender->sendMessage(TextFormat::RED."/simplechat mode <replace:warn>");
    	      return true;
    	    }
    	    elseif ($args[1] === "replace"){
    	      unset($decodes["filterYtype"]);
    	      $decodes["filterYtype"] = "replace";
    	      $newjson = json_encode($decodes);
    	      $this->updateJson($newjson);
    	      $sender->sendMessage(TextFormat::GREEN."Filter mode switched to replace.");
    	      return true;
    	    }
    	    elseif ($args[1] === "warn"){
    	      unset($decodes["filterYtype"]);
    	      $decodes["filterYtype"] = "warn";
    	      $newjson = json_encode($decodes);
    	      $this->updateJson($newJson);
    	      $sender->sendMessage(TextFormat::GREEN."Filter mode switched to warn.");
    	      return true;
    	    }
    	    else{
    	      $sender->sendMessage(TextFormat::RED."/simplechat mode <replace:warn>");
    	      return true;
    	    }
    	  }
    	}
    }
}
