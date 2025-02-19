<?php

function PlayAura($cardID, $player, $number = 1, $isToken = false, $rogueHeronSpecial = false, $numAttackCounters = 0, $from = "-", $additionalCosts = "-", $effectController = "-", $effectSource = "-")
{
  global $CS_NumAuras, $EffectContext, $defPlayer, $CS_FealtyCreated;
  $otherPlayer = ($player == 1 ? 2 : 1);
  $cardID = IdentifierToCardID($cardID); //SCAFFOLDING
  if ($effectController == "-") $effectController = $player;
  if (TypeContains($cardID, "T", $player)) $isToken = true;
  if (DelimStringContains(CardSubType($cardID), "Affliction")) {
    $otherPlayer = $player;
    $player = ($player == 1 ? 2 : 1);
  }
  $auras = &GetAuras($player);
  $numMinusTokens = 0;
  $numMinusTokens = CountCurrentTurnEffects("HVY209", $player) + CountCurrentTurnEffects("HVY209", $otherPlayer);
  $effectSource = $effectSource == "-" ? $EffectContext : $effectSource;
  // only modify the event if there is an event
  if ($number > 0) $number += CharacterModifiesPlayAura($player, $isToken, $effectController);

  if ($numMinusTokens > 0 && $isToken && (TypeContains($effectSource, "AA", $player) || TypeContains($effectSource, "A", $player))) $number -= $numMinusTokens;
  if ($cardID == "ARC112") $number += CountCurrentTurnEffects("ARC081", $player);
  if ($cardID == "MON104") {
    $index = SearchArsenalReadyCard($player, "MON404");
    if ($index > -1) TheLibrarianEffect($player, $index);
  }
  if ($cardID == "MST031") SearchCardList($additionalCosts, $player, subtype: "Chi") != "" ? $numAttackCounters += 4 : $numAttackCounters += 2;
  if ($cardID == "MST043" || $cardID == "MST044" || $cardID == "MST045") $numAttackCounters += SearchPitchForColor($player, 3) > 0 ? 1 : 0;
  if (ClassContains($cardID, "ILLUSIONIST", $player) && SearchCurrentTurnEffects("MST155", $player, true) && CardCost($cardID, $from) <= 2 && CardCost($cardID, $from) > -1) {
    ++$numAttackCounters;
  }
  if (ClassContains($cardID, "ILLUSIONIST", $player) && SearchCurrentTurnEffects("MST156", $player, true) && CardCost($cardID, $from) <= 1 && CardCost($cardID, $from) > -1) {
    ++$numAttackCounters;
  }
  if (ClassContains($cardID, "ILLUSIONIST", $player) && SearchCurrentTurnEffects("MST157", $player, true) && CardCost($cardID, $from) <= 0 && CardCost($cardID, $from) > -1) {
    ++$numAttackCounters;
  }

  $myHoldState = AuraDefaultHoldTriggerState($cardID);
  if ($myHoldState == 0 && HoldPrioritySetting($player) == 1) $myHoldState = 1;
  $theirHoldState = AuraDefaultHoldTriggerState($cardID);
  if ($theirHoldState == 0 && HoldPrioritySetting($otherPlayer) == 1) $theirHoldState = 1;

  for ($i = 0; $i < $number; ++$i) {
    array_push($auras, $cardID);
    array_push($auras, 2); //Status
    if ($rogueHeronSpecial) array_push($auras, 0); //Only happens on the damage effect of the Heron Master in the Roguelike Gamemode
    else array_push($auras, AuraPlayCounters($cardID)); //Miscellaneous Counters
    array_push($auras, $numAttackCounters); //Attack counters
    array_push($auras, ($isToken ? 1 : 0)); //Is token 0=No, 1=Yes
    array_push($auras, AuraNumUses($cardID));
    array_push($auras, GetUniqueId($cardID, $player));
    array_push($auras, $myHoldState); //My Hold priority for triggers setting 2=Always hold, 1=Hold, 0=Don't hold
    array_push($auras, $theirHoldState); //Opponent Hold priority for triggers setting 2=Always hold, 1=Hold, 0=Don't hold
    array_push($auras, $from);
  }
  if (DelimStringContains(CardSubType($cardID), "Affliction")) IncrementClassState($otherPlayer, $CS_NumAuras, $number);
  else if (DelimStringContains(CardSubType($EffectContext), "Trap") || CardType($EffectContext) == "DR") IncrementClassState($defPlayer, $CS_NumAuras, $number);
  else if (CreatesAuraForOpponent($EffectContext)) IncrementClassState($effectController, $CS_NumAuras, $number);
  else if ($cardID != "ELE111") IncrementClassState($player, $CS_NumAuras, $number);
  if ($cardID == "HNT167") IncrementClassState($player, $CS_FealtyCreated, $number);
}

//cards that instruct the player to create an aura under their opponent's control
//it still "counts" as the player creating an aura
function CreatesAuraForOpponent($cardID)
{
  return match($cardID) {
    "OUT021" => true,
    "OUT022" => true,
    "OUT023" => true,
    "OUT024" => true,
    "OUT025" => true,
    "OUT026" => true,
    "OUT036" => true,
    "OUT037" => true,
    "OUT038" => true,
    "OUT039" => true,
    "OUT040" => true,
    "OUT041" => true,
    default => false
  };
}

function AuraNumUses($cardID)
{
  switch ($cardID) {
    case "EVR140":
    case "EVR141":
    case "EVR142":
    case "EVR143":
    case "UPR005":
    case "DTD081":
    case "ROS077":
    case "ROS130":
    case "ROS131":
    case "ROS132":
    case "HNT256":
      return 1;
    default:
      return 0;
  }
}

function TokenCopyAura($player, $index)
{
  $auras = &GetAuras($player);
  PlayAura($auras[$index], $player, 1, true);
  PlayAbility($auras[$index], "TRIGGER", 0);
}

function AuraDestroyed($player, $cardID, $isToken = false, $from = "HAND")
{
  global $EffectContext;
  $auras = &GetAuras($player);
  for ($i = 0; $i < count($auras); $i += AuraPieces()) {
    $EffectContext = $auras[$i];
    switch ($auras[$i]) {
      case "EVR141":
        if (!$isToken && $auras[$i + 5] > 0 && ClassContains($cardID, "ILLUSIONIST", $player)) {
          --$auras[$i + 5];
          AddLayer("TRIGGER", $player, $auras[$i], "-", "-", $auras[$i + 6]);
        }
        break;
      case "EVO244":
        if (ClassContains($cardID, "ILLUSIONIST", $player)) PhantomTidemawDestroy($player, $i);
        break;
      default:
        break;
    }
  }
  $goesWhere = GoesWhereAfterResolving($cardID, $from);
  $numMercifulRetribution = SearchCount(SearchAurasForCard("MON012", $player)) + ($cardID == "MON012" ? 1 : 0);
  if ($numMercifulRetribution > 0 && TalentContains($cardID, "LIGHT", $player)) {
    AddDecisionQueue("PASSPARAMETER", $player, $cardID, 1);
    AddDecisionQueue("ADDSOUL", $player, "PLAY", 1);
    $goesWhere = "-";
  }
  for ($i = 0; $i < $numMercifulRetribution; ++$i) {
    if (CardType($cardID) != "T" && $isToken) WriteLog("<span style='color:red;'>The card is not put in your soul from Merciful Retribution because it is a token copy</span>");
    AddDecisionQueue("ADDTRIGGER", $player, "MON012," . $cardID);
  }
  if (HasWard($cardID, $player) && !$isToken) WardPoppedAbility($player, $cardID);
  if (CardType($cardID) == "T" || $isToken) return;//Don't need to add to anywhere if it's a token
  ResolveGoesWhere($goesWhere, $cardID, $player, "PLAY");
}

function AuraLeavesPlay($player, $index, $uniqueID, $location = "AURAS")
{
  global $mainPlayer;
  $auras = &GetAurasLocation($player, $location);
  $auraConstants = AuraLocationConstants($location);
  $uniqueIDIndex = $auraConstants[1];
  $cardID = $auras[$index];
  $uniqueID = $auras[$index + $uniqueIDIndex];
  $otherPlayer = ($player == 1 ? 2 : 1);
  switch ($cardID) {
    case "DYN072":
      $char = &GetPlayerCharacter($player);
      for ($j = 0; $j < count($char); $j += CharacterPieces()) {
        if (CardSubType($char[$j]) == "Sword") $char[$j + 3] = 0;
      }
      break;
    case "DYN221":
    case "DYN222":
    case "DYN223":
      $banish = new Banish($otherPlayer);
      $banishCard = $banish->FirstCardWithModifier($cardID . "-" . $uniqueID);
      if ($banishCard == null) break;
      $banishIndex = $banishCard->Index();
      if ($banishIndex > -1) PlayAura($banish->Remove($banishIndex), $otherPlayer);
      break;
    case "MST040":
    case "MST041":
    case "MST042":
      AddLayer("TRIGGER", $player, $cardID, "-", "-", $uniqueID);
      break;
    case "MST137":
    case "MST138":
    case "MST139":
      $illusionistAuras = SearchAura($player, class: "ILLUSIONIST");
      $aurasArray = explode(",", $illusionistAuras);
      if (count($aurasArray) <= 1) AddLayer("TRIGGER", $player, $cardID, "-", "-", $uniqueID);
      break;
    case "MST140":
    case "MST141":
    case "MST142":
      AddLayer("TRIGGER", $player, $cardID, "-", "-", $uniqueID);
      break;
    case "MST155":
    case "MST156":
    case "MST157":
      $illusionistAuras = SearchAura($player, class: "ILLUSIONIST");
      $aurasArray = explode(",", $illusionistAuras);
      if (count($aurasArray) <= 1) AddLayer("TRIGGER", $player, $cardID, "-", "-", $uniqueID);
      break;
    case "ROS022": //sigil of brilliance
      AddDecisionQueue("DRAW", $player, "-", 0);
      break;
    case "ROS045":
      WriteLog(CardLink($cardID, $cardID) . " created an " . CardLink("ELE109", "ELE109"));
      PlayAura("ELE109", $player);
      break;
    case "ROS070": //sigil of earth
      PlayAura("ELE109", $player);
      break;
    case "ROS088":
      WriteLog(CardLink($cardID, $cardID) . " created an " . CardLink("ELE110", "ELE110"));
      PlayAura("ELE110", $player);
      break;
    case "ROS113": //sigil of lightning
      PlayAura("ELE110", $player);
      break;
    case "ROS133":
      $deck = new Deck($player);
      if ($deck->Reveal()) {
        if (CardType($deck->Top()) == "AA") {
          AddPlayerHand($deck->Top(true), $player, "DECK");
        }
      }
      break;
    case "ROS152":
    case "ROS153":
    case "ROS154":
      if($mainPlayer == $player) { // main player is the attacking player, these being equal would mean that it is "your turn" to Arcane Cussing
        AddLayer("TRIGGER", $player, $cardID);
      }
      break;
    case "ROS161":
      PlayAura("ARC112", $player);
      break;
    case "ROS168"://Sigil of aether
      AddLayer("TRIGGER", $player, $cardID, "-", "Arcane", $uniqueID);
      break;
    case "ROS182":
      $deck = new Deck($player);
      $newCardID = $deck->Top();
      $mod = (DelimStringContains(CardType($newCardID), "A") ? "INST" : "-");
      BanishCardForPlayer($newCardID, $player, "DECK", $mod);
      $deck->Remove(0);
      break;
    case "ROS210":
      PlayAura("DYN244", $player);
      break;
    case "ROS230":
      GainHealth(1, $player);
      break;
    case "ROS226":
      AddDecisionQueue("FINDINDICES", $player, "HAND");
      AddDecisionQueue("CHOOSEHAND", $player, "<-", 1);
      AddDecisionQueue("REMOVEMYHAND", $player, "-", 1);
      AddDecisionQueue("DISCARDCARD", $player, "HAND-" . $player, 1);
      AddDecisionQueue("DRAW", $player, "-", 0);
      break;
    default:
      break;
  }
  if (SearchCurrentTurnEffects("ROS163", $player) && DelimStringContains(CardName($cardID), "Sigil", partial: true)){
    AddLayer("TRIGGER", $player, "ROS163");
  }
}

function AuraPlayCounters($cardID)
{
  switch ($cardID) {
    case "CRU075":
      return 1;
    case "EVR107":
    case "ROS130":
      return 3;
    case "EVR108":
    case "ROS131":
      return 2;
    case "EVR109":
    case "ROS132":
      return 1;
    case "UPR140":
      return 3;
    default:
      return 0;
  }
}

function DestroyAuraUniqueID($player, $uniqueID, $location = "AURAS")
{
  $index = $location == "AURAS" ? SearchAurasForUniqueID($uniqueID, $player) : SearchCharacterForUniqueID($uniqueID, $player);
  if ($index != -1) DestroyAura($player, $index, $uniqueID, $location);
}

function AuraLocationConstants($location)
{
  switch ($location) {
    case "AURAS":
      $pieces = AuraPieces();
      $uniqueIDIndex = 6;
      $numUsesIndex = 5;
      break;
    case "EQUIP":
      $pieces = CharacterPieces();
      $uniqueIDIndex = 11;
      $numUsesIndex = 5;
      break;
    default:
      $pieces = AuraPieces();
      $uniqueIDIndex = 6;
      $numUsesIndex = 5;
      break;
  }
  return array($pieces, $uniqueIDIndex, $numUsesIndex);
}

function &GetAurasLocation($player, $location)
{
  switch ($location) {
    case "AURAS":
      $auras = &GetAuras($player);
      break;
    case "EQUIP":
      $auras = &GetPlayerCharacter($player);
      break;
    default:
      $auras = &GetAuras($player);
      break;
  }
  return $auras;
}

function DestroyAura($player, $index, $uniqueID = "", $location = "AURAS")
{
  global $combatChainState, $CCS_WeaponIndex, $combatChain, $mainPlayer;
  $auras = &GetAurasLocation($player, $location);
  if ($location == "EQUIP") {
    $isToken = true;
  }
  else {
    $isToken = $auras[$index + 4] == 1;
  }
  if ($uniqueID != "") {
    $index = $location == "AURAS" ? SearchAurasForUniqueID($uniqueID, $player) : SearchCharacterForUniqueID($uniqueID, $player);
  }
  AuraDestroyAbility($player, $index, $isToken, $location);
  $from = $location == "AURAS" ? $auras[$index + 9] : "EQUIPMENT";
  $cardID = RemoveAura($player, $index, $uniqueID, $location);
  AuraDestroyed($player, $cardID, $isToken, $from);
  // Refreshes the aura index with the Unique ID in case of aura destruction
  if (isset($combatChain[0]) && DelimStringContains(CardSubtype($combatChain[0]), "Aura") && $player == $mainPlayer) {
    $combatChainState[$CCS_WeaponIndex] = SearchAurasForUniqueID($combatChain[8], $player);
  }
  return $cardID;
}

function AuraDestroyAbility($player, $index, $isToken, $location = "AURAS")
{
  global $EffectContext;
  $auras = &GetAurasLocation($player, $location);
  $cardID = $auras[$index];
  switch ($cardID) {
    case "EVR141":
      $auraConstants = AuraLocationConstants($location);
      $uniqueIDIndex = $auraConstants[1];
      $numUsesIndex = $auraConstants[2];
      if (!$isToken && $auras[$index + $numUsesIndex] > 0 && ClassContains($cardID, "ILLUSIONIST", $player)) {
        $EffectContext = $cardID;
        --$auras[$index + $numUsesIndex];
        AddLayer("TRIGGER", $player, $auras[$index], "-", "-", $auras[$index + $uniqueIDIndex]);
      }
      break;
    default:
      break;
  }
}

function RemoveAura($player, $index, $uniqueID = "", $location = "AURAS")
{
  AuraLeavesPlay($player, $index, $uniqueID, $location);
  if ($location == "AURAS") {
    $auras = &GetAuras($player);
    $cardID = $auras[$index];
    for ($i = $index + AuraPieces() - 1; $i >= $index; --$i) {
      unset($auras[$i]);
    }
    $auras = array_values($auras);
  }
  elseif ($location == "EQUIP") {
    $character = &GetPlayerCharacter($player);
    $cardID = $character[$index];
    RemoveCharacter($player, $index);
    $character = array_values($character);
  }
  if (IsSpecificAuraAttacking($player, $index) || (IsSpecificAuraAttackTarget($player, $index, $uniqueID))) {
    CloseCombatChain();
  }
  return $cardID;
}

function AuraCostModifier($cardID = "")
{
  global $currentPlayer;
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  $myAuras = &GetAuras($currentPlayer);
  $theirAuras = &GetAuras($otherPlayer);
  $modifier = 0;
  for ($i = count($myAuras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    switch ($myAuras[$i]) {
      case "ELE111":
        $modifier += 1;
        AddLayer("TRIGGER", $currentPlayer, "ELE111", "-", "-", $myAuras[$i + 6]);
        break;
      default:
        break;
    }
  }

  for ($i = count($theirAuras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    switch ($theirAuras[$i]) {
      case "ELE146":
        $modifier += 1;
        break;
      default:
        break;
    }
  }
  return CostCantBeModified($cardID) ? 0 : $modifier;
}

// CR 2.1 - 4.2.1. Players do not get priority during the Start Phase.
// CR 2.1 - 4.3.1. The “beginning of the action phase” event occurs and abilities that trigger at the beginning of the action phase are triggered.
function AuraStartTurnAbilities()
{
  global $mainPlayer, $EffectContext, $defPlayer, $CS_NumVigorDestroyed, $CS_NumMightDestroyed, $CS_NumAgilityDestroyed, $currentTurnEffects;
  $auras = &GetAuras($mainPlayer);
  for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
  $EffectContext = $auras[$i];
    switch ($auras[$i]) {
    //These are all start of turn events without priority
    case "MON006":
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "MYHAND");
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a card to put in your hero's soul for " . CardLink($auras[$i], $auras[$i]));
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      AddDecisionQueue("SPECIFICCARD", $mainPlayer, "GENESIS", 1);
      break;
    case "DYN013":
    case "DYN014":
    case "DYN015":
      if ($auras[$i] == "DYN013") $amount = 3;
      else if ($auras[$i] == "DYN014") $amount = 2;
      else $amount = 1;
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "DYN029":
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      $hand = &GetHand($mainPlayer);
      if (count($hand) == 0) {
        Draw($mainPlayer, false);
      }
      if (PlayerHasLessHealth($mainPlayer)) {
        GainHealth(2, $mainPlayer);
      }
      if (SearchCount(SearchCharacter($mainPlayer, type: "E")) < SearchCount(SearchCharacter($defPlayer, type: "E"))) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "MYCHAR:type=E;hasNegCounters=true");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose an equipment to remove a -1 defense counter", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZOP", $mainPlayer, "GETCARDINDEX", 1);
        AddDecisionQueue("MODDEFCOUNTER", $mainPlayer, "1", 1);
      }
      break;
    case "DYN033":
    case "DYN034":
    case "DYN035":
      if ($auras[$i] == "DYN033") $amount = 3;
      else if ($auras[$i] == "DYN034") $amount = 2;
      else $amount = 1;
      GainHealth($amount, $mainPlayer);
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "DYN048":
      AddPlayerHand("DYN065", $mainPlayer, "-");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "DYN053":
    case "DYN054":
    case "DYN055":
      if ($auras[$i] == "DYN053") $amount = 3;
      else if ($auras[$i] == "DYN054") $amount = 2;
      else $amount = 1;
      $index = BanishCardForPlayer("DYN065", $mainPlayer, "-", "TT", $mainPlayer);
      $banish = new Banish($mainPlayer);
      AddDecisionQueue("PASSPARAMETER", $mainPlayer, $banish->Card($index)->UniqueID());
      AddDecisionQueue("ADDLIMITEDCURRENTEFFECT", $mainPlayer, $auras[$i] . ",BANISH");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "DYN073":
    case "DYN074":
    case "DYN075":
      if ($auras[$i] == "DYN073") $amount = 3;
      else if ($auras[$i] == "DYN074") $amount = 2;
      else $amount = 1;
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "DYN098":
    case "DYN099":
    case "DYN100":
      if ($auras[$i] == "DYN098") $amount = 3;
      else if ($auras[$i] == "DYN099") $amount = 2;
      else $amount = 1;
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      $searchHyper = CombineSearches(SearchDiscardForCard($mainPlayer, "ARC036", "DYN111", "DYN112"), SearchBanishForCardMulti($mainPlayer, "ARC036", "DYN111", "DYN112"));
      $countHyper = count(explode(",", $searchHyper));
      if ($amount > $countHyper) $amount = $countHyper;
      for ($j = 0; $j < $amount; ++$j) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "MYDISCARD:cardID=ARC036;cardID=DYN111;cardID=DYN112&MYBANISH:cardID=ARC036;cardID=DYN111;cardID=DYN112");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose an item to put into play");
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("SETDQVAR", $mainPlayer, "0", 1);
        AddDecisionQueue("MZOP", $mainPlayer, "GETCARDID", 1);
        AddDecisionQueue("PASSPARAMETER", $mainPlayer, "{0}", 1);
        AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
        AddDecisionQueue("PUTPLAY", $mainPlayer, "False", 1);
      }
      break;
    case "DYN159":
    case "DYN160":
    case "DYN161":
      if ($auras[$i] == "DYN159") $amount = 3;
      else if ($auras[$i] == "DYN160") $amount = 2;
      else $amount = 1;
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      PlayerOpt($mainPlayer, $amount);
      AddDecisionQueue("SPECIFICCARD", $mainPlayer, "BLESSINGOFFOCUS", 1);
      break;
    case "DYN179":
    case "DYN180":
    case "DYN181":
      if ($auras[$i] == "DYN179") $amount = 3;
      else if ($auras[$i] == "DYN180") $amount = 2;
      else $amount = 1;
      PlayAura("ARC112", $mainPlayer, $amount, true);
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "DYN200":
    case "DYN201":
    case "DYN202":
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "DYN218":
    case "DYN219":
    case "DYN220":
      if ($auras[$i] == "DYN218") $amount = 3;
      else if ($auras[$i] == "DYN219") $amount = 2;
      else $amount = 1;
      PlayAura("MON104", $mainPlayer, $amount);
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "DTD170":
      if ($auras[$i + 2] > 0) {
        AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_remove_a_Doom_Counter_and_keep_" . CardLink($auras[$i], $auras[$i]) . "_and_keep_it_in_play?");
        AddDecisionQueue("REMOVECOUNTERAURAORDESTROY", $mainPlayer, $auras[$i + 6]);
      } else {
        WriteLog(CardLink($auras[$i], $auras[$i]) . " was destroyed");
        DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      }
      break;
    case "TCC037":
    case "TCC038":
    case "TCC042":
    case "TCC043":
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "TCC105":
    case "HVY241": //Might
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      IncrementClassState($mainPlayer, $CS_NumMightDestroyed, 1);
      break;
    case "TCC107":
    case "HVY242": //Vigor
      GainResources($mainPlayer, 1);
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      IncrementClassState($mainPlayer, $CS_NumVigorDestroyed, 1);
      break;
    case "EVO243":
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "HVY068":
    case "HVY069":
    case "HVY070":
      $effectSource = $auras[$i];
      WriteLog("Resolving " . CardLink($auras[$i], $auras[$i]) . " ability");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      Draw($mainPlayer, effectSource: $effectSource);
      MZMoveCard($mainPlayer, "MYHAND", "MYTOPDECK", silent: true);
      break;
    case "HVY083":
    case "HVY084":
    case "HVY085":
      AddCurrentTurnEffect($auras[$i] . "-BUFF", $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "HVY086":
    case "HVY087":
    case "HVY088":
      AddCurrentTurnEffect($auras[$i] . "-BUFF", $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "HVY240": //Agility
      if (!SearchCurrentTurnEffects($auras[$i], $mainPlayer)) AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      IncrementClassState($mainPlayer, $CS_NumAgilityDestroyed, 1);
      break;
    case "MST133":
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY", $auras[$i + 6]);
      break;
    case "MST143":
    case "MST144":
    case "MST145":
      $AurasArray = explode(",", SearchAura($mainPlayer, class: "ILLUSIONIST"));
      if (count($AurasArray) > 1) DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "AJV017": // Channel Mount Isen
      $character = &GetPlayerCharacter($mainPlayer);
      $eqFrostbiteCount = 0;
      for ($k = 0; $k < count($character); $k += CharacterPieces()) {
        if ($character[$k] == "ELE111") {
          $slot = "";
          for ($j = 0; $j < count($currentTurnEffects); $j += CurrentTurnEffectsPieces()) {
            $effect = explode(",", $currentTurnEffects[$j]);
            if ($effect[0] == "ELE111-" . $character[$k + 11]) {
              $slot = $effect[1];
              if ($slot == "Arms" || $slot == "Legs" || $slot == "Head" || $slot == "Chest") { // Only count these Frostbites if they are in an equipment slot.
                $eqFrostbiteCount += 1;
              }
            }
          }
        }
      }
      LoseHealth($eqFrostbiteCount, $mainPlayer);
      WriteLog("Player $mainPlayer loses " . $eqFrostbiteCount . " life due to ". CardLink("AJV017", "AJV017") .".");
      break;
    case "HNT125":
      if (!SearchCurrentTurnEffects($auras[$i], $mainPlayer)) AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY"); 
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "HNT126":
      $character = &GetPlayerCharacter($mainPlayer);
      $weaponIndex1 = CharacterPieces();
      $weaponIndex2 = CharacterPieces() * 2;
      if(SubtypeContains($character[$weaponIndex1], "Dagger")) AddCharacterUses($mainPlayer, $weaponIndex1, 1);
      if(SubtypeContains($character[$weaponIndex2], "Dagger")) AddCharacterUses($mainPlayer, $weaponIndex2, 1);
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "HNT127":
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY"); // These can stack, so we don't care if the effect is already in play. See: Ancestral Harmony for comparison.
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "HNT163":
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    default:
      break;
    }
  }
  $defPlayerAuras = &GetAuras($defPlayer);
  for ($i = count($defPlayerAuras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    $EffectContext = $defPlayerAuras[$i];
    switch ($defPlayerAuras[$i]) {
      case "MST133":
        AddCurrentTurnEffect($defPlayerAuras[$i], $defPlayer, "PLAY", $defPlayerAuras[$i + 6]);
        break;
      case "AJV017": // Channel Mount Isen
        $character = &GetPlayerCharacter($mainPlayer);
        $eqFrostbiteCount = 0;
        for ($k = 0; $k < count($character); $k += CharacterPieces()) {
          if ($character[$k] == "ELE111") {
            $slot = "";
            for ($j = 0; $j < count($currentTurnEffects); $j += CurrentTurnEffectsPieces()) {
              $effect = explode(",", $currentTurnEffects[$j]);
              if ($effect[0] == "ELE111-" . $character[$k + 11]) {
                $slot = $effect[1];
                if ($slot == "Arms" || $slot == "Legs" || $slot == "Head" || $slot == "Chest") { // Only count these Frostbites if they are in an equipment slot.
                  $eqFrostbiteCount += 1;
                }
              }
            }
          }
        }
        LoseHealth($eqFrostbiteCount, $mainPlayer);
        WriteLog("Player $mainPlayer loses " . $eqFrostbiteCount . " life due to ". CardLink("AJV017", "AJV017") .".");
        break;
      default:
        break;
    }
  }
}

function AuraBeginningActionPhaseAbilities(){
  global $mainPlayer, $EffectContext, $CS_NumSeismicSurgeDestroyed;
  $auras = &GetAuras($mainPlayer);
  for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    $EffectContext = $auras[$i];
    switch ($auras[$i]) {
      //These are all "beginning of the action phase" events with priority holding
      case "WTR046":
      case "WTR047":
      case "WTR054":
      case "WTR055":
      case "WTR056":
      case "WTR069":
      case "WTR070":
      case "WTR071":
      case "WTR072":
      case "WTR073":
      case "WTR074":
      case "ARC162":
      case "CRU028":
      case "CRU029":
      case "CRU030":
      case "CRU031":
      case "CRU038":
      case "CRU039":
      case "CRU040":
      case "CRU075":
      case "CRU144":
      case "MON186":
      case "ELE025":
      case "ELE026":
      case "ELE027":
      case "ELE028":
      case "ELE029":
      case "ELE030":
      case "ELE109":
      case "ELE206":
      case "ELE207":
      case "ELE208":
      case "EVR107":
      case "EVR108":
      case "EVR109":
      case "EVR131":
      case "EVR132":
      case "EVR133":
      case "UPR190":
      case "UPR218":
      case "UPR219":
      case "UPR220":
      case "DYN217":
      case "ROS022":
      case "ROS033":
      case "ROS061":
      case "ROS062":
      case "ROS063":
      case "ROS064":
      case "ROS065":
      case "ROS066":
      case "ROS070":
      case "ROS113":
      case "ROS133":
      case "ROS161":
      case "ROS168":// Sigil of aether
      case "ROS182":
      case "ROS210":
      case "ROS226":
      case "ROS230":
      case "AJV018":
        AddLayer("TRIGGER", $mainPlayer, $auras[$i], "-", "-", $auras[$i + 6]);
        break;
      case "WTR075":
        IncrementClassState($mainPlayer, $CS_NumSeismicSurgeDestroyed, 1);
        AddLayer("TRIGGER", $mainPlayer, $auras[$i], "-", "-", $auras[$i + 6]);
        break;
      default:
        break;
      }
    }
}

function AuraBeginEndPhaseTriggers()
{
  global $mainPlayer, $CS_FealtyCreated, $CS_NumDraconicPlayed;
  $auras = &GetAuras($mainPlayer);
  for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    switch ($auras[$i]) {
      case "UPR176":
      case "UPR177":
      case "UPR178":
      case "DYN244":
      case "OUT234":
      case "OUT235":
      case "OUT236":
      case "ROS034":
      case "HNT118":
        AddLayer("TRIGGER", $mainPlayer, $auras[$i], "-", "-", $auras[$i + 6]);
        break;
      case "HNT167":
        $fealtySurvives = GetClassState($mainPlayer, $CS_FealtyCreated) + GetClassState($mainPlayer, $CS_NumDraconicPlayed);
        if (!$fealtySurvives) {
          AddLayer("TRIGGER", $mainPlayer, $auras[$i], "-", "-", $auras[$i + 6]);
        }
      default:
        break;
    }
  }
  $auras = array_values($auras);
}

function OpponentsAuraBeginEndPhaseTriggers()
{
  global $defPlayer;
  $auras = &GetAuras($defPlayer);
  for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    switch ($auras[$i]) {
      case "ROS219":
        AddLayer("TRIGGER", $defPlayer, $auras[$i], "ROS219-1", uniqueID: $auras[$i + 6]);
        break;
      default:
        break;
    }
  }
  $auras = array_values($auras);
}

function AuraBeginEndPhaseAbilities()
{
  global $mainPlayer;
  $auras = &GetAuras($mainPlayer);
  for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    $remove = 0;
    switch ($auras[$i]) {
      case "ELE117":
      case "ROS033":
        ChannelTalent($i, "EARTH");
        break;
      case "ELE146":
      case "AJV017":
        ChannelTalent($i, "ICE");
        break;
      case "ELE175":
      case "ROS077":
        ChannelTalent($i, "LIGHTNING");
        break;
      case "UPR005":
        $toBanish = ++$auras[$i + 2];
        $discardReds = SearchCount(SearchDiscard($mainPlayer, pitch: 1));
        if ($toBanish <= $discardReds) {
          for ($j = $toBanish; $j > 0; --$j) {
            MZMoveCard($mainPlayer, "MYDISCARD:pitch=1", "MYBANISH,GY,-", may: true, isSubsequent: $j < $toBanish);
          }
          AddDecisionQueue("ELSE", $mainPlayer, "-");
          AddDecisionQueue("PASSPARAMETER", $mainPlayer, "MYAURAS-" . $i, 1);
          AddDecisionQueue("MZDESTROY", $mainPlayer, "-", 1);
        } else {
          DestroyAura($mainPlayer, $i);
        }
        break;
      case "UPR138":
        ChannelTalent($i, "ICE");
        break;
      case "ELE111":
        FrostHexEndTurnAbility($mainPlayer);
        $remove = 1;
        break;
      case "DYN175":
        if ($auras[$i + 2] == 0) $remove = 1;
        else {
          --$auras[$i + 2];
          DealArcane(2, 2, "PLAYCARD", "DYN175", false, $mainPlayer);
        }
        break;
      default:
        break;
    }
    if ($remove == 1) DestroyAura($mainPlayer, $i);
  }
  $auras = array_values($auras);
  // check auras in the equip slot
  $mainCharacter = &GetPlayerCharacter($mainPlayer);
  for ($i = count($mainCharacter) - CharacterPieces(); $i >= 0; $i -= CharacterPieces()) {
    $remove = 0;
    switch ($mainCharacter[$i]) {
      case "ELE111":
        FrostHexEndTurnAbility($mainPlayer);
        $remove = 1;
        break;
      default:
        break;
      }
    if ($remove == 1){
      $uniqueID = $mainCharacter[$i + 11];
      DestroyAuraUniqueID($mainPlayer, $uniqueID, "EQUIP");
    }
  }
  $mainCharacter = array_values($mainCharacter);
}

function ChannelTalent($index, $talent)
{
  global $mainPlayer;
  $auras = &GetAuras($mainPlayer);
  $toBottom = ++$auras[$index + 2];
  $numTalent = SearchCount(SearchPitch($mainPlayer, talent: $talent));
  if ($toBottom <= $numTalent) {
    for ($j = $toBottom; $j > 0; --$j) {
      MZMoveCard($mainPlayer, "MYPITCH:talent=" . $talent, "MYBOTDECK", $j == $toBottom ? true : false, isSubsequent: $j < $toBottom, DQContext: "Choose a " . ucwords(strtolower($talent)) . " card" . ($toBottom > 1 ? "s" : "") . " for your " . CardLink($auras[$index], $auras[$index]) . " with " . $toBottom . " flow counter" . ($toBottom > 1 ? "s" : "") . " on it:");
    }
    AddDecisionQueue("ELSE", $mainPlayer, "-");
    AddDecisionQueue("PASSPARAMETER", $mainPlayer, "MYAURAS-" . $index, 1);
    AddDecisionQueue("MZDESTROY", $mainPlayer, "-", 1);
  } else {
    DestroyAura($mainPlayer, $index);
  }
}

function AuraEndTurnAbilities()
{
  global $CS_NumNonAttackCards, $mainPlayer, $CS_HitsWithSword, $CS_NumTimesAttacked;
  $auras = &GetAuras($mainPlayer);
  for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    $remove = false;
    switch ($auras[$i]) {
      case "ARC167":
      case "ARC168":
      case "ARC169":
        if (GetClassState($mainPlayer, $CS_NumNonAttackCards) == 0) $remove = true;
        break;
      case "ELE226":
        $remove = true;
        break;
      case "UPR139":
        $remove = true;
        break;
      case "DYN072":
        if (GetClassState($mainPlayer, $CS_HitsWithSword) <= 0) $remove = true;
        break;
      case "HNT073":
        if (GetClassState($mainPlayer, $CS_NumTimesAttacked) < 3) $remove = true;
        break;
      default:
        break;
    }
    if ($remove) DestroyAura($mainPlayer, $i);
  }
}

function AuraEndTurnCleanup()
{
  $auras = &GetAuras(1);
  for ($i = 0; $i < count($auras); $i += AuraPieces()) $auras[$i + 5] = AuraNumUses($auras[$i]);
  $auras = &GetAuras(2);
  for ($i = 0; $i < count($auras); $i += AuraPieces()) $auras[$i + 5] = AuraNumUses($auras[$i]);
}

function AuraDamagePreventionAmount($player, $index, $type, $damage = 0, $active = false, &$cancelRemove = false, $check = false)
{
  $preventedDamage = 0;
  $auras = &GetAuras($player);
  if (HasWard($auras[$index], $player)) $preventedDamage = WardAmount($auras[$index], $player, $index);
  elseif (HasArcaneShelter($auras[$index]) && $type == "ARCANE") $preventedDamage = ArcaneShelterAmount($auras[$index]);
  switch ($auras[$index]) {
    case "ARC167":
      $preventedDamage = 4;
      break;
    case "ARC168":
      $preventedDamage = 3;
      break;
    case "ARC169":
      $preventedDamage = 2;
      break;
    case "DTD081":
      $auras = &GetAuras($player);
      if ($active) {
        $soul = &GetSoul($player);
        if (count($soul) > 0) {
          $cancelRemove = count($soul) > 1 ? true : false;
          MZMoveCard($player, "MYSOUL", "MYBANISH,SOUL,-");
          if ($damage > 1) $auras[$index + 5] = 0;
          $preventedDamage = 1;
        }
      } else if ($auras[$index + 5] == 1) {
        $preventedDamage = 1;
      } else {
        $auras[$index + 5] = 1;
        $preventedDamage = 0;
      }
      break;
    default:
      break;
  }
  if (!$check && SearchCurrentTurnEffects("OUT174", $player) != "" && $preventedDamage > 0) {//vambrace
    $preventedDamage -= 1;
    SearchCurrentTurnEffects("OUT174", $player, remove:true);
  }
  return $preventedDamage;
}

//This function is for effects that prevent damage and DO destroy themselves
function AuraTakeDamageAbility($player, $index, $damage, $preventable, $type)
{
  $cancelRemove = false;
  $preventionAmount = 0;
  $auras = &GetAuras($player);
  if ($preventable) {
    $preventionAmount = AuraDamagePreventionAmount($player, $index, $type, $damage, true, $cancelRemove);
    $damage -= $preventionAmount;
    if($preventionAmount > 0) WriteLog(CardLink($auras[$index], $auras[$index]) . " was destroyed and prevented " . $preventionAmount . " damage.");
  }
  if (!$cancelRemove) DestroyAura($player, $index);
  return $damage;
}

//This function is for effects that prevent damage and do NOT destroy themselves
//These are applied first and not prompted (which would be annoying because of course you want to do this before consuming something)
function AuraTakeDamageAbilities($player, $damage, $type, $source)
{
  $auras = &GetAuras($player);
  //CR 2.1 6.4.10f If an effect states that a prevention effect can not prevent the damage of an event, the prevention effect still applies to the event but its prevention amount is not reduced. Any additional modifications to the event by the prevention effect still occur.
  $preventable = CanDamageBePrevented($player, $damage, $type, $source);
  for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    if ($damage <= 0) {
      $damage = 0;
      break;
    }
    switch ($auras[$i]) {
      case "CRU075":
        if ($preventable) $damage -= 1;
        break;
      case "CRU144":
        if ($auras[$i + 1] == 2) {
          $auras[$i + 1] = 1;
          $numRunchants = CountAura("ARC112", $player);
          if ($numRunchants <= $damage) {
            for ($j = 0; $j < $numRunchants; $j++) {
              $index = SearchAurasForIndex("ARC112", $player);
              if ($index != -1) DestroyAuraUniqueID($player, $auras[$index + 6]);
            }
            if ($numRunchants > 1) WriteLog($numRunchants . " " . CardLink("ARC112", "ARC112") . "s were destroyed");
            else WriteLog($numRunchants . " " . CardLink("ARC112", "ARC112") . " was destroyed");
            if ($preventable) $damage -= $numRunchants;
          } else {
            for ($j = 0; $j < $damage; $j++) {
              $index = SearchAurasForIndex("ARC112", $player);
              if ($index != -1) DestroyAuraUniqueID($player, $auras[$index + 6]);
            }
            if ($damage > 1) WriteLog($damage . " " . CardLink("ARC112", "ARC112") . "s were destroyed");
            else WriteLog($damage . " " . CardLink("ARC112", "ARC112") . " was destroyed");
            if ($preventable) $damage -= $damage;
          }
        }
        break;
      case "EVR131":
        if ($type == "ARCANE" && $preventable) $damage -= 3;
        break;
      case "EVR132":
        if ($type == "ARCANE" && $preventable) $damage -= 2;
        break;
      case "EVR133":
        if ($type == "ARCANE" && $preventable) $damage -= 1;
        break;
      default:
        break;
    }
  }
  return $damage;
}


function AuraDamageTakenAbilities($player, $damage, $source)
{
  global $CS_DamageTaken, $CS_ArcaneDamageTaken, $CS_DamageDealt, $CS_ArcaneDamageDealt;
  $otherPlayer = $player == 1 ? 2 : 1;

  $auras = &GetAuras($player);
  for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    $remove = 0;
    switch ($auras[$i]) {
      case "ARC106":
      case "ARC107":
      case "ARC108":
      case "EVR023":
      case "ROS152":
      case "ROS153":
      case "ROS154":
        $remove = 1;
        break;
      default:
        break;
    }
    if ($remove) DestroyAura($player, $i);
  }

  $otherAuras = &GetAuras($otherPlayer);
  for ($i = count($otherAuras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    switch ($otherAuras[$i]) {
      case "ROS077":
        if(GetClassState($otherPlayer, $CS_DamageDealt) == 0 && GetClassState($otherPlayer, $CS_ArcaneDamageDealt) == 0 && $damage > 0 && $otherAuras[$i + 5] > 0){
          $otherAuras[$i + 5] -= 1;
          if (CardType($source) != "AA" || !SearchCurrentTurnEffects("OUT108", $otherPlayer) && !HitEffectsArePrevented($source)) {
            AddLayer("TRIGGER", $otherPlayer, $otherAuras[$i], uniqueID: $otherAuras[$i + 6]);
          }
        }
        elseif (GetClassState($player, $CS_DamageTaken) == 0 && GetClassState($player, $CS_ArcaneDamageTaken) == 0 && $damage > 0 && $otherAuras[$i + 5] > 0) {
          $otherAuras[$i + 5] -= 1;
          if (CardType($source) != "AA" || !SearchCurrentTurnEffects("OUT108", $otherPlayer) && !HitEffectsArePrevented($source)) {
            AddLayer("TRIGGER", $otherPlayer, $otherAuras[$i], uniqueID: $otherAuras[$i + 6]);
          }
        }
        break;
      default:
        break;
    }
  }

  return $damage;
}

function AuraDamageDealtAbilities($player, $damage)
{
  $auras = &GetAuras($player);
  for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    $remove = 0;
    switch ($auras[$i]) {
      case "ROS152":
      case "ROS153":
      case "ROS154":
        $remove = 1;
        break;
      default:
        break;
    }
    if ($remove) DestroyAura($player, $i);
  }
  return $damage;
}

function AuraLoseHealthAbilities($player, $amount)
{
  global $mainPlayer;
  $auras = &GetAuras($player);
  for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    $remove = 0;
    switch ($auras[$i]) {
      case "MON157":
        if ($player == $mainPlayer) $remove = 1;
        break;
      default:
        break;
    }
    if ($remove == 1) DestroyAura($player, $i);
  }
  return $amount;
}

function AuraPlayAbilities($cardID, $from = "")
{
  global $currentPlayer, $CS_NumIllusionistActionCardAttacks;
  $auras = &GetAuras($currentPlayer);
  $cardType = CardType($cardID);
  $cardSubType = CardSubType($cardID);
  for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    $remove = 0;
    switch ($auras[$i]) {
      case "WTR225":
        if (($cardType == "AA" && (GetResolvedAbilityType($cardID) == "" || GetResolvedAbilityType($cardID) == "AA"))
          || (DelimStringContains($cardSubType, "Aura") && $from == "PLAY" && (GetResolvedAbilityType($cardID) == "" || GetResolvedAbilityType($cardID) == "AA"))
          || (TypeContains($cardID, "W") && GetResolvedAbilityType($cardID) == "AA" && $from == "EQUIP")) {
          WriteLog(CardLink($auras[$i], $auras[$i]) . " gives the attack go again");
          GiveAttackGoAgain();
          $remove = 1;
        }
        break;
      case "ARC112":
        if (($cardType == "AA" && GetResolvedAbilityType($cardID) != "I" && $from != "PLAY") || (DelimStringContains($cardSubType, "Aura") && $from == "PLAY") || ((TypeContains($cardID, "W", $currentPlayer) && GetResolvedAbilityType($cardID) == "AA")) && GetResolvedAbilityType($cardID) != "I") {
          AddLayer("TRIGGER", $currentPlayer, $auras[$i], "-", "-", $auras[$i + 6]);
        }
        break;
      case "MON157":
        DimenxxionalCrossroadsPassive($cardID, $from);
        break;
      case "ELE110":
        if ($cardType == "AA" && GetResolvedAbilityType($cardID) != "I") {
          AddLayer("TRIGGER", $currentPlayer, $auras[$i], "-", $cardID, $auras[$i + 6]);
        }
        break;
      case "EVR143":
        if ($auras[$i + 5] > 0 && CardType($cardID) == "AA" && ClassContains($cardID, "ILLUSIONIST", $currentPlayer) && GetClassState($currentPlayer, $CS_NumIllusionistActionCardAttacks) <= 1) {
          WriteLog(CardLink($auras[$i], $auras[$i]) . " gives the attack +2");
          --$auras[$i + 5];
          AddCurrentTurnEffect("EVR143", $currentPlayer, true);
        }
        break;
      case "ELE175":
        if ((DelimStringContains($cardType, "A") || $cardType == "AA") && (GetResolvedAbilityType($cardID, $from) == "" || GetResolvedAbilityType($cardID, $from) == "AA" || GetResolvedAbilityType($cardID, $from) == "A")) {
          AddLayer("TRIGGER", $currentPlayer, $auras[$i], $cardType, "-", $auras[$i + 6]);
        }
        break;
      case "DTD232":
        if ($cardType == "AA" && (GetResolvedAbilityType($cardID, $from) == "" || GetResolvedAbilityType($cardID, $from) == "AA")
          || (DelimStringContains($cardSubType, "Aura") && $from == "PLAY")
          || ((TypeContains($cardID, "W", $currentPlayer) && GetResolvedAbilityType($cardID) != "A")) && GetResolvedAbilityType($cardID) != "I") {
          AddCurrentTurnEffect("DTD232", $currentPlayer);
          $remove = 1;
        }
        break;
      case "DTD233":
        if (DelimStringContains($cardType, "A") && $from != "PLAY") {
          WriteLog(CardLink($auras[$i], $auras[$i]) . " gives the next non-attack action card go again");
          AddLayer("TRIGGER", $currentPlayer, $auras[$i], $cardType, "-", $auras[$i + 6]);
        }
        break;
      case "ROS130":
      case "ROS131":
      case "ROS132":
        if ($cardType == "AA" && (GetResolvedAbilityType($cardID, $from) == "" || GetResolvedAbilityType($cardID, $from) == "AA") && $auras[$i + 5] > 0) {
          --$auras[$i + 5];
          AddLayer("TRIGGER", $currentPlayer, $auras[$i], "-", $cardID, $auras[$i + 6]);
        }
        break;
      default:
        break;
    }
    if ($remove == 1) DestroyAura($currentPlayer, $i);
  }
}

function AuraAttackAbilities($attackID)
{
  global $mainPlayer, $CS_PlayIndex, $CS_NumIllusionistAttacks, $CS_NumTimesAttacked;
  $auras = &GetAuras($mainPlayer);
  $attackType = CardType($attackID);
  for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    switch ($auras[$i]) {
      case "ELE226":
        if ($attackType == "AA") {
          AddLayer("TRIGGER", $mainPlayer, $auras[$i], "-", $attackID, $auras[$i + 6]);
        }
        break;
      case "EVR140":
        if ($auras[$i + 5] > 0 && DelimStringContains(CardSubtype($attackID), "Aura") && ClassContains($attackID, "ILLUSIONIST", $mainPlayer)) {
          $index = GetClassState($mainPlayer, $CS_PlayIndex);
          WriteLog(CardLink($auras[$i], $auras[$i]) . " puts a +1 counter on " . CardLink($auras[$index], $auras[$index]));
          ++$auras[$index + 3];
          --$auras[$i + 5];
        }
        break;
      case "EVR142":
        if ($auras[$i + 5] > 0 && ClassContains($attackID, "ILLUSIONIST", $mainPlayer) && GetClassState($mainPlayer, $CS_NumIllusionistAttacks) <= 1) {
          WriteLog(CardLink($auras[$i], $auras[$i]) . " makes your first illusionist attack each turn lose Phantasm");
          --$auras[$i + 5];
          AddCurrentTurnEffect("EVR142", $mainPlayer, true);
        }
        break;
      case "UPR005":
        if ($auras[$i + 5] > 0 && DelimStringContains(CardSubType($attackID), "Dragon")) {
          --$auras[$i + 5];
          AddLayer("TRIGGER", $mainPlayer, $auras[$i], "-", $attackID, $auras[$i + 6]);
        }
        break;
      case "HNT073":
        if (GetClassState($mainPlayer, $CS_NumTimesAttacked) == 4) {
          AddLayer("TRIGGER", $mainPlayer, $auras[$i], "-", $attackID, $auras[$i + 6]);
        }
      default:
        break;
    }
  }
}

function AuraHitEffects($attackID)
{
  global $mainPlayer;
  $attackType = CardType($attackID);
  $auras = &GetAuras($mainPlayer);
  for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    $remove = 0;
    switch ($auras[$i]) {
      case "ARC106":
      case "ARC107":
      case "ARC108":
        if ($attackType == "AA") {
          AddLayer("TRIGGER", $mainPlayer, $auras[$i], "-", $attackID, $auras[$i + 6]);
        }
        break;
      default:
        break;
    }
    if ($remove == 1) DestroyAura($mainPlayer, $i);
  }
}

function AuraAttackModifiers($index, &$attackModifiers, $onBlock=false)
{
  global $CombatChain, $combatChainState, $CCS_AttackPlayedFrom;
  global $CID_Frailty;
  $chainCard = $CombatChain->Card($index);
  $modifier = 0;
  $player = $chainCard->PlayerID();
  $myAuras = &GetAuras($player);
  if (!$onBlock) {//This codeblock was counting CMH twice on block
    for ($i = 0; $i < count($myAuras); $i += AuraPieces()) {
      switch ($myAuras[$i]) {
        case "ELE117":
          if (CardType($chainCard->ID()) == "AA") {
            $modifier += 3;
            array_push($attackModifiers, $myAuras[$i]);
            array_push($attackModifiers, 3);
          }
          break;
        case $CID_Frailty:
          if ($index == 0 && (IsWeaponAttack() || $combatChainState[$CCS_AttackPlayedFrom] == "ARS")) {
            $modifier -= 1;
            array_push($attackModifiers, $myAuras[$i]);
            array_push($attackModifiers, -1);
          }
          break;
        case "HNT118":
          if(IsWeaponAttack())
          {
            $modifier += 1;
            array_push($attackModifiers, $myAuras[$i]);
            array_push($attackModifiers, 1);
          }
        default:
          break;
      }
    }
  }
  $theirAuras = &GetAuras($player == 1 ? 2 : 1);
  for ($i = 0; $i < count($theirAuras); $i += AuraPieces()) {
    switch ($theirAuras[$i]) {
      case "MON011":
        if (CardType($CombatChain->CurrentAttack()) == "AA") {
          $modifier -= 1;
          array_push($attackModifiers, $theirAuras[$i]);
          array_push($attackModifiers, -1);
        }
        break;
      default:
        break;
    }
  }
  return $modifier;
}

function NumNonTokenAura($player)
{
  $count = 0;
  $auras = &GetAuras($player);
  for ($i = 0; $i < count($auras); $i += AuraPieces()) {
    if (CardType($auras[$i]) != "T") ++$count;
  }
  return $count;
}

function DestroyAllThisAura($player, $cardID)
{
  $auras = &GetAuras($player);
  $count = 0;
  for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    if ($auras[$i] == $cardID) {
      DestroyAura($player, $i);
      ++$count;
    }
  }
  return $count;
}

function GetAuraGemState($player, $cardID)
{
  global $currentPlayer;
  $auras = &GetAuras($player);
  $offset = ($currentPlayer == $player ? 7 : 8);
  $state = 0;
  for ($i = 0; $i < count($auras); $i += AuraPieces()) {
    if ($auras[$i] == $cardID && $auras[$i + $offset] > $state) $state = $auras[$i + $offset];
  }
  return $state;
}

function AuraIntellectModifier()
{
  global $mainPlayer;
  $otherPlayer = ($mainPlayer == 1 ? 2 : 1);
  $intellectModifier = 0;
  $auras = &GetAuras($mainPlayer);
  for ($i = 0; $i < count($auras); $i += AuraPieces()) {
    switch ($auras[$i]) {
      case "EVO243":
        $intellectModifier -= 1;
        break;
      default:
        break;
    }
  }
  $auras = &GetAuras($otherPlayer);
  for ($i = 0; $i < count($auras); $i += AuraPieces()) {
    switch ($auras[$i]) {
      case "EVO243":
        $intellectModifier -= 1;
        break;
      default:
        break;
    }
  }
  return $intellectModifier;
}

function PayAuraAbilityAdditionalCosts($cardID, $from)
{
  global $currentPlayer, $CS_PlayIndex;
  $index = GetClassState($currentPlayer, $CS_PlayIndex);
  $auras = &GetAuras($currentPlayer);
  switch ($cardID) {
    case "DTD060":
    case "DTD061":
    case "DTD062":
      $hand = &GetHand($currentPlayer);
      if (count($hand) == 0) {
        WriteLog("You do not have a card to charge. Reverting gamestate.", highlight: true);
        RevertGamestate();
        return;
      }
      DestroyAura($currentPlayer, $index);
      Charge(may: false);
      break;
    case "MST133":
      $abilityType = GetResolvedAbilityType($cardID);
      if ($abilityType == "I" && $from == "PLAY" && SearchCurrentTurnEffectsForUniqueID($auras[$index + 6]) != -1) {
        --$auras[$index + 3];
        RemoveCurrentTurnEffect(SearchCurrentTurnEffectsForUniqueID($auras[$index + 6]));
        AddCurrentTurnEffect($cardID, $currentPlayer, "", $auras[$index + 6] . "-PAID");
      } elseif ($abilityType == "AA") {
        $auras[$index + 1] = 1;
      }
      break;
    case "HNT167":
      DestroyAura($currentPlayer, $index);
    default:
      break;
  }
}

function AurasAttackYouControlModifiers($cardID, $player)
{
  $auras = &GetAuras($player);
  $attackModifier = 0;
  for ($i = 0; $i < count($auras); $i += ItemPieces()) {
    switch ($auras[$i]) {
      case "ELE117":
        if (CardType($cardID) == "AA") $attackModifier += 3;
      default:
        break;
    }
  }
  return $attackModifier;
}
