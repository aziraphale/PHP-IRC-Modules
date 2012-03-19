<?php

require_once './modules/load-dynamic-class.php';

class twitter extends module {

    public $title     = "twitter";
    public $author     = "Andrew Gillard <contact@andrewgillard.com>";
    public $version = "0.1";
    public $date     = "2011-07-16";
    
    private $reconnectionDelay = 0;
    private $headersRead = false;
    private $readBuffer = '';
    private $lastDataTime = 0;
    private $connectionTime = 0;
    
    private static $twitterUsername = 'SET_TWITTER_USERNAME_HERE';
    private static $twitterPassword = 'SET_TWITTER_PASSWORD_HERE';
    private static $channelName = '#SET_CHANNEL_NAME';
    private static $adminUserNick = 'IRC_NICK_OF_ADMIN';
    private static $publicRetweetsEnabled = true;
    private static $readTimerInterval = 1; // seconds
    private static $dataTimeout = 120; // 2 minutes
    private static $connectionTimeout = 60; // 1 minute
    
    private static $urlClassname;
    
    /**
     * 
     * @link https://api.twitter.com/1/users/show.json?screen_name=ev
     * @var array(int=>array(string,string,...))
     */
    private static $followList = array(
        // Twitter user account format:
        // USER_ID => array('user'=>'TWITTER_USER_NAME', 'nicks'=>array('IRC_NICK1'[, 'IRC_NICK2'[, ...]]), 'notice'=>SEND_NOTICES_INSTEAD_OF_QUERIES)
        // e.g.:
        // 20 => array('user'=>'ev', 'nicks'=>array('ev', 'ev_away'), 'notice'=>false)
    );
    
    /** @var socket */
//    private $socketClass;
    private $followListNames = array();
    private $followListIds = array();
    
    public function init() {
        $this->followListIds = array_keys(self::$followList);
        foreach (self::$followList as $u) {
            if ($u['nicks']) {
                $this->followListNames[] = $u['user'];
            }
        }
        $this->delayedConnect();
        
        self::$urlClassname = 'URL';
        loadDynamicClass(self::$urlClassname, 'urlreveal/url.class.php');
    }
    
    private function delayedConnect($delay=2, $now=true) {
        $this->disconnect();
        if ($now) {
            echo "Setting a timer to try connecting now and every $delay seconds thereafter...\r\n";
        } else {
            echo "Setting a timer to try connecting in $delay seconds' time and every $delay seconds thereafter...\r\n";
        }
        $this->timerClass->addTimer('twitterConnectWhenReady', $this, 'connectWhenReady', '', $delay, $now);
        
    }
    
    public function connectWhenReady() {
        echo "connectWhenReady()...\r\n";
        if ($this->ircClass->getStatusRaw() < STATUS_CONNECTED_REGISTERED) {
            //Wait...
            echo "Still waiting to connect...\r\n";
            return true;
        } else {
            $this->makeNewConnection();
            return false;
        }
    }
    
    private function makeNewConnection() {
        $this->disconnect();
//        if (isset($GLOBALS['twitterConnection'])) {
//            @$GLOBALS['twitterConnection']->disconnect();
//            unset($GLOBALS['twitterConnection']);
//        }
        
        echo "Connecting to Twitter...\r\n";
        
        $postData = array(  'follow' => join(',', $this->followListIds),
                            'track' => '@' . join(',@', $this->followListNames),
        );
//        $additionalHeaders = array("User-Agent: PHP-IRC Twitter Connection v{$this->version}");
//        
//        $params = array();
//        $params['http'] = array();
//        $params['http']['method'] = 'POST';
//        $params['http']['content'] = http_build_query($postData);
//        $params['http']['header'] = $additionalHeaders;

//        if ($fp = fopen("http://" . self::$twitterUsername . ":" . self::$twitterPassword . "@stream.twitter.com/1/statuses/filter.json", 'rb', false, stream_context_create($params))) {
        if ($fp = fsockopen('ssl://stream.twitter.com', 443, $errno, $errstr, 5)) {
        //"http://" . self::$twitterUsername . ":" . self::$twitterPassword . "@stream.twitter.com/1/statuses/filter.json", 'rb', false, stream_context_create($params))) {
            $GLOBALS['twitterConnection'] = $fp;
            echo "Connected to Twitter!\r\n";
            stream_set_timeout($fp, 3);
            stream_set_blocking($fp, 0);
            
            $postDataStr = http_build_query($postData, '', '&');
            $postDataLength = strlen($postDataStr);
            
//            fwrite($fp, 'HTTP/1.0 POST /1/statuses/filter.json?follow=' . join(',', $this->followListIds) . '&track=@' . join(',@', $this->followListNames) . "\r\n");
            $this->connWrite("POST /1/statuses/filter.json HTTP/1.0\r\n");
            $this->connWrite("Host: stream.twitter.com\r\n");
            $this->connWrite('Authorization: ' . base64_encode(self::$twitterUsername . ':' . self::$twitterPassword) . "\r\n");
            $this->connWrite("User-Agent: PHP-IRC Twitter Connection v{$this->version} ({$this->date})\r\n");
            $this->connWrite("Content-Type: application/x-www-form-urlencoded\r\n");
            $this->connWrite("Content-Length: $postDataLength\r\n");
            $this->connWrite("\r\n");
            $this->connWrite("$postDataStr");
            echo "Twitter HTTP request sent. Awaiting response...\r\n";
            
            $this->lastDataTime = time();
            $this->connectionTime = time();
            $this->timerClass->addTimer('twitterConnectionRead', $this, 'connectionReadTimer', '', self::$readTimerInterval, true);
//            if (false !== ($res = @stream_get_contents($fp))) {
//                return $res;
//            }
        } else {
            echo "Failed to connect to Twitter [$errno]: $errstr\r\n";
            $this->reconnectAfterTcpError();
        }
        
//        $conn = new connection("www.lorddeath.net", "80", 5);
//        $conn = new connection("stream.twitter.com", "80", 15);
//        $GLOBALS['twitterConnection'] = $conn;
//        $conn->setTransTimeout(10);
//        
//        $conn->setSocketClass($this->socketClass);
//        $conn->setIrcClass($this->ircClass);
//        $conn->setTimerClass($this->timerClass);
//        $conn->setCallbackClass($this);
//        
//        $conn->init();
//        
//        if ($conn->getError()) {
//            echo "!TWITTER ERROR!: Error connecting: {$conn->getErrorMsg()}\r\n";
//        }
    }
    
    private function connWrite($string) {
        if (!empty($GLOBALS['twitterConnection']) && is_resource($GLOBALS['twitterConnection'])) {
            echo ">> $string\r\n";
            fwrite($GLOBALS['twitterConnection'], $string);
        }
    }
    
    private function disconnect() {
        if (!empty($GLOBALS['twitterConnection']) && is_resource($GLOBALS['twitterConnection'])) {
            echo "TWITTER: Closing connection...\r\n";
            fclose($GLOBALS['twitterConnection']);
            $this->timerClass->removeTimer('twitterConnectionRead');
        }
        $this->readBuffer = '';
        $this->headersRead = false;
    }
    
    public function connectionReadTimer($args) {
//        echo "Twitter timer elapsed.\r\n";
        if (!is_resource($GLOBALS['twitterConnection']))
            return false;
        
        if ($this->connectionTime) {
            if ((time() - $this->connectionTime) > self::$connectionTimeout) {
                //No data for $connectionTimeout seconds after connect
                $this->ircClass->privMsg(self::$adminUserNick, "Connection timeout elapsed! Reconnecting in 10 seconds.");
                echo "Connection timeout elapsed! Reconnecting in 10 seconds.\r\n";
                
                $this->disconnect();
                $this->delayedConnect(10, false);
                return false;
            }
        } else {
            if ((time() - $this->lastDataTime) > self::$dataTimeout) {
                //No data for $dataTimeout seconds
                $this->ircClass->privMsg(self::$adminUserNick, "Data timeout elapsed! Reconnecting in 10 seconds.");
                echo "Data timeout elapsed! Reconnecting in 10 seconds.\r\n";
                
                $this->disconnect();
                $this->delayedConnect(10, false);
                return false;
            }
        }
        
        while ($data = fread($GLOBALS['twitterConnection'], 4096)) {
            $this->readBuffer .= $data;
        }
        
        while(($nlPos = strpos($this->readBuffer, "\n")) !== false) {
            $this->lastDataTime = time();
            if ($this->connectionTime) {
                $this->connectionTime = 0;
            }
            
            $line = rtrim(substr($this->readBuffer, 0, $nlPos), "\r");
            $this->readBuffer = substr($this->readBuffer, $nlPos + 1);
            
            $this->handleLine($line);
        }
        
        return true;
    }
    
    public function _timerReconnectExpire($arguments) {
        $this->makeNewConnection();
        return false;
    }
    
    private function reconnectAfterSetDelay() {
        echo "!TWITTER: Reconnecting after {$this->reconnectionDelay} seconds...\r\n";
        $this->timerClass->addTimer("twitterReconnect", $this, "_timerReconnectExpire", "", $this->reconnectionDelay, false);
    }
    
    private function reconnectAfterTcpError() {
        // 0.25, 0.5, 0.75, 1, 1.25 .. 16 secs
        if ($this->reconnectionDelay < 16) {
            $this->reconnectionDelay += 0.25;
        }
        
        /** @todo Debugging code; remove */
//        $this->reconnectionDelay = 30;
        
        $this->reconnectAfterSetDelay();
    }
    
    private function reconnectAfterHttpError() {
        // 10, 20, 40 .. 240 secs
        if ($this->reconnectionDelay < 240) {
            if ($this->reconnectionDelay <= 0) {
                $this->reconnectionDelay = 10;
            } else {
                $this->reconnectionDelay *= 2;
            }
        }
        
        /** @todo Debugging code; remove */
//        $this->reconnectionDelay = 30;
        
        $this->reconnectAfterSetDelay();
    }
    
    public function onTransferTimeout(/** @var connection */ $connection) {
        echo "!TWITTER ERROR: Connection transfer timed out.\r\n";
        $connection->disconnect();
        $this->reconnectAfterTcpError();
    }

    public function onConnectTimeout(/** @var connection */ $connection) {
        echo "!TWITTER ERROR: Connection timed out.\r\n";
        @$connection->disconnect();
        $this->reconnectAfterTcpError();
    }

    public function onConnect(/** @var connection */ $connection) {
        echo "Twitter connection established. Sending HTTP request...\r\n";
        
        $this->socketClass->sendSocket($connection->getSockInt(), 'HTTP/1.0 GET /1/statuses/filter.json?follow=' . join(',', $this->followListIds) . '&track=@' . join(',@', $this->followListNames) . "\r\n");
        $this->socketClass->sendSocket($connection->getSockInt(), "Host: stream.twitter.com\r\n");
        $this->socketClass->sendSocket($connection->getSockInt(), 'Authorization: ' . base64_encode(self::$twitterUsername . ':' . self::$twitterPassword) . "\r\n");
        $this->socketClass->sendSocket($connection->getSockInt(), "User-Agent: PHP-IRC Twitter Connection v{$this->version}\r\n");
        $this->socketClass->sendSocket($connection->getSockInt(), "\r\n");
    }

    public function onRead(/** @var connection */ $connection) {
        if ($this->socketClass->hasLine($connection->getSockInt())) {
            $line = $this->socketClass->getQueueLine($connection->getSockInt());
            $this->handleLine($line);
            
            if ($this->socketClass->hasLine($connection->getSockInt()))
                return true;
        }
        return false;
    }

    public function onDead(/** @var connection */ $connection) {
        @$connection->disconnect();
        $this->reconnectAfterTcpError();
    }
    
    private function handleLine($line) {
        echo "#Twitter# Read line: $line\r\n";
        if (!$this->headersRead) {
//            echo "Headers not yet read...\r\n";
            if (preg_match('#^HTTP/\d\.\d\s+(\d+)\s*(.*)#i', $line, $m)) {
                $this->handleHttpStatus($m[1], $m[2]);
            } elseif (preg_match('#^([A-Za-z0-9\-]+): (.*)#', $line, $m)) {
                $this->handleHttpHeader($m[1], $m[2]);
            } elseif (!$line) {
                $this->headersRead = true;
            }
        } else {
//            echo "Headers have been read...\r\n";
            $this->handleBodyLine($line);
        }
    }
    
    private function handleHttpStatus($statusCode, $statusMessage) {
        if ($statusCode == 200) {
            $this->reconnectionDelay = 0;
        } else {
            echo "!TWITTER ERROR: HTTP response code was not 200: $statusCode - $statusMessage\r\n";
//            $GLOBALS['twitterConnection']->disconnect();
            $this->disconnect();
            $this->reconnectAfterHttpError();
        }
    }
    
    private function handleHttpHeader($name, $value) {
        
    }
    
    private function handleBodyLine($line) {
        if (!$line)
            return;
        
        if (substr($line, 0, 1) != '{')
            return;
        if ($json = json_decode($line)) {
            $this->handleJson($json);
        }
    }
    
    private function fixMessage($msg) {
        $msg = preg_replace("/\s*[\r\n]+\s*/", ' ', $msg);
        $msg = str_replace('…', '...', $msg);
        return $msg;
    }
    
    private function preSendMsg(&$msg) {
        $uc = self::$urlClassname;
        echo "URL Class name is: $uc\r\n";
        echo "Replacing URLs in this string: $msg\r\n";
        $msg = $uc::replaceUrlsWithDestinationsInString($msg);
        echo "String after replacing URLs: $msg\r\n";
        
        $msg = self::parseMessageForFormattingCodes($msg);
        $msg = $this->fixMessage($msg);
    }
    
    private function formatHashtag($tag) {
        $ret = "%C%CYAN_BOLD#{$tag->text}%R";
        return $ret;
    }
    
    private function formatUsername($user) {
        try {
            $user = "%B" . self::twitterUsernameToIrcNick($user->screen_name) . "%R";
        } catch (Exception $ex) {
            $user = "@%C%CYAN_BOLD{$user->screen_name}%R (%B{$user->name}%R)";
        }
        return $user;
    }
    
    private function replaceEntities($msg, $entities) {
        /*
        "entities":{
            "urls":[{"indices":[99,118],"display_url":"on.ft.com\/pmUAdv","expanded_url":"http:\/\/on.ft.com\/pmUAdv","url":"http:\/\/t.co\/aVY2Eiq"}],
                    {"url":"http:\/\/t.co\/F0UvdweD","indices":[102,122],"expanded_url":"http:\/\/reg.cx\/1RYp","display_url":"reg.cx\/1RYp"}
            "hashtags":[],
            "user_mentions":[{"indices":[19,23],"screen_name":"tim","name":"Tim Bradshaw","id":76133,"id_str":"76133"}]
        },
        */
        if (!$entities)
            return $msg;
        
        foreach ($entities->user_mentions as $u) {
            $msg = preg_replace('/@' . preg_quote($u->screen_name, '/') . '\b/i', $this->formatUsername($u), $msg);
        }
        
        foreach ($entities->urls as $u) {
            if (!strlen($u->expanded_url)) continue;
            $msg = preg_replace('/' . preg_quote($u->url, '/') . '\b/i', $u->expanded_url, $msg);
        }
        
        foreach ($entities->media as $m) {
            $msg = preg_replace('/' . preg_quote($m->url, '/') . '\b/i', (!empty($m->media_url) ? $m->media_url : $m->expanded_url), $msg);
        }
        
        foreach ($entities->hashtags as $h) {
            $msg = preg_replace('/#' . preg_quote($h->text, '/') . '\b/i', $this->formatHashtag($h), $msg);
        }
        
        return $msg;
    }
    
    private function sendMsg($msg, $recipientTwitterUID=null) {
        $this->preSendMsg($msg);
        if ($recipientTwitterUID !== null) {
            foreach (self::$followList[$recipientTwitterUID]['nicks'] as $nick) {
                if ($this->ircClass->isOnline($nick, self::$channelName)) {
                    if (empty(self::$followList[$recipientTwitterUID]['notice'])) {
                        $this->ircClass->privMsg($nick, $msg);
                    } else {
                        $this->ircClass->notice($nick, $msg);
                    }
                }
            }
        } else {
            $this->ircClass->privMsg(self::$channelName, $msg);
        }
    }
    
    private function sendRetweetNotice($json) {
        $msg = "%BTwitter%R - you were %Bretweeted%B by {$this->formatUsername($json->user)}: " . html_entity_decode($json->retweeted_status->text);
        $msg = $this->replaceEntities($msg, $json->retweeted_status->entities);
        $this->sendMsg($msg, $json->retweeted_status->user->id_str);
    }
    
    private function sendMentionNotice($json, $uid) {
        $msg = "%BTwitter%R - you were %Bmentioned%B by {$this->formatUsername($json->user)}: " . html_entity_decode($json->text);
        $msg = $this->replaceEntities($msg, $json->entities);
        $this->sendMsg($msg, $uid);
    }
    
    private function sendChannelTweet($json) {
        $msg = '';
        if (!empty($json->retweeted_status)) {
            //Was a retweet by one of our followees (and wasn't originally posted by someone else we follow)
            if (self::$publicRetweetsEnabled && !array_key_exists($json->retweeted_status->user->id_str, self::$followList)) {
                $msg = "RT {$this->formatUsername($json->retweeted_status->user)}: " . html_entity_decode($json->retweeted_status->text);
                $msg = $this->replaceEntities($msg, $json->retweeted_status->entities);
            }
        } else {
            //Normal, non-retweet
            $msg = html_entity_decode($json->text);
            $msg = $this->replaceEntities($msg, $json->entities);
        }
        
        if ($msg) {
            $msg = "%BTwitter%R post by {$this->formatUsername($json->user)}: $msg";
            $this->sendMsg($msg);
        }
    }
    
    private function handleJson($json) {
        if (!empty($json->delete)) {
            //Can't handle delete requests, since we've already sent a message
            return;
        }
        
        //Retweet of something one of our users tweeted?
        if ($json->retweeted_status) {
            $this->sendRetweetNotice($json);
        }
        
        //How about a mention?
        if ($json->entities->user_mentions) {
            foreach ($json->entities->user_mentions as $u) {
                if (in_array($u->id_str, $this->followListIds)) {
                    //Someone mentioned a user we follow; notify that user
                    $this->sendMentionNotice($json, $u->id_str);
                }
            }
        }
        
        //By someone we follow?
        if (in_array($json->user->id_str, $this->followListIds)) {
            if ($json->in_reply_to_user_id_str && substr($json->text, 0, 1) == '@') {
                //Don't tweet replies
                return;
            }
            
            $this->sendChannelTweet($json);
        }
    }
    
    private static function parseMessageForFormattingCodes($message) {
        $uc = self::$urlClassname;
        $message = $uc::parseMessageForFormattingCodes($message);
        return $message;
    }
    
    /**
     * @todo Twitter module
     * @link https://dev.twitter.com/pages/streaming_api
     * 
     */
    
    public function handlePrivMessage($line, $args) {
        if (strpos($line['to'], "#" ) === false)
            return;
        if (in_array(strtolower($line['fromNick']), array()))
            return;
        
    }
    
    public static function twitterUsernameToIrcNick($username, $dehighlight=true) {
        $u = null;
        if (is_numeric($username) && isset(self::$followList[$username])) {
            $u = self::$followList[$username];
        } else {
            foreach (self::$followList as $v) {
                if (strcasecmp($v['user'], $username) == 0) {
                    $u = $v;
                    break;
                }
            }
        }
        
        if ($u && $u['nicks']) {
            $nick = reset($u['nicks']);
            $nick = $nick[0] . '%B%B' . substr($nick, 1);
            $nick = self::parseMessageForFormattingCodes($nick);
            return $nick;
        }
        throw new Exception("Unrecognised Twitter username");
    }
    
    /**
     * @todo:
     * 
     * Properly handle URLs in tweets (trigger URL outputs for twitpic/yfrog [maybe already done?], but not if the message is the same in tweet vs caption)
     */
}

?> 
