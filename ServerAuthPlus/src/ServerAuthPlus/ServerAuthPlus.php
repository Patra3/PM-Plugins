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
    private $emailreg = array("players" => array());
    
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
        if ($this->getConfig()->get("emailauth") === "on"){
            $this->emailreg["active"] = true;
        }
        else{
            $this->emailreg["active"] = false;
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
                unlink($this->makeup[array_search($event->getPlayer()->getName(), $this->makeup)]);
                return true;
            }
        }
        if ($this->emailreg["active"]){
            array_push($this->emailreg["players"], $event->getPlayer()->getName());
            $event->getPlayer()->sendMessage("One more step! Please enter your email.");
            return true;
        }
    }
    public function onChat(PlayerChatEvent $event){
        if (in_array($event->getPlayer()->getName(), $this->makeup)){
            $result = ServerAuth::getAPI()->registerPlayer($event->getPlayer(), $event->getMessage());
        }
        if (in_array($event->getPlayer->getName(), $this->emailreg["players"])){
            if (!strstr($event->getMessage(), "@")){
                $sender->sendMessage("That is not a valid email address. Please try again.");
                $event->setCancelled();
                return true;
            }
            else{
                $sender->sendMessage("Thank you. You may now login.");
                foreach ($this->getConfig()->get("emailauthcontent") as $strmsg){
                    $finalmsg = "";
                    $finalmsg = $finalmsg.$strmsg.\n;
                }
                $finalmsg = str_replace("{player}", $event->getPlayer()->getName(), $finalmsg);
                $rs = mail($event->getMessage(), "Registration", $finalmsg);
                if (!$rs){
                    $this->getLogger()->warning("Registration email sending to ".$event->getMessage()." failed.");
                }
                $event->setCancelled();
                return true;
            }
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