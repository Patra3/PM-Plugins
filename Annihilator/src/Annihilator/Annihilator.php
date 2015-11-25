<?php

namespace Annihilator;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item;
use pocketmine\Player;
use pocketmine\event\Listener;

$killer = array();
$arrowtimeshot = array();

class Annihilator extends PluginBase implements Listener {
  /*
  This is a commissioned plugin developed by HotFireyDeath. 
  License is under the Creative Commons Attribution-ShareAlike 4.0 International.
  
  A copy of the license is available in the LICENSE.txt file.
  */
  public function onEnable(){
    if (!is_dir($this->getDataFolder())){
      mkdir($this->getDataFolder());
    }
    $this->getServer()->getPluginManager()->registerEvents($this, $this);
  }
  public function addKillPoint($name){
    if (!is_file($name.".json")){
      $data = array();
      $data["kills"] = 0;
      $data["kills"] = $data["kills"] + 1;
      $encode = json_encode($data);
      $handle = fopen($name.".json", "w+");
      fwrite($handle, $encode);
      fclose($handle);
    }
    else{
      $file = file_get_contents($name.".json");
      $decode = json_decode($file, true);
      $decode["kills"] = $decode["kills"] + 1;
      $encode = json_encode($decode);
      unlink($name.".json");
      $handle = fopen($name.".json", "w+");
      fwrite($handle, $encode);
      fclose($handle);
    }
  }
  public function checkEg($name){
    $data = file_get_contents($name.".json");
    $decode = json_decode($data, true);
    if (intval($decode["kills"]) === 7){
      return true;
    }
    else{
      return false;
    }
  }
  public function resetKillPoint($name){
    if (!is_file($name.".json")){
      $data = array();
      $data["kills"] = 0;
      $encode = json_encode($data);
      $handle = fopen($name.".json", "w+");
      fwrite($handle, $encode);
      fclose($handle);
    }
    else{
      unlink($name.".json");
      $handle = fopen($name.".json", "w+");
      $data = array();
      $data["kills"] = 0;
      $encode = json_encode($data);
      fwrite($handle, $encode);
      fclose($handle);
    }
  }
  public function annihilator($name){
    $data = file_get_contents($name.".json");
    $decode = json_decode($data, true);
    $decode["annihilator"] = "yes";
    unlink($name.".json");
    $encode = json_encode($decode);
    $handle = fopen($name.".json", "w+");
    fwrite($handle, $encode);
    fclose($handle);
    $player = $this->getServer()->getPlayer($name);
    $bow = new Bow(0, 1);
    $player->getInventory()->addItem($bow);
    $arrowtimeshot[$name] = 3;
    return true;
  }
  public function onBowShoot(EntityShootBowEvent $event){
    $player = $event->getEntity()->getName();
    $data = file_get_contents($player.".json");
    $decode = json_decode($data, true);
    if ($decode["annihilator"] === "yes"){
      $event->setForce(100);
      $arrowtimeshot[$player] = intval($arrowtimeshot[$player]) - 1;
      return true;
    }
  }
  public function onHold(PlayerItemHeldEvent $event){
    $item = $event->getItem();
    $player = $event->getPlayer();
    if ($item instanceof Bow){
      $data = file_get_contents($player->getName().".json");
      $decode = json_decode($data, true);
      if ($decode["annihilator"] != "yes"){
        return true;
      }
      $sender->sendMessage(TextFormat::BLUE.">> ".TextFormat::RED."Annihilator ".TextFormat::BLUE." <<");
      return true;
    }
  }
  public function onHurtf(EntityDamageEvent $event){
    //$cause = $event->getCause();
    
    $pr = $event->getEntity();
    if ($event instanceof EntityDamageByEntityEvent){
      $dam = $event->getDamager();
      if ($dam instanceof Player){
        $ms = $dam->getName();
        if (!is_file($ms.".json")){
          return true;
        }
        $hhd = file_get_contents($ms.".json");
        $dec = json_decode($hhd, true);
        if (isset($decode["annihilator"])){
          if ($decode["annihilator"] === "yes"){
            $event->setDamage(PHP_INT_MAX);
            return true;
          }
        }
      }
    }
  }
  public function onEntityEntity(EntityDamageByEntityEvent $event){
    $damager = $event->getDamager();
    $ent = $event->getEntity()->getName();
    $killer[$damager] = $ent;
    return true;
  }
  public function onDeath(PlayerDeathEvent $event){
    $pr = $event->getEntity();
    $pln = $pr->getName();
    $cc = $pr->getLastDamageCause();
    $ps = array_search($pln, $killer);
    if (!($ps instanceof Player)){
      return true;
    }
    elseif ($ps->isCreative()){
      return true;
    }
    $na = $ps->getName();
    $this->addKillPoint($na);
    $this->resetKillPoint($pln);
    $vals = $this->checkEg($na);
    
    if ($vals){
      $this->annihilator($na);
    }
    else{
      return true;
    }
  }
}
