<?php

include "HostFiles/Redirector.php";
include_once "AccountFiles/AccountSessionAPI.php";


array_push($layerPriority, ShouldHoldPriority(1));
array_push($layerPriority, ShouldHoldPriority(2));

$p1Char = &GetPlayerCharacter(1);
$p2Char = &GetPlayerCharacter(2);
$p1H = &GetHealth(1);
$p2H = &GetHealth(2);
$p1H = CharacterHealth($p1Char[0]);
$p2H = CharacterHealth($p2Char[0]);
$format = is_numeric($format) ? FormatName($format) : $format;

if ($p1StartingHealth != "") $p1H = $p1StartingHealth;

$fullLog = "../Games/" . $gameName . "/fullGamelog.txt";
if (!file_exists($fullLog)) $fullLog = "../Games/" . $gameName . "/fullGamelog.txt";
if (file_exists($fullLog)) {
  $handler = fopen($fullLog, "w+");
  fwrite($handler, "Player $firstPlayer is the first player and will begin play" . "\r\n");
  fclose($handler);
}

$mainPlayer = $firstPlayer;
$currentPlayer = $firstPlayer;
$otherPlayer = ($currentPlayer == 1 ? 2 : 1);
StatsStartTurn();

$MakeStartTurnBackup = false;
$MakeStartGameBackup = false;

if ($p2Char[0] == "DUMMY") {
  SetCachePiece($gameName, 3, "99999999999999");
}

//roguelike gamemode powers
if (CardSet($p2Char[0]) == "ROG") {
  $deck = &GetDeck(1);
  $powers = SearchDeck(1, "", "Power");
  if (strlen($powers) != 0) {
    $powersArray = explode(",", $powers);
    for ($i = count($powersArray) - 1; $i >= 0; --$i) {
      PutPermanentIntoPlay(1, $deck[$powersArray[$i]]);
      array_splice($deck, $powersArray[$i], 1);
    }
  }
  ROGUEPowerStart();
}

//Dummy - Single Player
if ($p2Char[0] == "DUMMY") {
  $cards = ["combustible_courier_red", "zipper_hit_red", "over_loop_blue", "microprocessor_blue", "induction_chamber_red", "zero_to_sixty_red"];
  AddGraveyard($cards[rand(0, 5)], 2, "DECK");
  AddGraveyard($cards[rand(0, 5)], 2, "DECK");
  AddGraveyard($cards[rand(0, 5)], 2, "DECK");
  AddGraveyard($cards[rand(0, 5)], 2, "DECK");
  AddGraveyard($cards[rand(0, 5)], 2, "DECK");
}

//CR 2.0 4.1.5b Meta-static abilities affecting deck composition
//Dash
$p1IsDash = $p1Char[0] == "dash_inventor_extraordinaire" || $p1Char[0] == "dash";
$p2IsDash = $p2Char[0] == "dash_inventor_extraordinaire" || $p2Char[0] == "dash";
if ($p1IsDash) {
  $items = SearchDeck(1, "", "Item", 2, -1, "MECHANOLOGIST");//Player 1, max cost 2
  AddDecisionQueue("CHOOSEDECK", 1, $items);
  AddDecisionQueue("SETDQVAR", 1, "0");
}
if ($p2IsDash) {
  $items = SearchDeck(2, "", "Item", 2, -1, "MECHANOLOGIST");//Player 2, max cost 2
  AddDecisionQueue("CHOOSEDECK", 2, $items);
  AddDecisionQueue("SETDQVAR", 2, "1");
}
//Actually put the item into play after each has chosen to prevent unfair advantage
if ($p1IsDash) {
  AddDecisionQueue("PASSPARAMETER", 1, "{0}");
  AddDecisionQueue("PUTPLAY", 1, "-");
}
if ($p2IsDash) {
  AddDecisionQueue("PASSPARAMETER", 2, "{1}");
  AddDecisionQueue("PUTPLAY", 2, "-");
}

//Fai
if ($p1Char[0] == "fai_rising_rebellion" || $p1Char[0] == "fai") {
  $cards = SearchDeckForCard(1, "phoenix_flame_red");
  if ($cards != "") {
    AddDecisionQueue("MAYCHOOSEDECK", 1, $cards);
    AddDecisionQueue("ADDDISCARD", 1, "DECK", 1);
  }
}
if ($p2Char[0] == "fai_rising_rebellion" || $p2Char[0] == "fai") {
  $cards = SearchDeckForCard(2, "phoenix_flame_red");
  if ($cards != "") {
    AddDecisionQueue("MAYCHOOSEDECK", 2, $cards);
    AddDecisionQueue("ADDDISCARD", 2, "DECK", 1);
  }
}

//Crown of Dominion
if (SearchCharacterForCard(1, "crown_of_dominion")) {
  AddDecisionQueue("PASSPARAMETER", 1, "gold");
  AddDecisionQueue("PUTPLAY", 1, "-");
}
if (SearchCharacterForCard(2, "crown_of_dominion")) {
  AddDecisionQueue("PASSPARAMETER", 2, "gold");
  AddDecisionQueue("PUTPLAY", 2, "-");
}

//Seasoned Saviour
if (($index = FindCharacterIndex(1, "seasoned_saviour")) > 0) {
  $p1Char[$index + 4] = -2;
}
if (($index = FindCharacterIndex(2, "seasoned_saviour")) > 0) {
  $p2Char[$index + 4] = -2;
}

//Barbed Castaway
if (($index = FindCharacterIndex(1, "barbed_castaway")) > 0) {
  AddCurrentTurnEffect("barbed_castaway-Load", 1);
  AddCurrentTurnEffect("barbed_castaway-Aim", 1);
}
if (($index = FindCharacterIndex(2, "barbed_castaway")) > 0) {
  AddCurrentTurnEffect("barbed_castaway-Load", 2);
  AddCurrentTurnEffect("barbed_castaway-Aim", 2);
}

//Victor
if (SearchCharacterForCard(1, "victor_goldmane_high_and_mighty") || SearchCharacterForCard(1, "victor_goldmane")) {
  AddDecisionQueue("ADDCURRENTEFFECT", 1, $p1Char[0] . "-1", 1);
}
if (SearchCharacterForCard(2, "victor_goldmane_high_and_mighty") || SearchCharacterForCard(2, "victor_goldmane")) {
  AddDecisionQueue("ADDCURRENTEFFECT", 2, $p2Char[0] . "-1", 1);
}

//Aria Sanctuary for Rosseta Limited
/* if($format == "draft"){
  AddDecisionQueue("PASSPARAMETER", 1, "sanctuary_of_aria");
  AddDecisionQueue("PUTPLAY", 1, "-");
  AddDecisionQueue("PASSPARAMETER", 2, "sanctuary_of_aria");
  AddDecisionQueue("PUTPLAY", 2, "-");
} */

InventoryStartGameAbilities(1);
InventoryStartGameAbilities(2);

//Cogwerx equipments
EquipWithSteamCounter("cogwerx_base_head", $p1Char, $p2Char);
EquipWithSteamCounter("cogwerx_base_chest", $p1Char, $p2Char);
EquipWithSteamCounter("cogwerx_base_arms", $p1Char, $p2Char);
EquipWithSteamCounter("cogwerx_base_legs", $p1Char, $p2Char);

//Quickshot Apprentice
if ($p2Char[0] == "ROGUE016") {
  $p2Hand = &GetHand(2);
  array_unshift($p2Hand, "searing_shot_red");
}
if ($p2Char[0] == "ROGUE025") {
  $options = array("ROGUE801", "ROGUE803", "ROGUE805");
  PutPermanentIntoPlay(0, $options[rand(0, count($options) - 1)]);
}

if ($p2Char[0] == "ROGUE008") {
  PutPermanentIntoPlay(0, "ROGUE601");
  PutPermanentIntoPlay(0, "ROGUE603");
  PutPermanentIntoPlay(0, "ROGUE803");
}

AddDecisionQueue("SHUFFLEDECK", 1, "SKIPSEED"); //CR 2.0 4.1.7 Shuffle Deck
AddDecisionQueue("SHUFFLEDECK", 2, "SKIPSEED"); //CR 2.0 4.1.7 Shuffle Deck
AddDecisionQueue("DRAWTOINTELLECT", 1, "-"); //CR 2.0 4.1.9 Draw to Intellect
AddDecisionQueue("DRAWTOINTELLECT", 2, "-"); //CR 2.0 4.1.9 Draw to Intellect
AddDecisionQueue("STARTGAME", $mainPlayer, "-"); //CR ?? Start Game
AddDecisionQueue("STARTTURNABILITIES", $mainPlayer, "-"); //CR 2.0 4.2 Start Phase

ProcessDecisionQueue();
CombatDummyAI(); //Only does anything if applicable
DoGamestateUpdate();
include "WriteGamestate.php";

if ($MakeStartTurnBackup) MakeStartTurnBackup();
if ($MakeStartGameBackup) MakeGamestateBackup("origGamestate.txt");

function EquipWithSteamCounter($cardID, &$p1Char, &$p2Char)
{
  if (($index = FindCharacterIndex(1, $cardID)) > 0) $p1Char[$index + 2] += 1;
  if (($index = FindCharacterIndex(2, $cardID)) > 0) $p2Char[$index + 2] += 1;
}

function InventoryStartGameAbilities($player)
{
  global $p1Inventory, $p2Inventory;
  $inventory = $player == 1 ? $p1Inventory : $p2Inventory;
  for ($i = 0; $i < count($inventory); $i += InventoryPieces()) {
    switch ($inventory[$i]) {
      case "levia_redeemed":
        PutPermanentIntoPlay($player, "levia_redeemed");
        array_push($inventory, "blasmophet_levia_consumed");
        break;
      case "adaptive_plating":
        AddDecisionQueue("LISTEMPTYEQUIPSLOTS", $player, "-");
        AddDecisionQueue("SETDQVAR", $player, "0", 1);
        AddDecisionQueue("SETDQCONTEXT", $player, "Choose where to equip your " . CardLink($inventory[$i], $inventory[$i]), 1);
        AddDecisionQueue("BUTTONINPUT", $player, "{0},None", 1);
        AddDecisionQueue("MODAL", $player, "ADAPTIVEPLATING", 1);
        AddDecisionQueue("SHOWMODES", $player, "adaptive_plating", 1);
        break;
      case "adaptive_dissolver":
        AddDecisionQueue("LISTEMPTYEQUIPSLOTS", $player, "-");
        AddDecisionQueue("SETDQVAR", $player, "0", 1);
        AddDecisionQueue("SETDQCONTEXT", $player, "Choose where to equip your " . CardLink($inventory[$i], $inventory[$i]), 1);
        AddDecisionQueue("BUTTONINPUT", $player, "{0},None", 1);
        AddDecisionQueue("MODAL", $player, "ADAPTIVEDISSOLVER", 1);
        AddDecisionQueue("SHOWMODES", $player, "adaptive_dissolver", 1);
        break;
      default:
        break;
    }
  }
}
