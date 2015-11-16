<?php
namespace HalloweenPM;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
//﻿ＨａｌｌｏｗｅｅｎＰＭ  －  ＨｏｔＦｉｒｅｙＤｅａｔｈ
class HalloweenAPI{
    /*
    * This is the official API for HalloweenPM plugin.
    * 
    * How to use:
    * make sure to use \ this script
    * $someVariable = new HalloweenAPI();
    * call different functions using $someVariable->someFunction($argument);
    */ 
    public function hasCandy($datafolder, $player){
        /*
        * Returns True if a user has candy, or False if the user doesn't have candy.
        */
        $directory = $datafolder."/".$player."/";
        //     =====CANDIES=====
        $snickers = file_get_contents($directory."snickers.txt");
        $tootsieroll = file_get_contents($directory."tootsieroll.txt");
        $hersheybar = file_get_contents($directory."hersheybar.txt");
        $buttercup = file_get_contents($directory."buttercup.txt");
        $butterfinger = file_get_contents($directory."butterfingers.txt");
        $peanutbcup = file_get_contents($directory."peanutbuttercup.txt");
        $twix = file_get_contents($directory."twix.txt");
        $musketeers = file_get_contents($directory."musketeers.txt");
        
        //     =====ENDOFCANDIES=====
        
        if ($snickers === "yes"){
            return True;
        }
        else{
            return False;
        }
        if ($tootsieroll === "yes"){
            return True;
        }
        else{
            return False;
        }
        if ($hersheybar === "yes"){
            return True;
        }
        else{
            return False;
        }
        if ($buttercup === "yes"){
            return True;
        }
        else{
            return False;
        }
        if ($butterfinger === "yes"){
            return True;
        }
        else{
            return False;
        }
        if ($peanutbcup === "yes"){
            return True;
        }
        else{
            return False;
        }
        if ($twix === "yes"){
            return True;
        }
        else{
            return False;
        }
        if ($musketeers === "yes"){
            return True;
        }
        else{
            return False;
        }
    }
    public function giveUsage(){
        // returns usage of the give command. You'll have to relay the message.
        return "/candy give <player>";
    }
    public function eatUsage(){
        return "/candy eat <type>";
    }
    public function giveCandyList($sender){
        // gives the $sender the list of candies and prices.
        $sender->sendMessage("Candies: Snickers, TootsieRoll, HersheyBar, Buttercup," .
            " Butterfinger, PeanutButterCup, Twix, Musketeers.");
        
        
    }
}
class HandyDevUtils{
    /*
    * This is just for handy utilities that I will be using during development.
    * Most of the contents just include file checking and folder tools.
    * Nothing important should be in this class, and so this class shouldn't
    * be used for other plugins. Thanks :)
    */
    public function checkPlayerRegistered($datafolder, $player){
        /*
        * this function is used to run regular directory checks.
        * to make sure files are there so it doesn't crash,
        * when a player is being given something.
        */
        $directory = $datafolder."/".$player."/";
        if (!is_dir($directory)){
            mkdir($directory);
        }
        if (!is_file($directory."snickers.txt")){
            $handle = fopen($directory."snickers.txt", "w+");
            fwrite($handle, "no");
            fclose($handle);
        }
        if (!is_file($directory."tootsieroll.txt")){
            $handle = fopen($directory."tootsieroll.txt", "w+");
            fwrite($handle, "no");
            fclose($handle);
        }
        if (!is_file($directory."hersheybar.txt")){
            $handle = fopen($directory."hersheybar.txt", "w+");
            fwrite($handle, "no");
            fclose($handle);
        }
        if (!is_file($directory."buttercup.txt")){
            $handle = fopen($directory."buttercup.txt", "w+");
            fwrite($handle, "no");
            fclose($handle);
        }
        if (!is_file($directory."butterfingers.txt")){
            $handle = fopen($directory."butterfingers.txt", "w+");
            fwrite($handle, "no");
            fclose($handle);
        }
        if (!is_file($directory."peanutbuttercup.txt")){
            $handle = fopen($directory."peanutbuttercup.txt", "w+");
            fwrite($handle, "no");
            fclose($handle);
        }
        if (!is_file($directory."twix.txt")){
            $handle = fopen($directory."twix.txt", "w+");
            fwrite($handle, "no");
            fclose($handle);
        }
        if (!is_file($directory."musketeers.txt")){
            $handle = fopen($directory."musketeers.txt", "w+");
            fwrite($handle, "no");
            fclose($handle);
        }
    }
    
}
class HalloweenPM extends PluginBase {
    public function onEnable(){
        if (!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
        }
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        
      // ===declaring information===
      $player = $sender->getName();
      $directory = $this->getDataFolder()."/".$player."/";
      $datafolder = $this->getDataFolder();
      $ess = new HalloweenAPI();
      $evs = new HandyDevUtils();
      $evs->checkPlayerRegistered($datafolder, $player);
      // ===end of declaration===
      
      switch($command->getName()){
          case "frankenstein":
              $msg = implode(" ", $args);
              $fullmsg = TextFormat::BLUE.TextFormat::BOLD."[Frankenstein] ".TextFormat::RED.$msg;
              $this->getServer()->broadcastMessage($fullmsg);
              return True;
          case "death":
              $msg = implode(" ", $args);
              $fullmsg = TextFormat::BLACK.TextFormat::BOLD."[Death] ".TextFormat::RED.$msg;
              $this->getServer()->broadcastMessage($fullmsg);
              return True;
          case "pumpkin":
              $msg = implode(" ", $args);
              $fullmsg = TextFormat::ORANGE.TextFormat::BOLD."[Pumpkin] ".TextFormat::YELLOW.$msg;
              $this->getServer()->broadcastMessage($fullmsg);
              return True;
          case "zombie":
              $msg = implode(" ", $args);
              $fullmsg = TextFormat::DARK_BLUE.TextFormat::BOLD."[Zombie] ".TextFormat::RED.$msg;
              $this->getServer()->broadcastMessage($fullmsg);
              return True;
          
          case "candy":
              if (!isset($args[0])){
                  return True;
              }
              else{
                  if ($args[0] === "have?"){
                      $player = $sender->getName();
                      $evs->checkPlayerRegistered($datafolder, $player);
                      $result = $ess->hasCandy($datafolder, $player);
                      
                      if ($result === True){
                          $sender->sendMessage("You have candy!");
                          return True;
                      }
                      else{
                          $sender->sendMessage("No candy for you!");
                          return True;
                      }
                  }
                  elseif ($args[0] === "eat"){
                      if (!isset($args[1])){
                          $msg = $ess->eatUsage();
                          $sender->sendMessage(TextFormat::RED."Usage: ".$msg);
                          return True;
                      }
                      
                      else{
                          $snickers = file_get_contents($directory."snickers.txt");
                          $twix = file_get_contents($directory."twix.txt");
                          $butterfinger = file_get_contents($directory."butterfingers.txt");
                          $tootsieroll = file_get_contents($directory."tootsieroll.txt");
                          $peanutbutter = file_get_contents($directory."peanutbuttercup.txt");
                          $hershey = file_get_contents($directory."hersheybar.txt");
                          $buttercup = file_get_contents($directory."buttercup.txt");
                          $musketeers = file_get_contents($directory."musketeers.txt");
                          
                          if ($args[1] === "Snickers"){
                              if ($snickers === "yes"){
                                  unlink($directory."snickers.txt");
                                  $handle = fopen($directory."snickers.txt", "w+");
                                  fwrite($handle, "no");
                                  fclose($handle);
                                  $sender->sendMessage("You ate some Snickers!");
                                   
                                  return True;
                              }
                              elseif ($snickers === "no"){
                                  $sender->sendMessage("Sorry, you don't have Snickers...");
                                  return True;
                              }
                          }
                          elseif ($args[1] === "Twix"){
                              if ($twix === "yes"){
                                  unlink($directory."twix.txt");
                                  $handle = fopen($directory."twix.txt", "w+");
                                  fwrite($handle, "no");
                                  fclose($handle);
                                  $sender->sendMessage("You ate some Twix!");
                                   
                                  return True;
                              }
                              elseif ($twix === "no"){
                                  $sender->sendMessage("Sorry, you don't have Twix...");
                                  return True;
                              }
                          }
                          elseif ($args[1] === "TootsieRoll"){
                              if ($tootsieroll === "yes"){
                                  unlink($directory."tootsieroll.txt");
                                  $handle = fopen($directory."tootsieroll.txt", "w+");
                                  fwrite($handle, "no");
                                  fclose($handle);
                                  $sender->sendMessage("You ate some Tootsie Rolls!");
                                   
                                  return True;
                              }
                              elseif ($tootsieroll === "no"){
                                  $sender->sendMessage("Sorry, you don't have some tootsie rolls...");
                                  return True;
                              }
                          }
                          elseif ($args[1] === "HersheyBar"){
                              if ($hersheybar === "yes"){
                                  unlink($directory."hersheybar.txt");
                                  $handle = fopen($directory."hersheybar.txt", "w+");
                                  fwrite($handle, "no");
                                  fclose($handle);
                                  $sender->sendMessage("You ate a Hershey Bar!");
                                  
                                  return True;
                              }
                              elseif ($hersheybar === "no"){
                                  $sender->sendMessage("Sorry, you don't have Hershey bars...");
                                  return True;
                              }
                          }
                          elseif ($args[1] === "Butterfinger"){
                              if ($butterfinger === "yes"){
                                  unlink($directory."butterfingers.txt");
                                  $handle = fopen($directory."butterfingers.txt", "w+");
                                  fwrite($handle, "no");
                                  fclose($handle);
                                  $sender->sendMessage("You ate some Butterfingers!");
                                   
                                  return True;
                              }
                              elseif ($butterfinger === "no"){
                                  $sender->sendMessage("Sorry, you don't have butterfingers...");
                                  return True;
                              }
                          }
                          elseif ($args[1] === "PeanutButterCup"){
                              if ($peanutbutter === "yes"){
                                  unlink($directory."peanutbuttercup.txt");
                                  $handle = fopen($directory."peanutbuttercup.txt", "w+");
                                  fwrite($handle, "no");
                                  fclose($handle);
                                  $sender->sendMessage("You ate some Peanut butter cups!");
                                   
                                  return True;
                              }
                              elseif ($peanutbutter === "no"){
                                  $sender->sendMessage("Sorry, you don't have peanut butter cups...");
                                  return True;
                              }
                          }
                          elseif ($args[1] === "Musketeers"){
                              if ($musketeers === "yes"){
                                  unlink($directory."musketeers.txt");
                                  $handle = fopen($directory."musketeers.txt", "w+");
                                  fwrite($handle, "no");
                                  fclose($handle);
                                  $sender->sendMessage("You ate a Musketeer!");
                                   
                                  return True;
                              }
                              elseif ($musketeers === "no"){
                                  $sender->sendMessage("Sorry, you don't have musketeers...");
                                  return True;
                              }
                          }
                          elseif ($args[1] === "Buttercup"){
                              if ($buttercup === "yes"){
                                  unlink($directory."buttercup.txt");
                                  $handle = fopen($directory."buttercup.txt", "w+");
                                  fwrite($handle, "no");
                                  fclose($handle);
                                  $sender->sendMessage("You ate a Buttercup!");
                                   
                                  return True;
                              }
                              elseif ($buttercup === "no"){
                                  $sender->sendMessage("Sorry, you don't have buttercups...");
                                  return True;
                              }
                          }
                      }
                  }
                  elseif ($args[0] === "give"){
                      if (!$ess->hasCandy($datafolder, $player)){
                          $sender->sendMessage("You don't even have candy to give...");
                          return True;
                      }
                      if (!isset($args[1])){
                          $msg = $ess->giveUsage();
                          $sender->sendMessage(TextFormat::RED."Usage: ".$msg);
                          return True;
                      }
                      else{
                          $player = $sender->getName();
                          if ($ess->hasCandy($datafolder, $player)){
                              $snickers = file_get_contents($directory."snickers.txt");
                              $twix = file_get_contents($directory."twix.txt");
                              $butterfinger = file_get_contents($directory."butterfingers.txt");
                              $peanutbutter = file_get_contents($directory."peanutbuttercup.txt");
                              $tootsieroll = file_get_contents($directory."tootsieroll");
                              $hershey = file_get_contents($directory."hersheybar.txt");
                              $buttercup = file_get_contents($directory."buttercup.txt");
                              $musketeers = file_get_contents($directory."musketeers.txt");
                              
                              $negdir = $datafolder."/".$args[1]."/";
                              
                              
                              if ($snickers === "yes"){
                                   unlink($negdir."snickers.txt");
                                   $handle = fopen($negdir."snickers.txt", "w+");
                                   fwrite($handle, "yes");
                                   fclose($handle);
                                   
                                   return True;
                              }
                              elseif ($twix === "yes"){
                                   unlink($negdir."twix.txt");
                                   $handle = fopen($negdir."twix.txt", "w+");
                                   fwrite($handle, "yes");
                                   fclose($handle);
                                   
                                   return True;
                              }
                              elseif ($butterfinger === "yes"){
                                   unlink($negdir."butterfingers.txt");
                                   $handle = fopen($negdir."butterfingers.txt", "w+");
                                   fwrite($handle, "yes");
                                   fclose($handle);
                                   
                                   return True;
                              }
                              elseif ($tootsieroll === "yes"){
                                   unlink($negdir."tootsieroll.txt");
                                   $handle = fopen($negdir."tootsieroll.txt", "w+");
                                   fwrite($handle, "yes");
                                   fclose($handle);
                                   
                                   return True;
                              }
                              elseif ($peanutbutter === "yes"){
                                   unlink($negdir."peanutbuttercup.txt");
                                   $handle = fopen($negdir."peanutbuttercup.txt", "w+");
                                   fwrite($handle, "yes");
                                   fclose($handle);
                                   
                                   return True;
                              }
                              elseif ($hershey === "yes"){
                                   unlink($negdir."hersheybar.txt");
                                   $handle = fopen($negdir."hersheybar.txt", "w+");
                                   fwrite($handle, "yes");
                                   fclose($handle);
                                   
                                   return True;
                              }
                              elseif ($buttercup === "yes"){
                                   unlink($negdir."buttercup.txt");
                                   $handle = fopen($negdir."buttercup.txt", "w+");
                                   fwrite($handle, "yes");
                                   fclose($handle);
                                   
                                   return True;
                              }
                              elseif ($musketeers === "yes"){
                                   unlink($negdir."musketeers.txt");
                                   $handle = fopen($negdir."musketeers.txt", "w+");
                                   fwrite($handle, "yes");
                                   fclose($handle);
                                   
                                   return True;
                              }
                              
                              
                          }
                      }
                  }
                  elseif ($args[0] === "buy"){
                      $player = $sender->getName();
                      $directory = $this->getDataFolder()."/".$player."/";
                      if (!isset($args[1])){
                          $ess->giveCandyList($sender);
                          return True;
                      }
                      else{
                          if ($args[1] === "Snickers"){
                              $player = $sender->getName();
                              $sender->sendMessage("You bought some snickers, and now you have 'em!");
                              
                              unlink($directory."snickers.txt");
                              $handle = fopen($directory."snickers.txt", "w+");
                              fwrite($handle, "yes");
                              fclose($handle);
                              return True;
                          }
                          elseif ($args[1] === "TootsieRoll"){
                              $player = $sender->getName();
                              $sender->sendMessage("You bought some Tootsie Rolls, and now you have 'em!");
                              
                              unlink($directory."tootsieroll.txt");
                              $handle = fopen($directory."tootsieroll.txt", "w+");
                              fwrite($handle, "yes");
                              fclose($handle);
                              return True;
                          }
                          elseif ($args[1] === "HersheyBar"){
                              $player = $sender->getName();
                              $sender->sendMessage("You bought a Hershey bar, and now you have 'em!");
                              
                              unlink($directory."hersheybar.txt");
                              $handle = fopen($directory."hersheybar.txt", "w+");
                              fwrite($handle, "yes");
                              fclose($handle);
                              return True;
                          }
                          elseif ($args[1] === "Buttercup"){
                              $player = $sender->getName();
                              $sender->sendMessage("You bought a buttercup, and now you have 'em!");
                              
                              unlink($directory."buttercup.txt");
                              $handle = fopen($directory."buttercup.txt", "w+");
                              fwrite($handle, "yes");
                              fclose($handle);
                              return True;
                          }
                          elseif ($args[1] === "Butterfingers"){
                              $player = $sender->getName();
                              $sender->sendMessage("You bought some Butterfingers, and now you have 'em!");
                              
                              unlink($directory."butterfingers.txt");
                              $handle = fopen($directory."butterfingers.txt", "w+");
                              fwrite($handle, "yes");
                              fclose($handle);
                              return True;
                          }
                          elseif ($args[1] === "PeanutButterCup"){
                              $player = $sender->getName();
                              $sender->sendMessage("You bought some peanut butter cups, and now you have 'em!");
                              
                              unlink($directory."peanutbuttercup.txt");
                              $handle = fopen($directory."peanutbuttercup.txt", "w+");
                              fwrite($handle, "yes");
                              fclose($handle);
                              return True;
                          }
                          elseif ($args[1] === "Twix"){
                              $player = $sender->getName();
                              $sender->sendMessage("You bought a Twix, and now you have 'em!");
                              
                              unlink($directory."twix.txt");
                              $handle = fopen($directory."twix.txt", "w+");
                              fwrite($handle, "yes");
                              fclose($handle);
                              return True;
                          }
                          elseif ($args[1] === "Musketeers"){
                              $player = $sender->getName();
                              $sender->sendMessage("You bought a Musketeer, and now you have 'em!");
                              
                              unlink($directory."musketeers.txt");
                              $handle = fopen($directory."musketeers.txt", "w+");
                              fwrite($handle, "yes");
                              fclose($handle);
                              return True;
                          }
                          else{
                              $sender->sendMessage("We don't currently have those in stock!");
                              return True;
                          }
                      }
                  }
              }
      }
    }
}
