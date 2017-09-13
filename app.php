<?php

class App
{
    private static $output;
    private $steamUser;
    private static $steamkey = null;
    private static $steamuserid = null;
    
    private function __construct() {}
    private function __clone() {}
    
    public static function init() {
        $configData = parse_ini_file('.config');
        self::$steamkey = $configData['STEAMKEY'];
        self::$steamuserid = $configData['STEAMUSERID'];
        
        $steamApi = new SteamWebApiApp();
        self::$output = $steamApi->getUserData('GET', self::$steamkey, self::$steamuserid); // 'GET', SteamWebAPI key, userId
        $steamApi->getOwnedGames('GET', self::$steamkey, self::$steamuserid);
        self::$output .= $steamApi->getRecentlyPlayedGames('GET', self::$steamkey, self::$steamuserid);
        // ALL GAMES
        //self::$output .= $steamApi->getOwnedGames('GET', self::$steamkey, self::$steamuserid);
    }
    
    public static function showOutput() {
        //echo '<pre>'.self::$output.'</pre>';
        echo self::$output;
    }
}