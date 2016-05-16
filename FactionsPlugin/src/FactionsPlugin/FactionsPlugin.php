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
use FactionsPlugin\tasks\NametagTask;
use FactionsPlugin\ClaimedLand;
use FactionsPlugin\FactionMember;
use FactionsPlugin\SingleFaction;

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
    public function denyInvitation(Player $player, SingleFaction $faction){
        /**
         * Returns true if denied, else false if non-existant invitation or something.
         * @param Player $player
         * @param SingleFaction $faction
         * @return boolean
         */
        $fac = $this->factions_data[array_search($faction, $this->factions_data)];
        $fac_m = $fac->invited_list;
        unset($fac_m[array_search($player->getName(), $fac_m)]);
        $this->factions_data[array_search($faction, $this->factions_data)]->invited_list = $fac_m;
        return true;
    }
    public function acceptInvitation(Player $player, SingleFaction $faction){
        /**
         * Returns true if accepted, else false if non-existant invitation or something.
         * @param Player $player
         * @param SingleFaction $faction
         * @return boolean
         */
        $accepted = false;
        foreach($this->factions_data as $data){
            if ($accepted){
                $this->denyInvitation($player, $data);
            }
            if ($data->faction_name === $faction->faction_name){
                if (!in_array($player->getName(), $data->invited_list)){
                    return false;
                }
                unset($data->invited_list[array_search($player->getName(), $data->invited_list)]);
                array_push($data->members, new FactionMember($player, "Member", $data));
                $accepted = true;
            }
            $data->updateSave($data);
        }
        return $accepted;
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
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new NametagTask($this), 20);
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
            elseif ($args[0] === "accept"){
                if (!isset($args[1])){
                    $sender->sendMessage("/f accept <faction>");
                    return true;
                }
                else{
                    if (!empty($this->isPlayerInFaction($sender))){
                        $sender->sendMessage(TextFormat::RED."You are already in a faction!");
                        return true;
                    }
                    foreach($this->factions_data as $datas){
                        if ($datas->faction_name === $args[1]){
                            if (in_array($sender->getName(), $datas->invited_list)){
                                $this->acceptInvitation($sender, $datas);
                                $sender->sendMessage(TextFormat::GREEN."You have joined faction '".$datas->faction_name."'!");
                                $datas->updateSave();
                                return true;
                            }
                            else{
                                $sender->sendMessage(TextFormat::RED."That faction did not invite you, or the invitation expired.");
                                return true;
                            }
                        }
                    }
                    $sender->sendMessage(TextFormat::RED."We ran into an error. Try again later.");
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
        $this->getLogger()->info("Player joined, updated cached data..");
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

