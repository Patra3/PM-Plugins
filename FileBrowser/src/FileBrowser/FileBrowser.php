<?php

namespace FileBrowser;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permissible;
use pocketmine\utils\TextFormat;

class FileBrowser extends PluginBase {
  public function onEnable(){
    if (!is_dir($this->getDataFolder())){
      mkdir($this->getDataFolder());
    }
    $data = array();
    $data["ftp"] = array();
    $string = json_encode($data);
    $handle = fopen($this->getDataFolder()."/data.json", "w+");
    fwrite($handle, $string);
    fclose($handle);
  }
  public function onCommand(CommandSender $sender, Command $command, $label, array $args){
    if(strtolower($command->getName()) === "filebrowser"){
      $data = file_get_contents($this->getDataFolder()."/data.json");
      $truw = json_decode($data);
      if (!isset($args[0])){
        if ($sender->hasPermission("filebrowser.main")){
          $sender->sendMessage(TextFormat::RED."/filebrowser help");
          return true;
        }
        else{
          $sender->sendMessage(TextFormat::RED."Access denied!");
          return true;
        }
      }
      elseif ($args[0] === "ftp"){
        $ftpdata = $truw["ftp"];
        if (!isset($args[1])){
          if (!isset($ftpdata["openConnections"])){
            $sender->sendMessage("[FileBrowser] No active connections exist.");
            return true;
          }
          elseif ($ftpdata["openConnections"] === "none"){
            $sender->sendMessage("[FileBrowser] No active connections exist.");
            return true;
          }
        }
        elseif ($args[1] === "connect"){
          if (!isset($args[2])){
            $sender->sendMessage(TextFormat::RED."/filebrowser connect <host> <port> <username> <password>");
            return true;
          }
          elseif (!isset($args[3])){
            $sender->sendMessage(TextFormat::RED."/filebrowser connect <host> <port> <username> <password>");
            return true;
          }
          elseif (!isset($args[4])){
            $sender->sendMessage(TextFormat::RED."/filebrowser connect <host> <port> <username> <password>");
            return true;
          }
          elseif (!isset($args[5])){
            $sender->sendMessage(TextFormat::RED."/filebrowser connect <host> <port> <username> <password>");
          }
          else{
            
          }
        }
      }
    }
}
