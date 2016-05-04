<?php

namespace FastArmor;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Armor;
use pocketmine\item\Item;

class FastArmor extends PluginBase implements Listener {
    public $helms = array(
        Item::LEATHER_CAP,
        Item::CHAIN_HELMET,
        Item::IRON_HELMET,
        Item::DIAMOND_HELMET,
        Item::GOLD_HELMET
        );
    public $chestplates = array(
        Item::LEATHER_TUNIC,
        Item::CHAIN_CHESTPLATE,
        Item::IRON_CHESTPLATE,
        Item::DIAMOND_CHESTPLATE,
        Item::GOLD_CHESTPLATE
        );
    public $leggings = array(
        Item::LEATHER_PANTS,
        Item::CHAIN_LEGGINGS,
        Item::IRON_LEGGINGS,
        Item::DIAMOND_LEGGINGS,
        Item::GOLD_LEGGINGS
        );
    public $boots = array(
        Item::LEATHER_BOOTS,
        Item::CHAIN_BOOTS,
        Item::IRON_BOOTS,
        Item::DIAMOND_BOOTS,
        Item::GOLD_BOOTS
        );
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    public function onInteract(PlayerInteractEvent $event){
        if ($event->getItem() instanceof Armor){
            if (in_array($event->getItem()->getId(), $this->helms)){
                // Is helmet.
                $event->getPlayer()->getInventory()->setHelmet($event->getItem());
            }
            elseif (in_array($event->getItem()->getId(), $this->chestplates)){
                // Is chestplate.
                $event->getPlayer()->getInventory()->setChestplate($event->getItem());
            }
            elseif (in_array($event->getItem()->getId(), $this->leggings)){
                // Is leggings.
                $event->getPlayer()->getInventory()->setLeggings($event->getItem());
            }
            elseif (in_array($event->getItem()->getId(), $this->boots)){
                // Is boots.
                $event->getPlayer()->getInventory()->setBoots($event->getItem());
            }
        }
    }
}

?>