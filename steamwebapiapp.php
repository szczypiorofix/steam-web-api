<?php

class SteamWebApiApp
{
    private $steamUser = null;
    
    public function __construct() {}
    
    private function getDataFromAPI($url, $type, $args) {
        $c = curl_init();
        curl_setopt($c, CURLOPT_HEADER, 0);
        curl_setopt($c, CURLOPT_VERBOSE, 0);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_URL, $url);
        //url_setopt($c, CURLOPT_CAINFO, dirname(__FILE__) .  '/trello.com.crt');

        if (count($args)) {
            curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($args));
        }

        switch ($type) {
            case 'POST':
                curl_setopt($c, CURLOPT_POST, 1);
                break;
            case 'GET':
                curl_setopt($c, CURLOPT_HTTPGET, 1);
                break;
            default:
                curl_setopt($c, CURLOPT_CUSTOMREQUEST, $type);
        }
        $data = curl_exec($c);
        echo curl_error($c);
        curl_close($c);
        return json_decode($data);
    }
    
    public function getUserContent() {
        $content = '';
        //$content .= '<p>User name: '.$this->steamUser->personaname.'</p>';
        //$content .= '<p>User id: '.$this->steamUser->steamId.'</p>';
        $content .= '<div class="userdiv"><div class="leftpaneluser">';
        $content .= '<img src="'.$this->steamUser->avatarfull.'" alt="User avatar"/>';
        $content .= '</div>';
        $content .= '<div class="rightpaneluser">';
        $content .= '<p class="username"><a href="'.$this->steamUser->profileurl.'">'.$this->steamUser->personaname.'</a></p>';

        $content .= '<p>'.$this->steamUser->realname.'</p>';
        $content .= '<p>Country: '.$this->steamUser->loccountrycode.'</p>';
        $content .= '<p>Community state: ';
        switch ($this->steamUser->communityvisibilitystate) {
            case 1 :
                $content .= 'Private';
                break;
            case 3 :
                $content .= 'Public';
                break;
        }
        $content .= '</p>';
        $content .= '<p>Status: ';
        switch ($this->steamUser->personastate) {
            case 0 :
                $content .= 'Offline';
                $cd = new DateTime();
                $pd = new DateTime(date("Y-m-d H:i:s", $this->steamUser->lastlogoff));
                $diff = $cd->diff($pd);
                
                $content .= '<p>Last online: '.$diff->h.' hours, '.$diff->i.' minutes ago.</p>';
                break;
            case 1 :
                $content .= 'Online';
                break;
            case 2 :
                $content .= 'Busy';
                break;
            case 3 :
                $content .= 'Away';
                break;
            case 4 :
                $content .= 'Snooze';
                break;
            case 5 :
                $content .= 'Looking to trade';
                break;
            case 6 :
                $content .= 'Looking to play';
                break;
        }
        $content .= '</p></div><div class="clear"></div>';
        $content .= '</div></div>';
        return $content;
    }
    
    public function getOwnedGames($type, $key, $userId)
    {
        $args = array();
        //http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key=A5FD61367C73D974CA294A3E08CCBE53&steamid=76561198076742801&format=json&include_appinfo=1
        $url = 'http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?key='.$key.'&steamid='.$userId.'&format=json&include_appinfo=1&include_played_free_games=1';
        $dataFromAPI = $this->getDataFromAPI($url, $type, $args);
        
        $response = $dataFromAPI->response;
        $content = '<br><details><summary>OWNED GAMES: '.$response->game_count.'</summary>';
        $this->steamUser->ownedGames = $response->games;
         
        uasort($this->steamUser->ownedGames, function($a, $b) {
            if ($b->playtime_forever == $a->playtime_forever) {
                return 0;
            }
            return ($b->playtime_forever < $a->playtime_forever) ? - 1 : 1;
        });
        
        foreach ($this->steamUser->ownedGames as $game) {
            $content .= '<p style="font-size: 18px;">'.$game->name.'</p>';
            if ($game->img_logo_url !== '') {
                $content .= '<img src="http://media.steampowered.com/steamcommunity/public/images/apps/'.$game->appid.'/'.$game->img_logo_url.'.jpg" alt="game icon" class="achievementicon"/>';
                $content .= '<p style="margin-bottom: 5px;">Hours played (total): '.sprintf('%02dh:%02dm', (int) ($game->playtime_forever / 60), ($game->playtime_forever % 60)).'</p><br>';
            }
        }
        $content .= '</details>';
        return $content;
        //return print_r($dataFromAPI, true);
    }
    
    public function getRecentlyPlayedGames($type, $key, $userId)
    {
        $args = array();
        //http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=XXXXXXXXXXXXXXXXXXXXXXX&steamids=76561197960435530
        $url = 'http://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/?key='.$key.'&steamid='.$userId.'&format=json&count=3';
        $dataFromAPI = $this->getDataFromAPI($url, $type, $args);
        
        $response = $dataFromAPI->response;
        $arrayOfGames = $response->games;
        
        $content = '<br><h3>Recently most played games:</h3>';        
        for ($i = 0; $i < count($arrayOfGames); $i++) {
            //$content .= '<p>Name: '.$arrayOfGames[$i]->name.', Id: '.$arrayOfGames[$i]->appid.'</p>';
            $this->steamUser->gamesIdLastPlayed[] = $arrayOfGames[$i]->appid;
        }
        
        for ($j = 0; $j < count($this->steamUser->ownedGames); $j++) {
            for ($i = 0; $i < count($this->steamUser->gamesIdLastPlayed); $i++) {
                if ($this->steamUser->ownedGames[$j]->appid === $this->steamUser->gamesIdLastPlayed[$i]) {
                    if ($this->steamUser->ownedGames[$j]->img_logo_url !== '') {
                        $content .= '<div style="height: 100%; padding: 10px; border-radius: 5px; background-color: #071720;"><div class="leftpanel">';
                        $content .= '<img src="http://media.steampowered.com/steamcommunity/public/images/apps/'.$this->steamUser->ownedGames[$j]->appid.'/'.$this->steamUser->ownedGames[$j]->img_logo_url.'.jpg" alt="game icon"/>';
                        $content .= '</div>';
                        $content .= '<div class="rightpanel"><p style="margin-top: 5px;"><b>'.$this->steamUser->ownedGames[$j]->name.'</b></p>';
                        $content .= '<p>Hours played (last 2 weeks): '.sprintf('%02dh: %02dm', (int) ($this->steamUser->ownedGames[$j]->playtime_2weeks / 60), ($this->steamUser->ownedGames[$j]->playtime_2weeks % 60)).'</p>';
                        $content .= '<p>Hours played (total): '.sprintf('%02dh: %02dm', (int) ($this->steamUser->ownedGames[$j]->playtime_forever / 60), ($this->steamUser->ownedGames[$j]->playtime_forever % 60)).'</p>';
                        $content .= '</div><div class="clear"></div>';
                        //$content .= $this->getUserAchievements('GET', $key, $userId, $this->steamUser->ownedGames[$j]->appid);
                        $this->getGameAchievements('GET', $key, $userId, $this->steamUser->ownedGames[$j]->appid);
                        $content .= $this->getUserAchievements('GET', $key, $userId, $this->steamUser->ownedGames[$j]->appid);
                        $content .= '</div><br>';
                    }
                }
            }
        }
        
        //$content = print_r($arrayOfGames);
        return $content;
    }
    
    public function getGameAchievements($type, $key, $userId, $appid)
    {
        $args = array();
        ///http://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2/?key=A5FD61367C73D974CA294A3E08CCBE53&appid=588430
        $url = 'http://api.steampowered.com/ISteamUserStats/GetSchemaForGame/v2/?key='.$key.'&appid='.$appid;
        $dataFromAPI = $this->getDataFromAPI($url, $type, $args);
        $content = '';
        //$content .= '<p>Achievements:</p>';
        $gameAchievements = '';
        if (isset($dataFromAPI->game->availableGameStats)) {
            $gameAchievements = $dataFromAPI->game->availableGameStats->achievements;
            $this->steamUser->recentlyPlayedGamesAchievements = $dataFromAPI->game->availableGameStats->achievements;            
            foreach($gameAchievements as $ach) {
                $content .= '<img src="'.$ach->icon.'" alt="Achievement description"/>';
            }
        }
        else {
            $this->steamUser->recentlyPlayedGamesAchievements[] = null;
            $content = '<p>no achievements</p>';
        }
        //$content = print_r($gameAchievements, true);
        //return $content;
    }
    
    public function getUserAchievements($type, $key, $userId, $appid)
    {
        $args = array();
        //http://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v0001/?key=A5FD61367C73D974CA294A3E08CCBE53&steamid=76561198076742801&appid=588430
        $url = 'http://api.steampowered.com/ISteamUserStats/GetPlayerAchievements/v0001/?key='.$key.'&steamid='.$userId.'&l=en&appid='.$appid;
        $dataFromAPI = $this->getDataFromAPI($url, $type, $args);
        
        $content = '';
        
        if (isset($dataFromAPI->playerstats->gameName)) {
            //$content .= '<p>Achievements:</p>';
            $achievements = $dataFromAPI->playerstats->achievements;
            //print_r($this->steamUser->recentlyPlayedGamesAchievements);
            $userAchievements = 0;
            foreach($achievements as $achC) {
                if ($achC->achieved === 1) {
                    foreach($this->steamUser->recentlyPlayedGamesAchievements as $uachC) {
                        if ($uachC->name === $achC->apiname) {
                            $userAchievements++;
                        }
                    }
                }
            }
            $content .= '<p>Achievements: '.$userAchievements.' / '.count($this->steamUser->recentlyPlayedGamesAchievements).', Percentage: '.round($userAchievements / count($this->steamUser->recentlyPlayedGamesAchievements) * 100).'%</p>';
            foreach($achievements as $ach) {
                if ($ach->achieved === 1) {
                    //print_r($ach);
                    foreach($this->steamUser->recentlyPlayedGamesAchievements as $uach) {
                        if ($uach->name === $ach->apiname) {
                            $desc = '[description not available]';
                            if (isset($uach->description)) {
                                $desc = $uach->description;
                            }
                            $content .= '<img class="achievementicon" src="'.$uach->icon.'" alt="'.$uach->displayName.'" style="height: 50px;" title="'.$uach->displayName.': '.$desc.'";/>';
                        }
                    }
                }
            }
        foreach($achievements as $ach) {
            if ($ach->achieved === 0) {
                $content .= '<img class="achievementicon" src="hidden.png" alt="HIDDEN" style="height: 50px;" title="HIDDEN";/>';
            }
        }
            
        }
        return $content;
    }
    
    public function getUserData($type, $key, $userId)
    {
        $args = array();

        //  http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=XXXXXXXXXXXXXXXXXXXXXXX&steamids=76561197960435530
        $url = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key='.$key.'&steamids='.$userId;
        $dataFromAPI = $this->getDataFromAPI($url, $type, $args);
        
        $this->steamUser = new SteamWebUser();
        $content = '';
        
        if ($dataFromAPI !== '') {
            $this->steamUser = $dataFromAPI;
            $this->steamUser->response = $this->steamUser->response->players[0];
            $this->steamUser->steamId = $this->steamUser->response->steamid;
            $this->steamUser->personaname = $this->steamUser->response->personaname;
            $this->steamUser->profileurl = $this->steamUser->response->profileurl;
            $this->steamUser->avatar = $this->steamUser->response->avatar;
            $this->steamUser->avatarmedium = $this->steamUser->response->avatarmedium;
            $this->steamUser->avatarfull = $this->steamUser->response->avatarfull;
            $this->steamUser->realname = $this->steamUser->response->realname;
            $this->steamUser->loccountrycode = $this->steamUser->response->loccountrycode;
            $this->steamUser->communityvisibilitystate = $this->steamUser->response->communityvisibilitystate;
            $this->steamUser->personastate = $this->steamUser->response->personastate;
            $this->steamUser->lastlogoff = $this->steamUser->response->lastlogoff;
            $content = $this->getUserContent();
        }
        
        //$content = print_r($dataFromAPI);
        return $content;
    }
}