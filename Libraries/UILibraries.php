<?php

use JetBrains\PhpStorm\Language;

require_once("CoreLibraries.php");

$isReactFE = false;

//0 Card number = card ID (e.g. heart_of_fyendal_blue = Heart of Fyendal)
//1 action = (ProcessInput mode)
//2 overlay = 0 is none, 1 is grayed out/disabled
//3 borderColor = Border Color
//4 Counters = number of counters
//5 actionDataOverride = The value to give to ProcessInput
//6 lifeCounters = Number of life counters
//7 defCounters = Number of defense counters
//8 atkCounters = Number of attack counters
//9 controller = Player that controls it
//10 type = card type
//11 sType = card subtype
//12 restriction = something preventing the card from being played (or "" if nothing)
//13 isBroken = 1 if card is destroyed
//14 onChain = 1 if card is on combat chain (mostly for equipment)
//15 isFrozen = 1 if frozen
//16 shows gem = (0, 1, 2) (0 off, 1 active, 2 inactive)
function JSONRenderedCard(
  $cardNumber,
  $action = NULL,
  $overlay = NULL,
  $borderColor = NULL,
  $counters = NULL, // deprecated
  $actionDataOverride = NULL,
  $lifeCounters = NULL, // deprecated
  $defCounters = NULL, // deprecated
  $atkCounters = NULL, // deprecated
  $controller = NULL,
  $type = NULL,
  $sType = NULL,
  $restriction = NULL,
  $isBroken = NULL,
  $onChain = NULL,
  $isFrozen = NULL,
  $gem = NULL,
  $countersMap = new stdClass(), // new object for counters
  $label = NULL,
  $facing = NULL,
  $numUses = NULL,
  $subcard = NULL,
  $steamCounters = NULL,
  $energyCounters = NULL,
  $hauntCounters = NULL,
  $verseCounters = NULL,
  $doomCounters = NULL,
  $lessonCounters = NULL,
  $rustCounters = NULL,
  $flowCounters = NULL,
  $frostCounters = NULL,
  $balanceCounters = NULL,
  $bindCounters = NULL,
  $stainCounters = NULL,
  $lightningPlayed = NULL,
  $showAmpAmount = false,
  $marked = NULL
) {
  global $playerID, $CS_NumLightningPlayed;
  $isSpectator = (isset($playerID) && intval($playerID) == 3 ? true : false);
  $otherPlayer = ($playerID == 1 ? 2 : 1);

  $counters = property_exists($countersMap, 'counters') ? $countersMap->counters : $counters;
  if($counters != NULL) $countersMap->counters = $counters;
  $lifeCounters = property_exists($countersMap, 'life') ? $countersMap->life : $lifeCounters;
  if($lifeCounters != NULL) $countersMap->life = $lifeCounters;
  $defCounters = property_exists($countersMap, 'defence') ? $countersMap->defence : $defCounters;
  if($defCounters != NULL) $countersMap->defence = $defCounters;
  $atkCounters = property_exists($countersMap, 'attack') ? $atkCounters->attack : $atkCounters;
  if($atkCounters != NULL) $countersMap->attack = $atkCounters;
  $steamCounters = property_exists($countersMap, 'steam') ? $steamCounters->steam : $steamCounters;
  if($steamCounters != NULL) $countersMap->steam = $steamCounters;
  $energyCounters = property_exists($countersMap, 'energy') ? $energyCounters->energy : $energyCounters;
  if($energyCounters != NULL) $countersMap->steam = $steamCounters;
  $hauntCounters = property_exists($countersMap, 'haunt') ? $hauntCounters->haunt : $hauntCounters;
  if($hauntCounters != NULL) $countersMap->energy = $energyCounters;
  $verseCounters = property_exists($countersMap, 'verse') ? $verseCounters->verse : $verseCounters;
  if($verseCounters != NULL) $countersMap->verse = $verseCounters;
  $doomCounters = property_exists($countersMap, 'doom') ? $doomCounters->doom : $doomCounters;
  if($doomCounters != NULL) $countersMap->doom = $doomCounters;
  $lessonCounters = property_exists($countersMap, 'lesson') ? $lessonCounters->lesson : $lessonCounters;
  if($lessonCounters != NULL) $countersMap->lesson = $lessonCounters;
  $rustCounters = property_exists($countersMap, 'rust') ? $rustCounters->rust : $rustCounters;
  if($rustCounters != NULL) $countersMap->rust = $rustCounters;
  $flowCounters = property_exists($countersMap, 'flow') ? $flowCounters->flow : $flowCounters;
  if($flowCounters != NULL) $countersMap->flow = $flowCounters;
  $frostCounters = property_exists($countersMap, 'frost') ? $frostCounters->frost : $frostCounters;
  if($frostCounters != NULL) $countersMap->frost = $frostCounters;
  $balanceCounters = property_exists($countersMap, 'balance') ? $balanceCounters->balance : $balanceCounters;
  if($balanceCounters != NULL) $countersMap->balance = $balanceCounters;
  $bindCounters = property_exists($countersMap, 'bind') ? $bindCounters->bind : $bindCounters;
  if($bindCounters != NULL) $countersMap->bind = $bindCounters;
  $stainCounters = property_exists($countersMap, 'stain') ? $stainCounters->stain : $stainCounters;
  if($stainCounters != NULL) $countersMap->stain = $stainCounters;

  if(property_exists($countersMap, 'counters') && $countersMap->counters > 0) {
    $class = CardClass($cardNumber);
    $type = CardType($cardNumber);
    $subtype = CardSubType($cardNumber);
    if ($class == "MECHANOLOGIST" && ($subtype == "Item" || CardType($cardNumber) == "W")) {
      $countersMap->steam = $countersMap->counters;
      $countersMap->counters = 0;
    } 
    else if(IsEnergyCounters($cardNumber)){
      $countersMap->energy = $countersMap->counters;
      $countersMap->counters = 0;
    } 
    else if(HasHauntCounters($cardNumber)){
      $countersMap->haunt = $countersMap->counters;
      $countersMap->counters = 0;
    } 
    else if(HasVerseCounters($cardNumber)){
      $countersMap->verse = $countersMap->counters;
      $countersMap->counters = 0;
    } 
    else if(HasDoomCounters($cardNumber)) {
      $countersMap->doom = $countersMap->counters;
      $countersMap->counters = 0;
    }
    else if ($type == "M") {
      $countersMap->lesson = $countersMap->counters;
      $countersMap->counters = 0;
    } 
    else if(HasRustCounters($cardNumber)) {
      $countersMap->rust = $countersMap->counters;
      $countersMap->counters = 0;
    }
    else if(HasFlowCounters($cardNumber)) {
      $countersMap->flow = $countersMap->counters;
      $countersMap->counters = 0;
    }
    else if(HasFrostCounters($cardNumber)) {
      $countersMap->frost = $countersMap->counters;
      $countersMap->counters = 0;
    }
    else if(HasBalanceCounters($cardNumber)) {
      $countersMap->balance = $countersMap->counters;
      $countersMap->counters = 0;
    }
    else if(HasBindCounters($cardNumber)) {
      $countersMap->bind = $countersMap->counters;
      $countersMap->counters = 0;
    }
    else if(HasStainCounters($cardNumber)) {
      $countersMap->stain = $countersMap->counters;
      $countersMap->counters = 0;
    }
    else if ($type == "E") {
      if (EquipmentsUsingSteamCounter($cardNumber)) {
        $countersMap->steam = $countersMap->counters;
        $countersMap->counters = 0;
      }
    } 
    else if ($subtype == "Arrow") {
      $countersMap->aim = $countersMap->counters;
      $countersMap->counters = 0;
    } 
  }
  //Volzar Amp icon
  if($controller != NULL){
    if($cardNumber == "volzar_the_lightning_rod" && $controller == $playerID && GetClassState($playerID, $CS_NumLightningPlayed) > 0 && $lightningPlayed == NULL) {
      $countersMap->lightning = GetClassState($playerID, $CS_NumLightningPlayed);
      $countersMap->counters = 0;
    }
    if($cardNumber == "volzar_the_lightning_rod" && $controller == $otherPlayer && GetClassState($otherPlayer, $CS_NumLightningPlayed) > 0 && $lightningPlayed == NULL) {
      $countersMap->lightning = GetClassState($otherPlayer, $CS_NumLightningPlayed);
      $countersMap->counters = 0;
    }
  }

  //Current Turn Effects amp amount
  if(substr($showAmpAmount, 0, 6) == "Effect") {
    $index = explode("-", $showAmpAmount)[1];
    if(ArcaneModifierAmount($cardNumber, $playerID, $index) > 0) {
      $countersMap->amp = ArcaneModifierAmount($cardNumber, $playerID, $index);
      $countersMap->counters = 0;
    } 
    if(ArcaneModifierAmount($cardNumber, $otherPlayer, $index) > 0) {
      $countersMap->amp = ArcaneModifierAmount($cardNumber, $otherPlayer, $index);
      $countersMap->counters = 0;
    }   
  }
  
  if($isSpectator) $gem = NULL;
  if($subcard != NULL) {
    $subcard = explode(',', $subcard);
  }

  $card = new stdClass();

  if($gem !== NULL) $card->gem = $gem;
  if($cardNumber !== NULL) $card->cardNumber = $cardNumber;
  if($action !== NULL) $card->action = $action;
  if($overlay !== NULL) $card->overlay = $overlay;
  if($borderColor !== NULL) $card->borderColor = $borderColor;
  if($counters !== NULL) $card->counters = $counters;
  if($actionDataOverride !== NULL) $card->actionDataOverride = $actionDataOverride;
  if($lifeCounters !== NULL) $card->lifeCounters = $lifeCounters;
  if($defCounters !== NULL) $card->defCounters = $defCounters;
  if($atkCounters !== NULL) $card->atkCounters = $atkCounters;
  if($steamCounters !== NULL) $card->steamCounters = $steamCounters;
  if($controller !== NULL) $card->controller = $controller;
  if($type !== NULL) $card->type = $type;
  if($sType !== NULL) $card->sType = $sType;
  if($restriction !== NULL) $card->restriction = $restriction;
  if($isBroken !== NULL) $card->isBroken = $isBroken;
  if($onChain !== NULL) $card->onChain = $onChain;
  if($isFrozen !== NULL) $card->isFrozen = $isFrozen;
  if($countersMap != json_decode('{}')) $card->countersMap = $countersMap;
  if($label !== NULL) $card->label = $label;
  if($facing !== NULL) $card->facing = $facing;
  if($numUses !== NULL) $card->numUses = $numUses;
  if($subcard !== NULL) $card->subcards = $subcard;
  if($marked !== NULL) $card->marked = $marked;
  return $card;
}

function CreateButtonAPI($playerID, $caption, $mode, $input, $size = null, $image = null, $tooltip = null, $fullRefresh = false, $fullReload = false, $prompt = null)
{
  $button = new stdClass();
  $button->mode = $mode;
  $button->buttonInput = $input;
  $button->fullRefresh = $fullRefresh;
  $button->prompt = $prompt;
  $button->imgURL = $image;
  $button->tooltip = $tooltip;
  $button->caption = $caption;
  $button->sizeOverride = $size;
  $button->fullReload = $fullReload;
  return $button;
}

function ProcessInputLink($player, $mode, $input, $event = 'onmousedown', $fullRefresh = false, $prompt = "")
{
  $jsCode = "SubmitInput(\"" . $mode . "\", \"&buttonInput=" . $input . "\", " . $fullRefresh . ");";
  // If a prompt is given, surround the code with a "confirm()" call
  if ($prompt != "")
    $jsCode = "if (confirm(\"" . $prompt . "\")) { " . $jsCode . " }";

  return " " . $event . "='" . $jsCode . "'";
}

function CreateCheckboxAPI($input, $value, $immediateSubmitMode = -1, $defaultChecked = false, $label = "&nbsp;", $fullRefresh = false)
{
  $option = new stdClass();
  global $playerID;
  $submitLink = "";
  if ($immediateSubmitMode != -1) $submitLink = ProcessInputLink($playerID, $immediateSubmitMode, $input, "onchange", $fullRefresh);
  $option->submitLink = $submitLink;
  $option->input = $input;
  $option->value = $value;
  $option->check = $defaultChecked;
  $option->label = $label;
  return $option;
}

function CreatePopupAPI($id, $fromArr, $canClose, $defaultState = 0, $title = "", $arrElements = 1, $customInput = "", $path = "./", $big = false, $overCombatChain = false, $additionalComments = "", $size = 0, $cardsArray = [], $topCards = [], $bottomCards = [] )
{
  $result = new stdClass();
  $result->size = $size;
  $result->big = $big;
  $result->overCombatChain = $overCombatChain;
  $result->id = $id;
  $result->title = $title;
  $result->canClose = $canClose;
  $result->additionalComments = $additionalComments;
  $cards = array();
  for ($i = 0; $i < count($fromArr); $i += $arrElements) {
    array_push($cards, JSONRenderedCard($fromArr[$i]));
  }
  if (count($cardsArray) > 0) {
    $cards = $cardsArray;
  }
  $result->cards = $cards;
  $result->topCards = $topCards;
  $result->bottomCards = $bottomCards;
  $result->customInput = $customInput;
  return $result;
}

function CardBorderColor($cardID, $from, $isPlayable, $mod = "-")
{
  global $turn;
  if ($turn[0] == "B") return ($isPlayable ? 6 : 0);
  if ($from == "BANISH") {
    if ($isPlayable || PlayableFromBanish($cardID, $mod)) return 7;
    if (HasBloodDebt($cardID)) return 2;
    if ($isPlayable && HasReprise($cardID) && RepriseActive()) return 5;
    if ($isPlayable && ComboActive($cardID)) return 5;
    if ($isPlayable && HasRupture($cardID) && RuptureActive(true)) return 5;
    return 0;
  }
  if ($from == "GY") {
    if ($isPlayable || PlayableFromGraveyard($cardID)) return 7;
    return 0;
  }
  if ($isPlayable && ComboActive($cardID)) return 3;
  if ($isPlayable && HasReprise($cardID) && RepriseActive()) return 3;
  if ($isPlayable && HasRupture($cardID) && RuptureActive(true, (CardType($cardID) != "AA"))) return 3;
  else if ($isPlayable) return 6;
  return 0;
}

function CardLink($caption, $cardNumber, $recordMenu = false)
{
  global $darkMode, $playerID, $isReactFE;
  if ($isReactFE) {
    return "{{" . $cardNumber . "|" . CardName($cardNumber) . "|" . PitchValue($cardNumber) . "}}";
  }

  $name = CardName($cardNumber);
  if ($name == "") return "";
  $pitchValue = PitchValue($cardNumber);
  $pitchText = "";
  if ($recordMenu) {
    $color = "#DDD";
  } else {
    switch ($pitchValue) {
      case 3:
        $color = "#009DDF";
        $pitchText = " (3)";
        break;
      case 2:
        $color = "GoldenRod";
        $pitchText = " (2)";
        break;
      case 1:
        $color = "#AF1518";
        $pitchText = " (1)";
        break;
      default:
        if ($darkMode) {
          $color = "#1a1a1a";
          break;
        } else {
          $color = "#888";
          break;
        }
    }
  }
  if (function_exists("IsColorblindMode") && !IsColorblindMode($playerID)) $pitchText = "";
  $file = "'./" . "WebpImages" . "/" . $cardNumber . ".webp'";
  return "<b><span style='color:" . $color . "; cursor:default;' onmouseover=\"ShowDetail(event," . $file . ")\" onmouseout='HideCardDetail()'>" . $name . $pitchText . "</span></b>";
}

function GetTheirBanishForDisplay($playerID)
{
  global $theirBanish;
  $TheirCardBack = GetCardBack($playerID == 1 ? 2 : 1);
  $banish = array();
  for ($i = 0; $i < count($theirBanish); $i += BanishPieces()) {
    if (isFaceDownMod($theirBanish[$i + 1])) array_push($banish, $TheirCardBack);
    else array_push($banish, $theirBanish[$i]);
  }
  return $banish;
}

function GetMyBanishForDisplay($playerID)
{
  global $myBanish;
  $myCardBack = GetCardBack($playerID == 1 ? 1 : 2);
  $banish = array();
  for ($i = 0; $i < count($myBanish); $i += BanishPieces()) {
    if (isFaceDownMod($myBanish[$i + 1])) array_push($banish, $myCardBack);
    else array_push($banish, $myBanish[$i]);
  }
  return $banish;
}

function isFaceDownMod($mod)
{
  return $mod == "INT" || $mod == "FACEDOWN" || $mod == "UZURI" || $mod == "NTSTONERAIN" || $mod == "STONERAIN" || $mod == "TRAPDOOR";
}
