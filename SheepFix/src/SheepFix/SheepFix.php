<?php

namespace SheepFix;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\Shears;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\entity\Sheep;
use pocketmine\block\Wool;
use SheepFix\CooldownTask;

class SheepFix extends PluginBase implements Listener {
    
    public static $cooldown = array();
    
    public function onEnable(){
        if(!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
            $this->saveDefaultConfig();
        }
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new CooldownTask($this), 10);
    }
    public function onItemHeld(PlayerItemHeldEvent $event){
        $item = $event->getItem();
        if ($item->getId() === Item::SHEARS){
            $player = $event->getPlayer();
            $player->sendMessage(TextFormat::GREEN."Hit a sheep with your shears to shear sheep.");
        }
    }
    public function onDamage(EntityDamageEvent $event){
        $c = $event->getEntity()->getLastDamageCause(); //sheep's last dmg cause
        $sheep = $event->getEntity();
        if ($this->isSheepInCooldown($sheep)){
            return true;
        }
        if ($event->getEntity() instanceof Sheep){
            if ($c instanceof EntityDamageByEntityEvent){
                $d = $c->getDamager(); //player..
                if ($d instanceof Player){
                    $itemf = $d->getInventory()->getItemInHand()->getId();
                    if ($itemf === Item::SHEARS){
                        $d->getInventory()->addItem(new ItemBlock(new Wool(), 0, rand(1, 3)));
                        array_push(self::$cooldown, array($sheep, $this->getConfig()->get("cooldown")));
                        $event->setCancelled();
                    }
                }
            }
        }
    }
    private function isSheepInCooldown($entity){
        foreach(self::$cooldown as $arra){
            if ($arra[0] === $entity){
                return true;
            }
        }
        return false;
    }
    public function setCooldownArray($array){
        self::$cooldown = $array;
    }
}