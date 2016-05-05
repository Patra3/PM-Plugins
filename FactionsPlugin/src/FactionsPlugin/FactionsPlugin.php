<?php

namespace FactionsPlugin;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\math\Vector3;
use pocketmine\event\player\PlayerMoveEvent;

use FactionsPlugin\tasks\CheckPlayerWalkTask;

class FactionMember {
    
    public $player;
    public $rank;
    public $faction;
    
    public function __construct($player, $rank, $faction){
        $this->player = $player;
        $this->rank = $rank;
        $this->faction = $faction;
    }
}

class ClaimedLand {
    
    public $faction;
    public $x;
    public $y;
    public $z;
    
    public function __construct($faction, $x, $y, $z){
        $this->faction = $faction;
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }
}

class SingleFaction {
    
    public $plugin; //FactionsPlugin $plugin;
    public $faction_name; //String $faction_name;
    public $members = array(); //FactionMember $members array();
    public $members_array_backup = array(); //Array $members_array_backup;
    public $invited_list;
    public $ally_list;
    public $enemy_list;
    public $neutral_list;
    public $power;
    public $claimed_land;
    public $claimed_land_array_backup = array(); //Array $claimed_land_array_backup;
    public $data_file;
    
    public function __construct(String $faction_name, $owner, FactionsPlugin $plugin, $invited_list = array(), $ally_list = array(), $enemy_list = array(), $neutral_list = array(), $power = 0, $claimed_land = array(), $members = null){
        $this->faction_name = $faction_name;
        $this->plugin = $plugin;
        if (empty($members)){
            array_push($this->members, new FactionMember($owner, "Owner", $this));
        }
        elseif (!empty($members)){
            $this->members = $members;
        }
        foreach($this->members as $objs){
            if (!$objs->player instanceof Player){
                $p_in = $objs->player;
            }
            else{
                $p_in = $objs->player->getName();
            }
            if ($objs->faction instanceof SingleFaction){
                $f = $objs->faction->faction_name;
            }
            else{
                $f = $objs->faction;
            }
            array_push($this->members_array_backup, array("player" => $p_in, "rank" => $objs->rank, "faction" => $f));
        }
        unset($objs);
        $issets = false;
        foreach($this->plugin->factions_data as $central){
            //checks if the faction is already made in the array.
            if ($central->faction_name === $this->faction_name){
                $issets = true;
            }
        }
        unset($central);
        if (is_file($this->plugin->getDataFolder()."/saves/".$faction_name.".json") and $issets){
            $this->plugin->getLogger()->critical("An unexpected faction class was created!");
            $this->plugin->getServer()->shutdown();
        }
        $this->invited_list = $invited_list;
        $this->ally_list = $ally_list;
        $this->enemy_list = $enemy_list;
        $this->neutral_list = $neutral_list;
        $this->power = $power;
        $this->claimed_land = $claimed_land;
    }
    public static function loadFromSaveFile(String $file, FactionsPlugin $plugin){
        $data = json_decode(file_get_contents($file), true);
        if ($data["power"] === null){
            //corrupted power data, reset it.
            $data["power"] = 0;
        }
        $owner = null;
        /*
        foreach($data["members"] as $ar){
            if ($ar["rank"] === "Owner"){
                $owner = $ar["player"];
            }
        }
        */
        $owner = null;
        $members = array();
        $fn = $data["faction_name"];
        $invited_list = $data["invited_list"];
        $ally_list = array();
        $enemy_list = array();
        $neutral_list = array();
        $power = $data["power"];
        $land = array();
        foreach($data["claimed_land"] as $lands){
            array_push($land, new Claimedland($fn, $lands["x"], $lands["y"], $lands["z"]));
        }
        foreach($data["members"] as $membersd){
            array_push($members, new FactionMember($membersd["player"], $membersd["rank"], $membersd["faction"]));
        }
        unset($ar);
        unset($data);
        //unlink($file);
        return new SingleFaction($fn, $owner, $plugin, $invited_list, $ally_list, $enemy_list, $neutral_list, $power, $land, $members);
    }
    public function updateSave(){
        $file = $this->plugin->getDataFolder()."/saves/".$this->faction_name.".json";
        if (is_file($file)){
            unlink($file);
        }
        $h = fopen($file, "w+");
        $save_array = array("faction_name" => $this->faction_name, "members" => array(), "claimed_land" => array(), "power" => $this->power,
            "invited_list" => $this->invited_list);
        foreach($this->members as $mm){ // save all players
            if (!$mm->player instanceof Player){
                $name = $mm->player;
            }
            else{
                $name = $mm->player->getName();
            }
            if ($mm->faction instanceof SingleFaction){
                $mf = $mm->faction->faction_name;
            }
            else{
                $mf = $mm->faction;
            }
            array_push($save_array["members"], array("player" => $name, "rank" => $mm->rank, "faction" => $mf));
        }
        foreach($this->claimed_land as $lands){
            if (!$lands->faction instanceof SingleFaction){
                $faction = $lands->faction;
            }
            else{
                $faction = $lands->faction->faction_name;
            }
            array_push($save_array["claimed_land"], array("faction" => $faction, "x" => $lands->x, "y" => $lands->y, "z" => $lands->z));
        }
        fwrite($h, json_encode($save_array));
        fclose($h);
        if ($this->plugin->getConfig()->get("misc")["updateSaveNotify"]){
            $this->plugin->getLogger()->info(TextFormat::YELLOW.$this->faction_name."'s save has been updated.");
        }
    }
}

class FactionsPlugin extends PluginBase implements Listener{
    
    public $factions_data; // This will store all SingleFaction objects.
    
    public function sendable($player){
        /**
         * Returns true if player is online, false else.
         * @param String $player
         * @return boolean
         */
        foreach($this->getServer()->getOnlinePlayers() as $pl){
            if ($pl->getName() === $player){
                return true;
            }
        }
        return false;
    }
    public function acceptInvitation(Player $player, SingleFaction $faction){
        /**
         * Returns true if accepted, else false if non-existant invitation or something.
         * @param Player $player
         * @param SingleFaction $faction
         * @return boolean
         */
        foreach($this->factions_data as $data){
            if ($data->faction_name === $faction->faction_name){
                if (!in_array($player->getName(), $data->invited_list)){
                    return false;
                }
                unset($data->invited_list[array_search($player->getName(), $data->invited_list)]);
                array_push($data->members )
            }
        }
    }
    public function factionExists(String $name){
        /**
         * Returns true if exists, false otherwise.
         * @param String $name
         * @return boolean
         */
        foreach($this->factions_data as $o){
            if ($o->faction_name === $name){
                unset($o);
                return true;
            }
        }
        return false;
    }
    public function isLandClaimed($x, $z){
        /**
         * Returns an array.
         * @param $x
         * @param $z
         * @return Array
         */
        $area = array(
            "claimed" => false
        );
        $init_vector3 = new Vector3($x, PHP_INT_MAX, $z);
        foreach ($this->factions_data as $datas){
            foreach($datas->claimed_land as $lands){
                $target_vector3 = new Vector3($lands->x, PHP_INT_MAX, $lands->z);
                if ($init_vector3->distance($target_vector3) <= ($this->getConfig()->get("land")["claimsize"] - 1)){
                    $area = array(
                        "claimed" => true,
                        "claimed_by" => $datas->faction_name
                    );
                }
            }
        }
        return $area;
    }
    public function removePower(SingleFaction $faction, $amount){
        /**
         * Removes $amount of power from $faction.
         * @param SingleFaction $faction
         * @param type $amount
         */
        $faction->power = $faction->power - $amount;
    }
    public function addPower(SingleFaction $faction, $amount){
        /**
         * Adds $amount of power to $faction.
         * @param SingleFaction $faction
         * @param type $amount
         */
        $faction->power = $faction->power + $amount;
    }
    public function getPlayerRank(Player $player){
        /**
         * Returns the player rank, or null if player is not in faction.
         * @param Player $player
         * @return String/null
         */
        if (!$this->isPlayerInFaction($player)){
            return null;
        }
        else{
            foreach($this->factions_data as $datas){
                foreach($datas->members as $memb){
                    if (!$memb->player instanceof Player){
                        $name = $memb->player;
                    }
                    else{
                        $name = $memb->player->getName();
                    }
                    if ($name === $player->getName()){
                        return $memb->rank;
                    }
                }
                return null;
            }
            return null;
        }
    }
    public function isPlayerInFaction(Player $player){
        /**
         * Returns string of faction name or null if not in faction.
         * @param Player $player
         * @return String/null
         */
        $faction = null;
        foreach($this->factions_data as $o){
            foreach($o->members as $members){
                if (!$members->player instanceof Player){
                    if ($members->player === $player->getName()){
                        $faction = $o->faction_name;
                    }
                }
                else{
                    if ($members->player === $player){
                        $faction = $o->faction_name;
                    }
                }
            }
        }
        if (!empty($faction)){
            unset($o);
            unset($members);
        }
        return $faction;
    }
    private function loadData(){
        $scan = scandir($this->getDataFolder()."/saves/");
        unset($scan[array_search(".", $scan)]);
        unset($scan[array_search("..", $scan)]);
        foreach ($scan as $file){
            array_push($this->factions_data, SingleFaction::loadFromSaveFile($this->getDataFolder()."/saves/".$file, $this));
        }
        foreach($this->factions_data as $d){
            $d->updateSave();
        }
        $this->getLogger()->warning("Data loaded unstably. Bugs may occur...");
    }
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        if (!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
            $this->saveDefaultConfig();
        }
        if (!is_dir($this->getDataFolder()."/saves/")){
            mkdir($this->getDataFolder()."/saves/");
        }
        if (empty($this->factions_data)){
            $this->factions_data = array();
        }
        $this->loadData();
        $this->getLogger()->info(TextFormat::GREEN."\nFactionsPlugin loaded successfully!\n");
        
        if (!is_file($this->getDataFolder()."/config.yml")){
            $this->getLogger()->info("Configuration not detected. Creating...");
            $this->saveDefaultConfig();
        }
        if ($this->getConfig()->get("land")["blockFromWalking"]){
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new CheckPlayerWalkTask($this), 20);
        }
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        $com = $command->getName();
        if ($com === "f"){
            if (!isset($args[0])){
                $sender->sendMessage("/f help for all commands.");
                return true;
            }
            elseif ($args[0] === "create"){
                if (!isset($args[1])){
                    $sender->sendMessage("/f create <name>");
                    return true;
                }
                else{
                    if ($this->factionExists($args[1])){
                        $sender->sendMessage(TextFormat::RED."Sorry, this faction exists! Choose a different name!");
                        return true;
                    }
                    if (!$sender instanceof Player){
                        $this->getLogger()->info("Sorry, please run from in-game.");
                        return true;
                    }
                    if (!empty($this->isPlayerInFaction($sender))){
                        $sender->sendMessage(TextFormat::YELLOW."You are in a faction...");
                        return true;
                    }
                    array_push($this->factions_data, new SingleFaction($args[1], $sender->getName(), $this));
                    foreach($this->factions_data as $s){
                        $s->updateSave();
                    }
                    $sender->sendMessage(TextFormat::GREEN."Faction created!");
                    return true;
                }
            }
            elseif ($args[0] === "claim"){
                if (empty($this->isPlayerInFaction($sender))){
                    $sender->sendMessage(TextFormat::RED."You must be in a faction to claim land.");
                    return true;
                }
                else{
                    $check = $this->isLandClaimed($sender->getPosition()->getX(), $sender->getPosition()->getZ());
                    if ($check["claimed"]){
                        $sender->sendMessage(TextFormat::RED."This land is claimed by: ". $check["claimed_by"].".");
                        return true;
                    }
                    foreach($this->factions_data as $j){
                        foreach ($j->members as $mem)
                            if (!$mem->player instanceof Player){
                                $name = $mem->player;
                            }
                            else{
                                $name = $mem->player->getName();
                            if ($name === $sender->getName()){
                                if (!in_array($this->getPlayerRank($sender), array("Owner", "Officer"))){
                                    $sender->sendMessage(TextFormat::RED."You do not have permission to claim land for your faction.");
                                    return true;
                                }
                            }
                        }
                    }
                    $faction = $this->isPlayerInFaction($sender); //faction name
                    foreach ($this->factions_data as $objs){
                        if ($objs->faction_name === $faction){
                            array_push($objs->claimed_land, new ClaimedLand($objs, $sender->getPosition()->getX(), $sender->getPosition()->getY(), $sender->getPosition()->getZ()));
                            $objs->updateSave();
                        }
                    }
                    $sender->sendMessage(TextFormat::GREEN."Claimed land successfully!");
                    $sender->sendMessage(TextFormat::YELLOW.$this->getConfig()->get("land")["claim_power_cost"]." power has been taken from your faction.");
                    return true;
                }
            }
            elseif ($args[0] === "info"){
                if (empty($this->isPlayerInFaction($sender))){
                    $sender->sendMessage(TextFormat::RED."You must be in a faction!");
                    return true;
                }
                else{
                    $sender->sendMessage("Your faction: ".TextFormat::GREEN.$this->isPlayerInFaction($sender));
                    $sender->sendMessage("Your rank: ".TextFormat::YELLOW.$this->getPlayerRank($sender));
                    return true;
                }
            }
            elseif ($args[0] === "del"){
                if (!$sender->isOp()){
                    $sender->sendMessage(TextFormat::RED."You have no permission to delete factions.");
                    return true;
                }
                else{
                    if (!isset($args[1])){
                        $sender->sendMessage("/f del <faction>");
                        return true;
                    }
                    foreach($this->factions_data as $data){
                        if ($data->faction_name === $args[1]){
                            unlink($this->getDataFolder()."/saves/".$data->faction_name.".json");
                            unset($this->factions_data[array_search($data, $this->factions_data)]);
                            $sender->sendMessage(TextFormat::YELLOW."Faction deleted.");
                            return true;
                        }
                    }
                    $sender->sendMessage("Faction not found.");
                }
            }
            elseif ($args[0] === "invite"){
                if (!in_array($this->getPlayerRank($sender), array("Owner", "Officer"))){
                    $sender->sendMessage(TextFormat::RED."You do not have permission to invite!");
                    return true;
                }
                else{
                    if (!isset($args[1])){
                        $sender->sendMessage("/f invite <name>");
                        return true;
                    }
                    elseif (!$this->sendable($args[1])){
                        $sender->sendMessage(TextFormat::YELLOW."This player is currently not online. Try again later.");
                        return true;
                    }
                    elseif ($this->isPlayerInFaction($this->getServer()->getPlayer($args[1])) === $this->isPlayerInFaction($sender)){
                        $sender->sendMessage(TextFormat::YELLOW."Target is in the same faction!");
                        return true;
                    }
                    elseif (!empty($this->isPlayerInFaction($this->getServer()->getPlayer($args[1])))){
                        $sender->sendMessage(TextFormat::YELLOW."Target is already in faction.");
                        return true;
                    }
                    foreach($this->factions_data as $datas){
                        if ($datas->faction_name === $this->isPlayerInFaction($sender)){
                            if (in_array($args[1], $datas->invited_list)){
                                $sender->sendMessage(TextFormat::YELLOW."Invitation already sent.");
                                return true;
                            }
                            array_push($datas->invited_list, $args[1]);
                            $datas->updateSave();
                        }
                    }
                    $sender->sendMessage(TextFormat::GREEN.$args[1]." has been invited to join your faction!");
                    return true;
                }
            }
        }
    }
    public function onJoin(PlayerJoinEvent $event){
        // This is done for stability purposes. Updating the loaded data is nessessary.
        foreach($this->factions_data as $objs){
            foreach($objs->members as $mes){
                if (!$mes->player instanceof Player){
                    if ($mes->player === $event->getPlayer()->getName()){
                        $mes->player = $event->getPlayer(); //update string to Player object.
                        $mes->faction = $objs; //update loadFromSave's string to SingleFaction object.
                    }
                }
            }
        }
        $this->getLogger()->info("Player joined, updating loaded data..");
    }
    public function onMove(PlayerMoveEvent $event){
        $loc = $event->getTo();
        $scan = $this->isLandClaimed($loc->getX(), $loc->getZ());
        if ($scan["claimed"]){
            if ($this->isPlayerInFaction($event->getPlayer()) != $scan["claimed_by"]){
                $event->getPlayer()->sendMessage(TextFormat::RED."You are in '".$scan["claimed_by"]."''s land.");
                $event->setCancelled(true);
            }
        }
    }
}

