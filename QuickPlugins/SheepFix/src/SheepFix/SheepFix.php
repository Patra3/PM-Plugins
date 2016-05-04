<?php

namespace SheepFix;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\item\ItemBlock;
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
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new CooldownTask($this), 20);
        $this->setCooldownArray(array());
    }
    public function onItemHeld(PlayerItemHeldEvent $event){
        $item = $event->getItem();
        if ($item->getId() === Item::SHEARS){
            $player = $event->getPlayer();
            $player->sendPopup(TextFormat::GREEN."Hit a sheep with your shears to shear sheep.");
        }
    }
    public function onDamage(EntityDamageEvent $event){
        $event_is = false;
        $c = $event->getEntity()->getLastDamageCause(); //sheep's last dmg cause
        $sheep = $event->getEntity();
        if ($this->isSheepInCooldown($sheep)){
            $p = $event->getEntity()->getLastDamageCause()->getDamager(); //player
            $p->sendPopup(TextFormat::YELLOW."Sheep is in cooldown!");
            $event->setCancelled();
            return true;
        }
        if ($event->getEntity() instanceof Sheep){
            if ($c instanceof EntityDamageByEntityEvent){
                $d = $c->getDamager(); //player..
                if ($d instanceof Player){
                    $itemf = $d->getInventory()->getItemInHand()->getId();
                    if ($itemf === Item::SHEARS){
                        $event_is = true;
                        $amount = rand(1, 3);
                        $d->getInventory()->addItem(new ItemBlock(new Wool(), 0, $amount));
                        array_push($this->cooldown, new CooldownClass($sheep, $this->getConfig()->get("cooldown")));
                        $d->sendPopup(TextFormat::GREEN."Sheared and got ".$amount." wool!");
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
    private function isSheepInCooldown($sheep){
        foreach ($this->cooldown as $objs){
            if ($sheep === $objs->sheep){
                return true;
            }
        }
        return false;
    }
    public function onDisable(){
        foreach ($this->cooldown as $objs){
            $objs->sheep->setNameTag("");
        }
        $this->getLogger()->info("Sheep cooldowns reset!");
    }
}