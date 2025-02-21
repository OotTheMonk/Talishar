<?php

function BanishCardForPlayer($cardID, $player, $from, $mod = "-", $banishedBy = "")
{
  global $mainPlayer, $mainPlayerGamestateStillBuilt, $myBanish, $theirBanish, $mainBanish, $defBanish;
  global $myClassState, $theirClassState, $mainClassState, $defClassState;
  global $myStateBuiltFor, $CS_NumCrouchingTigerCreatedThisTurn;
  if (CardNameContains($cardID, "Crouching Tiger", $player)) IncrementClassState($player, $CS_NumCrouchingTigerCreatedThisTurn);
  if ($mainPlayerGamestateStillBuilt) {
    if ($player == $mainPlayer) return BanishCard($mainBanish, $mainClassState, $cardID, $mod, $player, $from, $banishedBy);
    else return BanishCard($defBanish, $defClassState, $cardID, $mod, $player, $from, $banishedBy);
  } else {
    if ($player == $myStateBuiltFor) return BanishCard($myBanish, $myClassState, $cardID, $mod, $player, $from, $banishedBy);
    else return BanishCard($theirBanish, $theirClassState, $cardID, $mod, $player, $from, $banishedBy);
  }
}

function BanishCard(&$banish, &$classState, $cardID, $mod, $player = "", $from = "", $banishedBy = "")
{
  global $CS_CardsBanished, $actionPoints, $CS_Num6PowBan, $currentPlayer, $mainPlayer, $CS_NumEarthBanished, $EffectContext;
  $rv = -1;
  if ($player == "") $player = $currentPlayer;
  $otherPlayer = $player == 1 ? 2 : 1;
  $character = &GetPlayerCharacter($player);
  $characterID = ShiyanaCharacter($character[0]);
  AddEvent("BANISH", (isFaceDownMod($mod) ? "CardBack" : $cardID));
  //Effects that change the modifier
  if ($characterID == "levia_redeemed" && $character[1] < 3) {
    AddLayer("TRIGGER", $player, $characterID);
    if ($mod != "INT") $mod = "levia_redeemed";
  }
  //Do effects that change where it goes, or banish it if not
  if ($from == "DECK" && (SearchCharacterActive($player, "data_doll_mkii") || SearchCurrentTurnEffects("data_doll_mkii-SHIYANA", $player)) && CardSubType($cardID, $from) == "Item" && CardCost($cardID, $from) <= 2) {
    $character = &GetPlayerCharacter($player);
    AddLayer("TRIGGER", $player, $character[0], $cardID);
  }
  if (!TypeContains($cardID, "T", $player)) { //If you banish a token, the token ceases to exist.
    $rv = count($banish);
    array_push($banish, $cardID);
    array_push($banish, $mod);
    array_push($banish, GetUniqueId($cardID, $player));
  }
  ++$classState[$CS_CardsBanished];
  if (isFaceDownMod($mod)) return $rv;
  //Do additional effects
  if ($cardID == "slithering_shadowpede_red" && $from == "HAND" && $mod != "levia_redeemed" && ($mod != "NOFEAR" || $player == $mainPlayer)) $banish[count($banish) - 2] = "TT";
  if (($mod == "BOOST" || $from == "DECK") 
  && ($cardID == "back_alley_breakline_red" || $cardID == "back_alley_breakline_yellow" || $cardID == "back_alley_breakline_blue") 
  && (TypeContains($EffectContext, "A", $player) || TypeContains($EffectContext, "AA", $player) || GetAbilityType($EffectContext) != "")
  && $player == $mainPlayer) {
    WriteLog("Player ". $player ." gained 1 action point from " . CardLink($cardID, $cardID).".");
    ++$actionPoints;
  }
  if (($mod == "BOOST" && $from == "DECK") && ($cardID == "crankshaft_red" || $cardID == "crankshaft_yellow" || $cardID == "crankshaft_blue")) {
    WriteLog(CardLink($cardID, $cardID) . " was banished to pay a boost cost. Put a counter on a Hyper Drive you control.");
    AddLayer("TRIGGER", $player, $cardID);
  }
  if (ModifiedAttackValue($cardID, $player, $from, source: $banishedBy) >= 6) {
    if ($classState[$CS_Num6PowBan] == 0 && $player == $mainPlayer && ($characterID == "levia_shadowborn_abomination" || $characterID == "levia") && $character[1] == 2) { // Levia
      WriteLog(CardLink($characterID, $characterID) . " banished a card with 6+ power, and won't lose life from Blood Debt this turn.");
    }
    ++$classState[$CS_Num6PowBan];
    $index = FindCharacterIndex($player, "hooves_of_the_shadowbeast");
    if ($index >= 0 && IsCharacterAbilityActive($player, $index, checkGem: true) && $player == $mainPlayer && SearchLayersForCardID("hooves_of_the_shadowbeast") == -1) {
      AddLayer("TRIGGER", $player, $character[$index]);
    }
  }
  if(TalentContains($cardID, "EARTH", $player)) {
    ++$classState[$CS_NumEarthBanished];
  }
  if (TypeContains($cardID, "E", $player)) {
    $charIndex = FindCharacterIndex($player, $cardID);
    if ($charIndex == -1) {
      DestroyCharacter($player, $charIndex, skipDestroy: true);
      CharacterBanishEffect($cardID, $player);
    } else DestroyCharacter($player, $charIndex, wasBanished: true);
  }
  if ($banishedBy != "" && $player != $mainPlayer) CheckContracts($mainPlayer, $cardID);
  if ($banishedBy == "nasreth_the_soul_harrower" && TalentContains($cardID, "LIGHT", $player)) {
    GainHealth(1, $otherPlayer);
    return $rv;
  }
  if ($banishedBy == "persuasive_prognosis_blue" && (DelimStringContains(CardType($cardID), "A") || CardType($cardID) == "AA")) {
    GainHealth(1, $otherPlayer);
    return $rv;
  }
  if ($banishedBy == "art_of_desire_body_red" && ColorContains($cardID, 1, $player)) {
    Draw($otherPlayer);
    GainHealth(1, $otherPlayer);
    return $rv;
  }
  if ($banishedBy == "art_of_desire_soul_yellow" && ColorContains($cardID, 2, $player)) {
    Draw($otherPlayer);
    GainHealth(1, $otherPlayer);
    return $rv;
  }
  if ($banishedBy == "art_of_desire_mind_blue" && ColorContains($cardID, 3, $player)) {
    Draw($otherPlayer);
    GainHealth(1, $otherPlayer);
    return $rv;
  }
  if ($banishedBy == "bonds_of_attraction_red" || $banishedBy == "bonds_of_attraction_yellow" || $banishedBy == "bonds_of_attraction_blue" && count($banish) / BanishPieces() >= 2) {
    $count = count($banish) - BanishPieces();
    $pitchValues = [];
    for ($i = $count; $i >= 0; $i--) {
      if ($banish[$i + 1] == "Source-" . $banishedBy) {
        array_push($pitchValues, PitchValue($banish[$i]));
      }
    }
    if (count($pitchValues) !== count(array_unique($pitchValues))) {
      GainHealth(1, $otherPlayer);
    }
    return $rv;
  }
  if (($banishedBy == "bonds_of_memory_red" || $banishedBy == "bonds_of_memory_yellow" || $banishedBy == "bonds_of_memory_blue") && count($banish) / BanishPieces() >= 2) {
    $count = count($banish) - BanishPieces();
    $cardNames = [];
    for ($i = $count; $i >= 0; $i--) {
      if ($banish[$i + 1] == "Source-" . $banishedBy) {
        array_push($cardNames, CardName($banish[$i]));
      }
    }
    if (count($cardNames) !== count(array_unique($cardNames))) {
      GainHealth(1, $otherPlayer);
    }
    return $rv;
  }
  if (($banishedBy == "desires_of_flesh_red" || $banishedBy == "desires_of_flesh_yellow" || $banishedBy == "desires_of_flesh_blue") && TypeContains($cardID, "AA", $player)) {
    GainHealth(1, $otherPlayer);
    return $rv;
  }
  if (($banishedBy == "impulsive_desire_red" || $banishedBy == "impulsive_desire_yellow" || $banishedBy == "impulsive_desire_blue") && (TypeContains($cardID, "AR", $player) || TypeContains($cardID, "DR", $player) || TypeContains($cardID, "I", $player))) {
    GainHealth(1, $otherPlayer);
    return $rv;
  }
  if (($banishedBy == "minds_desire_red" || $banishedBy == "minds_desire_yellow" || $banishedBy == "minds_desire_blue") && TypeContains($cardID, "A", $player)) {
    GainHealth(1, $otherPlayer);
    return $rv;
  }
  return $rv;
}

function RemoveBanish($player, $index)
{
  $banish = &GetBanish($player);
  if($index == -1) return "";
  $cardID = $banish[$index];
  for ($i = $index + BanishPieces() - 1; $i >= $index; --$i) {
    unset($banish[$i]);
  }
  $banish = array_values($banish);
  return $cardID;
}

//When it matters, make it save this off to a different zone
function TurnBanishFaceDown($player, $index)
{
  $banish = &GetBanish($player);
  $banish[$index + 1] = "FACEDOWN";
}

function AddBottomDeck($cardID, $player, $from)
{
  if(TypeContains($cardID, "T", $player)) { // 'T' type indicates the card is a token
    WriteLog(CardLink($cardID, $cardID) . " is a token. So instead of going on the bottom of the deck, it ceases to exist.");
  }
  else {
  $deck = &GetDeck($player);
  array_push($deck, $cardID);
  }
}

function AddTopDeck($cardID, $player, $from, $deckIndexModifier = 0)
{
  $deck = &GetDeck($player);
  if ($deckIndexModifier == 0) {
    array_unshift($deck, $cardID);
    return;
  }
  array_splice($deck, $deckIndexModifier, 0, $cardID);
}

function AddPlayerHand($cardID, $player, $from, $amount = 1)
{
  global $CS_NumCrouchingTigerCreatedThisTurn;
  if(TypeContains($cardID, "T", $player)) { // 'T' type indicates the card is a token
    WriteLog(CardLink($cardID, $cardID) . " is a token. So instead of going to hand, it ceases to exist.");
  }
  else {
  $hand = &GetHand($player);
  if (CardNameContains($cardID, "Crouching Tiger", $player)) IncrementClassState($player, $CS_NumCrouchingTigerCreatedThisTurn);
    for ($i = 0; $i < $amount; ++$i) {
      array_push($hand, $cardID);
    }
  }
}

function RemoveHand($player, $index)
{
  $hand = &GetHand($player);
  if (empty($hand)) return "";
  $cardID = $hand[$index];
  array_splice($hand, $index, HandPieces());
  return $cardID;
}

function RemoveDeck($player, $index)
{
  $deck = &GetDeck($player);
  if (empty($deck)) return "";
  $cardID = $deck[$index];
  array_splice($deck, $index, DeckPieces());
  return $cardID;
}

function RemoveDiscard($player, $index)
{
  $discard = &GetDiscard($player);
  if (empty($discard)) return "";
  $cardID = $discard[$index];
  array_splice($discard, $index, DiscardPieces());
  return $cardID;
}

function GainResources($player, $amount)
{
  $resources = &GetResources($player);
  $resources[0] += $amount;
}

function AddResourceCost($player, $amount)
{
  $resources = &GetResources($player);
  $resources[1] += $amount;
}

function RemovePitch($player, $index)
{
  $pitch = &GetPitch($player);
  $cardID = $pitch[$index];
  unset($pitch[$index]);
  $pitch = array_values($pitch);
  return $cardID;
}

function AddArsenal($cardID, $player, $from, $facing, $counters = 0)
{
  global $mainPlayer, $EffectContext;
  $arsenal = &GetArsenal($player);
  $character = &GetPlayerCharacter($player);
  $cardSubType = CardSubType($cardID);
  if ($facing == "UP" && $from == "DECK" && $cardSubType == "Arrow" && FindCharacterIndex($player, "sandscour_greatbow") != -1) $counters = 1;
  array_push($arsenal, $cardID);
  array_push($arsenal, $facing);
  array_push($arsenal, 1); //Num uses - currently always 1
  array_push($arsenal, $counters); //Counters
  array_push($arsenal, "0"); //Is Frozen (1 = Frozen)
  array_push($arsenal, GetUniqueId($cardID, $player)); //Unique ID
  $otherPlayer = $player == 1 ? 2 : 1;
  if ($facing == "UP") {
    if ($from == "DECK" && ($cardID == "back_alley_breakline_red" || $cardID == "back_alley_breakline_yellow" || $cardID == "back_alley_breakline_blue") && (TypeContains($EffectContext, "A", $player) || TypeContains($EffectContext, "AA", $player) || GetResolvedAbilityType($EffectContext, $from) == "A")) {
      if ($player == $mainPlayer) {
        WriteLog("Player ". $player ." gained 1 action point from " . CardLink($cardID, $cardID).".");
        GainActionPoints(1);
      }    }
    if ($from == "DECK" && CardSubType($cardID) == "Arrow" && SearchCharacterActive($player, "crows_nest")) {
      AddLayer("TRIGGER", $player, "crows_nest", "-", "-", -1);
    }
    switch ($cardID) {
      case "head_shot_red":
      case "head_shot_yellow":
      case "head_shot_blue":
        AddCurrentTurnEffect($cardID, $player);
        break;
      case "ridge_rider_shot_red":
      case "ridge_rider_shot_yellow":
      case "ridge_rider_shot_blue":
        Opt($cardID, 1);
        break;
      case "remorseless_red":
        AddCurrentTurnEffect($cardID, $otherPlayer);
        break;
      case "spire_sniping_red":
      case "spire_sniping_yellow":
      case "spire_sniping_blue":
        SpireSnipingAbility($player);
        break;
      default:
        break;
    }
  }
}

function ArsenalEndTurn($player)
{
  $arsenal = &GetArsenal($player);
  for ($i = 0; $i < count($arsenal); $i += ArsenalPieces()) {
    $arsenal[$i + 2] = 1;//Num uses - currently always 1
  }
}

function SetArsenalFacing($facing, $player)
{
  $arsenal = &GetArsenal($player);
  for ($i = 0; $i < count($arsenal); $i += ArsenalPieces()) {
    if ($facing == "UP" && $arsenal[$i + 1] == "DOWN") {
      $arsenal[$i + 1] = "UP";
      ArsenalTurnFaceUpAbility($arsenal[$i], $player);
      return $arsenal[$i];
    }
  }
  return "";
}

function ArsenalTurnFaceUpAbility($cardID, $player)
{
  switch ($cardID) {
    case "spire_sniping_red":
    case "spire_sniping_yellow":
    case "spire_sniping_blue":
      SpireSnipingAbility($player);
      break;
    default:
      break;
  }
}

function RemoveArsenal($player, $index)
{
  $arsenal = &GetArsenal($player);
  if (count($arsenal) == 0) return "";
  $cardID = $arsenal[$index];
  for ($i = $index + ArsenalPieces() - 1; $i >= $index; --$i) {
    unset($arsenal[$i]);
  }
  $arsenal = array_values($arsenal);
  return $cardID;
}

function DestroyArsenal($player, $index = -1, $effectController = "", $allArsenal = true)
{
  $arsenal = &GetArsenal($player);
  $cardIDs = "";
  for ($i = 0; $i < count($arsenal); $i += ArsenalPieces()) {
    if ($index > -1 && $index != $i) continue;
    if ($cardIDs != "") $cardIDs .= ",";
    $cardIDs .= $arsenal[$i];
    WriteLog(CardLink($arsenal[$i], $arsenal[$i]) . " was destroyed from the arsenal");
    AddGraveyard($arsenal[$i], $player, "ARS", $effectController);
    RemoveArsenal($player, $i);
  }
  if ($allArsenal) $arsenal = [];
  else $arsenal = array_values($arsenal);

  return $cardIDs;
}

function AddSoul($cardID, $player, $from, $isMainPhase = true)
{
  global $mainPlayer, $mainPlayerGamestateStillBuilt, $combatChain;
  global $mySoul, $theirSoul, $mainSoul, $defSoul;
  AddEvent("SOUL", $cardID);
  global $CS_NumAddedToSoul, $CS_NumYellowPutSoul;
  global $myStateBuiltFor;
  if ($cardID == "spirit_of_eirina_yellow") {
    WriteLog("The spirit of Eirina is inside you, always.");
    PutItemIntoPlayForPlayer($cardID, $player);
  } else {
    if ($mainPlayerGamestateStillBuilt) {
      if ($player == $mainPlayer) AddSpecificSoul($cardID, $mainSoul, $from);
      else AddSpecificSoul($cardID, $defSoul, $from);
    } else {
      if ($player == $myStateBuiltFor) AddSpecificSoul($cardID, $mySoul, $from);
      else AddSpecificSoul($cardID, $theirSoul, $from);
    }
    IncrementClassState($player, $CS_NumAddedToSoul);
    if (ColorContains($cardID, 2, $player)) IncrementClassState($player, $CS_NumYellowPutSoul);
    if ($isMainPhase && str_contains(NameOverride($cardID, $player), "Herald") && (SearchCharacterActive($player, "prism_awakener_of_sol") || SearchCharacterActive($player, "prism_advent_of_thrones"))) {
      if ($from != "CC") {
        $char = GetPlayerCharacter($player);
        AddLayer("TRIGGER", $player, $char[0]);
      } elseif (CardNameContains($combatChain[0], "Herald", $player, true)) {
        $char = GetPlayerCharacter($player);
        AddLayer("TRIGGER", $player, $char[0]);
      }
    }
    if ($player == $mainPlayer)
      if (SearchCharacterAlive($player, "empyrean_rapture") && !SearchCurrentTurnEffects("empyrean_rapture", $player) && CardNameContains($cardID, "Herald", $player, true)) AddCurrentTurnEffect("empyrean_rapture", $player);
  }
}

function AddSpecificSoul($cardID, &$soul, $from)
{
  array_push($soul, $cardID);
}

function BanishFromSoul($player, $index = 0)
{
  global $mainPlayer, $mainPlayerGamestateStillBuilt;
  global $mySoul, $theirSoul, $mainSoul, $defSoul;
  global $myStateBuiltFor;
  if ($mainPlayerGamestateStillBuilt) {
    if ($player == $mainPlayer) BanishFromSpecificSoul($mainSoul, $player, $index);
    else BanishFromSpecificSoul($defSoul, $player, $index);
  } else {
    if ($player == $myStateBuiltFor) BanishFromSpecificSoul($mySoul, $player, $index);
    else BanishFromSpecificSoul($theirSoul, $player, $index);
  }
}

function BanishFromSpecificSoul(&$soul, $player, $index = 0)
{
  if (count($soul) == 0) return;
  $cardID = $soul[$index];
  unset($soul[$index]);
  $soul = array_values($soul);
  BanishCardForPlayer($cardID, $player, "SOUL", "SOUL");
}

function RemoveSoul($player, $index)
{
  $soul = &GetSoul($player);
  $cardID = $soul[$index];
  unset($soul[$index]);
  $soul = array_values($soul);
  return $cardID;
}

function EffectArcaneBonus($source)
{
  $idArr = explode(",", $source);
  $source = $idArr[0];
  $modifier = (count($idArr) > 1 ? $idArr[1] : 0);
  switch ($source) {
    case "crucible_of_aetherweave":
      return 1;
    case "tome_of_aetherwind_red":
      return 1;
    case "absorb_in_aether_red":
    case "absorb_in_aether_yellow":
    case "absorb_in_aether_blue":
      return 2;
    case "stir_the_aetherwinds_red":
      return 3;
    case "stir_the_aetherwinds_yellow":
      return 2;
    case "stir_the_aetherwinds_blue":
      return 1;
    case "aether_flare_red":
    case "aether_flare_yellow":
    case "aether_flare_blue":
      return intval($modifier);
    case "metacarpus_node":
      return 1;
    case "cindering_foresight_red":
    case "cindering_foresight_yellow":
    case "cindering_foresight_blue":
      return 1;
    case "rousing_aether_red":
    case "rousing_aether_yellow":
    case "rousing_aether_blue":
      return 1;
    case "surgent_aethertide":
      return intval($modifier);
    case "blessing_of_aether_red":
      return 3;
    case "blessing_of_aether_yellow":
      return 2;
    case "blessing_of_aether_blue":
      return 1;
    case "tempest_aurora_red":
    case "tempest_aurora_yellow":
    case "tempest_aurora_blue":
      return 1;
    default:
      return 0;
  }
}

function AssignArcaneBonus($playerID)
{
  global $currentTurnEffects, $layers;
  $layerIndex = 0;
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $playerID && EffectArcaneBonus($currentTurnEffects[$i]) > 0) {
      $skip = intval($currentTurnEffects[$i + 2]) != -1;
      switch ($currentTurnEffects[$i]) {
        case "tempest_aurora_red":
          if (CardCost($layers[$layerIndex]) > 2) $skip = true;
          break;
        case "tempest_aurora_yellow":
          if (CardCost($layers[$layerIndex]) > 1) $skip = true;
          break;
        case "tempest_aurora_blue":
          if (CardCost($layers[$layerIndex]) > 0) $skip = true;
          break;
        default:
          break;
      }
      if (!$skip) {
        while ($layers[$layerIndex] == "TRIGGER") {
          $layerIndex += LayerPieces();
        }
        WriteLog("Arcane bonus from " . CardLink($currentTurnEffects[$i], $currentTurnEffects[$i]) . " associated with " . CardLink($layers[$layerIndex], $layers[$layerIndex]));
        $uniqueID = $layers[$layerIndex + 6];
        $currentTurnEffects[$i + 2] = $uniqueID;
      }
    }
  }
}

function ClearNextCardArcaneBuffs($player, $playedCard = "", $from = "")
{
  global $currentTurnEffects;
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectsPieces()) {
    $remove = 0;
    if ($currentTurnEffects[$i + 1] == $player) {
      switch ($currentTurnEffects[$i]) {
        case "blessing_of_aether_red":
        case "blessing_of_aether_yellow":
        case "blessing_of_aether_blue":
          if (!IsStaticType(CardType($playedCard), $from, $playedCard)) $remove = 1;
          break;
        default:
          break;
      }
    }
    if ($remove == 1) RemoveCurrentTurnEffect($i);
  }
}

function ConsumeArcaneBonus($player, $noRemove = false)
{
  global $currentTurnEffects, $CS_ResolvingLayerUniqueID;
  $uniqueID = GetClassState($player, $CS_ResolvingLayerUniqueID);
  $totalBonus = 0;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = 0;
    if ($currentTurnEffects[$i + 1] == $player && ($currentTurnEffects[$i + 2] == $uniqueID || DelimStringContains($uniqueID, "MELD", true))) {
      $bonus = EffectArcaneBonus($currentTurnEffects[$i]);
      if ($bonus > 0) {
        $totalBonus += $bonus;
        if (!$noRemove) $remove = 1;
      }
    }
    if ($remove == 1) RemoveCurrentTurnEffect($i);
  }
  return $totalBonus;
}

function ConsumeDamagePrevention($player)
{
  global $CS_NextDamagePrevented;
  $prevention = GetClassState($player, $CS_NextDamagePrevented);
  SetClassState($player, $CS_NextDamagePrevented, 0);
  return $prevention;
}

function IncrementClassState($player, $piece, $amount = 1)
{
  SetClassState($player, $piece, (GetClassState($player, $piece) + $amount));
}

function AppendClassState($player, $piece, $value)
{
  $currentState = GetClassState($player, $piece);
  if ($currentState == "-") $currentState = "";
  if ($currentState != "") $currentState .= ",";
  $currentState .= $value;
  SetClassState($player, $piece, $currentState);
}

function SetClassState($player, $piece, $value)
{
  global $mainPlayer, $mainPlayerGamestateStillBuilt;
  global $myClassState, $theirClassState, $mainClassState, $defClassState;
  global $myStateBuiltFor;
  if ($mainPlayerGamestateStillBuilt) {
    if ($player == $mainPlayer) $mainClassState[$piece] = $value;
    else $defClassState[$piece] = $value;
  } else {
    if ($player == $myStateBuiltFor) $myClassState[$piece] = $value;
    else $theirClassState[$piece] = $value;
  }
}

function AddCharacterEffect($player, $index, $effect)
{
  global $mainPlayer, $mainPlayerGamestateStillBuilt;
  global $myCharacterEffects, $theirCharacterEffects, $mainCharacterEffects, $defCharacterEffects;
  global $myStateBuiltFor;
  if ($mainPlayerGamestateStillBuilt) {
    if ($player == $mainPlayer) {
      array_push($mainCharacterEffects, $index);
      array_push($mainCharacterEffects, $effect);
    } else {
      array_push($defCharacterEffects, $index);
      array_push($defCharacterEffects, $effect);
    }
  } else {
    if ($player == $myStateBuiltFor) {
      array_push($myCharacterEffects, $index);
      array_push($myCharacterEffects, $effect);
    } else {
      array_push($theirCharacterEffects, $index);
      array_push($theirCharacterEffects, $effect);
    }
  }
}

function AddGraveyard($cardID, $player, $from, $effectController = "")
{
  global $mainPlayer, $mainPlayerGamestateStillBuilt;
  global $myDiscard, $theirDiscard, $mainDiscard, $defDiscard;
  global $myStateBuiltFor, $CS_CardsEnteredGY, $EffectContext;
  if ($from == "DECK" && ($cardID == "back_alley_breakline_red" || $cardID == "back_alley_breakline_yellow" || $cardID == "back_alley_breakline_blue") && (TypeContains($EffectContext, "A", $player) || TypeContains($EffectContext, "AA", $player))) {
    if ($player == $mainPlayer) {
      WriteLog("Player ". $player ." gained 1 action point from " . CardLink($cardID, $cardID).".");
      GainActionPoints(1);
    }
  }
  //Code for equipped evos+ going to GY, then Scrapped and it makes them unplayable.
  // this may not be required anymore
  if ($from == "CHAR") {
    $splitCard = explode("_", $cardID);
    if ($splitCard[count($splitCard) - 1] == "equip") {
      $cardID = GetCardIDBeforeTransform($cardID);
    }
  }
  if (HasEphemeral($cardID) || TypeContains($cardID, "T", $player)) return;
  switch ($cardID) {
    case "mark_of_the_beast_yellow":
      BanishCardForPlayer($cardID, $player, $from, "NA");
      return;
    case "beast_within_yellow":
      if ($from != "CC") AddLayer("TRIGGER", $player, $cardID);
      break;
    case "drone_of_brutality_red":
    case "drone_of_brutality_yellow":
    case "drone_of_brutality_blue":
      AddBottomDeck($cardID, $player, $from);
      return;
    case "nasty_surprise_blue":
      if ($effectController != $player && $from != "CC") AddLayer("TRIGGER", $player, $cardID);
      break;
    default:
      break;
  }
  IncrementClassState($player, $CS_CardsEnteredGY);
  if ($mainPlayerGamestateStillBuilt) {
    if ($player == $mainPlayer) AddSpecificGraveyard($cardID, $mainDiscard, $from);
    else AddSpecificGraveyard($cardID, $defDiscard, $from);
  } else {
    if ($player == $myStateBuiltFor) AddSpecificGraveyard($cardID, $myDiscard, $from);
    else AddSpecificGraveyard($cardID, $theirDiscard, $from);
  }
}

function RemoveGraveyard($player, $index)
{
  $discard = &GetDiscard($player);
  $cardID = "";
  if (isset($discard[$index])) {
    $cardID = $discard[$index];
    unset($discard[$index + 1]);
    unset($discard[$index]);
    $discard = array_values($discard);
  }
  return $cardID;
}

function SearchCharacterAddUses($player, $uses, $type = "", $subtype = "")
{
  $character = &GetPlayerCharacter($player);
  for ($i = 0; $i < count($character); $i += CharacterPieces()) {
    if ($character[$i + 1] != 0 && ($type == "" || CardType($character[$i]) == $type) && ($subtype == "" || $subtype == CardSubtype($character[$i]))) {
      $character[$i + 1] = 2;
      $character[$i + 5] += $uses;
    }
  }
}

function SearchCharacterAddEffect($player, $effect, $type = "", $subtype = "")
{
  $character = &GetPlayerCharacter($player);
  for ($i = 0; $i < count($character); $i += CharacterPieces()) {
    if ($character[$i + 1] != 0 && ($type == "" || CardType($character[$i]) == $type) && ($subtype == "" || $subtype == CardSubtype($character[$i]))) {
      AddCharacterEffect($player, $i, $effect);
    }
  }
}

function RemoveCharacterEffects($player, $index, $effect)
{
  $effects = &GetCharacterEffects($player);
  for ($i = count($effects) - CharacterEffectPieces(); $i >= 0; $i -= CharacterEffectPieces()) {
    if ($effects[$i] == $index && $effects[$i + 1] == $effect) {
      unset($effects[$i + 1]);
      unset($effects[$i]);
    }
  }
  $effects = array_values($effects);
  return false;
}

function AddSpecificGraveyard($cardID, &$graveyard, $from)
{
  array_push($graveyard, $cardID);
  array_push($graveyard, GetUniqueId());
}

function NegateLayer($MZIndex, $goesWhere = "GY")
{
  global $layers;
  $params = explode("-", $MZIndex);
  $index = $params[1];
  $cardID = $layers[$index];
  $player = $layers[$index + 1];
  $otherPlayer = $player == 1 ? 2 : 1;
  for ($i = $index + LayerPieces() - 1; $i >= $index; --$i) {
    unset($layers[$i]);
  }
  $layers = array_values($layers);
  ResolveGoesWhere($goesWhere, $cardID, $player, "LAYER", $otherPlayer);
}

function AddAdditionalCost($player, $value)
{
  global $CS_AdditionalCosts;
  AppendClassState($player, $CS_AdditionalCosts, $value);
}

function ClearAdditionalCosts($player)
{
  global $CS_AdditionalCosts;
  SetClassState($player, $CS_AdditionalCosts, "-");
}

function FaceDownArsenalBotDeck($player)
{
  if (ArsenalHasFaceDownCard($player)) {
    AddDecisionQueue("FINDINDICES", $player, "ARSENALDOWN");
    AddDecisionQueue("CHOOSEARSENAL", $player, "<-", 1);
    AddDecisionQueue("REMOVEARSENAL", $player, "-", 1);
    AddDecisionQueue("ADDBOTDECK", $player, "-", 1);
  }
}

function RemoveInventory($player, $index)
{
  $index = intval($index);
  $inventory = &GetInventory($player);
  $cardID = $inventory[$index];
  for ($j = $index + InventoryPieces() - 1; $j >= $index; --$j) {
    unset($inventory[$j]);
  }
  $inventory = array_values($inventory);
  return $cardID;
}

function IsAltCard($cardID)
{
  switch ($cardID) {
    case "MON400":
    case "MON401":
    case "MON402":
    case "the_librarian":
    case "minerva_themis":
    case "lady_barthimont":
    case "lord_sutcliffe":
    case "the_hand_that_pulls_the_strings":
      return true;
  }
  return false;
}
