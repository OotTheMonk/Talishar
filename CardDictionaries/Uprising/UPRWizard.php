<?php

  function UPRWizardPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
  {
    global $currentPlayer, $mainPlayer, $CS_ArcaneDamagePrevention, $CS_LastDynCost, $layers;
    $rv = "";
    switch($cardID)
    {
      case "encase_red":
        DealArcane(3, 2, "PLAYCARD", $cardID, false, $currentPlayer, false, false, !DelimStringContains($additionalCosts, "ICE"), resolvedTarget: $target);
        return "";
      case "freezing_point_red":
        if(DelimStringContains($additionalCosts, "ICE")) {
          $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
          $damage = 5 + CountAura("frostbite", $otherPlayer) + SearchCount(SearchAura($otherPlayer, "", "Affliction", -1, -1, "", "ICE")) + FrozenCount($otherPlayer);
        }
        else $damage = 5;
        DealArcane($damage, 0, "PLAYCARD", $cardID, false, $currentPlayer, resolvedTarget: $target);
        return "";
      case "sigil_of_permafrost_red": case "sigil_of_permafrost_yellow": case "sigil_of_permafrost_blue":
        if(DelimStringContains($additionalCosts, "ICE")) AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "ice_eternal_blue":
        $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
        $numFrostBite = GetClassState($currentPlayer, $CS_LastDynCost)/2;
        PlayAura("frostbite", $otherPlayer, $numFrostBite, effectController: $currentPlayer);
        $amountArcane = SearchCount(SearchAurasForCard("frostbite", $otherPlayer));
        if(DelimStringContains($additionalCosts, "ICE")) DealArcane($amountArcane, 0, "PLAYCARD", $cardID, false, $currentPlayer, resolvedTarget: $target);
        return "";
      case "succumb_to_winter_red": case "succumb_to_winter_yellow": case "succumb_to_winter_blue":
        if($cardID == "succumb_to_winter_red") $damage = 5;
        else if($cardID == "succumb_to_winter_yellow") $damage = 4;
        else $damage = 3;
        DealArcane($damage, 2, "PLAYCARD", $cardID, false, $currentPlayer, resolvedTarget: $target);
        if(DelimStringContains($additionalCosts, "ICE")) {
          AddDecisionQueue("PASSPARAMETER", $currentPlayer, $target . "-" . $cardID, 1);
          AddDecisionQueue("SUCCUMBTOWINTER", $currentPlayer, "-", 1);
        }
        return "";
      case "aether_icevein_red": case "aether_icevein_yellow": case "aether_icevein_blue":
        if($cardID == "aether_icevein_red") $damage = 5;
        else if($cardID == "aether_icevein_yellow") $damage = 4;
        else $damage = 3;
        DealArcane($damage, 2, "PLAYCARD", $cardID, false, $currentPlayer, false, false, !DelimStringContains($additionalCosts, "ICE"), resolvedTarget: $target);
        return "";
      case "brain_freeze_red": case "brain_freeze_yellow": case "brain_freeze_blue":
        $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
        $hand = &GetHand($otherPlayer);
        $cards = "";
        for($i=0; $i<count($hand); ++$i) {
          if($cards != "") $cards .= ",";
          $cards .= $hand[$i];
        }
        $cardsRevealed = RevealCards($cards);
        if($cardsRevealed && DelimStringContains($additionalCosts, "ICE")) {
          if($cardID == "brain_freeze_red") $maxCost = 2;
          else if($cardID == "brain_freeze_yellow") $maxCost = 1;
          else $maxCost = 0;
          AddDecisionQueue("FINDINDICES", $otherPlayer, "HANDACTIONMAXCOST," . $maxCost);
          AddDecisionQueue("CHOOSETHEIRHAND", $currentPlayer, "<-", 1);
          AddDecisionQueue("MULTIREMOVEHAND", $otherPlayer, "-", 1);
          AddDecisionQueue("MULTIADDTOPDECK", $otherPlayer, "-", 1);
        }
        return "";
      case "icebind_red": case "icebind_yellow": case "icebind_blue":
        if($cardID == "icebind_red") $damage = 3;
        else if($cardID == "icebind_yellow") $damage = 2;
        else $damage = 1;
        DealArcane($damage, 2, "PLAYCARD", $cardID, false, $currentPlayer, false, false, !DelimStringContains($additionalCosts, "ICE"), resolvedTarget: $target);
        return "";
      case "polar_cap_red": case "polar_cap_yellow": case "polar_cap_blue":
        if($cardID == "polar_cap_red") $damage = 4;
        else if($cardID == "polar_cap_yellow") $damage = 3;
        else $damage = 2;
        DealArcane($damage, 2, "PLAYCARD", $cardID, false, $currentPlayer, false, false, !DelimStringContains($additionalCosts, "ICE"), resolvedTarget: $target);
        return "";
      case "conduit_of_frostburn":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "aether_hail_red": case "aether_hail_yellow": case "aether_hail_blue":
        if($cardID == "aether_hail_red") $damage = 4;
        else if($cardID == "aether_hail_yellow") $damage = 3;
        else $damage = 2;
        DealArcane($damage, 2, "PLAYCARD", $cardID, false, $currentPlayer, resolvedTarget: $target);
        return "";
      case "frosting_red": case "frosting_yellow": case "frosting_blue":
        if($cardID == "frosting_red") $damage = 3;
        else if($cardID == "frosting_yellow") $damage = 2;
        else $damage = 1;
        DealArcane($damage, 2, "PLAYCARD", $cardID, false, $currentPlayer, resolvedTarget: $target);
        return "";
      case "ice_bolt_red": case "ice_bolt_yellow": case "ice_bolt_blue":
        if($cardID == "ice_bolt_red") $damage = 5;
        else if($cardID == "ice_bolt_yellow") $damage = 4;
        else $damage = 3;
        DealArcane($damage, 2, "PLAYCARD", $cardID, false, $currentPlayer, resolvedTarget: $target);
        return "";
      case "waning_moon":
        if($currentPlayer != $mainPlayer) $damage = 3;
        else $damage = 2;
        DealArcane($damage, 0, "ABILITY", $cardID, resolvedTarget: $target);
        return "";
      case "alluvion_constellas":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "spellfire_cloak":
        GainResources($currentPlayer, 1);
        return "Gain 1 resource.";
      case "tome_of_duplicity_blue":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "DECKTOPXINDICES,2");
        AddDecisionQueue("CHOOSEDECK", $currentPlayer, "<-", 1);
        AddDecisionQueue("SPECIFICCARD", $currentPlayer, "TOMEOFDUPLICITY", 1);
        return "";
      case "rewind_blue":
        $index = intval(explode("-", $target)[1]);
        $targetPlayer = $layers[$index + 1];
        if(substr($from, 0, 5) != "THEIR") NegateLayer($target, "HAND");
        else NegateLayer($target, "THEIRHAND"); 
        if($targetPlayer == $mainPlayer) GainActionPoints(1, $mainPlayer);
        return "";
      case "dampen_red": case "dampen_yellow": case "dampen_blue":
        if($cardID == "dampen_red") $damage = 4;
        else if($cardID == "dampen_yellow") $damage = 3;
        else $damage = 2;
        DealArcane($damage, 2, "PLAYCARD", $cardID, false, $currentPlayer, resolvedTarget: $target);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_ArcaneDamagePrevention, 1);
        return "";
      case "aether_dart_red": case "aether_dart_yellow": case "aether_dart_blue":
        if($cardID == "aether_dart_red") $damage = 3;
        else if($cardID == "aether_dart_yellow") $damage = 2;
        else $damage = 1;
        DealArcane($damage, 2, "PLAYCARD", $cardID, false, $currentPlayer, resolvedTarget: $target);
        return "";
      case "singe_red": case "singe_yellow": case "singe_blue":
        if($cardID == "singe_red") $maxAllies = 3;
        else if($cardID == "singe_yellow") $maxAllies = 2;
        else $maxAllies = 1;
        $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
        $allies = &GetAllies($otherPlayer);
        if(count($allies) < $maxAllies) $maxAllies = count($allies);
        $damage = ArcaneDamage($cardID) + ConsumeArcaneBonus($currentPlayer);
        DealArcane($damage, 1, "PLAYCARD", $cardID, false, $currentPlayer, false, false, resolvedTarget: $target);
        for($i=1; $i<$maxAllies; ++$i) DealArcane($damage, 5, "PLAYCARD", $cardID, false, $currentPlayer, true, true);
        DealArcane($damage, 5, "PLAYCARD", $cardID, false, $currentPlayer, true, false);
        return "";
      default: return "";
    }
  }

  function UPRWizardHitEffect($cardID)
  {
    switch($cardID)
    {
      default: break;
    }
  }

  function FrostHexEndTurnAbility($player)
  {
    $numFrostHex = SearchCount(SearchAurasForCard("frost_hex_blue", $player));
    for($i=0; $i<$numFrostHex; ++$i)
    {
      DealArcane(1, 4, "TRIGGER", "frostbite", false, $player);
    }
  }


?>
