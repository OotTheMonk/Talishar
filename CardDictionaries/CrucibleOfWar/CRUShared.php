<?php

  function CRUAbilityCost($cardID) {
    global $CS_PlayIndex, $currentPlayer;
    switch($cardID) {
      case "mandible_claw": case "mandible_claw": return 2;
      case "sledge_of_anvilheim": return 4;
      case "crater_fist": return 3;
      case "harmonized_kodachi": return 1;
      case "edge_of_autumn": case "zephyr_needle": case "zephyr_needle": return 1;
      case "cintari_saber": case "cintari_saber": return 1;
      case "plasma_barrel_shot": return (GetResolvedAbilityType($cardID) == "A" ? 2 : 0);
      case "plasma_purifier_red": 
        $items = &GetItems($currentPlayer); 
        if ($items[GetClassState($currentPlayer, $CS_PlayIndex) + 2] < 2) return 1;
        if ($items[GetClassState($currentPlayer, $CS_PlayIndex) + 1] > 0) return 0;
        return 1;
      case "kavdaen_trader_of_skins": return 3;
      case "perch_grapplers": return 2;
      case "reaping_blade": return 1;
      case "aether_conduit": return 2;
      case "talishar_the_lost_prince": return 2;
      case "copper": return 4;
      default: return 0;
    }
  }

  function CRUAbilityType($cardID, $index=-1) {
    switch($cardID) {
      case "mandible_claw": case "mandible_claw": return "AA";
      case "skullhorn": return "A";
      case "sledge_of_anvilheim": return "AA";
      case "crater_fist": return "A";
      case "harmonized_kodachi": return "AA";
      case "edge_of_autumn": case "zephyr_needle": case "zephyr_needle": return "AA";
      case "cintari_saber": case "cintari_saber": return "AA";
      case "courage_of_bladehold": return "A";
      case "plasma_barrel_shot": return "A";
      case "viziertronic_model_i": return "A";
      case "plasma_purifier_red": return "A";
      case "kavdaen_trader_of_skins": return "A";
      case "red_liner": return "A";
      case "perch_grapplers": return "A";
      case "reaping_blade": return "AA";
      case "bloodsheath_skeleta": return "I";
      case "aether_conduit": return "A";
      case "talishar_the_lost_prince": return "AA";
      case "copper": return "A";
      default: return "";
    }
  }

  function CRUAbilityHasGoAgain($cardID) {
    switch($cardID) {
      case "skullhorn": return true;
      case "crater_fist": return true;
      case "courage_of_bladehold": return true;
      case "plasma_barrel_shot": return GetResolvedAbilityType($cardID) == "A";
      case "viziertronic_model_i": return true;
      case "plasma_purifier_red": return true;
      case "kavdaen_trader_of_skins": return true;
      case "red_liner": case "perch_grapplers": return true;
      case "copper": return true;
      default: return false;
    }
  }

  function CRUEffectAttackModifier($cardID) {
    switch($cardID) {
      case "massacre_red": return 2;
      case "crater_fist": return 2;
      case "towering_titan_red": return 10;
      case "towering_titan_yellow": return 9;
      case "towering_titan_blue": return 8;
      case "emerging_dominance_red": return 3;
      case "emerging_dominance_yellow": return 2;
      case "emerging_dominance_blue": return 1;
      case "ira_crimson_haze": return 1;
      case "benji_the_piercing_wind": return 1;
      case "flood_of_force_yellow": return 3;
      case "bittering_thorns_yellow": return 1;
      case "spoils_of_war_red": return 2;
      case "dauntless_red-1": return 3;
      case "dauntless_yellow-1": return 2;
      case "dauntless_blue-1": return 1;
      case "out_for_blood_red-1": return 3;
      case "out_for_blood_yellow-1": return 2;
      case "out_for_blood_blue-1": return 1;
      case "out_for_blood_red-2": return 1;
      case "out_for_blood_yellow-2": return 1;
      case "out_for_blood_blue-2": return 1;
      case "hit_and_run_red-2": return 3;
      case "hit_and_run_yellow-2": return 2;
      case "hit_and_run_blue-2": return 1;
      case "push_forward_red-1": return 3;
      case "push_forward_yellow-1": return 2;
      case "push_forward_blue-1": return 1;
      case "plasma_purifier_red": return 1;
      case "combustible_courier_red": case "combustible_courier_yellow": case "combustible_courier_blue": return 3;
      case "increase_the_tension_red": return 3;
      case "increase_the_tension_yellow": return 2;
      case "increase_the_tension_blue": return 1;
      case "lunging_press_blue": return 1;
      default: return 0;
    }
  }

  function CRUCombatEffectActive($cardID, $attackID) {
    global $CombatChain, $combatChainState, $mainPlayer, $CCS_IsBoosted, $CS_ArsenalFacing;
    switch($cardID) {
      case "massacre_red": return true;
      case "predatory_assault_red": case "predatory_assault_yellow": case "predatory_assault_blue": return true;
      case "crater_fist": return HasCrush($attackID);
      case "towering_titan_red": case "towering_titan_yellow": case "towering_titan_blue": return CardType($attackID) == "AA" && ClassContains($attackID, "GUARDIAN", $mainPlayer);
      case "emerging_dominance_red": case "emerging_dominance_yellow": case "emerging_dominance_blue": return CardType($attackID) == "AA" && ClassContains($attackID, "GUARDIAN", $mainPlayer);
      case "ira_crimson_haze": return true;
      case "benji_the_piercing_wind": return true;
      case "breeze_rider_boots": return HasCombo($attackID);
      case "flood_of_force_yellow": return true;
      case "bittering_thorns_yellow": return true;
      case "spoils_of_war_red": return TypeContains($attackID, "W", $mainPlayer);
      case "spoils_of_war_red-2": return TypeContains($attackID, "W", $mainPlayer);
      case "dauntless_red-1": case "dauntless_yellow-1": case "dauntless_blue-1": return TypeContains($attackID, "W", $mainPlayer);
      case "out_for_blood_red-1": case "out_for_blood_yellow-1": case "out_for_blood_blue-1": return TypeContains($attackID, "W", $mainPlayer);
      case "out_for_blood_red-2": case "out_for_blood_yellow-2": case "out_for_blood_blue-2": return true;
      case "hit_and_run_red-1": case "hit_and_run_yellow-1": case "hit_and_run_blue-1": return TypeContains($attackID, "W", $mainPlayer);
      case "hit_and_run_red-2": case "hit_and_run_yellow-2": case "hit_and_run_blue-2": return true;
      case "push_forward_red-1": case "push_forward_yellow-1": case "push_forward_blue-1": return TypeContains($attackID, "W", $mainPlayer);
      case "push_forward_red-2": case "push_forward_yellow-2": case "push_forward_blue-2": return true;
      case "plasma_purifier_red": return TypeContains($attackID, "W", $mainPlayer) && CardSubtype($attackID) == "Pistol" && ClassContains($attackID, "MECHANOLOGIST", $mainPlayer);
      case "high_speed_impact_red": case "high_speed_impact_yellow": case "high_speed_impact_blue": return $combatChainState[$CCS_IsBoosted] == "1";
      case "combustible_courier_red": case "combustible_courier_yellow": case "combustible_courier_blue": return $combatChainState[$CCS_IsBoosted] == "1";
      case "perch_grapplers": return $CombatChain->AttackCard()->From() == "ARS" && GetClassState($mainPlayer, $CS_ArsenalFacing) == "UP" && CardSubtype($attackID) == "Arrow"; //The card being played from ARS and being an Arrow implies that the card is UP.
      case "remorseless_red": return $attackID == "remorseless_red";
      case "poison_the_tips_yellow": return CardSubtype($attackID) == "Arrow";
      case "feign_death_yellow": return true;
      case "tripwire_trap_red": return true;
      case "increase_the_tension_red": case "increase_the_tension_yellow": case "increase_the_tension_blue": return CardSubtype($attackID) == "Arrow";
      case "increase_the_tension_red-1": case "increase_the_tension_yellow-1": case "increase_the_tension_blue-1": return CardSubtype($attackID) == "Arrow";
      case "mauvrion_skies_red": case "mauvrion_skies_yellow": case "mauvrion_skies_blue": return CardType($attackID) == "AA" && ClassContains($attackID, "RUNEBLADE", $mainPlayer);
      case "lunging_press_blue": return true;
      default: return false;
    }
  }

function CRUPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts) {
  global $mainPlayer, $CS_NumBoosted, $combatChainState, $currentPlayer, $defPlayer;
  global $CS_AtksWWeapon, $CS_Num6PowDisc, $CCS_WeaponIndex, $CS_NextDamagePrevented, $CS_PlayIndex, $CS_NextWizardNAAInstant, $CS_NumWizardNonAttack;
  global $CCS_BaseAttackDefenseMax, $CCS_ResourceCostDefenseMin, $CCS_CardTypeDefenseRequirement, $CCS_RequiredEquipmentBlock, $CCS_NumBoosted;
  $rv = "";
  switch($cardID) {
    case "skullhorn":
      Draw($currentPlayer);
      DiscardRandom($currentPlayer, $cardID);
      return "";
    case "massacre_red":
      if(GetClassState($currentPlayer, $CS_Num6PowDisc) > 0) {
        Intimidate();
        AddCurrentTurnEffect($cardID, $currentPlayer);
      }
      return "";
    case "argh_smash_yellow":
      $roll = GetDieRoll($currentPlayer);
      for($i = 1; $i < $roll; $i += 2) {
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRITEMS", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
      }
      return "Argh... Smash! rolled " . $roll;
    case "predatory_assault_red": case "predatory_assault_yellow": case "predatory_assault_blue":
      if(GetClassState($currentPlayer, $CS_Num6PowDisc) > 0) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
        $rv = "Gains Dominate.";
      }
      return $rv;
    case "crater_fist":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "stamp_authority_blue":
      if(SearchCount(SearchPitch($mainPlayer, minCost:3)) >= 2) AddCurrentTurnEffect($cardID, $currentPlayer);
      return $rv;
    case "blessing_of_serenity_red": case "blessing_of_serenity_yellow": case "blessing_of_serenity_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "harmonized_kodachi":
      if(SearchCount(SearchPitch($currentPlayer, minCost:0, maxCost:0)) > 0) GiveAttackGoAgain();
      return "";
    case "find_center_blue":
      if(ComboActive()) {
        $numLinks = NumChainLinks();
        $combatChainState[$CCS_ResourceCostDefenseMin] = $numLinks;
        $rv = "Cannot be defended by cards with cost less than " . $numLinks;
      }
      return $rv;
    case "flood_of_force_yellow":
      if(ComboActive()) {
        AddDecisionQueue("DECKCARDS", $mainPlayer, "0");
        AddDecisionQueue("REVEALCARDS", $mainPlayer, "-", 1);
        AddDecisionQueue("ALLCARDSCOMBOORPASS", $mainPlayer, "-", 1);
        AddDecisionQueue("FINDINDICES", $mainPlayer, "TOPDECK", 1);
        AddDecisionQueue("MULTIREMOVEDECK", $mainPlayer, "<-", 1);
        AddDecisionQueue("MULTIADDHAND", $mainPlayer, "-", 1);
        AddDecisionQueue("ADDCURRENTEFFECT", $mainPlayer, "flood_of_force_yellow", 1);
      }
      return $rv;
    case "herons_flight_red":
      if(ComboActive()) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a mode");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Attack_Action,Non-attack_Action");
        AddDecisionQueue("SETCOMBATCHAINSTATE", $currentPlayer, $CCS_CardTypeDefenseRequirement, 1);
      }
      return "";
    case "crane_dance_red": case "crane_dance_yellow": case "crane_dance_blue":
      if(ComboActive()) {
        $numLinks = NumChainLinks();
        $combatChainState[$CCS_BaseAttackDefenseMax] = $numLinks;
        $rv = "Cannot be defended by attacks with greater than " . $numLinks . " base attack";
      }
      return $rv;
    case "courage_of_bladehold":
      AddCurrentTurnEffect($cardID, $mainPlayer);
      return "";
    case "twinning_blade_yellow":
      $character = &GetPlayerCharacter($currentPlayer);
      if(SubtypeContains($character[$combatChainState[$CCS_WeaponIndex]], "Sword", $currentPlayer)) {
        ++$character[$combatChainState[$CCS_WeaponIndex] + 5];
        if($character[$combatChainState[$CCS_WeaponIndex] + 1] == 1) $character[$combatChainState[$CCS_WeaponIndex] + 1] = 2;
      }
      else {
        $weaponIndex = SearchCharacterIndexSubtype($currentPlayer, "Sword");
        if($weaponIndex != -1) {
          ++$character[$weaponIndex + 5];
          if($character[$weaponIndex + 1] == 1) $character[$weaponIndex + 1] = 2;
        }
      }
      return "";
    case "unified_decree_yellow":
      if(RepriseActive()) {
        AddDecisionQueue("DECKCARDS", $currentPlayer, "0");
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
        AddDecisionQueue("ALLCARDTYPEORPASS", $currentPlayer, "AR", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Do you want to banish <0> with ".CardLink($cardID, $cardID)."?");
        AddDecisionQueue("YESNO", $currentPlayer, "whether to banish the card", 1);
        AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
        AddDecisionQueue("PARAMDELIMTOARRAY", $currentPlayer, "0", 1);
        AddDecisionQueue("MULTIREMOVEDECK", $currentPlayer, "0", 1);
        AddDecisionQueue("MULTIBANISH", $currentPlayer, "DECK,TCC", 1);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
        AddDecisionQueue("WRITELOG", $currentPlayer, "<0> was banished.", 1);
      }
      return "";
    case "spoils_of_war_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      AddCurrentTurnEffect($cardID . "-2", $currentPlayer);
      return "";
    case "dauntless_red": case "dauntless_yellow": case "dauntless_blue":
      AddCurrentTurnEffect($cardID . "-1", $mainPlayer);
      AddCurrentTurnEffect($cardID . "-2", ($mainPlayer == 1 ? 2 : 1));
      return "";
    case "out_for_blood_red": case "out_for_blood_yellow": case "out_for_blood_blue":
      AddCurrentTurnEffect($cardID . "-1", $mainPlayer);
      if(RepriseActive()) AddCurrentTurnEffectFromCombat($cardID . "-2", $mainPlayer);
      return "";
    case "hit_and_run_red": case "hit_and_run_yellow": case "hit_and_run_blue":
      AddCurrentTurnEffect($cardID . "-1", $mainPlayer);
      if(GetClassState($currentPlayer, $CS_AtksWWeapon) > 0)
      {
        AddCurrentTurnEffect($cardID . "-2", $mainPlayer);
        $rv = "Gives your next weapon +" . EffectAttackModifier($cardID . "-2") . " because you've attacked with a weapon";
      }
      return $rv;
    case "push_forward_red": case "push_forward_yellow": case "push_forward_blue":
      AddCurrentTurnEffect($cardID . "-1", $mainPlayer);
      if(GetClassState($currentPlayer, $CS_AtksWWeapon) > 0) {
        AddCurrentTurnEffect($cardID . "-2", $mainPlayer);
        $rv = "Gives your attack dominate because you've attacked with a weapon";
      }
      return $rv;
    case "plasma_barrel_shot":
      $abilityType = GetResolvedAbilityType($cardID);
      if($abilityType == "A") {
        $character = &GetPlayerCharacter($currentPlayer);
        $index = GetClassState($currentPlayer, $CS_PlayIndex);
        $character[$index + 2] = 1;
      }
      return "";
    case "viziertronic_model_i":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "meganetic_shockwave_blue":
      if($combatChainState[$CCS_NumBoosted] && !IsAllyAttackTarget()) {
        if ($combatChainState[$CCS_NumBoosted] > 1 && IsOverpowerActive()) $combatChainState[$CCS_RequiredEquipmentBlock] = 1;
        else $combatChainState[$CCS_RequiredEquipmentBlock] = $combatChainState[$CCS_NumBoosted];
        $rv .= "Requires you to block with " . $combatChainState[$CCS_NumBoosted] . " equipment if able";
      }
      return $rv;
    case "plasma_purifier_red":
      if($from == "PLAY") {
        $items = &GetItems($currentPlayer);
        $index = GetClassState($currentPlayer, $CS_PlayIndex);
        if(ClassContains($items[$index], "MECHANOLOGIST", $currentPlayer) && $items[$index + 2] == 2 && $additionalCosts == "PAID") {
          $items[$index + 2] = 1;
          AddCurrentTurnEffect($cardID, $currentPlayer);
        } else {
          $items[$index + 1] = 1;
        }
      }
      return "";
    case "teklovossens_workshop_red": case "teklovossens_workshop_yellow": case "teklovossens_workshop_blue":
      if($cardID == "teklovossens_workshop_red") $maxCost = 2;
      else if($cardID == "teklovossens_workshop_yellow") $maxCost = 1;
      else if($cardID == "teklovossens_workshop_blue") $maxCost = 0;
      Opt($cardID, GetClassState($currentPlayer, $CS_NumBoosted));
      AddDecisionQueue("DECKCARDS", $currentPlayer, "0");
      AddDecisionQueue("REVEALCARDS", $currentPlayer, "-", 1);
      AddDecisionQueue("ALLCARDSUBTYPEORPASS", $currentPlayer, "Item", 1);
      AddDecisionQueue("ALLCARDMAXCOSTORPASS", $currentPlayer, $maxCost, 1);
      AddDecisionQueue("FINDINDICES", $currentPlayer, "TOPDECK", 1);
      AddDecisionQueue("MULTIREMOVEDECK", $currentPlayer, "<-", 1);
      AddDecisionQueue("PUTPLAY", $currentPlayer, "-", 1);
      return "";
    case "kavdaen_trader_of_skins":
      if(PlayerHasLessHealth(1)) {
        LoseHealth(1, 2);
        PutItemIntoPlayForPlayer("copper", 2);
        WriteLog("Player 2 lost a life and gained a copper from Kavdaen");
        if(PlayerHasLessHealth(1)) {
          GainHealth(1, 1);
        }
      } else if(PlayerHasLessHealth(2)) {
        LoseHealth(1, 1);
        PutItemIntoPlayForPlayer("copper", 1);
        WriteLog("Player 1 lost a life and gained a copper from Kavdaen");
        if(PlayerHasLessHealth(2)) {
          GainHealth(1, 2);
        }
      }
      return "";
    case "red_liner":
      if(!ArsenalEmpty($currentPlayer)) return "Your arsenal is full, you cannot reload";
      LoadArrow($currentPlayer);
      return "";
    case "perch_grapplers":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "poison_the_tips_yellow":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      Reload();
      return "";
    case "feign_death_yellow":
      SetClassState($currentPlayer, $CS_NextDamagePrevented, 1);
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "increase_the_tension_red": case "increase_the_tension_yellow": case "increase_the_tension_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      AddCurrentTurnEffect($cardID . "-1", ($currentPlayer == 1 ? 2 : 1));
      return "";
    case "bloodsheath_skeleta":
      AddCurrentTurnEffect($cardID . "-AA", $currentPlayer);
      AddCurrentTurnEffect($cardID . "-NAA", $currentPlayer);
      return "";
    case "dread_triptych_blue":
      AddLayer("TRIGGER", $currentPlayer, $cardID);
      return "";
    case "rattle_bones_red":
      $params = explode("-", $target);
      $index = SearchdiscardForUniqueID($params[1], $currentPlayer);
      if ($index != -1) {
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "MYDISCARD-" . $index, 1);
        AddDecisionQueue("MZADDZONE", $currentPlayer, "MYBANISH,GY,TT", 1);
        AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      } 
      else {
        WriteLog(CardLink($cardID, $cardID) . " layer fails as there are no remaining targets for the targeted effect.");
        return "FAILED";
      }
      return "";
    case "runeblood_barrier_yellow":
      PlayAura("runechant", $currentPlayer, 4);
      return "";
    case "mauvrion_skies_red": case "mauvrion_skies_yellow": case "mauvrion_skies_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "sutcliffes_research_notes_red": case "sutcliffes_research_notes_yellow": case "sutcliffes_research_notes_blue":
      if($cardID == "sutcliffes_research_notes_red") $count = 3;
      else if($cardID == "sutcliffes_research_notes_yellow") $count = 2;
      else $count = 1;
      $deck = new Deck($currentPlayer);
      $numRunechants = 0;
      if($deck->Reveal($count)) {
        $cards = explode(",", $deck->Top(remove:true, amount:$count));
        for($i=0; $i<count($cards); ++$i) if(ClassContains($cards[$i], "RUNEBLADE", $currentPlayer) && CardType($cards[$i]) == "AA") ++$numRunechants;
        if($numRunechants > 0) PlayAura("runechant", $currentPlayer, number:$numRunechants);
        AddDecisionQueue("CHOOSETOP", $currentPlayer, implode(",", $cards));
      }
      return "";
    case "aether_conduit":
      DealArcane(2, 0, "ABILITY", $cardID);
      return "";
    case "chain_lightning_yellow":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      SetClassState($currentPlayer, $CS_NextWizardNAAInstant, 1);
      if(GetClassState($currentPlayer, $CS_NumWizardNonAttack) >= 2) {
        DealArcane(3, 1, "PLAYCARD", $cardID, resolvedTarget: $target);
      }
      return "";
    case "gaze_the_ages_blue":
      PlayerOpt($currentPlayer, 2);
      return "";
    case "aetherize_blue":
      NegateLayer($target);
      return "";
    case "cindering_foresight_red": case "cindering_foresight_yellow": case "cindering_foresight_blue":
      if($cardID == "cindering_foresight_red") $optAmount = 3;
      else if($cardID == "cindering_foresight_yellow") $optAmount = 2;
      else $optAmount = 1;
      AddCurrentTurnEffect($cardID, $currentPlayer);
      PlayerOpt($currentPlayer, $optAmount);
      return "";
    case "foreboding_bolt_red": case "foreboding_bolt_yellow": case "foreboding_bolt_blue":
      DealArcane(ArcaneDamage($cardID), 0, "PLAYCARD", $cardID, resolvedTarget: $target);
      PlayerOpt($currentPlayer, 1);
      return "";
    case "rousing_aether_red": case "rousing_aether_yellow": case "rousing_aether_blue":
      DealArcane(ArcaneDamage($cardID), 0, "PLAYCARD", $cardID, resolvedTarget: $target);
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "snapback_red": case "snapback_yellow": case "snapback_blue":
      DealArcane(ArcaneDamage($cardID), 0, "PLAYCARD",$cardID, resolvedTarget: $target);
      return "";
    case "gorganian_tome":
      $count = SearchCount(CombineSearches(SearchDiscardForCard(1, "gorganian_tome"), SearchDiscardForCard(2, "gorganian_tome")));
      for($i = 0; $i < $count; ++$i) Draw($currentPlayer);
      return "Drew " . $count . " card" . ($count > 1 ? "s" : "");
    case "snag_blue":
      AddCurrentTurnEffect("snag_blue", ($currentPlayer == 1 ? 2 : 1));
      return "";
    case "promise_of_plenty_red": case "promise_of_plenty_yellow": case "promise_of_plenty_blue":
      if($from == "ARS") {
        GiveAttackGoAgain();
        $rv = "Gains go again";
      }
      return $rv;
    case "lunging_press_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "cash_in_yellow":
      Draw($currentPlayer);
      Draw($currentPlayer);
      return "";
    case "reinforce_the_line_red": case "reinforce_the_line_yellow": case "reinforce_the_line_blue":
      $options = GetChainLinkCards($defPlayer, "AA");
      if($options == "") return "No defending attack action cards";
      AddDecisionQueue("CHOOSECOMBATCHAIN", $currentPlayer, $options);
      AddDecisionQueue("COMBATCHAINDEFENSEMODIFIER", $currentPlayer, PlayBlockModifier($cardID), 1);
      return "";
    case "copper":
      if($from == "PLAY") {
        Draw($currentPlayer);
        DestroyItemForPlayer($currentPlayer, GetClassState($currentPlayer, $CS_PlayIndex));
      }
      return "";
    default: return "";
  }
}

function CRUHitEffect($cardID)
{
  global $mainPlayer, $defPlayer, $combatChainState, $CS_ArcaneDamageTaken;
  switch($cardID) {
    case "find_center_blue": PlayAura("zen_state", $mainPlayer); break;
    case "rushing_river_red": case "rushing_river_yellow": case "rushing_river_blue":
      if(ComboActive()) {
        $num = NumAttacksHit()+1;
        for($i = 0; $i < $num; ++$i) {
          Draw($mainPlayer);
          AddDecisionQueue("FINDINDICES", $mainPlayer, "HAND");
          AddDecisionQueue("CHOOSEHAND", $mainPlayer, "<-", 1);
          AddDecisionQueue("MULTIREMOVEHAND", $mainPlayer, "-", 1);
          AddDecisionQueue("MULTIADDTOPDECK", $mainPlayer, "-", 1);
        }
      }
      break;
    case "soulbead_strike_red": case "soulbead_strike_yellow": case "soulbead_strike_blue":
      GiveAttackGoAgain();
      break;
    case "torrent_of_tempo_red": case "torrent_of_tempo_yellow": case "torrent_of_tempo_blue":
      GiveAttackGoAgain();
      break;
    case "bittering_thorns_yellow":
      AddCurrentTurnEffectFromCombat($cardID, $mainPlayer);
      break;
    case "whirling_mist_blossom_yellow":
      if(HitsInRow() > 0) {
        Draw($mainPlayer);
        Draw($mainPlayer);
      }
      break;
    case "high_speed_impact_red": case "high_speed_impact_yellow": case "high_speed_impact_blue":
      AddCurrentTurnEffectFromCombat($cardID, $mainPlayer);
      break;
    case "combustible_courier_red": case "combustible_courier_yellow": case "combustible_courier_blue":
      AddCurrentTurnEffectFromCombat($cardID, $mainPlayer);
      break;
    case "remorseless_red":
      if(IsHeroAttackTarget()) {
        AddCurrentTurnEffect("remorseless_red-DMG", $defPlayer);
        AddNextTurnEffect("remorseless_red-DMG", $defPlayer);
      }
      break;
    case "pathing_helix_red": case "pathing_helix_yellow": case "pathing_helix_blue":
      if(!ArsenalEmpty($mainPlayer)) return "There is already a card in your arsenal, so you cannot put an arrow in your arsenal";
      MZMoveCard($mainPlayer, "MYHAND", "MYARS,HAND,DOWN", may:true);
      break;
    case "sleep_dart_red": case "sleep_dart_yellow": case "sleep_dart_blue":
      if(IsHeroAttackTarget()) {
        $char = &GetPlayerCharacter($defPlayer);
        $char[1] = 3;
      }
      break;
    case "dread_triptych_blue":
      PlayAura("runechant", $mainPlayer);
      break;
    case "consuming_volition_red": case "consuming_volition_yellow": case "consuming_volition_blue":
      if(IsHeroAttackTarget() && GetClassState($defPlayer, $CS_ArcaneDamageTaken)) PummelHit();
      break;
    case "meat_and_greet_red": case "meat_and_greet_yellow": case "meat_and_greet_blue":
      PlayAura("runechant", $mainPlayer);
      break;
    case "coax_a_commotion_red":
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose any number of options");
      AddDecisionQueue("MAYMULTICHOOSETEXT", $mainPlayer, "3-Quicken_token,Draw_card,Gain_life");
      AddDecisionQueue("MODAL", $mainPlayer, "COAXCOMMOTION", 1);
      AddDecisionQueue("SHOWMODES", $mainPlayer, $cardID, 1);
      break;
    case "promise_of_plenty_red": case "promise_of_plenty_yellow": case "promise_of_plenty_blue":
      if(ArsenalEmpty($defPlayer)) TopDeckToArsenal($defPlayer);
      if(ArsenalEmpty($mainPlayer)) TopDeckToArsenal($mainPlayer);
      break;
    default: break;
  }
}

function KayoStaticAbility($cardId)
{
  global $combatChainState, $CCS_LinkBaseAttack, $mainPlayer;
  $roll = GetDieRoll($mainPlayer);
  if(PowerCantBeModified($cardId)) return;
  if($roll >= 5) {
    if(CanGainAttack($cardId)) {
      $combatChainState[$CCS_LinkBaseAttack] *= 2;
    }
  } else $combatChainState[$CCS_LinkBaseAttack] = floor($combatChainState[$CCS_LinkBaseAttack] / 2);
}

function KassaiEndTurnAbility()
{
  global $mainPlayer, $CS_AtksWWeapon, $CS_HitsWithWeapon;
  if(GetClassState($mainPlayer, $CS_AtksWWeapon) >= 2) {
    PutItemIntoPlayForPlayer("copper", $mainPlayer, number:GetClassState($mainPlayer, $CS_HitsWithWeapon));
  }
}
