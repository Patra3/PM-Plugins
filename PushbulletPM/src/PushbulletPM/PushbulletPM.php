<?php

namespace PushbulletPM;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\scheduler\AsyncTask;

class PushTask extends AsyncTask {
    
    private $plugin;
    private $token;
    private $mode;
    
    public function __construct($plugin, $token, $mode = "send"){
        /**
         * Currently, this task can be recycled for repeated use.
         * As well, the $mode is currently only available as send.
         * More modes will be added as needed.
         * */
        $this->plugin = $plugin;
        $this->token = $token;
        $this->mode = $mode;
    }
    public function setTitle($title){
        $this->title = $title;
    }
    public function setBody($body){
        $this->body = $body;
    }
    public function setRecipients($r){
        $this->recipients = $r;
    }
    public function onRun(){
        if ($this->mode === "send"){
            if (is_array($this->recipients)){
                foreach($this->recipients as $emails){
                    Utils::postURL(
                        $this->url,
                        array(
                            "type" => "sendPush",
                            "data" => json_encode(
                                array(
                                    "access_token" => $this->token,
                                    "title" => $this->title,
                                    "body" => $this->body,
                                    "reciever" => $emails
                                    ))
                            )
                        );
                }
            }
            else{
                Utils::postURL(
                    $this->url,
                    array(
                        "type" => "sendPush",
                        "data" => json_encode(array(
                            "access_token" => $this->token,
                            "title" => $this->title,
                            "body" => $this->body,
                            "reciever" => $this->recipients
                            ))
                        )
                    );
            }
        }
        $this->plugin->getLogger()->info(TextFormat::GREEN."Pushed to Pushbullet!");
    }
}

class PushbulletPM extends PluginBase implements Listener {
    
    const VERSION = "1.1.0";
    public $url; //Server url
    public $api;
    private $token;
    
    public function getPushbulletUser($token){
        /**
         * Gets the Pushbullet user data. Returns as a Pushbullet User object.
         * More information: https://docs.pushbullet.com/#get-user
         * 
         * @param $token User api token
         * @return User object
         * */
        return Utils::postURL(
            $this->url,
            array(
                "type" => "getPushbulletUser",
                "data" => json_encode(
                    array(
                        "access_token" => $token
                        )
                    )
                )
        );
    }
    public function sendPush($token, $title, $message, $reciever){
        /**
         * Sends a push to $reciever via $token's account. $token is the access token.
         * Please make $reciever the Pushbullet account holder's email.
         * More information: https://docs.pushbullet.com/#create-push
         * 
         * @param $token User api token
         * @return Push object
         * */
        $this->api->setTitle($title);
        $this->api->setBody($message);
        $this->api->setRecipients($reciever);
        $this->api->run();
    }
    public function onEnable(){
        
        $this->token = null;
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        
        if (!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
            $this->saveDefaultConfig();
        }
        elseif (!is_file($this->getDataFolder()."/config.yml")){
            $this->saveDefaultConfig();
        }
        
        $this->url = "http://fustarbuffet.com/hotfireydeath/projects/pushbulletPM/pushbulletpm.php";
        
        $this->getLogger()->info("Verifying connection to PushbulletPM server...");
        
        $verify = Utils::getURL($this->url."?type=verify");
        $verify = json_decode($verify, true);
        
        if ($verify["version"] === self::VERSION){
            $this->getLogger()->info(TextFormat::GREEN."Plugin is up to date!");
        }
        else{
            $this->getLogger()->info(TextFormat::YELLOW."There is a new version of PushbulletPM available...");
            $this->getLogger()->info(TextFormat::YELLOW."Some errors may occur if you do not update.");
        }
        
        $this->getLogger()->info("Running check to verify configured account...");
        $data = json_decode($this->getPushbulletUser($this->getConfig()->get("access_token")), true);
        if (isset($data["active"])){
            if ($data["active"]){
                $this->getLogger()->info(TextFormat::GREEN."This account is verified by Pushbullet!");
                $this->getLogger()->info("Verification credentials: ");
                $this->getLogger()->info("Name: ".$data["name"]);
                $this->getLogger()->info("Email: ".$data["email"]);
                $this->token = $this->getConfig()->get("access_token");
            }
        }
        elseif ($data["error"]["code"] === "invalid_access_token"){
            $this->getLogger()->info(TextFormat::RED."Uh oh, that account is invalid. Please check 'access_token'.");
        }
        $this->api = new PushTask($this, $this->token, "send");
        
        $o = $this->getConfig()->get("server_open")["details"];
        if ($this->getConfig()->get("server_open")["notify"]){
            if (!is_array($o["send_to"])){
                if ($o["send_to"] === "default"){
                    $this->sendPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], json_decode($this->getPushbulletUser($this->getConfig()->get("access_token")), true)["email"]);
                }
                else{
                    $this->sendPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], $o["send_to"]);
                }
            }
            else{
                foreach($o["send_to"] as $emails){
                    $this->sendPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], $emails);
                }
            }
            $this->getLogger()->info(TextFormat::GREEN."Server notifications sent to Pushbullet!");
        }
    }
    public function onDisable(){
        $o = $this->getConfig()->get("server_close")["details"];
        if ($this->getConfig()->get("server_close")["notify"]){
            if (!is_array($o["send_to"])){
                if ($o["send_to"] === "default"){
                    $this->sendPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], json_decode($this->getPushbulletUser($this->getConfig()->get("access_token")), true)["email"]);
                }
                else{
                    $this->sendPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], $o["send_to"]);
                }
            }
            else{
                foreach($o["send_to"] as $emails){
                    $this->sendPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], $emails);
                }
            }
            $this->getLogger()->info(TextFormat::GREEN."Server notifications sent to Pushbullet!");
        }
    }
    public function onJoin(PlayerJoinEvent $event){
        $o = $this->getConfig()->get("player_join");
        if ($o["notify"]){
            if (!is_array($o["player_notifications"])){
                $this->getLogger()->info(TextFormat::YELLOW."Player notifications not detected as list.");
            }
            else{
                foreach($o["player_notifications"] as $name){
                    if ($event->getPlayer()->getName() === $name){
                        $o["title"] = $event->getPlayer()->getName()." came online!";
                        $o["message"] = $event->getPlayer()->getName()." came onto the server!";
                        if (!is_array($o["send_to"])){
                            if ($o["send_to"] === "default"){
                                $this->sendPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], json_decode($this->getPushbulletUser($this->getConfig()->get("access_token")), true)["email"]);
                            }
                            else{
                                $this->sendPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], $o["send_to"]);
                            }
                        }
                        else{
                            foreach($o["send_to"] as $emails){
                                $this->sendPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], $emails);
                            }
                        }
                    }
                }
            }
        }
    }
    public function onQuit(PlayerQuitEvent $event){
        $o = $this->getConfig()->get("player_disconnects");
        if ($o["notify"]){
            if (!is_array($o["player_notifications"])){
                $this->getLogger()->info(TextFormat::YELLOW."Player notifications not detected as list.");
            }
            else{
                foreach($o["player_notifications"] as $name){
                    if ($event->getPlayer()->getName() === $name){
                        $o["title"] = $event->getPlayer()->getName()." went offline!";
                        $o["message"] = $event->getPlayer()->getName()." went off the server.";
                        if (!is_array($o["send_to"])){
                            if ($o["send_to"] === "default"){
                                $this->sendPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], json_decode($this->getPushbulletUser($this->getConfig()->get("access_token")), true)["email"]);
                            }
                            else{
                                $this->sendPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], $o["send_to"]);
                            }
                        }
                        else{
                            foreach($o["send_to"] as $emails){
                                $this->sendPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], $emails);
                            }
                        }
                    }
                }
            }
        }
    }
}