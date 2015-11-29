<?php

namespace Annihilator;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\item\Bow;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\event\Listener;

class Annihilator extends PluginBase implements Listener {
  /*
  This is a commissioned plugin developed by HotFireyDeath. 
  License is under the Creative Commons Attribution-ShareAlike 4.0 International.
  
  A copy of the license is available in the LICENSE.txt file.
  */
  public $killer;
  public $bow;
  public $arrowtimeshot;
  
  public function onEnable(){
    if (!is_dir($this->getDataFolder())){
      mkdir($this->getDataFolder());
    }
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
    $this->bow = new Bow(0, 1);
    $this->arrowtimeshot = array();
    if (is_file($this->getDataFolder()."/tempsave.json")){
        $this->getLogger()->info("Picking up last save data...");
        $data = file_get_contents($this->getDataFolder()."/tempsave.json");
        $this->arrowtimeshot = json_decode($data, true);
    }
  }
  public function addKillPoint($name){
    if (!is_file($this->getDataFolder().$name.".json")){
      $data = array();
      $data["kills"] = 0;
      $data["kills"] = intval($data["kills"] + 1);
      $encode = json_encode($data);
      $handle = fopen($this->getDataFolder().$name.".json", "w+");
      fwrite($handle, $encode);
      fclose($handle);
    }
    else{
      $file = file_get_contents($this->getDataFolder().$name.".json");
      $decode = json_decode($file, true);
      $decode["kills"] = intval($decode["kills"] + 1);
      $encode = json_encode($decode);
      unlink($this->getDataFolder().$name.".json");
      $handle = fopen($this->getDataFolder().$name.".json", "w+");
      fwrite($handle, $encode);
      fclose($handle);
    }
  }
  public function checkEg($name){
    $data = file_get_contents($this->getDataFolder().$name.".json");
    $decode = json_decode($data, true);
    if (intval($decode["kills"]) === 7){
      return true;
    }
    else{
      return false;
    }
  }
  public function resetKillPoint($name){
    if (!is_file($this->getDataFolder().$name.".json")){
      $data = array();
      $data["kills"] = 0;
      $encode = json_encode($data);
      $handle = fopen($this->getDataFolder().$name.".json", "w+");
      fwrite($handle, $encode);
      fclose($handle);
      return true;
    }
    else{
      unlink($this->getDataFolder().$name.".json");
      $handle = fopen($this->getDataFolder().$name.".json", "w+");
      $data = array();
      $data["kills"] = 0;
      $encode = json_encode($data);
      fwrite($handle, $encode);
      fclose($handle);
      return true;
    }
  }
  public function annihilator($name){
    $this->getServer()->getPlayer($name)->sendMessage("You have obtained the >>Annihilator<<!");
    $data = file_get_contents($this->getDataFolder().$name.".json");
    $decode = json_decode($data, true);
    $decode["annihilator"] = "yes";
    unlink($this->getDataFolder().$name.".json");
    $encode = json_encode($decode);
    $handle = fopen($this->getDataFolder().$name.".json", "w+");
    fwrite($handle, $encode);
    fclose($handle);
    $player = $this->getServer()->getPlayer($name);
    $player->getInventory()->addItem($this->bow); //bow
    $player->getInventory()->addItem(Item::get(262, 0, 3)); //arrow
    $this->arrowtimeshot[$name] = 3;
    return true;
  }
  public function detransform($name){
    $this->getServer()->getPlayer($name)->sendMessage("You've run out of shots!");
    $data = file_get_contents($this->getDataFolder().$name.".json");
    $decode = json_decode($data, true);
    unset($decode["annihilator"]);
    $encode = json_encode($decode);
    unlink($this->getDataFolder().$name.".json");
    $handle = fopen($this->getDataFolder().$name.".json");
    fwrite($handle, $encode);
    fclose($handle);
    $this->resetKillPoint($name);
    return true;
  }
  public function onBowShoot(EntityShootBowEvent $event){
    $player = $event->getEntity()->getName();
    $ps = $event->getEntity();
    
    $data = file_get_contents($this->getDataFolder().$player.".json");
    $decode = json_decode($data, true);
    if (!isset($decode["annihilator"])){
        return true;
    }
    if ($decode["annihilator"] === "yes"){
      $event->setForce(100);
      $this->arrowtimeshot[$player] = intval($this->arrowtimeshot[$player]) - 1;
      $this->getServer()->getPlayer($player)->sendMessage("You have ".$this->arrowtimeshot[$player]." shots left.");
      if (intval($this->arrowtimeshot[$player]) <= "0"){
        $ps->getInventory()->removeItem($this->bow);
        $ps->getInventory()->removeItem(Item::get(262, 0, 3));
        $this->detransform($player);
        return true;
      }
      return true;
    }
  }
  public function onHold(PlayerItemHeldEvent $event){
    $item = $event->getItem();
    $player = $event->getPlayer();
    if ($item instanceof Bow){
      if (!is_file($this->getDataFolder().$player->getName().".json")){
          return true;
      }
      $data = file_get_contents($this->getDataFolder().$player->getName().".json");
      $decode = json_decode($data, true);
      if (isset($decode["annihilator"])){
        if ($decode["annihilator"] != "yes"){
          return true;
        }
        $player->sendMessage(TextFormat::BLUE.">> ".TextFormat::RED."Annihilator ".TextFormat::BLUE." <<");
        return true;
      }
      
    }
  }
  public function onHurtf(EntityDamageEvent $event){
    //$cause = $event->getCause();
    
    $pr = $event->getEntity();
    $cause = $pr->getLastDamageCause();
    if ($cause instanceof EntityDamageByEntityEvent){
      $dam = $event->getDamager()->getName();
      $ent = $event->getEntity()->getPlayer()->getName();
      $this->killer[$dam] = strval($ent);
      
      if ($dam instanceof Player){
        $ms = $dam;
        if (!\is_file($this->getDataFolder().$ms.".json")){
          return true;
        }
        $hhd = file_get_contents($this->getDataFolder().$ms.".json");
        $dec = json_decode($hhd, true);
        if (isset($dec["annihilator"])){
          if ($dec["annihilator"] === "yes"){
            $event->setDamage(PHP_INT_MAX);
            return true;
          }
        }
      }
    }
  }
  public function onDeath(PlayerDeathEvent $event){
    $pr = $event->getEntity();
    $pln = $pr->getName();
    //$cc = $pr->getLastDamageCause();
    $ps = array_search($pln, $this->killer);
    $lss = $this->getServer()->getPlayer($ps);
    if (!($lss instanceof Player)){
      return true;
    }
    elseif ($lss->isCreative()){
      return true;
    }
    $na = $ps;
    //grab stuffed teddies :P
    if (is_file($this->getDataFolder().$na.".json")){
        $grab = file_get_contents($this->getDataFolder().$na.".json");
        $de = json_decode($grab, true);
    }
    
    if (isset($de["annihilator"])){
        return true;
    }
    
    $this->addKillPoint($na);
    $grab = file_get_contents($this->getDataFolder().$na.".json");
    $de = json_decode($grab, true);
    $lss->sendMessage("Kills: ".$de["kills"]."/7");
    $this->resetKillPoint($pln);
    
    if (intval($de["kills"]) >= "7"){
      $this->annihilator($na);
      return true;
    }
  }
  
  public function onDisable(){
      if (empty($this->arrowtimeshot)){
          return true;
      }
      else{
          $this->getLogger()->info("Saving data...");
          $data = json_encode($this->arrowtimeshot);
          $handle = fopen($this->getDataFolder()."/tempsave.json", "w+");
          fwrite($handle, $data);
          fclose($handle);
      }
  }
}
