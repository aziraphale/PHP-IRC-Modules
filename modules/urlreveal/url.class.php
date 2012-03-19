<?php

/**
 * Supported formatting codes:
     * C
     * B
     * U
     * I
     * R or O
 * 
 * Supported colours:
     * WHITE (WHITE_BOLD)
     * BLACK (BLACK_BOLD)
     * BLUE (BLUE_BOLD)
     * GREEN (GREEN_BOLD)
     * RED (RED_BOLD)
     * MAGENTA
     * YELLOW (YELLOW_BOLD)
     * CYAN (CYAN_BOLD)
     * PINK
 * 
 */
class URL_dummy extends URL {
    const IMAGES_ENABLED = false;
    const NORMAL_URLS_ENABLED = false;
    const RECURSION_DEPTH_LIMIT = 5;
    const RECURSION_COUNT_LIMIT = 8;
    const FA_IB_KEYWORD_LIMIT = 10;
    const URL_FETCH_LIMIT = 8;
    const REDIR_URL_DISPLAY_LENGTH_LIMIT = 50;
    
    /**
     * REQUIRED
     */
    const DEST_YOUTUBE = 1;
    const DEST_TWITPIC = 2;
    const DEST_IMGUR = 3;
    const DEST_YFROG = 4;
    const DEST_BBCNEWS = 5;
    const DEST_TWITTER = 6;
    const DEST_FURAFFINITY = 7;
    const DEST_INKBUNNY = 8;
    const DEST_BBCPROGRAMME = 9;
    const DEST_FLICKR = 10;
    const DEST_E621 = 12;
    const DEST_SOFURRY = 13;
    const DEST_WILDCRITTERS = 14;
    const DEST_EBUYER = 15;
    const DEST_ANDROID_MARKET = 16;
    
    private static $cookies = array(
        self::DEST_FURAFFINITY => 'b=aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa; a=aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa',
        self::DEST_INKBUNNY => 'PHPSESSID=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
    );
    
    /**
     * REQUIRED
     */
    private static $urlMatchDestinationPatterns = array(
        self::DEST_YOUTUBE => '#^https?://(www\.)?(youtube\.com|youtu\.be)\b#i',
        self::DEST_TWITPIC => '#^https?://(www\.)?(twitpic\.com)\b#i',
        self::DEST_IMGUR => '#^https?://(www\.|i\.)?(imgur\.com)\b#i',
        self::DEST_YFROG => '#^https?://(www\.)?(yfrog\.com)\b#i',
        self::DEST_BBCNEWS => '#^https?://(www\.)?(bbc\.co\.uk/news(beat)?/)\b#i',
        self::DEST_TWITTER => '#^https?://(www\.)?(twitter\.com/(\#!/)?[^/]+/status/\d+)\b#i',
        self::DEST_FURAFFINITY => '#^https?://(www\.)?(furaffinity\.net/view/\d+)\b#i',
        self::DEST_INKBUNNY => '#^https?://(www\.)?(inkbunny\.net/submissionview\.php\?id=\d+)\b#i',
        self::DEST_BBCPROGRAMME => '#^https?://(www\.)?(bbc\.co\.uk/(programmes|iplayer/episode)/[0-9a-z]+)\b#i',
        self::DEST_FLICKR => '#^https?://(www\.)?flickr\.com/photos/[^/]+/(sets/)?\d+\b#i',
        self::DEST_E621 => '#^https?://(www\.)?e621\.net/post/show/\d+\b#i',
        self::DEST_SOFURRY => '#^https?://(www\.)?sofurry\.com/page/\d+\b#i',
        self::DEST_WILDCRITTERS => '#^https?://(www\.)?wildcritters\.ws/post/show/\d+\b#i',
        self::DEST_EBUYER => '#^https?://(www\.)?ebuyer\.com/product/\d+\b#i',
        self::DEST_ANDROID_MARKET => '#^https?://play\.google\.com/store/apps/details\?#i',
    );
    
    /**
     * REQUIRED
     */
    private static $destinationMethods = array(
        self::DEST_YOUTUBE => 'outputYoutube',
        self::DEST_TWITPIC => 'outputTwitpic',
        self::DEST_IMGUR => 'outputImgur',
        self::DEST_YFROG => 'outputYfrog',
        self::DEST_BBCNEWS => 'outputBbcNews',
        self::DEST_TWITTER => 'outputTwitter',
        self::DEST_FURAFFINITY => 'outputFurAffinity',
        self::DEST_INKBUNNY => 'outputInkbunny',
        self::DEST_BBCPROGRAMME => 'outputBbcProgramme',
        self::DEST_FLICKR => 'outputFlickr',
        self::DEST_E621 => 'outputE621',
        self::DEST_SOFURRY => 'outputSofurry',
        self::DEST_WILDCRITTERS => 'outputWildCritters',
        self::DEST_EBUYER => 'outputEbuyer',
        self::DEST_ANDROID_MARKET => 'outputAndroidMarket',
    );
    
    /**
     * Can contain a mix of destination constants or regular expressions to match against the URL
     */
    private static $destinationsToSkipPageFetchOf = array(
        self::DEST_TWITTER,
        '#^https?://(i\.)?(imgur\.com)\b#i',
    );
    
    private static $exceptionalMimeTypes = array(
        'application/pdf' => 'PDF document',
        'application/msword' => 'MS Word document',
        'application/postscript' => 'PostScript document',
        'application/rtf' => 'RTF document',
        'application/vnd.ms-excel' => 'MS Excel workbook',
        'application/vnd.ms-powerpoint' => 'MS PowerPoint show',
        'application/vnd.ms-works' => 'MS Works document',
        'application/x-mspublisher' => 'MS Publisher document',
        'application/x-shockwave-flash' => 'Shockwave Flash video',
        'application/x-tar' => 'Tar archive',
        'application/zip' => 'Zip archive',
        'audio/mpeg' => 'MPEG audio',
        'audio/x-wav' => 'WAV audio',
        'image/bmp' => 'Windows Bitmap image',
        'image/tiff' => 'TIFF image',
        'text/richtext' => 'RTF document',
        'video/mpeg' => 'MPEG video',
        'video/quicktime' => 'QuickTime video',
        'video/x-la-asf' => 'ASF video',
        'video/x-ms-asf' => 'ASF video',
        'video/x-msvideo' => 'MS video',
        'application/octet-stream' => 'Binary stream',
    );
    
    const URL_REGEX = '#((?:https?://|www\.(?!\.)(?=[^.]+\.[^.]+))(?P<main>(?:[-a-z0-9@%_+.~\#!?&/=]|[,:;](?!\s)|\((?P>main)\))+))#i';
    
    private static $urlRedirectIgnoreList = array(
        '#^https?://(www\.)?facebook\.com\b#i',
        '#^https?://(www\.)?google\.(com|co\.\w+)/search\b#i',
        '#(\blogin\b|[a-z]L(?i)ogin)#i',
        '#\bover18\b#i',
        '#\bverify_age\b#i',
    );
    
    private function outputImgur() {
        $imgId = '';
        $isAlbum = false;
        $title = $this->pageTitle;

        if (strlen($this->pageContent) < 10 && preg_match('#https?://(?:www\.)?(?:imgur\.com/)(a/)?(?:gallery/)?(?P<id>[a-z0-9]+)#i', $this->url, $m2)) {
            $imgId = $m2['id'];
            echo "Imgur page content is (almost?) empty; attempting a re-download (found image ID '$imgId')...\r\n";
            $pageBody = file_get_contents("http://imgur.com/gallery/$imgId");
            if ($pageBody) {
                $this->pageContent = $pageBody;
                $title = $this->extractString("<title>", "<\/title>", $pageBody);
            }
        }
        
        file_put_contents("/dev/shm/imgur.html", $this->pageContent);
        if (preg_match('#^https?://i\.imgur\.com/(?P<id>[a-z0-9]+)\.(?P<ext>jpg|png|gif)$#i', $this->url, $m)) {
            $imgId = $m['id'];
            $pageBody = file_get_contents("http://imgur.com/gallery/$imgId");
            if ($pageBody) {
                $title = $this->extractString("<title>", "<\/title>", $pageBody);
            }
        }
        if (!$imgId) {
            if (preg_match('#https?://(?:www\.)?(?:imgur\.com/)(?P<isalbum>a/)?(?:gallery/)?(?P<id>[a-z0-9]+)#i', $this->url, $m2)) {
                $imgId = $m2['id'];
                if (!empty($m2['isalbum'])) {
                    $isAlbum = true;
                }
            }
        }
        if (!$imgId) {
            return;
        }
        
        if (preg_match('/^(.+?) - Imgur$/is', $title, $m1)) {
            $type = $isAlbum ? 'album' : 'image';
            $imgTitle = html_entity_decode($m1[1], ENT_QUOTES);
            $this->outputMsg = "%C%GREENi%R%C%WHITE_BOLDmgur%R $type %B$imgId%R: {$imgTitle}";
        } else {
            echo "! Failed to match imgur on '{$this->pageTitle}' ($title) and '{$this->url}'.\r\n";
        }
    }
    
    private function outputTwitpic() {
        if (preg_match('#https?://(?:www\.)?(?:twitpic\.com/)(?!photos/)([a-zA-Z0-9]+)#', $this->url, $m2)) {
            $caption = $this->extractString('<div id="view-photo-caption">', "<\/div>", $this->pageContent);
            $caption = strip_tags($caption);
            $caption = html_entity_decode($caption, ENT_QUOTES);
            $user = '';
            if (preg_match('#<h1><a id="photo_username" class="nav-link" href="/photos/.+?">@(.+?)</a></h1>#i', $this->pageContent, $m3)) {
                $user = " by @%C%CYAN_BOLD{$m3[1]}%R";
            }
            $this->outputMsg = "%C%WHITE_BOLD,%BLACKTwit%R%C%CYAN_BOLD,%BLACKpic%R image %B{$m2[1]}%R$user%R: $caption";
        } else {
            echo "! Failed to match Twitpic on '{$this->pageTitle}' and '{$this->url}'.\r\n";
        }
    }
    
    private function outputYfrog() {
        if (preg_match('#https?://(?:www\.)?(?:yfrog\.com/)(?:z/)?(.+)#', $this->url, $m2)) {
            $user = '';
            $caption = '';
            echo "Page title is: {$this->pageTitle}\r\n";
            if (preg_match('/ Shared by (.+?)\s*$/is', $this->pageTitle, $m1)) {
                $user = " by @%C%CYAN_BOLD{$m1[1]}%R";
            } else {
                echo "Unable to find user in title: {$this->pageTitle}\r\n";
            }
            $jsonUrl = "http://yfrog.com/media/comments.json?limit=1&hash={$m2[1]}&order=asc&_=" . time();
            if ($jsonStr = file_get_contents($jsonUrl)) {
                if ($json = json_decode(trim($jsonStr))) {
                    $caption = htmlspecialchars_decode($json->result->caption->message_cleaned);
                } else {
                    echo "Unable to decode JSON string:\r\n$jsonStr\r\n";
                }
            } else {
                echo "Unable to fetch JSON URL: $jsonUrl\r\n";
            }
            
            if (strlen(trim($caption)) <= 0) {
                $caption = '[Image has no title]';
            }
            
            $this->outputMsg = "%C%CYAN_BOLDy%C%GREEN_BOLDf%C%YELLOW_BOLDr%C%RED_BOLDo%C%MAGENTAg%R image %B{$m2[1]}%R$user%R: $caption";
        } else {
            echo "! Failed to match yfrog on '{$this->pageTitle}' and '{$this->url}'.\r\n";
        }
    }
    
    private function outputYoutube() {
        if (preg_match('/\<meta\s+name\="title"\s+content\="(.+?)"\s*\>/is', $this->pageContent, $m1)) {
            $id = false;
            $over18 = false;
            if (preg_match('#https?://(?:www\.)?(?:youtube\.com/watch\?(?:.+&)*v=|youtu\.be/)([a-zA-Z0-9_\-]+?)($|&)#', $this->url, $m2)) {
                $id = $m2[1];
            } elseif (preg_match('#https?://(?:www\.)?(?:youtube\.com/verify_age\?next_url=/watch%3Fv%3D)([a-zA-Z0-9_\-]+?)($|&)#', $this->url, $m2)) {
                // https://www.youtube.com/verify_age?next_url=/watch%3Fv%3DKlqxMNdST1g%26ob%3Dav2e
                $id = $m2[1];
                $over18 = true;
            }
            
            if (preg_match('#<div id="verify-details">#i', $this->pageContent)) {
                $over18 = true;
            }
            
            $title = html_entity_decode($m1[1], ENT_QUOTES);
            $unavailable = '';
            if (preg_match('#\<div\s+id="watch-player-unavailable-message"\s*\>#i', $this->pageContent)) {
                $unavailable = " %C%RED_BOLD[Unavailable in the UK]%R";
            }
            
            $idStr = '';
            if ($id) {
                $idStr = " %B$id%R";
            }
            
            $ageRestrictStr = '';
            if ($over18) {
                $ageRestrictStr = ' %C%RED_BOLD[18+]%R';
            }
            
            $this->outputMsg = "%C%WHITE_BOLD,%BLACKYou%R%C%WHITE_BOLD,%REDTube%R video$idStr: {$title}$unavailable$ageRestrictStr";
        } else {
            echo "! Failed to match YouTube on '{$this->url}' and page body.\r\n";
        }
    }
    
    private function outputBbcNews() {
        if (preg_match('/^(?:BBC News|BBC - Newsbeat) - (.+)$/is', $this->pageTitle, $m1) && preg_match('#https?://(?:www\.)?bbc\.co\.uk/news((?:beat)?)/(?:[a-z\-]+-)?(\d+)\b#i', $this->url, $m2)) {
            $title = html_entity_decode($m1[1], ENT_QUOTES);
            if (strcasecmp($m2[1], 'beat') == 0) {
                $this->outputMsg = "%C%WHITE_BOLD,%BLACKBBC%R %C%WHITE_BOLD,%CYANNewsbeat%R article %B{$m2[2]}%R: {$title}";
            } else {
                $this->outputMsg = "%C%WHITE_BOLD,%BLACKBBC%R %C%WHITE_BOLD,%REDNews%R article %B{$m2[2]}%R: {$title}";
            }
        } else {
            echo "! Failed to match BBC News on '{$this->pageTitle}' and '{$this->url}'.\r\n";
        }
    }
    
    private function outputTwitter() {
        if (preg_match('#https?://(?:www\.)?twitter\.com/(?:\#!/)?([^/]+)/status/(\d+)\b#i', $this->url, $m2)) {
            $apiUrl = "https://api.twitter.com/1/statuses/show/{$m2[2]}.json?trim_user=true";
            echo "Matched Twitter; attempting to communicate with API [$apiUrl]...\r\n";
            
            $apiResult = file_get_contents("http://api.twitter.com/1/statuses/show/{$m2[2]}.json?trim_user=false");
            if ($apiResult && ($json = json_decode($apiResult))) {
                $screenName = html_entity_decode($json->user->screen_name, ENT_QUOTES, 'utf-8');
                $name = html_entity_decode($json->user->name, ENT_QUOTES, 'utf-8');
                $text = preg_replace("/\s*[\r\n]+\s*/", ' ', html_entity_decode($json->text, ENT_QUOTES, 'utf-8'));
                $this->outputMsg = "%C%CYAN_BOLDtwitter%R post %B{$json->id_str}%R by @%C%CYAN_BOLD$screenName%R ($name): $text";
            }
        } else {
            echo "! Failed to match Twitter on '{$this->url}'.\r\n";
        }
    }
    
    private function outputFurAffinity() {
        if (preg_match('/^(.+) by ([a-z0-9_]+) -- Fur Affinity \[dot\] net$/is', $this->pageTitle, $m1) && preg_match('#https?://(?:www\.)?furaffinity\.net/view/(\d+)\b#i', $this->url, $m2)) {
            $keywords = '';
            if (preg_match_all('#<a\s+[^>]*href="/search/@keywords [^>]+?"[^>]*>([^>]+)</a>#is', $this->pageContent, $keywordsmatches, PREG_PATTERN_ORDER)) {
                $keywords = $this->cutDownKeywords($keywordsmatches[1]);
                $keywords = ' [' . join(', ', $keywords) . ']';
            }
            
            $imgurl = '';
            if (preg_match('#<a\s+[^>]*href="(https?://d\.facdn\.net/art/.+?/\d+\..+?\.jpg)"[^>]*>\s*Download\s*</a>#is', $this->pageContent, $imgurlmatch)) {
                $imgurl = " %C%WHITE,%BLACK[ Direct image URL: {$imgurlmatch[1]} ]%R";
            }
            
            $rating = '';
            if (preg_match("#<img [^>]*src=\"/img/labels/(general|mature|adult).gif\"\s*/?>#is", $this->pageContent, $ratingMatch)) {
                switch (strtolower($ratingMatch[1])) {
                    case 'general':
                        $rating = " %C%WHITE,%BLACK[ Rated: %C%BLACK,%WHITE_BOLDGeneral%R%C%WHITE,%BLACK ]%R";
                        break;
                    case 'mature':
                        $rating = " %C%WHITE,%BLACK[ Rated: %C%BLACK,%BLUEMature%R%C%WHITE,%BLACK ]%R";
                        break;
                    case 'adult':
                        $rating = " %C%WHITE,%BLACK[ Rated: %C%BLACK,%RED_BOLDAdult%R%C%WHITE,%BLACK ]%R";
                        break;
                }
            }
            
            $title = html_entity_decode($m1[1], ENT_QUOTES);
            
            $this->outputMsg = "%BFur Affinity%R post #%B{$m2[1]}%R by %B{$m1[2]}%R: {$title}{$rating}{$keywords}{$imgurl}";
        } else {
            echo "! Failed to match Fur Affinity on '{$this->pageTitle}' and '{$this->url}'.\r\n";
        }
    }
    
    private function outputInkbunny() {
        if (preg_match('/^(.+) by ([a-z0-9_]+) < Submission \| Inkbunny, the Furry Art Community$/is', $this->pageTitle, $m1) && preg_match('#https?://(?:www\.)?inkbunny\.net/submissionview\.php\?id=(\d+)\b#i', $this->url, $m2)) {
            $keywords = '';
            if (preg_match_all("#<a href='search_process\.php\?keyword_id=\d+'.*?>([^>]+)</a>#is", $this->pageContent, $keywordsmatches, PREG_PATTERN_ORDER)) {
                $keywords = $this->cutDownKeywords($keywordsmatches[1]);
                $keywords = ' [' . join(', ', $keywords) . ']';
            }
            
            $imgurl = '';
            if (preg_match("#<a href='(https?://(?:www\.)?inkbunny\.net/+files/full/\d+/.+?\.(jpg|png|gif))'.*?><img src='[^']+'.*?/></a>#is", $this->pageContent, $imgurlmatch)) {
                $imgurl = " %C%WHITE,%BLACK[ Direct image URL: {$imgurlmatch[1]} ]%R";
            } elseif (preg_match("#<img src='(https?://(?:www\.)?inkbunny\.net/+files/screen/\d+/.+?\.(jpg|png|gif))'.*?/>#is", $this->pageContent, $imgurlmatch)) {
                $imgurl = " %C%WHITE,%BLACK[ Direct image URL: {$imgurlmatch[1]} ]%R";
            }
            
            $rating = '';
            if (preg_match("#<div[^>]*>Rating:\s*</div>\s*<div[^>]*>\s*(General|Mature|Adult)\s*</div>#is", $this->pageContent, $ratingMatch)) {
                switch (strtolower($ratingMatch[1])) {
                    case 'general':
                        $rating = " %C%WHITE,%BLACK[ Rated: %C%WHITE_BOLD,%GREENGeneral%R%C%WHITE,%BLACK ]%R";
                        break;
                    case 'mature':
                        $rating = " %C%WHITE,%BLACK[ Rated: %C%WHITE_BOLD,%BLUE_BOLDMature%R%C%WHITE,%BLACK ]%R";
                        break;
                    case 'adult':
                        $rating = " %C%WHITE,%BLACK[ Rated: %C%WHITE_BOLD,%REDAdult%R%C%WHITE,%BLACK ]%R";
                        break;
                }
            }
            
            $title = html_entity_decode($m1[1], ENT_QUOTES);
            
            $this->outputMsg = "%BInkbunny%R post #%B{$m2[1]}%R by %B{$m1[2]}%R: {$title}{$rating}{$keywords}{$imgurl}";
        } else {
            echo "! Failed to match Inkbunny on '{$this->pageTitle}' and '{$this->url}'.\r\n";
        }
    }
    
    private function outputBbcProgramme() {
        $json = $this->extractString("episodeRegistry\.addData\(\r?\n", "\s+\);\r?\n", $this->pageContent);
        $json = substr($json, strpos($json, '{'), strrpos($json, '}')+1);
        $json = json_decode($json);
        
        if (!$json) {
            echo "Failed to decode BBC JSON.\r\n";
            return;
        }
        
        $json = reset($json);
        
        $id = $title = $channel = $synopsis = '';
        $time = 0;
        $tags = array();
        
        if (property_exists($json, 'id') && $json->id) {
            $id = $json->id;
            echo "Found ID $id\r\n";
        }
        if (property_exists($json, 'original_title') && $json->original_title) {
            $title = $json->original_title;
            echo "Found title $title\r\n";
        }
        if (property_exists($json, 'masterbrand_title') && $json->masterbrand_title) {
            $channel = $json->masterbrand_title;
            echo "Found channel $channel\r\n";
        }
        if (property_exists($json, 'tag_schemes') && $json->tag_schemes && is_array($json->tag_schemes) && isset($json->tag_schemes[0]) && $json->tag_schemes[0] && property_exists($json->tag_schemes[0], 'tags') && $json->tag_schemes[0]->tags) {
            foreach ($json->tag_schemes[0]->tags as $t) {
                $tags[] = $t->name;
            }
            echo "Found tags: " . join(', ', $tags) . "\r\n";
        }
        if (property_exists($json, 'synopsis') && $json->synopsis) {
            $synopsis = $json->synopsis;
            echo "Found synopsis: $synopsis\r\n";
        }
        
        if (property_exists($json, 'original_broadcast_datetime') && $json->original_broadcast_datetime) {
            $time = strtotime($json->original_broadcast_datetime);
            echo "Found original broadcast time: $time ({$json->original_broadcast_datetime})\r\n";
        } elseif (property_exists($json, 'next_broadcasts') && $json->next_broadcasts && is_array($json->next_broadcasts) && isset($json->next_broadcasts[0]) && property_exists($json->next_broadcasts[0], 'start_time_iso') && $json->next_broadcasts[0]->start_time_iso) {
            $time = strtotime($json->next_broadcasts[0]->start_time_iso);
            echo "Found next broadcast time: $time ({$json->next_broadcasts[0]->start_time_iso})\r\n";
        }
        
        if (!$title) {
            echo "No title found; giving up\r\n";
            return;
        }
        
        $this->outputMsg = "";
        $this->outputMsg .= "%C%PINK,%BLACKBBC Programme%R";
        $this->outputMsg .= " %B$id%R: %B$title%R";
        
        $dateString = date('%\BjS M%\R @ %\BH:i%\R', $time);
        $attributes = array();
        if ($channel && $time) {
            $attributes[] = "%B$channel%R, $dateString";
        } elseif ($channel) {
            $attributes[] = "%B$channel%R";
        } elseif ($time) {
            $attributes[] = "%B$dateString%R";
        }
        
        if ($attributes) {
            $this->outputMsg .= ' (' . join('; ', $attributes) . ')';
        }
        
        if ($synopsis) {
            $this->outputMsg .= ': ';
            if (strlen($synopsis) > 250) {
                $this->outputMsg .= substr($synopsis, 0, 250) . '...';
            } else {
                $this->outputMsg .= $synopsis;
            }
        }
        
        return;
        
        if (preg_match('/^(?:BBC - BBC Radio 4 Programmes - |BBC iPlayer - )(.+)$/is', $this->pageTitle, $m1) && preg_match('#^https?://(?:www\.)?bbc\.co\.uk/(?:programmes|iplayer/episode)/([0-9a-z]+)\b#i', $this->url, $m2)) {
            $progName = html_entity_decode($m1[1], ENT_QUOTES);
            $this->outputMsg = "%C%PINK,%BLACKBBC Programme%R %B{$m2[1]}%R: $progName";
        } else {
            echo "! Failed to match BBC programme on '{$this->pageTitle}' and '{$this->url}'.\r\n";
        }
    }
    
    private function outputFlickr() {
        if (($r1 = preg_match('/^\s*(?:(?P<name1>.+?)\s+\-\s+a\s+set\s+on\s+Flickr|(?P<name2>.+?)\s+\|\s+Flickr\s+\-\s+Photo\s+Sharing!)\s*$/is', $this->pageTitle, $m1)) && ($r2 = preg_match('#^https?://(www\.)?flickr\.com/photos/[^/]+/(?P<isset>sets/)?(?P<id>\d+)\b#i', $this->url, $m2))) {
            $name = (!empty($m1['name1']) ? $m1['name1'] : (!empty($m1['name2']) ? $m1['name2'] : 'Unknown Title'));
            $name = html_entity_decode($name, ENT_QUOTES);
            $setimg = !empty($m2['isset']) ? 'set' : 'image';
            $this->outputMsg = "%C%CYANflick%C%PINKr%R $setimg %B{$m2['id']}%R: $name";
        } else {
            var_dump($r1, $r2);
            echo "! Failed to match flickr image on '{$this->pageTitle}' ($r1) and '{$this->url}' ($r2).\r\n";
        }
    }
    
    private function outputE621() {
        if (preg_match('#https?://(?:www\.)?e621\.net/post/show/(\d+)\b#i', $this->url, $m2)) {
            if (preg_match_all("#<li class=\"tag-type-(?P<type>general|copyright|character|artist)\"><a .*?href=\"/(?:wiki|artist)/show\?(?:title|name)=.*?\">\?</a> <a href=\"/post/index\?tags=.*?\">(?P<name>.*?)</a> <span class=\"post-count\">(?P<count>\d+)</span></li>#i", $this->pageContent, $keywordsmatches, PREG_SET_ORDER)) {
                $keywords = array();
                foreach ($keywordsmatches as $v) {
                    $kw = '';
                    switch ($v['type']) {
                        case 'copyright':
                            $kw .= '%B%C%PINK';
                            break;
                        case 'character':
                            $kw .= '%B%C%GREEN';
                            break;
                        case 'artist':
                            $kw .= '%B%C%YELLOW';
                            break;
                        case 'general':
                        default:
                            $kw .= '';
                    }
                    $kw .= $v['name'] . '%R';
                    $keywords[] = $kw;
                }
                $keywords = $this->cutDownKeywords($keywords);
                $keywords = ' [' . join(', ', $keywords) . ']';
                $this->outputMsg = "%Be621%R post #%B{$m2[1]}%R:{$keywords}";
            }
        } else {
            echo "! Failed to match e621 on '{$this->url}'.\r\n";
        }
    }
    
    private function outputSofurry() {
        if (preg_match('/^\s*var __SUBMISSION_DATA = (.*)\s*$/im', $this->pageContent, $m1)) {
            if (preg_match('#https?://(?:www\.)?sofurry\.com/page/(\d+)\b#i', $this->url, $m2)) {
                $json = json_decode(rtrim($m1[1], ';'));
                if ($json) {
                    $keywords = $this->cutDownKeywords(explode(',', $json->keywords));
                    $keywords = ' [' . join(', ', $keywords) . ']';
                    
                    $author = $json->author;
                    $title = $json->name;
                    
                    $this->outputMsg = "%BSoFurry%R post #%B{$m2[1]}%R by %B$author%R: $title{$keywords}";
                } else {
                    echo "Failed to decode found sofurry JSON: {$m1[1]}\r\n";
                }
            } else {
                echo "! Failed to match SoFurry on '{$this->url}'.\r\n";
            }
        } else {
            echo "! Failed to find the SoFurry JSON data.\r\n{$this->pageContent}\r\n\r\n\r\n\r\n";
        }
    }
    
    private function outputWildCritters() {
        if (preg_match('#https?://(?:www\.)?wildcritters\.ws/post/show/(\d+)\b#i', $this->url, $m2)) {
            if (preg_match_all("#<li class=\"tag-type-(?P<type>general|fetish|species|artist)\"><a .*?href=\"/(?:wiki|artist)/show\?(?:title|name)=.*?\">\?</a> <a href=\"/post\?tags=.*?\">(?P<name>.*?)</a> <span class=\"post-count\">(?P<count>\d+)</span> </li>#i", $this->pageContent, $keywordsmatches, PREG_SET_ORDER)) {
                $keywords = array();
                foreach ($keywordsmatches as $v) {
                    $kw = '';
                    switch ($v['type']) {
                        case 'fetish':
                            $kw .= '%B%C%MAGENTA';
                            break;
                        case 'species':
                            $kw .= '%B%C%CYAN';
                            break;
                        case 'artist':
                            $kw .= '%B%C%RED_BOLD';
                            break;
                        case 'general':
                        default: 
                            $kw .= '%B%C%BLUE_BOLD';
                    }
                    $kw .= $v['name'] . '%R';
                    $keywords[] = $kw;
                }
                $keywords = $this->cutDownKeywords($keywords);
                $keywords = ' [' . join(', ', $keywords) . ']';
                $this->outputMsg = "%BWildCritters%R post #%B{$m2[1]}%R:{$keywords}";
            } else {
                echo "! Failed to find any WildCritters keywords...\r\n";
            }
        } else {
            echo "! Failed to match WildCritters on '{$this->url}'.\r\n";
        }
    }
    
    private function outputEbuyer() {
        if (preg_match('/\<h1\>(.+?)\<\/h1\>/is', $this->pageContent, $m1) && preg_match('/\<li [^>]*class\="(?:\w +)*inc(?: +\w)*"[^>]*\>\<span [^>]*class\="(?:\w +)*now(?: +\w)*"[^>]*\>\s*(?:&pound;|£)([0-9.]+)\s*\<\/span\>/is', $this->pageContent, $m3) && preg_match('#^https?://(?:www\.)?ebuyer\.com/product/(\d+)\b#i', $this->url, $m2)) {
            $productName = html_entity_decode($m1[1], ENT_QUOTES);
            $this->outputMsg = "%C%RED_BOLDE%R%C%WHITE_BOLDbuyer%R Product %B{$m2[1]}%R: $productName [£{$m3[1]}]";
        } else {
            echo "! Failed to match Ebuyer product on '{$this->url}' or the page body.\r\n";
        }
    }
    
    private function outputAndroidMarket() {
        if (($r1 = preg_match('/^\s*(.+) - Android Apps on Google Play\s*$/is', $this->pageTitle, $m1)) && ($r2 = preg_match('#^https?://play\.google\.com/store/apps/details\b.*[?&]id=([a-z0-9.]+)#is', $this->url, $m2))) {
            $appName = html_entity_decode($m1[1], ENT_QUOTES);
            $this->outputMsg = "%C%GREEN,%WHITEAndroid Market%R App %B{$m2[1]}%R: $appName";
        } else {
            echo "! Failed to match Android Market app on '{$this->pageTitle}' [$r1] and '{$this->url}' [$r2].\r\n";
        }
    }
    
    private $irc;
    private $message;
    private $fromNick;
    private $channel;
    private $line;
    private $url;
    private $prettyUrl;
    private $urlDestination = 0;
    private $skipPageFetch = false;
    private $isRedirect;
    private $redirectTarget;
    private $pageTitle;
    private $pageMimeType;
    private $pageContent;
    private $pageDownloadSpeed;
    private $pageSize;
    private $pageRedirectionTime;
    private $pageDnsLookupTime;
    private $pageConnectionTime;
    private $httpResponseCode;
    private $pageConnectionError = false;
    private $outputMsg;
    private $childUrls = array();
    
    private static $recursive = false;
    private static $urlFetchLimitCounter = 0;
    private static $recursionDepthLimitCounter = 1;
    private static $recursionCountLimitCounter = 0;
    private static $recursionRoot;
    private static $allFoundUrls = array();
    private static $failingUrls = array();
    
    private function __construct($irc, $line, $url, $recursive) {
        $this->irc = $irc;
        $this->channel = isset($line['to']) ? $line['to'] : '';
        $this->message = isset($line['text']) ? $line['text'] : '';
        $this->fromNick = isset($line['fromNick']) ? $line['fromNick'] : '';
        $this->line = $line;
        $this->recursive = $recursive;
        
        if (strcasecmp(substr($url, 0, 4), 'http') != 0) {
            $url = "http://$url";
        }
        
        $url = $this->removeUtmTagsEtc($url);
        $this->url = $url;
        
        if (!self::isUrlInFailedCacheList($url)) {
            $this->urlDestination = $this->determineLocation();
            if (!$this->skipPageFetch && (!$this->urlDestination || !in_array($this->urlDestination, self::$destinationsToSkipPageFetchOf))) {
                $this->fetchUrl();
            }
            $this->createOutput();
            
            $this->findChildUrls();
        }
    }
    
    private static function isUrlInFailedCacheList($url) {
        $timeout = time() - 120;
        foreach (self::$failingUrls as $u => $time) {
            if ($time < $timeout) {
                unset(self::$failingUrls[$u]);
                continue;
            }
            
            if (self::_urlMatches($u, $url)) {
                return true;
            }
        }
        return false;
    }
    
    private function removeUtmTagsEtc($url) {
        $url = preg_replace('#(?<=[?&])utm_(source|medium|campaign|term|content)=.*?(?=&|$)#i', '', $url);
        $url = rtrim($url, '?&');
        return $url;
    }
    
    private function prettifyUrl() {
        $this->prettyUrl = urldecode($this->url);
    }
    
    public function getPrettyUrl($truncate=30) {
        $this->prettifyUrl();
        
        if (strlen($this->url) > 30)
            return substr($this->url, 0, 27) . '...';
        return $this->url;
    }
    
    private function determineLocation($url=null) {
        if (!isset($url))
            $url = $this->url;
        
        echo "Trying to match URL '$url'\r\n";
        foreach (self::$urlMatchDestinationPatterns as $d => $p) {
            if (preg_match($p, $url)) {
                foreach (self::$destinationsToSkipPageFetchOf as $spp) {
                    if (!is_numeric($spp)) {
                        if (preg_match($spp, $url)) {
                            $this->skipPageFetch = true;
                            break;
                        }
                    }
                }
                return $d;
            }
        }
        echo "('$url' does not appear to be something we're interested in...)\r\n";
        return 0;
    }
    
    private function fetchUrl() {
        if (self::$urlFetchLimitCounter >= self::URL_FETCH_LIMIT) {
            return;
        }
        self::$urlFetchLimitCounter++;
        
        $this->getUrlContents();
    }
    
    public function getUrl() {
        return $this->url;
    }
    
    private static function getBaseUrlForComparison($url) {
        if (strcasecmp(substr($url, 0, 8), 'https://') == 0) {
            $url = substr($url, 8);
        } elseif (strcasecmp(substr($url, 0, 7), 'http://') == 0) {
            $url = substr($url, 7);
        }
        $url = rtrim($url, '/');
        return $url;
    }
    
    public static function _urlMatches($a, $b) {
        if ($a instanceof self)
            $a = $a->getUrl();
        if ($b instanceof self)
            $b = $b->getUrl();
        
        $a = rtrim($a, '/');
        $b = rtrim($b, '/');
        
        echo "Comparing `$a` against `$b`...\r\n";
        
        return (strcasecmp($a, $b) == 0 ||
                strcasecmp(urldecode($a), $b) == 0 ||
                strcasecmp($a, urldecode($b)) == 0
            );
    }
    
    public function urlMatches($comparison) {
        if (is_array($comparison)) {
            foreach ($comparison as $c) {
                if ($this->urlMatches($c))
                    return true;
            }
        }
        
        return self::_urlMatches($this, $comparison);
    }
    
    public static function urlMatchesStringVsObjectArray($needle, array $haystack) {
        echo "Searching for URL `$needle`...\r\n";
        foreach ($haystack as /** @var URL_dummy */ $u) {
            if ($u->urlMatches($needle))
                return true;
        }
        return false;
    }
    
    public function isRedirect() {
        if (!isset($this->isRedirect)) {
            $this->isRedirect = $this->redirectTarget && !$this->urlMatches($this->redirectTarget);
            
            if ($this->isRedirect) {
                foreach (self::$urlRedirectIgnoreList as $regex) {
                    if (preg_match($regex, $this->redirectTarget)) {
                        $this->isRedirect = false;;
                    }
                }
            }
        }
        return $this->isRedirect;
    }
    
    public function getRedirectTarget() {
        return $this->redirectTarget;
    }
    
    public function getDestinationSite() {
        return $this->urlDestination;
    }
    
    public function getRedirectLocationDestinationSite() {
        if (!$this->isRedirect())
            return false;
        if ($this->urlDestination)
            return false;
        
        return $this->determineLocation($this->redirectTarget);
    }
    
    public function getRedirectLocationDestinationSiteObject() {
        if ($site = $this->getRedirectLocationDestinationSite()) {
            return new self($this->irc, $this->line, $this->redirectTarget);
        }
    }
    
    public function getUrlsFromOutput() {
        return self::findUrls($this->outputMsg);
    }
    
    public function isImage() {
        return (bool) preg_match("/image/i", $this->pageMimeType);
    }
    
    public static function parseMessageForFormattingCodes($message) {
        $message = preg_replace_callback('/(?<!%)%((?:WHITE|BLACK|BLUE|GREEN|RED|MAGENTA|YELLOW|CYAN|PINK|C|B|U|I|R|O)(?:_BOLD)?)/', function($m) {
            switch ($m[1]) {
                case 'C':           return "\03";
                case 'B':           return "\02";
                case 'U':           return "\037";
                case 'I':           return "\026";
                case 'R':
                case 'O':           return "\017";
                
                case 'WHITE':       return 15;
                case 'WHITE_BOLD':  return 0;
                case 'BLACK':       return 1;
                case 'BLACK_BOLD':  return 14;
                case 'BLUE_BOLD':   return 2;
                case 'BLUE':        return 12;
                case 'GREEN':       return 3;
                case 'GREEN_BOLD':  return 9;
                case 'RED':         return 5;
                case 'RED_BOLD':    return 4;
                case 'MAGENTA':     return 6;
                case 'YELLOW':      return 7;
                case 'YELLOW_BOLD': return 8;
                case 'CYAN':        return 10;
                case 'CYAN_BOLD':   return 11;
                case 'PINK':        return 13;
            }
            return '';
        }, $message);
        return $message;
    }
    
    public function sendMessage($recursive=true) {
        if (!$this->outputMsg)
            return;
        
        $childUrls = $this->childUrls;
        
        if ($this->isRedirect()) {
            //Special handling
            $childUrls = $this->outputTidyRedirectUrl();
        } else {
            $this->outputMsg = self::replaceUrlsWithDestinationsInString($this->outputMsg);
        }
        
        $message = self::parseMessageForFormattingCodes($this->outputMsg);
        $this->irc->privMsg($this->channel, $message);
        
        foreach ($childUrls as $u) {
            $u->sendMessage($recursive);
        }
    }
    
    private function getEventualRedirectTargetUrl() {
        if ($this->isRedirect() && $this->childUrls) {
            return $this->childUrls[0]->getEventualRedirectTargetUrl();
        }
        return $this;
    }
    
    private function createOutput() {
        if ($this->urlDestination && isset(self::$destinationMethods[$this->urlDestination])) {
            call_user_method(self::$destinationMethods[$this->urlDestination], $this);
        }
        if (!$this->outputMsg && $this->isRedirect()) {
            $this->outputRedirectUrl();
        }
        if (!$this->outputMsg && $this->isImage()) {
            if (self::IMAGES_ENABLED) {
                $this->outputMsg = $this->outputImage();
            }
        }
        if (!$this->outputMsg && $this->isExceptional()) {
            $this->outputExceptionalUrl();
        }
        if ($this->isSpecificRequest()) {
            $this->outputSpecificRequest();
        }
        if (!$this->outputMsg) {
            if (self::NORMAL_URLS_ENABLED) {
                $this->outputNormalUrl();
            }
        }
    }
    
    public function isExceptional() {
        if ($this->isExceptionalImageFile())
            return true;
        if ($this->isExceptionalNormalFile())
            return true;
        if ($this->isExceptionalMimeType())
            return true;
        if ($this->httpResponseCode >= 400 || $this->pageConnectionError) {
            self::$failingUrls[$this->getUrl()] = time();
            echo "Added this failing URL to the temporary blacklist. List now consists of:\r\n";
            foreach (self::$failingUrls as $k => $v) {
                echo "    $k\r\n";
            }
            return true;
        }
        
        return false;
    }
    
    private function isExceptionalMimeType() {
        return isset(self::$exceptionalMimeTypes[strtolower($this->pageMimeType)]);
    }
    
    private function isExceptionalImageFile() {
        return (substr($this->pageMimeType, 0, 6) == 'image/' && $this->pageSize > 2097152 /** 2 MB */);
    }
    
    private function isExceptionalNormalFile() {
        return (substr($this->pageMimeType, 0, 6) != 'image/' && $this->pageSize > 524288 /** 512 kB */);
    }
    
    public function isSpecificRequest() {
        $nick = $this->irc->getNick();
        if (preg_match('/[\[\(\{\<]\s*' . $nick . '\s*[\]\)\}\>]/i', $this->message))
            return true;
        return false;
    }
    
    private function outputExceptionalUrl() {
        if ($this->httpResponseCode >= 400) {
            $msg = '[No Description]';
            $type = '';
            if ($this->httpResponseCode >= 400 && $this->httpResponseCode < 500) {
                //Client error
                $type = " indicating a client error";
                switch ($this->httpResponseCode) {
                    case 400: $msg = "Bad Request"; break;
                    case 401: $msg = "Unauthorized"; break;
                    case 402: $msg = "Payment Required"; break;
                    case 403: $msg = "Forbidden"; break;
                    case 404: $msg = "Not Found"; break;
                    case 405: $msg = "Method Not Allowed"; break;
                    case 406: $msg = "Not Acceptable"; break;
                    case 407: $msg = "Proxy Authentication Required"; break;
                    case 408: $msg = "Request Timeout"; break;
                    case 409: $msg = "Conflict"; break;
                    case 410: $msg = "Gone"; break;
                    case 411: $msg = "Length Required"; break;
                    case 412: $msg = "Precondition Failed"; break;
                    case 413: $msg = "Request Entity Too Large"; break;
                    case 414: $msg = "Request-URI Too Long"; break;
                    case 415: $msg = "Unsupported Media Type"; break;
                    case 416: $msg = "Requested Range Not Satisfiable"; break;
                    case 417: $msg = "Expectation Failed"; break;
                    case 418: $msg = "I'm a teapot"; break; // Yep, we support HTCPCP (aka RFC 2324) :)
                    case 422: $msg = "Unprocessable Entity (WebDAV) (RFC 4918)"; break;
                    case 423: $msg = "Locked (WebDAV) (RFC 4918)"; break;
                    case 424: $msg = "Failed Dependency (WebDAV) (RFC 4918)"; break;
                    case 425: $msg = "Unordered Collection (RFC 3648)"; break;
                    case 426: $msg = "Upgrade Required (RFC 2817)"; break;
                    case 444: $msg = "No Response"; break;
                    case 449: $msg = "Retry With"; break;
                    case 450: $msg = "Blocked by Windows Parental Controls"; break;
                    case 499: $msg = "Client Closed Request"; break;
                }
            } elseif ($this->httpResponseCode < 600) {
                //Server error
                $type = " indicating a server error";
                switch ($this->httpResponseCode) {
                    case 500: $msg = "Internal Server Error"; break;
                    case 501: $msg = "Not Implemented"; break;
                    case 502: $msg = "Bad Gateway"; break;
                    case 503: $msg = "Service Unavailable"; break;
                    case 504: $msg = "Gateway Timeout"; break;
                    case 505: $msg = "HTTP Version Not Supported"; break;
                    case 506: $msg = "Variant Also Negotiates (RFC 2295)"; break;
                    case 507: $msg = "Insufficient Storage (WebDAV)(RFC 4918)"; break;
                    case 509: $msg = "Bandwidth Limit Exceeded (Apache bw/limited extension)"; break;
                    case 510: $msg = "Not Extended (RFC 2774)"; break;
                }
            } else {
                //600+ HTTP response code? wtf?
                $msg = "Wha? What kind of bizarre Web server is this?!";
            }
            
            $this->outputMsg = "%C%RED_BOLDNote:%R That URL is responding with an unexpected HTTP status code: %B{$this->httpResponseCode}%R - $msg";
        } elseif ($this->pageConnectionError && !preg_match('/^Operation timed out after \d+ milliseconds with \d+ (?:out of \d+ )?bytes received$/i', $this->pageConnectionError)) {
            $this->outputMsg = "%C%RED_BOLDNote:%R Connections to that URL are failing with this error: {$this->pageConnectionError}";
        } else {
            $filesize = $this->formatFilesize($this->pageSize, 2, false);
            if ($this->isExceptionalMimeType()) {
                $type = 'file';
                if (isset(self::$exceptionalMimeTypes[strtolower($this->pageMimeType)])) {
                    $type = self::$exceptionalMimeTypes[strtolower($this->pageMimeType)];
                }
                
                $this->outputMsg = "%C%RED_BOLDWARNING:%R That URL points to a $filesize $type!";
            } elseif ($this->isExceptionalImageFile()) {
                $this->outputMsg = "%C%RED_BOLDWARNING:%R That URL points to a $filesize image!";
            } elseif ($this->isExceptionalNormalFile()) {
                $this->outputMsg = "%C%RED_BOLDWARNING:%R That URL points to a $filesize file!";
            }
        }
    }
    
    private function outputSpecificRequest() {
        if ($this->pageTitle) {
            $this->outputMsg = "%BURL title%R: {$this->pageTitle}";
        }
    }
    
    private function outputImage() {
        $f = tempnam('/tmp', 'phpirc-img');
        file_put_contents($f, $this->pageContent);
        list($width, $height, $type, $attr) = getimagesize($myFile);

        $this->outputMsg = "%C%YELLOWImage URL%C ({$this->prettyUrl}) - %C%YELLOWDimensions:%C {$height}x{$width} - %C%YELLOWSize:%C ".self::formatFilesize(filesize($f))." - %C%YELLOWType:%C {$res['type']}";
        
        unlink($f);
    }
    
    private function outputNormalUrl() {
        $this->outputMsg = "%C%YELLOWURL%C {$this->url} - %C%YELLOWTitle%C: {$this->pageTitle}";
    }
    
    private function outputRedirectUrl() {
        $title = "";
        echo "!! Title of redirect target page is: {$this->pageTitle}\r\n";
        if ($this->pageTitle && !preg_match('/^%C%YELLOWURL%C/', $this->pageTitle)) /** @todo The preg_match is a horrible hack, but I've given up... */
            $title = ": {$this->pageTitle}";
        
        $redirTarget = $this->redirectTarget;
        if (strlen($redirTarget) > self::REDIR_URL_DISPLAY_LENGTH_LIMIT) {
            $redirTarget = substr($redirTarget, 0, self::REDIR_URL_DISPLAY_LENGTH_LIMIT) . '...';
        }
        
        $this->outputMsg = "%C%YELLOWURL%C %B{$this->url}%R redirects to %B{$redirTarget}%R$title";
    }
    
    private function outputTidyRedirectUrl() {
        $target = $this->getEventualRedirectTargetUrl();
        
        $this->pageTitle = $target->outputMsg;
        
        if (!$this->pageTitle && $this->isSpecificRequest()) {
            $this->pageTitle = $target->pageTitle;
        }

        $this->outputRedirectUrl();
        
        return $target->childUrls;
    }
    
    private function cutDownKeywords(array $keywords, $limit=self::FA_IB_KEYWORD_LIMIT, $random=true) {
        if (count($keywords) <= $limit) {
            return $keywords;
        }
        
        if (!$random) {
            return array_slice($keywords, 0, $limit);
        }
        
        $out = array();
        foreach (array_rand($keywords, $limit) as $k) {
            $out[] = $keywords[$k];
        }
        return $out;
    }
    
    private function getUrlContents() {
        $crl = curl_init();
        $header[] = "Connection: keep-alive";
        $header[] = "Keep-Alive: 5";
        $header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $header[] = "Accept-Language: en-gb,en;q=0.5";
        $header[] = "Accept-Encoding: gzip,deflate";
        $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
        $header[] = "Pragma: "; // browsers keep this blank.

        if ($this->urlDestination && isset(self::$cookies[$this->urlDestination])) {
            curl_setopt($crl, CURLOPT_COOKIE, self::$cookies[$this->urlDestination]);
        }
        curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($crl, CURLOPT_TIMEOUT, 5);
        curl_setopt($crl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-GB; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3 PHP-IRC');
        curl_setopt($crl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($crl, CURLOPT_URL, $this->url);
        curl_setopt($crl, CURLOPT_VERBOSE, false);
        curl_setopt($crl, CURLOPT_HEADER, true);
        curl_setopt($crl, CURLOPT_ENCODING, "");
        curl_setopt($crl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($crl, CURLOPT_MAXREDIRS, 30);
        curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($crl, CURLOPT_AUTOREFERER, true);
        curl_setopt($crl, CURLOPT_RANGE, '0-204800');
        curl_setopt($crl, CURLOPT_COOKIESESSION, true);
        curl_setopt($crl, CURLOPT_COOKIEFILE, "");

        $response = curl_exec($crl);
        if ($response === false) {
            $this->pageConnectionError = curl_error($crl);
        } else {
            if (strpos($response, "\r\n\r\n") === false) {
                $this->pageContent = $response;
            } else {
                list($headerString, $this->pageContent) = explode("\r\n\r\n", $response, 2);
            }

            $headers = array();
            foreach (explode("\r\n", $headerString) as $v) {
                if (strpos($v, ': ') !== false) {
                    list($a, $b) = explode(': ', $v, 2);
                    $headers[$a] = $b;
                    
                    switch (strtolower($a)) {
                        case 'content-range':
                            if (preg_match('/bytes 0-\d+\/(\d+)/i', $b, $m)) {
                                $this->pageSize = $m[1];
                            }
                            break;
                    }
                } else {
                    $headers[] = $v;
                }
            }
        
            $this->pageTitle = $this->extractString("<title>", "<\/title>", $this->pageContent);
            $this->pageTitle = str_replace(array('&#x202a;', '&#x202c;', '&rlm;'), '', $this->pageTitle);
            $this->pageTitle = html_entity_decode($this->pageTitle, ENT_QUOTES, 'UTF-8');
            $this->pageTitle = trim($this->pageTitle);
            $this->pageTitle = preg_replace('/\s+/', ' ', $this->pageTitle);

            $this->redirectTarget = curl_getinfo($crl, CURLINFO_EFFECTIVE_URL);
            $this->pageDownloadSpeed = curl_getinfo($crl, CURLINFO_SPEED_DOWNLOAD);
            
            if (!$this->pageSize) {
                $this->pageSize = curl_getinfo($crl, CURLINFO_SIZE_DOWNLOAD);
            }
            
            $this->pageMimeType = curl_getinfo($crl, CURLINFO_CONTENT_TYPE);
            if (preg_match('#^(.+?/.+?); charset=.+#i', $this->pageMimeType, $m)) {
                $this->pageMimeType = $m[1];
            }

            $this->pageRedirectionTime = number_format(curl_getinfo($crl, CURLINFO_REDIRECT_TIME), 2);
            $this->pageDnsLookupTime = number_format(curl_getinfo($crl, CURLINFO_NAMELOOKUP_TIME), 2);
            $this->pageConnectionTime = number_format(curl_getinfo($crl, CURLINFO_CONNECT_TIME), 2);
            $this->httpResponseCode = curl_getinfo($crl, CURLINFO_HTTP_CODE);

            $this->redirectTarget = $this->removeUtmTagsEtc($this->redirectTarget);
            
//            if (strcasecmp($this->redirectTarget, $this->url) == 0) {
                if (preg_match('#<\s*meta\s+(?:http-equiv="refresh"\s+content="\d;URL=(?P<url1>[^"]+)"\s*|content="\d;URL=(?P<url2>[^"]+)"\s+http-equiv="refresh")\s*(?:/\s*)?>#i', $response, $m)) {
                    //Found a META redirect instead
                    $this->redirectTarget = $m['url1'] ? $m['url1'] : $m['url2'];
                    echo "!! META redirect: {$this->redirectTarget}\r\n";
                    
                    $metaRedirUrl = new self($this->irc, $this->line, $this->redirectTarget, true);
                    if ($metaRedirUrl->isRedirect()) {
                        $this->redirectTarget = $metaRedirUrl->getEventualRedirectTargetUrl();
                        
                        // Copy other details from the child
                        echo "!! Setting the title of this META redirect URL to: {$metaRedirUrl->pageTitle}\r\n";
                        $this->pageContent = $metaRedirUrl->pageContent;
                        $this->pageTitle = $metaRedirUrl->pageTitle;
                        $this->pageDownloadSpeed = $metaRedirUrl->pageDownloadSpeed;
                        $this->pageSize = $metaRedirUrl->pageSize;
                        $this->pageMimeType = $metaRedirUrl->pageMimeType;
                        $this->pageRedirectionTime = $metaRedirUrl->pageRedirectionTime;
                        $this->pageDnsLookupTime = $metaRedirUrl->pageDnsLookupTime;
                        $this->pageConnectionTime = $metaRedirUrl->pageConnectionTime;
                        $this->httpResponseCode = $metaRedirUrl->httpResponseCode;
                    }
                }
//            }
        }
        
        curl_close($crl);
        
        if ($this->urlDestination && !strlen($this->pageContent)) {
            //Failed to download somehow? imgur was having problems with this...
            echo "Page content is empty; fetching with file_get_contents()...\r\n";
            $this->pageContent = file_get_contents($this->url);
        }
    }
    
    private function extractString($start, $end, $text, $casesens=false, $trim=true){
        $flags = ($casesens ? 's' : 'is');
        if (preg_match("/$start(?P<matched>.*?)$end/$flags", $text, $m)) {
            return trim($m['matched']);
        }
        return '';
    }
    
    private static function formatFilesize($bytes, $decimalPlaces=2, $includeTrailingZeroes=false) {
        $suffixes = array(  'B',
                            'kiB',
                            'MiB',
                            'GiB',
                            'TiB',
                            'PiB',
                            'EiB',
                            'ZiB',
                            'YiB'
                            );
        $suffix = 0;
        while($bytes >= 1024) {
            $bytes /= 1024;
            $suffix++;
        }
        $r = number_format(round($bytes, $decimalPlaces), $decimalPlaces, '.', ',');
        if (!$includeTrailingZeroes) {
            if (strpos($r, '.') !== false) {
                //Trim off trailing zeroes, then the trailing decimal point, if necessary (check
                // for the existence of a decimal point first, or we can end up stripping trailing
                // zeroes BEFORE the decimal point, thus completely altering the displayed value!)
                $r = rtrim(rtrim($r, '0'), '.');
            }
        }
        return $r . ' ' . $suffixes[$suffix];
    }
    
    private static function _createAllFromLine($irc, array $line) {
        $out = array();
        $urlStrings = self::findUrls($line['text']);
        foreach ($urlStrings as $u) {
            if (self::urlMatchesStringVsObjectArray($u, self::$allFoundUrls)) {
                echo "'$u' is already in our found array.\r\n";
                continue;
            }
            if (self::$recursionCountLimitCounter >= self::RECURSION_COUNT_LIMIT) {
                echo "We have reached the count limit.\r\n";
                break;
            }
            self::$recursionCountLimitCounter++;
            
            $obj = new self($irc, $line, $u, true);
            $out[] = $obj;
            self::$allFoundUrls[] = $obj;
        }
        return $out;
    }
    
    /**
     * @return URL_dummy
     */
    public static function createAllFromLine($irc, array $line, $recursive=false) {
        self::$urlFetchLimitCounter = 0;
        self::$recursionCountLimitCounter = 0;
        self::$allFoundUrls = array();
        self::$recursionRoot = null;
        self::$recursive = $recursive;
        
        $out = self::_createAllFromLine($irc, $line);
        
        return $out;
    }
    
    private function findChildUrls() {
        if (self::$recursionDepthLimitCounter >= self::RECURSION_DEPTH_LIMIT)
            return array();
        self::$recursionDepthLimitCounter++;
        
        if (!self::$recursionRoot)
            self::$recursionRoot = $this;
        
        $line = self::$recursionRoot->line;
        if ($this->isRedirect()) {
            $line['text'] = $this->redirectTarget;
        } else {
            $line['text'] = $this->outputMsg;
        }
        
        $out = self::_createAllFromLine(self::$recursionRoot->irc, $line);
        
        self::$recursionDepthLimitCounter--;
        $this->childUrls = $out;
    }
    
    public static function findUrls($message) {
        if (preg_match_all(self::URL_REGEX, $message, $m, PREG_PATTERN_ORDER)) {
//            foreach ($m[1] as &$v) {
//                if (!preg_match('#https?://#i', $v)) {
//                    echo "URL $v doesn't start with a scheme. Adding one.\r\n";
//                    $v = 'http://' . $v;
//                }
//            } unset($v);
            return $m[1];
        }
        return array();
    }
    
    public function debugDump() {
        var_dump(array(
            'message' => $this->message,
            'url' => $this->url,
            'urlDestination' => $this->urlDestination,
            'isRedirect' => $this->isRedirect(),
            'redirectTarget' => $this->redirectTarget,
            'pageTitle' => $this->pageTitle,
            'outputMsg' => $this->outputMsg,
        ));
    }
    
    public function __toString() {
        return $this->url;
    }
    
    private static function _replaceUrlsWithDestinationsInString($m) {
        /** @var URL_dummy */ $url = new self(null, null, $m[0], false);
        return $url->getEventualRedirectTargetUrl();
    }
    
    public static function replaceUrlsWithDestinationsInString($string) {
        return preg_replace_callback(self::URL_REGEX, 'self::_replaceUrlsWithDestinationsInString', $string);
    }
}

?>