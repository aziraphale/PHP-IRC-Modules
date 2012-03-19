<?php

if (!function_exists('loadDynamicClass')) {
    function loadDynamicClass(&$className, $filename) {
        static $loadTimes = array(), $classNames = array();
        
        $origFilename = $filename;
        if (substr($filename, 0, 1) != '/')
            $filename = dirname(__FILE__) . '/' . $filename;
        
        $firstLoad = !isset($loadTimes[$filename]);
        
        if (file_exists($filename)) {
            if (!$firstLoad) {
                if (filemtime($filename) <= $loadTimes[$filename]) {
                    //Not modified
                    $className = $classNames[$filename];
                    return;
                }
            }
            
            $classContent = file_get_contents($filename);
            $className = $className . '_' . md5(uniqid('', true));
            if (preg_match('/^class\s+[a-z0-9_]+\s+extends\s+([a-z0-9_]+)\s+\{/im', $classContent, $m)) {
                $classContent = preg_replace('/^class\s+[a-z0-9_]+\s+extends\s+[a-z0-9_]+\s+\{/im', "class $className extends {$m[1]} {", $classContent);
                
                if ($firstLoad) {
                    eval("class {$m[1]} {}");
                }
                eval(" ?>$classContent<?php ");
                
                $loadTimes[$filename] = time();
                $classNames[$filename] = $className;
            } else {
                throw new Exception("Unable to find a class definition in '$origFilename'.");
            }
        } else {
            throw new Exception("File '$origFilename' does not exist.");
        }
    }
}

?>