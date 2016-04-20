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

class CooldownClass {
    
    public $sheep;
    public $seconds;
    
    public function __construct($sheep, $seconds){
        $this->sheep = $sheep;
        $this->seconds = $seconds;
    }
    public function updateSeconds($new_seconds){
        $this->seconds = $new_seconds;
    }
}

class SheepFix extends PluginBase implements Listener {
    
    public $cooldown;
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
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
        $event_is = false;
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
                        $event_is = true;
                        $d->getInventory()->addItem(new ItemBlock(new Wool(), 0, rand(1, 3)));
                        array_push(self::$cooldown, new CooldownClass($sheep, $this->getConfig()->get("cooldown")));
                    }
                }
            }
        }
        if ($event_is){
            $event->setCancelled();
        }
    }
    public function setCooldownArray($array){
        $this->cooldown = $array;
    }
}