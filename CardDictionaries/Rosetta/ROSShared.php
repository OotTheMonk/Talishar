<?php

function ROSAbilityType($cardID): string
{
  return match ($cardID) {
    "aurora_shooting_star", "aurora", "oscilio_constella_intelligence", "oscilio", "volzar_the_lightning_rod", "sanctuary_of_aria",
    "well_grounded", "lightning_greaves", "twinkle_toes", "inklined_cloak", "hood_of_second_thoughts", "bruised_leather",
    "four_finger_gloves", "calming_cloak", "calming_gesture", "aether_bindings_of_the_third_age" => "I",
    "staff_of_verdant_shoots", "bloodtorn_bodice", "runehold_release", "hold_focus" => "A",
    "rotwood_reaper", "star_fall" => "AA",
    "adaptive_dissolver" => "A",
    default => ""
  };
}

function ROSAbilityCost($cardID): int
{
  global $currentPlayer;
  return match ($cardID) {
    "staff_of_verdant_shoots" => 3,
    "rotwood_reaper", "aurora_shooting_star", "aurora", "sanctuary_of_aria" => 2,
    "star_fall", "lightning_greaves", "calming_cloak", "calming_gesture" => 1,
    "volzar_the_lightning_rod" => HasAuraWithSigilInName($currentPlayer) ? 0 : 1,
    default => 0
  };
}

function ROSAbilityHasGoAgain($cardID): bool
{
  return match ($cardID) {
    "staff_of_verdant_shoots", "bloodtorn_bodice", "runehold_release", "hold_focus" => true,
    default => false,
  };
}

function ROSEffectAttackModifier($cardID): int
{
  return match ($cardID) {
    "strong_yield_blue", "electrostatic_discharge_blue", "condemn_to_slaughter_blue" => 1,
    "strong_yield_yellow", "electrostatic_discharge_yellow", "condemn_to_slaughter_yellow" => 2,
    "strong_yield_red", "electrostatic_discharge_red", "condemn_to_slaughter_red", "unsheathed_red" => 3,
    default => 0,
  };
}

function ROSCombatEffectActive($cardID, $attackID): bool
{
  global $mainPlayer, $CS_ActionsPlayed;
  $actionsPlayed = explode(",", GetClassState($mainPlayer, $CS_ActionsPlayed));
  $numActions = count($actionsPlayed);    
  return match ($cardID) {
    "arc_lightning_yellow-GOAGAIN" => TypeContains($attackID, "AA", $mainPlayer) || TypeContains($attackID, "A", $mainPlayer), //Arc Lightning giving next action go again.
    "strong_yield_red", "strong_yield_yellow", "strong_yield_blue", "burn_up__shock_red", "gone_in_a_flash_red" => true,
    "current_funnel_blue" => $actionsPlayed[$numActions-2] == "current_funnel_blue" && $actionsPlayed[$numActions-1] != "current_funnel_blue" && (TypeContains($attackID, "AA", $mainPlayer) || TypeContains($attackID, "AA", $mainPlayer)),
    "eclectic_magnetism_red" => true,
    "electrostatic_discharge_red", "electrostatic_discharge_yellow", "electrostatic_discharge_blue" => CardType($attackID) == "AA" && CardCost($attackID) <= 1,
    "machinations_of_dominion_blue" => CardType($attackID) == "AA" && ClassContains($attackID, "RUNEBLADE", $mainPlayer),
    "condemn_to_slaughter_red", "condemn_to_slaughter_yellow", "condemn_to_slaughter_blue", "succumb_to_temptation_yellow" => ClassContains($attackID, "RUNEBLADE", $mainPlayer),
    "unsheathed_red" => CardSubType($attackID) == "Sword", // this conditional should remove both the buff and 2x attack bonus go again.
    default => false,
  };
}

function ROSPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = ""): string
{
  global $currentPlayer, $CS_DamagePrevention, $CS_NumLightningPlayed, $CCS_NextInstantBouncesAura, $combatChainState, $CS_ArcaneDamageTaken;
  global $mainPlayer, $CCS_EclecticMag, $CS_DamageDealt, $CS_ArcaneDamageDealt;
  global $combatChainState, $CS_ActionsPlayed;
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  switch ($cardID) {
    case "germinate_blue":
      $xVal = $resourcesPaid / 2;
      for ($i = 0; $i <= $xVal; $i++) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which aura to create:");
        AddDecisionQueue("CHOOSECARD", $currentPlayer, "runechant" . "," . "embodiment_of_earth");
        AddDecisionQueue("PUTPLAY", $currentPlayer, "-", 1);
      }
      AddDecisionQueue("GAINLIFE", $currentPlayer, $xVal + 1);
      return "";
    case "aurora_shooting_star":
    case "aurora":
      PlayAura("embodiment_of_lightning", $currentPlayer);
      return "";
    case "arc_lightning_yellow":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      AddCurrentTurnEffect($cardID . "-GOAGAIN", $currentPlayer);
      return "";
    case "staff_of_verdant_shoots":
      AddCurrentTurnEffect($cardID . "-AMP", $currentPlayer, from: "ABILITY");
      if (SearchCardList($additionalCosts, $currentPlayer, talent: "EARTH") != "") {
        AddCurrentTurnEffect($cardID, $currentPlayer, from: "ABILITY");
      }
      return CardLink($cardID, $cardID) . " is amping 1";
    case "heartbeat_of_candlehold_blue":
      GainHealth(1, $currentPlayer);
      GainHealth(1, $currentPlayer);
      GainHealth(1, $currentPlayer);
      return "";
    case "oscilio_constella_intelligence":
    case "oscilio":
      Draw($currentPlayer);
      return "";
    case "volzar_the_lightning_rod":
      $ampAmount = GetClassState($currentPlayer, $CS_NumLightningPlayed);
      if($ampAmount > 0) {
        AddCurrentTurnEffect($cardID . "," . $ampAmount, $currentPlayer, "ABILITY");
      }
      return CardLink($cardID, $cardID) . " is amping " . $ampAmount .".";
    case "sanctuary_of_aria":
      if($from == "HAND") {
        PutItemIntoPlayForPlayer("sanctuary_of_aria", $otherPlayer); //Work around for player to put it in play themselves (Mostly for Blitz LSS precons)
      }
      else{
        $params = explode("-", $target);
        if(str_contains($params[0], "AURAS")) {
          $index = SearchAurasForUniqueID($params[1], $otherPlayer);
          $target = "THEIRAURAS-" . $index;
        }
        if(GetMZCard($currentPlayer, $target) == "MELD"){
          $target = $params[0] . "-" . $params[1]+2;
        }
        if($target != "-") AddCurrentTurnEffect($cardID, $currentPlayer, $from, GetMZCard($currentPlayer, $target));
        if(!SearchCurrentTurnEffects($cardID . "-1", $currentPlayer)) AddCurrentTurnEffect($cardID . "-1", $currentPlayer);  
      }
      return "";
    case "well_grounded":
      IncrementClassState($currentPlayer, $CS_DamagePrevention, 2);
      return "";
    case "felling_of_the_crown_red":
    case "plow_under_yellow":
    case "rootbound_carapace_red":
    case "rootbound_carapace_yellow":
    case "rootbound_carapace_blue":
    case "blossoming_decay_red":
    case "blossoming_decay_yellow":
    case "blossoming_decay_blue":
    case "cadaverous_tilling_red":
    case "cadaverous_tilling_yellow":
    case "cadaverous_tilling_blue":
      $totalBanishes = 3;
      $earthBanishes = 2; 
      // Only perform the action if we have the minimum # of cards that meet the requirement for total banishes.
      $countInDiscard = SearchCount(
        SearchRemoveDuplicates(
          CombineSearches(
            SearchDiscard($currentPlayer, talent: "EARTH"),
            CombineSearches(
              SearchDiscard($currentPlayer, "A"),
              SearchDiscard($currentPlayer
              , "AA"))
            )
          )
        );
      // Must have the minimum # of earth cards too.
      $earthCountInDiscard = SearchCount(SearchDiscard($currentPlayer, talent: "EARTH"));
      // This is a MAY ability.
      if($countInDiscard >= $totalBanishes && $earthCountInDiscard >= $earthBanishes) {
        AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_Decompose");
        AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDTRIGGER", $currentPlayer, $cardID, 1);
      }
      return "";
    case "channel_the_millennium_tree_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "seeds_of_tomorrow_blue":
      IncrementClassState($currentPlayer, $CS_DamagePrevention, 5);
      return CardLink($cardID, $cardID) . " is preventing the next 5 damage.";
    case "summers_fall_red":
    case "summers_fall_yellow":
    case "summers_fall_blue":
      $totalBanishes = 3;
      $earthBanishes = 2; 
      // Only perform the action if we have the minimum # of cards that meet the requirement for total banishes.
      $countInDiscard = SearchCount(
        SearchRemoveDuplicates(
          CombineSearches(
            SearchDiscard($currentPlayer, talent: "EARTH"),
            CombineSearches(
              SearchDiscard($currentPlayer, "A"),
              SearchDiscard($currentPlayer
              , "AA"))
            )
          )
        );
      // Must have the minimum # of earth cards too.
      $earthCountInDiscard = SearchCount(SearchDiscard($currentPlayer, talent: "EARTH"));
      // This is a MAY ability.
      if($countInDiscard >= $totalBanishes && $earthCountInDiscard >= $earthBanishes) {
        AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_Decompose");
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
        AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRAURAS&MYAURAS", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose target aura", 1);
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("SUMMERSFALL", $currentPlayer, $cardID.",NONE");
      }
      return "";
    case "fruits_of_the_forest_red":
    case "fruits_of_the_forest_yellow":
    case "fruits_of_the_forest_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I") {
        GainHealth(2, $currentPlayer);
      }
      return "";
    case "fertile_ground_red": //fertile ground red
      $earthCountInBanish = SearchCount(SearchBanish($currentPlayer, talent: "EARTH"));
      WriteLog($earthCountInBanish . " earth cards in banish");
      if ($earthCountInBanish >= 4) {
        GainHealth(5, $currentPlayer);
      } else {
        GainHealth(2, $currentPlayer);
      }
      return "";
    case "fertile_ground_yellow": //fertile ground yellow
      $earthCountInBanish = SearchCount(SearchBanish($currentPlayer, talent: "EARTH"));
      if ($earthCountInBanish >= 4) {
        GainHealth(4, $currentPlayer);
      } else {
        GainHealth(2, $currentPlayer);
      }
      return "";
    case "fertile_ground_blue": //fertile ground blue
      $earthCountInBanish = SearchCount(SearchBanish($currentPlayer, talent: "EARTH"));
      if ($earthCountInBanish >= 4) {
        GainHealth(3, $currentPlayer);
      } else {
        GainHealth(2, $currentPlayer);
      }
      return "";
    case "lightning_greaves":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "twinkle_toes":
      IncrementClassState($currentPlayer, $CS_DamagePrevention, 2);
      return "";
    case "current_funnel_blue":
      $actionsPlayed = explode(",", GetClassState($currentPlayer, $CS_ActionsPlayed));
      $numActions = count($actionsPlayed);    
      if (count($actionsPlayed) > 1 && TalentContains($actionsPlayed[$numActions-2], "LIGHTNING", $currentPlayer)) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
      }
      return "";
    case "eclectic_magnetism_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      $combatChainState[$CCS_EclecticMag] = 1;
      return "";
    case "gone_in_a_flash_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "high_voltage_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      Writelog(CardLink($cardID, $cardID) . " is amping 1");
      return "";
    case "blast_to_oblivion_red":
    case "blast_to_oblivion_yellow":
    case "blast_to_oblivion_blue":
      $combatChainState[$CCS_NextInstantBouncesAura] = 1;
      return "";
    case "electromagnetic_somersault_red":
    case "electromagnetic_somersault_yellow":
    case "electromagnetic_somersault_blue":
      $minCost = match ($cardID) {
        "electromagnetic_somersault_red" => 0,
        "electromagnetic_somersault_yellow" => 1,
        "electromagnetic_somersault_blue" => 2
      };
      $options = SearchCombatChainLink($currentPlayer, "AA", minCost: $minCost);
      if($options != "") {
        $max = count(explode(",", $options));
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "COMBATCHAINLINK:type=AA;minCost=".$minCost);
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
        AddDecisionQueue("MZSETDQVAR", $currentPlayer, "1", 1);
        AddDecisionQueue("WRITELOGCARDLINK", $currentPlayer, "{1} was chosen.", 1);
        AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID . "-{1}", 1);
        if($max > 1) {
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "COMBATCHAINLINK:type=AA;minCost=".$minCost);
          AddDecisionQueue("REMOVEPREVIOUSCHOICES", $currentPlayer, "{0}", 1);
          AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("MZSETDQVAR", $currentPlayer, "1", 1);
          AddDecisionQueue("WRITELOGCARDLINK", $currentPlayer, "{1} was chosen.", 1);
          AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID . "-{1}", 1);  
        }
      }
      return "";
    case "second_strike_red":
    case "second_strike_yellow":
    case "second_strike_blue":
      if (GetClassState($currentPlayer, $CS_DamageDealt) + GetClassState($currentPlayer, $CS_ArcaneDamageDealt) > 0) GiveAttackGoAgain();
    case "trip_the_light_fantastic_red":
    case "trip_the_light_fantastic_yellow":
    case "trip_the_light_fantastic_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I") {
        IncrementClassState($currentPlayer, $CS_DamagePrevention, 2);
        return CardLink($cardID, $cardID) . " is preventing the next 2 damage.";
      }
      return "";
    case "electrostatic_discharge_red":
    case "electrostatic_discharge_yellow":
    case "electrostatic_discharge_blue":
      AddCurrentTurnEffectNextAttack($cardID, $currentPlayer);
      return "";
    case "bloodtorn_bodice":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYAURAS");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
      AddDecisionQueue("GAINRESOURCES", $currentPlayer, "1", 1);
      return "";
    case "machinations_of_dominion_blue":
    case "succumb_to_temptation_yellow":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "haunting_rendition_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      IncrementClassState($currentPlayer, $CS_DamagePrevention, 2);
      return "";
    case "splintering_deadwood_red":
    case "splintering_deadwood_yellow":
    case "splintering_deadwood_blue":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYAURAS");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
      AddDecisionQueue("PLAYAURA", $currentPlayer, "runechant", 1);
      return "";
    case "condemn_to_slaughter_red":
    case "condemn_to_slaughter_yellow":
    case "condemn_to_slaughter_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);

      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYAURAS");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
      AddDecisionQueue("MULTIZONEINDICES", $otherPlayer, "MYAURAS", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $otherPlayer, "<-", 1);
      AddDecisionQueue("MZDESTROY", $otherPlayer, "-", 1);
      return "";
    case "hocus_pocus_red":
    case "hocus_pocus_yellow":
    case "hocus_pocus_blue":
    case "runehold_release":
      PlayAura("runechant", $currentPlayer);
      return "";
    case "deadwood_dirge_red":
    case "deadwood_dirge_yellow":
    case "deadwood_dirge_blue":
      $numRunechants = match ($cardID) {
        "deadwood_dirge_red" => 3,
        "deadwood_dirge_yellow" => 2,
        "deadwood_dirge_blue" => 1
      };
      if($currentPlayer == $mainPlayer){
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYAURAS");
      }
      else {
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYAURAS&COMBATCHAINLINK:subtype=Aura");
      }
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
      AddDecisionQueue("PLAYAURA", $currentPlayer, "runechant-" . $numRunechants, 1);
      return "";
    case "aether_bindings_of_the_third_age":
      AddCurrentTurnEffect("aether_bindings_of_the_third_age", $currentPlayer);
      return "";
    case "inklined_cloak":
      GainResources($currentPlayer, 1);
      return "";
    case "hold_focus":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "mental_block_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      IncrementClassState($currentPlayer, $CS_DamagePrevention, 2);
      return "";
    case "chorus_of_the_amphitheater_red":
    case "chorus_of_the_amphitheater_yellow":
    case "chorus_of_the_amphitheater_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I" && $from == "HAND") {
        AddCurrentTurnEffect($cardID, $currentPlayer, from: "ABILITY");
      } else {
        DealArcane(ArcaneDamage($cardID), 2, "PLAYCARD", $cardID, resolvedTarget: $target);
      }
      return "";
    case "glyph_overlay_red":
    case "glyph_overlay_yellow":
    case "glyph_overlay_blue":
      $numSigils = 0;
      $sigils = SearchAura($currentPlayer, nameIncludes: "Sigil");
      if($sigils != "") $numSigils = count(explode(",", $sigils));
      DealArcane(ArcaneDamage($cardID) + $numSigils, 0, "PLAYCARD", $cardID, resolvedTarget: $target);
      return "";
    case "save_the_thought_red":
    case "save_the_thought_yellow":
    case "save_the_thought_blue":
      $numCardsShuffled = match ($cardID) {
        "save_the_thought_red" => 3,
        "save_the_thought_yellow" => 2,
        "save_the_thought_blue" => 1
      };
      $actions = SearchDiscard($currentPlayer, "A");
      PlayAura("ponder", $currentPlayer);
      if ($actions == "") return "";
      AddDecisionQueue("MULTICHOOSEDISCARD", $currentPlayer, $numCardsShuffled . "-" . $actions);
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "REMEMBRANCE", 1);
      AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
      return "";
    case "arcane_twining_red":
    case "arcane_twining_yellow":
    case "arcane_twining_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I" && $from == "HAND") {
        AddCurrentTurnEffect($cardID, $currentPlayer, from: "ABILITY");
      } else {
        DealArcane(ArcaneDamage($cardID), 2, "PLAYCARD", $cardID, resolvedTarget: $target);
      }
      return "";
    case "exploding_aether_red":
    case "exploding_aether_yellow":
    case "exploding_aether_blue":
      $ampAmount = match ($cardID) {
        "exploding_aether_red" => 3,
        "exploding_aether_yellow" => 2,
        "exploding_aether_blue" => 1
      };
      AddCurrentTurnEffect($cardID . "," . $ampAmount, $currentPlayer, "PLAY");
      return CardLink($cardID, $cardID) . " is amping " . $ampAmount;
    case "destructive_aethertide_blue"://destructive aethertide
    case "eternal_inferno_red"://eternal inferno
    case "pop_the_bubble_red":
    case "pop_the_bubble_yellow":
    case "pop_the_bubble_blue":
    case "etchings_of_arcana_red":
    case "etchings_of_arcana_yellow":
    case "etchings_of_arcana_blue":
    case "open_the_flood_gates_red":
    case "open_the_flood_gates_yellow":
    case "open_the_flood_gates_blue"://open the floodgates
    case "overflow_the_aetherwell_red":
    case "overflow_the_aetherwell_yellow":
    case "overflow_the_aetherwell_blue":
    case "perennial_aetherbloom_red":
    case "perennial_aetherbloom_yellow":
    case "perennial_aetherbloom_blue":
      DealArcane(ArcaneDamage($cardID), 0, "PLAYCARD", $cardID, resolvedTarget: $target);
      return "";
    case "photon_splicing_red":
    case "photon_splicing_yellow":
    case "photon_splicing_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I" && $from == "HAND") {
        AddCurrentTurnEffect($cardID, $currentPlayer, from: "ABILITY");
      } else {
        DealArcane(ArcaneDamage($cardID), 2, "PLAYCARD", $cardID, resolvedTarget: $target);
      }
      return "";
    case "trailblazing_aether_red":
    case "trailblazing_aether_yellow":
    case "trailblazing_aether_blue":
      DealArcane(ArcaneDamage($cardID), 0, "PLAYCARD", $cardID, resolvedTarget: $target);
      return "";
    case "ten_foot_tall_and_bulletproof_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "count_your_blessings_red":
    case "count_your_blessings_yellow":
    case "count_your_blessings_blue":
      $baseLife = match ($cardID) {
        "count_your_blessings_red" => 3,
        "count_your_blessings_yellow" => 2,
        "count_your_blessings_blue" => 1
      };
      $cardsInGraveyard = SearchCount(SearchDiscardByName($currentPlayer, "Count Your Blessings"))-1; //-1 so it doesn't count itself as the card on Talishar goes in the graveyard before it finish resolving
      $cardsInGraveyard = $cardsInGraveyard < 0 ? 0 : $cardsInGraveyard;
      GainHealth($cardsInGraveyard + $baseLife, $currentPlayer); 
      return "";
    case "hood_of_second_thoughts":
    case "bruised_leather":
    case "four_finger_gloves":
      IncrementClassState($currentPlayer, $CS_DamagePrevention);
      return "";
    case "call_to_the_grave_blue":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "DECK");
      AddDecisionQueue("MAYCHOOSEDECK", $currentPlayer, "<-", 1);
      AddDecisionQueue("ADDDISCARD", $currentPlayer, "DECK", 1);
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
      AddDecisionQueue("WRITELOG", $currentPlayer, "<0> was chosen.", 1);
      AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
      return "";
    case "truce_blue":
      if($currentPlayer == $mainPlayer) AddNextTurnEffect($cardID, $currentPlayer);
      else AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "arcane_polarity_red":
    case "arcane_polarity_yellow":
    case "arcane_polarity_blue":
      if (GetClassState($currentPlayer, $CS_ArcaneDamageTaken) > 0) {
        $HealthGain = match ($cardID) {
          "arcane_polarity_red" => 4,
          "arcane_polarity_yellow" => 3,
          "arcane_polarity_blue" => 2
        };
        GainHealth($HealthGain, $currentPlayer);
      } else {
        GainHealth(1, $currentPlayer);
      }
      return "";
    case "drink_em_under_the_table_red":
      if (IsHeroAttackTarget()) AskWager($cardID);
      return "";
    case "adaptive_dissolver":
      ModularMove($cardID, $additionalCosts);
      return "";
    case "plan_for_the_worst_blue":
      LookAtHand($otherPlayer);
      LookAtArsenal($otherPlayer);
      if ($otherPlayer != $mainPlayer) AddNextTurnEffect($cardID . "-1", $otherPlayer);
      else AddCurrentTurnEffect($cardID . "-1", $otherPlayer);
      for ($i=0; $i < 3; $i++) { 
        MZMoveCard($currentPlayer, "MYDECK:subtype=Trap", "MYHAND", may: true, DQContext:"Choose traps from your deck to add to your hand:");
      }
      AddDecisionQueue("FINDINDICES", $currentPlayer, "HAND");
      AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "2-");
      AddDecisionQueue("APPENDLASTRESULT", $currentPlayer, "-2");
      AddDecisionQueue("MULTICHOOSEHAND", $currentPlayer, "<-");
      AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-");
      AddDecisionQueue("MULTIADDDECK", $currentPlayer, "-");
      AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
      return "";
    case "unsheathed_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "calming_cloak":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "calming_gesture":
      PlayAura("spectral_shield", $currentPlayer, 1);
      return "";
    case "dust_from_the_fertile_fields_red":
      PutPermanentIntoPlay($currentPlayer, $cardID);
      return "";
    default:
      return "";
  }
}

function ROSHitEffect($cardID): void
{
  global $currentPlayer, $defPlayer;
  switch ($cardID) {
    case "earth_form_red":
    case "earth_form_yellow":
    case "earth_form_blue":
      PlayAura("embodiment_of_earth", $currentPlayer);
      break;
    case "lightning_form_red":
    case "lightning_form_yellow":
    case "lightning_form_blue":
      PlayAura("embodiment_of_lightning", $currentPlayer);
      break;
    case "splintering_deadwood_red":
    case "splintering_deadwood_yellow":
    case "splintering_deadwood_blue":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYAURAS");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
      AddDecisionQueue("PLAYAURA", $currentPlayer, "runechant", 1);
      break;
    case "hand_behind_the_pen_red":
      if (ArsenalHasFaceDownCard($defPlayer)) {
        SetArsenalFacing("UP", $defPlayer);
      }
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRARS:type=A");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which card you want to banish", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZBANISH", $currentPlayer, "THEIRARS", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      break;
    case "smash_up_red":
      if (ArsenalHasFaceDownCard($defPlayer)) {
        SetArsenalFacing("UP", $defPlayer);
      }
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRARS:type=AA");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which card you want to banish", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZBANISH", $currentPlayer, "THEIRARS", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      break;
    case "tongue_tied_red":
      if (ArsenalHasFaceDownCard($defPlayer)) {
        SetArsenalFacing("UP", $defPlayer);
      }
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRARS:type=I");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which card you want to banish", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZBANISH", $currentPlayer, "THEIRARS", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      break;
    case "splatter_skull_red":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRBANISH:isIntimidated=true");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose an intimidated card to put into the graveyard (The cards were intimated in left to right order)", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZADDZONE", $currentPlayer, "THEIRDISCARD", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "THEIRBANISH", 1);
      break;
    case "snuff_out_red":
      $myAuras = &GetAuras($currentPlayer);
      if (count($myAuras) > 0) {
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYAURAS");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
        AddDecisionQueue("FINDINDICES", $defPlayer, "HAND", 1);
        AddDecisionQueue("CHOOSEHAND", $defPlayer, "<-", 1);
        AddDecisionQueue("REMOVEMYHAND", $defPlayer, "-", 1);
        AddDecisionQueue("DISCARDCARD", $defPlayer, "HAND-".$defPlayer, 1);
      }
      break;
    case "cut_through_the_facade_red":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRAURAS");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
      break;
    default:
      break;
  }
}

function GetTrapIndices($player)
{
  return SearchDeck($player, subtype: "Trap");
}

function HasAuraWithSigilInName($player)
{
  $auras = &GetAuras($player);
  for ($i = 0; $i < count($auras); $i += AuraPieces()) {
    if (CardNameContains($auras[$i], "Sigil", $player, partial: true)) return true;
  }
  return false;
}

function IsDoubleArcane($cardID): bool //checks for cards that can shock, but not use up arcane buffs on the shock
{
  return match ($cardID) {
    "comet_storm__shock_red" => true,
    default => false,
  };
}