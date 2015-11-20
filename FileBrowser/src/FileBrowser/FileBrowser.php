<?php

namespace FileBrowser;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginManager;
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
    /*
    * PART OF THE ADVANCED FILEBROWSER API.
    * Do not use this function if you don't know what you're doing.
    *
    * Retrieves and returns an $array of credential $id.
    */
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
  public function uploadFTPItem($id, $filepath){
    /*
    * PART OF THE FILEBROWSER API
    * Uploads a file to ftp $id with name $filepath in the folder /filebrowser/.
    */
    $stf = $this->returnFTPconnectionitems($id);
    if (!$this->returnFTPconnectionitems($id)){
      return "invalidID";
    }
    if (!isset($stf["host"])){
      return "credentialerror";
    }
    if (!is_file($filepath)){
      return false;
    }
    $extension = substr($filepath, -4); //return .ext
    $locale = strrchr($filepath, "/");
    $local = "/filebrowser/".$locale.$extension;
    $connection = ftp_connect($stf["host"], $stf["port"]);
    $username = $stf["username"];
    $password = $stf["password"];
    $lgin = ftp_login($connection, $username, $password);
    if (!ftp_size($connection, "/filebrowser/")){
      ftp_mkdir($connection, "/filebrowser/");
    }
    if (ftp_put($connection, $local, $filepath, FTP_ASCII)){
      ftp_close($connection);
      return true;
    }
    else{
      return false;
    }
  }
  public function exploreFTPdirectory($directory, $id){
    /*
    * PART OF THE MORE ADVANCED FILEBROWSER API
    * Gets the array with files inside $directory of FTP $id.
    */
    $stf = $this->returnFTPconnectionitems($id);
    if (!$this->returnFTPconnectionitems($id)){
      return "invalidID";
    }
    if (!isset($stf["host"])){
      return "credentialerror";
    }
    $connection = ftp_connect($stf["host"], $stf["port"]);
    $username = $stf["username"];
    $password = $stf["password"];
    $lgin = ftp_login($connection, $username, $password);
    $esst = ftp_nlist($connection, $directory);
    if (ftp_nlist($connection, $directory)){
      return $esst;
    }
    else{
      return false;
    }
  }
  public function downloadFTPItem($ddirectory, $id, $filepath){
    /*
    * PART OF THE FILEBROWSER API
    * Downloads a file from ftp $id with name $filepath, and stores at directory $ddirectory.
    */
    $extension = substr($filepath, -4); //return .ext
    $stf = $this->returnFTPconnectionitems($id);
    if (!$this->returnFTPconnectionitems($id)){
      return "invalidID";
    }
    else{
      if (!isset($stf["host"])){
        return "credentialerror";
      }
      $connection = ftp_connect($stf["host"], $stf["port"]);
      $username = $stf["username"];
      $password = $stf["password"];
      $locale = strrchr($filepath, "/"); //returns file without .ext;
      $local = $ddirectory.$locale.$extension;
      $lgin = ftp_login($connection, $username, $password);
              
      if (ftp_get($connection, $local, $filepath, FTP_BINARY)){
        ftp_close($connection);
        return true;
      }
      else{
        ftp_close($connection);
        return false;
      }
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
      if (is_array($inner)) unset($inner[$id]);
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
      elseif ($args[0] === "help"){
        $sender->sendMessage("[FileBrowser] Main commands:");
        $sender->sendMessage("/filebrowser help : Get help commands.");
        $sender->sendMessage("/filebrowser ftp : Access FileBrowserFTP.");
      }
      elseif ($args[0] === "plugins"){
        if (!isset($args[1])){
          $sender->sendMessage("/filebrowser plugins help");
          return true;
        }
        elseif ($args[1] === "list"){
          $plugins = $this->getPlugins();
          $sender->sendMessage("Loaded plugins:");
          foreach($plugins as $ytu){
            $sender->sendMessage($ytu);
          }
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
          $sender->sendMessage(TextFormat::RED."/filebrowser ftp help");
          return true;
        }
        elseif ($args[1] === "connect"){
          if (!$sender->hasPermission("filebrowser.ftp.commands")){
            $sender->sendMessage(TextFormat::RED."[FileBrowser] Access denied!");
            return true;
          }
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
          if (!$sender->hasPermission("filebrowser.ftp.connection")){
            $sender->sendMessage(TextFormat::RED."[FileBrowser] Access denied!");
            return true;
          }
          if (!isset($args[2])){
            $sender->sendMessage(TextFormat::RED."/filebrowser ftp connection help");
            return true;
          }
          elseif ($args[2] === "list"){
            $sender->sendMessage("[FileBrowser] Connections: ");
            if (!$sender->hasPermission("filebrowser.ftp.connection.list")){
              $sender->sendMessage(TextFormat::RED."[FileBrowser] Access denied!");
              return true;
            }
            if ($ftpdata["openConnections"] === "none"){
              $sender->sendMessage(TextFormat::RED."[FileBrowser] No active connections exist.");
              return true;
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
          elseif ($args[2] === "help"){
            if (!$sender->hasPermission("filebrowser.ftp.connection.help")){
              $sender->sendMessage("[FileBrowser] Access denied!");
              return true;
            }
            $sender->sendMessage("[FileBrowser] Connection commands:");
            $sender->sendMessage("/filebrowser ftp connection list : Lists all connections");
            $sender->sendMessage("/filebrowser ftp connection delete <id> : Deletes a connection");
            $sender->sendMessage("/filebrowser ftp connection edit <id> <part> <newpart> : Edits connection");
            $sender->sendMessage("/filebrowser ftp connection help : Lists all connect commands");
            return true;
          }
          elseif ($args[2] === "delete"){
            if (!$sender->hasPermission("filebrowser.ftp.connection.delete")){
              $sender->sendMessage(TextFormat::RED."[FileBrowser] Access denied!");
              return true;
            }
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
            if (!$sender->hasPermission("filebrowser.ftp.connection.edit")){
              $sender->sendMessage(TextFormat::RED."[FileBrowser] Access denied!");
            }
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
          if (!$sender->hasPermission("filebrowser.ftp.download")){
            $sender->sendMessage(TextFormat::RED."[FileBrowser] Access denied!");
            return true;
          }
          if (!isset($args[2])){
            $sender->sendMessage(TextFormat::RED."/filebrowser ftp download <filepath> <connectionid>");
            return true;
          }
          elseif (!isset($args[3])){
            $sender->sendMessage(TextFormat::RED."/filebrowser ftp download <filepath> <connectionid>");
            return true;
          }
          else{
            $ddirectory = $this->getDataFolder()."/downloads/";
            $id = $args[3];
            $filepath = $args[2];
            if ($this->downloadFTPItem($ddirectory, $id, $filepath)){
              $sender->sendMessage(TextFormat::GREEN."[FileBrowser] File downloaded successfully.");
              return true;
            }
            elseif ($this->downloadFTPItem($ddirectory, $id, $filepath) === "invalidID"){
              $sender->sendMessage(TextFormat::RED."[FileBrowser] FTP ID '".$id."' does not exist.");
              return true;
            }
            elseif ($this->downloadFTPItem($ddirectory, $id, $filepath) === "credentialerror"){
              $sender->sendMessage(TextFormat::RED."[FileBrowser] FTP credentials incorrect. Try again.");
              return true;
            }
            else{
              $sender->sendMessage(TextFormat::RED."[FileBrowser] An unknown error has occured, error logged to console
              ."." Try again.");
              var_dump($this->downloadFTPItem($ddirectory, $id, $filepath));
              return true;
            }
          }
        }
        elseif ($args[1] === "help"){
          if (!$sender->hasPermission("filebrowser.ftp.help")){
            $sender->sendMessage(TextFormat::RED."[FileBrowser] Access denied!");
            return true;
          }
          $sender->sendMessage("[FileBrowser] FTP commands:");
          $sender->sendMessage("/filebrowser ftp help : Get help commands");
          $sender->sendMessage("/filebrowser ftp connect : Connects to ftp credentials");
          $sender->sendMessage("/filebrowser ftp connection : Manage your connections");
          $sender->sendMessage("/filebrowser ftp download : Downloads a file");
          $sender->sendMessage("/filebrowser ftp upload : Uploads a file");
          $sender->sendMessage("/filebrowser ftp explore : Explores FTP directory.");
          return true;
        }
        elseif ($args[1] === "upload"){
          if (!$sender->hasPermission("filebrowser.ftp.upload")){
            $sender->sendMessage(TextFormat::RED."[FileBrowser] Access denied!");
            return true;
          }
          if (!isset($args[2])){
            $sender->sendMessage(TextFormat::RED."/filebrowser ftp upload <filepath> <connectionid>");
            return true;
          }
          elseif (!isset($args[3])){
            $sender->sendMessage(TextFormat::RED."/filebrowser ftp upload <filepath> <connectionid>");
            return true;
          }
          else{
            $id = $args[3];
            $filepath = $args[2];
            if ($this->uploadFTPItem($id, $filepath)){
              $sender->sendMessage(TextFormat::GREEN."[FileBrowser] File sucessfully uploaded.");
              return true;
            }
            elseif ($this->uploadFTPItem($id, $filepath) === "credentialerror"){
              $sender->sendMessage(TextFormat::RED."[FileBrowser] FTP credentials incorrect. Try again.");
              return true;
            }
            elseif ($this->uploadFTPItem($id, $filepath) === "invalidID"){
              $sender->sendMessage(TextFormat::RED."[FileBrowser] FTP ID '".$id."' does not exist. Try again.");
              return true;
            }
            else{
              $sender->sendMessage(TextFormat::RED."[FileBrowser] File upload unsuccessful. Try again.");
              return true;
            }
          }
        }
        elseif ($args[1] === "explore"){
          if (!$sender->hasPermission("filebrowser.ftp.explore")){
            $sender->sendMessage(TextFormat::RED."[FileBrowser] Access denied!");
            return true;
          }
          elseif (!isset($args[2])){
            $sender->sendMessage(TextFormat::RED."/filebrowser ftp explore <id> <directory>");
            return true;
          }
          elseif (!isset($args[3])){
            $sender->sendMessage(TextFormat::RED."/filebrowser ftp explore <id> <directory>");
          }
          else{
            $directory = $args[3];
            $id = $args[2];
            $dirarr = $this->exploreFTPdirectory($directory, $id);
            if (!$this->exploreFTPdirectory($directory, $id)){
              $sender->sendMessage(TextFormat::RED."[FileBrowser] FTP exploration unsuccessful. Try again.");
              return true;
            }
            else{
              $sender->sendMessage("[FileBrowser] Files in directory '".$directory."':");
              foreach($dirarr as $files){
                $sender->sendMessage($files);
              }
              return true;
            }
          }
        }
      }
    }
  }
}
