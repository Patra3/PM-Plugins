<?php

namespace FactionsPlugin;

use FactionsPlugin\FactionsPlugin;

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