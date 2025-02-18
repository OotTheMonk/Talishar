<?php

  function WTRAbilityCost($cardID)
  {
    switch($cardID)
    {
      case "romping_club": return 2;
      case "bravo_showstopper": case "bravo": return 2;
      case "anothos": return 3;
      case "tectonic_plating": return 1;
      case "helm_of_isens_peak": return 1;
      case "harmonized_kodachi": return 1;
      case "dawnblade": return 1;
      case "braveforge_bracers": return 1;
      default: return 0;
    }
  }

  function WTRAbilityType($cardID, $index=-1, $from="")
  {
    switch($cardID)
    {
      case "romping_club": return "AA";
      case "scabskin_leathers": return "A";
      case "barkbone_strapping": return "I";
      case "bravo_showstopper": case "bravo": return "A";
      case "anothos": return "AA";
      case "tectonic_plating": case "helm_of_isens_peak": return "A";
      case "harmonized_kodachi": return "AA";
      case "breaking_scales": return "AR";
      case "dawnblade": return "AA";
      case "braveforge_bracers": return "A";
      case "fyendals_spring_tunic": return "I";
      case "hope_merchants_hood": return "I";
      case "heartened_cross_strap": return "A";
      case "snapdragon_scalers": return "AR";
      case "goliath_gauntlet": return "A";
      case "crazy_brew_blue": return "A";
      case "energy_potion_blue": 
        if($from == "PLAY") return "I";
        else return "A";
      case "potion_of_strength_blue": case "timesnap_potion_blue": return "A";
      default: return "";
    }
  }

  function WTRAbilityHasGoAgain($cardID)
  {
    switch($cardID)
    {
      case "bravo_showstopper": case "bravo": return true;
      case "tectonic_plating": return true;
      case "braveforge_bracers": return true;
      case "heartened_cross_strap": return true;
      case "goliath_gauntlet": return true;
      case "potion_of_strength_blue": return true;
      default: return false;
    }
  }

  function WTREffectAttackModifier($cardID)
  {
    $idArr = explode("-", $cardID);
    $cardID = $idArr[0];
    switch($cardID)
    {
      case "bloodrush_bellow_yellow": return 2;
      case "barraging_beatdown_red": return NumNonEquipmentDefended() < 2 ? 4 : 0;
      case "barraging_beatdown_yellow": return NumNonEquipmentDefended() < 2 ? 3 : 0;
      case "barraging_beatdown_blue": return NumNonEquipmentDefended() < 2 ? 2 : 0;
      case "awakening_bellow_red": return 3;
      case "awakening_bellow_yellow": return 2;
      case "awakening_bellow_blue": return 1;
      case "primeval_bellow_red": return 5;
      case "primeval_bellow_yellow": return 4;
      case "primeval_bellow_blue": return 3;
      case "debilitate_red": case "debilitate_yellow": case "debilitate_blue": return -2;
      case "emerging_power_red": return 3;
      case "emerging_power_yellow": return 2;
      case "emerging_power_blue": return 1;
      case "lord_of_wind_blue": return (count($idArr) > 1 ? $idArr[1] : 0);
      case "braveforge_bracers": return 1;
      case "warriors_valor_red": return 3;
      case "warriors_valor_yellow": return 2;
      case "warriors_valor_blue": return 1;
      case "sharpen_steel_red": return 3;
      case "sharpen_steel_yellow": return 2;
      case "sharpen_steel_blue": return 1;
      case "driving_blade_red": return 3;
      case "driving_blade_yellow": return 2;
      case "driving_blade_blue": return 1;
      case "natures_path_pilgrimage_red": return 3;
      case "natures_path_pilgrimage_yellow": return 2;
      case "natures_path_pilgrimage_blue": return 1;
      case "goliath_gauntlet": return 2;
      case "enlightened_strike_red": return 2;
      case "last_ditch_effort_blue": return 4;
      case "crazy_brew_blue": return 2;
      case "potion_of_strength_blue": return 2;
      case "nimble_strike_red": case "nimble_strike_yellow": case "nimble_strike_blue": return 1;
      case "wounded_bull_red": case "wounded_bull_yellow": case "wounded_bull_blue": return 1;
      case "pummel_red": return 4;
      case "pummel_yellow": return 3;
      case "pummel_blue": return 2;
      case "razor_reflex_red": return 3;
      case "razor_reflex_yellow": return 2;
      case "razor_reflex_blue": return 1;
      case "nimblism_red": return 3;
      case "nimblism_yellow": return 2;
      case "nimblism_blue": return 1;
      case "sloggism_red": return 6;
      case "sloggism_yellow": return 5;
      case "sloggism_blue": return 4;
      default: return 0;
    }
  }

  function WTRCombatEffectActive($cardID, $attackID)
  {
    global $mainPlayer, $CS_LastDynCost;
    $idArr = explode("-", $cardID);
    $cardID = $idArr[0];
    switch($cardID)
    {
      case "bloodrush_bellow_yellow": return ClassContains($attackID, "BRUTE", $mainPlayer);
      case "barraging_beatdown_red": case "barraging_beatdown_yellow": case "barraging_beatdown_blue": return ClassContains($attackID, "BRUTE", $mainPlayer);
      case "awakening_bellow_red": case "awakening_bellow_yellow": case "awakening_bellow_blue": return CardType($attackID) == "AA" && ClassContains($attackID, "BRUTE", $mainPlayer);
      case "primeval_bellow_red": case "primeval_bellow_yellow": case "primeval_bellow_blue": return ClassContains($attackID, "BRUTE", $mainPlayer);
      case "bravo_showstopper": case "bravo": return CardType($attackID) == "AA" && CardCost($attackID) >= 3;
      case "debilitate_red": case "debilitate_yellow": case "debilitate_blue": return true;
      case "emerging_power_red": case "emerging_power_yellow": case "emerging_power_blue": return CardType($attackID) == "AA" && ClassContains($attackID, "GUARDIAN", $mainPlayer);
      case "lord_of_wind_blue": return true;
      case "braveforge_bracers": return TypeContains($attackID, "W", $mainPlayer);
      case "warriors_valor_red": case "warriors_valor_yellow": case "warriors_valor_blue": return TypeContains($attackID, "W", $mainPlayer);
      case "sharpen_steel_red": case "sharpen_steel_yellow": case "sharpen_steel_blue": return TypeContains($attackID, "W", $mainPlayer);
      case "driving_blade_red": case "driving_blade_yellow": case "driving_blade_blue": return TypeContains($attackID, "W", $mainPlayer);
      case "natures_path_pilgrimage_red": case "natures_path_pilgrimage_yellow": case "natures_path_pilgrimage_blue": return TypeContains($attackID, "W", $mainPlayer);
      case "goliath_gauntlet": return CardType($attackID) == "AA" && (CardCost($attackID) >= 2 || GetClassState($mainPlayer, $CS_LastDynCost) >= 2);
      case "snapdragon_scalers": return true;
      case "enlightened_strike_red": return true;
      case "last_ditch_effort_blue": return true;
      case "crazy_brew_blue": return true;
      case "potion_of_strength_blue": return true;
      case "nimble_strike_red": case "nimble_strike_yellow": case "nimble_strike_blue": return true;
      case "regurgitating_slog_red": case "regurgitating_slog_yellow": case "regurgitating_slog_blue": return true;
      case "wounded_bull_red": case "wounded_bull_yellow": case "wounded_bull_blue": return true;
      case "pummel_red": case "pummel_yellow": case "pummel_blue": return true;
      case "razor_reflex_red": case "razor_reflex_yellow": case "razor_reflex_blue": return true;
      case "nimblism_red": case "nimblism_yellow": case "nimblism_blue": return CardType($attackID) == "AA" && CardCost($attackID) <= 1;
      case "sloggism_red": case "sloggism_yellow": case "sloggism_blue": return CardType($attackID) == "AA" && CardCost($attackID) >= 2;
      default: return false;
    }
  }

  function WTRPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
  {
    global $mainPlayer, $currentPlayer, $defPlayer, $CS_DamagePrevention;
    $rv = "";
    switch($cardID) {
      case "blessing_of_deliverance_red": case "blessing_of_deliverance_yellow": case "blessing_of_deliverance_blue": if(SearchCount(SearchPitch($currentPlayer, minCost:3)) > 0) Draw($currentPlayer); return "";
      case "scabskin_leathers":
        $roll = GetDieRoll($currentPlayer);
        GainActionPoints(intval($roll/2), $currentPlayer);
        return "Rolled $roll and gained " . intval($roll/2) . " action points";
      case "barkbone_strapping":
        $roll = GetDieRoll($currentPlayer);
        GainResources($currentPlayer, intval($roll/2));
        return "Rolled $roll and gained " . intval($roll/2) . " resources";
      case "alpha_rampage_red":
        Intimidate();
        return "";
      case "bloodrush_bellow_yellow":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        if(ModifiedAttackValue($additionalCosts, $currentPlayer, "HAND", source:$cardID) >= 6) {
          AddCurrentTurnEffect($cardID."-GOAGAIN", $currentPlayer);
          Draw($currentPlayer);
          Draw($currentPlayer);
          ResolveGoAgain($cardID, $currentPlayer, $from);
          $rv = "Draws 2 cards and gains go again";
        }
        return $rv;
      case "reckless_swing_blue":
        if(IsAllyAttacking()) {
          return "<span style='color:red;'>No damage is dealt because there is no attacking hero when allies attack.</span>";
        }
        else if(ModifiedAttackValue($additionalCosts, $currentPlayer, "HAND", source:$cardID) >= 6) {
          WriteLog(Cardlink($cardID, $cardID) . " deals 2 damage"); DamageTrigger($mainPlayer, 2, "DAMAGE", $cardID);
        }
        return "";
      case "sand_sketched_plan_blue":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "DECK");
        AddDecisionQueue("MAYCHOOSEDECK", $currentPlayer, "<-", 1);
        AddDecisionQueue("ADDHAND", $currentPlayer, "-", 1);
        AddDecisionQueue("SPECIFICCARD", $currentPlayer, "SANDSKETCH");
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
        return "";
      case "bone_head_barrier_yellow":
        $roll = GetDieRoll($currentPlayer);
        IncrementClassState($currentPlayer, $CS_DamagePrevention, $roll);
        return "Prevents the next $roll damage that will be dealt to you this turn";
      case "breakneck_battery_red": case "breakneck_battery_yellow": case "breakneck_battery_blue":
        if(ModifiedAttackValue($additionalCosts, $currentPlayer, "HAND", source:$cardID) >= 6) {
          GiveAttackGoAgain();
          $rv = "Discarded a 6 power card and gains go again.";
        }
        return $rv;
      case "savage_feast_red": case "savage_feast_yellow": case "savage_feast_blue":
        if(ModifiedAttackValue($additionalCosts, $currentPlayer, "HAND", source:$cardID) >= 6) Draw($currentPlayer);
        return "";
      case "barraging_beatdown_red": case "barraging_beatdown_yellow": case "barraging_beatdown_blue":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        Intimidate();
        return "";
      case "pack_hunt_red": case "pack_hunt_yellow": case "pack_hunt_blue":
        Intimidate();
        return "";
      case "smash_instinct_red": case "smash_instinct_yellow": case "smash_instinct_blue":
        Intimidate();
        return "";
      case "awakening_bellow_red": case "awakening_bellow_yellow": case "awakening_bellow_blue":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        Intimidate();
        return "";
      case "primeval_bellow_red": case "primeval_bellow_yellow": case "primeval_bellow_blue":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        return "";
      //Guardian
      case "bravo_showstopper": case "bravo":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        return "";
      case "tectonic_plating":
        PlayAura("seismic_surge", $mainPlayer);
        return "";
      case "helm_of_isens_peak":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        return "";
      case "show_time_blue":
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDECK:type=AA;class=GUARDIAN");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIADDHAND", $currentPlayer, "-", 1);
        AddDecisionQueue("REVEALCARDS", $currentPlayer, "-", 1);
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
        return "";
      //Ninja
      case "ancestral_empowerment_red":
        Draw($currentPlayer);
        return "";
      case "flic_flak_red": case "flic_flak_yellow": case "flic_flak_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      //Warrior
      case "braveforge_bracers":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "glint_the_quicksilver_blue":
        GiveAttackGoAgain();
        if(RepriseActive()) Draw($currentPlayer);
        return "";
      case "steelblade_supremacy_red": case "ironsong_determination_yellow":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        AddDecisionQueue("FINDINDICES", $currentPlayer, "WEAPON");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose_target_weapon");
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("ADDMZBUFF", $currentPlayer, $cardID, 1);
        return "";
      case "rout_red":
        $options = GetChainLinkCards($defPlayer, "", "E,C", exclCardSubTypes:"Evo");
        if(RepriseActive() && $options != "") {
          AddDecisionQueue("MAYCHOOSECOMBATCHAIN", $mainPlayer, $options);
          AddDecisionQueue("ADDHANDOWNER", $defPlayer, "-", 1);
          AddDecisionQueue("REMOVECOMBATCHAIN", $mainPlayer, "-", 1);
        }
        return "";
      case "singing_steelblade_yellow":
        if(RepriseActive() && SearchDeck($currentPlayer, "AR") != "") {
          $ARs = SearchDeck($currentPlayer, "AR");
          AddDecisionQueue("MAYCHOOSEDECK", $currentPlayer, $ARs);
          AddDecisionQueue("MULTIBANISH", $currentPlayer, "DECK,TCL", 1);
          AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
          AddDecisionQueue("WRITELOG", $currentPlayer, "<0> was banished.", 1);
          AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
        }
        return "";
      case "steelblade_shunt_red": case "steelblade_shunt_yellow": case "steelblade_shunt_blue":
        if(IsWeaponAttack()) {
          DamageTrigger($mainPlayer, 1, "DAMAGE", $cardID);
          $rv = "Did 1 damage to the attacking hero";
        }
        return $rv;
      case "warriors_valor_red": case "warriors_valor_yellow": case "warriors_valor_blue":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        return "";
      case "biting_blade_red": case "biting_blade_yellow": case "biting_blade_blue":
        if(RepriseActive()) { ApplyEffectToEachWeapon($cardID); $rv = "Gives weapons you control +1 for the rest of the turn"; }
        return $rv;
      case "stroke_of_foresight_red": case "stroke_of_foresight_yellow": case "stroke_of_foresight_blue":
        if(RepriseActive()) {
          Draw($currentPlayer);
          $hand = &GetHand($mainPlayer);
          if(count($hand) > 0) AddDecisionQueue("HANDTOPBOTTOM", $mainPlayer, "");
        }
        return "";
      case "sharpen_steel_red": case "sharpen_steel_yellow": case "sharpen_steel_blue":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        return "";
      case "driving_blade_red": case "driving_blade_yellow": case "driving_blade_blue":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        return "";
      case "natures_path_pilgrimage_red": case "natures_path_pilgrimage_yellow": case "natures_path_pilgrimage_blue":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        return "";
      case "fyendals_spring_tunic":
        GainResources($currentPlayer, 1);
        return "";
      case "hope_merchants_hood":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "MULTIHAND");
        AddDecisionQueue("MULTICHOOSEHAND", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIADDDECK", $currentPlayer, "-", 1);
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
        AddDecisionQueue("SPECIFICCARD", $currentPlayer, "HOPEMERCHANTHOOD", 1);
        return "";
      case "heartened_cross_strap":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        return "";
      case "goliath_gauntlet":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        return "";
      case "snapdragon_scalers":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        return "";
      case "enlightened_strike_red":
        PrependDecisionQueue("MODAL", $currentPlayer, "ESTRIKE", 1);
        PrependDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts, 1);
        return "";
      case "tome_of_fyendal_yellow":
        Draw($currentPlayer);
        Draw($currentPlayer);
        if($from == "ARS") { $hand = &GetHand($currentPlayer); GainHealth(count($hand), $currentPlayer); }
        return "";
      case "last_ditch_effort_blue":
        if(count(GetDeck($currentPlayer)) == 0) {
          GiveAttackGoAgain();
          AddCurrentTurnEffect($cardID, $currentPlayer);
          $rv = "Gains go again and +4";
        }
        return $rv;
      case "crazy_brew_blue":
        if($from == "PLAY") {
          $roll = GetDieRoll($currentPlayer);
          $rv = "Crazy Brew rolled " . $roll;
          if($roll <= 2) {
            LoseHealth(2, $currentPlayer);
            GainActionPoints(1, $currentPlayer);
            $rv .= " and lost you 2 life.";
          }
          else if($roll <= 4) {
            GainHealth(2, $currentPlayer);
            GainActionPoints(1, $currentPlayer);
          } else {
            $resources = &GetResources($currentPlayer);
            AddCurrentTurnEffect($cardID, $currentPlayer);
            GainResources($currentPlayer, 2);
            GainActionPoints(2, $currentPlayer);
            $rv .= " and gained 2 action points, resources, and power.";
          }
        }
        return $rv;
      case "remembrance_yellow":
        $actions = SearchDiscard($currentPlayer, "A");
        $attackActions = SearchDiscard($currentPlayer, "AA");
        if($actions == "") $actions = $attackActions;
        else if($attackActions != "") $actions = $actions . "," . $attackActions;
        if($actions == "") return "";
        AddDecisionQueue("MULTICHOOSEDISCARD", $currentPlayer, "3-" . $actions);
        AddDecisionQueue("SPECIFICCARD", $currentPlayer, "REMEMBRANCE", 1);
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
        return "";
      case "energy_potion_blue":
        if($from == "PLAY") GainResources($currentPlayer, 2);
        return "";
      case "potion_of_strength_blue":
        if($from == "PLAY") AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "timesnap_potion_blue":
        if($from == "PLAY") GainActionPoints(2, $currentPlayer);
        return "";
      case "sigil_of_solace_red": GainHealth(3, $currentPlayer); return "";
      case "sigil_of_solace_yellow": GainHealth(2, $currentPlayer); return "";
      case "sigil_of_solace_blue": GainHealth(1, $currentPlayer); return "";
      case "flock_of_the_feather_walkers_red": case "flock_of_the_feather_walkers_yellow": case "flock_of_the_feather_walkers_blue":
        PlayAura("quicken", $currentPlayer);
        return "";
      case "scar_for_a_scar_red": case "scar_for_a_scar_yellow": case "scar_for_a_scar_blue":
        if(PlayerHasLessHealth($mainPlayer)) { GiveAttackGoAgain(); $rv = "Gains go again"; }
        return $rv;
      case "scour_the_battlescape_red": case "scour_the_battlescape_yellow": case "scour_the_battlescape_blue":
        BottomDeck($currentPlayer, true, shouldDraw:true);
        if($from == "ARS") { GiveAttackGoAgain(); $rv = "Gains go again"; }
        return $rv;
      case "wounded_bull_red": case "wounded_bull_yellow": case "wounded_bull_blue":
        if(PlayerHasLessHealth($mainPlayer)) { AddCurrentTurnEffect($cardID, $mainPlayer); $rv = "Gains +1 attack"; }
        return $rv;
      case "pummel_red": case "pummel_yellow": case "pummel_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "razor_reflex_red": case "razor_reflex_yellow": case "razor_reflex_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "sink_below_red": case "sink_below_yellow": case "sink_below_blue":
        BottomDeck($currentPlayer, true, shouldDraw:true);
        return "";
      case "nimblism_red": case "nimblism_yellow": case "nimblism_blue":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        return "";
      case "sloggism_red": case "sloggism_yellow": case "sloggism_blue":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        return "";
      default: return "";
    }
  }

  function WTRHitEffect($cardID)
  {
    global $CS_HitsWDawnblade, $combatChainState, $CCS_WeaponIndex;
    global $mainPlayer, $defPlayer, $CCS_DamageDealt;
    switch($cardID)
    {
      case "mugenshi_release_yellow":
        if(ComboActive())
        {
          AddDecisionQueue("FINDINDICES", $mainPlayer, "mugenshi_release_yellow");
          AddDecisionQueue("MULTICHOOSEDECK", $mainPlayer, "<-", 1);
          AddDecisionQueue("MULTIREMOVEDECK", $mainPlayer, "-", 1);
          AddDecisionQueue("MULTIADDHAND", $mainPlayer, "-", 1);
          AddDecisionQueue("SHUFFLEDECK", $mainPlayer, "-");
        }
        break;
      case "hurricane_technique_yellow":
        AddDecisionQueue("PASSPARAMETER", $mainPlayer, $cardID);
        if(ComboActive()) {
          AddDecisionQueue("ADDHAND", $mainPlayer, "-");
        }
        break;
      case "pounding_gale_red":
        if(IsHeroAttackTarget() && ComboActive()) {
          LoseHealth($combatChainState[$CCS_DamageDealt], $defPlayer);
        }
        break;
      case "whelming_gustwave_red": case "whelming_gustwave_yellow": case "whelming_gustwave_blue": if(ComboActive()) { Draw($mainPlayer); } break;
      case "dawnblade":
        if(GetClassState($mainPlayer, $CS_HitsWDawnblade) == 1) {
          $mainCharacter = &GetPlayerCharacter($mainPlayer);
          $index = FindCharacterIndex($mainPlayer, $cardID);
          ++$mainCharacter[$index+3];
        }
        IncrementClassState($mainPlayer, $CS_HitsWDawnblade, 1);
      break;
      case "snatch_red": case "snatch_yellow": case "snatch_blue": Draw($mainPlayer); break;
      default: break;
    }
  }

  function BlessingOfDeliveranceDestroy($amount)
  {
    global $mainPlayer;
    if(!CanRevealCards($mainPlayer)) return "Blessing of Deliverance cannot reveal cards.";
    $deck = GetDeck($mainPlayer);
    $lifegain = 0;
    $cards = "";
    for($i=0; $i<$amount; ++$i)
    {
      if(count($deck) > $i)
      {
        $cards .= $deck[$i] . ($i < 2 ? "," : "");
        if(CardCost($deck[$i]) >= 3) ++$lifegain;
      }
    }
    RevealCards($cards, $mainPlayer);
    GainHealth($lifegain, $mainPlayer);
    return "";
  }

  function KatsuHit($context="")
  {
    global $mainPlayer;
    $hand = &GetHand($mainPlayer);
    $char = &GetPlayerCharacter($mainPlayer);
    if($context == "") $context = "to_use_Katsu's_ability";
    if(count($hand) > 0)
    {
      AddDecisionQueue("YESNO", $mainPlayer, $context);
      AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "MYHAND:maxCost=0;minCost=0", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZDISCARD", $mainPlayer, "HAND,".$mainPlayer, 1);
      AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "MYDECK:comboOnly=true", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZADDZONE", $mainPlayer, "MYBANISH,DECK,TT", 1);
      AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      AddDecisionQueue("SHUFFLEDECK", $mainPlayer, "-", 1);
      if($context == "to_use_Katsu's_ability")AddDecisionQueue("LOGPLAYCARDSTATS", $mainPlayer, $char[0].",HAND,KATSUDISCARD", 1);
    }
  }

  function LordOfWindIndices($player)
  {
    $array = [];
    $indices = SearchDiscardForCard($player, "surging_strike_red", "surging_strike_yellow", "surging_strike_blue");
    if($indices != "") array_push($array, $indices);
    $indices = SearchDiscardForCard($player, "whelming_gustwave_red", "whelming_gustwave_yellow", "whelming_gustwave_blue");
    if($indices != "") array_push($array, $indices);
    $indices = SearchDiscardForCard($player, "mugenshi_release_yellow");
    if($indices != "") array_push($array, $indices);
    return implode(",", $array);
  }

  function NaturesPathPilgrimageHit()
  {
    global $mainPlayer;
    $deck = new Deck($mainPlayer);
    if(!ArsenalFull($mainPlayer) && !$deck->Empty()) {
      $type = CardType($deck->Top());
      if($deck->Reveal() && (DelimStringContains($type, "A") || $type == "AA")) {
        AddArsenal($deck->Top(remove:true), $mainPlayer, "DECK", "DOWN");
      }
    }
  }


  function HasCrush($cardID)
  {
    switch($cardID) {
      case "crippling_crush_red": case "spinal_crush_red": case "cranial_crush_blue": case "buckling_blow_red": case "buckling_blow_yellow": case "buckling_blow_blue":
      case "cartilage_crush_red": case "cartilage_crush_yellow": case "cartilage_crush_blue": case "crush_confidence_red": case "crush_confidence_yellow": case "crush_confidence_blue":
      case "debilitate_red": case "debilitate_yellow": case "debilitate_blue": case "disable_blue": case "disable_yellow": case "disable_red":
      case "mangle_red": case "righteous_cleansing_yellow": case "crush_the_weak_red": case "crush_the_weak_yellow": case "crush_the_weak_blue": case "chokeslam_red":
      case "chokeslam_yellow": case "chokeslam_blue": case "boulder_drop_yellow": case "boulder_drop_blue":
      case "star_struck_yellow":
        return true;
      default:
        return false;
    }
  }

  function Mangle()
  {
    global $mainPlayer;
    AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRCHAR:type=E;hasNegCounters=true");
    AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
    AddDecisionQueue("MZDESTROY", $mainPlayer, "-", 1);
  }

  function ProcessCrushEffect($cardID)
  {
    global $mainPlayer, $defPlayer, $defCharacter, $CombatChain, $combatChainState, $CCS_DamageDealt, $layers;
    if(!IsHeroAttackTarget()) return;
    if(CardType($CombatChain->AttackCard()->ID()) == "AA" && SearchCurrentTurnEffects("tarpit_trap_yellow", $mainPlayer, count($layers) <= LayerPieces())) {
      WriteLog("Hit effect prevented by " . CardLink("tarpit_trap_yellow", "tarpit_trap_yellow"));
      return true;
    }
    switch($cardID) {
      case "crippling_crush_red":
        DiscardRandom($defPlayer, $cardID, $mainPlayer);
        DiscardRandom($defPlayer, $cardID, $mainPlayer);
        break;
      case "spinal_crush_red":
        AddNextTurnEffect($cardID, $defPlayer);
        break;
      case "cranial_crush_blue":
        AddNextTurnEffect($cardID, $defPlayer);
        break;
      case "disable_red": case "disable_yellow": case "disable_blue":
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRARS", 1);
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want to put on the bottom of the deck", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZADDZONE", $mainPlayer, "THEIRBOTDECK", 1);
        AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
        break;
      case "buckling_blow_red": case "buckling_blow_yellow": case "buckling_blow_blue":
        AddDecisionQueue("FINDINDICES", $defPlayer, "EQUIP");
        AddDecisionQueue("CHOOSETHEIRCHARACTER", $mainPlayer, "<-", 1);
        AddDecisionQueue("MODDEFCOUNTER", $defPlayer, "-1", 1);
        break;
      case "cartilage_crush_red": case "cartilage_crush_yellow": case "cartilage_crush_blue":
        AddNextTurnEffect($cardID, $defPlayer);
        break;
      case "crush_confidence_red": case "crush_confidence_yellow": case "crush_confidence_blue":
        if(IsHeroAttackTarget()) {
          $char = &GetPlayerCharacter($defPlayer);
          $char[1] = 3;
          AddNextTurnEffect($cardID, $defPlayer);
        }
        break;
      case "debilitate_red": case "debilitate_yellow": case "debilitate_blue":
        AddNextTurnEffect($cardID, $defPlayer);
        break;
      case "mangle_red":
        Mangle();
        break;
      case "righteous_cleansing_yellow":
        AddDecisionQueue("FINDINDICES", $defPlayer, "DECKTOPXINDICES,5");
        AddDecisionQueue("SETDQVAR", $mainPlayer, "0");
        AddDecisionQueue("COUNTPARAM", $defPlayer, "<-", 1);
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card(s) to banish", 1);
        AddDecisionQueue("MULTICHOOSETHEIRDECK", $mainPlayer, "<-", 1, 1);
        AddDecisionQueue("VALIDATEALLSAMENAME", $defPlayer, "DECK", 1);
        AddDecisionQueue("MULTIREMOVEDECK", $defPlayer, "-", 1);
        AddDecisionQueue("MULTIBANISH", $defPlayer, "DECK,-", 1);
        AddDecisionQueue("SPECIFICCARD", $mainPlayer, "RIGHTEOUSCLEANSING", 1);
        break;
      case "crush_the_weak_red": case "crush_the_weak_yellow": case "crush_the_weak_blue":
        AddNextTurnEffect("crush_the_weak_red", $defPlayer);
        break;
      case "chokeslam_red": case "chokeslam_yellow": case "chokeslam_blue":
        AddNextTurnEffect("chokeslam_red", $defPlayer);
        break;
      case "star_struck_yellow":
        $damageDone = $combatChainState[$CCS_DamageDealt];
        AddNextTurnEffect("star_struck_yellow," . $damageDone, $defPlayer);
        break;
      case "boulder_drop_yellow": case "boulder_drop_blue":
        MZMoveCard($defPlayer, "MYHAND", "MYTOPDECK", silent:true);
        break;
      default: return;
    }
  }
