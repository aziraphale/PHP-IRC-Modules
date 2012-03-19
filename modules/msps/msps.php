<?php

class msps extends module {
    public $title = "MSPs Converter";
    public $author = "Andrew Gillard";
    public $version = "0.1";
    
    private static $rateGBP = 0.0085;
    private static $rateUSD = 0.0125;
    private static $rateEUR = 0.0120;

    public function init() {}

    public function destroy() {}
    
    private function round($value, $dec=2) {
        return number_format(round($value, $dec), $dec, '.', ',');
    }
    
    public function msps($line, $args) {
        if ($args['nargs'] <= 0) {
            $this->ircClass->notice($line['fromNick'], "Usage: !msps <MSP count>");
            return;
        }

        $msps = $args['arg1'];
        
        $gbp = $this->round($msps * self::$rateGBP);
        $usd = $this->round($msps * self::$rateUSD);
        $eur = $this->round($msps * self::$rateEUR);
        
        $this->ircClass->privMsg($line['to'], "$msps MSPs equates to: £$gbp, \$$usd or €$eur");
    }
}

?>
