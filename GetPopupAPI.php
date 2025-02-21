<?php

include "./Libraries/HTTPLibraries.php";

$gameName = $_GET["gameName"];
if (!IsGameNameValid($gameName)) {
  echo ("Invalid game name.");
  exit;
}
$playerID = $_GET["playerID"];
$authKey = TryGet("authKey", "");
$popupType = $_GET["popupType"];
$chainLinkIndex = TryGet("index", "");

ob_start();
include "./Libraries/SHMOPLibraries.php";
include "./ParseGamestate.php";
include "./GameLogic.php";
include "./Libraries/UILibraries.php";
include "./Libraries/StatFunctions.php";
include "./Libraries/PlayerSettings.php";
include_once 'Assets/patreon-php-master/src/PatreonDictionary.php';
include "./GameTerms.php";
include "./HostFiles/Redirector.php";
include_once "./includes/dbh.inc.php";
include_once "./includes/functions.inc.php";
ob_end_clean();

session_start();

if($playerID == 3) {
  include_once "./AccountFiles/AccountSessionAPI.php";
  if(!IsUserLoggedIn()) {
    loginFromCookie();
    if(!IsUserLoggedIn()) {
      $response->error = "You must be logged in to spectate games";
      echo (json_encode($response));
      exit;
    }
  }
  //Now audit the spectate
  $userID = LoggedInUser();
  $conn = GetDBConnection();
  if ($conn->connect_error) {
    $response->error = "Database connection failed: " . $conn->connect_error;
    echo (json_encode($response));
    exit;
  }
  $query = "UPDATE users SET numSpectates = numSpectates + 1 WHERE usersId = ?";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $userID);
  $stmt->execute();
  $stmt->close();
}

// CORS etc *must* be set for all endpoints
SetHeaders();

$cardSize = 120;
$params = explode("-", $popupType);
$popupType = $params[0];
$response = new stdClass();
switch ($popupType) {
  case "attackSummary":
    $totalAttack = 0;
    $totalDefense = 0;
    $attackModifiers = [];
    $response->Cards = array();
    EvaluateCombatChain($totalAttack, $totalDefense, $attackModifiers);
    for ($i = 0; $i < count($attackModifiers); $i += 2) {
      $thisModifier = new stdClass();
      $idArr = explode("-", $attackModifiers[$i]);
      $cardID = ExtractCardID($idArr[0]);
      $bonus = $attackModifiers[$i + 1];
      if ($bonus == 0) continue;
      $effectName = CardName($cardID);
      if($effectName == "")
      {
        $effectName = $cardID;
        $cardID = "";
      }
      $thisModifier->Player = $mainPlayer;
      $thisModifier->Name = $effectName;
      $thisModifier->cardID = $cardID;
      $thisModifier->modifier = $bonus;
      array_push($response->Cards, $thisModifier);
    }
    break;
  case "myPitchPopup":
    JSONPopup($response, $myPitch, PitchPieces());
    break;
  case "myDiscardPopup":
    JSONPopup($response, $myDiscard, DiscardPieces());
    break;
  case "myBanishPopup":
    JSONPopup($response, $myBanish, BanishPieces());
    break;
  case "myDeckPopup":
    JSONPopup($response, $myDeck, DeckPieces());
    break;
  case "myStatsPopup":
    if($turn[0] == "OVER") SetCachePiece($gameName, 14, 99);//$MGS_GameOver
    echo(SerializeGameResult($playerID, "", file_get_contents("./Games/" . $gameName . "/p" . $playerID . "Deck.txt"), $gameName, includeFullLog:true));
    exit;
  case "mySoulPopup":
    JSONPopup($response, $mySoul, SoulPieces());
    break;
  case "theirBanishPopup":
    JSONPopup($response, $theirBanish, BanishPieces());
    break;
  case "theirPitchPopup":
    JSONPopup($response, $theirPitch, PitchPieces());
    break;
  case "theirDiscardPopup":
    JSONPopup($response, $theirDiscard, DiscardPieces());
    break;
  case "theirSoulPopup":
    JSONPopup($response, $theirSoul, SoulPieces());
    break;
  case "chainLinkPopup":
    $popupIndex = intval(($chainLinkIndex ?? $params[1]));
    $response = ChainLinkObject($popupIndex);
    $index = $popupIndex * ChainLinkSummaryPieces();
    if (isset($chainLinkSummary[$index])) {
        $response->TotalDamageDealt = $chainLinkSummary[$index];
    } else {
        $response->TotalDamageDealt = 0;
    }
    break;
  case "mySettings":
    global $SET_AlwaysHoldPriority, $SET_TryUI2, $SET_DarkMode, $SET_ManualMode, $SET_SkipARs, $SET_SkipDRs;
    global $SET_PassDRStep, $SET_AutotargetArcane, $SET_ColorblindMode, $SET_ShortcutAttackThreshold, $SET_EnableDynamicScaling;
    global $SET_Mute, $SET_Cardback, $SET_IsPatron, $SET_MuteChat, $SET_DisableStats, $SET_CasterMode, $SET_StreamerMode;
    global $SET_Playmat, $SET_AlwaysAllowUndo, $SET_DisableAltArts, $SET_ManualTunic;
    $response->Settings = array();
    AddSetting($response->Settings, "HoldPrioritySetting", $SET_AlwaysHoldPriority);
    AddSetting($response->Settings, "TryReactUI", $SET_TryUI2);
    AddSetting($response->Settings, "DarkMode", $SET_DarkMode);
    AddSetting($response->Settings, "ManualMode", $SET_ManualMode);
    AddSetting($response->Settings, "SkipARWindow", $SET_SkipARs);
    AddSetting($response->Settings, "SkipDRWindow", $SET_SkipDRs);
    AddSetting($response->Settings, "AutoTargetOpponent", $SET_AutotargetArcane);
    AddSetting($response->Settings, "ColorblindMode", $SET_ColorblindMode);
    AddSetting($response->Settings, "ShortcutAttackThreshold", $SET_ShortcutAttackThreshold);
    AddSetting($response->Settings, "MuteSound", $SET_Mute);
    AddSetting($response->Settings, "CardBack", $SET_Cardback);
    AddSetting($response->Settings, "IsPatron", $SET_IsPatron);
    AddSetting($response->Settings, "MuteChat", $SET_MuteChat);
    AddSetting($response->Settings, "DisableStats", $SET_DisableStats);
    AddSetting($response->Settings, "DisableAltArts", $SET_DisableAltArts);
    AddSetting($response->Settings, "IsCasterMode", $SET_CasterMode);
    AddSetting($response->Settings, "IsStreamerMode", $SET_StreamerMode);
    AddSetting($response->Settings, "Playmat", $SET_Playmat);
    AddSetting($response->Settings, "AlwaysAllowUndo", $SET_AlwaysAllowUndo);
    AddSetting($response->Settings, "ManualTunic", $SET_ManualTunic);
    $response->isSpectatingEnabled = GetCachePiece($gameName, 9) == "1";
    break;
  default:
    break;
}

echo json_encode($response);

function AddSetting(&$response, $name, $setting)
{
  global $playerID;
  $mySettings = &GetSettings($playerID);
  $thisSetting = new stdClass();
  $thisSetting->name = $name;
  $thisSetting->value = $mySettings[$setting];
  array_push($response, $thisSetting);
}

function JSONPopup($response, $zone, $zonePieces)
{
  $response->cards = array();
  for($i=0; $i<count($zone); $i+=$zonePieces)
  {
    array_push($response->cards, JSONRenderedCard($zone[$i]));
  }
}

function ChainLinkObject($link)
{
  global $chainLinks, $mainPlayer, $defPlayer;
  $chainLink = new stdClass();
  $chainLink->Cards = array();
  if (!is_array($chainLinks) || empty($chainLinks[$link])) {
    return $chainLink;
  }
  for ($i = 0; $i < count($chainLinks[$link]); $i += ChainLinksPieces()) {
    $card = new stdClass();
    $card->Player = $chainLinks[$link][$i+1];
    if ($chainLinks[$link][$i + 1] == $mainPlayer && CardType($chainLinks[$link][$i]) != "AR")
    {
      $attackValue = AttackValue($chainLinks[$link][$i]) + $chainLinks[$link][$i + 4];
    }
    elseif ($chainLinks[$link][$i + 1] == $mainPlayer && (CardType($chainLinks[$link][$i]) == "AR" || DelimStringContains(CardType($chainLinks[$link][$i]), "I")))
    {
      $attackValue = AttackModifier($chainLinks[$link][$i]);
    }
    else $attackValue = 0;

    if ($chainLinks[$link][$i + 1] == $defPlayer) $blockValue = BlockValue($chainLinks[$link][$i]) + $chainLinks[$link][$i + 5];
    else $blockValue = 0;

    if($card->Player == $mainPlayer) $card->modifier = $attackValue;
    else $card->modifier = $blockValue;
    $card->cardID = $chainLinks[$link][$i];
    $card->Name = CardName($chainLinks[$link][$i]);

    array_push($chainLink->Cards, $card);
  }
  return $chainLink;
}
