<?php

require_once './modules/load-dynamic-class.php';

class mobilelocation extends module {

    public $title     = "mobilelocation";
    public $author     = "Andrew Gillard <contact@andrewgillard.com>";
    public $version = "0.2";
    public $date     = "2011-08-13";
    
    private static $urlClassname;
    
    private static $users = array(
        // Define an array of IRC nicks to the temporary filename used to store the location data, e.g.:
        //  'MyNick' => '/tmp/irc-location_mynick',
        // This location must match the filename used by the location PHP HTTP script
    );
    private static $channelName = '#MYCHANNEL';
    private static $checkInterval = 2; // how frequently (in seconds) to check for the location filename
    private static $debug = false;
    
    private static $headings = array(
        'N' => 0,
        'NNE' => 22.5,
        'NE' => 45,
        'ENE' => 67.5,
        'E' => 90,
        'ESE' => 112.5,
        'SE' => 135,
        'SSE' => 157.5,
        'S' => 180,
        'SSW' => 202.5,
        'SW' => 225,
        'WSW' => 247.5,
        'W' => 270,
        'WNW' => 292.5,
        'NW' => 315,
        'NNW' => 337.5,
    );
    
    public function init() {
        self::$urlClassname = 'URL';
        loadDynamicClass(self::$urlClassname, 'urlreveal/url.class.php');
        
        self::debug("Clearing any old timer");
        $this->timerClass->removeTimer('mobileLocationCheckFiles');
        self::debug("Setting timer to look for files every " . self::$checkInterval . " seconds");
        $this->timerClass->addTimer('mobileLocationCheckFiles', $this, 'checkFiles', '', self::$checkInterval, false);
        
        if (self::$debug) {
            echo "¬ Debugging is enabled\r\n";
        } else {
            echo "¬ Debugging is disabled\r\n";
        }
    }
    
    private static function debug($m) {
        if (self::$debug) {
            echo "¬ $m\r\n";
        }
    }
    
    public function checkFiles() {
        if ($this->ircClass->getStatusRaw() < STATUS_CONNECTED_REGISTERED) {
            self::debug("Not yet registered...");
            return true;
        }
        
        foreach (self::$users as $username => $file) {
            self::debug("Looking for file '$file' for user '$username'");
            if (file_exists($file)) {
                if (!is_file($file)) {
                    self::debug("'$file' does not appear to be a file!");
                    continue;
                }
                if (!is_readable($file)) {
                    self::debug("Unable to read file '$file'");
                    continue;
                }
                $json = json_decode(file_get_contents($file));
                if (!$json) {
                    self::debug("Failed to JSON-decode the contents of '$file'");
                    continue;
                }
                if (!unlink($file)) {
                    self::debug("Unable to unlink '$file'");
                    continue;
                }
                
                $heading = false;
                $speed = false;
                if ($json->coords->heading !== null) {
                    $heading = $json->coords->heading;
                    foreach (self::$headings as $dir => $angle) {
                        if ($angle < 11.25) {
                            if ($heading > 360 + ($angle - 11.25)) {
                                $heading = $dir;
                                break;
                            }
                        }
                        
                        if ($heading > ($angle - 11.25) && $heading <= ($angle + 11.25)) {
                            $heading = $dir;
                            break;
                        }
                    }
                    
                    if (is_numeric($heading)) {
                        $heading = null;
                    }
                }
                
                if ($json->coords->speed !== null) {
                    $speed = round($json->coords->speed * 2.23693629);
                }
                
                $dirSpeed = '';
                if ($heading !== false && $speed !== false) {
                    $dirSpeed = ", heading %C%WHITE_BOLD$heading%R at%C%WHITE_BOLD $speed%R mph";
                } elseif ($heading) {
                    $dirSpeed = ", heading %C%WHITE_BOLD$heading%R";
                } elseif ($speed) {
                    $dirSpeed = ", travelling at %C%WHITE_BOLD$speed%R mph";
                }
                
                $accuracy = $json->coords->accuracy;
                if ($accuracy > 1000) {
                    $accuracy = round($accuracy / 1000, 1) . 'km';
                } else {
                    $accuracy = "{$accuracy}m";
                }
                
                $geocode = $json->coords->geocode;
                
                $mapsUrl = "http://maps.google.com/maps?q=" . rawurlencode($json->coords->pretty);
                $msg = "%C%WHITE_BOLD$username%R's current location is within $accuracy of:%C%WHITE_BOLD $geocode%R$dirSpeed %C%CYAN(=MAPSURL=)%R";
                $msg = self::parseMessageForFormattingCodes($msg);
                $msg = str_replace('=MAPSURL=', $mapsUrl, $msg);
                $this->ircClass->privMsg(self::$channelName, $msg);
            }
        }
        
        return true;
    }
    
    private static function parseMessageForFormattingCodes($message) {
        $uc = self::$urlClassname;
        $message = $uc::parseMessageForFormattingCodes($message);
        return $message;
    }
}

?>