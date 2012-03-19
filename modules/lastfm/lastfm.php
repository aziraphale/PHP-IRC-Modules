<?php

require_once './modules/load-dynamic-class.php';

class lastfm extends module {

    public $title     = "lastfm";
    public $author     = "Andrew Gillard";
    public $version = "0.1";
    public $date     = "2012-02-21";
    
    private static $urlClassname;
    private static $channelName = '#myChannel';
    private static $lastFmApiKey = "12c9d20754869173658b68c83f71c4e0";
    
    private static $users = array(
        // Define IRC nick -> Last.fm username mapping, e.g.:
        // 'exampleNick' => 'MyLastfmUser',
    );

    public function init() {
        self::$urlClassname = 'URL';
        loadDynamicClass(self::$urlClassname, 'urlreveal/url.class.php');
    }
    
    public function notice_lastfm($line, $args) {
        try {
            if (strcasecmp($args['cmd'], '!np') != 0)
                return;
            
            $fromnick = strtolower($line['fromNick']);
            
            if (in_array($fromnick, array()))
                throw new Exception("You have been banned from using this function.");
            
            $channel = self::$channelName;
            if (strpos($line['to'], '#') !== false) {
                $channel = $line['to'];
                $this->ircClass->notice($fromnick, "Please use a /notice or /msg directly to this bot to request this function, in order to avoid spamming public channels.");
            } elseif (strpos($args['arg1'], '#') !== false) {
                $channel = $args['arg1'];
            }
            
            if (!array_key_exists($fromnick, self::$users))
                throw new Exception("Your Last.fm account hasn't been configured in this module.");
            
            $jsonString = file_get_contents('http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks&user=' . self::$users[$fromnick] . '&limit=1&api_key=' . self::$lastFmApiKey . '&format=json');
            if (!$jsonString)
                throw new Exception("Failed to load the now-playing data from Last.fm!");
            
            $json = json_decode($jsonString);
            if (!$json)
                throw new Exception("The now-playing data sent by Last.fm appears to be invalid!");
            
            $trackOne = $json->recenttracks->track[0];
            if (strcasecmp($trackOne->{"@attr"}->nowplaying, 'true') != 0)
                throw new Exception("You don't appear to be currently playing anything in a music player configured to scrobble to Last.fm.");
            
            $artist = (string) $trackOne->artist->{"#text"};
            $trackName = (string) $trackOne->name;
            $url = (string) $trackOne->url;
            
            $outMsg = "%B" . substr($line['fromNick'], 0, 1) . "%B%B" . substr($line['fromNick'], 1) . "%R is listening to: %B$trackName%R by %B$artist%R (%C%BLACK$url%R)";
            
            $outMsg = self::parseMessageForFormattingCodes($outMsg);
            $this->ircClass->privMsg($channel, $outMsg);
        } catch (Exception $ex) {
            $this->ircClass->notice($fromnick, "ERROR: {$ex->getMessage()}");
        }
    }
    
    private static function parseMessageForFormattingCodes($message) {
        $uc = self::$urlClassname;
        $message = $uc::parseMessageForFormattingCodes($message);
        return $message;
    }
}

?> 

?>
