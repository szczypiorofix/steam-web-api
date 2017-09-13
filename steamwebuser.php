<?php

class SteamWebUser
{
    public $steamId;
    public $personaname;
    public $profileurl;
    public $avatar;
    public $avatarmedium;
    public $avatarfull;
    public $realname;
    public $personastateflags;
    public $personastate;
    public $lastlogoff;
    public $profilestate;
    public $response;
    public $loccountrycode;
    public $communityvisibilitystate;
    public $gamesIdLastPlayed = [];
    public $ownedGames = [];
    public $recentlyPlayedGamesAchievements = [];
    
    public function __construct() {}
}