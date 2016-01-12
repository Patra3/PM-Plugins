<?php

namespace ChatUtils;

use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;

class ChatUtils extends PluginBase implements Listener {
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (!is_dir($this->getDataFolder())){
            $this->setup();
        }
        if (!is_file($this->getDataFolder()."/reset.txt")){
            $this->setup();
        }
    }
    public function getChatID($message){
        // API function for developers or later use.
        // Returns the Chat ID by message.
        $data = json_decode(file_get_contents($this->getDataFolder()."/log.json"), true);
        $key = array_search($message, $data);
        if ($key){
            return $key;
        }
        else{
            return false;
        }
    }
    private function setup(){
        mkdir($this->getDataFolder());
        mkdir($this->getDataFolder()."/savedata/");
        $handle = fopen($this->getDataFolder()."/reset.txt", "w+");
        fwrite($handle, "This file is simply for data reset. If you delete this file, I will attempt".
        " to re-create the contents in this datafolder.");
        fclose($handle);
    }
    public function onChat(PlayerChatEvent $event){
        $message = $event->getMessage();
        if ($this->getConfig()->get("log") === "on"){
            if (!is_file($this->getDataFolder()."/log.json")){
                $data = array();
                $handle = fopen($this->getDataFolder()."/log.json", "w+");
                fwrite($handle, json_encode($data));
                fclose($handle);
            }
            else{
                $data = json_decode(file_get_contents($this->getDataFolder()."/log.json"), true);
                array_push($data, $message);
                unlink($this->getDataFolder()."/log.json");
                $handle = fopen($this->getDataFolder()."/log.json", "w+");
                fwrite($handle, json_encode($data));
                fclose($handle);
            }
        }
        $xx1 = explode(" ", $message);
        foreach($xx1 as $words){
            if ($words[0] === "!"){
                // ===========  MATH OPERATORS ===========
                if ($words[1] === "a" && $words[2] === "d" && $words[3] === "d"){
                    // They did !add, get the data and calculate now.
                    $xx2 = explode("/", $words);
                    $paste = array();
                    foreach($xx2 as $text){
                        if (is_numeric($text)){
                            array_push($paste, intval($text));
                        }
                    }
                    $final_number = array_rand($paste);
                    unset($paste[array_search($final_number, $paste)]);
                    foreach($paste as $numbers){
                        $final_number = $final_number + $numbers;
                    }
                    $event->setMessage(str_replace($words, $final_number, $event->getMessage()));
                }
                elseif ($words[1] === "s" && $words[2] === "u" && $words[3] === "b" && $words[4] === "t"){
                    // They did !subt, get the data and calculate now.
                    $xx2 = explode("/", $words);
                    $paste = array();
                    foreach($xx2 as $text){
                        if (is_numeric($text)){
                            array_push($paste, intval($text));
                        }
                    }
                    $final_number = array_rand($paste);
                    unset($paste[array_search($final_number, $paste)]);
                    foreach($paste as $numbers){
                        $final_number = $final_number - $numbers;
                    }
                    $event->setMessage(str_replace($words, $final_number, $event->getMessage()));
                }
                elseif ($words[1] === "m" && $words[2] === "u" && $words[3] === "l" && $words[4] === "t"){
                    // They did !mult, get the data and calculate now.
                    $xx2 = explode("/", $words);
                    $paste = array();
                    foreach($xx2 as $text){
                        if (is_numeric($text)){
                            array_push($paste, intval($text));
                        }
                    }
                    $final_number = array_rand($paste);
                    unset($paste[array_search($final_number, $paste)]);
                    foreach($paste as $numbers){
                        $final_number = $final_number * $numbers;
                    }
                    $event->setMessage(str_replace($words, $final_number, $event->getMessage()));
                }
                elseif ($words[1] === "d" && $words[2] === "i" && $words[3] === "v"){
                    // They did !subt, get the data and calculate now.
                    $xx2 = explode("/", $words);
                    $paste = array();
                    foreach($xx2 as $text){
                        if (is_numeric($text)){
                            array_push($paste, intval($text));
                        }
                    }
                    $final_number = array_rand($paste);
                    unset($paste[array_search($final_number, $paste)]);
                    foreach($paste as $numbers){
                        $final_number = $final_number / $numbers;
                    }
                    $event->setMessage(str_replace($words, $final_number, $event->getMessage()));
                }
                
                // =========== ID & QUOTES ===========
                elseif ($words[1] === "i" && $words[2] === "d"){
                    $texture = explode("/", $words);
                }
                foreach($texture as $cod){
                    
                }
                
                // =========== COLOR CODES ===========
                $colors = array("black" => "§0", "dark_blue" => "§1", "dark_green" => "§2",
    "dark_aqua" => "§3", "dark_red" => "§4", "dark_purple" => "§5", "gold" => "§6", 
    "gray" => "§7", "dark_gray" => "§8", "blue" => "§9", "green" => "§a", "aqua" => "§b",
    "red" => "§c", "light_purple" => "§d", "yellow" => "§e", "white" => "§f");
                $ys = str_replace($words[0], "", $words); // should be just a color msg, strips off the !.
                if (array_key_exists(strtolower($ys))){
                    $event->setMessage(str_replace($words, $colors[strtolower($ys)], $event->getMessage()));
                }
                
                // =========== EMOJI REPLACES ===========
                $emojis = array("smile" => "😀", "laugh" => "😆", "wink" => "😉", "smirk" => "😏",
                "kiss" => "😗", "tongue" => "😛", "cry" => "😢", "grin" => "😀");
                $ys = str_replace($words[0], "", $words); // should be just a color msg, strips off the !.
                if (array_key_exists(strtolower($ys))){
                    $event->setMessage(str_replace($words, $emojis[strtolower($ys)], $event->getMessage()));
                }
            }
            elseif ($words[0] === "@"){
                $username = str_replace($words[0], "", $words);
                
            }
        }
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if (strtolower($command->getName()) === "id"){
            if ($this->getConfig()->get("log") === "on"){
                if (!isset($args[0])){
                    $sender->sendMessage("/id <number>");
                    return true;
                }
                else{
                    $data = json_decode(file_get_contents($this->getDataFolder()."/log.json"), true);
                    if (array_key_exists($args[0], $data)){
                        $sender->sendMessage("|Chat #".$args[0]." |:".$data[$args[0]]);
                        return true;
                    }
                    else{
                        $sender->sendMessage(TextFormat::RED."The requested chat could not be found.");
                        return true;
                    }
                }
            }
            else{
                $sender->sendMessage(TextFormat::RED."This functionality has been disabled by the server owner.");
                return true;
            }
        }
    }
}