<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WhatToPlay?:thinking</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css?family=Red+Hat+Display&display=swap" rel="stylesheet">
    <script>
    document.onkeydown=function(evt){
        var keyCode = evt ? (evt.which ? evt.which : evt.keyCode) : event.keyCode;
        if(keyCode == 13)
        {
            document.url.submit();
        }
    }
</script>
</head>
<body>
<div class="container">
    <div class="form-wrapper">
        <form action="index.php" class="profileUrl">
            <input type="text" name ="url" placeholder="Write your Steam Profile URL" id="url">
            <div class="features">
                <input type="submit" value="OK" id="send">
                <label for="#freegames">
                    <input type="checkbox" id="freegames" name="freegames">Include free games
                </label>
            </div>
        </form>
    </div>
            <p>e.g: <span class="example">'https://steamcommunity.com/id/mediocreeee' </span> <br> or: <span class="example">'https://steamcommunity.com/profiles/76561198857721734'</span></p>
    <?php

//BASIC VARIABLES
$key = 'EC5CABEEED9100CB16C508CA64F3A647';

$linkUser = "http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?";
//GETTING USERS STEAM URL FROM USER INPUT
$url = $_GET['url'];
setcookie($steamurl, $url, time() + (86400 * 30), "/");
$urlParsed = parse_url($url);
$path = $urlParsed["path"];

//GETTING JUST A VANITYURL OR STEAM ID
if ($path[1] == 'i') {
	if (substr($path, -1) == '/') {
		$vanityUrl = substr($path, 4, -1);
	} else {
		$vanityUrl = substr($path, 4);
	}
	$dataUser = array(
		'key' => $key,
		'vanityurl' => $vanityUrl,
	);
//MAKING REQUEST TO STEAM WEB ABOUT USER DATA
	$request_query_user = http_build_query($dataUser);
	$requestUser = $linkUser . $request_query_user;
	
//PARSING RESPONSE
	$fileUser = file_get_contents($requestUser);
	$userJSON = json_decode($fileUser, TRUE);

//GETTING STEAMID FROM RESPONSE
	$steamid = $userJSON['response']['steamid'];
} elseif ($path[1] == 'p') {
	if (substr($path, -1) == '/') {
		$steamid = substr($path, 10, -1);
	} else {
		$steamid = substr($path, 10);
	}
}

//PART2
$linkGames = 'http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/?';
$format = 'json';
$include_appinfo = 'true';

if (isset($_GET['freegames'])) {
	$include_played_free_games = 'true';
} else {
	$include_played_free_games = 'false';
}

//GENERATING _GET QUERY FOR USERS OWNED GAMES REQUEST
$dataGames = array(
	'key' => $key,
	'steamid' => $steamid,
	'format' => $format,
	'include_appinfo' => $include_appinfo,
	'include_played_free_games' => $include_played_free_games,
);

//MAKING REQUEST TO STEAM API ABOUT USERS OWNED GAMES
$request_query_games = http_build_query($dataGames);
$requestGames = $linkGames . $request_query_games;
//PARSING RESPONSE
$fileGames = file_get_contents($requestGames);
$gamesJSON = json_decode($fileGames, TRUE);

//GENERATING USERS OWNED GAMES ARRAY
for ($i = 0; $i < sizeof($gamesJSON["response"]["games"]); $i++) {
	$gamesList[] = [
		'name' => $gamesJSON["response"]["games"][$i]["name"],
		'appid' => $gamesJSON["response"]["games"][$i]["appid"],
		'img_logo_url' => $gamesJSON["response"]["games"][$i]["img_logo_url"],
		'img_icon_url' => $gamesJSON["response"]["games"][$i]['img_icon_url'],
	];
}
// print_r($gamesList);
//GETTING RANDOM GAME FROM OWNED GAMES ARRAY
$randGame = array_rand($gamesList);
//GETTING RANDOM GAME LOGO
$imageLink = 'http://media.steampowered.com/steamcommunity/public/images/apps/';
$gameId = $gamesList[$randGame]['appid'];
$logoHash = $gamesList[$randGame]['img_logo_url'];
$logoUrl = $imageLink . $gameId . '/' . $logoHash . '.jpg';
$logoData = base64_encode(file_get_contents($logoUrl));
//GETTING RANDOM GAME ICON
$iconHash = $gamesList[$randGame]['img_icon_url'];
$iconUrl = $imageLink . $gameId . '/' . $iconHash . '.jpg';
$iconData = base64_encode(file_get_contents($iconUrl));

//GETTING RANDOM GAME URL IN STORE
$storeLink = 'https://store.steampowered.com/app/';
$gameUrl = $storeLink . $gameId;
?>
        <div id="randomGame">
            <p>
                I think you should try. . .
            	<br>
                <br>
                <span class="gameName">
 <?php
echo '<img src="data: image/jpeg; base64,' . $iconData . '">';
echo $gamesList[$randGame]['name'];
?>
                </span>
            </p>
        </div>
        <br>
        <div class="randomGameImage">
            <br>
<?php
echo '<a href="' . $gameUrl . '"><img src="data: image/jpeg; base64,' . $logoData . '"></a>';
?>
            <div class="zhopa">
                 clickable&#8607;
            </div>
        </div>
    </div>
</body>
</html>
