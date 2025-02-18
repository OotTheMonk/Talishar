<?php

include 'Libraries/HTTPLibraries.php';
include "HostFiles/Redirector.php";
include "Libraries/SHMOPLibraries.php";
include "WriteLog.php";
include_once "./Assets/patreon-php-master/src/PatreonDictionary.php";
include_once "./AccountFiles/AccountSessionAPI.php";

// array holding allowed Origin domains
SetHeaders();

header('Content-Type: application/json; charset=utf-8');
$response = new stdClass();

//We should always have a player ID as a URL parameter
$gameName = $_GET["gameName"];
if (!IsGameNameValid($gameName)) {
  $response->errorMessage = ("Invalid game name.");
  echo (json_encode($response));
  exit;
}

$playerID = TryGet("playerID", 3);
if (!is_numeric($playerID)) {
  $response->errorMessage = ("Invalid player ID.");
  echo (json_encode($response));
  exit;
}

if ($playerID == 3 && GetCachePiece($gameName, 9) != "1") {
  header('HTTP/1.0 403 Forbidden');
  exit;
}

$authKey = TryGet("authKey", "");
$lastUpdate = intval(TryGet("lastUpdate", 0));

if (($playerID == 1 || $playerID == 2) && $authKey == "") {
  if (isset($_COOKIE["lastAuthKey"])) $authKey = $_COOKIE["lastAuthKey"];
}

$isGamePlayer = $playerID == 1 || $playerID == 2;
$opponentDisconnected = false;
$opponentInactive = false;

$currentTime = round(microtime(true) * 1000);
if ($isGamePlayer) {
  $playerStatus = intval(GetCachePiece($gameName, $playerID + 3));
  if ($playerStatus == "-1") WriteLog("🔌Player $playerID has connected.");
  SetCachePiece($gameName, $playerID + 1, $currentTime);
  SetCachePiece($gameName, $playerID + 3, "0");
  if ($playerStatus > 0) {
    WriteLog("🔌Player $playerID has reconnected.");
    SetCachePiece($gameName, $playerID + 3, "0");
  }
}
$count = 0;
$cacheVal = intval(GetCachePiece($gameName, 1));

while ($lastUpdate != 0 && $cacheVal <= $lastUpdate) {
  usleep(100000); //100 milliseconds
  if (!file_exists("./Games/" . $gameName . "/GameFile.txt")) break;
  $currentTime = round(microtime(true) * 1000);
  $cacheVal = GetCachePiece($gameName, 1);
  if ($isGamePlayer) {
    SetCachePiece($gameName, $playerID + 1, $currentTime);
    $otherPlayer = ($playerID == 1 ? 2 : 1);
    $oppLastTime = intval(GetCachePiece($gameName, $otherPlayer + 1));
    $oppStatus = GetCachePiece($gameName, $otherPlayer + 3);
    if (($currentTime - $oppLastTime) > 3000 && (intval($oppStatus) == 0)) {
      WriteLog("🔌Opponent has disconnected. Waiting 60 seconds to reconnect.");
      GamestateUpdated($gameName);
      SetCachePiece($gameName, $otherPlayer + 3, "1");
    } else if (($currentTime - $oppLastTime) > 60000 && $oppStatus == "1") {
      WriteLog("Opponent has left the game.");
      GamestateUpdated($gameName);
      SetCachePiece($gameName, $otherPlayer + 3, "2");
      $lastUpdate = 0;
      $opponentDisconnected = true;
    }
    //Handle server timeout
    $lastUpdateTime = GetCachePiece($gameName, 6);
    if ($currentTime - $lastUpdateTime > 60000 && GetCachePiece($gameName, 12) != "1") //60 seconds
    {
      SetCachePiece($gameName, 12, "1");
      $opponentInactive = true;
      $lastUpdate = 0;
    }
  }
  ++$count;
  if ($count == 100) break;
}

if($count == 100) $lastUpdate = 0;//If we waited the full 10 seconds with nothing happening, send back an update in case it got stuck

if($lastUpdate == 0) {
  $lastUpdateTime = GetCachePiece($gameName, 6);
  if($lastUpdateTime == "") { echo("The game no longer exists on the server."); exit; }
  if($currentTime - $lastUpdateTime > 60000 && GetCachePiece($gameName, 12) == "1") //60 seconds
  {
    $opponentInactive = true;
  }
}

if ($lastUpdate != 0 && $cacheVal <= $lastUpdate) {
  echo "0";
  exit;
} else {
  //First we need to parse the game state from the file
  include "ParseGamestate.php";
  include 'GameLogic.php';
  include "GameTerms.php";
  include "Libraries/UILibraries.php";
  include "Libraries/StatFunctions.php";
  include "Libraries/PlayerSettings.php";

  $isReactFE = true;

  if ($opponentDisconnected && !IsGameOver()) {
    include_once "./includes/dbh.inc.php";
    include_once "./includes/functions.inc.php";
    PlayerLoseHealth($otherPlayer, GetHealth($otherPlayer));
    include "WriteGamestate.php";
  } else if ($currentPlayerActivity != 2 && $opponentInactive && !IsGameOver() ) {
    $currentPlayerActivity = 2;
    WriteLog("⌛The current player is inactive.");
    include "WriteGamestate.php";
    GamestateUpdated($gameName);
  }

  if ($turn[0] == "REMATCH") {
    include "MenuFiles/ParseGamefile.php";
    include "MenuFiles/WriteGamefile.php";
    if ($gameStatus == $MGS_GameStarted) {
      include "AI/CombatDummy.php";
      $origDeck = "./Games/" . $gameName . "/p1DeckOrig.txt";
      if (file_exists($origDeck)) copy($origDeck, "./Games/" . $gameName . "/p1Deck.txt");
      $origDeck = "./Games/" . $gameName . "/p2DeckOrig.txt";
      if (file_exists($origDeck)) copy($origDeck, "./Games/" . $gameName . "/p2Deck.txt");
      $gameStatus = (IsPlayerAI(2) ? $MGS_ReadyToStart : $MGS_ChooseFirstPlayer);
      SetCachePiece($gameName, 14, $gameStatus);
      $firstPlayer = 1;
      $firstPlayerChooser = ($winner == 1 ? 2 : 1);
      unlink("./Games/" . $gameName . "/gamestate.txt");

      $errorFileName = "./BugReports/CreateGameFailsafe.txt";
      $errorHandler = fopen($errorFileName, "a");
      date_default_timezone_set('America/Chicago');
      $errorDate = date('m/d/Y h:i:s a');
      $errorOutput = "Rematch failsafe hit for game $gameName at $errorDate";
      fwrite($errorHandler, $errorOutput . "\r\n");
      fclose($errorHandler);

      WriteLog("Player $firstPlayerChooser lost and will choose first player for the rematch.");
    }
    $format = is_numeric($format) ? FormatName($format) : $format; // the frontend expects the name of the format
    WriteGameFile();
    $currentTime = round(microtime(true) * 1000);
    SetCachePiece($gameName, 2, $currentTime);
    SetCachePiece($gameName, 3, $currentTime);
    $response->errorMessage = "1234REMATCH";
    echo (json_encode($response));
    exit;
  }

  $response->lastUpdate = $cacheVal;

  $targetAuth = ($playerID == 1 ? $p1Key : $p2Key);
  if ($playerID != 3 && $authKey != $targetAuth) {
    $response->errorMessage = "Invalid Authkey";
    echo (json_encode($response));
    exit;
  }

  if (count($turn) == 0) {
    RevertGamestate();
    GamestateUpdated($gameName);
    exit();
  }

  // send initial on-load information if our first time connecting.
  if ($lastUpdate == 0) {
    include "MenuFiles/ParseGamefile.php";
    $initialLoad = new stdClass();
    $initialLoad->playerName = $playerID == 1 ? $p1uid : $p2uid;
    $initialLoad->opponentName = $playerID == 1 ? $p2uid : $p1uid;
    $contributors = array("sugitime", "OotTheMonk", "Launch", "LaustinSpayce", "Star_Seraph", "Tower", "Etasus", "scary987", "Celenar", "DKGaming");
    $initialLoad->playerIsPatron = ($playerID == 1 ? $p1IsPatron : $p2IsPatron);
    $initialLoad->playerIsContributor = in_array($initialLoad->playerName, $contributors);
    $initialLoad->opponentIsPatron = ($playerID == 1 ? $p2IsPatron : $p1IsPatron);
    $initialLoad->opponentIsContributor = in_array($initialLoad->opponentName, $contributors);
    $initialLoad->roguelikeGameID = $roguelikeGameID;
    $initialLoad->playerIsPvtVoidPatron = $initialLoad->playerName == "PvtVoid" || ($playerID == 1 && isset($_SESSION["isPvtVoidPatron"]));
    $initialLoad->opponentIsPvtVoidPatron = $initialLoad->opponentName == "PvtVoid" || ($playerID == 2 && isset($_SESSION["isPvtVoidPatron"]));

    $initialLoad->altArts = [];

    //Get Alt arts
    if(!AltArtsDisabled($playerID))
    {
      foreach(PatreonCampaign::cases() as $campaign) {
        if(isset($_SESSION[$campaign->SessionID()]) || (IsUserLoggedIn() && $campaign->IsTeamMember(LoggedInUserName()))) {
          $altArts = $campaign->AltArts();
          if($altArts == "") continue;
          $altArts = explode(",", $altArts);
          for($i = 0; $i < count($altArts); ++$i) {
            $arr = explode("=", $altArts[$i]);
            $altArt = new stdClass();
            $altArt->name = $campaign->CampaignName() . (count($altArts) > 1 ? " " . $i + 1 : "");
            $altArt->cardId = $arr[0];
            $altArt->altPath = $arr[1];
            array_push($initialLoad->altArts, $altArt);
          }
        }
      }
    }
    $response->initialLoad = $initialLoad;
  }

  $blankZone = 'blankZone';
  $otherPlayer = ($playerID == 1 ? 2 : 1);

  //Choose Cardback
  $MyCardBack = GetCardBack($playerID);
  $TheirCardBack = GetCardBack($otherPlayer);
  $borderColor = 0;

  $response->MyPlaymat = (IsColorblindMode($playerID) ? 0 : GetPlaymat($playerID));
  $response->TheirPlaymat = (IsColorblindMode($playerID) ? 0 : GetPlaymat($otherPlayer));
  if ($response->MyPlaymat == 0) $response->TheirPlaymat = 0;

  //Display active chain link
  $activeChainLink = new stdClass();
  $combatChainReactions = array();
  for ($i = 0; $i < count($combatChain); $i += CombatChainPieces()) {
    // vars for active chain link: Is there an action?
    $action = $currentPlayer == $playerID && $turn[0] != "P" && 
      $currentPlayer == $combatChain[$i + 1] &&
      AbilityPlayableFromCombatChain($combatChain[$i]) &&
      IsPlayable($combatChain[$i], $turn[0], "PLAY", $i) ? 21 : 0;

    $borderColor = $action == 21 ? 1 : null;
    $countersMap = new stdClass();
    if (HasAimCounter() && $i == 0) $countersMap->aim = 1;

    if ($i == 0) {
      $activeChainLink->attackingCard = JSONRenderedCard(
        cardNumber: $combatChain[$i],
        controller: $combatChain[$i + 1],
        action: $action,
        actionDataOverride: '0',
        borderColor: $borderColor,
        countersMap: $countersMap,
      );
      continue;
    }
    array_push($combatChainReactions, JSONRenderedCard(
      cardNumber: $combatChain[$i],
      controller: isset($combatChain[$i + 1]) ? $combatChain[$i + 1] : NULL,
      action: $action,
      actionDataOverride: strval($i),
      borderColor: $borderColor,
      countersMap: $countersMap,
    ));
  }
  $activeChainLink->reactions = $combatChainReactions;
  $activeChainLink->attackTarget = CardName(GetMZCard($mainPlayer, GetAttackTarget()));
  $activeChainLink->damagePrevention = GetDamagePrevention($defPlayer);
  $activeChainLink->goAgain = DoesAttackHaveGoAgain();
  $activeChainLink->dominate = CachedDominateActive();
  $activeChainLink->overpower = CachedOverpowerActive();
  if ($combatChainState[$CCS_RequiredEquipmentBlock] > NumEquipBlock()) $activeChainLink->numRequiredEquipBlock = $combatChainState[$CCS_RequiredEquipmentBlock];
  elseif ($combatChainState[$CCS_RequiredNegCounterEquipmentBlock] > NumNegCounterEquipBlock()) $activeChainLink->numRequiredEquipBlock = $combatChainState[$CCS_RequiredNegCounterEquipmentBlock];
  $activeChainLink->wager = CachedWagerActive();
  $activeChainLink->phantasm = CachedPhantasmActive();
  $activeChainLink->fusion = CachedFusionActive();
  if ($CombatChain->HasCurrentLink()) $activeChainLink->tower = IsTowerActive();
  if ($CombatChain->HasCurrentLink()) $activeChainLink->piercing = IsPiercingActive($combatChain[0]);
  if ($CombatChain->HasCurrentLink()) $activeChainLink->combo = ComboActive();

  $activeChainLink->fused = false;

  // current chain link attack
  $totalAttack = 0;
  $totalDefense = 0;
  if (count($combatChain) > 0) {
    $chainAttackModifiers = [];
    EvaluateCombatChain($totalAttack, $totalDefense, $chainAttackModifiers);
  }
  $activeChainLink->totalAttack = $totalAttack;

  // current chain link defence
  $activeChainLink->totalDefence = $totalDefense;

  $response->activeChainLink = $activeChainLink;

  //Tracker state
  $tracker = new stdClass();
  $tracker->color = ($playerID == $currentPlayer ? "blue" : "red");
  if ($turn[0] == "B" || (count($layers) > 0 && $layers[0] == "DEFENDSTEP")) $tracker->position = "Defense";
  else if ($turn[0] == "A" || $turn[0] == "D") $tracker->position = "Reactions";
  else if ($turn[0] == "PDECK" || $turn[0] == "ARS" || (count($layers) > 0 && ($layers[0] == "ENDTURN" || $layers[0] == "FINALIZECHAINLINK"))) $tracker->position = "EndTurn";
  else if (count($chainLinks) > 0 || (count($layers) > 0 && $layers[0] == "ATTACKSTEP")) $tracker->position = "Combat";
  else $tracker->position = "Main";
  $response->tracker = $tracker;

  //Display layer
  $layerObject = new stdClass;
  $layerContents = array();
  for ($i = count($layers) - LayerPieces(); $i >= 0; $i -= LayerPieces()) {
    $layerName = ($layers[$i] == "LAYER" || $layers[$i] == "TRIGGER" || $layers[$i] == "MELD" ? $layers[$i + 2] : $layers[$i]);
    array_push($layerContents, JSONRenderedCard(cardNumber: $layerName, controller: $layers[$i + 1]));
  }
  $reorderableLayers = array();
  $numReorderable = 0;
  for ($i = count($layers) - LayerPieces(); $i >= 0; $i -= LayerPieces()) {
    $layer = new stdClass();
    $layerName = ($layers[$i] == "LAYER" || $layers[$i] == "TRIGGER" || $layers[$i] == "MELD" ? $layers[$i + 2] : $layers[$i]);
    $layer->card = JSONRenderedCard(cardNumber: $layerName, controller: $layers[$i + 1], lightningPlayed:"SKIP");
    $layer->layerID = $i;
    $layer->isReorderable = $playerID == $mainPlayer && $i <= $dqState[8] && ($i > 0 || $numReorderable > 0);
    if ($layer->isReorderable) ++$numReorderable;
    array_push($reorderableLayers, $layer);
  }
  $layerObject->target = GetRelativeMZCardLink($mainPlayer, GetAttackTarget());
  $layerObject->layerContents = $layerContents;
  $layerObject->reorderableLayers = $reorderableLayers;
  $response->layerDisplay = $layerObject;

  // their hand contents
  $theirHandContents = array();
  for ($i=0; $i < count($theirBanish); $i += BanishPieces()) {
    if (PlayableFromBanish($theirBanish[$i], $theirBanish[$i+1], player:$otherPlayer) && $theirBanish[$i+1] != "TRAPDOOR") {
      array_push($theirHandContents, JSONRenderedCard($theirBanish[$i], borderColor:7));
    }
  }
  for ($i=0; $i < count($myBanish); $i += BanishPieces()) {
    if(PlayableFromOtherPlayerBanish($myBanish[$i], $myBanish[$i+1], $otherPlayer)) {
      array_push($theirHandContents, JSONRenderedCard($myBanish[$i], borderColor:7));
    }
  }
  for ($i = 0; $i < count($theirHand); ++$i) {
    if($playerID == 3 && IsCasterMode() || IsGameOver()) array_push($theirHandContents, JSONRenderedCard($theirHand[$i]));
    else array_push($theirHandContents, JSONRenderedCard($TheirCardBack));
  }

  $response->opponentHand = $theirHandContents;

  //Their life
  $response->opponentHealth = $theirHealth;
  //Their soul count
  $response->opponentSoulCount = count($theirSoul);

  //Display their discard, pitch, deck, and banish
  $opponentDiscardArray = array();
  for ($i = 0; $i < count($theirDiscard); $i += DiscardPieces()) {
    array_push($opponentDiscardArray, JSONRenderedCard($theirDiscard[$i]));
  }
  $response->opponentDiscard = $opponentDiscardArray;

  $response->opponentPitchCount = $theirResources[0];
  $opponentPitchArr = array();
  for ($i = count($theirPitch) - PitchPieces(); $i >= 0; $i -= PitchPieces()) {
    if($turn[0] != "PDECK") array_push($opponentPitchArr, JSONRenderedCard($theirPitch[$i]));
    else array_push($opponentPitchArr, JSONRenderedCard($TheirCardBack));
  }
  $response->opponentPitch = $opponentPitchArr;

  $response->opponentDeckCount = count($theirDeck);
  $response->opponentDeckCard = JSONRenderedCard(count($theirDeck) > 0 ? $TheirCardBack : $blankZone);
  $opponentDeckArr = array();
  if(IsGameOver()) {
    for($i=0; $i<count($theirDeck); $i+=DeckPieces()) {
      array_push($opponentDeckArr, JSONRenderedCard($theirDeck[$i]));
    }
  }
  $theirBlessingsCount = SearchCount(SearchDiscardForCard($otherPlayer, "count_your_blessings_red", "count_your_blessings_yellow", "count_your_blessings_blue"));
  if ($theirBlessingsCount > 0) {
    $response->opponentBlessingsCount = $theirBlessingsCount;
  }
  $response->opponentDeck = $opponentDeckArr;

  $response->opponentCardBack = JSONRenderedCard($TheirCardBack);

  //Their Banish
  $opponentBanishArr = array();
  for ($i = 0; $i < count($theirBanish); $i += BanishPieces()) {
    $overlay = 0;
    $border = 0;
    $cardID = $theirBanish[$i];
    $mod = explode("-", $theirBanish[$i + 1])[0];
    $action = $currentPlayer == $playerID && IsPlayable($theirBanish[$i], $turn[0], "THEIRBANISH", $i) ? 15 : 0;
    if (isFaceDownMod($mod)) {
      $cardID = "CardBack";
    }
    else $border = CardBorderColor($theirBanish[$i], "BANISH", $action > 0, $mod);

    array_push($opponentBanishArr, JSONRenderedCard($cardID, $action, $overlay, borderColor: $border, actionDataOverride: strval($i)));
  }
  $response->opponentBanish = $opponentBanishArr;
  if (TalentContains($theirCharacter[0], "SHADOW")) {
    $response->opponentBloodDebtCount = SearchCount(SearchBanish($otherPlayer, "", "", -1, -1, "", "", true));
    $response->isOpponentBloodDebtImmune = IsImmuneToBloodDebt($otherPlayer);
  }
  if (HasEssenceOfEarth($theirCharacter[0])) {
    $response->opponentEarthCount = SearchCount(SearchBanish($otherPlayer, talent:"EARTH"));
  }

  //Now display their character and equipment
  $numWeapons = 0;
  $characterContents = array();
  for ($i = 0; $i < count($theirCharacter); $i += CharacterPieces()) {
    $label = "";
    $border = 0;
    $theirChar = $theirCharacter[$i];
    if ($theirCharacter[$i + 1] == 4) $theirChar = "DUMMYDISHONORED";
    $atkCounters = 0;
    $counters = 0;
    $type = CardType($theirChar);
    if (TypeContains($theirChar, "D")) $type = "C";
    $sTypeArr = explode(",", CardSubType($theirChar, $theirCharacter[$i+11]));
    $sType = $sTypeArr[0];
    for($j=0; $j<count($sTypeArr); ++$j) {
      if($sTypeArr[$j] == "Head" || $sTypeArr[$j] == "Chest" || $sTypeArr[$j] == "Arms" || $sTypeArr[$j] == "Legs") {
        $sType = $sTypeArr[$j];
        break;
      }
    }
    if (TypeContains($theirCharacter[$i], "W", $playerID)) {
      ++$numWeapons;
      if ($numWeapons > 1) {
        $type = "E";
        $sType = "Off-Hand";
      }
      $label = WeaponHasGoAgainLabel($i, $otherPlayer) ? "Go Again" : "";
      $weaponAttackModifiers = [];
      $atkCounters = $theirCharacter[$i + 3];
      if(MainCharacterAttackModifiers($weaponAttackModifiers, $i, true, $otherPlayer) > 0) $border = 5;
    }
    if ($theirCharacter[$i + 2] > 0) $counters = $theirCharacter[$i + 2];
    $counters = $theirCharacter[$i + 1] != 0 ? $counters : 0;
    if(IsGameOver()) $theirCharacter[$i + 12] = "UP";
    if ($theirCharacter[$i + 12] == "UP" || $playerID == 3 && IsCasterMode() || IsGameOver()) {
      if($theirCharacter[$i + 1] > 0) { //Don't show broken equipment cards as they are in the graveyard
      array_push($characterContents, JSONRenderedCard(
        $theirChar,
        borderColor: $border,
        overlay: ($theirCharacter[$i + 1] != 2 ? 1 : 0),
        counters: $counters,
        defCounters: $theirCharacter[$i + 4],
        atkCounters: $atkCounters,
        controller: $otherPlayer,
        type: $type,
        sType: $sType,
        isFrozen: ($theirCharacter[$i + 8] == 1),
        onChain: ($theirCharacter[$i + 6] == 1),
        isBroken: ($theirCharacter[$i + 1] == 0),
        label: $label,
        facing: $theirCharacter[$i + 12],
        numUses: $theirCharacter[$i + 5],
        subcard: isSubcardEmpty($theirCharacter, $i) ? NULL : $theirCharacter[$i+10],
        marked: $theirCharacter[$i + 13] == 1
        ));
      }
    } else {
      array_push($characterContents, JSONRenderedCard(
          $TheirCardBack,
          overlay: ($theirCharacter[$i + 1] != 2 ? 1 : 0),
          counters: $counters,
          defCounters: $theirCharacter[$i + 4],
          atkCounters: $atkCounters,
          controller: $otherPlayer,
          type: $type,
          sType: $sType,
          label: $label,
          facing: $theirCharacter[$i + 12],
          subcard: isSubcardEmpty($theirCharacter, $i) ? NULL : $theirCharacter[$i+10],
          marked: $theirCharacter[$i + 13] == 1
          ));
    } 
  }
  $response->opponentEquipment = $characterContents;

  // my hand contents
  $restriction = "";
  $actionType = $turn[0] == "ARS" ? 4 : 27;
  $resourceRestrictedCard = "";
  if(isset($turn[3])) $resourceRestrictedCard = $turn[3];
  if (strpos($turn[0], "CHOOSEHAND") !== false && ($turn[0] != "MULTICHOOSEHAND" || $turn[0] != "MAYMULTICHOOSEHAND")) $actionType = 16;
  $myHandContents = array();
  for ($i = 0; $i < count($myHand); ++$i) {
    if ($playerID == 3) {
      if(IsCasterMode() || IsGameOver()) array_push($myHandContents, JSONRenderedCard(cardNumber: $myHand[$i], controller: 2));
      else array_push($myHandContents, JSONRenderedCard(cardNumber: $MyCardBack, controller: 2));
    } else {
      if ($playerID == $currentPlayer) $playable = $turn[0] == "ARS" || IsPlayable($myHand[$i], $turn[0], "HAND", -1, $restriction, pitchRestriction:$resourceRestrictedCard) || ($actionType == 16 && $turn[0] != "MULTICHOOSEHAND" && strpos("," . $turn[2] . ",", "," . $i . ",") !== false && $restriction == "");
      else $playable = false;
      $border = CardBorderColor($myHand[$i], "HAND", $playable);
      $actionTypeOut = (($currentPlayer == $playerID) && $playable == 1 ? $actionType : 0);
      if ($restriction != "") $restriction = implode("_", explode(" ", $restriction));
      $actionDataOverride = (($actionType == 16 || $actionType == 27) ? strval($i) : $myHand[$i]);
      array_push($myHandContents, JSONRenderedCard(cardNumber: $myHand[$i], action: $actionTypeOut, borderColor: $border, actionDataOverride: $actionDataOverride, controller: $playerID, restriction: $restriction));
    }
  }
  $response->playerHand = $myHandContents;

  //My life
  $response->playerHealth = $myHealth;
  //My soul count
  $response->playerSoulCount = count($mySoul);

  //my discard
  $playerDiscardArr = array();
  for($i = 0; $i < count($myDiscard); $i += DiscardPieces()) {
    $action = $currentPlayer == $playerID && PlayableFromGraveyard($myDiscard[$i]) && IsPlayable($myDiscard[$i], $turn[0], "GY", $i) ? 36 : 0;
    $border = CardBorderColor($myDiscard[$i], "GY", ($action == 36));
    array_push($playerDiscardArr, JSONRenderedCard($myDiscard[$i], action: $action, borderColor: $border, actionDataOverride: strval($i)));
  }
  $myBlessingsCount = SearchCount(SearchDiscardForCard($playerID, "count_your_blessings_red", "count_your_blessings_yellow", "count_your_blessings_blue"));
  if ($myBlessingsCount > 0) {
    $response->myBlessingsCount = $myBlessingsCount;
  }
  $response->playerDiscard = $playerDiscardArr;

  $response->playerPitchCount = $myResources[0];
  $playerPitchArr = array();
  for($i = count($myPitch) - PitchPieces(); $i >= 0; $i -= PitchPieces()) {
    array_push($playerPitchArr, JSONRenderedCard($myPitch[$i]));
  }
  $response->playerPitch = $playerPitchArr;

  $response->playerDeckCount = count($myDeck);
  $playerHero = ShiyanaCharacter($myCharacter[0], $playerID);
  if($playerID < 3 && count($myDeck) > 0 && $myCharacter[1] < 3 && ($playerHero == "dash_database" || $playerHero == "dash_io") && $turn[0] != "OPT" && $turn[0] != "P" && $turn[0] != "CHOOSETOPOPPONENT" && $turn[0] != "DOCRANK") {
    $playable = $playerID == $currentPlayer && IsPlayable($myDeck[0], $turn[0], "DECK", 0);
    $response->playerDeckCard = JSONRenderedCard($myDeck[0], action:($playable ? 35 : 0), actionDataOverride:strval(0), borderColor: ($playable ? 6 : 0), controller:$playerID);
  }
  else $response->playerDeckCard = JSONRenderedCard(count($myDeck) > 0 ? $MyCardBack : $blankZone);
  $playerDeckArr = array();
  $response->playerDeckPopup = false;
  if(IsGameOver() || (($turn[0] == "CHOOSEMULTIZONE" || $turn[0] == "MAYCHOOSEMULTIZONE") && substr($turn[2], 0, 6) === "MYDECK") || $turn[0] == "MAYCHOOSEDECK" || $turn[0] == "CHOOSEDECK" || $turn[0] == "MULTICHOOSEDECK") {
    for($i=0; $i<count($myDeck); $i+=DeckPieces()) {
      array_push($playerDeckArr, JSONRenderedCard($myDeck[$i]));
    }
  }
  $response->playerDeck = $playerDeckArr;

  $response->playerCardBack = JSONRenderedCard($MyCardBack);

  //My Banish
  $playerBanishArr = array();
  for ($i = 0; $i < count($myBanish); $i += BanishPieces()) {
    $label = "";
    $overlay = 0;
    $action = $currentPlayer == $playerID && IsPlayable($myBanish[$i], $turn[0], "BANISH", $i) ? 14 : 0;
    $mod = explode("-", $myBanish[$i + 1])[0];
    $border = CardBorderColor($myBanish[$i], "BANISH", $action > 0, $mod);
    $cardID = $myBanish[$i];
    if($mod == "FACEDOWN") {
      $overlay = 1;
      $border = 0;
    }
    elseif (isFaceDownMod($mod) && $playerID == 3) $cardID = "CardBack";
    if ($mod == "INT") {
      $overlay = 1;
      $label = "Intimidated";
    }
    array_push($playerBanishArr, JSONRenderedCard($cardID, $action, $overlay, borderColor: $border, actionDataOverride: strval($i), label: $label));
  }
  $response->playerBanish = $playerBanishArr;
  if (TalentContains($myCharacter[0], "SHADOW")) {
    $response->myBloodDebtCount = SearchCount(SearchBanish($playerID, "", "", -1, -1, "", "", true));
    $response->amIBloodDebtImmune = IsImmuneToBloodDebt($playerID);
  }
  if (HasEssenceOfEarth($myCharacter[0])) {
    $response->myEarthCount = SearchCount(SearchBanish($playerID, talent:"EARTH"));
  }

  //Now display my character and equipment
  $numWeapons = 0;
  $myCharData = array();
  for ($i = 0; $i < count($myCharacter); $i += CharacterPieces()) {
    $restriction = "";
    $counters = 0;
    $atkCounters = 0;
    $gem = 0;
    $label = "";
    $border = 0;
    $myChar = $myCharacter[$i];
    if ($myCharacter[$i + 1] == 4) $myChar = "DUMMYDISHONORED";
    if ($myCharacter[$i + 2] > 0) $counters = $myCharacter[$i + 2];
    $playable = $playerID == $currentPlayer && $myCharacter[$i + 1] > 0 && IsPlayable($myChar, $turn[0], "CHAR", $i, $restriction);
    $border = CardBorderColor($myChar, "CHAR", $playable);
    $type = CardType($myChar);
    if (TypeContains($myChar, "D")) $type = "C";
    $sTypeArr = explode(",", CardSubType($myChar, $myCharacter[$i+11]));
    $sType = $sTypeArr[0];
    for($j=0; $j<count($sTypeArr); ++$j) {
      if($sTypeArr[$j] == "Head" || $sTypeArr[$j] == "Chest" || $sTypeArr[$j] == "Arms" || $sTypeArr[$j] == "Legs") {
        $sType = $sTypeArr[$j];
        break;
      }
    }
    if (TypeContains($myCharacter[$i], "W", $playerID)) {
      ++$numWeapons;
      if ($numWeapons > 1) {
        $type = "E";
        $sType = "Off-Hand";
      }
      $label = WeaponHasGoAgainLabel($i, $playerID) ? "Go Again" : "";
      $weaponAttackModifiers = [];
    if (!$playable) {
        if (MainCharacterAttackModifiers($weaponAttackModifiers, $i, true, $playerID) > 0 ||
            SearchCurrentTurnEffectsForPartielID($myCharacter[$i + 11])) {
            $border = 5;
        }
    }
      $atkCounters = $myCharacter[$i + 3];
    }
    if ($myCharacter[$i + 9] != 2 && $myCharacter[$i + 1] != 0 && $playerID != 3 && $myCharacter[$i + 12] != "DOWN") {
      $gem = ($myCharacter[$i + 9] == 1 ? 1 : 2);
    }
    $restriction = implode("_", explode(" ", $restriction));
    if(IsGameOver()) $myCharacter[$i + 12] = "UP";
    if($playerID == 3 && $myCharacter[$i + 12] == "DOWN" && !IsGameOver()) {
      array_push($myCharData, JSONRenderedCard(
        $MyCardBack)); //CardID
    }
    else{
      if($myCharacter[$i + 1] > 0) { //Don't show broken equipment cards as they are in the graveyard
        array_push($myCharData, JSONRenderedCard(
          $myChar, //CardID
          $currentPlayer == $playerID && $playable ? 3 : 0,
          $myCharacter[$i + 1] != 2 ? 1 : 0, //Overlay
          $border,
          $myCharacter[$i + 1] != 0 ? $counters : 0, //Counters
          strval($i), //Action Data Override
          0, //Life Counters
          $myCharacter[$i + 4], //Def Counters
          $atkCounters,
          $playerID,
          $type,
          $sType,
          $restriction,
          $myCharacter[$i + 1] == 0, //Status
          $myCharacter[$i + 6] == 1, //On Chain
          $myCharacter[$i + 8] == 1, //Frozen
          $gem,
          label: $label,
          facing: $myCharacter[$i + 12],
          numUses: $myCharacter[$i + 5], //Number of Uses
          subcard: isSubcardEmpty($myCharacter, $i) ? NULL : $myCharacter[$i+10],
          marked: $myCharacter[$i + 13] == 1));
      }
    }
  }
  $response->playerEquipment = $myCharData;

  // what's up their arse
  $theirArse = array();
  if ($theirArsenal != "") {
    for ($i = 0; $i < count($theirArsenal); $i += ArsenalPieces()) {
      if ($theirArsenal[$i + 1] == "UP" || $playerID == 3 && IsCasterMode() || IsGameOver()) {
        array_push($theirArse, JSONRenderedCard(
          cardNumber: $theirArsenal[$i],
          controller: ($playerID == 1 ? 2 : 1),
          facing: $theirArsenal[$i + 1],
          countersMap: (object) ["counters" => $theirArsenal[$i + 3]],
          isFrozen: $theirArsenal[$i + 4] == 1
        ));
      } else array_push($theirArse, (JSONRenderedCard(
        cardNumber: $TheirCardBack,
        controller: ($playerID == 1 ? 2 : 1),
        facing: $theirArsenal[$i + 1],
        countersMap: (object) ["counters" => $theirArsenal[$i + 3]],
        isFrozen: $theirArsenal[$i + 4] == 1
      )));
    }
  }
  $response->opponentArse = $theirArse;

  // what's up my arse
  $myArse = array();
  if ($myArsenal != "") {
    for ($i = 0; $i < count($myArsenal); $i += ArsenalPieces()) {
      if ($playerID == 3 && !IsCasterMode() && $myArsenal[$i + 1] != "UP" && !IsGameOver()) {
        array_push($myArse, JSONRenderedCard(
          cardNumber: $MyCardBack,
          controller: 2,
          facing: $myArsenal[$i + 1],
          countersMap: (object) ["counters" => $myArsenal[$i + 3]],
          isFrozen: $myArsenal[$i + 4] == 1
        ));
      } else {
        $playable = $playerID == $currentPlayer && $turn[0] != "P" && IsPlayable($myArsenal[$i], $turn[0], "ARS", $i, $restriction);
        $border = CardBorderColor($myArsenal[$i], "ARS", $playable);
        $actionTypeOut = (($currentPlayer == $playerID) && $playable == 1 ? 5 : 0);
        if ($restriction != "") $restriction = implode("_", explode(" ", $restriction));
        $actionDataOverride = (($actionType == 16 || $actionType == 27) ? strval($i) : "");
        array_push($myArse, JSONRenderedCard(
          cardNumber: $myArsenal[$i],
          action: $actionTypeOut,
          borderColor: $border,
          actionDataOverride: $actionDataOverride,
          controller: $playerID,
          restriction: $restriction,
          facing: $myArsenal[$i + 1],
          countersMap: (object) ["counters" => $myArsenal[$i + 3]],
          isFrozen: $myArsenal[$i + 4] == 1
        ));
      }
    }
  }
  $response->playerArse = $myArse;

  // Chain Links, how many are there and do they do things?
  $chainLinkOutput = array();
  for ($i = 0; $i < count($chainLinks); ++$i) {
    $damage = $chainLinkSummary[$i * ChainLinkSummaryPieces()];
    $isDraconic = DelimStringContains($chainLinkSummary[$i * ChainLinkSummaryPieces() + 2], "DRACONIC", $playerID);
    array_push($chainLinkOutput, [
      'result' => $damage > 0 ? "hit" : "no-hit",
      'isDraconic' => $isDraconic
    ]);
  }
  $response->combatChainLinks = $chainLinkOutput;

  // their allies
  $theirAlliesOutput = array();
  $theirAllies = GetAllies($playerID == 1 ? 2 : 1);
  for ($i = 0; $i + AllyPieces() - 1 < count($theirAllies); $i += AllyPieces()) {
    $label = "";
    $type = CardType($theirAllies[$i]);
    $sType = CardSubType($theirAllies[$i]);
    $uniqueID = $theirAllies[$i+5];
    if(SearchCurrentTurnEffectsForUniqueID($uniqueID) != -1) $label = "Buffed";
    array_push($theirAlliesOutput, JSONRenderedCard(cardNumber: $theirAllies[$i], overlay: ($theirAllies[$i + 1] != 2 ? 1 : 0), counters: $theirAllies[$i + 6], lifeCounters: $theirAllies[$i + 2], controller: $otherPlayer, type: $type, sType: $sType, isFrozen: ($theirAllies[$i + 3] == 1), subcard: $theirAllies[$i+4] != "-" ? $theirAllies[$i+4] : NULL, atkCounters:$theirAllies[$i+9], label: $label));
  }
  $response->opponentAllies = $theirAlliesOutput;

  //their auras
  $theirAurasOutput = array();
  for ($i = 0; $i + AuraPieces() - 1 < count($theirAuras); $i += AuraPieces()) {
    $type = CardType($theirAuras[$i]);
    $sType = CardSubType($theirAuras[$i]);
    $gem = $theirAuras[$i + 8] != 2 ? $theirAuras[$i + 8] : NULL;
    array_push($theirAurasOutput, 
      JSONRenderedCard(cardNumber: $theirAuras[$i],
      actionDataOverride: strval($i),
      overlay: ($theirAuras[$i + 1] != 2 ? 1 : 0),
      counters: $theirAuras[$i + 2], 
      atkCounters: $theirAuras[$i + 3],
      controller: $otherPlayer,
      type: $type,
      sType: $sType,
      gem: $gem,
      label: (!TypeContains($theirAuras[$i], "T") && $theirAuras[$i + 4] == 1 ? "Token Copy" : "")));
  }
  $response->opponentAuras = $theirAurasOutput;

  //their items
  $theirItemsOutput = array();
  for ($i = 0; $i + ItemPieces() - 1 < count($theirItems); $i += ItemPieces()) {
    $type = CardType($theirItems[$i]);
    $sType = CardSubType($theirItems[$i]);
    $gem = $theirItems[$i + 6] != 2 ? $theirItems[$i + 6] : NULL;
    array_push($theirItemsOutput, 
    JSONRenderedCard(
      cardNumber: $theirItems[$i], 
      actionDataOverride: strval($i), 
      overlay: ($theirItems[$i + 2] != 2 ? 1 : 0), 
      counters: $theirItems[$i + 1], 
      controller: $otherPlayer, 
      type: $type, 
      sType: $sType, 
      isFrozen: $theirItems[$i + 7] == 1,
      gem: $gem));
  }
  $response->opponentItems = $theirItemsOutput;

  //their permanents
  $theirPermanentsOutput = array();
  $theirPermanents = GetPermanents($playerID == 1 ? 2 : 1);
  for ($i = 0; $i + PermanentPieces() - 1 < count($theirPermanents); $i += PermanentPieces()) {
    if($theirPermanents[$i] == "levia_redeemed") continue;//Cards in inventory should not be shown to opponent
    $type = CardType($theirPermanents[$i]);
    $sType = CardSubType($theirPermanents[$i]);
    array_push($theirPermanentsOutput, JSONRenderedCard(cardNumber: $theirPermanents[$i], controller: $otherPlayer, type: $type, sType: $sType));
  }
  $response->opponentPermanents = $theirPermanentsOutput;

  //my allies
  $myAlliesOutput = array();
  $myAllies = GetAllies($playerID == 1 ? 1 : 2);
  for ($i = 0; $i + AllyPieces() - 1 < count($myAllies); $i += AllyPieces()) {
    $label = "";
    $type = CardType($myAllies[$i]);
    $sType = CardSubType($myAllies[$i]);
    $playable = ($currentPlayer == $playerID ? IsPlayable($myAllies[$i], $turn[0], "PLAY", $i, $restriction) && $myAllies[$i + 1] == 2 : false);
    $actionType = ($currentPlayer == $playerID && $turn[0] != "P" && $playable) ? 24 : 0;
    $border = CardBorderColor($myAllies[$i], "PLAY", $playable);
    $actionDataOverride = ($actionType == 24 ? strval($i) : "");
    $uniqueID = $myAllies[$i+5];
    if(SearchCurrentTurnEffectsForUniqueID($uniqueID) != -1) $label = "Buffed";
    array_push($myAlliesOutput, JSONRenderedCard(
      cardNumber: $myAllies[$i],
      action: $actionType,
      overlay: ($myAllies[$i+1] != 2 ? 1 : 0),
      counters: $myAllies[$i+6],
      borderColor: $border,
      actionDataOverride: $actionDataOverride,
      lifeCounters: $myAllies[$i+2],
      controller: $playerID,
      type: $type,
      sType: $sType,
      isFrozen: ($myAllies[$i+3] == 1),
      subcard: $myAllies[$i+4] != "-" ? $myAllies[$i+4] : NULL,
      atkCounters: $myAllies[$i+9],
      label: $label
    ));
  }
  $response->playerAllies = $myAlliesOutput;

  //my auras
  $auraTileMap = [];
  $myAurasOutput = array();
  for ($i = 0; $i + AuraPieces() - 1 < count($myAuras); $i += AuraPieces()) {
    $playable = ($currentPlayer == $playerID ? $myAuras[$i + 1] == 2 && IsPlayable($myAuras[$i], $turn[0], "PLAY", $i, $restriction) : false);
    if($myAuras[$i] == "restless_coalescence_yellow" && $currentPlayer == $playerID && IsPlayable($myAuras[$i], $turn[0], "PLAY", $i, $restriction)) $playable = true;
    $border = CardBorderColor($myAuras[$i], "PLAY", $playable);
    $counters = $myAuras[$i + 2];
    $atkCounters = $myAuras[$i + 3];
    $action = $currentPlayer == $playerID && $turn[0] != "P" && $playable ? 22 : 0;
    $type = CardType($myAuras[$i]);
    $sType = CardSubType($myAuras[$i]);
    $gem = $myAuras[$i + 7] != 2 ? $myAuras[$i + 7] : NULL;
    if (isset($auraTileMap[$myAuras[$i]])) $gem = $auraTileMap[$myAuras[$i]];
    else $auraTileMap[$myAuras[$i]] = $gem;
    array_push($myAurasOutput, JSONRenderedCard(
      cardNumber: $myAuras[$i],
      overlay: ($myAuras[$i + 1] != 2 ? 1 : 0),
      counters: $counters,
      atkCounters: $atkCounters,
      action: $action,
      controller: $playerID,
      borderColor: $border,
      type: $type,
      actionDataOverride: strval($i),
      sType: $sType,
      gem: $gem,
      label: !TypeContains($myAuras[$i], "T") && $myAuras[$i + 4] == 1 ? "Token Copy" : ""
    ));
  }
  $response->playerAuras = $myAurasOutput;

  //my items
  $itemTileMap = [];
  $myItemsOutput = array();
  for ($i = 0; $i + ItemPieces() - 1 < count($myItems); $i += ItemPieces()) {
    $type = CardType($myItems[$i]);
    $sType = CardSubType($myItems[$i]);
    $playable = ($currentPlayer == $playerID ? IsPlayable($myItems[$i], $turn[0], "PLAY", $i, $restriction) : false);
    $border = CardBorderColor($myItems[$i], "PLAY", $playable);
    $actionTypeOut = (($currentPlayer == $playerID) && $playable == 1 ? 10 : 0);
    if ($restriction != "") $restriction = implode("_", explode(" ", $restriction));
    $actionDataOverride = strval($i);
    $gem = $myItems[$i + 5] != 2 ? $myItems[$i + 5] : NULL;
    if (isset($itemTileMap[$myItems[$i]])) $gem = $itemTileMap[$myItems[$i]];
    else $itemTileMap[$myItems[$i]] = $gem;
    array_push($myItemsOutput, 
    JSONRenderedCard(
      cardNumber: $myItems[$i], 
      action: $actionTypeOut, 
      borderColor: $border, 
      actionDataOverride: $actionDataOverride, 
      overlay: ItemOverlay($myItems[$i], $myItems[$i + 2], $myItems[$i + 3]), 
      counters: $myItems[$i + 1],
      controller: $playerID, 
      type: $type,
      sType: $sType, 
      isFrozen: $myItems[$i + 7] == 1, //Frozen
      gem: $gem, 
      restriction: $restriction));
  }
  $response->playerItems = $myItemsOutput;

  //my permanents
  $myPermanentsOutput = array();
  $myPermanents = GetPermanents($playerID == 1 ? 1 : 2);
  for ($i = 0; $i + PermanentPieces() - 1 < count($myPermanents); $i += PermanentPieces()) {
    $type = CardType($myPermanents[$i]);
    $sType = CardSubType($myPermanents[$i]);
    $playable = ($currentPlayer == $playerID ? IsPlayable($myPermanents[$i], $turn[0], "PLAY", $i, $restriction) : false);
    $border = CardBorderColor($myPermanents[$i], "PLAY", $playable);
    $actionTypeOut = (($currentPlayer == $playerID) && $playable == 1 ? 34 : 0);
    if ($restriction != "") $restriction = implode("_", explode(" ", $restriction));
    $actionDataOverride = strval($i);
    array_push($myPermanentsOutput, JSONRenderedCard(cardNumber: $myPermanents[$i], controller: $playerID, type: $type, sType: $sType, action: $actionTypeOut, borderColor: $border, actionDataOverride: $actionDataOverride, restriction: $restriction));
  }
  $response->playerPermanents = $myPermanentsOutput;

  //Landmarks
  $landmarksOutput = array();
  for ($i = 0; $i + LandmarkPieces() - 1 < count($landmarks); $i += LandmarkPieces()) {
    $playable = ($currentPlayer == $playerID ? IsPlayable($landmarks[$i], $turn[0], "PLAY", $i, $restriction) : false);
    $action = ($playable && $currentPlayer == $playerID ? 25 : 0);
    $border = CardBorderColor($landmarks[$i], "PLAY", $playable);
    $counters = 0;
    $type = CardType($landmarks[$i]);
    $sType = CardSubType($landmarks[$i]);
    array_push($landmarksOutput, JSONRenderedCard(
      cardNumber: $landmarks[$i],
      type: $type,
      sType: $sType,
      actionDataOverride: strval($i),
      action: $action,
      borderColor: $border
    ));
  }
  $response->landmarks = $landmarksOutput;

  // Chat Log
  $response->chatLog = JSONLog($gameName, $playerID);

  // Deduplicate current turn effects
  $playerEffects = array();
  $opponentEffects = array();
  $friendlyEffects = "";
  $BorderColor = NULL;
  $counters = NULL;
  $friendlyCounts = array();
  $opponentCounts = array();
  $friendlyRenderedEffects = array();
  $opponentRenderedEffects = array();

  // Count the occurrences of each effect
  for ($i = 0; $i + CurrentTurnEffectsPieces() - 1 < count($currentTurnEffects); $i += CurrentTurnEffectsPieces()) {
      $cardID = explode("-", $currentTurnEffects[$i])[0];
      $cardID = explode(",", $cardID)[0];
      // $cardID = explode("_", $cardID)[0]; TODO: keep an eye on if removing this breaks anything
      if(AdministrativeEffect($cardID) || $cardID == "luminaris_angels_glow-1" || $cardID == "luminaris_angels_glow-2") continue; //Don't show useless administrative effect
      $isFriendly = ($playerID == $currentTurnEffects[$i + 1] || $playerID == 3 && $otherPlayer != $currentTurnEffects[$i + 1]);

      if ($isFriendly) {
          if (!isset($friendlyCounts[$cardID])) $friendlyCounts[$cardID] = 0;
          $friendlyCounts[$cardID]++;
      } else {
          if (!isset($opponentCounts[$cardID])) $opponentCounts[$cardID] = 0;
          $opponentCounts[$cardID]++;
      }
  }

  // Render the effects
  for ($i = 0; $i + CurrentTurnEffectsPieces() - 1 < count($currentTurnEffects); $i += CurrentTurnEffectsPieces()) {
      $cardID = explode("-", $currentTurnEffects[$i])[0];
      $cardID = explode(",", $cardID)[0];
      // $cardID = explode("_", $cardID)[0]; TODO: keep an eye on if removing this breaks anything
      if(AdministrativeEffect($cardID) || $cardID == "luminaris_angels_glow-1" || $cardID == "luminaris_angels_glow-2") continue; //Don't show useless administrative effect
      $isFriendly = ($playerID == $currentTurnEffects[$i + 1] || $playerID == 3 && $otherPlayer != $currentTurnEffects[$i + 1]);
      $BorderColor = ($isFriendly ? "blue" : "red");

      $counters = ($isFriendly ? $friendlyCounts[$cardID] : $opponentCounts[$cardID]);

      if($cardID == "shelter_from_the_storm_red" || $cardID == "calming_breeze_red") {
        $counters = $currentTurnEffects[$i + 3];
      }

      if ($playerID == $currentTurnEffects[$i + 1] || $playerID == 3 && $otherPlayer != $currentTurnEffects[$i + 1]) {
          if(array_search($cardID, $friendlyRenderedEffects) === false || !skipEffectUIStacking($cardID)) {
              array_push($friendlyRenderedEffects, $cardID);
              array_push($playerEffects, JSONRenderedCard($cardID, borderColor:$BorderColor, counters:$counters > 1 ? $counters : NULL, lightningPlayed:"SKIP", showAmpAmount:"Effect-".$i));
          }
      }  
      elseif(array_search($cardID, $opponentRenderedEffects) === false && $otherPlayer == $currentTurnEffects[$i + 1] || !skipEffectUIStacking($cardID)) {
          array_push($opponentRenderedEffects, $cardID);
          array_push($opponentEffects, JSONRenderedCard($cardID, borderColor:$BorderColor, counters:$counters > 1 ? $counters : NULL, lightningPlayed:"SKIP", showAmpAmount:"Effect-".$i));
      }  
  }
  $response->opponentEffects = $opponentEffects;
  $response->playerEffects = $playerEffects;

  //Events
  $newEvents = new stdClass();
  $newEvents->eventArray = array();
  for ($i = 0; $i < count($events); $i += EventPieces()) {
    $thisEvent = new stdClass();
    $thisEvent->eventType = $events[$i];
    $thisEvent->eventValue = isset($events[$i + 1]) ? $events[$i + 1] : null;
    array_push($newEvents->eventArray, $thisEvent);
  }
  $response->newEvents = $newEvents;

  // Phase of the turn (for the tracker widget)
  $turnPhase = new stdClass();
  $turnPhase->turnPhase = $turn[0];
  if (count($layers) > 0) {
    $turnPhase->layer = $layers[0];
  }
  $isItMeOrThem = $currentPlayer == $playerID ? "Choose " : "Your opponent is choosing ";
  $turnPhase->caption = $isItMeOrThem . TypeToPlay($turn[0]);
  $response->turnPhase = $turnPhase;

  // Do we have priority?
  $response->havePriority = $currentPlayer == $playerID ? true : false;

  // opponent and player Action Points
  if ($mainPlayer == $playerID || ($playerID == 3 && $mainPlayer != $otherPlayer)) {
    $response->opponentAP = 0;
    $response->playerAP = $actionPoints;
  } else {
    $response->opponentAP = $actionPoints;
    $response->playerAP = 0;
  }

  // Last played Card
  $response->lastPlayedCard = (count($lastPlayed) == 0) ?
    JSONRenderedCard($MyCardBack) :
    JSONRenderedCard($lastPlayed[0], controller: $lastPlayed[1]);
  // is the player the active player (is it their turn?)
  $response->amIActivePlayer = ($turn[1] == $playerID) ? true : false;
  // who's turn it is
  $response->turnPlayer = $mainPlayer;
  //Turn number
  $response->turnNo = $currentTurn;
  //Clock
  $response->clock = $p1TotalTime + $p2TotalTime;

  $playerPrompt = new StdClass();
  $promptButtons = array();
  $helpText = "";
  // Reminder text box highlight thing
  if ($turn[0] != "OVER") {
    $helpText .= ($currentPlayer != $playerID ? "Waiting for other player to choose " . TypeToPlay($turn[0]) : GetPhaseHelptext());
    if ($currentPlayer == $playerID) {
      if ($turn[0] == "P" || $turn[0] == "CHOOSEHANDCANCEL" || $turn[0] == "CHOOSEDISCARDCANCEL") {
        $helpText .= $turn[0] == "P" ? " (" . $myResources[0] . " of " . $myResources[1]. ")" : "";
        array_push($promptButtons, CreateButtonAPI($playerID, "Cancel", 10000, 0, "16px"));
      }
      if (CanPassPhase($turn[0])) {
        if ($turn[0] == "B") {
          array_push($promptButtons, CreateButtonAPI($playerID, "Undo Block", 10001, 0, "16px"));
          array_push($promptButtons, CreateButtonAPI($playerID, "Pass", 99, 0, "16px"));
          array_push($promptButtons, CreateButtonAPI($playerID, "Pass Block and Reactions", 101, 0, "16px", "", "Reactions will not be skipped if the opponent reacts"));
        }
      }
    } else {
      if ($currentPlayerActivity == 2 && $playerID != 3) {
        $helpText .= " — Opponent is inactive";
        array_push($promptButtons, CreateButtonAPI($playerID, "Claim Victory", 100007, 0, "16px"));
      }
    }
  }

  $playerPrompt->helpText = $helpText;
  $playerPrompt->buttons = $promptButtons;
  $response->playerPrompt = $playerPrompt;

  $response->fullRematchAccepted = $turn[0] == "REMATCH";

  // ******************************
  // * PLAYER MUST CHOOSE A THING *
  // ******************************

  $playerInputPopup = new stdClass();
  $playerInputButtons = array();
  $playerInputPopup->active = false;

  // Do arcane separately
  if (($turn[0] == "BUTTONINPUT" || $turn[0] == "BUTTONINPUTNOPASS" || $turn[0] == "CHOOSEARCANE") && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    $options = explode(",", $turn[2]);
    $caption = "";
    if ($turn[0] == "CHOOSEARCANE") {
      $vars = explode("-", $dqVars[0]);
      $caption .= "Source: " . CardLink($vars[1], $vars[1]) . "&nbsp | &nbspTotal Damage: " . $vars[0];
      if(!CanDamageBePrevented($playerID, $vars[0], "ARCANE", $vars[1])) {
        $caption .= "&nbsp | &nbsp <span style='font-size: 0.8em; color:red;'>**WARNING: THIS DAMAGE IS UNPREVENTABLE**</span><br>";
      }
      else $caption .= "<br>";
    }
    for ($i = 0; $i < count($options); ++$i) {
      array_push($playerInputButtons, CreateButtonAPI($playerID, str_replace("_", " ", $options[$i]), 17, strval($options[$i]), "24px"));
    }
    if(isset($vars[1]) && $vars[1] == "runechant")
    {
      array_push($playerInputButtons, CreateButtonAPI($playerID, "Skip All Runechants", 105, 0, "24px"));
    }
    $playerInputPopup->popup = CreatePopupAPI("BUTTONINPUT", [], 0, 1, $caption . GetPhaseHelptext(), 1, "");
  }

  if (($turn[0] == "YESNO" || $turn[0] == "DOCRANK") && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    array_push($playerInputButtons, CreateButtonAPI($playerID, "Yes", 20, "YES", "20px"));
    array_push($playerInputButtons, CreateButtonAPI($playerID, "No", 20, "NO", "20px"));
    $playerInputPopup->popup = CreatePopupAPI("YESNO", [], 0, 1, GetPhaseHelptext(), 1, "");
  }

  if ($turn[0] == "PDECK" && $currentPlayer == $playerID) {
    $playerInputPopup->active = true;
    $pitchingCards = array();
    for ($i = 0; $i < count($myPitch); $i += 1) {
      array_push($pitchingCards, JSONRenderedCard($myPitch[$i], action: 6, actionDataOverride: $myPitch[$i]));
    }
    $playerInputPopup->popup = CreatePopupAPI("PITCH", [], 0, 1, "Choose a card from your pitch zone to put on the bottom of your deck", 1, cardsArray: $pitchingCards);
  }

  if ($turn[0] == "DYNPITCH" && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    $options = explode(",", $turn[2]);
    for ($i = 0; $i < count($options); ++$i) {
      array_push($playerInputButtons, CreateButtonAPI($playerID, $options[$i], 7, $options[$i], "24px"));
    }
    $playerInputPopup->popup = CreatePopupAPI("DYNPITCH", [], 0, 1, GetPhaseHelptext(), 1, "");
  }

  if ($turn[0] == "CHOOSENUMBER" && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    $options = explode(",", $turn[2]);
    for ($i = 0; $i < count($options); ++$i) {
      array_push($playerInputButtons, CreateButtonAPI($playerID, $options[$i], 7, $options[$i], "24px"));
    }
    $playerInputPopup->popup = CreatePopupAPI("CHOOSENUMBER", [], 0, 1, GetPhaseHelptext(), 1, "");
  }

  if ($turn[0] == "OK" && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    array_push($playerInputButtons, CreateButtonAPI($playerID, "Ok", 99, "OK", "20px"));
    $playerInputPopup->popup = CreatePopupAPI("OK", [], 0, 1, GetPhaseHelptext(), 1, "");
  }

  if (($turn[0] == "CHOOSETOP" || $turn[0] == "CHOOSEBOTTOM" || $turn[0] == "CHOOSECARD" || $turn[0] == "MAYCHOOSECARD") && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    $options = explode(",", $turn[2]);
    $optCards = array();
    for ($i = 0; $i < count($options); ++$i) {
      array_push($optCards, JSONRenderedCard($options[$i], action: 0));
      if ($turn[0] == "CHOOSETOP") array_push($playerInputButtons, CreateButtonAPI($playerID, "Top", 8, $options[$i], "20px"));
      if ($turn[0] == "CHOOSEBOTTOM") array_push($playerInputButtons, CreateButtonAPI($playerID, "Bottom", 9, $options[$i], "20px"));
      if ($turn[0] == "CHOOSECARD" || $turn[0] == "MAYCHOOSECARD") array_push($playerInputButtons, CreateButtonAPI($playerID, "Choose", 23, $options[$i], "20px"));
    }
    $playerInputPopup->popup = CreatePopupAPI("OPT", [], 0, 1, GetPhaseHelptext(), 1, "", cardsArray: $optCards);
  }

  if ($turn[0] == "OPT" && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    $options = explode(";", $turn[2]);
    $topOptions = explode(",", $options[0] ?? "");
    if (count($topOptions) === 1 && $topOptions[0] === "") {
        $topOptions = [];
    }
    $bottomOptions = explode(",", $options[1] ?? "");
    if (count($bottomOptions) === 1 && $bottomOptions[0] === "") {
        $bottomOptions = [];
    }
    $topOptCards = array();
    for ($i = 0; $i < count($topOptions); ++$i) {
      array_push($topOptCards, JSONRenderedCard($topOptions[$i], action: 0));
    }
    $bottomOptCards = array();
    for ($i = 0; $i < count($bottomOptions); ++$i) {
      array_push($bottomOptCards, JSONRenderedCard($bottomOptions[$i], action: 0));
    }
    $playerInputPopup->popup = CreatePopupAPI("NEWOPT", [], 0, 1, "Drag cards to add to the top or bottom of the deck", 1, "", topCards: $topOptCards, bottomCards: $bottomOptCards);
  }

  if (($turn[0] == "CHOOSETOPOPPONENT") && $turn[1] == $playerID) { //Use when you have to reorder the top of your opponent library e.g. Righteous Cleansing
    $playerInputPopup->active = true;
    $otherPlayer = ($playerID == 1 ? 2 : 1);
    $options = explode(",", $turn[2]);
    $optCards = array();
    for ($i = 0; $i < count($options); ++$i) {
      array_push($optCards, JSONRenderedCard($options[$i], action: 0));
      if ($turn[0] == "CHOOSETOPOPPONENT") array_push($playerInputButtons, CreateButtonAPI($otherPlayer, "Top", 29, $options[$i], "20px"));
    }
    $playerInputPopup->popup = CreatePopupAPI("CHOOSETOPOPPONENT", [], 0, 1, GetPhaseHelptext(), 1, "", cardsArray: $optCards);
  }

  if ($turn[0] == "INPUTCARDNAME" && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    $playerInputPopup->popup = CreatePopupAPI("INPUTCARDNAME", [], 0, 1, "Name a card");
  }

  if ($turn[0] == "HANDTOPBOTTOM" && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    $cardsArray = array();
    for ($i = 0; $i < count($myHand); ++$i) {
      array_push($cardsArray, JSONRenderedCard($myHand[$i], action: 0));
      array_push($playerInputButtons, CreateButtonAPI($playerID, "Top", 12, $myHand[$i], "20px"));
      array_push($playerInputButtons, CreateButtonAPI($playerID, "Bottom", 13, $myHand[$i], "20px"));
    }
    $playerInputPopup->popup = CreatePopupAPI("HANDTOPBOTTOM", [], 0, 1, GetPhaseHelptext(), 1, "", cardsArray: $cardsArray);
  }

  if (($turn[0] == "CHOOSECARDID") && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    $options = explode(",", $turn[2]);
    $cardList = array();
    for ($i = 0; $i < count($options); ++$i) {
      array_push($cardList, JSONRenderedCard($options[$i], action: 16, actionDataOverride: strval($options[$i])));
    }
    $playerInputPopup->popup = CreatePopupAPI("CHOOSEZONE", [], 0, 1, GetPhaseHelptext(), 1, "", cardsArray: $cardList);
  }

  if (($turn[0] == "MAYCHOOSEMULTIZONE" || $turn[0] == "CHOOSEMULTIZONE") && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    $options = explode(",", $turn[2]);
    $otherPlayer = $playerID == 2 ? 1 : 2;
    $theirAllies = &GetAllies($otherPlayer);
    $myAllies = &GetAllies($playerID);
    $cardsMultiZone = array();
    $maxCount = 0;
    $minCount = 0;
    $countOffset = 0;
    $subtitles = "";
    for ($i = 0; $i < count($options); ++$i) {
      $option = explode("-", $options[$i]);
      if ($option[0] == "MYAURAS") $source = $myAuras;
      else if ($option[0] == "THEIRAURAS") $source = $theirAuras;
      else if ($option[0] == "MYCHAR") $source = $myCharacter;
      else if ($option[0] == "THEIRCHAR") $source = $theirCharacter;
      else if ($option[0] == "MYITEMS") $source = $myItems;
      else if ($option[0] == "THEIRITEMS") $source = $theirItems;
      else if ($option[0] == "LAYER") $source = $layers;
      else if ($option[0] == "MYHAND") $source = $myHand;
      else if ($option[0] == "THEIRHAND") $source = $theirHand;
      else if ($option[0] == "MYDISCARD" || $option[0] == "MYDISCARDUID") $source = $myDiscard;
      else if ($option[0] == "THEIRDISCARD" || $option[0] == "THEIRDISCARDUID") $source = $theirDiscard;
      else if ($option[0] == "MYBANISH") $source = $myBanish;
      else if ($option[0] == "THEIRBANISH") $source = $theirBanish;
      else if ($option[0] == "MYALLY") $source = $myAllies;
      else if ($option[0] == "THEIRALLY") $source = $theirAllies;
      else if ($option[0] == "MYARS") $source = $myArsenal;
      else if ($option[0] == "THEIRARS") $source = $theirArsenal;
      else if ($option[0] == "MYPERM") $source = $myPermanents;
      else if ($option[0] == "THEIRPERM") $source = $theirPermanents;
      else if ($option[0] == "MYPITCH") $source = $myPitch;
      else if ($option[0] == "THEIRPITCH") $source = $theirPitch;
      else if ($option[0] == "MYDECK") $source = $myDeck;
      else if ($option[0] == "THEIRDECK") $source = $theirDeck;
      else if ($option[0] == "MYSOUL") $source = $mySoul;
      else if ($option[0] == "THEIRSOUL") $source = $theirSoul;
      else if ($option[0] == "LANDMARK") $source = $landmarks;
      else if ($option[0] == "CC") $source = $combatChain;
      else if ($option[0] == "COMBATCHAINLINK") $source = $combatChain;
      else if ($option[0] == "COMBATCHAINATTACKS") $source = GetCombatChainAttacks();
      else if ($option[0] == "MAXCOUNT") {$maxCount = intval($option[1]); $countOffset++; continue;}
      else if ($option[0] == "MINCOUNT") {$minCount = intval($option[1]); $countOffset++; continue;}
      $counters = 0;
      $lifeCounters = 0;
      $enduranceCounters = 0;
      $atkCounters = 0;
      $steamCounters = 0;
      $borderColor = 0;
      $uniqueIDIndex = -1;
      $label = "";

      if (($option[0] == "THEIRALLY" || $option[0] == "THEIRAURAS") && $option[1] == $combatChainState[$CCS_WeaponIndex] && $otherPlayer == $mainPlayer) $label = "Attacking";
      if (($option[0] == "MYALLY" || $option[0] == "MYAURAS") && $option[1] == $combatChainState[$CCS_WeaponIndex] && $playerID == $mainPlayer) $label = "Attacking";

      //Add indication for attacking Allies and Auras
      if (count($layers) > 0 && $layers[0] != "") {
        $searchType = $option[0] == "THEIRALLY" || $option[0] == "MYALLY" ? "Ally" : "Aura";
        $index = SearchLayer($otherPlayer, subtype: $searchType);
        if ($index != "") {
            $params = explode("|", $layers[$index + 2]);
            if (isset($params[2]) && $option[1] == $params[2]) {
              $label = "Attacking";
            }
        }
      }
      //Add indication for layers targets
      if (count($layers) > 0 && $layers[0] != "" && ($option[0] == "MYDISCARD" || $option[0] == "THEIRDISCARD")) {
        $countLayers = count($layers);
        for ($j=0; $j < $countLayers; $j += LayerPieces()) { 
            $target = $option[0]."-".$option[1];
            $cardID = GetMZCard($currentPlayer, $target);
            $params = explode("-", $layers[$j + 3]);
            if(isset($params[1])) {
              if($option[0] == "MYDISCARD") $uniqueIDIndex = SearchDiscardForUniqueID($params[1], $currentPlayer);
              else $uniqueIDIndex = SearchDiscardForUniqueID($params[1], $layers[$j + 1]);
            }
            if($uniqueIDIndex != -1 && isset($source[$uniqueIDIndex]) && $cardID == $source[$uniqueIDIndex]) {
              $label = "Targeted";
              continue;
            }
        }   
      }

      //Bonds of Agony - add indication for hand, graveyard and deck
      if(count($combatChain) > 0) {
        if($combatChain[0] == "bonds_of_agony_blue" && $turn[0] == "MAYCHOOSEMULTIZONE") {
          if($option[0] == "THEIRHAND") $label = "Hand"; 
          elseif ($option[0] == "THEIRDECK") $label = "Deck";
          elseif ($option[0] == "THEIRDISCARD") $label = "Graveyard";  
        }
      }

      //Add indication for Crown of Providence if you have the same card in hand and in the arsenal.
      if ($option[0] == "MYARS") $label = "Arsenal";
      //Add indication for Attacking Mechanoid
      if (($option[0] == "CC" || $option[0] == "LAYER") && (GetMZCard($currentPlayer, $options[$i]) == "nitro_mechanoida" || GetMZCard($currentPlayer, $options[$i]) == "teklovossen_the_mechropotenta")) $label = "Attacking";

      $index = intval($option[1]);
      $card = ($option[0] != "CARDID") ? $source[$index] : $option[1];
      if ($option[0] == "LAYER" && ($card == "TRIGGER" || $card == "MELD")) $card = $source[$index + 2];

      if ($option[0] == "THEIRBANISH") {
        $mod = explode("-", $theirBanish[$index + 1])[0];
        $action = IsPlayable($card, $turn[0], "BANISH", $index, player:$otherPlayer) ? 14 : 0;
        $borderColor = CardBorderColor($card, "BANISH", $action > 0, $mod);
        if($borderColor == 7) $label = "Playable";
        if (isFaceDownMod($source[$index + 1])) $card = "CardBack";
      }
      else if (substr($option[0], 0, 2) == "MY") $borderColor = 1;
      else if (substr($option[0], 0, 5) == "THEIR") $borderColor = 2;
      else if ($option[0] == "CC") $borderColor = ($combatChain[$index + 1] == $playerID ? 1 : 2);
      else if ($option[0] == "LAYER") {
        $borderColor = ($layers[$index + 1] == $playerID ? 1 : 2);
      }
      else if ($option[0] == "COMBATCHAINATTACKS") {
        $borderColor = 1;
      }
      if ($option[0] == "COMBATCHAINLINK"){
        $borderColor = ($combatChain[$index + 1] == $playerID ? 1 : 2);
      }

      if (($option[0] == "THEIRARS" && $theirArsenal[$index + 1] == "DOWN") || ($option[0] == "THEIRCHAR" && $theirCharacter[$option[1] + 12] == "DOWN")) {
        $card = $TheirCardBack;
        switch ($option[0]) {
          case "THEIRARS":
            $label = "Arsenal";
            break;
          case "THEIRCHAR":
            $label = "Equip-".CardSubType($theirCharacter[$option[1]]);
            break;
          default:
            break;
        }
      }

      //Show Life and Def counters on allies in the popups
      if ($option[0] == "THEIRALLY" || $option[0] == "MYALLY") {
        $lifeCounters = ($option[0] == "THEIRALLY") ? $theirAllies[$index + 2] : $myAllies[$index + 2];
        $enduranceCounters = ($option[0] == "THEIRALLY") ? $theirAllies[$index + 6] : $myAllies[$index + 6];
        $uniqueID = ($option[0] == "THEIRALLY") ? $theirAllies[$index + 5] : $myAllies[$index + 5];
        $attackCounters = 0;
        if (SearchCurrentTurnEffectsForUniqueID($uniqueID) != -1) {
            $attackCounters = EffectAttackModifier(SearchUniqueIDForCurrentTurnEffects($uniqueID)) + AttackValue(($option[0] == "THEIRALLY") ? $theirAllies[$index] : $myAllies[$index]);
        }
      }
      //Show Atk counters on Auras in the popups
      $atkCounters = ($option[0] == "THEIRAURAS" || $option[0] == "MYAURAS") ? ($option[0] == "THEIRAURAS" ? $theirAuras[$index + 3] : $myAuras[$index + 3]) : null;
      //Show various counters on Auras in the popups
      $counters = ($option[0] == "THEIRAURAS" || $option[0] == "MYAURAS") ? ($option[0] == "THEIRAURAS" ? $theirAuras[$index + 2] : $myAuras[$index + 2]) : null;
      //Show Steam Counters on items
      $steamCounters = ($option[0] == "THEIRITEMS" || $option[0] == "MYITEMS") ? ($option[0] == "THEIRITEMS" ? $theirItems[$index + 1] : $myItems[$index + 1]) : null;
      //Show Subtitles on MyDeck
      if(substr($turn[2], 0, 6) === "MYDECK"){
        $subtitles = "(You can click your deck to see its content during this card resolution)";
      }

      if ($maxCount < 2)
        array_push($cardsMultiZone, JSONRenderedCard($card, action: 16, overlay: 0, borderColor: $borderColor, counters: $counters, actionDataOverride: $options[$i], lifeCounters: $lifeCounters, defCounters: $enduranceCounters, atkCounters: $atkCounters, controller: $borderColor, label: $label, steamCounters: $steamCounters));
      else
        array_push($cardsMultiZone, JSONRenderedCard($card, actionDataOverride: $i - $countOffset));
    }
    if ($maxCount >= 2) {
      $formOptions = new stdClass();
      $formOptions->playerID = $playerID;
      $formOptions->caption = "Submit";
      $formOptions->mode = 19;
      $formOptions->maxNo = count($options);
      $playerInputPopup->formOptions = $formOptions;
      $choiceOptions = "checkbox";
      $playerInputPopup->choiceOptions = $choiceOptions;
    }
    $playerInputPopup->popup = CreatePopupAPI("CHOOSEMULTIZONE", [], 0, 1, GetPhaseHelptext(), 1, additionalComments: $subtitles,cardsArray: $cardsMultiZone);
  }

  if (($turn[0] == "MAYCHOOSEDECK" || $turn[0] == "CHOOSEDECK") && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    if (GetDQHelpText() != "-") $caption = GamestateUnsanitize(GetDQHelpText());
    else $caption = "Choose a card from your deck:";
    $playerInputPopup->popup = ChoosePopup($myDeck, $turn[2], 11, $caption, DeckPieces(), "(You can click your deck to see its content during this card resolution)");
  }

  if (($turn[0] == "MAYCHOOSETHEIRDECK" || $turn[0] == "CHOOSETHEIRDECK") && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    if (GetDQHelpText() != "-") $caption = GamestateUnsanitize(GetDQHelpText());
    else $caption = "Choose a card from your opponent deck:";
    $playerInputPopup->popup = ChoosePopup($theirDeck, $turn[2], 11, $caption, DeckPieces());
  }

  if ($turn[0] == "CHOOSEBANISH" && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    if (GetDQHelpText() != "-") $caption = GamestateUnsanitize(GetDQHelpText());
    else $caption = "Choose a card from your banish:";
    $playerInputPopup->popup = ChoosePopup($myBanish, $turn[2], 16, $caption, BanishPieces());
  }

  if (($turn[0] == "MAYCHOOSEARSENAL" || $turn[0] == "CHOOSEARSENAL" || $turn[0] == "CHOOSEARSENALCANCEL") && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    if (GetDQHelpText() != "-") $caption = GamestateUnsanitize(GetDQHelpText());
    else $caption = "Choose a card from your arsenal:";
    $playerInputPopup->popup = ChoosePopup($myArsenal, $turn[2], 16, $caption, ArsenalPieces());
  }

  if (($turn[0] == "CHOOSEPERMANENT" || $turn[0] == "MAYCHOOSEPERMANENT") && $turn[1] == $playerID) {
    $myPermanents = &GetPermanents($playerID);
    $playerInputPopup->active = true;
    $playerInputPopup->popup = ChoosePopup($myPermanents, $turn[2], 16, GetPhaseHelptext(), PermanentPieces());
  }

  if (($turn[0] == "CHOOSETHEIRHAND") && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    if (GetDQHelpText() != "-") $caption = GamestateUnsanitize(GetDQHelpText());
    else $caption = "Choose a card from your opponent's hand:";
    $playerInputPopup->popup = ChoosePopup($theirHand, $turn[2], 16, $caption);
  }

  if (($turn[0] == "CHOOSEMYAURA") && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    if (GetDQHelpText() != "-") $caption = GamestateUnsanitize(GetDQHelpText());
    else $caption = "Choose one of your auras:";
    $playerInputPopup->popup = ChoosePopup($myAuras, $turn[2], 16, $caption);
  }

  if (($turn[0] == "CHOOSEDISCARD" || $turn[0] == "MAYCHOOSEDISCARD" || $turn[0] == "CHOOSEDISCARDCANCEL") && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    if (GetDQHelpText() != "-") $caption = GamestateUnsanitize(GetDQHelpText());
    else $caption = "Choose a card from your graveyard:";
    $playerInputPopup->popup = ChoosePopup($myDiscard, $turn[2], 16, $caption);
  }

  if (($turn[0] == "MAYCHOOSETHEIRDISCARD") && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    if (GetDQHelpText() != "-") $caption = GamestateUnsanitize(GetDQHelpText());
    else $caption = "Choose a card from your opponent's graveyard:";
    $playerInputPopup->popup = ChoosePopup($theirDiscard, $turn[2], 16, $caption);
  }

  if (($turn[0] == "CHOOSECOMBATCHAIN" || $turn[0] == "MAYCHOOSECOMBATCHAIN") && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    if (GetDQHelpText() != "-") $caption = GamestateUnsanitize(GetDQHelpText());
    else $caption =  "Choose a card from the combat chain:";
    $playerInputPopup->popup = ChoosePopup($combatChain, $turn[2], 16, $caption, CombatChainPieces());
  }

  if ($turn[0] == "CHOOSECHARACTER" && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    if (GetDQHelpText() != "-") $caption = GamestateUnsanitize(GetDQHelpText());
    else $caption = "Choose a card from your character/equipment:";
    $playerInputPopup->popup = ChoosePopup($myCharacter, $turn[2], 16, $caption, CharacterPieces());
  }

  if ($turn[0] == "CHOOSETHEIRCHARACTER" && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    if (GetDQHelpText() != "-") $caption = GamestateUnsanitize(GetDQHelpText());
    else $caption = "Choose a card from your opponent character/equipment:";
    $playerInputPopup->popup = ChoosePopup($theirCharacter, $turn[2], 16, $caption, CharacterPieces());
  }

  if (($turn[0] == "MULTICHOOSETHEIRDISCARD" || $turn[0] == "MULTICHOOSEDISCARD" || $turn[0] == "MULTICHOOSEHAND" || $turn[0] == "MAYMULTICHOOSEHAND" || $turn[0] == "MULTICHOOSEDECK" || $turn[0] == "MULTICHOOSETEXT" || $turn[0] == "MAYMULTICHOOSETEXT" || $turn[0] == "MULTICHOOSETHEIRDECK" || $turn[0] == "MULTICHOOSEBANISH" || $turn[0] == "MULTICHOOSEITEMS" || $turn[0] == "MULTICHOOSESUBCARDS") && $currentPlayer == $playerID) {
    $playerInputPopup->active = true;
    $formOptions = new stdClass();
    $cardsArray = array();

    $content = "";
    $params = explode("-", $turn[2]);
    $options = explode(",", $params[1]);
    $maxNumber = intval($params[0]);
    $minNumber = count($params) > 2 ? intval($params[2]) : 0;
    $title = "Choose " . ($minNumber > 0 ? $maxNumber : "up to " . $maxNumber ) . " card" . ($maxNumber > 1 ? "s:" : ":");
    $subtitles = "";

    if($turn[0] == "MULTICHOOSEDECK"){
      $subtitles = "(You can click your deck to see its content during this card resolution)";
    }

    if(GetDQHelpText() != "-") $caption = GamestateUnsanitize(GetDQHelpText());
    else $caption = $title;

    $formOptions->playerID = $playerID;
    $formOptions->caption = "Submit";
    $formOptions->mode = 19;
    $formOptions->maxNo = count($options);
    $playerInputPopup->formOptions = $formOptions;

    $choiceOptions = "checkbox";
    $playerInputPopup->choiceOptions = $choiceOptions;

    if ($turn[0] == "MULTICHOOSETEXT" || $turn[0] == "MAYMULTICHOOSETEXT") {
      $multiChooseText = array();

      for ($i = 0; $i < count($options); ++$i) {
        array_push($multiChooseText, CreateCheckboxAPI($i, $i, -1, false, GamestateUnsanitize(strval($options[$i]))));
      }
      $playerInputPopup->popup =  CreatePopupAPI("MULTICHOOSE", [], 0, 1, $caption, 1, $content);
      $playerInputPopup->multiChooseText = $multiChooseText;
    } else {
      for ($i = 0; $i < count($options); ++$i) {
        if ($turn[0] == "MULTICHOOSEDISCARD") array_push($cardsArray, JSONRenderedCard($myDiscard[$options[$i]], actionDataOverride: $i));
        else if ($turn[0] == "MULTICHOOSETHEIRDISCARD") array_push($cardsArray, JSONRenderedCard($theirDiscard[$options[$i]], actionDataOverride: $i));
        else if ($turn[0] == "MULTICHOOSEHAND" || $turn[0] == "MAYMULTICHOOSEHAND") array_push($cardsArray, JSONRenderedCard($myHand[$options[$i]], actionDataOverride: $i));
        else if ($turn[0] == "MULTICHOOSEDECK") array_push($cardsArray, JSONRenderedCard($myDeck[$options[$i]], actionDataOverride: $i));
        else if ($turn[0] == "MULTICHOOSETHEIRDECK") array_push($cardsArray, JSONRenderedCard($theirDeck[$options[$i]], actionDataOverride: $i));
        else if ($turn[0] == "MULTICHOOSEBANISH") array_push($cardsArray, JSONRenderedCard($myBanish[$options[$i]], actionDataOverride: $i));
        else if ($turn[0] == "MULTICHOOSEITEMS") array_push($cardsArray, JSONRenderedCard($myItems[$options[$i]], overlay:$myItems[$options[$i]+2] != 2 ? 'disabled' : 'none', counters: $myItems[$options[$i]+1], actionDataOverride: $i));
        else if ($turn[0] == "MULTICHOOSESUBCARDS") array_push($cardsArray, JSONRenderedCard($options[$i], actionDataOverride: $i));
      }
      $playerInputPopup->popup = CreatePopupAPI("MULTICHOOSE", [], 0, 1, $caption, 1, additionalComments: $subtitles, cardsArray: $cardsArray);
    }
  }

  if ($turn[0] == "MULTISHOWCARDSDECK" && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    $cardsToShow = array();
    $options = explode(",", $turn[2]);
    $caption = GetDQHelpText() != "-" ? GamestateUnsanitize(GetDQHelpText()) : $title;    
    
    foreach ($options as $i => $option) {
      $cardsToShow[] = JSONRenderedCard($myDeck[$i], borderColor: $borderColor, actionDataOverride: $i);
    }
    
    $playerInputPopup->popup = CreatePopupAPI("OK", [], 0, 1, $caption, 1, cardsArray: $cardsToShow);
    array_push($playerInputButtons, CreateButtonAPI($playerID, "Ok", 99, "OK", "20px"));
  }

  if ($turn[0] == "MULTISHOWCARDSTHEIRDECK" && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    $cardsToShow = array();
    $options = explode(",", $turn[2]);
    $caption = GetDQHelpText() != "-" ? GamestateUnsanitize(GetDQHelpText()) : $title;    
    
    foreach ($options as $i => $option) {
      $cardsToShow[] = JSONRenderedCard($theirDeck[$i], borderColor: $borderColor, actionDataOverride: $i);
    }
    
    $playerInputPopup->popup = CreatePopupAPI("OK", [], 0, 1, $caption, 1, cardsArray: $cardsToShow);
    array_push($playerInputButtons, CreateButtonAPI($playerID, "Ok", 99, "OK", "20px"));
  }

  if (($turn[0] == "CHOOSEMYSOUL" || $turn[0] == "MAYCHOOSEMYSOUL") && $turn[1] == $playerID) {
    $playerInputPopup->active = true;
    if (GetDQHelpText() != "-") $caption = GamestateUnsanitize(GetDQHelpText());
    else $caption = "Choose one of your soul:";
    $playerInputPopup->popup = ChoosePopup($mySoul, $turn[2], 16, $caption, SoulPieces());
  }

  $playerInputPopup->buttons = $playerInputButtons;
  $response->playerInputPopUp = $playerInputPopup;
  $response->canPassPhase = (CanPassPhase($turn[0]) && $currentPlayer == $playerID) || (IsReplay() && $playerID == 3);

  $response->preventPassPrompt = "";
  // Prompt the player if they want to skip arsenal with cards in hand.
  if ((CanPassPhase($turn[0]) && $currentPlayer == $playerID) || (IsReplay() && $playerID == 3)) {
    if ($turn[0] == "ARS" && count($myHand) > 0 && !ArsenalFull($playerID) && !IsReplay()) {
      $response->preventPassPrompt = "Are you sure you want to skip arsenal?";
    }
  }

  // If both players have enabled chat, is true, else false
  $response->chatEnabled = intval(GetCachePiece($gameName, 15)) == 1 && intval(GetCachePiece($gameName, 16)) == 1 ? true : false;

  // encode and send it out
  echo json_encode($response);
  exit;
}

function PlayableCardBorderColor($cardID)
{
  if (HasReprise($cardID) && RepriseActive()) return 3;
  return 0;
}

function ChoosePopup($zone, $options, $mode, $caption = "", $zoneSize = 1, $additionalComments = "")
{
  $options = explode(",", $options);
  $cardList = array();

  for ($i = 0; $i < count($options); ++$i) {
    array_push($cardList, JSONRenderedCard($zone[$options[$i]], action: $mode, actionDataOverride: strval($options[$i])));
  }

  return CreatePopupAPI("CHOOSEZONE", [], 0, 1, $caption, 1, "", additionalComments: $additionalComments, cardsArray: $cardList);
}

function ItemOverlay($item, $isReady, $numUses)
{
  if ($item == "microprocessor_blue" && $numUses < 3) return 1;
  return ($isReady != 2 ? 1 : 0);
}

function GetPhaseHelptext()
{
  global $turn;
  $defaultText = "Choose " . TypeToPlay($turn[0]);
  return (GetDQHelpText() != "-" ? GamestateUnsanitize(GetDQHelpText()) : $defaultText);
}

function skipEffectUIStacking($cardID) {
  return $cardID != "shelter_from_the_storm_red" && $cardID != "calming_breeze_red";
}