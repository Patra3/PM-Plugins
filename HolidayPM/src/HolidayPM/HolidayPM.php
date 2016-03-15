<?php

namespace HolidayPM;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class HolidayPM extends PluginBase {
    public $holidaylist;
    public static function getCurrentHoliday(){
        /**
         * Returns the current holiday, as a string (or null if not found).
         * @return NULL/Mixed
         * */
        $type = $this->getConfig()->get("holiday_mode");
        if ($type === "auto"){
            // holiday_mode is automatic
            foreach($this->holidaylist as $dates){
                $datel = explode(",", $dates);
                // $datel[0] = month ; $datel[1] = day ; $datel[2] = year
                if ($datel[0] == date("m")){
                    // same month...
                    if (($datel[1] - date("d")) <= "10"){
                        // 10 days or less! The holiday is coming!
                        return array_search($dates, $this->holidaylist);
                    }
                }
            }
            return NULL;
        }
        elseif ($type === "manual"){
            // holiday mode is manual
            foreach($this->holidaylist as $dates){
                $datel = explode(",", $dates);
                if (isset($datel[3])){
                    // custom holiday, will set $datel[3] to 'custom'
                    $on_off = NULL;
                }
                else{
                    $on_off = $this->getConfig()->get(array_search($dates, $this->holidaylist));
                }
                if ($on_off === "on"){
                    return array_search($dates, $this->holidaylist);
                }
            }
            return NULL;
        }
        else{
            // Format is unreadable.
            return NULL;
        }
    }
    public function onEnable(){
        if (!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
            mkdir($this->getDataFolder()."/_customs"); // custom holidays folder.
            $this->saveDefaultConfig();
        }
        if (base64_decode($this->getConfig()->get("version")) != "1.0.0"){
            $this->getLogger()->error("[~] Your version of config.yml is unrecognizable or outdated.");
            $this->getLogger()->warning("[~] HolidayPM will attempt to continue running.");
        }
        else{
            $this->getLogger()->info(TextFormat::GREEN."[~] Version is up-to-date");
        }
        $this->holidaylist = array(
            "new_year" => "1,1,2017",
            "christmas" => "12,25,2016",
            "day_of_the_dead" => "11,1,2016",
            "chinese_new_years" => "2,8,2016",
            "valentines" => "2,14,2016",
            "st_patricks" => "3,17,2016",
            "independence_day" => "7,4,2016",
            "halloween" => "10,31,2016",
            "hanukkah" => "12,24,2016",
            "thanksgiving" => "11,24,2016"
            );
        $timezone = $this->getConfig()->get("timezone");
        if (date_default_timezone_set($timezone)){
            $this->getLogger()->info(TextFormat::GREEN."[~] Timezone set to ".$timezone."!");
        }
        else{
            $this->getLogger()->error("[~] Timezone defined incorrectly! This may cause holidays to mess up!");
        }
    }
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        $holiday = self::getCurrentHoliday();
        if (strtolower($command->getName()) === "holidaypm"){
            if (!isset($args[0])){
                
            }
        }
    }
}