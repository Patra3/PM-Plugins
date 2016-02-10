<?php

namespace ImporterPM;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Zippy;

class ImporterPM extends PluginBase {
    
    const REPO_NOT_FOUND = 1;
    const PMBUILD_NOT_FOUND = 2;
    const PLUGIN_NOT_FOUND = 3;
    const PLUGIN_EXISTS = 4;
    
    public function onEnable(){
        if (!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
        }
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        if (strtolower($command->getName()) === "importer"){
            if (!isset($args[0])){
                $sender->sendMessage("/importer help for all commands.");
                return true;
            }
            elseif ($args[0] === "help"){
                $sender->sendMessage("/importer help : List all commands.");
                $sender->sendMessage("/importer import <username/repository> : Imports a github plugin directly.");
                $sender->sendMessage("/importer pmbuild <url> : Imports/loads a custom PocketMine build.");
                $sender->sendMessage("/importer pimport <url> : Downloads and installs a phar plugin from url.");
                return true;
            }
            elseif ($args[0] === "import"){
                if (!isset($args[1])){
                    $sender->sendMessage("/importer import <username/repository>");
                    return true;
                }
                else{
                    $result = $this->importGitRepo($args[1]);
                    if ($result === ImporterPM::REPO_NOT_FOUND){
                        $sender->sendMessage(TextFormat::RED."Github repository not found.");
                        return true;
                    }
                    else{
                        $sender->sendMessage(TextFormat::GREEN."Plugin imported successfully!");
                        $sender->sendMessage(TextFormat::YELLOW."Please restart the server for changes to take effect.");
                        return true;
                    }
                }
            }
            elseif ($args[0] === "pmbuild"){
                $sender->sendMessage(TextFormat::RED."[TIP] Please enter the full URL (including http://).");
                if (!isset($args[1])){
                    $sender->sendMessage("/importer pmbuild <url>");
                    return true;
                }
                else{
                    $result = $this->importPMBuild($args[1]);
                    if ($result === ImporterPM::PMBUILD_NOT_FOUND){
                        $sender->sendMessage(TextFormat::RED."Build not found at URL.");
                        return true;
                    }
                    else{
                        $sender->sendMessage(TextFormat::GREEN."Build installed successfully!");
                        $this->getLogger()->info("New build installed. Please start server to see effects.");
                        $this->getServer()->stop();
                        // If you don't stop the server, errors will eventually happen as the origin .phar file
                        // was removed.
                        return true;
                    }
                }
            }
            elseif ($args[0] === "pimport"){
                $sender->sendMessage(TextFormat::RED."[TIP] Please enter the full URL (including http://).");
                if (!isset($args[1])){
                    $sender->sendMessage("/importer pimport <url>");
                    return true;
                }
                else{
                    $result = $this->importPharPlugin($args[1]);
                    if ($result === ImporterPM::PLUGIN_NOT_FOUND){
                        $sender->sendMessage(TextFormat::RED."Plugin not found at URL.");
                        return true;
                    }
                    else{
                        $sender->sendMessage(TextFormat::GREEN."Plugin imported successfully!");
                        $sender->sendMessage(TextFormat::YELLOW."Please restart the server for changes to take effect.");
                        return true;
                    }
                }
            }
        }
    }
    public function importGitRepo($name){
        /**
         * Imports a PocketMine github plugin into your plugins folder (.phar)
         * @param $name Repo name, example, HotFireyDeath/Glifcos
         **/
         
        // $name should be (example) : HotFireyDeath/Glifcos
        $download = file_get_contents("https://github.com/".$name."/archive/master.zip");
        if (empty($download)){
            return ImporterPM::REPO_NOT_FOUND;
        }
        $pkg = fopen($this->getDataFolder()."/extract.zip", "w+");
        fwrite($pkg, $download);
        fclose($pkg);
        
        //Zippy!
        $zippy = Zippy::load();
        $arc = $zippy->open($this->getDataFolder()."/extract.zip");
        $arc->extract($this->getDataFolder());
        $config = new Config($this->getDataFolder()."/plugin.yml", Config::YAML);
        $name = $config->get("name"); //get plugin name.
        unlink("extract.zip");
        unset($config);
        
        //Now build from directory.
        if (!empty($this->getServer()->getPluginManager()->getPlugin($name))){
            return ImporterPM::PLUGIN_EXISTS;
        }
        $phar = new \Phar($this->getServer()->getPluginPath()."/".$name.".yml");
        $phar->buildFromDirectory($this->getDataFolder());
        $this->FolderRm($this->getDataFolder());
        return true;
    }
    private function FolderRm($path){
        // RESOURCE FOUND AT STACKOVERFLOW.
        if (is_dir($path) === true){
            $files = array_diff(scandir($path), array('.', '..'));
            foreach ($files as $file){
                $this->FolderRm(realpath($path) . '/' . $file);
            }
            return rmdir($path);
        }
        else if (is_file($path) === true){
            return unlink($path);
        }
        return false;
    }
    public function importPMBuild($url){
        /**
         * Imports a custom PocketMine build (ex. ImagicalMine).
         * @param $url Download URL.
         **/
        $download = file_get_contents($url);
        if (empty($download)){
            return ImporterPM::PMBUILD_NOT_FOUND;
        }
        unlink($this->getServer()->getDataPath()."/PocketMine-MP.phar");
        $pkg = fopen($this->getServer()->getDataPath()."/PocketMine-MP.phar", "w+");
        fwrite($pkg, $download);
        fclose($pkg);
        return true;
    }
    public function importPharPlugin($url){
        /**
         * Imports a PHAR plugin from URL.
         * @param $url Download URL.
         **/
        $download = file_get_contents($url);
        if (empty($download)){
            return ImporterPM::PLUGIN_NOT_FOUND;
        }
        // extract phar to datafolder.
        $phar = fopen($this->getDataFolder()."/extract.phar", "w+");
        fwrite($phar, $download);
        fclose($phar);
        $data = new \Phar($this->getDataFolder()."/extract.phar");
        $data->extractTo($this->getDataFolder());
        unset($data);
        // gets name of plugin
        $config = new Config($this->getDataFolder()."/plugin.yml", Config::YAML);
        $name = $config->get("name");
        unset($config);
        if (!empty($this->getServer()->getPluginManager()->getPlugin($name))){
            return ImporterPM::PLUGIN_EXISTS;
        }
        // makes plugin
        $plugin = fopen($this->getServer()->getPluginPath()."/".$name.".phar", "w+");
        fwrite($plugin, $download);
        fclose($plugin);
        return true;
    }
}