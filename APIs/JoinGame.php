<?php

include_once "../WriteLog.php";
include_once "../Libraries/HTTPLibraries.php";
include_once "../Libraries/SHMOPLibraries.php";
include_once "../APIKeys/APIKeys.php";
include_once '../includes/functions.inc.php';
include_once '../includes/dbh.inc.php';

if (!function_exists("DelimStringContains")) {
  function DelimStringContains($str, $find, $partial=false)
  {
    $arr = explode(",", $str);
    for($i=0; $i<count($arr); ++$i)
    {
      if($partial && str_contains($arr[$i], $find)) return true;
      else if($arr[$i] == $find) return true;
    }
    return false;
  }
}

if (!function_exists("SubtypeContains")) {
  function SubtypeContains($cardID, $subtype)
  {
    $cardSubtype = CardSubtype($cardID);
    return DelimStringContains($cardSubtype, $subtype);
  }
}

if (!function_exists("TypeContains")) {
  function TypeContains($cardID, $type)
  {
    $cardType = CardType($cardID);
    return DelimStringContains($cardType, $type);
  }
}

SetHeaders();

$response = new stdClass();

session_start();
if (!isset($gameName)) {
  $_POST = json_decode(file_get_contents('php://input'), true);
  if($_POST == NULL) {
    $response->error = "Parameters were not passed";
    echo json_encode($response);
    exit;
  }
  $gameName = $_POST["gameName"];
}
if (!IsGameNameValid($gameName)) {
  $response->error = "Invalid game name.";
  echo (json_encode($response));
  exit;
}
if (!isset($playerID)) $playerID = intval($_POST["playerID"]);
if (!isset($deck)) $deck = TryPOST("deck"); //This is for limited game modes (see JoinGameInput.php)
if (!isset($decklink)) $decklink = TryPOST("fabdb", ""); //Deck builder decklink
if (!isset($decksToTry)) $decksToTry = TryPOST("decksToTry"); //This is only used if there's no favorite deck or decklink. 1 = ira
if (!isset($favoriteDeck)) $favoriteDeck = TryPOST("favoriteDeck", false); //Set this to true to save the provided deck link to your favorites
if (!isset($favoriteDeckLink)) $favoriteDeckLink = TryPOST("favoriteDecks", "0"); //This one is kind of weird. It's the favorite deck index, then the string "<fav>" then the favorite deck link
if (!isset($matchup)) $matchup = TryPOST("matchup", ""); //The matchup link
$starterDeck = false;

if ($matchup == "" && GetCachePiece($gameName, $playerID + 6) != "") {
  $response->error = "Another player has already joined the game.";
  echo (json_encode($response));
  exit;
}
if ($decklink == "" && $deck == "" && $favoriteDeckLink == "0") {
  $starterDeck = true;
  switch ($decksToTry) {
    case '1':
      $decklink = "https://fabrary.net/decks/01GJG7Z4WGWSZ95FY74KX4M557";
      break;
    default:
      $decklink = "https://fabrary.net/decks/01GJG7Z4WGWSZ95FY74KX4M557";
      break;
  }
}

if ($favoriteDeckLink != "0" && $decklink == "") $decklink = $favoriteDeckLink;

if ($deck == "" && !IsDeckLinkValid($decklink)) {
  $response->error = "Deck URL is not valid: " . $decklink;
  echo (json_encode($response));
  exit;
}

include "../HostFiles/Redirector.php";
include "../CardDictionary.php";
include "./APIParseGamefile.php";
include "../MenuFiles/WriteGamefile.php";

$joinerName = (isset($_SESSION["useruid"]) ? $_SESSION["useruid"] : "Player 2");
if(($playerID == 3 && $joinerName == "starmorgs") || (($p1uid == "zeni" || $p1uid == "rkhalid890") && $joinerName == "starmorgs") || ($p1uid == "starmorgs" && ($joinerName == "zeni" || $joinerName == "rkhalid890"))) {
  $response->error = "Unable to join this game.";
  WriteGameFile();
  echo (json_encode($response));
  exit;
}

if ($matchup == "" && $playerID == 2 && $gameStatus >= $MGS_Player2Joined) {
  if ($gameStatus >= $MGS_GameStarted) {
    $response->gameStarted = true;
  } else {
    $response->error = "Another player has already joined the game.";
  }
  WriteGameFile();
  echo (json_encode($response));
  exit;
}

$deckLoaded = false;

if ($decklink != "") {
  if ($playerID == 1) $p1DeckLink = $decklink;
  else if ($playerID == 2) $p2DeckLink = $decklink;
  $curl = curl_init();
  $isFaBDB = str_contains($decklink, "fabdb");
  $isFaBMeta = str_contains($decklink, "fabmeta");
  if ($isFaBDB) {
    $decklinkArr = explode("/", $decklink);
    $slug = $decklinkArr[count($decklinkArr) - 1];
    $apiLink = "https://api.fabdb.net/decks/" . $slug;
  } else if (str_contains($decklink, "fabrary")) {
    $headers = array(
      "x-api-key: " . $FaBraryKey,
      "Content-Type: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    $decklinkArr = explode("/", $decklink);
    $decklinkArr = explode("?", $decklinkArr[count($decklinkArr) - 1]);
    $slug = $decklinkArr[0];
    $apiLink = "https://atofkpq0x8.execute-api.us-east-2.amazonaws.com/prod/v1/decks/" . $slug;
    if ($matchup != "") $apiLink .= "?matchupId=" . $matchup;
  } else {
    $decklinkArr = explode("/", $decklink);
    $slug = $decklinkArr[count($decklinkArr) - 1];
    $apiLink = "https://api.fabmeta.net/deck/" . $slug;
  }
  $response->apiLink = $apiLink;
  curl_setopt($curl, CURLOPT_URL, $apiLink);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  $apiDeck = curl_exec($curl);
  $apiInfo = curl_getinfo($curl);
  curl_close($curl);

  if ($apiDeck === FALSE) {
    WriteGameFile();
    if(is_array($decklink)) $response->error = "Deckbuilder API for this deck returns no data: " . implode("/", $decklink);
    else $response->error = "Deckbuilder API for this deck returns no data: " . $decklink;
    echo (json_encode($response));
    exit;
  }
  $deckObj = json_decode($apiDeck);
  // if has message forbidden error out.
  if ($apiInfo['http_code'] == 403) {
    $response->error = "API FORBIDDEN! Invalid or missing token to access API: " . $apiLink . " The response from the deck hosting service was: " . $apiDeck;
    echo (json_encode($response));
    die();
  }
  if ($deckObj == null) {
    $response->error = 'Deck object is null. Failed to retrieve deck from API.';
    echo (json_encode($response));
    exit;
  }
  if(!isset($deckObj->{'name'}))
  {
    $response->error = 'Deck is invalid. Failed to retrieve deck from API.';
    echo (json_encode($response));
    exit;
  }
  $deckName = $deckObj->{'name'};
  if (isset($deckObj->{'matchups'})) {
    if ($playerID == 1) $p1Matchups = $deckObj->{'matchups'};
    else if ($playerID == 2) $p2Matchups = $deckObj->{'matchups'};
  }
  $deckFormat = (isset($deckObj->{'format'}) ? $deckObj->{'format'} : "");
  $cards = $deckObj->{'cards'};
  $deckCards = "";
  $sideboardCards = "";
  $headSideboard = "";
  $chestSideboard = "";
  $armsSideboard = "";
  $legsSideboard = "";
  $offhandSideboard = "";
  $quiverSideboard = "";
  $modularSideboard = "";
  $unsupportedCards = "";
  $bannedCard = "";
  $restrictedCard = "";
  $isDeckBlitzLegal = "";
  $isDeckCCLegal = "";
  $character = "";
  $head = "";
  $chest = "";
  $arms = "";
  $legs = "";
  $offhand = "";
  $quiver = "";
  $weapon1 = "";
  $weapon2 = "";
  $weaponSideboard = "";
  $totalCards = 0;
  $orderedSets = ["WTR", "ARC", "CRU", "MON", "ELE", "EVR", "UPR", "DYN", "OUT", "DTD", "TCC", "EVO", "HVY", "MST", "AKO", "ASB", "ROS", "AAZ", "TER", "AUR", "AIO", "AJV", "HNT"];

  if (is_countable($cards)) {
    for ($i = 0; $i < count($cards); ++$i) {
      $count = $cards[$i]->{'total'};
      $numSideboard = (isset($cards[$i]->{'sideboardTotal'}) ? $cards[$i]->{'sideboardTotal'} : 0);
      $id = GetCardId($cards[$i], $isFaBDB, $isFaBMeta, $orderedSets);
      if($id == "" && isset($cards[$i]->{'cardIdentifier'})) {
        $id = $cards[$i]->{'cardIdentifier'};
      }
      if ($id == "") continue;   
      ProcessCard($id, $count, $numSideboard, $isFaBDB, $totalCards, $modularSideboard, $unsupportedCards, $character, $weapon1, $weapon2, $weaponSideboard, $head, $headSideboard, $chest, $chestSideboard, $arms, $armsSideboard, $legs, $legsSideboard, $offhand, $offhandSideboard, $quiver, $quiverSideboard, $deckCards, $sideboardCards, $format, $character);

      if (IsCardBanned($id, $format, $character) && $format != "draft") {
        if ($bannedCard != "") $bannedCard .= ", ";
        $bannedCard .= PitchValue($id) > 0 ? CardName($id) . " (" . PitchValue($id) . ")" : CardName($id);
      }

      // Track the count of each card ID
      if (!isset($cardCounts[$id])) {
        $cardCounts[$id] = 0;
      }
      $cardCounts[$id] += $count;

      if(isCardRestricted($id, $format, $cardCounts[$id])) {
        if ($restrictedCard != "") $restrictedCard .= ", ";
        $restrictedCard .= PitchValue($id) > 0 ? CardName($id) . " (" . PitchValue($id) . ")" : CardName($id);
      }

      if($character != "brevant,_civic_protector" && $id != "chivalry_3") { //Exclude Brevant and Chivalry
        // Deck Check to make sure players don't run more than 2 copies of cards in Young Hero formats
        if (($format == "blitz" || $format == "compblitz" || $format == "openformatblitz" || $format == "clash") && $cardCounts[$id] > 2) {
          if ($isDeckBlitzLegal != "") $isDeckBlitzLegal .= ", ";
          $isDeckBlitzLegal .= PitchValue($id) > 0 ? CardName($id) . " (" . PitchValue($id) . ")" : CardName($id);
        }
      }
      // Deck Check to make sure players don't run more than 3 copies of cards in Classic Constructed formats
      if (($format == "cc" || $format == "compcc" || $format == "openformatcc" || $format == "llcc") && $cardCounts[$id] > 3) {
        if ($isDeckCCLegal != "") $isDeckCCLegal .= ", ";
        $isDeckCCLegal .= CardName($id);
      }
    }
    $deckLoaded = true;
  }

  if(!$deckLoaded) {
    $response->error = "⚠️ Error retrieving deck. Decklist link invalid.";
    echo(json_encode($response));
    exit;
  }

  if($isDeckBlitzLegal != "") {
    $response->error = "⚠️ The deck contains extra copies of cards and isn't legal: " . $isDeckBlitzLegal . ".";
    echo (json_encode($response));
    exit;
  }

  if($isDeckCCLegal != "") {
    $response->error = "⚠️ The deck contains extra copies of cards and isn't legal: " . $isDeckCCLegal . ".";
    echo (json_encode($response));
    exit;
  }

  if($unsupportedCards != "") {
    $response->error = "⚠️ The following cards are not yet supported: " . $unsupportedCards . ".";
    echo (json_encode($response));
    exit;
  }

  if (CharacterHealth($character) < 30 && ($format == "cc" || $format == "compcc" || $format == "openformatcc")) {
    $response->error = "⚠️ Young heroes are not legal in Classic Constructed: Young - " . CardName($character) . ".";
    echo (json_encode($response));
    exit;
  }

  if (CharacterHealth($character) >= 30 && ($format == "blitz" || $format == "compblitz" || $format == "clash" || $format == "openformatblitz")) {
    $response->error = "⚠️ Adult heroes are not legal in this format: " . CardName($character) . ".";
    echo (json_encode($response));
    exit;
  }

  if ($bannedCard != "") {
    $response->error = "⚠️ The following cards are not legal in this format: " . $bannedCard . ".";
    echo (json_encode($response));
    exit;
  }

  if ($restrictedCard != "") {
    $response->error = "⚠️ The following cards are restricted to up to 1 copy in this format: " . $restrictedCard . ".";
    echo (json_encode($response));
    exit;
  }

  if ($totalCards < 60  && ($format == "cc" || $format == "compcc")) {
    $response->error = "⚠️ The deck link you have entered has too few cards (" . $totalCards . ") and is likely for the blitz/commoner format. Please double-check your decklist link and try again.";
    echo (json_encode($response));
    exit;
  }

  if (($totalCards < 40 || $totalCards > 52) && ($format == "blitz" || $format == "compblitz" || $format == "commoner" || $format == "clash")) {
    $response->error = "⚠️ The deck link you have entered does not have 40 cards (" . $totalCards . ") and is likely for CC. Please double-check your decklist link and try again.";
    echo (json_encode($response));
    exit;
  }

  if ($totalCards > 80  && ($format == "cc" || $format == "compcc")) {
    $response->error = "⚠️ The deck link you have entered has too many cards (" . $totalCards . "). Please double-check your decklist link and try again.";
    echo (json_encode($response));
    exit;
  }

  //We have the decklist, now write to file
  $filename = "../Games/" . $gameName . "/p" . $playerID . "Deck.txt";
  $deckFile = fopen($filename, "w");
  $charString = $character;
  if ($weapon1 != "") $charString .= " " . $weapon1;
  if ($weapon2 != "") $charString .= " " . $weapon2;
  if ($offhand != "") $charString .= " " . $offhand;
  if ($quiver != "") $charString .= " " . $quiver;
  if ($head != "") $charString .= " " . $head;
  if ($chest != "") $charString .= " " . $chest;
  if ($arms != "") $charString .= " " . $arms;
  if ($legs != "") $charString .= " " . $legs;
  fwrite($deckFile, $charString . "\r\n");
  fwrite($deckFile, $deckCards . "\r\n");
  fwrite($deckFile, $headSideboard . "\r\n");
  fwrite($deckFile, $chestSideboard . "\r\n");
  fwrite($deckFile, $armsSideboard . "\r\n");
  fwrite($deckFile, $legsSideboard . "\r\n");
  fwrite($deckFile, $offhandSideboard . "\r\n");
  fwrite($deckFile, $weaponSideboard . "\r\n");
  fwrite($deckFile, $sideboardCards . "\r\n");
  fwrite($deckFile, $quiverSideboard . "\r\n");
  fwrite($deckFile, $modularSideboard);
  fclose($deckFile);
  copy($filename, "../Games/" . $gameName . "/p" . $playerID . "DeckOrig.txt");

  if (isset($_SESSION["userid"])) {
    include_once '../includes/functions.inc.php';
    include_once "../includes/dbh.inc.php";
    $deckbuilderID = GetDeckBuilderId($_SESSION["userid"], $decklink);
    if ($deckbuilderID != "") {
      if ($playerID == 1) $p1deckbuilderID = $deckbuilderID;
      else $p2deckbuilderID = $deckbuilderID;
    }
  }

  if ($favoriteDeck && isset($_SESSION["userid"])) {
    //Save deck
    include_once '../includes/functions.inc.php';
    include_once "../includes/dbh.inc.php";
    addFavoriteDeck($_SESSION["userid"], $decklink, $deckName, $character, $deckFormat);
  }
}

if (!isset($character) || $character == "") {
  $response->error = "There is no character. Something went wrong with parsing your deck.";
  echo (json_encode($response));
  exit;
}

if ($matchup == "") {
  if ($playerID == 2) {

    $gameStatus = $MGS_Player2Joined;
    if (file_exists("../Games/" . $gameName . "/gamestate.txt")) unlink("../Games/" . $gameName . "/gamestate.txt");

    $firstPlayerChooser = 1;
    $p1roll = 0;
    $p2roll = 0;
    $tries = 10;
    while ($p1roll == $p2roll && $tries > 0) {
      $p1roll = rand(1, 6) + rand(1, 6);
      $p2roll = rand(1, 6) + rand(1, 6);
      WriteLog("🎲 Player 1 rolled $p1roll and Player 2 rolled $p2roll.", path: "../");
      --$tries;
    }
    $firstPlayerChooser = ($p1roll > $p2roll ? 1 : 2);
    WriteLog("Player $firstPlayerChooser chooses who goes first.", path: "../");
    $gameStatus = $MGS_ChooseFirstPlayer;
    $joinerIP = $_SERVER['REMOTE_ADDR'];
  }

  if ($playerID == 1) {
    $p1uid = (isset($_SESSION["useruid"]) ? $_SESSION["useruid"] : "Player 1");
    $p1id = (isset($_SESSION["userid"]) ? $_SESSION["userid"] : "");
    $p1IsPatron = (isset($_SESSION["isPatron"]) || isset($_SESSION["isPvtVoidPatron"]) ? "1" : "");
    $p1ContentCreatorID = (isset($_SESSION["patreonEnum"]) ? $_SESSION["patreonEnum"] : "");
  } else if ($playerID == 2) {
    $p2uid = (isset($_SESSION["useruid"]) ? $_SESSION["useruid"] : "Player 2");
    $p2id = (isset($_SESSION["userid"]) ? $_SESSION["userid"] : "");
    $p2IsPatron = (isset($_SESSION["isPatron"]) || isset($_SESSION["isPvtVoidPatron"]) ? "1" : "");
    $p2ContentCreatorID = (isset($_SESSION["patreonEnum"]) ? $_SESSION["patreonEnum"] : "");
  }

  if ($playerID == 2) $p2Key = hash("sha256", rand() . rand() . rand());

  WriteGameFile();
  SetCachePiece($gameName, $playerID + 1, strval(round(microtime(true) * 1000)));
  SetCachePiece($gameName, $playerID + 3, "0");
  SetCachePiece($gameName, $playerID + 6, $character);
  SetCachePiece($gameName, 14, $gameStatus);
  GamestateUpdated($gameName);

  //$authKey = ($playerID == 1 ? $p1Key : $p2Key);
  //$_SESSION["authKey"] = $authKey;
  $domain = (!empty(getenv("DOMAIN")) ? getenv("DOMAIN") : "talishar.net");
  if ($playerID == 1) {
    $_SESSION["p1AuthKey"] = $p1Key;
    setcookie("lastAuthKey", $p1Key, time() + 86400, "/", $domain);
  } else if ($playerID == 2) {
    $_SESSION["p2AuthKey"] = $p2Key;
    setcookie("lastAuthKey", $p2Key, time() + 86400, "/", $domain);
  }
}

$response->message = "success";
$response->gameName = $gameName;
$response->playerID = $playerID;
$response->authKey = $playerID == 1 ? $p1Key : ($playerID == 2 ? $p2Key : '');
echo (json_encode($response));

session_write_close();

function GetAltCardID($cardID)
{
  switch ($cardID) {
    case "ironrot_helm": return "ironrot_helm";
    case "ironrot_plate": return "ironrot_plate";
    case "ironrot_gauntlet": return "ironrot_gauntlet";
    case "ironrot_legs": return "ironrot_legs";
    case "minerva_themis": return "minerva_themis";
    case "spell_fray_cloak": return "MON400";
    case "lord_sutcliffe": return "lord_sutcliffe";
    case "spell_fray_gloves": return "MON401";
    case "lady_barthimont": return "lady_barthimont";
    case "spell_fray_cloak": return "MON400";
    case "the_librarian": return "the_librarian";
    case "spell_fray_leggings": return "MON402";
    case "scar_for_a_scar_1": return "scar_for_a_scar_1";
    case "crazy_brew_3": return "crazy_brew_3";
    case "boneyard_marauder_1": return "boneyard_marauder_1";
    case "fate_foreseen_1": return "fate_foreseen_1";
    case "herald_of_protection_2": return "shield_bash_1";
    case "in_the_swing_1": return "in_the_swing_1";
    case "flock_of_the_feather_walkers_1": return "flock_of_the_feather_walkers_1";
    case "alpha_rampage_1": return "alpha_rampage_1";
    case "iyslander": return "iyslander";
    case "scar_for_a_scar_1": return "scar_for_a_scar_1";
    case "scar_for_a_scar_2": return "scar_for_a_scar_2";
    case "scar_for_a_scar_3": return "scar_for_a_scar_3";
    case "yoji,_royal_protector": return "yoji,_royal_protector";
    case "quicksilver_dagger": return "quicksilver_dagger";
    case "blessing_of_aether_1": return "blessing_of_aether_1";
    case "blessing_of_aether_2": return "blessing_of_aether_2";
    case "blessing_of_aether_3": return "blessing_of_aether_3";
    case "cracked_bauble_2": case "cracked_bauble_2": case "cracked_bauble_2": case "cracked_bauble_2": return "cracked_bauble_2";
    case "spell_fray_gloves": return "MON401";
    case "vantom_banshee_1": return "vantom_banshee_1";
    case "vantom_banshee_2": return "vantom_banshee_2";
    case "vantom_banshee_3": return "vantom_banshee_3";
    case "vynnset": return "vynnset";
    case "herald_of_judgment_2": return "herald_of_judgment_2";
    case "banneret_of_courage_2": return "banneret_of_courage_2";
    case "banneret_of_gallantry_2": return "banneret_of_gallantry_2";
    case "banneret_of_protection_2": return "banneret_of_protection_2";
    case "banneret_of_resilience_2": return "banneret_of_resilience_2";
    case "banneret_of_salvation_2": return "banneret_of_salvation_2";
    case "banneret_of_vigor_2": return "banneret_of_vigor_2";
    case "evo_tekloscope_3": return "evo_tekloscope_3";
    case "evo_energy_matrix_3": return "evo_energy_matrix_3";
    case "evo_scatter_shot_3": return "evo_scatter_shot_3";
    case "evo_rapid_fire_3": return "evo_rapid_fire_3";
    case "liquid-cooled_mayhem_1"; return "liquid-cooled_mayhem_1";
    case "hyper_driver_1": return "hyper_driver_1";
    case "firewall_1": return "firewall_1";
    case "firewall_2": return "firewall_2";
    case "firewall_3": return "firewall_3";
    case "under_loop_1": return "under_loop_1";
    case "proto_base_head": return "proto_base_head";
    case "proto_base_chest": return "proto_base_chest";
    case "proto_base_arms": return "proto_base_arms";
    case "proto_base_legs": return "proto_base_legs";
    case "sigil_of_solace_1": return "sigil_of_solace_1";
    case "star_fall": return "star_fall";
    case "heavy_industry_power_plant": return "heavy_industry_power_plant";
    case "the_hand_that_pulls_the_strings": return "the_hand_that_pulls_the_strings";
  }
  return $cardID;
}

function isClashLegal($cardID, $character) {
  $setID = SetID($cardID);
  $set = substr($setID, 0, 3);
  $number = intval(substr($setID, 3, 3));
  switch ($cardID) { //Special Use Promos
    case "taipanis,_dracai_of_judgement": case "proclamation_of_requisition": case "theryon,_magister_of_justice": case "proclamation_of_abundance": case "proclamation_of_abundance": case "proclamation_of_production": case "brutus,_summa_rudis": case "proclamation_of_combat": case "magrar":
      return true;
      default:
      break;
    }
  switch (CardType($cardID)) {
    case "C": case "M": case "W":
      return true;
    default: break;
  }
  if(IsSpecialization($cardID)) return true;
  if(Rarity($cardID) == "C" || Rarity($cardID) == "T" || Rarity($cardID) == "R") return true;
  if(($character == "emperor,_dracai_of_aesir" || $character == "") && $cardID == "command_and_conquer_1") return true; //C&C is legal for Emperor in Clash
  if(($character == "prism,_advent_of_thrones" || $character == "") && $set == "DTD" && $number >= 5 && $number <= 12) return true; //Figments are legal for Prism in Clash
  return false;
}

function IsCardBanned($cardID, $format, $character)
{
  $setID = SetID($cardID);
  $set = substr($setID, 0, 3);
  if ($format == "commoner" && (Rarity($cardID) != "C" && Rarity($cardID) != "T" && Rarity($cardID) != "R") && CardType($cardID) != "C" && $cardID != "springboard_somersault_2") return true;
  if ($format == "clash") return !isClashLegal($cardID, $character);

  //Ban spoiler cards in non-open-format
  if($format != "openformatcc" && $format != "openformatblitz" && $format != "openformatllcc" && isSpecialUsePromo($cardID)) return true;
  if(isBannedInFormat($cardID, $format)) return true;
  return false;
}

function isCardRestricted($cardID, $format, $count) {

  $restrictedCards = [
    "llcc" => [
      "crippling_crush_1", "oaken_old_1", "awakening_3", "hypothermia_3", "warmonger's_diplomacy_3", "bonds_of_ancestry_1", "bonds_of_ancestry_2", "bonds_of_ancestry_3", 
      "open_the_flood_gates_1", "open_the_flood_gates_2", "open_the_flood_gates_3", 
    ]
  ];

  return isset($restrictedCards[$format]) && in_array($cardID, $restrictedCards[$format]) && $count > 1;
}

function isSpecialUsePromo($cardID) {
  $specialUsePromos = [
      "taipanis,_dracai_of_judgement", "taipanis,_dracai_of_judgement", "proclamation_of_requisition", "proclamation_of_requisition", "gavel_of_natural_order", "theryon,_magister_of_justice", "theryon,_magister_of_justice", "proclamation_of_abundance",
      "proclamation_of_production", "brutus,_summa_rudis", "proclamation_of_combat", "magrar", "ruu'di,_gem_keeper", "go_bananas_2", "taylor", "yorick,_weaver_of_tales", "tales_of_adventure_3",
      "good_deeds_don't_go_unnoticed_2", "pink_visor", "diamond_hands", "hummingbird,_call_of_adventure", "shitty_xmas_present_2", "squizzy_&_floof"
  ];
  return in_array($cardID, $specialUsePromos);
}

function isBannedInFormat($cardID, $format) {
  if ($format == "compblitz") $format = "blitz";
  if ($format == "compcc") $format = "cc";

  $bannedCards = [
      "blitz" => [
          "rhinar", "tome_of_fyendal_2", "drone_of_brutality_1", "drone_of_brutality_2", "drone_of_brutality_3", "dash", "teklo_plasma_pistol", "viserai", "nebula_blade",
          "tome_of_aetherwind_1", "art_of_war_2", "awakening_3", "briar", "ball_lightning_1", "ball_lightning_2", "ball_lightning_3", "rosetta_thorn", "duskblade",
          "heartened_cross_strap", "bloodsheath_skeleta", "snapback_1", "snapback_2", "snapback_3", "cash_in_2", "tome_of_divinity_2", "stubby_hammerers", "mask_of_the_pouncing_lynx",
          "tome_of_firebrand_1", "iyslander", "iyslander", "kraken's_aethervein", "oldhim", "winter's_wail", "chane", "galaxxi_black", "kano",
          "crucible_of_aetherweave", "crucible_of_aetherweave", "kassai,_cintari_sellsword", "ira,_crimson_haze", "edge_of_autumn", "berserk_2", "bonds_of_ancestry_1", "bonds_of_ancestry_2", "bonds_of_ancestry_3",
          "victor_goldmane", "miller's_grindstone", "orihon_of_mystic_tenets_3", "belittle_1", "belittle_2", "belittle_3"
      ],
      "cc" => [
          "tome_of_fyendal_2", "drone_of_brutality_1", "drone_of_brutality_2", "drone_of_brutality_3", "tome_of_aetherwind_1", "art_of_war_2", "plunder_run_1", "plunder_run_2", "plunder_run_3",
          "bloodsheath_skeleta", "cash_in_2", "prism,_sculptor_of_arc_light", "luminaris", "tome_of_divinity_2", "chane,_bound_by_shadow", "galaxxi_black", "stubby_hammerers", "belittle_1",
          "belittle_2", "belittle_3", "awakening_3", "ball_lightning_1", "ball_lightning_2", "ball_lightning_3", "duskblade", "crown_of_seeds", "lexi,_livewire",
          "voltaire,_strike_twice", "briar,_warden_of_thorns", "rosetta_thorn", "oldhim,_grandfather_of_eternity", "winter's_wail", "bravo,_star_of_the_show", "dromai,_ash_artist", "storm_of_sandikai", "tome_of_firebrand_1",
          "iyslander,_stormbind", "kraken's_aethervein", "berserk_2", "bonds_of_ancestry_1", "bonds_of_ancestry_2", "bonds_of_ancestry_3", "orihon_of_mystic_tenets_3", "high_octane_1", "count_your_blessings_3"
      ],
      "commoner" => [
          "amulet_of_ice_3", "ball_lightning_1", "ball_lightning_2", "ball_lightning_3", "belittle_1", "belittle_2", "belittle_3", "aether_ironweave"
      ],
      "llcc" => [
          "kraken's_aethervein"
      ]
  ];

  return isset($bannedCards[$format]) && in_array($cardID, $bannedCards[$format]);
}

function ReverseArt($cardID)
{
  switch ($cardID) {
    case "harmonized_kodachi": return "harmonized_kodachi";
    case "mandible_claw": return "mandible_claw";
    case "zephyr_needle": return "zephyr_needle";
    case "cintari_saber": return "cintari_saber";
    case "quicksilver_dagger": return "quicksilver_dagger";
    case "spider's_bite": return "spider's_bite";
    case "nerve_scalpel": return "nerve_scalpel";
    case "orbitoclast": return "orbitoclast";
    case "scale_peeler": return "scale_peeler";
    default:
      return $cardID;
  }
}

function GetCardId($card, $isFaBDB, $isFaBMeta, $orderedSets) {
  if ($isFaBDB) {
      $printings = $card->{'printings'};
      $printing = $printings[0];
      $sku = $printing->{'sku'};
      return explode("-", $sku->{'sku'})[0];
  } elseif ($isFaBMeta) {
      return $card->{'identifier'};
  } elseif (isset($card->{'identifier'})) {
      return str_replace("-", "_", $card->{'identifier'});
  } elseif (isset($card->{'cardIdentifier'})) {
      return $card->{'cardIdentifier'};
  }
  return "";
}

function ProcessCard($id, $count, $numSideboard, $isFaBDB, &$totalCards, &$modularSideboard, &$unsupportedCards, &$character, &$weapon1, &$weapon2, &$weaponSideboard, &$head, &$headSideboard, &$chest, &$chestSideboard, &$arms, &$armsSideboard, &$legs, &$legsSideboard, &$offhand, &$offhandSideboard, &$quiver, &$quiverSideboard, &$deckCards, &$sideboardCards) {
  // $id = GetAltCardID($id);
  $cardName = CardName($id); 
  if ($cardName == "") {
      if ($unsupportedCards != "") $unsupportedCards .= " ";
      $unsupportedCards .= $id;
      return;
  }

  $numMainBoard = ($isFaBDB ? $count - $numSideboard : $count);

  if (IsModular($id)) {
      for ($j = 0; $j < $numMainBoard + $numSideboard; ++$j) {
          if ($modularSideboard != "") $modularSideboard .= " ";
          $modularSideboard .= $id;
      }
      $totalCards += $numMainBoard + $numSideboard;
  } elseif (TypeContains($id, "C")) {
      $character = $id;
  } elseif (TypeContains($id, "W")) {
      ++$totalCards;
      for ($j = 0; $j < $numMainBoard; ++$j) {
          if ($j > 0) $id = ReverseArt($id);
          if ($weapon1 == "") $weapon1 = $id;
          elseif ($weapon2 == "") $weapon2 = $id;
          else {
              if ($weaponSideboard != "") $weaponSideboard .= " ";
              $weaponSideboard .= $id;
          }
      }
      for ($j = 0; $j < $numSideboard; ++$j) {
          if ($numMainBoard > 0 || $j > 0) $id = ReverseArt($id);
          if ($weaponSideboard != "") $weaponSideboard .= " ";
          $weaponSideboard .= $id;
      }
  } elseif (TypeContains($id, "E")) {
      ++$totalCards;
      ProcessEquipment($id, $numMainBoard, $numSideboard, $head, $headSideboard, $chest, $chestSideboard, $arms, $armsSideboard, $legs, $legsSideboard, $offhand, $offhandSideboard, $quiver, $quiverSideboard);
  } else {
      for ($j = 0; $j < $numMainBoard; ++$j) {
          if ($deckCards != "") $deckCards .= " ";
          $deckCards .= $id;
      }
      for ($j = 0; $j < $numSideboard; ++$j) {
          if ($sideboardCards != "") $sideboardCards .= " ";
          $sideboardCards .= $id;
      }
      $totalCards += $numMainBoard + $numSideboard;
  }
}

function ProcessEquipment($id, $numMainBoard, $numSideboard, &$head, &$headSideboard, &$chest, &$chestSideboard, &$arms, &$armsSideboard, &$legs, &$legsSideboard, &$offhand, &$offhandSideboard, &$quiver, &$quiverSideboard) {
  if (SubtypeContains($id, "Head")) {
      for ($j = 0; $j < $numMainBoard; ++$j) {
          if ($head == "") $head = $id;
          else {
              if ($headSideboard != "") $headSideboard .= " ";
              $headSideboard .= $id;
          }
      }
      for ($j = 0; $j < $numSideboard; ++$j) {
          if ($headSideboard != "") $headSideboard .= " ";
          $headSideboard .= $id;
      }
  } elseif (SubtypeContains($id, "Chest")) {
      for ($j = 0; $j < $numMainBoard; ++$j) {
          if ($chest == "") $chest = $id;
          else {
              if ($chestSideboard != "") $chestSideboard .= " ";
              $chestSideboard .= $id;
          }
      }
      for ($j = 0; $j < $numSideboard; ++$j) {
          if ($chestSideboard != "") $chestSideboard .= " ";
          $chestSideboard .= $id;
      }
  } elseif (SubtypeContains($id, "Arms")) {
      for ($j = 0; $j < $numMainBoard; ++$j) {
          if ($arms == "") $arms = $id;
          else {
              if ($armsSideboard != "") $armsSideboard .= " ";
              $armsSideboard .= $id;
          }
      }
      for ($j = 0; $j < $numSideboard; ++$j) {
          if ($armsSideboard != "") $armsSideboard .= " ";
          $armsSideboard .= $id;
      }
  } elseif (SubtypeContains($id, "Legs")) {
      for ($j = 0; $j < $numMainBoard; ++$j) {
          if ($legs == "") $legs = $id;
          else {
              if ($legsSideboard != "") $legsSideboard .= " ";
              $legsSideboard .= $id;
          }
      }
      for ($j = 0; $j < $numSideboard; ++$j) {
          if ($legsSideboard != "") $legsSideboard .= " ";
          $legsSideboard .= $id;
      }
  } elseif (SubtypeContains($id, "Off-Hand")) {
      for ($j = 0; $j < $numMainBoard; ++$j) {
          if ($offhand == "") $offhand = $id;
          else {
              if ($offhandSideboard != "") $offhandSideboard .= " ";
              $offhandSideboard .= $id;
          }
      }
      for ($j = 0; $j < $numSideboard; ++$j) {
          if ($offhandSideboard != "") $offhandSideboard .= " ";
          $offhandSideboard .= $id;
      }
  } elseif (SubtypeContains($id, "Quiver")) {
      for ($j = 0; $j < $numMainBoard; ++$j) {
          if ($quiver == "") $quiver = $id;
          else {
              if ($quiverSideboard != "") $quiverSideboard .= " ";
              $quiverSideboard .= $id;
          }
      }
      for ($j = 0; $j < $numSideboard; ++$j) {
          if ($quiverSideboard != "") $quiverSideboard .= " ";
          $quiverSideboard .= $id;
      }
  }
}