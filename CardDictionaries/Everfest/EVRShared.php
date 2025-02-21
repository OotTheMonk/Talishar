<?php

  function EVRAbilityCost($cardID)
  {
    switch($cardID)
    {
      case "helm_of_sharp_eye": return 1;
      case "microprocessor_blue": return 0;
      case "genis_wotchuneed": return 2;
      case "dreadbore": return 1;
      case "vexing_quillhand": return 0;
      case "crown_of_reflection": return 0;
      case "krakens_aethervein": return 3;
      case "firebreathing_red": return 1;
      case "even_bigger_than_that_red": case "even_bigger_than_that_yellow": case "even_bigger_than_that_blue": return 0;
      case "amulet_of_assertiveness_yellow": return 0;
      case "amulet_of_echoes_blue": return 0;
      case "amulet_of_havencall_blue": return 0;
      case "amulet_of_ignition_yellow": return 0;
      case "amulet_of_intervention_blue": return 0;
      case "amulet_of_oblation_blue": return 0;
      case "clarity_potion_blue": case "healing_potion_blue": case "potion_of_seeing_blue": case "potion_of_deja_vu_blue": case "potion_of_ironhide_blue": return 0;
      case "potion_of_luck_blue": return 0;
      case "talisman_of_featherfoot_yellow": return 0;
      case "silver": return 3;
      default: return 0;
    }
  }

  function EVRAbilityType($cardID, $index=-1, $from="")
  {
    switch($cardID)
    {
      case "helm_of_sharp_eye": return "AR";
      case "microprocessor_blue": return "A";
      case "genis_wotchuneed": return "A";
      case "dreadbore": return "A";
      case "vexing_quillhand": return "A";
      case "crown_of_reflection": return "I";
      case "krakens_aethervein": return "I";
      case "firebreathing_red": 
        if($from == "PLAY") return "I";
        else return "AA";
      case "amulet_of_assertiveness_yellow": 
      if($from == "PLAY") return "AR";
      else return "A";
      case "amulet_of_echoes_blue":
        if($from == "PLAY") return "I";
        else return "A";
      case "amulet_of_havencall_blue":       
        if($from == "PLAY") return "DR";
        else return "A";
      case "amulet_of_ignition_yellow": 
      case "amulet_of_intervention_blue": 
      case "amulet_of_oblation_blue": 
      case "clarity_potion_blue": 
        if($from == "PLAY") return "I";
        else return "A";
      case "healing_potion_blue": return "A";
      case "potion_of_seeing_blue": case "potion_of_deja_vu_blue": case "potion_of_ironhide_blue": case "potion_of_luck_blue": 
        if($from == "PLAY") return "I";
        else return "A";
      case "silver": return "A";
      default: return "";
    }
  }

  function EVRAbilityHasGoAgain($cardID)
  {
    switch($cardID)
    {
      case "genis_wotchuneed": return true;
      case "dreadbore": return true;
      case "vexing_quillhand": return true;
      case "healing_potion_blue": return true;
      case "silver": return true;
      default: return false;
    }
  }

  function EVREffectAttackModifier($cardID)
  {
    $params = explode(",", $cardID);
    $cardID = $params[0];
    if(count($params) > 1) $parameter = $params[1];
    switch($cardID)
    {
      case "skull_crushers": return 1;
      case "rolling_thunder_red": return $parameter;
      case "bare_fangs_red": case "bare_fangs_yellow": case "bare_fangs_blue": return 2;
      case "bad_beats_red": case "bad_beats_yellow": case "bad_beats_blue": return 5;
      case "bravo_star_of_the_show": return 2;
      case "pulverize_red": return -4;
      case "twin_twisters_red-2": case "twin_twisters_yellow-2": case "twin_twisters_blue-2": return 1;
      case "slice_and_dice_red-1": case "slice_and_dice_yellow-1": case "slice_and_dice_blue-1": return 1;
      case "slice_and_dice_red-2": return 3;
      case "slice_and_dice_yellow-2": return 2;
      case "slice_and_dice_blue-2": return 1;
      case "blade_runner_red": return 3;
      case "blade_runner_yellow": return 2;
      case "blade_runner_blue": return 1;
      case "outland_skirmish_red": return 3;
      case "outland_skirmish_yellow": return 2;
      case "outland_skirmish_blue": return 1;
      case "teklo_pounder_blue": return 2;
      case "rotary_ram_red": return 3;
      case "rotary_ram_yellow": return 2;
      case "rotary_ram_blue": return 1;
      case "dreadbore": return 1;
      case "rain_razors_yellow": return 2;
      case "release_the_tension_red": return 3;
      case "release_the_tension_yellow": return 2;
      case "release_the_tension_blue": return 1;
      case "read_the_glide_path_red": return 3;
      case "read_the_glide_path_yellow": return 2;
      case "read_the_glide_path_blue": return 1;
      case "pierce_reality_blue": return 2;
      case "veiled_intentions_red": return 4;
      case "veiled_intentions_yellow": return 3;
      case "veiled_intentions_blue": return 2;
      case "firebreathing_red-BUFF": return 1;
      case "this_rounds_on_me_blue": return IsHeroAttackTarget() ? -1 : 0;
      case "life_of_the_party_red-2": 
      case "life_of_the_party_yellow-2":
      case "life_of_the_party_blue-2": 
        return 2;
      case "smashing_good_time_red-2": return 3;
      case "smashing_good_time_yellow-2": return 2;
      case "smashing_good_time_blue-2": return 1;
      default: return 0;
    }
  }

  function EVRCombatEffectActive($cardID, $attackID)
  {
    global $CS_AtksWWeapon, $mainPlayer;
    $params = explode(",", $cardID);
    $cardID = $params[0];
    switch($cardID)
    {
      case "skull_crushers": return ClassContains($attackID, "BRUTE", $mainPlayer);
      case "rolling_thunder_red": return ClassContains($attackID, "BRUTE", $mainPlayer);
      case "bare_fangs_red": case "bare_fangs_yellow": case "bare_fangs_blue": return true;
      case "bad_beats_red": case "bad_beats_yellow": case "bad_beats_blue": return CardType($attackID) == "AA" && ClassContains($attackID, "BRUTE", $mainPlayer);
      case "bravo_star_of_the_show": return CardCost($attackID) >= 3;
      case "valda_brightaxe": return HasCrush($attackID);
      case "pulverize_red": return true;
      case "ride_the_tailwind_red": case "ride_the_tailwind_yellow": case "ride_the_tailwind_blue": return CardType($attackID) == "AA" && AttackValue($attackID) <= 2;//Base attack
      case "twin_twisters_red-1": case "twin_twisters_yellow-1": case "twin_twisters_blue-1": return true;
      case "twin_twisters_red-2": case "twin_twisters_yellow-2": case "twin_twisters_blue-2": return true;
      case "slice_and_dice_red-1": case "slice_and_dice_yellow-1": case "slice_and_dice_blue-1":
        $subtype = CardSubType($attackID);
        if($subtype != "Sword" && $subtype != "Dagger") return false;
        return TypeContains($attackID, "W", $mainPlayer) && GetClassState($mainPlayer, $CS_AtksWWeapon) == 0;
      case "slice_and_dice_red-2": case "slice_and_dice_yellow-2": case "slice_and_dice_blue-2":
        $subtype = CardSubType($attackID);
        if($subtype != "Sword" && $subtype != "Dagger") return false;
        return TypeContains($attackID, "W", $mainPlayer) && GetClassState($mainPlayer, $CS_AtksWWeapon) == 1;
      case "blade_runner_red": case "blade_runner_yellow": case "blade_runner_blue": return TypeContains($attackID, "W", $mainPlayer);
      case "outland_skirmish_red": case "outland_skirmish_yellow": case "outland_skirmish_blue": return TypeContains($attackID, "W", $mainPlayer) && Is1H($attackID);
      case "outland_skirmish_red-1": case "outland_skirmish_yellow-1": case "outland_skirmish_blue-1": return TypeContains($attackID, "W", $mainPlayer);
      case "teklo_pounder_blue": return true;
      case "rotary_ram_red": case "rotary_ram_yellow": case "rotary_ram_blue": return CardType($attackID) == "AA" && ClassContains($attackID, "MECHANOLOGIST", $mainPlayer);
      case "dreadbore": return CardSubType($attackID) == "Arrow";
      case "rain_razors_yellow": return CardSubType($attackID) == "Arrow";
      case "release_the_tension_red": case "release_the_tension_yellow": case "release_the_tension_blue": return CardSubType($attackID) == "Arrow";
      case "release_the_tension_red-1": case "release_the_tension_yellow-1": case "release_the_tension_blue-1": return CardSubType($attackID) == "Arrow";
      case "fatigue_shot_red": case "fatigue_shot_yellow": case "fatigue_shot_blue": return CardType($attackID) == "AA";
      case "read_the_glide_path_red": case "read_the_glide_path_yellow": case "read_the_glide_path_blue": return CardSubType($attackID) == "Arrow";
      case "passing_mirage_blue": return ClassContains($attackID, "ILLUSIONIST", $mainPlayer);
      case "pierce_reality_blue": return ClassContains($attackID, "ILLUSIONIST", $mainPlayer) && CardType($attackID) == "AA";
      case "veiled_intentions_red": case "veiled_intentions_yellow": case "veiled_intentions_blue": return CardType($attackID) == "AA";
      case "this_rounds_on_me_blue": return true;
      case "life_of_the_party_red-1": case "life_of_the_party_red-2": case "life_of_the_party_red-3": return true;
      case "life_of_the_party_yellow-1": case "life_of_the_party_yellow-2": case "life_of_the_party_yellow-3": return true;
      case "life_of_the_party_blue-1": case "life_of_the_party_blue-2": case "life_of_the_party_blue-3": return true;
      case "high_striker_red": case "high_striker_yellow": case "high_striker_blue": return true;
      case "smashing_good_time_red-1": case "smashing_good_time_yellow-1": case "smashing_good_time_blue-1": return CardType($attackID) == "AA";
      case "smashing_good_time_red-2": case "smashing_good_time_yellow-2": case "smashing_good_time_blue-2": return CardType($attackID) == "AA";
      case "potion_of_ironhide_blue": return true;
      case "firebreathing_red-BUFF": return true;
      default: return false;
    }
  }

  function EVRPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
  {
    global $currentPlayer, $CombatChain, $CS_PlayIndex, $combatChainState, $CCS_GoesWhereAfterLinkResolves, $CCS_NumBoosted;
    global $CS_HighestRoll, $CS_NumNonAttackCards, $CS_NumAttackCards, $mainPlayer, $CCS_RequiredEquipmentBlock, $CS_DamagePrevention;
    $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
    $rv = "";
    switch($cardID)
    {
      case "swing_big_red":
        if(IsHeroAttackTarget()) {
          AddCurrentTurnEffect($cardID, $currentPlayer);
        }
        return "";
      case "ready_to_roll_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "rolling_thunder_red":
        AddCurrentTurnEffect($cardID . "," . GetDieRoll($currentPlayer), $currentPlayer);
        return "";
      case "high_roller_red": case "high_roller_yellow": case "high_roller_blue":
        $rv = "Intimidates";
        Intimidate();
        if($cardID == "high_roller_red") $targetHigh = 4;
        else if($cardID == "high_roller_yellow") $targetHigh = 5;
        else if($cardID == "high_roller_blue") $targetHigh = 6;
        if(GetClassState($currentPlayer, $CS_HighestRoll) >= $targetHigh) Intimidate();
        return "";
      case "bare_fangs_red": case "bare_fangs_yellow": case "bare_fangs_blue":
        Draw($currentPlayer);
        $card = DiscardRandom();
        if(ModifiedAttackValue($card, $currentPlayer, "HAND", source:$cardID) >= 6) AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "wild_ride_red": case "wild_ride_yellow": case "wild_ride_blue":
        Draw($currentPlayer);
        $card = DiscardRandom();
        if(ModifiedAttackValue($card, $currentPlayer, "HAND", source:$cardID) >= 6) GiveAttackGoAgain();
        return "";
      case "bad_beats_red": case "bad_beats_yellow": case "bad_beats_blue":
        if($cardID == "bad_beats_red") $target = 4;
        else if($cardID == "bad_beats_yellow") $target = 5;
        else $target = 6;
        if(GetDieRoll($currentPlayer) >= $target) AddCurrentTurnEffect($cardID, $currentPlayer);
        return $rv;
      case "imposing_visage_blue":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "DECKAURAMAXCOST," . ($resourcesPaid-CardCost($cardID)), 1);
        AddDecisionQueue("MAYCHOOSEDECK", $currentPlayer, "<-", 1);
        AddDecisionQueue("PUTPLAY", $currentPlayer, "-", 1);
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
        return "";
      case "nerves_of_steel_blue":
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:type=E;subtype=Chest;hasNegCounters=true");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a chest piece to remove a -1 defense counter");
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZOP", $currentPlayer, "GETCARDINDEX", 1);
        AddDecisionQueue("MODDEFCOUNTER", $currentPlayer, "1", 1);
        return "";
      case "seismic_stir_red": case "seismic_stir_yellow": case "seismic_stir_blue":
        if($cardID == "seismic_stir_red") $amount = 3;
        else if($cardID == "seismic_stir_yellow") $amount = 2;
        else $amount = 1;
        PlayAura("seismic_surge", $currentPlayer, $amount);
        return "";
      case "steadfast_red": case "steadfast_yellow": case "steadfast_blue":
        if($target != "-") {
          if(substr($target, 0, 2) == "MY") AddCurrentTurnEffect($cardID, $currentPlayer, $from, GetMZCard(($currentPlayer == 1 ? 2 : 1), $target));
          else AddCurrentTurnEffect($cardID, $currentPlayer, $from, GetMZCard($currentPlayer, $target));
        }
        return "";
      case "twin_twisters_red": case "twin_twisters_yellow": case "twin_twisters_blue":
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts);
        AddDecisionQueue("MODAL", $currentPlayer, "TWINTWISTERS");
        return "";
      case "helm_of_sharp_eye":
        $deck = new Deck($currentPlayer);
        $deck->BanishTop("TCC");
        return "";
      case "shatter_yellow":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "WEAPON");
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("ADDMZBUFF", $currentPlayer, "shatter_yellow", 1);
        return "";
      case "blood_on_her_hands_yellow":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts, 1);
        AddDecisionQueue("MODAL", $currentPlayer, "BLOODONHERHANDS", 1);
        break;
      case "oath_of_steel_red":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "slice_and_dice_red": case "slice_and_dice_yellow": case "slice_and_dice_blue":
        AddCurrentTurnEffect($cardID . "-1", $currentPlayer);
        AddCurrentTurnEffect($cardID . "-2", $currentPlayer);
        return "";
      case "blade_runner_red": case "blade_runner_yellow": case "blade_runner_blue":
        GiveAttackGoAgain();
        AddCurrentTurnEffectFromCombat($cardID, $currentPlayer);
        return "";
      case "outland_skirmish_red": case "outland_skirmish_yellow": case "outland_skirmish_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        AddCurrentTurnEffect($cardID . "-1", $currentPlayer);
        return "";
      case "microprocessor_blue":
        if($from == "PLAY")
        {
          $items = &GetItems($currentPlayer);
          if($items[GetClassState($currentPlayer, $CS_PlayIndex)+3] == 2) { $rv = "Gained an action point from Micro-Processor"; GainActionPoints(1); }
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a mode");
          AddDecisionQueue("BUTTONINPUT", $currentPlayer, $items[GetClassState($currentPlayer, $CS_PlayIndex)+8]);
          AddDecisionQueue("MODAL", $currentPlayer, "MICROPROCESSOR,".GetClassState($currentPlayer, $CS_PlayIndex), 1);
        }
        return $rv;
      case "tbone_red": case "tbone_yellow": case "tbone_blue":
        if($combatChainState[$CCS_NumBoosted] && !IsAllyAttackTarget()) $combatChainState[$CCS_RequiredEquipmentBlock] = 1;
        return "";
      case "zoom_in_red": case "zoom_in_yellow": case "zoom_in_blue":
        Opt($cardID, $combatChainState[$CCS_NumBoosted]);
        return "Lets you opt " . $combatChainState[$CCS_NumBoosted];
      case "rotary_ram_red": case "rotary_ram_yellow": case "rotary_ram_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "genis_wotchuneed":
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "0");
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
        AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
        AddDecisionQueue("MAYCHOOSEHAND", $otherPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEHAND", $otherPlayer, "-", 1);
        AddDecisionQueue("ADDBOTDECK", $otherPlayer, "-", 1);
        AddDecisionQueue("DRAW", $otherPlayer, "-", 1);
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "silver", 1);
        AddDecisionQueue("PUTPLAY", $currentPlayer, "0", 1);
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "1", 1);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
        AddDecisionQueue("DQVARPASSIFSET", $currentPlayer, "0");
        AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
        return "";
      case "dreadbore":
        LoadArrow($currentPlayer);
        AddDecisionQueue("LASTARSENALADDEFFECT", $currentPlayer, $cardID . ",HAND", 1);
        return "";
      case "trishot_blue":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "WEAPON,Bow");
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("ADDMZUSES", $currentPlayer, 2, 1);
        return "";
      case "rain_razors_yellow":
        AddCurrentTurnEffect($cardID, 1);
        AddCurrentTurnEffect($cardID, 2);
        return "";
      case "release_the_tension_red": case "release_the_tension_yellow": case "release_the_tension_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        AddCurrentTurnEffect($cardID . "-1", $otherPlayer);
        return "";
      case "read_the_glide_path_red": case "read_the_glide_path_yellow": case "read_the_glide_path_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        Opt($cardID, 1);
        return "";
      case "vexing_quillhand":
        PlayAura("runechant", $currentPlayer, 2);
        return "";
      case "revel_in_runeblood_red":
        if(GetClassState($currentPlayer, $CS_NumNonAttackCards) > 1 && GetClassState($currentPlayer, $CS_NumAttackCards) > 0) PlayAura("runechant", $currentPlayer, 4);
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "krakens_aethervein":
        DealArcane(1, 1, "ABILITY", $cardID);
        AddDecisionQueue("SPECIFICCARD", $currentPlayer, "KRAKENAETHERVEIN");
        return "";
      case "aether_wildfire_red":
        DealArcane(4, 1, "PLAYCARD", $cardID, resolvedTarget: $target);
        if($currentPlayer != $mainPlayer) {
          AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "aether_wildfire_red,");
          AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "<-");
        }
        return "";
      case "scour_blue":
        $targetPlayer = substr($target, 0, 5) == "THEIR";
        $parameter = $targetPlayer ? "THEIRAURAS:maxCost=0" : "MYAURAS:maxCost=0";
        for($i=0; $i<$resourcesPaid; ++$i) {
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, $parameter);
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose " . $resourcesPaid-$i . " aura(s) to destroy", 1);
          AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
        }
        AddDecisionQueue("SCOUR", $currentPlayer, $resourcesPaid.",".$targetPlayer, 1);
        return "";
      case "emeritus_scolding_red": case "emeritus_scolding_yellow": case "emeritus_scolding_blue":
        $oppTurn = $currentPlayer != $mainPlayer;
        if($cardID == "emeritus_scolding_red") $damage = ($oppTurn ? 6 : 4);
        if($cardID == "emeritus_scolding_yellow") $damage = ($oppTurn ? 5 : 3);
        if($cardID == "emeritus_scolding_blue") $damage = ($oppTurn ? 4 : 2);
        DealArcane($damage, 0, "PLAYCARD", $cardID, resolvedTarget: $target);
        return "";
      case "pry_red": case "pry_yellow": case "pry_blue":
        if($mainPlayer != $currentPlayer) $numReveal = count(GetHand($otherPlayer));
        else if($cardID == "pry_red") $numReveal = 3;
        else if($cardID == "pry_yellow") $numReveal = 2;
        else $numReveal = 1;
        AddDecisionQueue("PASSPARAMETER", $mainPlayer, $numReveal);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose target hero");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Target_Opponent,Target_Yourself");
        AddDecisionQueue("PLAYERTARGETEDABILITY", $currentPlayer, "PRY", 1);
        return "";
      case "timekeepers_whim_red": case "timekeepers_whim_yellow": case "timekeepers_whim_blue":
        DealArcane(ArcaneDamage($cardID), 0, "PLAYCARD", $cardID, resolvedTarget: $target);
        return "";
      case "crown_of_reflection":
        MZChooseAndDestroy($currentPlayer, "MYAURAS:class=ILLUSIONIST");
        AddDecisionQueue("FINDINDICES", $currentPlayer, "CROWNOFREFLECTION", 1);
        AddDecisionQueue("MAYCHOOSEHAND", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-", 1);
        AddDecisionQueue("PUTPLAY", $currentPlayer, "-", 1);
        return "";
      case "fractal_replication_red":
        FractalReplicationStats("Ability");
        return "";
      case "veiled_intentions_red": case "veiled_intentions_yellow": case "veiled_intentions_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "firebreathing_red":
        if($from == "PLAY") AddCurrentTurnEffect($cardID . "-BUFF", $currentPlayer, "PLAY");
        return "";
      case "cash_out_blue":
        PutItemIntoPlayForPlayer("silver", $currentPlayer, 0, intval($additionalCosts));
        return "";
      case "knick_knack_bricabrac_red":
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts);
        AddDecisionQueue("SPECIFICCARD", $currentPlayer, "KNICKKNACK");
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");  
        return "";
      case "this_rounds_on_me_blue":
        Draw(1);
        Draw(2);
        if($currentPlayer != $mainPlayer) AddCurrentTurnEffect($cardID, $otherPlayer);
        else AddNextTurnEffect($cardID, $otherPlayer);
        return "";
      case "life_of_the_party_red": case "life_of_the_party_yellow": case "life_of_the_party_blue":
        $rand = GetRandom(1, 3);
        $altCostPaid = DelimStringContains($additionalCosts, "ALTERNATIVECOST");
        $log = "";
        if($altCostPaid || $rand == 1) { 
          $log .= " When this hits gain 2 life"; 
          AddCurrentTurnEffect($cardID . "-1", $currentPlayer); 
        }
        if($altCostPaid || $rand == 2) { 
          if($log != "") $log .= ", +2 power";
          else $log = " gained +2 power"; 
          AddCurrentTurnEffect($cardID . "-2", $currentPlayer); 
        }
        if($altCostPaid || $rand == 3) { 
          if($log != "") $log .= " and go again.";
          else $log .= " gained go again"; 
          AddCurrentTurnEffect($cardID . "-3", $currentPlayer); 
        }
        return $log .= ($altCostPaid == 1 ? " Party time!🍻" : ".");
      case "high_striker_red": case "high_striker_yellow": case "high_striker_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "pick_a_card_any_card_red": case "pick_a_card_any_card_yellow": case "pick_a_card_any_card_blue":
        if($cardID == "pick_a_card_any_card_red") $times = 4;
        else if($cardID == "pick_a_card_any_card_yellow") $times = 3;
        else if($cardID == "pick_a_card_any_card_blue") $times = 2;
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRHAND");
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
        AddDecisionQueue("MZOP", $currentPlayer, "GETCARDID", 1);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "1", 1);
        AddDecisionQueue("WRITELOG", $currentPlayer, "Card chosen: <1>", 1);
        for($i=0; $i<$times; ++$i) AddDecisionQueue("SPECIFICCARD", $currentPlayer, "PICKACARD", 1);
        return "";
      case "smashing_good_time_red": case "smashing_good_time_yellow": case "smashing_good_time_blue":
        $rv = "Makes your next attack action that hits destroy an item";
        AddCurrentTurnEffect($cardID . "-1", $currentPlayer);
        if($from == "ARS") AddCurrentTurnEffect($cardID . "-2", $currentPlayer);
        return "";
      case "even_bigger_than_that_red": case "even_bigger_than_that_yellow": case "even_bigger_than_that_blue":
        if($cardID == "even_bigger_than_that_red") $opt = 3;
        else if($cardID == "even_bigger_than_that_yellow") $opt = 2;
        else if($cardID == "even_bigger_than_that_blue") $opt = 1;
        PlayerOpt($currentPlayer, $opt);
        AddDecisionQueue("SPECIFICCARD", $currentPlayer, "EVENBIGGERTHANTHAT-".$cardID);
        return "";
      case "amulet_of_assertiveness_yellow":
        if($from == "PLAY") {
          $deck = new Deck($currentPlayer);
          if($deck->Empty()) return "Deck is empty";
          $deck->BanishTop(CardType($deck->Top()) == "AA" ? "TT" : "-");
        }
        return "";
      case "amulet_of_echoes_blue":
        if($from == "PLAY") {
          if(ShouldAutotargetOpponent($currentPlayer)) {
            AddDecisionQueue("PASSPARAMETER", $currentPlayer, "Target_Opponent");
          }
          else {
            AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose target hero");
            AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Target_Opponent,Target_Yourself");
          }
          AddDecisionQueue("PLAYERTARGETEDABILITY", $currentPlayer, "AMULETOFECHOES", 1);
        }
        return "";
      case "amulet_of_havencall_blue":
        if($from == "PLAY") {
          AddDecisionQueue("FINDINDICES", $currentPlayer, "amulet_of_havencall_blue");
          AddDecisionQueue("MAYCHOOSEDECK", $currentPlayer, "<-", 1);
          AddDecisionQueue("ADDCARDTOCHAINASDEFENDINGCARD", $currentPlayer, "DECK", 1);
          AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
        }
        return "";
      case "amulet_of_ignition_yellow":
        if($from == "PLAY") AddCurrentTurnEffect($cardID, $currentPlayer, $from);
        return "";
      case "amulet_of_intervention_blue":
        if($from == "PLAY") {
          AddCurrentTurnEffect($cardID, $currentPlayer, $from);
          IncrementClassState($currentPlayer, $CS_DamagePrevention, 1);
        }
        return "";
      case "amulet_of_oblation_blue":
        if($from == "PLAY") {
          AddDecisionQueue("FINDINDICES", $currentPlayer, "CCAA");
          AddDecisionQueue("CHOOSECOMBATCHAIN", $currentPlayer, "<-", 1);
          AddDecisionQueue("AMULETOFOBLATION", $currentPlayer, $cardID."-!CC", 1);
        }
        return "";
      case "clarity_potion_blue":
        if($from == "PLAY") PlayerOpt($currentPlayer, 2);
        return "";
      case "healing_potion_blue":
        if($from == "PLAY") GainHealth(2, $currentPlayer);
        return "";
      case "potion_of_seeing_blue":
        if($from == "PLAY") LookAtHand($otherPlayer);
        return "";
      case "potion_of_deja_vu_blue":
        if($from == "PLAY"){
          $cards = "";
          $pitch = &GetPitch($currentPlayer);
          while(count($pitch) > 0) {
            if($cards != "") $cards .= ",";
            $cards .= array_shift($pitch);
            for($i=1; $i<PitchPieces(); ++$i) array_shift($pitch);
          }
          if($cards != "") AddDecisionQueue("CHOOSETOP", $currentPlayer, $cards);
        }
        return "";
      case "potion_of_ironhide_blue":
        if($from == "PLAY") AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "potion_of_luck_blue":
        if($from == "PLAY"){
          $numToDraw = 0;
          $card = "";
          while(($card = RemoveHand($currentPlayer, 0)) != "") { AddBottomDeck($card, $currentPlayer, "HAND"); ++$numToDraw; }
          while(($card = RemoveArsenal($currentPlayer, 0)) != "") { AddBottomDeck($card, $currentPlayer, "ARS"); ++$numToDraw; }
          AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
          for($i = 0; $i < $numToDraw; $i++) AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
        }
        return "";
      case "talisman_of_featherfoot_yellow":
        if($from == "PLAY"){
          DestroyItemForPlayer($currentPlayer, GetClassState($currentPlayer, $CS_PlayIndex));
          GiveAttackGoAgain();
        }
        return "Partially manual card: Activate the instant ability if you met the criteria";
      case "silver":
        if($from == "PLAY"){
          DestroyItemForPlayer($currentPlayer, GetClassState($currentPlayer, $CS_PlayIndex));
          Draw($currentPlayer);
        }
        return "";
      default: return "";
    }
  }

  function EVRHitEffect($cardID)
  {
    global $mainPlayer, $defPlayer, $CS_NumAuras, $chainLinks, $chainLinkSummary;
    switch($cardID)
    {
      case "pulverize_red":
        if(IsHeroAttackTarget()) AddNextTurnEffect($cardID, $defPlayer);
        break;
      case "break_tide_yellow":
        if(ComboActive()) {
          $deck = new Deck($mainPlayer);
          $deck->BanishTop("NT", $mainPlayer);
        }
        break;
      case "spring_tidings_yellow":
        for($i=0; $i<SearchCount(SearchChainLinks(-1, 2, "AA")); ++$i) Draw($mainPlayer);
        break;
      case "winds_of_eternity_blue":
        if(ComboActive())
        {
          $deck = new Deck($mainPlayer);
          for($i=0; $i<count($chainLinks); ++$i)
          {
            $listOfNames = $chainLinkSummary[$i*ChainLinkSummaryPieces()+4];
            foreach (explode(",", $listOfNames) as $name) {
              if($chainLinks[$i][2] == "1" && GamestateUnsanitize($name) == "Hundred Winds")
              {
                $chainLinks[$i][2] = "0";
                $deck->AddBottom($chainLinks[$i][0], "CC");
              }
            }
          }
          AddDecisionQueue("SHUFFLEDECK", $mainPlayer, "-");
        }
        break;
      case "ride_the_tailwind_red": case "ride_the_tailwind_yellow": case "ride_the_tailwind_blue":
        AddCurrentTurnEffectFromCombat($cardID, $mainPlayer);
        break;
      case "battering_bolt_red":
        if(IsHeroAttackTarget() && CanRevealCards($mainPlayer))
        {
          $hand = &GetHand($defPlayer);
          $cards = "";
          $numDiscarded = 0;
          for($i=count($hand)-HandPieces(); $i>=0; $i-=HandPieces())
          {
            $id = $hand[$i];
            $cardType = CardType($id);
            if(!DelimStringContains($cardType, "A") && $cardType != "AA")
            {
              AddGraveyard($id, $defPlayer, "HAND");
              unset($hand[$i]);
              ++$numDiscarded;
            }
            if($cards != "") $cards .= ",";
            $cards .= $id;
          }
          LoseHealth($numDiscarded, $defPlayer);
          RevealCards($cards, $defPlayer);//CanReveal checked earlier
          if($numDiscarded > 0)WriteLog(CardLink("battering_bolt_red", "battering_bolt_red") . " discarded " . $numDiscarded . " and caused the defending player to lose that much life.");
          $hand = array_values($hand);
        }
        break;
      case "fatigue_shot_red": case "fatigue_shot_yellow": case "fatigue_shot_blue":
        if(IsHeroAttackTarget()) AddNextTurnEffect($cardID, $defPlayer);
        break;
      case "timidity_point_red": case "timidity_point_yellow": case "timidity_point_blue":
        if(IsHeroAttackTarget()) AddNextTurnEffect($cardID, $defPlayer);
        break;
      case "runic_reclamation_red":
        if(IsHeroAttackTarget()) {
          MZChooseAndDestroy($mainPlayer, "THEIRAURAS");
          AddDecisionQueue("PASSPARAMETER", $mainPlayer, "runechant", 1);
          AddDecisionQueue("PUTPLAY", $mainPlayer, "-", 1);
        }
        break;
      case "swarming_gloomveil_red":
        if(IsHeroAttackTarget() && GetClassState($mainPlayer, $CS_NumAuras) >= 3) AddCurrentTurnEffect("swarming_gloomveil_red", $defPlayer);
        break;
      case "drowning_dire_red": case "drowning_dire_yellow": case "drowning_dire_blue":
        MZMoveCard($mainPlayer, "MYDISCARD:type=A", "MYBOTDECK", may:true);
        break;
      case "reek_of_corruption_red": case "reek_of_corruption_yellow": case "reek_of_corruption_blue":
        if(IsHeroAttackTarget() && GetClassState($mainPlayer, $CS_NumAuras) > 0) PummelHit();
        break;
      case "fractal_replication_red":
        FractalReplicationStats("Hit");
        break;
      case "bingo_red":
        if(IsHeroAttackTarget()) {
          AddDecisionQueue("FINDINDICES", $defPlayer, "HAND");
          AddDecisionQueue("CHOOSEHAND", $defPlayer, "<-", 1);
          AddDecisionQueue("HANDCARD", $defPlayer, "-", 1);
          AddDecisionQueue("REVEALCARDS", $defPlayer, "-", 1);
          AddDecisionQueue("BINGO", $mainPlayer, "-", 1);
        }
      default: break;
    }
  }

  function HeaveValue($cardID)
  {
    switch($cardID)
    {
      case "pulverize_red": return 3;
      case "thunder_quake_red": case "thunder_quake_yellow": case "thunder_quake_blue": return 3;
      default: return 0;
    }
  }

  function HeaveIndices()
  {
    global $mainPlayer;
    if(ArsenalFull($mainPlayer)) return "";
    $hand = &GetHand($mainPlayer);
    $heaveIndices = "";
    for($i=0; $i<count($hand); $i+=HandPieces()) {
      if(HeaveValue($hand[$i]) > 0) {
        if($heaveIndices != "") $heaveIndices .= ",";
        $heaveIndices .= $i;
      }
    }
    return $heaveIndices;
  }

  function Heave()
  {
    global $mainPlayer;
    AddDecisionQueue("FINDINDICES", $mainPlayer, "HEAVE");
    AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "You may choose to heave a card or pass");
    AddDecisionQueue("MAYCHOOSEHAND", $mainPlayer, "<-", 1, 1);
    AddDecisionQueue("MULTIREMOVEHAND", $mainPlayer, "-", 1);
    AddDecisionQueue("HEAVE", $mainPlayer, "-", 1);
  }

  function BravoStarOfTheShowIndices()
  {
    global $mainPlayer;
    $earth = SearchHand($mainPlayer, "", "", -1, -1, "", "EARTH");
    $ice = SearchHand($mainPlayer, "", "", -1, -1, "", "ICE");
    $lightning = SearchHand($mainPlayer, "", "", -1, -1, "", "LIGHTNING");
    if($earth != "" && $ice != "" && $lightning != "")
    {
      $indices = CombineSearches($earth, $ice);
      $indices = CombineSearches($indices, $lightning);
      $count = SearchCount($indices);
      if($count > 3) $count = 3;
      return $count . "-" . SearchRemoveDuplicates($indices);
    }
    return "";
  }

  function TalismanOfBalanceEndTurn()
  {
    global $mainPlayer, $defPlayer;
    if(ArsenalFull($mainPlayer)) return false;
    $mainArs = &GetArsenal($mainPlayer);
    $defArs = &GetArsenal($defPlayer);
    if(count($mainArs) < count($defArs))
    {
      $deck = new Deck($mainPlayer);
      AddArsenal($deck->Top(remove:true), $mainPlayer, "DECK", "DOWN");
      WriteLog("Talisman of Balance destroyed itself and put a card in your arsenal");
      return true;
    }
    return false;
  }

  function LifeOfThePartyIndices()
  {
    global $currentPlayer;
    $items = SearchMultizoneFormat(SearchItemsForCard("crazy_brew_blue", $currentPlayer), "MYITEMS");
    $handCards = SearchMultizoneFormat(SearchHandForCard($currentPlayer, "crazy_brew_blue"), "MYHAND");
    return CombineSearches($items, $handCards);
  }

  function CoalescentMirageDestroyed()
  {
    global $mainPlayer;
    AddDecisionQueue("FINDINDICES", $mainPlayer, "COALESCENTMIRAGE");
    AddDecisionQueue("MAYCHOOSEHAND", $mainPlayer, "<-", 1);
    AddDecisionQueue("MULTIREMOVEHAND", $mainPlayer, "-", 1);
    AddDecisionQueue("PLAYAURA", $mainPlayer, "<-", 1);
  }

  function MirragingMetamorphDestroyed()
  {
    global $mainPlayer;
    AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "MYAURAS", 1);
    AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
    AddDecisionQueue("MULTIZONETOKENCOPY", $mainPlayer, "-", 1);
  }

  function FractalReplicationStats($stat)
  {
    global $chainLinks, $CombatChain, $currentPlayer, $chainLinkSummary;
    $highestAttack = 0;
    $highestBlock = 0;
    $hasPhantasm = false;
    $hasGoAgain = false;
    for($i=0; $i<count($chainLinks); ++$i) {
      for($j=0; $j<count($chainLinks[$i]); $j+=ChainLinksPieces()) {
        $isIllusionist = ClassContains($chainLinks[$i][$j], "ILLUSIONIST", $currentPlayer) || ($j == 0 && DelimStringContains($chainLinkSummary[$i*ChainLinkSummaryPieces()+3], "ILLUSIONIST"));
        if($chainLinks[$i][$j+2] == "1" && $chainLinks[$i][$j] != "fractal_replication_red" && $isIllusionist && CardType($chainLinks[$i][$j]) == "AA")
        {
          if($stat == "Hit") ProcessHitEffect($chainLinks[$i][$j]);
          elseif ($stat == "Ability") {
            PlayAbility($chainLinks[$i][$j], "HAND", 0);
            $modalAbilities = explode("-",  $chainLinkSummary[$i*ChainLinkSummaryPieces()+7]);
            ModalAbilities($currentPlayer, $modalAbilities[0], $modalAbilities[1]);
          }
          else {
            $attack = ModifiedAttackValue($chainLinks[$i][$j], $currentPlayer, "CC", source:"fractal_replication_red");
            if($attack > $highestAttack) $highestAttack = $attack;
            $modifiedBaseAttack = $chainLinkSummary[$i*ChainLinkSummaryPieces()+6];
            if($modifiedBaseAttack > $highestAttack) $highestAttack = $modifiedBaseAttack;
            $block = BlockValue($chainLinks[$i][$j]);
            if($block > $highestBlock) $highestBlock = $block;
            if(!$hasPhantasm) $hasPhantasm = HasPhantasm($chainLinks[$i][$j]);
            if(!$hasGoAgain) $hasGoAgain = HasGoAgain($chainLinks[$i][$j]);
          }
        }
      }
    }
    for($i=0; $i<$CombatChain->NumCardsActiveLink(); ++$i) {
      $cardID = $CombatChain->Card($i, cardNumber:true)->ID();
      if($cardID != "fractal_replication_red" && ClassContains($cardID, "ILLUSIONIST", $currentPlayer) && CardType($cardID) == "AA")
      {
        if($stat == "Hit") ProcessHitEffect($cardID);
        elseif ($stat == "Ability") {
          PlayAbility($cardID, "HAND", 0);
          $modalAbilities = explode("-",  $chainLinkSummary[$i*ChainLinkSummaryPieces()+7]);
          ModalAbilities($currentPlayer, $modalAbilities[0], $modalAbilities[1]);
        }
        else {
          $attack = ModifiedAttackValue($cardID, $currentPlayer, "CC", source:"fractal_replication_red");
          if($attack > $highestAttack) $highestAttack = $attack;
          $block = BlockValue($cardID);
          if($block > $highestBlock) $highestBlock = $block;
          if(!$hasPhantasm) $hasPhantasm = HasPhantasm($cardID);
          if(!$hasGoAgain) $hasGoAgain = HasGoAgain($cardID);
        }
      }
    }
    switch($stat) {
      case "Attack": return $highestAttack;
      case "Block": return $highestBlock;
      case "HasPhantasm": return $hasPhantasm;
      case "GoAgain": return $hasGoAgain;
      default: return 0;
    }
  }

  function ShatterIndices($player, $pendingDamage)
  {
    $character = &GetPlayerCharacter($player);
    $indices = "";
    for($i=0; $i<count($character); $i+=CharacterPieces()) {
      if($character[$i+6] == 1 
      && $character[$i+1] != 0 
      && $character[$i+12] != "DOWN"
      && (CardType($character[$i]) == "E" || DelimStringContains(CardSubType($character[$i]), "Evo")) 
      && (BlockValue($character[$i]) - $character[$i+4]) < $pendingDamage)
      {
        if($indices != "") $indices .= ",";
        $indices .= $i;
      }
    }
    return $indices;
  }

  function KnickKnackIndices($player)
  {
    $deck = &GetDeck($player);
    $indices = "";
    for($i=0; $i<count($deck); $i+=DeckPieces()) {
      if(CardSubType($deck[$i]) == "Item") {
        $name = CardName($deck[$i]);
        if(str_contains($name, "Potion") || str_contains($name, "Talisman") || str_contains($name, "Amulet")) {
          if($indices != "") $indices .= ",";
          $indices .= $i;
        }
      }
    }
    return $indices;
  }

  function CashOutIndices($player)
  {
    $equipIndices = SearchMultizoneFormat(GetEquipmentIndices($player), "MYCHAR");
    $weaponIndices = WeaponIndices($player, $player);
    $itemIndices = SearchMultizoneFormat(SearchItems($player, "A"), "MYITEMS");
    $rv = CombineSearches($equipIndices, $weaponIndices);
    return CombineSearches($rv, $itemIndices);
  }

  function IsAmuletOfEchoesRestricted($from, $player)
  {
    global $CS_NamesOfCardsPlayed;
    if($from == "PLAY") {
      if(GetClassState($player, $CS_NamesOfCardsPlayed) == "-") return true;
      $cardsPlayed = explode(",", GetClassState($player, $CS_NamesOfCardsPlayed));
      $cardCount = count($cardsPlayed);
      for($i=0; $i<$cardCount; ++$i) {
        for($j=0; $j < $cardCount; ++$j) { 
          if($i == $j) continue;
          if(CardNameContains($cardsPlayed[$j], CardName($cardsPlayed[$i]), $player)) {
            return false;
          }
        }
      }
    }
    return true;
  }
