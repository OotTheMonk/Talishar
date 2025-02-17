<?php

use SendGrid\Mail\Mail;

// Check for empty input signup
function emptyInputSignup($username, $email, $pwd, $pwdRepeat)
{
	if (empty($username) || empty($email) || empty($pwd) || empty($pwdRepeat)) {
		$result = true;
	} else {
		$result = false;
	}
	return $result;
}

// Check invalid username
function invalidUid($username)
{
	if (!ctype_alnum($username)) {
		$result = true;
	} else {
		$result = false;
	}
	return $result;
}

// Check invalid email
function invalidEmail($email)
{
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$result = true;
	} else {
		$result = false;
	}
	return $result;
}

// Check if passwords matches
function pwdMatch($pwd, $pwdrepeat)
{
	if ($pwd !== $pwdrepeat) {
		$result = true;
	} else {
		$result = false;
	}
	return $result;
}

// Check if username is in database, if so then return data
function uidExists($conn, $username)
{
	$conn = GetDBConnection();
	$sql = "SELECT * FROM users WHERE usersUid = ? OR usersEmail = ?;";
	$stmt = mysqli_stmt_init($conn);
	if (!mysqli_stmt_prepare($stmt, $sql)) {
		header("location: ../Signup.php?error=stmtfailed");
		exit();
	}

	mysqli_stmt_bind_param($stmt, "ss", $username, $email);
	mysqli_stmt_execute($stmt);

	// "Get result" returns the results from a prepared statement
	$resultData = mysqli_stmt_get_result($stmt);

	if ($row = mysqli_fetch_assoc($resultData)) {
		return $row;
	} else {
		$result = false;
		return $result;
	}
	mysqli_stmt_close($stmt);
	mysqli_close($conn);
}

// Insert new user into database
function createUser($conn, $username, $email, $pwd, $reportingServer = false)
{
	if ($reportingServer) $conn = GetReportingDBConnection();
	else $conn = GetDBConnection();
	$sql = "INSERT INTO users (usersUid, usersEmail, usersPwd) VALUES (?, ?, ?);";

	$stmt = mysqli_stmt_init($conn);
	if (!mysqli_stmt_prepare($stmt, $sql)) {
		header("location: ../Signup.php?error=stmtfailed");
		exit();
	}

	$hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);

	mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashedPwd);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	mysqli_close($conn);
	header("location: ../Signup.php?error=none");
	exit();
}

function CreateUserAPI($conn, $username, $email, $pwd)
{
	$conn = GetDBConnection();
	$sql = "INSERT INTO users (usersUid, usersEmail, usersPwd) VALUES (?, ?, ?);";

	$stmt = mysqli_stmt_init($conn);
	if (!mysqli_stmt_prepare($stmt, $sql)) {
		return false;
	}

	$hashedPwd = password_hash($pwd, PASSWORD_DEFAULT);

	mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashedPwd);
	mysqli_stmt_execute($stmt);
	mysqli_stmt_close($stmt);
	mysqli_close($conn);
}

function loginFromCookie()
{
	$token = $_COOKIE["rememberMeToken"];
	$conn = GetDBConnection();
	$sql = "SELECT usersId, usersUid, usersEmail, patreonAccessToken, patreonRefreshToken, patreonEnum, isBanned, lastGameName, lastPlayerId, lastAuthKey FROM users WHERE rememberMeToken=?";
	$stmt = mysqli_stmt_init($conn);
	if (mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "s", $token);
		mysqli_stmt_execute($stmt);
		$data = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_array($data, MYSQLI_NUM);
		mysqli_stmt_close($stmt);
		if (session_status() !== PHP_SESSION_ACTIVE) session_start();
		if ($row != null && count($row) > 0) {
			$_SESSION["userid"] = $row[0];
			$_SESSION["useruid"] = $row[1];
			$_SESSION["useremail"] = $row[2];
			$patreonAccessToken = $row[3];
			$patreonRefreshToken = $row[4];
			$_SESSION["patreonEnum"] = $row[5];
			$_SESSION["isBanned"] = $row[6];
			$_SESSION["lastGameName"] = $row[7];
			$_SESSION["lastPlayerId"] = $row[8];
			$_SESSION["lastAuthKey"] = $row[9];
			try {
				PatreonLogin($patreonAccessToken);
			} catch (\Exception $e) {
			}
		} else {
			unset($_SESSION["userid"]);
			unset($_SESSION["useruid"]);
			unset($_SESSION["useremail"]);
			unset($_SESSION["patreonEnum"]);
			unset($_SESSION["isBanned"]);
			unset($_SESSION["lastGameName"]);
			unset($_SESSION["lastPlayerId"]);
			unset($_SESSION["lastAuthKey"]);
		}
		session_write_close();
	}
	mysqli_close($conn);
}

function storeFabraryId($uid, $fabraryId)
{
	$conn = GetDBConnection();
	$sql = "UPDATE users SET fabraryId=? WHERE usersId=?";
	$stmt = mysqli_stmt_init($conn);
	if (mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "ss", $fabraryId, $uid);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	}
}

function storeFabDBId($uid, $fabdbId)
{
	$conn = GetDBConnection();
	$sql = "UPDATE users SET fabdbId=? WHERE usersId=?";
	$stmt = mysqli_stmt_init($conn);
	if (mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "ss", $fabdbId, $uid);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	}
}

function StoreLastGameInfo($uid, $gameName, $playerID, $authKey)
{
	$conn = GetDBConnection();
	$sql = "UPDATE users SET lastGameName=?, lastPlayerId=?, lastAuthKey=? WHERE usersId=?";
	$stmt = mysqli_stmt_init($conn);
	if (mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "ssss", $gameName, $playerID, $authKey, $uid);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	}
	mysqli_close($conn);

	if(session_status() !== PHP_SESSION_ACTIVE) session_start();
	$_SESSION["lastGameName"] = $gameName;
	$_SESSION["lastPlayerId"] = $playerID;
	$_SESSION["lastAuthKey"] = $authKey;
	session_write_close();
}

function GetDeckBuilderId($uid, $decklink)
{
	$conn = GetDBConnection();
	$sql = "SELECT fabraryId,fabdbId FROM users WHERE usersId=?";
	$stmt = mysqli_stmt_init($conn);
	if (mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "s", $uid);
		mysqli_stmt_execute($stmt);
		$data = mysqli_stmt_get_result($stmt);
		$row = mysqli_fetch_array($data, MYSQLI_NUM);
		mysqli_stmt_close($stmt);
	}
	mysqli_close($conn);
	$dbId = "";
	if (count($row) == 0) return "";
	if (str_contains($decklink, "fabrary")) $dbId = $row[0];
	else if (str_contains($decklink, "fabdb")) $dbId = $row[1];
	if ($dbId == "NULL") $dbId = "";
	return $dbId;
}

function addFavoriteDeck($userID, $decklink, $deckName, $heroID, $format = "")
{
	$conn = GetDBConnection();
	$deckName = implode("", explode("\"", $deckName));
	$deckName = implode("", explode("'", $deckName));
	$values = "'" . $decklink . "'," . $userID . ",'" . $deckName . "','" . $heroID . "','" . $format . "'";
	$sql = "INSERT IGNORE INTO favoritedeck (decklink, usersId, name, hero, format) VALUES (?, ?, ?, ?, ?);";
	$stmt = mysqli_stmt_init($conn);
	if (mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "sssss", $decklink, $userID, $deckName, $heroID, $format);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	}
	mysqli_close($conn);
}

function LoadFavoriteDecks($userID)
{
	if ($userID == "") return [];
	$conn = GetDBConnection();
	$sql = "SELECT decklink, name, hero, format from favoritedeck where usersId=?";
	$stmt = mysqli_stmt_init($conn);
	$output = [];
	if (mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "s", $userID);
		mysqli_stmt_execute($stmt);
		$data = mysqli_stmt_get_result($stmt);
		while ($row = mysqli_fetch_array($data, MYSQLI_NUM)) {
			for ($i = 0; $i < 4; ++$i) array_push($output, $row[$i]);
		}
		mysqli_stmt_close($stmt);
	}
	mysqli_close($conn);
	return $output;
}

//Challenge ID 1 = sigil of solace blue
//Challenge ID 2 = Talishar no dash
//Challenge ID 3 = Moon Wish
function logCompletedGameStats()
{
	global $winner, $currentTurn, $gameName; //gameName is assumed by ParseGamefile.php
	global $p1id, $p2id, $p1IsChallengeActive, $p2IsChallengeActive, $p1DeckLink, $p2DeckLink, $firstPlayer;
	global $p1deckbuilderID, $p2deckbuilderID;
	$loser = ($winner == 1 ? 2 : 1);
	$columns = "WinningHero, LosingHero, NumTurns, WinnerDeck, LoserDeck, WinnerHealth, FirstPlayer";
	$values = "?, ?, ?, ?, ?, ?, ?";
	$winnerDeck = file_get_contents("./Games/" . $gameName . "/p" . $winner . "Deck.txt");
	$loserDeck = file_get_contents("./Games/" . $gameName . "/p" . $loser . "Deck.txt");
	$winHero = &GetPlayerCharacter($winner);
	$loseHero = &GetPlayerCharacter($loser);

	$conn = GetDBConnection();

	if ($p1id != "" && $p1id != "-") {
		$columns .= ", " . ($winner == 1 ? "WinningPID" : "LosingPID");
		$values .= ", " . $p1id;
	}
	if ($p2id != "" && $p2id != "-") {
		$columns .= ", " . ($winner == 2 ? "WinningPID" : "LosingPID");
		$values .= ", " . $p2id;
	}

	$sql = "INSERT INTO completedgame (" . $columns . ") VALUES (" . $values . ");";
	$stmt = mysqli_stmt_init($conn);
	$gameResultID = 0;
	if (mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "sssssss", $winHero[0], $loseHero[0], $currentTurn, $winnerDeck, $loserDeck, GetHealth($winner), $firstPlayer);
		mysqli_stmt_execute($stmt);
		$gameResultID = mysqli_insert_id($conn);
		mysqli_stmt_close($stmt);
	}

	if ($p1IsChallengeActive == "1" && $p1id != "-") LogChallengeResult($conn, $gameResultID, $p1id, ($winner == 1 ? 1 : 0));
	if ($p2IsChallengeActive == "1" && $p2id != "-") LogChallengeResult($conn, $gameResultID, $p2id, ($winner == 2 ? 1 : 0));

	$p1Deck = ($winner == 1 ? $winnerDeck : $loserDeck);
	$p2Deck = ($winner == 2 ? $winnerDeck : $loserDeck);
	$p1Hero = ($winner == 1 ? $winHero[0] : $loseHero[0]);
	$p2Hero = ($winner == 2 ? $winHero[0] : $loseHero[0]);

	if (!AreStatsDisabled(1)) SendFabDBResults(1, $p1DeckLink, $p1Deck, $gameResultID, $p2Hero);
	if (!AreStatsDisabled(2)) SendFabDBResults(2, $p2DeckLink, $p2Deck, $gameResultID, $p1Hero);
	if (!AreStatsDisabled(1) && !AreStatsDisabled(2)) SendFullFabraryResults($gameResultID, $p1DeckLink, $p1Deck, $p1Hero, $p1deckbuilderID, $p2DeckLink, $p2Deck, $p2Hero, $p2deckbuilderID);
	
	// Sends data to FabInsights DB
	if (!AreStatsDisabled(1) && !AreStatsDisabled(2)) SendFaBInsightsResults($gameResultID, $p1DeckLink, $p1Deck, $p1Hero, $p1deckbuilderID, $p2DeckLink, $p2Deck, $p2Hero, $p2deckbuilderID);

	mysqli_close($conn);
}

function LogChallengeResult($conn, $gameResultID, $playerID, $result)
{
	WriteLog("Writing challenge result for player " . $playerID);
	$challengeId = 3;
	$sql = "INSERT INTO challengeresult (gameId, challengeId, playerId, result) VALUES (?, ?, ?, ?);";
	$stmt = mysqli_stmt_init($conn);
	if (mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "ssss", $gameResultID, $challengeId, $playerID, $result); //Challenge ID 1 = sigil of solace blue
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	}
}


function SendFabDBResults($player, $decklink, $deck, $gameID, $opposingHero)
{
	global $fabDBToken, $fabDBSecret, $gameName, $p1deckbuilderID, $p2deckbuilderID;
	if($decklink == null || !str_contains($decklink, "fabdb.net")) return;

	$linkArr = explode("/", $decklink);
	$slug = array_pop($linkArr);

	$url = "https://api.fabdb.net/game/results/" . $slug;
	$ch = curl_init($url);
	$payload = SerializeGameResult($player, $decklink, $deck, $gameID, $opposingHero, $gameName);
	$payloadArr = json_decode($payload, true);
	$payloadArr["time"] = microtime();
	$payloadArr["hash"] = hash("sha512", $fabDBSecret . $payloadArr["time"]);
	$payloadArr["player"] = $player;
	$payloadArr["user"] = ($player == 1 ? $p1deckbuilderID : $p2deckbuilderID);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payloadArr));
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);

	$headers = array(
		"Authorization: Bearer " . $fabDBToken,
	);

	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	$result = curl_exec($ch);
	$information = curl_getinfo($ch);
	curl_close($ch);
}

function SendFullFabraryResults($gameID, $p1Decklink, $p1Deck, $p1Hero, $p1deckbuilderID, $p2Decklink, $p2Deck, $p2Hero, $p2deckbuilderID)
{
	global $FaBraryKey, $gameName, $p2IsAI;
	if($p2IsAI == "1") return;//Don't file results for AI games
	$url = "https://atofkpq0x8.execute-api.us-east-2.amazonaws.com/prod/v1/results";
	$ch = curl_init($url);
	$payloadArr = [];
	$payloadArr['gameID'] = $gameID;
	$payloadArr['gameName'] = $gameName;
	$payloadArr['deck1'] = json_decode(SerializeGameResult(1, $p1Decklink, $p1Deck, $gameID, $p2Hero, $gameName, $p1deckbuilderID));
	$payloadArr['deck2'] = json_decode(SerializeGameResult(2, $p2Decklink, $p2Deck, $gameID, $p1Hero, $gameName, $p2deckbuilderID));
	$payloadArr["format"] = GetCachePiece(intval($gameName), 13);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadArr));
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
	$headers = array(
		"x-api-key: " . $FaBraryKey,
		"Content-Type: application/json",
	);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);

	/*
	//Uncomment to log fabrary stats reporting
	$logfile = "./BugReports/FabraryStatsLogging.txt";
	$logHandler = fopen($logfile, "a");
	date_default_timezone_set('America/Chicago');
	$logDate = date('m/d/Y h:i:s a');
	$logText = "Game log sent to fabrary for game $gameName at $logDate. $p1deckbuilderID as $p1Hero with $p1Decklink vs $p2deckbuilderID as $p2Hero with $p2Decklink. API Response: $result";
	fwrite($logHandler, $logText . "\r\n");
	fclose($logHandler);
	*/

	curl_close($ch);
}

function HashPlayerName($name, $salt) {
    if(empty($name)) return "";
    if(empty($salt)) {
        error_log("WARNING: Player name hash salt not configured - player names will not be sent");
        return "";
    }
    // Use HMAC with SHA-256 for consistent but secure hashing
    return hash_hmac('sha256', $name, $salt);
}

function SendFaBInsightsResults($gameID, $p1DeckLink, $p1Deck, $p1Hero, $p1deckbuilderID, $p2DeckLink, $p2Deck, $p2Hero, $p2deckbuilderID)
{
	global $FaBInsightsKey, $gameName, $p2IsAI, $p1uid, $p2uid, $playerHashSalt;
    // Skip AI games
    if ($p2IsAI == "1") return;

    // Hash player names
    $hashedP1Name = HashPlayerName($p1uid, $playerHashSalt);
    $hashedP2Name = HashPlayerName($p2uid, $playerHashSalt);

    // Your Azure Function endpoint URL
    $url = "https://fab-insights.azurewebsites.net/api/send_results";

    // Prepare the data for the POST request
    $payloadArr = [];
    $payloadArr['gameID'] = $gameID;
    $payloadArr['gameName'] = $gameName;
    $payloadArr['player1Name'] = $hashedP1Name;
    $payloadArr['player2Name'] = $hashedP2Name;
    $payloadArr['deck1'] = json_decode(SerializeDetailedGameResult(1, $p1DeckLink, $p1Deck, $gameID, $p2Hero, $gameName, $p1deckbuilderID, $p1Hero));
    $payloadArr['deck2'] = json_decode(SerializeDetailedGameResult(2, $p2Decklink, $p2Deck, $gameID, $p1Hero, $gameName, $p2deckbuilderID, $p2Hero));
    $payloadArr["format"] = GetCachePiece(intval($gameName), 13);

    // Initialize cURL
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payloadArr));  // Send JSON data
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                 // Get the response back
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",  // Specify the content type
		"x-functions-key: " . $FaBInsightsKey // Add x-functions-key header
    ]);

    // Execute the request and get the response
    $response = curl_exec($ch);

	// Uncomment to log fab-insights stats reporting
	// $logfile = "./BugReports/FabInsightsStatsLogging.txt";
	// $logHandler = fopen($logfile, "a");
	// date_default_timezone_set('America/Chicago');
	// $logDate = date('m/d/Y h:i:s a');
	// $logText = "Game log sent to FaBInsights for game $gameName at $logDate. $p1deckbuilderID as $p1Hero with $p1Decklink vs $p2deckbuilderID as $p2Hero with $p2Decklink. API Response: $response";
	// fwrite($logHandler, $logText . "\r\n");
	// fclose($logHandler);

    // Close the cURL session
    curl_close($ch);
}


function SerializeGameResult($player, $DeckLink, $deckAfterSB, $gameID = "", $opposingHero = "", $gameName = "", $deckbuilderID = "", $includeFullLog=false)
{
	global $winner, $currentTurn, $CardStats_TimesPlayed, $CardStats_TimesBlocked, $CardStats_TimesPitched, $CardStats_TimesHit, $CardStats_TimesCharged, $firstPlayer, $CardStats_TimesKatsuDiscard;
	global $TurnStats_DamageThreatened, $TurnStats_DamageDealt, $TurnStats_CardsPlayedOffense, $TurnStats_CardsPlayedDefense, $TurnStats_CardsPitched, $TurnStats_CardsBlocked;
	global $TurnStats_ResourcesUsed, $TurnStats_CardsLeft, $TurnStats_DamageBlocked, $TurnStats_ResourcesLeft, $TurnStats_LifeGained;
	global $p1TotalTime, $p2TotalTime, $TurnStats_DamagePrevented;
	if($DeckLink != "") {
		$DeckLink = explode("/", $DeckLink);
		$DeckLink = $DeckLink[count($DeckLink) - 1];
	}
	$deckAfterSB = explode("\r\n", $deckAfterSB);
	if(count($deckAfterSB) == 1) return "";
	$character = $deckAfterSB[0];
	$deckAfterSB = $deckAfterSB[1];
	$deck = [];
	if($gameID != "") $deck["gameId"] = $gameID;
	if($gameName != "") $deck["gameName"] = $gameName;
	$deck["deckId"] = $DeckLink;
	$deck["turns"] = intval($currentTurn);
	$deck["result"] = ($player == $winner ? 1 : 0);
	$deck["firstPlayer"] = ($player == $firstPlayer ? 1 : 0);
	if($opposingHero != "") $deck["opposingHero"] = $opposingHero;
	if($deckbuilderID != "") $deck["deckbuilderID"] = $deckbuilderID;
	$deck["cardResults"] = [];
	$deck["character"] = [];

	$character = explode(" ", $character);
	$deduplicatedCharacter = [];
	for($i = 0; $i < count($character); ++$i) {
		$card = $character[$i];

		if (array_key_exists($card, $deduplicatedCharacter)) {
			$deduplicatedCharacter[$card]++;
		} else {
			$deduplicatedCharacter[$card] = 1;
		}
	}
	$deck["character"] = [];

	foreach ($deduplicatedCharacter as $card => $numCopies) {
		$cardResult = [
			"cardId" => GetNormalCardID($card),
			"cardName" => CardName($card),
			"numCopies" => $numCopies,
		];
		array_push($deck["character"], $cardResult);
	}

	$deckAfterSB = explode(" ", $deckAfterSB);
	$deduplicatedDeck = [];
	for($i = 0; $i < count($deckAfterSB); ++$i) {
		$card = $deckAfterSB[$i];

		if (array_key_exists($card, $deduplicatedDeck)) {
			$deduplicatedDeck[$card]++;
		} else {
			$deduplicatedDeck[$card] = 1;
		}
	}

	foreach ($deduplicatedDeck as $card => $numCopies) {
		$cardResult = [
			"cardId" => GetNormalCardID($card),
			"played" => 0,
			"blocked" => 0,
			"pitched" => 0,
			"hits" => 0,
			"charged" => 0,
			"cardName" => CardName($card),
			"pitchValue" => PitchValue($card),
			"numCopies" => $numCopies,
		];
		array_push($deck["cardResults"], $cardResult);
	}

	$cardStats = &GetCardStats($player);
	for($i = 0; $i < count($cardStats); $i += CardStatPieces()) {
		for($j = 0; $j < count($deck["cardResults"]); ++$j) {
			if($deck["cardResults"][$j]["cardId"] == GetNormalCardID($cardStats[$i])) {
				$deck["cardResults"][$j]["played"] = $cardStats[$i + $CardStats_TimesPlayed];
				$deck["cardResults"][$j]["blocked"] = $cardStats[$i + $CardStats_TimesBlocked];
				$deck["cardResults"][$j]["pitched"] = $cardStats[$i + $CardStats_TimesPitched];
				$deck["cardResults"][$j]["hits"] = $cardStats[$i + $CardStats_TimesHit];
				$deck["cardResults"][$j]["charged"] = $cardStats[$i + $CardStats_TimesCharged];
				$deck["cardResults"][$j]["charged"] = $cardStats[$i + $CardStats_TimesKatsuDiscard];
				break;
			}
		}
	}
	$turnStats = &GetTurnStats($player);
	$otherPlayerTurnStats = &GetTurnStats(($player == 1 ? 2 : 1));
	for($i = 0; $i < count($turnStats); $i += TurnStatPieces()) {
		$deck["turnResults"][$i]["cardsUsed"] = ($turnStats[$i + $TurnStats_CardsPlayedOffense] + $turnStats[$i + $TurnStats_CardsPlayedDefense]);
		$deck["turnResults"][$i]["cardsBlocked"] = $turnStats[$i + $TurnStats_CardsBlocked];
		$deck["turnResults"][$i]["cardsPitched"] = $turnStats[$i + $TurnStats_CardsPitched];
		$deck["turnResults"][$i]["resourcesUsed"] = $turnStats[$i + $TurnStats_ResourcesUsed];
		$deck["turnResults"][$i]["resourcesLeft"] = $turnStats[$i + $TurnStats_ResourcesLeft];
		$deck["turnResults"][$i]["cardsLeft"] = $turnStats[$i + $TurnStats_CardsLeft];
		$deck["turnResults"][$i]["damageThreatened"] = $turnStats[$i + $TurnStats_DamageThreatened];
		$deck["turnResults"][$i]["damageDealt"] = $turnStats[$i + $TurnStats_DamageDealt];
		$deck["turnResults"][$i]["damageBlocked"] = $turnStats[$i + $TurnStats_DamageBlocked];
		$deck["turnResults"][$i]["damagePrevented"] = $turnStats[$i + $TurnStats_DamagePrevented];
		$deck["turnResults"][$i]["damageTaken"] = $otherPlayerTurnStats[$i + $TurnStats_DamageDealt];
		$deck["turnResults"][$i]["lifeGained"] = $turnStats[$i + $TurnStats_LifeGained];
	}

	$time = ($player == 1 ? $p1TotalTime : $p2TotalTime);
	$totalTime = $p1TotalTime + $p2TotalTime;

	$deck["yourTime"] = $time;
	$deck["totalTime"] = $totalTime;

	//Damage stats
	$totalDamageThreatened = 0;
	$totalDamageDealt = 0;
	$totalResourcesUsed = 0;
	$totalCardsLeft = 0;
	$totalDefensiveCards = 0;
	$totalBlocked = 0;
	$totalLifeGained = 0;
	$totalDamagePrevented = 0;
	$numTurns = 0;
	$start = ($player == $firstPlayer ? TurnStatPieces() : 0);
	for($i = $start; $i < count($turnStats); $i += TurnStatPieces()) {
		$totalDamageThreatened += $turnStats[$i + $TurnStats_DamageThreatened];
		$totalDamageDealt += $turnStats[$i + $TurnStats_DamageDealt];
		$totalResourcesUsed += $turnStats[$i + $TurnStats_ResourcesUsed];
		$totalCardsLeft += $turnStats[$i + $TurnStats_CardsLeft];
		$totalDefensiveCards += ($turnStats[$i + $TurnStats_CardsPlayedDefense] + $turnStats[$i + $TurnStats_CardsBlocked]); //TODO: Separate out pitch for offense and defense
		$totalBlocked += $turnStats[$i + $TurnStats_DamageBlocked];
		$totalLifeGained += $turnStats[$i + $TurnStats_LifeGained];
		$totalDamagePrevented += $turnStats[$i + $TurnStats_DamagePrevented];
		++$numTurns;
	}

	if($numTurns < 1) $numTurns = 1;
	$totalOffensiveCards = 4 * $numTurns - $totalDefensiveCards;
	if($totalOffensiveCards == 0) $totalOffensiveCards = 1;

	$deck["totalDamageThreatened"] = $totalDamageThreatened;
	$deck["totalDamageDealt"] = $totalDamageDealt;
	$deck["totalLifeGained"] = $totalLifeGained;
	$deck["totalDamagePrevented"] = $totalDamagePrevented;
	$deck["averageDamageThreatenedPerTurn"] = round($totalDamageThreatened / $numTurns, 2);
	$deck["averageDamageDealtPerTurn"] = round($totalDamageDealt / $numTurns, 2);
	$deck["averageDamageThreatenedPerCard"] = round($totalDamageThreatened / $totalOffensiveCards, 2);
	$deck["averageResourcesUsedPerTurn"] = round($totalResourcesUsed / $numTurns, 2);
	$deck["averageCardsLeftOverPerTurn"] = round($totalCardsLeft / $numTurns, 2);
	$deck["averageCombatValuePerTurn"] = round(($totalDamageThreatened + $totalBlocked) / $numTurns, 2);
	$deck["averageValuePerTurn"] = round(($totalDamageThreatened + $totalBlocked + $totalLifeGained + $totalDamagePrevented) / $numTurns, 2);

	if($includeFullLog) { $deck["fullLog"] = IsPatron($player) ? implode("<BR>", explode("\r\n", @file_get_contents("./Games/" . $gameID . "/fullGamelog.txt"))) : ""; }
	
	return json_encode($deck);
}

function SerializeDetailedGameResult($player, $DeckLink, $deckAfterSB, $gameID = "", $opposingHero = "", $gameName = "", $deckbuilderID = "", $playerHero = "")
{
	global $winner, $currentTurn, $CardStats_TimesPlayed, $CardStats_TimesBlocked, $CardStats_TimesPitched, $CardStats_TimesHit, $CardStats_TimesCharged, $firstPlayer, $CardStats_TimesKatsuDiscard;
	global $TurnStats_DamageThreatened, $TurnStats_DamageDealt, $TurnStats_CardsPlayedOffense, $TurnStats_CardsPlayedDefense, $TurnStats_CardsPitched, $TurnStats_CardsBlocked;
	global $TurnStats_ResourcesUsed, $TurnStats_CardsLeft, $TurnStats_DamageBlocked, $TurnStats_ResourcesLeft, $TurnStats_LifeGained;
	global $p1TotalTime, $p2TotalTime, $TurnStats_DamagePrevented;
	if($DeckLink != "") {
		$DeckLink = explode("/", $DeckLink);
		$DeckLink = $DeckLink[count($DeckLink) - 1];
	}
	$deckAfterSB = explode("\r\n", $deckAfterSB);
	if(count($deckAfterSB) == 1) return "";
	$character = $deckAfterSB[0];
	$deckAfterSB = $deckAfterSB[1];
	$deck = [];
	if($gameID != "") $deck["gameId"] = $gameID;
	if($gameName != "") $deck["gameName"] = $gameName;
	$deck["deckId"] = $DeckLink;
	$deck["turns"] = intval($currentTurn);
	$deck["result"] = ($player == $winner ? 1 : 0);
	$deck["firstPlayer"] = ($player == $firstPlayer ? 1 : 0);
	if($opposingHero != "") $deck["opposingHero"] = $opposingHero;
	if($playerHero != "") $deck["playerHero"] = $playerHero;
	if($deckbuilderID != "") $deck["deckbuilderID"] = $deckbuilderID;
	$deck["cardResults"] = [];
	$deck["character"] = [];

	$character = explode(" ", $character);
	$deduplicatedCharacter = [];
	for($i = 0; $i < count($character); ++$i) {
		$card = $character[$i];

		if (array_key_exists($card, $deduplicatedCharacter)) {
			$deduplicatedCharacter[$card]++;
		} else {
			$deduplicatedCharacter[$card] = 1;
		}
	}
	$deck["character"] = [];

	foreach ($deduplicatedCharacter as $card => $numCopies) {
		$cardResult = [
			"cardId" => GetNormalCardID($card),
			"cardName" => CardName($card),
			"numCopies" => $numCopies,
		];
		array_push($deck["character"], $cardResult);
	}

	$deckAfterSB = explode(" ", $deckAfterSB);
	$deduplicatedDeck = [];
	for($i = 0; $i < count($deckAfterSB); ++$i) {
		$card = $deckAfterSB[$i];

		if (array_key_exists($card, $deduplicatedDeck)) {
			$deduplicatedDeck[$card]++;
		} else {
			$deduplicatedDeck[$card] = 1;
		}
	}

	foreach ($deduplicatedDeck as $card => $numCopies) {
		$cardResult = [
			"cardId" => GetNormalCardID($card),
			"played" => 0,
			"blocked" => 0,
			"pitched" => 0,
			"hits" => 0,
			"charged" => 0,
			"cardName" => CardName($card),
			"pitchValue" => PitchValue($card),
			"numCopies" => $numCopies,
		];
		array_push($deck["cardResults"], $cardResult);
	}

	$cardStats = &GetCardStats($player);
	for($i = 0; $i < count($cardStats); $i += CardStatPieces()) {
		for($j = 0; $j < count($deck["cardResults"]); ++$j) {
			if($deck["cardResults"][$j]["cardId"] == GetNormalCardID($cardStats[$i])) {
				$deck["cardResults"][$j]["played"] = intval($cardStats[$i + $CardStats_TimesPlayed]);
				$deck["cardResults"][$j]["blocked"] = intval($cardStats[$i + $CardStats_TimesBlocked]);
				$deck["cardResults"][$j]["pitched"] = intval($cardStats[$i + $CardStats_TimesPitched]);
				$deck["cardResults"][$j]["hits"] = intval($cardStats[$i + $CardStats_TimesHit]);
				$deck["cardResults"][$j]["charged"] = intval($cardStats[$i + $CardStats_TimesCharged]);
				$deck["cardResults"][$j]["charged"] = intval($cardStats[$i + $CardStats_TimesKatsuDiscard]);
				break;
			}
		}
	}
	$turnStats = &GetTurnStats($player);
	$otherPlayerTurnStats = &GetTurnStats(($player == 1 ? 2 : 1));
	for($i = 0; $i < count($turnStats); $i += TurnStatPieces()) {
		$deck["turnResults"][$i]["cardsUsed"] = intval($turnStats[$i + $TurnStats_CardsPlayedOffense] + $turnStats[$i + $TurnStats_CardsPlayedDefense]);
		$deck["turnResults"][$i]["cardsBlocked"] = intval($turnStats[$i + $TurnStats_CardsBlocked]);
		$deck["turnResults"][$i]["cardsPitched"] = intval($turnStats[$i + $TurnStats_CardsPitched]);
		$deck["turnResults"][$i]["resourcesUsed"] = intval($turnStats[$i + $TurnStats_ResourcesUsed]);
		$deck["turnResults"][$i]["resourcesLeft"] = intval($turnStats[$i + $TurnStats_ResourcesLeft]);
		$deck["turnResults"][$i]["cardsLeft"] = intval($turnStats[$i + $TurnStats_CardsLeft]);
		$deck["turnResults"][$i]["damageThreatened"] = intval($turnStats[$i + $TurnStats_DamageThreatened]);
		$deck["turnResults"][$i]["damageDealt"] = intval($turnStats[$i + $TurnStats_DamageDealt]);
		$deck["turnResults"][$i]["damageBlocked"] = intval($turnStats[$i + $TurnStats_DamageBlocked]);
		$deck["turnResults"][$i]["damagePrevented"] = intval($turnStats[$i + $TurnStats_DamagePrevented]);
		$deck["turnResults"][$i]["damageTaken"] = intval($otherPlayerTurnStats[$i + $TurnStats_DamageDealt]);
		$deck["turnResults"][$i]["lifeGained"] = intval($turnStats[$i + $TurnStats_LifeGained]);
	}

	$time = ($player == 1 ? $p1TotalTime : $p2TotalTime);
	$totalTime = $p1TotalTime + $p2TotalTime;

	$deck["yourTime"] = $time;
	$deck["totalTime"] = $totalTime;

	//Damage stats
	$totalDamageThreatened = 0;
	$totalDamageDealt = 0;
	$totalResourcesUsed = 0;
	$totalCardsLeft = 0;
	$totalDefensiveCards = 0;
	$totalBlocked = 0;
	$totalLifeGained = 0;
	$totalDamagePrevented = 0;
	$numTurns = 0;
	$start = ($player == $firstPlayer ? TurnStatPieces() : 0);
	for($i = $start; $i < count($turnStats); $i += TurnStatPieces()) {
		$totalDamageThreatened += $turnStats[$i + $TurnStats_DamageThreatened];
		$totalDamageDealt += $turnStats[$i + $TurnStats_DamageDealt];
		$totalResourcesUsed += $turnStats[$i + $TurnStats_ResourcesUsed];
		$totalCardsLeft += $turnStats[$i + $TurnStats_CardsLeft];
		$totalDefensiveCards += ($turnStats[$i + $TurnStats_CardsPlayedDefense] + $turnStats[$i + $TurnStats_CardsBlocked]); //TODO: Separate out pitch for offense and defense
		$totalBlocked += $turnStats[$i + $TurnStats_DamageBlocked];
		$totalLifeGained += $turnStats[$i + $TurnStats_LifeGained];
		$totalDamagePrevented += $turnStats[$i + $TurnStats_DamagePrevented];
		++$numTurns;
	}

	if($numTurns < 1) $numTurns = 1;
	$totalOffensiveCards = 4 * $numTurns - $totalDefensiveCards;
	if($totalOffensiveCards == 0) $totalOffensiveCards = 1;

	$deck["totalDamageThreatened"] = $totalDamageThreatened;
	$deck["totalDamageDealt"] = $totalDamageDealt;
	$deck["totalLifeGained"] = $totalLifeGained;
	$deck["totalDamagePrevented"] = $totalDamagePrevented;
	$deck["averageDamageThreatenedPerTurn"] = round($totalDamageThreatened / $numTurns, 2);
	$deck["averageDamageDealtPerTurn"] = round($totalDamageDealt / $numTurns, 2);
	$deck["averageDamageThreatenedPerCard"] = round($totalDamageThreatened / $totalOffensiveCards, 2);
	$deck["averageResourcesUsedPerTurn"] = round($totalResourcesUsed / $numTurns, 2);
	$deck["averageCardsLeftOverPerTurn"] = round($totalCardsLeft / $numTurns, 2);
	$deck["averageCombatValuePerTurn"] = round(($totalDamageThreatened + $totalBlocked) / $numTurns, 2);
	$deck["averageValuePerTurn"] = round(($totalDamageThreatened + $totalBlocked + $totalLifeGained + $totalDamagePrevented) / $numTurns, 2);

	if($includeFullLog) { $deck["fullLog"] = IsPatron($player) ? implode("<BR>", explode("\r\n", @file_get_contents("./Games/" . $gameID . "/fullGamelog.txt"))) : ""; }
	
	return json_encode($deck);
}

function GetNormalCardID($cardID)
{
	switch ($cardID) {
		case "MON405": return "BOL002";
		case "MON400": return "BOL006";
		case "MON407": return "CHN002";
		case "MON401": return "CHN006";
		case "MON406": return "LEV002";
		case "MON400": return "LEV005";
		case "MON404": return "PSM002";
		case "MON402": return "PSM007";
		case "HNT407": return "ARK007";
	}
	return $cardID;
}

function SavePatreonTokens($accessToken, $refreshToken)
{
	if(!isset($_SESSION["userid"])) return;
	$userID = $_SESSION["userid"];
	$conn = GetDBConnection();
	$sql = "UPDATE users SET patreonAccessToken=?, patreonRefreshToken=? WHERE usersid=?";
	$stmt = mysqli_stmt_init($conn);
	if(mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "sss", $accessToken, $refreshToken, $userID);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	}
	mysqli_close($conn);
}

function LoadBadges($userID)
{
	return [];//Return empty until inefficient join can be fixed with index
	if($userID == "") return "";
	$conn = GetDBConnection();
	$sql = "SELECT pb.playerId,pb.badgeId,pb.intVariable,bs.topText,bs.bottomText,bs.image,bs.link FROM playerbadge pb join badges bs on bs.badgeId = pb.badgeId WHERE pb.playerId = ?;";
	$stmt = mysqli_stmt_init($conn);
	$output = [];
	if(mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "s", $userID);
		mysqli_stmt_execute($stmt);
		$data = mysqli_stmt_get_result($stmt);
		while($row = mysqli_fetch_array($data, MYSQLI_NUM)) {
			for($i = 0; $i < 7; ++$i) array_push($output, $row[$i]);
		}
		mysqli_stmt_close($stmt);
	}
	mysqli_close($conn);
	return $output;
}

function GetMyAwardableBadges($userID)
{
	if($userID == "") return "";
	$output = [];
	$conn = GetDBConnection();
	$sql = "select * from userassignablebadge where playerId=?";
	$stmt = mysqli_stmt_init($conn);
	if(mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "s", $userID);
		mysqli_stmt_execute($stmt);
		$data = mysqli_stmt_get_result($stmt);
		while($row = mysqli_fetch_array($data, MYSQLI_NUM)) {
			array_push($output, $row[0]);
		}
		mysqli_stmt_close($stmt);
	}
	mysqli_close($conn);
	return $output;
}

function AwardBadge($userID, $badgeID)
{
	if($userID == "") return "";
	$conn = GetDBConnection();
	$sql = "insert into playerbadge (playerId, badgeId, intVariable) values (?, ?, 1) ON DUPLICATE KEY UPDATE intVariable = intVariable + 1;";
	$stmt = mysqli_stmt_init($conn);
	if(mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "ss", $userID, $badgeID);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	}
	mysqli_close($conn);
}

function SaveSetting($playerId, $settingNumber, $value)
{
	if($playerId == "") return;
	$conn = GetDBConnection();
	$sql = "insert into savedsettings (playerId, settingNumber, settingValue) values (?, ?, ?) ON DUPLICATE KEY UPDATE settingValue = VALUES(settingValue);";
	$stmt = mysqli_stmt_init($conn);
	if(mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "sss", $playerId, $settingNumber, $value);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	}
	mysqli_close($conn);
}

function LoadSavedSettings($playerId)
{
	if($playerId == "") return [];
	$output = [];
	$conn = GetDBConnection();
	$sql = "select settingNumber,settingValue from `savedsettings` where playerId=(?)";
	$stmt = mysqli_stmt_init($conn);
	if(mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "s", $playerId);
		mysqli_stmt_execute($stmt);
		$data = mysqli_stmt_get_result($stmt);
		while($row = mysqli_fetch_array($data, MYSQLI_NUM)) {
			array_push($output, $row[0]);
			array_push($output, $row[1]);
		}
		mysqli_stmt_close($stmt);
	}
	mysqli_close($conn);
	return $output;
}

function SendEmail($userEmail, $url)
{
	include "../APIKeys/APIKeys.php";
	require '../vendor/autoload.php';

	$email = new Mail();
	$email->setFrom("no-reply@fleshandbloodonline.com", "No-Reply");
	$email->setSubject("Talishar Password Reset Link");
	$email->addTo($userEmail);
	$email->addContent(
		"text/html",
		"
        <p>
          We recieved a password reset request. The link to reset your password is below.
          If you did not make this request, you can ignore this email
        </p>
        <p>
          Here is your password reset link: </br>
          <a href=$url>Password Reset</a>
        </p>
      "
	);
	$sendgrid = new \SendGrid($sendgridKey);
	try {
		$response = $sendgrid->send($email);
		print $response->statusCode() . "\n";
		print_r($response->headers());
		print $response->body() . "\n";
	} catch (Exception $e) {
		echo 'Caught exception: ' . $e->getMessage() . "\n";
	}
}

function SendEmailAPI($userEmail, $url)
{
	include "../APIKeys/APIKeys.php";
	require '../vendor/autoload.php';

	$email = new Mail();
	$email->setFrom("no-reply@talishar.net", "No-Reply");
	$email->setSubject("Talishar Password Reset Link");
	$email->addTo($userEmail);
	$email->addContent(
		"text/html",
		"
        <p>
          We recieved a password reset request. The link to reset your password is below.
          If you did not make this request, you can ignore this email
        </p>
        <p>
          Here is your password reset link: </br>
          <a href=$url>Password Reset</a>
        </p>
      "
	);
	$sendgrid = new \SendGrid($sendgridKey);
	try {
		$response = $sendgrid->send($email);
	} catch (Exception $e) {
		echo 'Caught exception: ' . $e->getMessage() . "\n";
	}
}

function BanPlayer($uid)
{
	$conn = GetDBConnection();
	$sql = "UPDATE users SET isBanned = true WHERE usersUid = ?";
	$stmt = mysqli_stmt_init($conn);
	if (mysqli_stmt_prepare($stmt, $sql)) {
		mysqli_stmt_bind_param($stmt, "s", $uid);
		mysqli_stmt_execute($stmt);
		mysqli_stmt_close($stmt);
	}
}
