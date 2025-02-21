<?php

function PlayAura($cardID, $player, $number = 1, $isToken = false, $rogueHeronSpecial = false, $numAttackCounters = 0, $from = "-", $additionalCosts = "-", $effectController = "-", $effectSource = "-")
{
  global $CS_NumAuras, $EffectContext, $defPlayer, $CS_FealtyCreated;
  $otherPlayer = ($player == 1 ? 2 : 1);
  if ($effectController == "-") $effectController = $player;
  if (TypeContains($cardID, "T", $player)) $isToken = true;
  if (DelimStringContains(CardSubType($cardID), "Affliction")) {
    $otherPlayer = $player;
    $player = ($player == 1 ? 2 : 1);
  }
  $auras = &GetAuras($player);
  $numMinusTokens = 0;
  $numMinusTokens = CountCurrentTurnEffects("ripple_away_blue", $player) + CountCurrentTurnEffects("ripple_away_blue", $otherPlayer);
  $effectSource = $effectSource == "-" ? $EffectContext : $effectSource;
  // only modify the event if there is an event
  if ($number > 0) $number += CharacterModifiesPlayAura($player, $isToken, $effectController);

  if ($numMinusTokens > 0 && $isToken && (TypeContains($effectSource, "AA", $player) || TypeContains($effectSource, "A", $player))) $number -= $numMinusTokens;
  if ($cardID == "runechant") $number += CountCurrentTurnEffects("mordred_tide_red", $player);
  if ($cardID == "spectral_shield") {
    $index = SearchArsenalReadyCard($player, "the_librarian");
    if ($index > -1) TheLibrarianEffect($player, $index);
  }
  if ($cardID == "manifestation_of_miragai_blue") SearchCardList($additionalCosts, $player, subtype: "Chi") != "" ? $numAttackCounters += 4 : $numAttackCounters += 2;
  if ($cardID == "waxing_specter_red" || $cardID == "waxing_specter_yellow" || $cardID == "waxing_specter_blue") $numAttackCounters += SearchPitchForColor($player, 3) > 0 ? 1 : 0;
  if (ClassContains($cardID, "ILLUSIONIST", $player) && SearchCurrentTurnEffects("vengeful_apparition_red", $player, true) && CardCost($cardID, $from) <= 2 && CardCost($cardID, $from) > -1) {
    ++$numAttackCounters;
  }
  if (ClassContains($cardID, "ILLUSIONIST", $player) && SearchCurrentTurnEffects("vengeful_apparition_yellow", $player, true) && CardCost($cardID, $from) <= 1 && CardCost($cardID, $from) > -1) {
    ++$numAttackCounters;
  }
  if (ClassContains($cardID, "ILLUSIONIST", $player) && SearchCurrentTurnEffects("vengeful_apparition_blue", $player, true) && CardCost($cardID, $from) <= 0 && CardCost($cardID, $from) > -1) {
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
  else if ($cardID != "frostbite") IncrementClassState($player, $CS_NumAuras, $number);
  if ($cardID == "fealty") IncrementClassState($player, $CS_FealtyCreated, $number);
}

//cards that instruct the player to create an aura under their opponent's control
//it still "counts" as the player creating an aura
function CreatesAuraForOpponent($cardID)
{
  return match($cardID) {
    "spike_with_bloodrot_red" => true,
    "spike_with_frailty_red" => true,
    "spike_with_inertia_red" => true,
    "infect_red" => true,
    "infect_yellow" => true,
    "infect_blue" => true,
    "sedate_red" => true,
    "sedate_yellow" => true,
    "sedate_blue" => true,
    "wither_red" => true,
    "wither_yellow" => true,
    "wither_blue" => true,
    default => false
  };
}

function AuraNumUses($cardID)
{
  switch ($cardID) {
    case "shimmers_of_silver_blue":
    case "haze_bending_blue":
    case "passing_mirage_blue":
    case "pierce_reality_blue":
    case "burn_them_all_red":
    case "radiant_forcefield_yellow":
    case "channel_lightning_valley_yellow":
    case "malefic_incantation_red":
    case "malefic_incantation_yellow":
    case "malefic_incantation_blue":
    case "ring_of_roses_yellow":
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
      case "haze_bending_blue":
        if (!$isToken && $auras[$i + 5] > 0 && ClassContains($cardID, "ILLUSIONIST", $player)) {
          --$auras[$i + 5];
          AddLayer("TRIGGER", $player, $auras[$i], "-", "-", $auras[$i + 6]);
        }
        break;
      case "phantom_tidemaw_blue":
        if (ClassContains($cardID, "ILLUSIONIST", $player)) PhantomTidemawDestroy($player, $i);
        break;
      default:
        break;
    }
  }
  $goesWhere = GoesWhereAfterResolving($cardID, $from);
  $numMercifulRetribution = SearchCount(SearchAurasForCard("merciful_retribution_yellow", $player)) + ($cardID == "merciful_retribution_yellow" ? 1 : 0);
  if ($numMercifulRetribution > 0 && TalentContains($cardID, "LIGHT", $player)) {
    AddDecisionQueue("PASSPARAMETER", $player, $cardID, 1);
    AddDecisionQueue("ADDSOUL", $player, "PLAY", 1);
    $goesWhere = "-";
  }
  for ($i = 0; $i < $numMercifulRetribution; ++$i) {
    if (CardType($cardID) != "T" && $isToken) WriteLog("<span style='color:red;'>The card is not put in your soul from Merciful Retribution because it is a token copy</span>");
    AddDecisionQueue("ADDTRIGGER", $player, "merciful_retribution_yellow," . $cardID);
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
    case "ironsong_pride_red":
      $char = &GetPlayerCharacter($player);
      for ($j = 0; $j < count($char); $j += CharacterPieces()) {
        if (CardSubType($char[$j]) == "Sword") $char[$j + 3] = 0;
      }
      break;
    case "tranquil_passing_red":
    case "tranquil_passing_yellow":
    case "tranquil_passing_blue":
      $banish = new Banish($otherPlayer);
      $banishCard = $banish->FirstCardWithModifier($cardID . "-" . $uniqueID);
      if ($banishCard == null) break;
      $banishIndex = $banishCard->Index();
      if ($banishIndex > -1) PlayAura($banish->Remove($banishIndex), $otherPlayer);
      break;
    case "waning_vengeance_red":
    case "waning_vengeance_yellow":
    case "waning_vengeance_blue":
      AddLayer("TRIGGER", $player, $cardID, "-", "-", $uniqueID);
      break;
    case "essence_of_ancestry_body_red":
    case "essence_of_ancestry_soul_yellow":
    case "essence_of_ancestry_mind_blue":
      $illusionistAuras = SearchAura($player, class: "ILLUSIONIST");
      $aurasArray = explode(",", $illusionistAuras);
      if (count($aurasArray) <= 1) AddLayer("TRIGGER", $player, $cardID, "-", "-", $uniqueID);
      break;
    case "haunting_specter_red":
    case "haunting_specter_yellow":
    case "haunting_specter_blue":
      AddLayer("TRIGGER", $player, $cardID, "-", "-", $uniqueID);
      break;
    case "vengeful_apparition_red":
    case "vengeful_apparition_yellow":
    case "vengeful_apparition_blue":
      $illusionistAuras = SearchAura($player, class: "ILLUSIONIST");
      $aurasArray = explode(",", $illusionistAuras);
      if (count($aurasArray) <= 1) AddLayer("TRIGGER", $player, $cardID, "-", "-", $uniqueID);
      break;
    case "sigil_of_brilliance_yellow": //sigil of brilliance
      AddDecisionQueue("DRAW", $player, "-", 0);
      break;
    case "sigil_of_sanctuary_blue":
      WriteLog(CardLink($cardID, $cardID) . " created an " . CardLink("embodiment_of_earth", "embodiment_of_earth"));
      PlayAura("embodiment_of_earth", $player);
      break;
    case "sigil_of_earth_blue": //sigil of earth
      PlayAura("embodiment_of_earth", $player);
      break;
    case "sigil_of_conductivity_blue":
      WriteLog(CardLink($cardID, $cardID) . " created an " . CardLink("embodiment_of_lightning", "embodiment_of_lightning"));
      PlayAura("embodiment_of_lightning", $player);
      break;
    case "sigil_of_lightning_blue": //sigil of lightning
      PlayAura("embodiment_of_lightning", $player);
      break;
    case "sigil_of_the_arknight_blue":
      $deck = new Deck($player);
      if ($deck->Reveal()) {
        if (CardType($deck->Top()) == "AA") {
          AddPlayerHand($deck->Top(true), $player, "DECK");
        }
      }
      break;
    case "arcane_cussing_red":
    case "arcane_cussing_yellow":
    case "arcane_cussing_blue":
      if($mainPlayer == $player) { // main player is the attacking player, these being equal would mean that it is "your turn" to Arcane Cussing
        AddLayer("TRIGGER", $player, $cardID);
      }
      break;
    case "sigil_of_deadwood_blue":
      PlayAura("runechant", $player);
      break;
    case "sigil_of_aether_blue"://Sigil of aether
      AddLayer("TRIGGER", $player, $cardID, "-", "Arcane", $uniqueID);
      break;
    case "sigil_of_temporal_manipulation_blue":
      $deck = new Deck($player);
      $newCardID = $deck->Top();
      $mod = (DelimStringContains(CardType($newCardID), "A") ? "INST" : "-");
      BanishCardForPlayer($newCardID, $player, "DECK", $mod);
      $deck->Remove(0);
      break;
    case "sigil_of_forethought_blue":
      PlayAura("ponder", $player);
      break;
    case "sigil_of_fyendal_blue":
      GainHealth(1, $player);
      break;
    case "sigil_of_cycles_blue":
      AddDecisionQueue("FINDINDICES", $player, "HAND");
      AddDecisionQueue("CHOOSEHAND", $player, "<-", 1);
      AddDecisionQueue("REMOVEMYHAND", $player, "-", 1);
      AddDecisionQueue("DISCARDCARD", $player, "HAND-" . $player, 1);
      AddDecisionQueue("DRAW", $player, "-", 0);
      break;
    default:
      break;
  }
  if (SearchCurrentTurnEffects("aether_bindings_of_the_third_age", $player) && DelimStringContains(CardName($cardID), "Sigil", partial: true)){
    AddLayer("TRIGGER", $player, "aether_bindings_of_the_third_age");
  }
}

function AuraPlayCounters($cardID)
{
  switch ($cardID) {
    case "zen_state":
      return 1;
    case "runeblood_incantation_red":
    case "malefic_incantation_red":
      return 3;
    case "runeblood_incantation_yellow":
    case "malefic_incantation_yellow":
      return 2;
    case "runeblood_incantation_blue":
    case "malefic_incantation_blue":
      return 1;
    case "insidious_chill_blue":
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
    case "haze_bending_blue":
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
      case "frostbite":
        $modifier += 1;
        AddLayer("TRIGGER", $currentPlayer, "frostbite", "-", "-", $myAuras[$i + 6]);
        break;
      default:
        break;
    }
  }

  for ($i = count($theirAuras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    switch ($theirAuras[$i]) {
      case "channel_lake_frigid_blue":
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
    case "genesis_yellow":
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "MYHAND");
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a card to put in your hero's soul for " . CardLink($auras[$i], $auras[$i]));
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      AddDecisionQueue("SPECIFICCARD", $mainPlayer, "GENESIS", 1);
      break;
    case "blessing_of_savagery_red":
    case "blessing_of_savagery_yellow":
    case "blessing_of_savagery_blue":
      if ($auras[$i] == "blessing_of_savagery_red") $amount = 3;
      else if ($auras[$i] == "blessing_of_savagery_yellow") $amount = 2;
      else $amount = 1;
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "never_yield_blue":
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
    case "blessing_of_patience_red":
    case "blessing_of_patience_yellow":
    case "blessing_of_patience_blue":
      if ($auras[$i] == "blessing_of_patience_red") $amount = 3;
      else if ($auras[$i] == "blessing_of_patience_yellow") $amount = 2;
      else $amount = 1;
      GainHealth($amount, $mainPlayer);
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "mindstate_of_tiger_blue":
      AddPlayerHand("crouching_tiger", $mainPlayer, "-");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "blessing_of_qi_red":
    case "blessing_of_qi_yellow":
    case "blessing_of_qi_blue":
      if ($auras[$i] == "blessing_of_qi_red") $amount = 3;
      else if ($auras[$i] == "blessing_of_qi_yellow") $amount = 2;
      else $amount = 1;
      $index = BanishCardForPlayer("crouching_tiger", $mainPlayer, "-", "TT", $mainPlayer);
      $banish = new Banish($mainPlayer);
      AddDecisionQueue("PASSPARAMETER", $mainPlayer, $banish->Card($index)->UniqueID());
      AddDecisionQueue("ADDLIMITEDCURRENTEFFECT", $mainPlayer, $auras[$i] . ",BANISH");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "blessing_of_steel_red":
    case "blessing_of_steel_yellow":
    case "blessing_of_steel_blue":
      if ($auras[$i] == "blessing_of_steel_red") $amount = 3;
      else if ($auras[$i] == "blessing_of_steel_yellow") $amount = 2;
      else $amount = 1;
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "blessing_of_ingenuity_red":
    case "blessing_of_ingenuity_yellow":
    case "blessing_of_ingenuity_blue":
      if ($auras[$i] == "blessing_of_ingenuity_red") $amount = 3;
      else if ($auras[$i] == "blessing_of_ingenuity_yellow") $amount = 2;
      else $amount = 1;
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      $searchHyper = CombineSearches(SearchDiscardForCard($mainPlayer, "hyper_driver_red", "hyper_driver_yellow", "hyper_driver_blue"), SearchBanishForCardMulti($mainPlayer, "hyper_driver_red", "hyper_driver_yellow", "hyper_driver_blue"));
      $countHyper = count(explode(",", $searchHyper));
      if ($amount > $countHyper) $amount = $countHyper;
      for ($j = 0; $j < $amount; ++$j) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "MYDISCARD:cardID=hyper_driver_red;cardID=hyper_driver_yellow;cardID=hyper_driver_blue&MYBANISH:cardID=hyper_driver_red;cardID=hyper_driver_yellow;cardID=hyper_driver_blue");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose an item to put into play");
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("SETDQVAR", $mainPlayer, "0", 1);
        AddDecisionQueue("MZOP", $mainPlayer, "GETCARDID", 1);
        AddDecisionQueue("PASSPARAMETER", $mainPlayer, "{0}", 1);
        AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
        AddDecisionQueue("PUTPLAY", $mainPlayer, "False", 1);
      }
      break;
    case "blessing_of_focus_red":
    case "blessing_of_focus_yellow":
    case "blessing_of_focus_blue":
      if ($auras[$i] == "blessing_of_focus_red") $amount = 3;
      else if ($auras[$i] == "blessing_of_focus_yellow") $amount = 2;
      else $amount = 1;
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      PlayerOpt($mainPlayer, $amount);
      AddDecisionQueue("SPECIFICCARD", $mainPlayer, "BLESSINGOFFOCUS", 1);
      break;
    case "blessing_of_occult_red":
    case "blessing_of_occult_yellow":
    case "blessing_of_occult_blue":
      if ($auras[$i] == "blessing_of_occult_red") $amount = 3;
      else if ($auras[$i] == "blessing_of_occult_yellow") $amount = 2;
      else $amount = 1;
      PlayAura("runechant", $mainPlayer, $amount, true);
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "blessing_of_aether_red":
    case "blessing_of_aether_yellow":
    case "blessing_of_aether_blue":
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "blessing_of_spirits_red":
    case "blessing_of_spirits_yellow":
    case "blessing_of_spirits_blue":
      if ($auras[$i] == "blessing_of_spirits_red") $amount = 3;
      else if ($auras[$i] == "blessing_of_spirits_yellow") $amount = 2;
      else $amount = 1;
      PlayAura("spectral_shield", $mainPlayer, $amount);
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "chains_of_mephetis_blue":
      if ($auras[$i + 2] > 0) {
        AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_remove_a_Doom_Counter_and_keep_" . CardLink($auras[$i], $auras[$i]) . "_and_keep_it_in_play?");
        AddDecisionQueue("REMOVECOUNTERAURAORDESTROY", $mainPlayer, $auras[$i + 6]);
      } else {
        WriteLog(CardLink($auras[$i], $auras[$i]) . " was destroyed");
        DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      }
      break;
    case "crash_down_red":
    case "earthlore_empowerment_red":
    case "crash_down_yellow":
    case "earthlore_empowerment_yellow":
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "might":
    case "might": //Might
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      IncrementClassState($mainPlayer, $CS_NumMightDestroyed, 1);
      break;
    case "vigor":
    case "vigor": //Vigor
      GainResources($mainPlayer, 1);
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      IncrementClassState($mainPlayer, $CS_NumVigorDestroyed, 1);
      break;
    case "contest_the_mindfield_blue":
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "stacked_in_your_favor_red":
    case "stacked_in_your_favor_yellow":
    case "stacked_in_your_favor_blue":
      $effectSource = $auras[$i];
      WriteLog("Resolving " . CardLink($auras[$i], $auras[$i]) . " ability");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      Draw($mainPlayer, effectSource: $effectSource);
      MZMoveCard($mainPlayer, "MYHAND", "MYTOPDECK", silent: true);
      break;
    case "big_bop_red":
    case "big_bop_yellow":
    case "big_bop_blue":
      AddCurrentTurnEffect($auras[$i] . "-BUFF", $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "bigger_than_big_red":
    case "bigger_than_big_yellow":
    case "bigger_than_big_blue":
      AddCurrentTurnEffect($auras[$i] . "-BUFF", $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "agility": //Agility
      if (!SearchCurrentTurnEffects($auras[$i], $mainPlayer)) AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY");
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      IncrementClassState($mainPlayer, $CS_NumAgilityDestroyed, 1);
      break;
    case "restless_coalescence_yellow":
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY", $auras[$i + 6]);
      break;
    case "sigil_of_solitude_red":
    case "sigil_of_solitude_yellow":
    case "sigil_of_solitude_blue":
      $AurasArray = explode(",", SearchAura($mainPlayer, class: "ILLUSIONIST"));
      if (count($AurasArray) > 1) DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "channel_mount_isen_blue": // Channel Mount Isen
      $character = &GetPlayerCharacter($mainPlayer);
      $eqFrostbiteCount = 0;
      for ($k = 0; $k < count($character); $k += CharacterPieces()) {
        if ($character[$k] == "frostbite") {
          $slot = "";
          for ($j = 0; $j < count($currentTurnEffects); $j += CurrentTurnEffectsPieces()) {
            $effect = explode(",", $currentTurnEffects[$j]);
            if ($effect[0] == "frostbite-" . $character[$k + 11]) {
              $slot = $effect[1];
              if ($slot == "Arms" || $slot == "Legs" || $slot == "Head" || $slot == "Chest") { // Only count these Frostbites if they are in an equipment slot.
                $eqFrostbiteCount += 1;
              }
            }
          }
        }
      }
      LoseHealth($eqFrostbiteCount, $mainPlayer);
      WriteLog("Player $mainPlayer loses " . $eqFrostbiteCount . " life due to ". CardLink("channel_mount_isen_blue", "channel_mount_isen_blue") .".");
      break;
    case "agility_stance_yellow":
      if (!SearchCurrentTurnEffects($auras[$i], $mainPlayer)) AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY"); 
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "flurry_stance_red":
      $character = &GetPlayerCharacter($mainPlayer);
      $weaponIndex1 = CharacterPieces();
      $weaponIndex2 = CharacterPieces() * 2;
      if(SubtypeContains($character[$weaponIndex1], "Dagger")) AddCharacterUses($mainPlayer, $weaponIndex1, 1);
      if(SubtypeContains($character[$weaponIndex2], "Dagger")) AddCharacterUses($mainPlayer, $weaponIndex2, 1);
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "power_stance_blue":
      AddCurrentTurnEffect($auras[$i], $mainPlayer, "PLAY"); // These can stack, so we don't care if the effect is already in play. See: Ancestral Harmony for comparison.
      DestroyAuraUniqueID($mainPlayer, $auras[$i + 6]);
      break;
    case "blessing_of_vynserakai_red":
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
      case "restless_coalescence_yellow":
        AddCurrentTurnEffect($defPlayerAuras[$i], $defPlayer, "PLAY", $defPlayerAuras[$i + 6]);
        break;
      case "channel_mount_isen_blue": // Channel Mount Isen
        $character = &GetPlayerCharacter($mainPlayer);
        $eqFrostbiteCount = 0;
        for ($k = 0; $k < count($character); $k += CharacterPieces()) {
          if ($character[$k] == "frostbite") {
            $slot = "";
            for ($j = 0; $j < count($currentTurnEffects); $j += CurrentTurnEffectsPieces()) {
              $effect = explode(",", $currentTurnEffects[$j]);
              if ($effect[0] == "frostbite-" . $character[$k + 11]) {
                $slot = $effect[1];
                if ($slot == "Arms" || $slot == "Legs" || $slot == "Head" || $slot == "Chest") { // Only count these Frostbites if they are in an equipment slot.
                  $eqFrostbiteCount += 1;
                }
              }
            }
          }
        }
        LoseHealth($eqFrostbiteCount, $mainPlayer);
        WriteLog("Player $mainPlayer loses " . $eqFrostbiteCount . " life due to ". CardLink("channel_mount_isen_blue", "channel_mount_isen_blue") .".");
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
      case "forged_for_war_yellow":
      case "show_time_blue":
      case "blessing_of_deliverance_red":
      case "blessing_of_deliverance_yellow":
      case "blessing_of_deliverance_blue":
      case "emerging_power_red":
      case "emerging_power_yellow":
      case "emerging_power_blue":
      case "stonewall_confidence_red":
      case "stonewall_confidence_yellow":
      case "stonewall_confidence_blue":
      case "chains_of_eminence_red":
      case "stamp_authority_blue":
      case "towering_titan_red":
      case "towering_titan_yellow":
      case "towering_titan_blue":
      case "emerging_dominance_red":
      case "emerging_dominance_yellow":
      case "emerging_dominance_blue":
      case "zen_state":
      case "runeblood_barrier_yellow":
      case "soul_shackle":
      case "emerging_avalanche_red":
      case "emerging_avalanche_yellow":
      case "emerging_avalanche_blue":
      case "strength_of_sequoia_red":
      case "strength_of_sequoia_yellow":
      case "strength_of_sequoia_blue":
      case "embodiment_of_earth":
      case "embolden_red":
      case "embolden_yellow":
      case "embolden_blue":
      case "runeblood_incantation_red":
      case "runeblood_incantation_yellow":
      case "runeblood_incantation_blue":
      case "pyroglyphic_protection_red":
      case "pyroglyphic_protection_yellow":
      case "pyroglyphic_protection_blue":
      case "fog_down_yellow":
      case "sigil_of_protection_red":
      case "sigil_of_protection_yellow":
      case "sigil_of_protection_blue":
      case "tome_of_aeo_blue":
      case "sigil_of_brilliance_yellow":
      case "channel_the_millennium_tree_red":
      case "harvest_season_red":
      case "harvest_season_yellow":
      case "harvest_season_blue":
      case "strong_yield_red":
      case "strong_yield_yellow":
      case "strong_yield_blue":
      case "sigil_of_earth_blue":
      case "sigil_of_lightning_blue":
      case "sigil_of_the_arknight_blue":
      case "sigil_of_deadwood_blue":
      case "sigil_of_aether_blue":// Sigil of aether
      case "sigil_of_temporal_manipulation_blue":
      case "sigil_of_forethought_blue":
      case "sigil_of_cycles_blue":
      case "sigil_of_fyendal_blue":
      case "crumble_to_eternity_blue":
        AddLayer("TRIGGER", $mainPlayer, $auras[$i], "-", "-", $auras[$i + 6]);
        break;
      case "seismic_surge":
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
      case "read_the_ripples_red":
      case "read_the_ripples_yellow":
      case "read_the_ripples_blue":
      case "ponder":
      case "bloodrot_pox":
      case "frailty":
      case "inertia":
      case "earths_embrace_blue":
      case "sharpened_senses_yellow":
        AddLayer("TRIGGER", $mainPlayer, $auras[$i], "-", "-", $auras[$i + 6]);
        break;
      case "fealty":
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
      case "truce_blue":
        AddLayer("TRIGGER", $defPlayer, $auras[$i], "truce_blue-1", uniqueID: $auras[$i + 6]);
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
      case "channel_mount_heroic_red":
      case "channel_the_millennium_tree_red":
        ChannelTalent($i, "EARTH");
        break;
      case "channel_lake_frigid_blue":
      case "channel_mount_isen_blue":
        ChannelTalent($i, "ICE");
        break;
      case "channel_thunder_steppe_yellow":
      case "channel_lightning_valley_yellow":
        ChannelTalent($i, "LIGHTNING");
        break;
      case "burn_them_all_red":
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
      case "channel_the_bleak_expanse_blue":
        ChannelTalent($i, "ICE");
        break;
      case "frostbite":
        FrostHexEndTurnAbility($mainPlayer);
        $remove = 1;
        break;
      case "looming_doom_blue":
        if ($auras[$i + 2] == 0) $remove = 1;
        else {
          --$auras[$i + 2];
          DealArcane(2, 2, "PLAYCARD", "looming_doom_blue", false, $mainPlayer);
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
      case "frostbite":
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
      case "enchanting_melody_red":
      case "enchanting_melody_yellow":
      case "enchanting_melody_blue":
        if (GetClassState($mainPlayer, $CS_NumNonAttackCards) == 0) $remove = true;
        break;
      case "sting_of_sorcery_blue":
        $remove = true;
        break;
      case "hypothermia_blue":
        $remove = true;
        break;
      case "ironsong_pride_red":
        if (GetClassState($mainPlayer, $CS_HitsWithSword) <= 0) $remove = true;
        break;
      case "prowess_of_agility_blue":
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
    case "enchanting_melody_red":
      $preventedDamage = 4;
      break;
    case "enchanting_melody_yellow":
      $preventedDamage = 3;
      break;
    case "enchanting_melody_blue":
      $preventedDamage = 2;
      break;
    case "radiant_forcefield_yellow":
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
  if (!$check && SearchCurrentTurnEffects("vambrace_of_determination", $player) != "" && $preventedDamage > 0) {//vambrace
    $preventedDamage -= 1;
    SearchCurrentTurnEffects("vambrace_of_determination", $player, remove:true);
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
      case "zen_state":
        if ($preventable) $damage -= 1;
        break;
      case "runeblood_barrier_yellow":
        if ($auras[$i + 1] == 2) {
          $auras[$i + 1] = 1;
          $numRunchants = CountAura("runechant", $player);
          if ($numRunchants <= $damage) {
            for ($j = 0; $j < $numRunchants; $j++) {
              $index = SearchAurasForIndex("runechant", $player);
              if ($index != -1) DestroyAuraUniqueID($player, $auras[$index + 6]);
            }
            if ($numRunchants > 1) WriteLog($numRunchants . " " . CardLink("runechant", "runechant") . "s were destroyed");
            else WriteLog($numRunchants . " " . CardLink("runechant", "runechant") . " was destroyed");
            if ($preventable) $damage -= $numRunchants;
          } else {
            for ($j = 0; $j < $damage; $j++) {
              $index = SearchAurasForIndex("runechant", $player);
              if ($index != -1) DestroyAuraUniqueID($player, $auras[$index + 6]);
            }
            if ($damage > 1) WriteLog($damage . " " . CardLink("runechant", "runechant") . "s were destroyed");
            else WriteLog($damage . " " . CardLink("runechant", "runechant") . " was destroyed");
            if ($preventable) $damage -= $damage;
          }
        }
        break;
      case "pyroglyphic_protection_red":
        if ($type == "ARCANE" && $preventable) $damage -= 3;
        break;
      case "pyroglyphic_protection_yellow":
        if ($type == "ARCANE" && $preventable) $damage -= 2;
        break;
      case "pyroglyphic_protection_blue":
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
      case "bloodspill_invocation_red":
      case "bloodspill_invocation_yellow":
      case "bloodspill_invocation_blue":
      case "nerves_of_steel_blue":
      case "arcane_cussing_red":
      case "arcane_cussing_yellow":
      case "arcane_cussing_blue":
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
      case "channel_lightning_valley_yellow":
        if(GetClassState($otherPlayer, $CS_DamageDealt) == 0 && GetClassState($otherPlayer, $CS_ArcaneDamageDealt) == 0 && $damage > 0 && $otherAuras[$i + 5] > 0){
          $otherAuras[$i + 5] -= 1;
          if (CardType($source) != "AA" || !SearchCurrentTurnEffects("tarpit_trap_yellow", $otherPlayer) && !HitEffectsArePrevented($source)) {
            AddLayer("TRIGGER", $otherPlayer, $otherAuras[$i], uniqueID: $otherAuras[$i + 6]);
          }
        }
        elseif (GetClassState($player, $CS_DamageTaken) == 0 && GetClassState($player, $CS_ArcaneDamageTaken) == 0 && $damage > 0 && $otherAuras[$i + 5] > 0) {
          $otherAuras[$i + 5] -= 1;
          if (CardType($source) != "AA" || !SearchCurrentTurnEffects("tarpit_trap_yellow", $otherPlayer) && !HitEffectsArePrevented($source)) {
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
      case "arcane_cussing_red":
      case "arcane_cussing_yellow":
      case "arcane_cussing_blue":
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
      case "dimenxxional_crossroads_yellow":
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
      case "quicken":
        if (($cardType == "AA" && (GetResolvedAbilityType($cardID) == "" || GetResolvedAbilityType($cardID) == "AA"))
          || (DelimStringContains($cardSubType, "Aura") && $from == "PLAY" && (GetResolvedAbilityType($cardID) == "" || GetResolvedAbilityType($cardID) == "AA"))
          || (TypeContains($cardID, "W") && GetResolvedAbilityType($cardID) == "AA" && $from == "EQUIP")) {
          WriteLog(CardLink($auras[$i], $auras[$i]) . " gives the attack go again");
          GiveAttackGoAgain();
          $remove = 1;
        }
        break;
      case "runechant":
        if (($cardType == "AA" && GetResolvedAbilityType($cardID) != "I" && $from != "PLAY") || (DelimStringContains($cardSubType, "Aura") && $from == "PLAY") || ((TypeContains($cardID, "W", $currentPlayer) && GetResolvedAbilityType($cardID) == "AA")) && GetResolvedAbilityType($cardID) != "I") {
          AddLayer("TRIGGER", $currentPlayer, $auras[$i], "-", "-", $auras[$i + 6]);
        }
        break;
      case "dimenxxional_crossroads_yellow":
        DimenxxionalCrossroadsPassive($cardID, $from);
        break;
      case "embodiment_of_lightning":
        if ($cardType == "AA" && GetResolvedAbilityType($cardID) != "I") {
          AddLayer("TRIGGER", $currentPlayer, $auras[$i], "-", $cardID, $auras[$i + 6]);
        }
        break;
      case "pierce_reality_blue":
        if ($auras[$i + 5] > 0 && CardType($cardID) == "AA" && ClassContains($cardID, "ILLUSIONIST", $currentPlayer) && GetClassState($currentPlayer, $CS_NumIllusionistActionCardAttacks) <= 1) {
          WriteLog(CardLink($auras[$i], $auras[$i]) . " gives the attack +2");
          --$auras[$i + 5];
          AddCurrentTurnEffect("pierce_reality_blue", $currentPlayer, true);
        }
        break;
      case "channel_thunder_steppe_yellow":
        if ((DelimStringContains($cardType, "A") || $cardType == "AA") && (GetResolvedAbilityType($cardID, $from) == "" || GetResolvedAbilityType($cardID, $from) == "AA" || GetResolvedAbilityType($cardID, $from) == "A")) {
          AddLayer("TRIGGER", $currentPlayer, $auras[$i], $cardType, "-", $auras[$i + 6]);
        }
        break;
      case "courage":
        if ($cardType == "AA" && (GetResolvedAbilityType($cardID, $from) == "" || GetResolvedAbilityType($cardID, $from) == "AA")
          || (DelimStringContains($cardSubType, "Aura") && $from == "PLAY")
          || ((TypeContains($cardID, "W", $currentPlayer) && GetResolvedAbilityType($cardID) != "A")) && GetResolvedAbilityType($cardID) != "I") {
          AddCurrentTurnEffect("courage", $currentPlayer);
          $remove = 1;
        }
        break;
      case "eloquence":
        if (DelimStringContains($cardType, "A") && $from != "PLAY") {
          WriteLog(CardLink($auras[$i], $auras[$i]) . " gives the next non-attack action card go again");
          AddLayer("TRIGGER", $currentPlayer, $auras[$i], $cardType, "-", $auras[$i + 6]);
        }
        break;
      case "malefic_incantation_red":
      case "malefic_incantation_yellow":
      case "malefic_incantation_blue":
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
      case "sting_of_sorcery_blue":
        if ($attackType == "AA") {
          AddLayer("TRIGGER", $mainPlayer, $auras[$i], "-", $attackID, $auras[$i + 6]);
        }
        break;
      case "shimmers_of_silver_blue":
        if ($auras[$i + 5] > 0 && DelimStringContains(CardSubtype($attackID), "Aura") && ClassContains($attackID, "ILLUSIONIST", $mainPlayer)) {
          $index = GetClassState($mainPlayer, $CS_PlayIndex);
          WriteLog(CardLink($auras[$i], $auras[$i]) . " puts a +1 counter on " . CardLink($auras[$index], $auras[$index]));
          ++$auras[$index + 3];
          --$auras[$i + 5];
        }
        break;
      case "passing_mirage_blue":
        if ($auras[$i + 5] > 0 && ClassContains($attackID, "ILLUSIONIST", $mainPlayer) && GetClassState($mainPlayer, $CS_NumIllusionistAttacks) <= 1) {
          WriteLog(CardLink($auras[$i], $auras[$i]) . " makes your first illusionist attack each turn lose Phantasm");
          --$auras[$i + 5];
          AddCurrentTurnEffect("passing_mirage_blue", $mainPlayer, true);
        }
        break;
      case "burn_them_all_red":
        if ($auras[$i + 5] > 0 && DelimStringContains(CardSubType($attackID), "Dragon")) {
          --$auras[$i + 5];
          AddLayer("TRIGGER", $mainPlayer, $auras[$i], "-", $attackID, $auras[$i + 6]);
        }
        break;
      case "prowess_of_agility_blue":
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
      case "bloodspill_invocation_red":
      case "bloodspill_invocation_yellow":
      case "bloodspill_invocation_blue":
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
        case "channel_mount_heroic_red":
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
        case "sharpened_senses_yellow":
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
      case "parable_of_humility_yellow":
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
      case "contest_the_mindfield_blue":
        $intellectModifier -= 1;
        break;
      default:
        break;
    }
  }
  $auras = &GetAuras($otherPlayer);
  for ($i = 0; $i < count($auras); $i += AuraPieces()) {
    switch ($auras[$i]) {
      case "contest_the_mindfield_blue":
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
    case "v_for_valor_red":
    case "v_for_valor_yellow":
    case "v_for_valor_blue":
      $hand = &GetHand($currentPlayer);
      if (count($hand) == 0) {
        WriteLog("You do not have a card to charge. Reverting gamestate.", highlight: true);
        RevertGamestate();
        return;
      }
      DestroyAura($currentPlayer, $index);
      Charge(may: false);
      break;
    case "restless_coalescence_yellow":
      $abilityType = GetResolvedAbilityType($cardID);
      if ($abilityType == "I" && $from == "PLAY" && SearchCurrentTurnEffectsForUniqueID($auras[$index + 6]) != -1) {
        --$auras[$index + 3];
        RemoveCurrentTurnEffect(SearchCurrentTurnEffectsForUniqueID($auras[$index + 6]));
        AddCurrentTurnEffect($cardID, $currentPlayer, "", $auras[$index + 6] . "-PAID");
      } elseif ($abilityType == "AA") {
        $auras[$index + 1] = 1;
      }
      break;
    case "fealty":
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
      case "channel_mount_heroic_red":
        if (CardType($cardID) == "AA") $attackModifier += 3;
      default:
        break;
    }
  }
  return $attackModifier;
}
