<?php

function PutPermanentIntoPlay($player, $cardID, $number=1, $isToken=false, $from="-", $subCards="-")
{
  global $EffectContext;
  $permanents = &GetPermanents($player);
  $otherPlayer = ($player == 1 ? 2 : 1);
  if(TypeContains($cardID, "T", $player)) $isToken = true;
  $numMinusTokens = 0;
  $numMinusTokens = CountCurrentTurnEffects("ripple_away_blue", $player) + CountCurrentTurnEffects("ripple_away_blue", $otherPlayer);
  if($numMinusTokens > 0 && $isToken && (TypeContains($EffectContext, "AA", $player) || TypeContains($EffectContext, "A", $player))) $number -= $numMinusTokens;
  for($i = 0; $i < $number; ++$i) {
    array_push($permanents, $cardID);
    array_push($permanents, $from);
    array_push($permanents, $subCards);
  }
  return count($permanents) - PermanentPieces();
}

function RemovePermanent($player, $index)
{
  $index = intval($index);
  $permanents = &GetPermanents($player);
  $cardID = $permanents[$index];
  for($j = $index + PermanentPieces() - 1; $j >= $index; --$j) {
    unset($permanents[$j]);
  }
  $permanents = array_values($permanents);
  return $cardID;
}

function DestroyPermanent($player, $index)
{
  if($index == -1) return;
  $index = intval($index);
  $permanents = &GetPermanents($player);
  $cardID = $permanents[$index];
  $isToken = isset($permanents[$index + 4]) ? ($permanents[$index + 4] == 1) : false;
  PermanentDestroyed($player, $cardID, $isToken);
  for ($j = $index + PermanentPieces() - 1; $j >= $index; --$j) {
    unset($permanents[$j]);
  }
  $permanents = array_values($permanents);
}

function PermanentDestroyed($player, $cardID, $isToken = false)
{
  $permanents = &GetPermanents($player);
  for ($i = 0; $i < count($permanents); $i += PermanentPieces()) {
    switch ($permanents[$i]) {
      default:
        break;
    }
  }
  $goesWhere = GoesWhereAfterResolving($cardID);
  if (CardType($cardID) == "T" || $isToken || CardType($cardID) == "Macro") return; //Don't need to add to anywhere if it's a token
  ResolveGoesWhere($goesWhere, $cardID, $player, "PLAY");
}

function PermanentBeginEndPhaseEffects()
{
  global $mainPlayer, $defPlayer;

  $permanents = &GetPermanents($mainPlayer);
  for ($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    $remove = 0;
    switch ($permanents[$i]) {
      case "UPR439": case "UPR440": case "UPR441":
        $origMaterial = explode(",", $permanents[$i+2])[0];
        if ($origMaterial != "-") PutPermanentIntoPlay($mainPlayer, $origMaterial);
        $remove = 1;
        break;
      case "ROGUE501":
        $deck = &GetDeck($mainPlayer);
        $discard = &GetDiscard($mainPlayer);
        $banish = &GetBanish($mainPlayer);
        for($i = count($discard)-1; $i >= 0; --$i)
        {
          if(rand(0, 1) == 0) array_push($deck, $discard[$i]);
          else
          {
            array_push($banish, $discard[$i]);
            array_push($banish, "");
            array_push($banish, GetUniqueId());
          }
          unset($discard[$i]);
        }
        $destArr = [];
        while (count($deck) > 0) {
          $index = GetRandom(0, count($deck) - 1);
          array_push($destArr, $deck[$index]);
          unset($deck[$index]);
          $deck = array_values($deck);
        }
        $deck = $destArr;
        break;
      case "ROGUE703":
        $deck = &GetDeck($mainPlayer);
        $discard = &GetDiscard($mainPlayer);
        $banish = &GetBanish($mainPlayer);
        for($i = count($discard)-1; $i >= 0; --$i)
        {
          if(rand(0, 1) == 0 && CardType($discard[$i]) != "W" && CardType($discard[$i]) != "E") array_push($deck, $discard[$i]);
          else
          {
            array_push($banish, $discard[$i]);
            array_push($banish, "");
            array_push($banish, GetUniqueId());
          }
          unset($discard[$i]);
        }
        $destArr = [];
        while (count($deck) > 0) {
          $index = GetRandom(0, count($deck) - 1);
          array_push($destArr, $deck[$index]);
          unset($deck[$index]);
          $deck = array_values($deck);
        }
        $deck = $destArr;
        break;
      default:
        break;
    }
    if ($remove == 1) DestroyPermanent($mainPlayer, $i);
  }

  $permanents = &GetPermanents($defPlayer);
  for ($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    $remove = 0;
    switch ($permanents[$i]) {
      case "UPR439": case "UPR440": case "UPR441":
        $origMaterial = explode(",", $permanents[$i+2])[0];
        if ($origMaterial != "-") PutPermanentIntoPlay($defPlayer, $origMaterial);
        $remove = 1;
        break;
      default:
        break;
    }
    if ($remove == 1) DestroyPermanent($defPlayer, $i);
  }
}

function PermanentTakeDamageAbilities($player, $damage, $type, $source)
{
  $char = &GetPlayerCharacter($player);
  $permanents = &GetPermanents($player);
  $otherPlayer = $player == 1 ? 1 : 2;
  //CR 2.1 6.4.10f If an effect states that a prevention effect can not prevent the damage of an event, the prevention effect still applies to the event but its prevention amount is not reduced. Any additional modifications to the event by the prevention effect still occur.
  $preventable = CanDamageBePrevented($otherPlayer, $damage, $type, $source);
  $preventedDamage = 0;
  for ($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    $remove = 0;
    switch ($permanents[$i]) {
      case "UPR439":
        if ($damage > 0) {
          if ($preventable) $preventedDamage += 4;
          $remove = 1;
        }
        break;
      case "UPR440":
        if ($damage > 0) {
          if ($preventable) $preventedDamage += 3;
          $remove = 1;
        }
        break;
      case "UPR441":
        if ($damage > 0) {
          if ($preventable) $preventedDamage += 2;
          $remove = 1;
        }
        break;
      default:
        break;
    }
    if ($remove == 1) {
      if (HasWard($permanents[$i], $player) && SearchCharacterActive($player, "celestial_kimono") && CardType($permanents[$i]) != "T") {
        $index = FindCharacterIndex($player, "celestial_kimono");
        $char[$index + 1] = 1;
        GainResources($player, 1);
        WriteLog("Player " . $player . " gained 1 resource from " . CardLink("celestial_kimono", "celestial_kimono"));
      }
      DestroyPermanent($player, $i);
    }
  }
  if (SearchCurrentTurnEffects("vambrace_of_determination", $player) != "" && $preventedDamage > 0) {//vambrace
    $preventedDamage -= 1;
    SearchCurrentTurnEffects("vambrace_of_determination", $player, remove:true);
  }
  $damage -= $preventedDamage;
  if ($damage <= 0) $damage = 0;
  return $damage;
}

function PermanentStartTurnAbilities()
{
  global $mainPlayer, $defPlayer;

  $permanents = &GetPermanents($mainPlayer);
  $defPermanents = &GetPermanents($defPlayer);
  $character = &GetPlayerCharacter($mainPlayer);
  $hand = &GetHand($mainPlayer);
  for ($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    $remove = 0;
    switch ($permanents[$i]) {
      case "ROGUE502":
        array_push($hand, $hand[rand(0, count($hand)-1)]);
        break;
      case "ROGUE503":
        $choices = array("head_jab_red", "head_jab_yellow", "head_jab_blue");
        array_push($hand, $choices[rand(0, count($choices)-1)]);
        break;
      case "ROGUE504":
        for($j = 0; $j < count($character)-1; ++$j)
        {
          if(CardType($character[$j]) == "W") $character[$j + 3] += 1;
        }
        break;
      case "ROGUE505":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE506":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE507":
        $items = &GetItems($mainPlayer);
        $found = false;
        for($j = 0; $j < count($items)-1; ++$j) { if($items[$j] == "gold") $found = true; continue; }
        if(!$found) AddDecisionQueue("STARTOFGAMEPUTPLAY", 1, "gold");
        break;
      case "ROGUE509":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        array_unshift($hand, "crouching_tiger");
        break;
      case "ROGUE512": case "ROGUE513":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE517":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE518":
        AddDecisionQueue("FINDINDICES", $mainPlayer, "HAND");
        AddDecisionQueue("MAYCHOOSEHAND", $mainPlayer, "<-", 1);
        AddDecisionQueue("ROGUEMIRRORTURNSTART", $mainPlayer, "0");
        break;
      case "ROGUE519":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE521":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE522":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE525":
        $resources = &GetResources($mainPlayer);
        $deck = &GetDeck($mainPlayer);
        $discard = &GetDiscard($mainPlayer);
        $resources[0] += ((count($deck) + count($discard) + count($hand)) - (count($deck) + count($discard) + count($hand))%10)/10;
        break;
      case "ROGUE526":
        global $currentTurn;
        $deckOne = &GetDeck($mainPlayer);
        $deckTwo = &GetDeck($defPlayer);
        if($currentTurn == 10) $deckOne = $deckTwo = [];
        break;
      case "ROGUE527":
        $trinkets = array(
          "crazy_brew_blue", "energy_potion_blue", "potion_of_strength_blue", "timesnap_potion_blue",
          "rusted_relic_blue",
          "talisman_of_dousing_yellow",
          "amulet_of_assertiveness_yellow", "amulet_of_echoes_blue", "amulet_of_havencall_blue", "amulet_of_ignition_yellow", "amulet_of_intervention_blue", "amulet_of_oblation_blue", "clarity_potion_blue", "healing_potion_blue", "potion_of_seeing_blue", "potion_of_deja_vu_blue", "potion_of_ironhide_blue", "potion_of_luck_blue", "talisman_of_balance_blue", "talisman_of_cremation_blue", "talisman_of_featherfoot_yellow", "talisman_of_recompense_yellow", "talisman_of_tithes_blue", "talisman_of_warfare_yellow"
        );
        AddDecisionQueue("STARTOFGAMEPUTPLAY", 1, $trinkets[rand(0, count($trinkets)-1)]);
        break;
      case "ROGUE528":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;

      case "ROGUE602":
        $indexChoices = [];
        for($j = count($character) - CharacterPieces(); $j >= 0; $j -= CharacterPieces())
        {
          if($character[$j+1] == 0) array_push($indexChoices, $j);
        }
        if(count($indexChoices) != 0) $character[$indexChoices[rand(0, count($indexChoices)-1)]+1] = 2;
        break;
      case "ROGUE603":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        array_unshift($hand, "crouching_tiger");
        break;
      case "ROGUE605":
        AddCurrentTurnEffect("ROGUE605-first", $mainPlayer);
        AddCurrentTurnEffect("ROGUE605-second", $mainPlayer);
        break;
      case "ROGUE606":
        BottomDeck($mainPlayer, true, shouldDraw:true);
        break;
      case "ROGUE608":
        $items = &GetItems($mainPlayer);
        $found = false;
        for($j = 0; $j < count($items)-1; ++$j) { if($items[$j] == "gold") $found = true; continue; }
        if(!$found) PutItemIntoPlayForPlayer("gold", $mainPlayer, effectController:$mainPlayer);
        break;
      case "ROGUE610":
        AddDecisionQueue("FINDINDICES", $mainPlayer, "HAND");
        AddDecisionQueue("MAYCHOOSEHAND", $mainPlayer, "<-", 1);
        AddDecisionQueue("ROGUEDECKCARDSTURNSTART", $mainPlayer, "0");
        AddDecisionQueue("SHUFFLEDECK", $mainPlayer, "-");
        break;
      case "ROGUE612": case "ROGUE613": case "ROGUE614": case "ROGUE615": case "ROGUE616":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE701":
        $resources = &GetResources($mainPlayer);
        $deck = &GetDeck($mainPlayer);
        $discard = &GetDiscard($mainPlayer);
        $resources[0] += ((count($deck) + count($discard) + count($hand)) - (count($deck) + count($discard) + count($hand))%10)/10;
        break;
      case "ROGUE702":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE704":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE707":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE710":
        AddCurrentTurnEffect($permanents[$i]."-GA", $mainPlayer);
        AddCurrentTurnEffect($permanents[$i]."-DO", $mainPlayer);
        break;
      case "ROGUE711":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE801":
        if(count($hand) > 0) array_push($hand, $hand[rand(0, count($hand)-1)]);
        break;
      case "ROGUE802":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE803":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE804":
        $options = array("ROGUE601", "ROGUE602", "ROGUE603", "ROGUE605", "ROGUE606", "ROGUE607", "ROGUE608", "ROGUE610");
        $choice = $options[rand(0, count($options)-1)];
        PutPermanentIntoPlay(1, $choice);
        switch($choice)
        {
          case "ROGUE603":
            AddCurrentTurnEffect($choice, $mainPlayer);
            break;
          case "ROGUE605":
            AddCurrentTurnEffect("ROGUE605-first", $mainPlayer);
            AddCurrentTurnEffect("ROGUE605-second", $mainPlayer);
            break;
        }
        break;
      case "ROGUE805":
        AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE806":
        $deck = new Deck($mainPlayer);
        $deck->BanishTop(banishedBy:$mainPlayer, amount:4);
        $deck = &GetDeck($mainPlayer);
        if($deck->RemainingCards() < 1) AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE807":
        Draw($mainPlayer);
        break;
      default:
        break;
    }
  }
  for ($i = count($defPermanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    switch ($defPermanents[$i]) {
      case "ROGUE506":
        AddCurrentTurnEffect($defPermanents[$i], $defPlayer);
        break;
      case "ROGUE523":
        AddCurrentTurnEffect($defPermanents[$i], $mainPlayer);
        break;

      case "ROGUE709":
        AddCurrentTurnEffect($defPermanents[$i], $mainPlayer);
        break;
      case "ROGUE802":
        AddCurrentTurnEffect($defPermanents[$i], $defPlayer);
        break;
      default:
        break;
    }
  }
}

function PermanentPlayAbilities($attackID, $from="")
{
  global $mainPlayer, $actionPoints;
  $permanents = &GetPermanents($mainPlayer);
  $cardType = CardType($attackID);
  $cardPitch = PitchValue($attackID);
  for ($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    switch($permanents[$i]) {
      case "ROGUE521":
        if($cardPitch == 2 && $cardType != "AA")
        {
          AddCurrentTurnEffect($permanents[$i] . "-NA", $mainPlayer);
        }
        break;
      case "ROGUE528":
        if(DelimStringContains($cardType, "A")) ++$actionPoints;
        break;
      case "ROGUE607":
        if($cardType != "A" && $cardType != "AA") AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      case "ROGUE702":
        if($cardPitch == 2 && $cardType != "AA")
        {
          AddCurrentTurnEffect($permanents[$i] . "-NA", $mainPlayer);
        }
        break;
      case "ROGUE704":
        if($cardType == "AA")
        {
          $banish = &GetBanish($mainPlayer);
          array_push($banish, $attackID);
          array_push($banish, "");
          array_push($banish, GetUniqueId());
        }
        break;
      case "ROGUE706":
        if($from == "ARS") Draw($mainPlayer);
        break;
      default:
        break;
    }
  }
}

function PermanentAddAttackAbilities()
{
  global $mainPlayer;
  $amount = 0;
  $permanents = &GetPermanents($mainPlayer);
  for ($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    switch($permanents[$i]) {
      case "ROGUE705":
        $amount += 1;
        break;
      default:
        break;
    }
  }
  return $amount;
}

function PermanentDrawCardAbilities($player)
{
  global $mainPlayer;
  $permanents = &GetPermanents($mainPlayer);
  for($i = count($permanents) - PermanentPieces(); $i >= 0; $i -= PermanentPieces()) {
    switch($permanents[$i]) {
      case "ROGUE601":
        if($mainPlayer == $player) AddCurrentTurnEffect($permanents[$i], $mainPlayer);
        break;
      default:
        break;
    }
  }
}