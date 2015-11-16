<?php
namespace TimeZones;
use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
class TimeZones extends PluginBase {
    # a commision plugin, view here: https://forums.pocketmine.net/threads/time-zones.7624/
    public function onEnable(){
        
    	if(!is_dir($this->getDataFolder())) {
      	   mkdir($this->getDataFolder());
           $this->saveDefaultConfig();
    	}
        $this->getLogger()->info("TimeZones plugin enabled!");
        $this->getLogger()->info("Current time is:");
        
        $commanderPackage = $this->getConfig()->get("Corrector");
        $correctHour = date('h');
        $dater = $correctHour + $commanderPackage;
        $timers = date('i:s');
        $color = $this->getConfig()->get("Color");
        
        if ($color === "Blue"){
            $this->getLogger()->info(TEXTFORMAT::BLUE.$dater . ":" . $timers);
        }
        elseif ($color === "Red"){
            $this->getLogger()->info(TEXTFORMAT::RED.$dater. ":" . $timers);
            
        }
        elseif ($color === "Green"){
            $this->getLogger()->info(TEXTFORMAT::GREEN.$dater. ":" . $timers);
            
        }
        elseif ($color === "Aqua"){
            $this->getLogger()->info(TEXTFORMAT::AQUA.$dater. ":" . $timers);
           
            
        }
        elseif ($color === "Yellow"){
            $this->getLogger()->info(TEXTFORMAT::YELLOW.$dater. ":" . $timers);
            
        }
        elseif ($color === "White"){
            $this->getLogger()->info(TEXTFORMAT::WHITE.$dater. ":" . $timers);
            
        }
        elseif ($color === "Gray"){
            $this->getLogger()->info(TEXTFORMAT::GRAY.$dater. ":" . $timers);
            
        }
        elseif ($color === "Purple"){
            $this->getLogger()->info(TEXTFORMAT::PURPLE.$dater. ":" . $timers);
            
        }
        elseif ($color === "Black"){
            $this->getLogger()->info(TEXTFORMAT::BLACK.$dater. ":" . $timers);
            
        }
        elseif ($color === "Plain"){
            $this->getLogger()->info($dater. ":" . $timers);
            
        }
        else{
            $this->getLogger()->critical("You have not set up color format correctly!");
            $this->getLogger()->info($dater. ":" . $timers);
        }
        
    }  
   
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
    	if(strtolower($command->getName()) === "tz"){
        
	        $commanderPackage = $this->getConfig()->get("Corrector");
	        $correctHour = date('h');
	        $dater = $correctHour + $commanderPackage;
	        $timers = date('i:s');
	        $color = $this->getConfig()->get("Color");
	        
	        }
	        if ($color === "Blue"){
	            $sender->sendMessage(TEXTFORMAT::BLUE.$dater . ":" . $timers);
	            
	        }
	        elseif ($color === "Red"){
	            $sender->sendMessage(TEXTFORMAT::RED.$dater. ":" . $timers);
	            
	        }
	        elseif ($color === "Green"){
	            $sender->sendMessage(TEXTFORMAT::GREEN.$dater. ":" . $timers);
	            
	        }
	        elseif ($color === "Aqua"){
	            $sender->sendMessage(TEXTFORMAT::AQUA.$dater. ":" . $timers);
	           
	            
	        }
	        elseif ($color === "Yellow"){
	            $sender->sendMessage(TEXTFORMAT::YELLOW.$dater. ":" . $timers);
	            
	        }
	        elseif ($color === "White"){
	            $sender->sendMessage(TEXTFORMAT::WHITE.$dater. ":" . $timers);
	            
	        }
	        elseif ($color === "Gray"){
	            $sender->sendMessage(TEXTFORMAT::GRAY.$dater. ":" . $timers);
	            
	        }
	        elseif ($color === "Purple"){
	            $sender->sendMessage(TEXTFORMAT::PURPLE.$dater. ":" . $timers);
	            
	        }
	        elseif ($color === "Black"){
	            $sender->sendMessage(TEXTFORMAT::BLACK.$dater. ":" . $timers);
	            
	        }
	        elseif ($color === "Plain"){
	            $sender->sendMessage($dater. ":" . $timers);
	            
	        }
	        else{
	            $sender->sendMessage(TEXTFORMAT::RED."You have not set up color format correctly!");
	            $sender->sendMessage($dater. ":" . $timers);
	        }
	        return true;
        
    	}
    
}
    
    
