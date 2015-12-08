<?php

namespace ServerAuthPlus;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use ServerAuth\Events\ServerAuthAuthenticateEvent;
use pocketmine\event\player\PlayerChatEvent;
use ServerAuth\Events\ServerAuthRegisterEvent;
use ServerAuth\ServerAuth;

class ServerAuthPlus extends PluginBase {
    
    private $temp_nametags = array();
    private $makeup = array();
    
    public function onEnable(){
        if (ServerAuth::VERSION != "2.11"){
            $this->getLogger()->warning("Please use the latest version of ServerAuth!");
        }
        else{
            $this->getLogger()->info("ServerAuth 2.11 detected!");
        }
        if (!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
            $this->saveDefaultConfig();
        }
    }
    public function onJoin(){
        $this->temp_nametags[$event->getPlayer()->getName()] = $event->getPlayer()->getNameTag();
        $event->getPlayer()->setNameTag(TextFormat::GREEN.$event->getPlayer()->getName().
        TextFormat::WHITE." is logging in...");
    }
    public function onAuthenticate(ServerAuthAuthenticateEvent $event){
        $event->getPlayer()->setNameTag($this->temp_nametags[$event->getPlayer()->getName()]);
    }
    public function onRegister(ServerAuthRegisterEvent $event){
        if ($this->getConfig()->get("limitchars") === "on"){
            $password = $event->getPassword();
            if (!preg_match("#^[a-zA-Z0-9]+$#", $text)) {
                // has extra chars.
                $event->getPlayer()->sendMessage("Your password has extra characters. Enter another password in-chat.");
                array_push($this->makeup, $event->getPlayer()->getName());
                $event->getPlayer()->sendMessage("Please only use 0-9, a-z, A-Z.");
                $api = ServerAuth::getAPI();
                $api->deauthenticatePlayer($event->getPlayer());
                $api->unregisterPlayer($event->getPlayer());
                return true;
            }
            else {
                return true;
            }
        }
    }
    public function onChat(PlayerChatEvent $event){
        if (in_array($event->getPlayer()->getName(), $this->makeup)){
            $result = ServerAuth::getAPI()->registerPlayer($event->getPlayer(), $event->getMessage());
        }
        if (!ServerAuth::getAPI()->isPlayerAuthenticated($event->getPlayer())){
            if ($this->getConfig()->get("chatauthenticate") === "on"){
                $result = ServerAuth::getAPI()->authenticatePlayer($event->getPlayer(), $event->getMessage());
                if ($result === ServerAuth::SUCCESS){
                    $event->getPlayer()->sendMessage(TextFormat::GREEN."You have been authenticated!");
                }
                elseif ($result === ServerAuth::ERR_WRONG_PASSWORD){
                    $event->getPlayer()->sendMessage(TextFormat::RED."Wrong password. Try again.");
                }
                elseif ($result === ServerAuth::ERR_GENERIC){
                    $event->getPlayer()->sendMessage("Something went wrong. Please try again.");
                }
                $event->setCancelled();
            }
        }
        else{
            return true;
        }
    }
}