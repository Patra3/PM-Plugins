<?php

namespace TapCommand;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

class TapCommand extends PluginBase implements Listener{
    private $tapwait = array();
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
        }
        if (!is_file($this->getDataFolder()."/data.json")){
            $data = array();
            $hf = fopen($this->getDataFolder()."/data.json", "w+");
            fwrite($hf, json_encode($data));
            fclose($hf);
        }
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if ($command->getName() === "tc"){
            if (!isset($args[0])){
                $sender->sendMessage("/tc add <command> : Add command to a block.");
                $sender->sendMessage("/tc delete : Deletes commands from block.");
                return true;
            }
            elseif ($args[0] === "add"){
                if (!isset($args[1])){
                    $sender->sendMessage("/tc add <command>");
                    return true;
                }
                else{
                    $moved_args = array();
                    foreach($args as $argss){
                        array_push($moved_args, $argss);
                    }
                    unset($moved_args[array_search("add", $moved_args)]);
                    $command = implode(" ", $moved_args);
                    array_push($this->tapwait, array("plyr" => $sender->getName(), "command" => $command));
                    $sender->sendMessage("Tap on the block to add the command to..");
                    return true;
                }
            }
            elseif ($args[0] == "delete"){
                if (!isset($args[1])){
                    $sender->sendMessage("/tc delete");
                    return true;
                }
                else{
                    array_push($this->tapwait, array("plyrv" => $sender->getName(), "dell" => "yes"));
                    $sender->sendMessage("Tap on the block to delete commands from..");
                    return true;
                }
            }
        }
    }
    public function onInteract(PlayerInteractEvent $event){
        $playername = $event->getPlayer()->getName();
        $block = $event->getBlock(); //block
        foreach($this->tapwait as $array){
            if (isset($array["plyrv"])){
                $data = json_decode(file_get_contents($this->getDataFolder()."/data.json"), true);
                foreach($data as $arrays){
                    if ($arrays["x"] == $block->getX() and $arrays["y"] == $block->getY() and $arrays["z"] == $block->getZ()){
                        unset($data[array_search($arrays, $data)]);
                        unlink($this->getDataFolder()."/data.json");
                        $hf = fopen($this->getDataFolder()."/data.json", "w+");
                        fwrite($hf, json_encode($data));
                        fclose($hf);
                        unset($this->tapwait[array_search($array. $this->tapwait)]);
                        $event->getPlayer()->sendMessage("Block successfully deleted.");
                        return true;
                    }
                }
            }
            if ($array["plyr"] == $playername){
                $data = json_decode(file_get_contents($this->getDataFolder()."/data.json"), true);
                array_push($data, array("x" => $block->getX(), "y" => $block->getY(), "z" => $block->getZ(), "command" => $array["command"]));
                unlink($this->getDataFolder()."/data.json");
                $hf = fopen($this->getDataFolder()."/data.json", "w+");
                fwrite($hf, json_encode($data));
                fclose($hf);
                unset($this->tapwait[array_search($array, $this->tapwait)]);
                $event->getPlayer()->sendMessage("Block successfully set.");
                return true;
            }
        }
        $data = json_decode(file_get_contents($this->getDataFolder()."/data.json"), true);
        foreach($data as $blockar){ 
            if ($blockar["x"] == $block->getX() and $blockar["y"] == $block->getY() and $blockar["z"] == $block->getZ()){
                $this->getServer()->dispatchCommand($event->getPlayer(), $blockar["command"]);
            }
        }
    }
}