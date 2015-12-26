<?php

namespace SurvivalGames;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;

class SurvivalGames extends PluginBase implements Listener {
    private $controlpanel = array();
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
            $this->saveDefaultConfig();
        }
        if (!is_file($this->getDataFolder()."/data.json")){
            $data = array("games" => array());
            $handle = fopen($this->getDataFolder()."/data.json", "w+");
            fwrite($handle , json_encode($data));
            fclose($handle);
        }
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if (strtolower($command->getName()) === "sg"){
            if (!isset($args[0])){
                $sender->sendMessage("Use /sg help for a full list of commands.");
                return true;
            }
            elseif ($args[0] === "help"){
                $sender->sendMessage("/sg cp : Access the Survival Games control panel.");
                $sender->sendMessage("/sg join <id> : Join a Survival Game instance.");
                $sender->sendMessage("/sg stats <player> : View a player's stats.");
                $sender->sendMessage("/sg kit <name> : Get a kit.");
            }
            elseif ($args[0] === "cp"){
                array_push($this->cp, $sender->getName());
                $sender->sendMessage(TextFormat::RED."-Survival Games ++ Control Panel-");
                $sender->sendMessage("You are now in Control Panel mode, switch back to normal by entering #exit.");
                $sender->sendMessage("List of commands: #help");
            }
        }
    }
    public function onChat(PlayerChatEvent $event){
        if (strstr($event->getMessage(), "#")){
            $player = $event->getPlayer(); // i wrote all of this while a kid was presenting krokodil.
            // ugh disgusting...
            if ($event->getMessage() === "#help"){
                $player->sendMessage("#create : Create a new survival games instance.");
                $player->sendMessage("#addnewplayerspawn <id> : Adds a new survival game spawn.");
            }
            elseif ($event->getMessage() === "#create"){
                $instance = json_decode(file_get_contents($this->getDataFolder()."/data.json"), true);
                $prev = count($instance["games"]) + 1;
                array_push($instance["games"], array("id" => count($instance["games"]) + 1, "spawns" => array()));
                $player->sendMessage("A new survival game instance was generated. ID: ".array_search(
                    array("id" => $prev, "spawns" => array())));
                unlink($this->getDataFolder()."/data.json");
                $ahdn  =  fopen($this->getDataFolder()."/data.json", "w+");
                fwrite($ahdn, json_encode($instance));
                fclose($ahdn);
            }
            elseif (strpos($event->getMessage(), "#addnewplayerspawn")){
                $thing = str_replace("#addnewplayerspawn", "", $event->getMessage()); //should return the round id.
                if (!is_numeric($thing){
                    $player->sendMessage("The survival game ID,".$thing.", is invalid.");
                }
                else{
                    $instance = json_decode(file_get_contents($this->getDataFolder()."/data.json"), true);
                    $pos = $player->getPosition();
                    foreach ($instance["games"] as $gamearray){
                        if ($gamearray["id"] == $thing){
                            array_push($gamearray["spawns"], array("x" => $pos->x, "y" => $pos->y, "z" => $pos->z));
                            $player->sendMessage("A new spawn at ".$pos->x,", ".$pos->y.", ".$pos->z." was added to game ID ".$thing.".");
                        }
                    }
                }
            }
            elseif ($event->getMessage() === "#list"){
                $instance = json_decode(file_get_contents($this->getDataFolder()."/data.json"), true);
                foreach($instance["games"] as $game){
                    
                }
            }
        }
    }
}