<?php

namespace TimeZones;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class TimeZones extends PluginBase {
    public $colorlist = array("black" => "§0", "dark_blue" => "§1", "dark_green" => "§2",
    "dark_aqua" => "§3", "dark_red" => "§4", "dark_purple" => "§5", "gold" => "§6", 
    "gray" => "§7", "dark_gray" => "§8", "blue" => "§9", "green" => "§a", "aqua" => "§b",
    "red" => "§c", "light_purple" => "§d", "yellow" => "§e", "white" => "§f");
    public function onEnable(){
        if (!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
            $this->saveDefaultConfig();
        }
    }
    public function getTime(){
        date_default_timezone_set($this->getConfig()->get("timezone"));
        return date("g:i a");
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if (strtolower($command->getName()) === "tz"){
            $sender->sendMessage($this->colorlist[strtolower($this->getConfig()->get("color"))].$this->getTime());
            return true;
        }
    }
}