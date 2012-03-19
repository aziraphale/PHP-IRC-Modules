<?php

require_once './modules/load-dynamic-class.php';

class dice extends module {

    public $title     = "dice";
    public $author     = "Andrew Gillard";
    public $version = "0.1";
    public $date     = "2011-05-09";
    
    private static $urlClassname;

    public function init() {
        self::$urlClassname = 'URL';
        loadDynamicClass(self::$urlClassname, 'urlreveal/url.class.php');
    }
    
    public function priv_dice($line, $args) {
        $channel = $line ['to'];
        $text = $line['text'];
        $fromnick = strtolower($line['fromNick']);
        
        if (strpos ( $channel, "#" ) === false)
            return;
        if (in_array($fromnick, array()))
            return;
        
        if (preg_match('/^!(\d*)d(\d+)/i', $text, $m)) {
            $dType = $tType = $m[2];
            $dCount = $tCount = $m[1];
            
            if (strlen($dCount) == 0)
                $dCount = 1;
            if ($dCount > 50) {
                $this->ircClass->notice($fromnick, "The !xDy function is limited to rolling %B50%B dice at one time.");
                return;
            }
            if ($dCount <= 0) {
                $this->ircClass->notice($fromnick, "You must roll at least one die...");
                return;
            }
            if ($dType < 2) {
                $this->ircClass->notice($fromnick, "You want to roll a one-sided die? How, pray tell, would that work?");
                return;
            }
            if ($dType > 1000000) {
                $this->ircClass->notice($fromnick, "The !xDy function is limited to rolling dice with a %Bmaximum of 1,000,000%B sides.");
                return;
            }
            
            if ($dCount == 1) {
                $out = mt_rand(1, $dType);
                if ($dType == 2) {
                    $outMsg = "Flipping a %Bcoin%R: " . ($out == 1 ? "heads (1)" : "tails (2)");
                } else {
                    $outMsg = "Rolling a %BD$tType%R: $out";
                }
            } else {
                $out = array();
                for ($i=0; $i<$dCount; ++$i) {
                    $out[] = mt_rand(1, $dType);
                }
                $sum = array_sum($out);
                $avg = round($sum / $dCount, 3);
                
                $typeAction = 'Rolling';
                $typeDesc = "D{$tType}s";
                if ($dType == 2) {
                    $typeAction = 'Flipping';
                    $typeDesc = "coins";
                }
                
                $outMsg = "$typeAction %B$tCount $typeDesc%R: " . join(', ', $out) . " [Total: %B$sum%R - Mean: %B$avg%R]";
            }
            
            foreach (explode("\n", wordwrap($outMsg, 400, "\n", false)) as $msgLine) {
                $msgLine = self::parseMessageForFormattingCodes($msgLine);
                $this->ircClass->privMsg($channel, $msgLine);
            }
        }
    }
    
    private static function parseMessageForFormattingCodes($message) {
        $uc = self::$urlClassname;
        $message = $uc::parseMessageForFormattingCodes($message);
        return $message;
    }
}

?> 
