<?php

require_once './modules/load-dynamic-class.php';

class urlreveal extends module {

	public $title 	= "urlreveal";
    
    // The author of the original urlreveal module that this was based on was "g2x3k"
	public $author 	= "Andrew Gillard";
	public $version = "0.9";
	public $date 	= "2012-03-19 11:54";
	private $delay 	= 0;
    
	public function init() {}
    
	public function priv_urlreveal($line, $args) {
		if (strpos($line['to'], "#" ) === false)
		    return;
		if (in_array(strtolower($line['fromNick']), array()))
		    return;
        
        $currentNick = $this->ircClass->getNick();
        if (
            stripos($line['text'], "[!$currentNick]") !== false ||
            stripos($line['text'], "(!$currentNick)") !== false ||
            stripos($line['text'], "<!$currentNick>") !== false ||
            stripos($line['text'], "{!$currentNick}") !== false
        ) {
            // We've been told to ignore this line
            return;
        }
        
        try {
            $urlClassName = 'URL';
            loadDynamicClass($urlClassName, 'urlreveal/url.class.php');
            $urls = $urlClassName::createAllFromLine($this->ircClass, $line, true);
            
            echo "Found " . count($urls) . " URLs.\r\n";
            
            foreach ($urls as /** @var URL_dummy */ $u) {
                $u->sendMessage();
            }
        } catch (Exception $e) {
            echo "Failed to load URL class: {$e->getMessage()}\r\n";
        }
	}
}

?> 
