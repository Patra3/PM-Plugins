<?php

namespace FancyTag;

use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use FancyTag\TagRenew;

class FancyTag extends PluginBase implements Listener {
    
    private $currenttags = array();
    private $oldtags = array();
    private $attributes = array();
    
    /*
      ______                  _______                     _____ _____ 
 |  ____|                |__   __|              /\   |  __ \_   _|
 | |__ __ _ _ __   ___ _   _| | __ _  __ _     /  \  | |__) || |  
 |  __/ _` | '_ \ / __| | | | |/ _` |/ _` |   / /\ \ |  ___/ | |  
 | | | (_| | | | | (__| |_| | | (_| | (_| |  / ____ \| |    _| |_ 
 |_|  \__,_|_| |_|\___|\__, |_|\__,_|\__, | /_/    \_\_|   |_____|
                        __/ |         __/ |                       
                       |___/         |___/   
    */
    public function registerPlayerToFancyTag($player, $tagarray){
        // registers a player to FancyTag (NAME OF PLAYER $player, ARRAY WITH TAG ORIENTATION $tagarray.);
        if (!is_file($this->getDataFolder()."/$player.json")){
            $handle = fopen($this->getDataFolder()."/$player.json", "w+");
            fwrite($handle, json_encode($tagarray));
            fclose($handle);
            return true; // success.
        }
        else{
            return false; // already registered.
        }
    }
    public function playerHasCustom($player){
        // checks a player to see if they have a custom tag setup (NAME OF PLAYER $player);
        $data = json_decode(file_get_contents($this->getDataFolder()."/$player.json"), true);
        if ($data === $this->getConfig()->get("defaulttagstyle")){
            return false; // has config default setup.
        }
        else{
            return true; // has custom nametag setup.
        }
    }
    public function registerAttribute($attributename, $value, $pluginname, $player){
        // registers an custom attribute for FancyTag (TAGNAME $attributename, VALUE (a number, text, etc.) $value, 
        // NAME OF PLUGIN $pluginname, PLAYERCLASS $player);
        if (!isset($this->attributes[$pluginname])){
            $this->attributes[$pluginname] = array();
        }
        if (!isset($this->attributes[$pluginname][$player->getName()])){
            $this->attributes[$pluginname][$player->getName()] = array();
        }
        // pluginname(FancyTag) -> playername(HotFireyDeath) -> attributename(HealthTag) = value;
        $this->attributes[$pluginname][$player->getName()][$attributename] = $value;
        return true;
    }
    public function returnTagArray($player){
        // returns the set tag array for (NAME OF PLAYER $player);
        return json_decode(file_get_contents($this->getDataFolder()."/$player.json"), true);
    }
    
    
    
    // --------------------------------------------------------
    
    private function updateAllPlayers(){
        // auto updates nametags at a fast interval :P
        $arrayt = $this->getServer()->getOnlinePlayers();
        foreach ($arrayt as $player){
            $this->registerPlayerToFancyTag($player->getName(), $this->getConfig()->get("defaulttagstyle"));
            if ($this->playerHasCustom($player->getName())){
                // call all api functions before renderTag();
                $this->registerAllAttributes($player);
                $fy = $this->renderTag($player->getName(), $this->returnTagArray($player->getName()));
                $event->getPlayer()->setNameTag("");
                foreach($fy as $rows){
                    $this->getPlayer->setNameTag($this->getPlayer()->getNameTag().$rows.\n);
                }
            }
        }
    }
    
    private function renderTag($player, $tagarray){
        $final = $tagarray;
        foreach($final as $tagrow){
            foreach($this->attributes as $pluginname){
                foreach($this->attributes[$pluginname] as $attribute){ //$attribute = player array();
                    foreach($this->attributes[$pluginname][$player->getName()] as $finalattribute){
                        str_replace(array_search($finalattribute, $this->attributes[$pluginname][$player->getName()])/*returns attributename
                        */, $finalattribute, $tagrow);
                    }
                }
            }
        }
        return $final;
    }
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
            $this->saveDefaultConfig();
        }
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new TagRenew($this), 20);
    }
    private function registerAllAttributes($player){
        // for this plugin only!!! Make your own otherwise!
        if ($this->getConfig()->get("HealthTag")[0] === "on"){
            if ($this->getConfig()->get("HealthTag")[1] === "bars"){
                if ($player->getHealth()/$player->getMaxHealth() * 100 >= "99.99"){
                    $bars = str_repeat("|", 9);
                }
                elseif ($player->getHealth()/$player->getMaxHealth() * 100 >= "88.88" and
                !$player->getHealth()/$player->getMaxHealth() * 100 >= "99.99"){
                    $bars = str_repeat("|", 8);
                }
                elseif ($player->getHealth()/$player->getMaxHealth() * 100 >= "77.77" and
                !$player->getHealth()/$player->getMaxHealth() * 100 >= "88.88"){
                    $bars = str_repeat("|", 7);
                }
                elseif ($player->getHealth()/$player->getMaxHealth() * 100 >= "66.66" and
                !$player->getHealth()/$player->getMaxHealth() * 100 >= "77.77"){
                    $bars = str_repeat("|", 6);
                }
                elseif ($player->getHealth()/$player->getMaxHealth() * 100 >= "55.55" and
                !$player->getHealth()/$player->getMaxHealth() * 100 >= "66.66"){
                    $bars = str_repeat("|", 5);
                }
                elseif ($player->getHealth()/$player->getMaxHealth() * 100 >= "44.44" and
                !$player->getHealth()/$player->getMaxHealth() * 100 >= "55.55"){
                    $bars = str_repeat("|", 4);
                }
                elseif ($player->getHealth()/$player->getMaxHealth() * 100 >= "33.33" and
                !$player->getHealth()/$player->getMaxHealth() * 100 >= "44.44"){
                    $bars = str_repeat("|", 3);
                }
                elseif ($player->getHealth()/$player->getMaxHealth() * 100 >= "22.22" and
                !$player->getHealth()/$player->getMaxHealth() * 100 >= "33.33"){
                    $bars = str_repeat("|", 2);
                }
                elseif ($player->getHealth()/$player->getMaxHealth() * 100 >= "11.11" and
                !$player->getHealth()/$player->getMaxHealth() * 100 >= "22.22"){
                    $bars = "|";
                }
                else{
                    $bars = " ";
                }
                $this->registerAttribute("HealthTag", "Health: [".TextFormat::GREEN.$bars."]", "FancyTag", $player);
            }
            elseif ($this->getConfig()->get("HealthTag")[1] === "numbers"){
                if ($player->getHealth()/$player->getMaxHealth() * 100 >= "75"){
                    $color = TextFormat::GREEN;
                }
                elseif ($player->getHealth()/$player->getMaxHealth() * 100 >= "50" and
                !$player->getHealth()/$player->getMaxHealth() * 100 >= "75"){
                    $color = TextFormat::GOLD;
                }
                elseif ($player->getHealth()/$player->getMaxHealth() * 100 >= "25"
                and !$player->getHealth()/$player->getMaxHealth() * 100 >= "50"){
                    $color = TextFormat::RED;
                }
                $this->registerAttribute("HealthTag", "Health: ".$color.$player->getHealth(), "FancyTag", $player);
            }
        }
        if ($this->getConfig()->get("ItemTag")[0] === "on"){
            $this->registerAttribute("ItemTag", "Item: ".TextFormat::GREEN.$player->getInventory()->getItemInHand()->getName(), "FancyTag", $player);
        }
        if ($this->getConfig()->get("ChatTag")[0] === "on"){
            $this->registerAttribute("ChatTag", $this->getConfig()->get("ChatTag")[2], "FancyTag", $player);
        }
        if ($this->getConfig()->get("WorldTag")[0] === "on"){
            $this->registerAttribute("WorldTag", "World: ".TextFormat::GREEN.$player->getLevel()->getName(), "FancyTag", $player);
        }
        if ($this->getConfig()->get("PositionTag")[0] === "on"){
            $pos = $player->getPosition();
            $this->registerAttribute("PositionTag", "Pos: ".TextFormat::GREEN.
            $pos->getX().TextFormat::WHITE.", ".TextFormat::GREEN.$pos->getY().TextFormat::WHITE.", ".
            TextFormat::GREEN.$pos->getZ(), "FancyTag", $player);
        }
        if ($this->getConfig()->get("VoteStatusTag")[0] === "on"){
            $data = file_get_contents("http://minecraftpocket-servers.com/api/?object=servers&element=detail&key=".
            $this->getConfig()->get("VoteStatusTag")[1]);
            if ($data === "Error: incorrect server key"){
                $this->getLogger()->warning("Server key incorrect. Disabling feature...");
            }
            else{
                $data = json_decode(file_get_contents("http://minecraftpocket-servers.com/api/?object=servers&element=voters&key=".
                $this->getConfig()->get("VoteStatusTag")[1]."&month=current&format=json"), true);
                foreach($data["voters"] as $arrays){
                    if ($arrays["nickname"] === $player->getName()){
                        $this->registerAttribute("VoteStatusTag", "Votes: ".TextFormat::GREEN.$arrays["votes"], "FancyTag", $player);
                    }
                    else{
                        $this->registerAttribute("VoteStatusTag", "Votes: ".TextFormat::RED."0", "FancyTag", $player);
                    }
                }
            }
        }
        if ($this->getConfig()->get("NameTag")[0] === "on"){
            $this->registerAttribute("NameTag", $player->getName(), "FancyTag", $player);
        }
    }
    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $this->registerPlayerToFancyTag($player->getName(), $this->getConfig()->get("defaulttagstyle"));
        if ($this->playerHasCustom($player->getName())){
            // call all api functions before renderTag();
            $this->registerAllAttributes($event->getPlayer());
            $fy = $this->renderTag($player->getName(), $this->returnTagArray($player->getName()));
            $event->getPlayer()->setNameTag("");
            foreach($fy as $rows){
                $event->getPlayer->setNameTag($event->getPlayer()->getNameTag().$rows.\n);
            }
        }
    }
}