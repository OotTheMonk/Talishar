<?php

  function ELETalentPlayAbility($cardID, $from, $resourcesPaid, $target="-", $additionalCosts="")
  {
    global $currentPlayer, $CS_PlayIndex, $mainPlayer, $CS_DamagePrevention, $combatChain, $layers;
    $rv = "";
    $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
    switch($cardID)
    {
      case "korshem_crossroad_of_elements":
        $rv = "Korshem is a partially manual card. Use the instant ability to destroy it when appropriate. Use the Revert Gamestate button under the Stats page if necessary.";
        if($from == "PLAY")
        {
          DestroyLandmark(GetClassState($currentPlayer, $CS_PlayIndex));
          $rv = "Korshem was destroyed";
        }
        return $rv;
      case "invigorate_red": case "invigorate_yellow": case "invigorate_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "rejuvenate_red": GainHealth(3, $currentPlayer); return "";
      case "rejuvenate_yellow": GainHealth(2, $currentPlayer); return "";
      case "rejuvenate_blue": GainHealth(1, $currentPlayer); return "";
      case "pulse_of_volthaven_red":
        if (count($combatChain) > 0 || isset($layers[0]) && (CardType($layers[0]) == "AA" || GetAbilityType($layers[0]) == "AA")) {
          AddCurrentTurnEffectFromCombat($cardID, $currentPlayer);
        }
        else AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "pulse_of_candlehold_yellow":
        AddDecisionQueue("FINDINDICES", $currentPlayer, $cardID);
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "2-", 1);
        AddDecisionQueue("MULTICHOOSEDISCARD", $currentPlayer, "<-", 1);
        AddDecisionQueue("WRITELOG", $currentPlayer, "Cards returned", 1);
        AddDecisionQueue("MULTIREMOVEDISCARD", $currentPlayer, "1", 1);
        AddDecisionQueue("MULTIADDTOPDECK", $currentPlayer, "-", 1);
        return "";
      case "pulse_of_isenloft_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "crown_of_seeds":
        AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDCLASSSTATE", $currentPlayer, $CS_DamagePrevention . "-1", 1);
        return "";
      case "plume_of_evergrowth":
        $params = explode("-", $target);
        $index = SearchdiscardForUniqueID($params[1], $currentPlayer);
        if ($index != -1) {
          AddDecisionQueue("PASSPARAMETER", $currentPlayer, "MYDISCARD-" . $index, 1);
          AddDecisionQueue("MZADDZONE", $currentPlayer, "MYHAND", 1);
          AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        } else {
          WriteLog(CardLink($cardID, $cardID) . " layer fails as there are no remaining targets for the targeted effect.");
          return "FAILED";
        }
        return "";
      case "tome_of_harvests_blue":
        Draw($currentPlayer);
        Draw($currentPlayer);
        Draw($currentPlayer);
        return "";
      case "evergreen_red": case "evergreen_yellow": case "evergreen_blue":
        return "";
      case "weave_earth_red": case "weave_earth_yellow": case "weave_earth_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "summerwood_shelter_red": case "summerwood_shelter_yellow": case "summerwood_shelter_blue":
       AddDecisionQueue("FINDINDICES", $currentPlayer, $cardID);
       AddDecisionQueue("CHOOSECOMBATCHAIN", $currentPlayer, "<-", 1);
       AddDecisionQueue("COMBATCHAINDEFENSEMODIFIER", $currentPlayer, PlayBlockModifier($cardID), 1);
       return "";
      case "break_ground_red": case "break_ground_yellow": case "break_ground_blue":
        MZMoveCard($currentPlayer, "MYARS", "MYBOTDECK", may:true, silent:true);
        AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
        return "";
      case "earthlore_surge_red": case "earthlore_surge_yellow": case "earthlore_surge_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "sow_tomorrow_red": case "sow_tomorrow_yellow": case "sow_tomorrow_blue":
        $params = explode("-", $target);
        $index = SearchdiscardForUniqueID($params[1], $currentPlayer);
        if ($index != -1) {
          AddDecisionQueue("PASSPARAMETER", $currentPlayer, "MYDISCARD-" . $index, 1);
          AddDecisionQueue("MZADDZONE", $currentPlayer, "MYBOTDECK", 1);
          AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
          if($from == "ARS") {
            AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
            WriteLog(CardLink($cardID, $cardID) . " draw a card.");
          }
          ResolveGoesWhere("BANISH", $cardID, $currentPlayer, $from);
        } 
        else {
          WriteLog(CardLink($cardID, $cardID) . " layer fails as there are no remaining targets for the targeted effect.");
          ResolveGoesWhere("GY", $cardID, $currentPlayer, $from);
          return "FAILED";
        }
        return "";
      case "amulet_of_earth_blue":
        if($from == "PLAY") AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "heart_of_ice":
        AddCurrentTurnEffect($cardID, $otherPlayer);
        return "";
      case "coat_of_frost":
        PlayAura("frostbite", $otherPlayer, effectController: $currentPlayer);
        return "";
      case "blizzard_blue":
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose_to_pay_2_or_you_lose_and_can't_gain_go_again");
        AddDecisionQueue("BUTTONINPUT", $mainPlayer, "0,2", 0, 1);
        AddDecisionQueue("PAYRESOURCES", $mainPlayer, "<-", 1);
        AddDecisionQueue("GREATERTHANPASS", $mainPlayer, "0", 1);
        AddDecisionQueue("ADDCURRENTEFFECT", $mainPlayer, $cardID, 1);
        return "";
      case "ice_quake_red": case "ice_quake_yellow": case "ice_quake_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        AddCurrentTurnEffect($cardID . "-HIT", $currentPlayer);
        return "";
      case "weave_ice_red": case "weave_ice_yellow": case "weave_ice_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "chill_to_the_bone_red": case "chill_to_the_bone_yellow": case "chill_to_the_bone_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "polar_blast_red": case "polar_blast_yellow": case "polar_blast_blue":
        if($cardID == "polar_blast_red") $cost = 3;
        else if($cardID == "polar_blast_yellow") $cost = 2;
        else $cost = 1;
        AddDecisionQueue("SETDQCONTEXT", $otherPlayer, "Choose_if_you_want_to_pay_".$cost."_to_prevent_Dominate");
        AddDecisionQueue("BUTTONINPUT", $otherPlayer, "0," . $cost, 0, 1);
        AddDecisionQueue("PAYRESOURCES", $otherPlayer, "<-", 1);
        AddDecisionQueue("GREATERTHANPASS", $otherPlayer, "0", 1);
        AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
        if($from == "ARS") {
          Draw($currentPlayer);
          WriteLog(CardLink($cardID, $cardID) . " draw a card.");
        }
        return "";
      case "winters_bite_red": case "winters_bite_yellow": case "winters_bite_blue": 
        if($cardID == "winters_bite_red") $pay = 3;
        else if($cardID == "winters_bite_yellow") $pay = 2;
        else $pay = 1;
        if(ShouldAutotargetOpponent($currentPlayer)) {
          AddDecisionQueue("PASSPARAMETER", $currentPlayer, "Target_Opponent");
          AddDecisionQueue("PLAYERTARGETEDABILITY", $currentPlayer, "WINTERSBITE-" . $pay, 1);
        } else {
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose target hero");
          AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Target_Opponent,Target_Yourself");
          AddDecisionQueue("PLAYERTARGETEDABILITY", $currentPlayer, "WINTERSBITE-" . $pay, 1);
        }
        return "";
      case "amulet_of_ice_blue":
        if($from == "PLAY") PayOrDiscard($otherPlayer, 2);
        return "";
      case "shock_charmers":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "blink_blue":
        GainActionPoints(1, $currentPlayer);
        return "";
      case "flash_red": case "flash_yellow": case "flash_blue":
        AddAfterResolveEffect($cardID, $currentPlayer);
        return "";
      case "weave_lightning_red": case "weave_lightning_yellow": case "weave_lightning_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "lightning_press_red": case "lightning_press_yellow": case "lightning_press_blue":
        $amount = 3;
        if($cardID == "lightning_press_yellow") $amount = 2;
        else if($cardID == "lightning_press_blue") $amount = 1;
        $targetIndex = intval(explode("-", $target)[1]);
        if($targetIndex != 0) CombatChainPowerModifier($targetIndex, $amount);
        else AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "ball_lightning_red": case "ball_lightning_yellow": case "ball_lightning_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "lightning_surge_red": case "lightning_surge_yellow": case "lightning_surge_blue":
        if($from == "ARS") GiveAttackGoAgain();
        return "";
      case "shock_striker_red": case "shock_striker_yellow": case "shock_striker_blue":
        if($from == "PLAY") AddCurrentTurnEffect($cardID, $currentPlayer, "", 1);
        return "";
      case "electrify_red": case "electrify_yellow": case "electrify_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        if($from == "ARS") {
          Draw($currentPlayer);
          WriteLog(CardLink($cardID, $cardID) . " draw a card.");
        }
        return "";
      case "amulet_of_lightning_blue":
        if($from == "PLAY") {
          if(count($combatChain) > 0) GiveAttackGoAgain();
          else AddCurrentTurnEffect($cardID, $currentPlayer);
        }
        return "";
      case "ragamuffins_hat":
        Draw($currentPlayer);
        AddDecisionQueue("FINDINDICES", $currentPlayer, "HAND");
        AddDecisionQueue("CHOOSEHAND", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-", 1);
        AddDecisionQueue("OPT", $currentPlayer, "<-");
        return "";
      case "deep_blue":
        GainResources($currentPlayer, 3);
        return "";
      case "cracker_jax":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "runaways":
        IncrementClassState($currentPlayer, $CS_DamagePrevention);
        return "";
      default: return "";
    }
  }

  function ELETalentHitEffect($cardID)
  {
    global $mainPlayer, $defPlayer;
    switch($cardID)
    {
      case "frost_fang_red": case "frost_fang_yellow": case "frost_fang_blue":
        if(IsHeroAttackTarget()) PayOrDiscard($defPlayer, 2);
        break;
      case "icy_encounter_red": case "icy_encounter_yellow": case "icy_encounter_blue":
        if(IsHeroAttackTarget()) PlayAura("frostbite", $defPlayer, effectController: $mainPlayer);
        break;
      default: break;
    }
  }

  function SowTomorrowIndices($player, $cardID)
  {
    if($cardID == "sow_tomorrow_red") $minCost = 0;
    else if($cardID == "sow_tomorrow_yellow") $minCost = 1;
    else $minCost = 2;
    $earth = CombineSearches(SearchDiscard($player, "A", "", -1, $minCost, "", "EARTH"), SearchDiscard($player, "AA", "", -1, $minCost, "", "EARTH"));
    $elemental = CombineSearches(SearchDiscard($player, "A", "", -1, $minCost, "", "ELEMENTAL"), SearchDiscard($player, "AA", "", -1, $minCost, "", "ELEMENTAL"));
    return CombineSearches($earth, $elemental);
  }

  function PulseOfCandleholdIndices($player)
  {
    return CombineSearches(SearchDiscard($player, "A", "", -1, -1, "", "EARTH,LIGHTNING,ELEMENTAL"), SearchDiscard($player, "AA", "", -1, -1, "", "EARTH,LIGHTNING,ELEMENTAL"));
  }

  function ExposedToTheElementsEarth($player)
  {
      $otherPlayer = $player == 1 ? 2 : 1;
      PrependDecisionQueue("MODDEFCOUNTER", $otherPlayer, "-1", 1);
      PrependDecisionQueue("CHOOSETHEIRCHARACTER", $player, "<-", 1);
      PrependDecisionQueue("SETDQCONTEXT", $player, "Choose an equipment to put a -1 counter", 1);
      PrependDecisionQueue("FINDINDICES", $otherPlayer, "EQUIP");
  }

  function ExposedToTheElementsIce($player)
  {
      $otherPlayer = $player == 1 ? 2 : 1;
      PrependDecisionQueue("DESTROYCHARACTER", $otherPlayer, "-", 1);
      PrependDecisionQueue("CHOOSETHEIRCHARACTER", $player, "<-", 1);
      PrependDecisionQueue("SETDQCONTEXT", $player, "Choose an equipment to destroy", 1);
      PrependDecisionQueue("FINDINDICES", $otherPlayer, "EQUIP0", 1);
      PrependDecisionQueue("WRITELOG", $player, "Player $otherPlayer declined_to_pay_for_".CardLink("exposed_to_the_elements_blue", "exposed_to_the_elements_blue").".", 1);
      PrependDecisionQueue("GREATERTHANPASS", $otherPlayer, "0", 1);
      PrependDecisionQueue("PAYRESOURCES", $otherPlayer, "<-", 1);
      PrependDecisionQueue("BUTTONINPUT", $otherPlayer, "0,2", 0);
      PrependDecisionQueue("SETDQCONTEXT", $otherPlayer, "Choose_if_you_want_to_pay_2_to_prevent_an_equipment_with_0_defense_from_being_destroyed.");
  }

  function KorshemRevealAbility($player)
  {
    WriteLog("Korshem triggered by revealing a card");
    AddDecisionQueue("SETDQCONTEXT", $player, "Choose a bonus", 1);
    AddDecisionQueue("BUTTONINPUT", $player, "Gain_a_resource,Gain_a_life,1_Attack,1_Defense");
    AddDecisionQueue("MODAL", $player, "KORSHEM", 1);
  }

?>
