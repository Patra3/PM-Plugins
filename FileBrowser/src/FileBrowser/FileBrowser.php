<?php

namespace FileBrowser;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permissible;
use pocketmine\utils\TextFormat;

class FileBrowser extends PluginBase {
  public function onEnable(){
    if (!is_dir($this->getDataFolder())){
      mkdir($this->getDataFolder());
      mkdir($this->getDataFolder()."/downloads/");
    }
    if (is_file($this->getDataFolder()."/data.json")){
      return true;
    }
    $ftpy = array("openConnections" => "none");
    $data = array();
    $data["ftp"] = $ftpy;
    $string = json_encode($data);
    $handle = fopen($this->getDataFolder()."/data.json", "w+");
    fwrite($handle, $string);
    fclose($handle);
  }
  public function returnFTPconnectionitems($id){
    $data = file_get_contents($this->getDataFolder()."/data.json");
    $dect = json_decode($data, true);
    $ftpy = $dect["ftp"];
    $inner = $ftpy["openConnections"];
    $access = $inner[$id];
    if (!isset($inner[$id])){
      return false;
    }
    else{
      return $access;
    }
  }
  public function editFTPconnection($id, $option, $newvalue){
    /*
    * PART OF THE FILEBROWSER API
    * Edits an FTP connection, specifically by id. $newvalue is the new connection value, $option being reference (username).
    */
    $data = file_get_contents($this->getDataFolder()."/data.json");
    $dect = json_decode($data, true);
    $ftpy = $dect["ftp"];
    $inner = $ftpy["openConnections"];
    $access = $inner[$id];
    if (!isset($access[$option])){
      return false;
    }
    else{
      unset($access[$option]);
      $access[$option] = $newvalue;
      unset($inner[$id]);
      $inner[$id] = $access;
      unset($ftpy["openConnections"]);
      $ftpy["openConnections"] = $inner;
      unset($dect["ftp"]);
      $dect["ftp"] = $ftpy;
      $enc = json_encode($dect);
      unlink($this->getDataFolder()."/data.json");
      $handle = fopen($this->getDataFolder()."/data.json", "w+");
      fwrite($handle, $enc);
      fclose($handle);
      return true;
    }
  }
  public function removeFTPconnection($id){
    /*
    * PART OF THE FILEBROWSER API
    * Removes an FTP connection from the data.json file, searched by array $key.
    */
    $data = file_get_contents($this->getDataFolder()."/data.json");
    $dect = json_decode($data, true);
    $ftpy = $dect["ftp"];
    $id = intval($id); 
    $inner = $ftpy["openConnections"];
    /*
    MAP:
    array(1){
      [0] =>
      info
    }
    */
    if (!isset($inner[$id])){
      return false;
    }
    else{
      unset($inner[$id]);
      if (empty($inner)){
        unset($ftpy["openConnections"]);
        $ftpy["openConnections"] = "none";
      }
      else{
        unset($ftpy["openConnections"]);
        $ftpy["openConnections"] = $inner;
      }
      unset($dect["ftp"]);
      $dect["ftp"] = $ftpy;
      $encoded = json_encode($dect);
      unlink($this->getDataFolder()."/data.json");
      $handle = fopen($this->getDataFolder()."/data.json", "w+");
      fwrite($handle, $encoded);
      fclose($handle);
      return true;
    }
  }
  public function addFTPconnection($host, $port, $username, $password){
    /*
    * PART OF THE FILEBROWSER API.
    * Adds an FTP connection to the data.json file.
    */
    $data = file_get_contents($this->getDataFolder()."/data.json");
    $decd = json_decode($data, true);
    $ftpy = $decd["ftp"];
    $connections = $ftpy["openConnections"];
    $connectd = ftp_connect($host, $port);
    if (!ftp_connect($host, $port)){
      return false;
    }
    if ($connections === "none"){
      unset($ftpy["openConnections"]);
      $ftpy["openConnections"] = array();
      $kep = array();
      $kep["username"] = $username;
      $kep["password"] = $password;
      $kep["host"] = $host;
      $kep["port"] = $port;
      array_push($ftpy["openConnections"], $kep);
      unset($decd["ftp"]);
      $decd["ftp"] = $ftpy;
      $encode = json_encode($decd);
      unlink($this->getDataFolder()."/data.json");
      $handle = fopen($this->getDataFolder()."/data.json", "w+");
      fwrite($handle, $encode);
      fclose($handle);
      return true;
    }
    else{
      unset($ftpy["openConnections"]);
      $ftpy["openConnections"] = array();
      $kep = array();
      $kep["username"] = $username;
      $kep["password"] = $password;
      $kep["host"] = $host;
      $kep["port"] = $port;
      array_push($ftpy["openConnections"], $kep);
      unset($decd["ftp"]);
      $decd["ftp"] = $ftpy;
      $encode = json_encode($decd);
      unlink($this->getDataFolder()."/data.json");
      $handle = fopen($this->getDataFolder()."/data.json", "w+");
      fwrite($handle, $encode);
      fclose($handle);
      return true;
    }
    
  }
  public function onCommand(CommandSender $sender, Command $command, $label, array $args){
    if(strtolower($command->getName()) === "filebrowser"){
      $data = file_get_contents($this->getDataFolder()."/data.json");
      $truw = json_decode($data, true);
      if (!isset($args[0])){
        if ($sender->hasPermission("filebrowser.main")){
          $sender->sendMessage(TextFormat::RED."/filebrowser help");
          return true;
        }
        else{
          $sender->sendMessage(TextFormat::RED."[FileBrowser] Access denied!");
          return true;
        }
      }
      elseif ($args[0] === "ftp"){
        if (!$sender->hasPermission("filebrowser.ftp")){
          $sender->sendMessage(TextFormat::RED."[FileBrowser] Access denied!");
          return true;
        }
        $ftpdata = $truw["ftp"];
        if (!isset($args[1])){
          if ($ftpdata["openConnections"] === "none"){
            $sender->sendMessage("[FileBrowser] No active connections exist.");
            return true;
          }
          else{
            $livenum = 0;
            foreach($ftpdata["openConnections"] as $connection){
              $livenum = $livenum + 1;
            }
            $sender->sendMessage("[FileBrowser] There are currently ".$livenum." connections.");
            return true;
          }
        }
        elseif ($args[1] === "connect"){
          if (!isset($args[2])){
            $sender->sendMessage(TextFormat::RED."/filebrowser ftp connect <host> <port> <username> <password>");
            return true;
          }
          elseif (!isset($args[3])){
            $sender->sendMessage(TextFormat::RED."/filebrowser ftp connect <host> <port> <username> <password>");
            return true;
          }
          elseif (!isset($args[4])){
            $sender->sendMessage(TextFormat::RED."/filebrowser ftp connect <host> <port> <username> <password>");
            return true;
          }
          elseif (!isset($args[5])){
            $sender->sendMessage(TextFormat::RED."/filebrowser ftp connect <host> <port> <username> <password>");
          }
          else{
            $host = $args[2];
            $port = $args[3];
            $username = $args[4];
            $password = $args[5];
            $this->addFTPconnection($host, $port, $username, $password);
            if ($this->addFTPconnection($host, $port, $username, $password)){
              $sender->sendMessage(TextFormat::GREEN."[FileBrowser] FTP connection established. /filebrowser ftp help");
              return true;
            }
            else{
              $sender->sendMessage(TextFormat::RED."[FileBrowser] FTP connection unsuccessful. Try again.");
              return true;
            }
          }
        }
        elseif ($args[1] === "connection"){
          $ftpdata = $truw["ftp"];
          if (!$sender->hasPermission("filebrowser.connection")){
            $sender->sendMessage(TextFormat::RED."[FileBrowser] Access denied!");
            return true;
          }
          if ($args[2] === "list"){
            $sender->sendMessage("[FileBrowser] Connections: ");
            if ($ftpdata["openConnections"] === "none"){
              $sender->sendMessage(TextFormat::RED."[FileBrowser] No active connections exist.");
            }
            foreach ($ftpdata["openConnections"] as $cont){
              $username = $cont["username"];
              $password = $cont["password"];
              $host = $cont["host"];
              $port = $cont["port"];
              
              $conkey = array_search($cont, $ftpdata["openConnections"]);
              $sender->sendMessage("#".$conkey."# Usr: ".$username.", Pswd: ".$password.", Host: ".$host.", Port: ".$port);
            }
            return true;
          }
          elseif (!isset($args[2])){
            $sender->sendMessage(TextFormat::RED."/filebrowser connection help");
            return true;
          }
          elseif ($args[2] === "help"){
            $sender->sendMessage("[FileBrowser] Connection commands:");
            $sender->sendMessage("/filebrowser ftp connection list : Lists all connections");
            $sender->sendMessage("/filebrowser ftp connection delete <id> : Deletes a connection");
            $sender->sendMessage("/filebrowser ftp connection edit <id> <part> <newpart> : Edits connection");
            $sender->sendMessage("/filebrowser ftp connection help : Lists all connect commands");
            return true;
          }
          elseif ($args[2] === "delete"){
            if (!isset($args[3])){
              $sender->sendMessage(TextFormat::RED."/filebrowser ftp connection delete <id>");
              return true;
            }
            else{
              $id = $args[2];
              $this->removeFTPconnection($id);
              if ($this->removeFTPconnection($id)){
                $sender->sendMessage(TextFormat::GREEN."[FileBrowser] FTP connection deleted.");
                return true;
              }
              else{
                $sender->sendMessage(TextFormat::RED."[FileBrowser] FTP deletion unsuccessful. Try again.");
                return true;
              }
            }
          }
          elseif ($args[2] === "edit"){
            if (!isset($args[3])){
              $sender->sendMessage(TextFormat::RED."/filebrowser ftp connection edit <id> <type> <newvalue>");
              return true;
            }
            elseif (!isset($args[4])){
              $sender->sendMessage(TextFormat::RED."/filebrowser ftp connection edit <id> <type> <newvalue>");
              return true;
            }
            elseif (!isset($args[5])){
              $sender->sendMessage(TextFormat::RED."/filebrowser ftp connection edit <id> <type> <newvalue>");
              return true;
            }
            else{
              $id = $args[3];
              $option = $args[4];
              $newvalue = $args[5];
              $this->editFTPconnection($id, $option, $newvalue);
              if ($this->editFTPconnection($id, $option, $newvalue)){
                $sender->sendMessage(TextFormat::GREEN."[FileBrowser] FTP connection edited.");
                return true;
              }
              else{
                $sender->sendMessage(TextFormat::RED."[FileBrowser] FTP editing unsuccessful. Try again.");
                return true;
              }
            }
          }
        }
        elseif ($args[1] === "download"){
          if (!isset($args[2])){
            $sender->sendMessage(TextFormat::RED."/filebrowser ftp download <filepath> <connectionid>");
            return true;
          }
          elseif (!isset($args[3])){
            $sender->sendMessage(TextFormat::RED."/filebrowser ftp download <filepath> <connectionid>");
            return true;
          }
          else{
            $extension = substr($args[2], -4); //return .ext
            $id = $args[3];
            $stf = $this->returnFTPconnectionitems($id);
            if (!$this->returnFTPconnectionitems($id)){
              $sender->sendMessage(TextFormat::RED."[FileBrowser] FTP id '".$id."' is invalid. Try again.");
              return true;
            }
            else{
              $connection = ftp_connect($stf["host"], $stf["port"]);
              $username = $stf["username"];
              $password = $stf["password"];
              $locale = substr($args[2], 0, -4); //returns file without .ext;
              $local = $this->getDataFolder()."/downloads/".$locale.$extension;
              $lgin = ftp_login($connection, $username, $password);
              
              if (ftp_get($connection, $local, $args[2], FTP_BINARY)){
                $sender->sendMessage(TextFormat::GREEN."[FileBrowser] File successfully downloaded, located in datafolder/downloads.");
                ftp_close($connection);
                return true;
              }
              else{
                $sender->sendMessage(TextFormat::RED."[FileBrowser] File couldn't be downloaded. Try again.");
                ftp_close($connection);
                return true;
              }
            }
          }
        }
      }
    }
  }
}
