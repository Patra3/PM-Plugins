<?php

namespace PushbulletPM;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Utils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\scheduler\AsyncTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class PushTask extends AsyncTask {
    
    public $token;
    public $mode;
    public $title;
    public $body;
    public $recipient;
    
    public function __construct($url, $token, $mode = "send", $title, $body, $recipient){
        $this->url = $url;
        $this->token = $token;
        $this->mode = $mode;
        $this->title = $title;
        $this->body = $body;
        $this->recipients = $recipient;
    }
    public function onRun(){
        if ($this->mode == "send"){
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
                                    "message" => $this->body,
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
                            "message" => $this->body,
                            "reciever" => $this->recipients
                            ))
                        )
                    );
            }
        }
    }
}

class PushbulletPM extends PluginBase implements Listener {
    
    const VERSION = "1.1.0";
    public $url; //Server url
    public $api;
    private $token;
    private $process;
    private $k;
    
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
    public function directPush($token, $title, $message, $reciever){
        /**
         * Directly sends a push to the server using POST.
         * It's seriously recommended to use the sendPush() method,
         * as this is not Async and can cause delays.
         * 
         * Use this method only if nessessary.
         * This method also returns the full Push object.
         * 
         * @param $token User api token
         * @param $title Title
         * @param $message Message
         * @param $reciever String
         * @return Push object
         * */
        return Utils::postURL(
            $this->url,
            array(
                "type" => "sendPush",
                "data" => json_encode(
                    array(
                        "access_token" => $token,
                        "title" => $title,
                        "message" => $message,
                        "reciever" => $reciever
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
         * @param $title Title
         * @param $message Message
         * @param $reciever Array/String
         * @return void
         * */
        $this->getServer()->getScheduler()->scheduleAsyncTask(new PushTask($this->url, $token, "send", $title, $message, $reciever));
    }
    public function onEnable(){
        
        $this->token = null;
        $this->process = array();
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
                    $this->directPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], json_decode($this->getPushbulletUser($this->getConfig()->get("access_token")), true)["email"]);
                }
                else{
                    $this->directPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], $o["send_to"]);
                }
            }
            else{
                foreach($o["send_to"] as $emails){
                    $this->directPush($this->getConfig()->get("access_token"), $o["title"], $o["message"], $emails);
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
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if (strtolower($command->getName()) === "pushbullet"){
            if (!isset($args[0])){
                $sender->sendMessage("/pushbullet help for all commands.");
                return true;
            }
            elseif ($args[0] === "help"){
                $sender->sendMessage("/pushbullet help : Help command."); //done
                $sender->sendMessage("/pushbullet push : Push using main account."); //done
                $sender->sendMessage("/pushbullet pushall : Push to all subscribed players.");
                $sender->sendMessage("/pushbullet subscribe <email> : Subscribe to server's push."); //done
                $sender->sendMessage("/pushbullet unsubscribe : Unsubscribe from server's push."); //done
                return true;
            }
            elseif ($args[0] === "push"){
                if (!array_key_exists($sender->getName(), $this->process)){
                    $this->process[$sender->getName()] = array();
                    $sender->sendMessage("Do /pushbullet push reciever <reciever> to set a reciever.");
                    $sender->sendMessage("Do /pushbullet push title <title> to set a title.");
                    $sender->sendMessage("Do /pushbullet push message <message> to set a message.");
                    $sender->sendMessage("Do /pushbullet push delete to delete the current push.");
                    $sender->sendMessage("Do /pushbullet push preview to send the push to yourself (as a test).");
                    $sender->sendMessage("Do /pushbullet push to send your push.");
                    return true;
                }
                else{
                    if (!isset($args[1])){
                        if (isset($this->process[$sender->getName()]["reciever"]) and isset($this->process[$sender->getName()]["title"]) and isset($this->process[$sender->getName()]["message"])){
                            $this->sendPush($this->token, $this->process[$sender->getName()]["title"], $this->process[$sender->getName()]["message"], $this->process[$sender->getName()]["reciever"]);
                            $sender->sendMessage(TextFormat::GREEN."Message pushed through Pushbullet!");
                            unset($this->process[$sender->getName()]);
                            return true;
                        }
                        else{
                            $sender->sendMessage(TextFormat::RED."A title, message, or reciever is not yet set.");
                            return true;
                        }
                    }
                    elseif ($args[1] === "preview"){
                        $this->sendPush($this->token, $this->process[$sender->getName()]["title"], $this->process[$sender->getName()]["message"], json_decode($this->getPushbulletUser($this->getConfig()->get("access_token")), true)["email"]);
                        $sender->sendMessage(TextFormat::GREEN."Preview sent to Pushbullet!");
                        return true;
                    }
                    elseif ($args[1] === "delete"){
                        unset($this->process[$sender->getName()]);
                        $sender->sendMessage(TextFormat::RED."Push deleted.");
                        return true;
                    }
                    else{
                        if (in_array($args[1], array("reciever", "message", "title"))){
                            $message = $args;
                            unset($message[0]);
                            unset($message[1]);
                            $message = implode(" ", $message);
                            $this->process[$sender->getName()][$args[1]] = $message;
                            $sender->sendMessage(TextFormat::GREEN.$args[1]." has been set in the push!");
                            return true;
                        }
                        else{
                            $sender->sendMessage(TextFormat::YELLOW.$args[1]." is not a valid push setting.");
                            return true;
                        }
                    }
                }
            }
            elseif ($args[0] === "subscribe"){
                if (!isset($args[1])){
                    $sender->sendMessage("/pushbullet subscribe <email> to subscribe to Pushbullet on this server.");
                    $sender->sendMessage(TextFormat::YELLOW."Disclaimer: Your email is not guaranteed 'safe' on some servers!");
                    return true;
                }
                else{
                    if (!is_file($this->getDataFolder()."/subscriptions.txt")){
                        $d = array();
                        $f = fopen($this->getDataFolder()."/subscriptions.txt", "w+");
                    }
                    else{
                        $d = json_decode(base64_decode(file_get_contents($this->getDataFolder()."/subscriptions.txt")), true);
                        unlink($this->getDataFolder()."/subscriptions.txt");
                        $f = fopen($this->getDataFolder()."/subscriptions.txt", "w+");
                    }
                    if (isset($d[$sender->getName()])){
                        $sender->sendMessage(TextFormat::YELLOW."Overwriting subscribed email...");
                    }
                    $d[$sender->getName()] = $args[1];
                    fwrite($f, base64_encode(json_encode($d)));
                    fclose($f);
                    $sender->sendMessage(TextFormat::GREEN."Email '".$args[1]."' has been subscribed.");
                    return true;
                }
            }
            elseif ($args[0] === "unsubscribe"){
                if (!is_file($this->getDataFolder()."/subscriptions.txt")){
                    $sender->sendMessage(TextFormat::RED."No subscription data found.");
                }
                else{
                    $d = json_decode(base64_decode(file_get_contents($this->getDataFolder()."/subscriptions.txt")), true);
                    if (!isset($d[$sender->getName()])){
                        $sender->sendMessage(TextFormat::RED."You have not subscribed yet!");
                        return true;
                    }
                    else{
                        unset($d[$sender->getName()]);
                        unlink($this->getDataFolder()."/subscriptions.txt");
                        $f = fopen($this->getDataFolder()."/subscriptions.txt", "w+");
                        fwrite($f, base64_encode(json_encode($d)));
                        fclose($f);
                        $sender->sendMessage(TextFormat::GREEN."You have been unsubscribed from this server.");
                        return true;
                    }
                }
            }
            elseif ($args[0] === "pushall"){
                if (!array_key_exists($sender->getName(), $this->process)){
                    $this->process[$sender->getName()] = array();
                    $sender->sendMessage("Do /pushbullet pushall title <title> to set a title.");
                    $sender->sendMessage("Do /pushbullet pushall message <message> to set a message.");
                    $sender->sendMessage("Do /pushbullet pushall delete to delete the current push.");
                    $sender->sendMessage("Do /pushbullet pushall preview to send the push to yourself (as a test).");
                    $sender->sendMessage("Do /pushbullet pushall to send your push to all subscribers.");
                    return true;
                }
                else{
                    if (!isset($args[1])){
                        if (isset($this->process[$sender->getName()]["title"]) and isset($this->process[$sender->getName()]["message"])){
                            $d = json_decode(base64_decode(file_get_contents($this->getDataFolder()."/subscriptions.txt")), true);
                            foreach($d as $value){
                                $this->sendPush($this->token, $this->process[$sender->getName()]["title"], $this->process[$sender->getName()]["message"], $value);
                            }
                            $sender->sendMessage(TextFormat::GREEN."Messages sent to Pushbullet!");
                            unset($this->process[$sender->getName()]);
                            return true;
                        }
                        else{
                            $sender->sendMessage(TextFormat::RED."A title or message is not yet set.");
                            return true;
                        }
                    }
                    elseif ($args[1] === "preview"){
                        $this->sendPush($this->token, $this->process[$sender->getName()]["title"], $this->process[$sender->getName()]["message"], json_decode($this->getPushbulletUser($this->getConfig()->get("access_token")), true)["email"]);
                        $sender->sendMessage(TextFormat::GREEN."Preview sent to Pushbullet!");
                        return true;
                    }
                    elseif ($args[1] === "delete"){
                        unset($this->process[$sender->getName()]);
                        $sender->sendMessage(TextFormat::RED."Push deleted.");
                        return true;
                    }
                    else{
                        if (in_array($args[1], array("message", "title"))){
                            $message = $args;
                            unset($message[0]);
                            unset($message[1]);
                            $message = implode(" ", $message);
                            $this->process[$sender->getName()][$args[1]] = $message;
                            $sender->sendMessage(TextFormat::GREEN.$args[1]." has been set in the push!");
                            return true;
                        }
                        else{
                            $sender->sendMessage(TextFormat::YELLOW.$args[1]." is not a valid push setting.");
                            return true;
                        }
                    }
                }
            }
        }
    }
}