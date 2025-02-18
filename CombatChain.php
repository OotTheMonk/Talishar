<?php

function ProcessHitEffect($cardID, $from = "-", $uniqueID = -1)
{
  global $CombatChain, $layers, $mainPlayer, $chainLinks;
  WriteLog("Processing hit effect for " . CardLink($cardID, $cardID));
  if (!DelimStringContains(CardType($cardID), "W")) {//stops flicks from interacting with tarpit trap
    if (CardType($CombatChain->AttackCard()->ID()) == "AA" && SearchCurrentTurnEffects("tarpit_trap_yellow", $mainPlayer, count($layers) < LayerPieces())) {
      WriteLog("Hit effect prevented by " . CardLink("tarpit_trap_yellow", "tarpit_trap_yellow"));
      return true;
    }
  }
  //check tarpit trap against flicked kiss of death if the current attack is a dagger
  if (CardType($cardID) == "AA" && SubtypeContains($cardID, "Dagger", $mainPlayer) && SearchCurrentTurnEffects("tarpit_trap_yellow", $mainPlayer, count($layers) < LayerPieces())) {
    WriteLog("Hit effect prevented by " . CardLink("tarpit_trap_yellow", "tarpit_trap_yellow"));
    return true;
  }
  $cardID = ShiyanaCharacter($cardID);

  $set = CardSet($cardID);
  $class = CardClass($cardID);
  if ($set == "WTR") return WTRHitEffect($cardID);
  else if ($set == "ARC") {
    switch ($class) {
      case "MECHANOLOGIST":
        return ARCMechanologistHitEffect($cardID, $from);
      case "RANGER":
        return ARCRangerHitEffect($cardID, $from);
      case "RUNEBLADE":
        return ARCRunebladeHitEffect($cardID);
      case "WIZARD":
        return ARCWizardHitEffect($cardID);
      case "GENERIC":
        return ARCGenericHitEffect($cardID);
    }
  } else if ($set == "CRU") return CRUHitEffect($cardID);
  else if ($set == "MON") {
    switch ($class) {
      case "BRUTE":
        return MONBruteHitEffect($cardID);
      case "ILLUSIONIST":
        return MONIllusionistHitEffect($cardID);
      case "RUNEBLADE":
        return MONRunebladeHitEffect($cardID);
      case "WARRIOR":
        return MONWarriorHitEffect($cardID);
      case "GENERIC":
        return MONGenericHitEffect($cardID);
      case "NONE":
        return MONTalentHitEffect($cardID);
      default:
        return "";
    }
  } else if ($set == "ELE") {
    switch ($class) {
      case "GUARDIAN":
        return ELEGuardianHitEffect($cardID);
      case "RANGER":
        return ELERangerHitEffect($cardID);
      case "RUNEBLADE":
        return ELERunebladeHitEffect($cardID);
      default:
        return ELETalentHitEffect($cardID);
    }
  } else if ($set == "EVR") return EVRHitEffect($cardID);
  else if ($set == "UPR") return UPRHitEffect($cardID);
  else if ($set == "DYN") return DYNHitEffect($cardID, $from, $CombatChain->AttackCard()->ID());
  else if ($set == "OUT") return OUTHitEffect($cardID, $from);
  else if ($set == "DTD") return DTDHitEffect($cardID);
  else if ($set == "TCC") return TCCHitEffect($cardID);
  else if ($set == "EVO") return EVOHitEffect($cardID);
  else if ($set == "HVY") return HVYHitEffect($cardID);
  else if ($set == "AKO") return AKOHitEffect($cardID);
  else if ($set == "MST") return MSTHitEffect($cardID, $from);
  else if ($set == "AAZ") return AAZHitEffect($cardID);
  else if ($set == "AUR") return AURHitEffect($cardID);
  else if ($set == "ROS") return ROSHitEffect($cardID);
  else if ($set == "AJV") return AJVHitEffect($cardID);
  else if ($set == "HNT") return HNTHitEffect($cardID, $uniqueID);
  else return -1;
}

function AttackModifier($cardID, $from = "", $resourcesPaid = 0, $repriseActive = -1)
{
  global $mainPlayer, $defPlayer, $CS_Num6PowDisc, $CombatChain, $combatChainState, $mainAuras, $CS_CardsBanished;
  global $CS_NumCharged, $CCS_NumBoosted, $defPlayer, $CS_ArcaneDamageTaken, $CS_NumYellowPutSoul, $CS_NumCardsDrawn;
  global $CS_NumPlayedFromBanish, $CS_NumAuras, $CS_AtksWWeapon, $CS_Num6PowBan, $CS_HaveIntimidated, $chainLinkSummary;
  global $combatChain, $CS_Transcended, $CS_NumBluePlayed, $CS_NumLightningPlayed, $CS_DamageDealt, $CS_NumCranked, $CS_ArcaneDamageDealt;
  global $chainLinks, $chainLinkSummary, $CCS_FlickedDamage;
  if ($repriseActive == -1) $repriseActive = RepriseActive();
  if (HasPiercing($cardID, $from)) return NumEquipBlock() > 0 ? 1 : 0;
  switch ($cardID) {
    case "romping_club":
      return (GetClassState($mainPlayer, $CS_Num6PowDisc) > 0 ? 1 : 0);
    case "breaking_scales":
      return 1;
    case "lord_of_wind_blue":
      return (ComboActive() ? $resourcesPaid : 0);
    case "ancestral_empowerment_red":
      return 1;
    case "mugenshi_release_yellow":
      return (ComboActive() ? 1 : 0);
    case "hurricane_technique_yellow":
      return (ComboActive() ? 1 : 0);
    case "fluster_fist_red":
    case "fluster_fist_yellow":
    case "fluster_fist_blue":
      return (ComboActive() ? NumAttacksHit() : 0);
    case "blackout_kick_red":
    case "blackout_kick_yellow":
    case "blackout_kick_blue":
      return (ComboActive() ? 3 : 0);
    case "open_the_center_red":
    case "open_the_center_yellow":
    case "open_the_center_blue":
      return (ComboActive() ? 1 : 0);
    case "rising_knee_thrust_red":
    case "rising_knee_thrust_yellow":
    case "rising_knee_thrust_blue":
      return (ComboActive() ? 2 : 0);
    case "whelming_gustwave_red":
    case "whelming_gustwave_yellow":
    case "whelming_gustwave_blue":
      return (ComboActive() ? 1 : 0);
    case "rout_red":
      return 3;
    case "singing_steelblade_yellow":
      return 1;
    case "overpower_red":
      return $repriseActive ? 6 : 4;
    case "overpower_yellow":
      return $repriseActive ? 5 : 3;
    case "overpower_blue":
      return $repriseActive ? 4 : 2;
    case "ironsong_response_red":
      return TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer) && $repriseActive ? 3 : 0;
    case "ironsong_response_yellow":
      return TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer) && $repriseActive ? 2 : 0;
    case "ironsong_response_blue":
      return TypeContains($CombatChain->AttackCard()->ID(), "W", $mainPlayer) && $repriseActive ? 1 : 0;
    case "biting_blade_red":
      return 3;
    case "biting_blade_yellow":
      return 2;
    case "biting_blade_blue":
      return 1;
    case "stroke_of_foresight_red":
      return 3;
    case "stroke_of_foresight_yellow":
      return 2;
    case "stroke_of_foresight_blue":
      return 1;
    case "barraging_brawnhide_red":
    case "barraging_brawnhide_yellow":
    case "barraging_brawnhide_blue":
      return NumCardsNonEquipBlocking() < 2 ? 1 : 0;
    case "push_the_point_red":
    case "push_the_point_yellow":
    case "push_the_point_blue":
      return $chainLinkSummary[count($chainLinkSummary) - ChainLinkSummaryPieces()] > 0 ? 2 : 0;
    case "riled_up_red":
    case "riled_up_yellow":
    case "riled_up_blue":
      return GetClassState($mainPlayer, $CS_Num6PowDisc) > 0 ? 1 : 0;
    case "herons_flight_red":
      return ComboActive() ? 2 : 0;
    case "crane_dance_red":
    case "crane_dance_yellow":
    case "crane_dance_blue":
      return ComboActive() ? 1 : 0;
    case "rushing_river_red":
    case "rushing_river_yellow":
    case "rushing_river_blue":
      return ComboActive() ? 1 : 0;
    case "flying_kick_red":
    case "flying_kick_yellow":
    case "flying_kick_blue":
      return NumChainLinks() >= 3 ? 2 : 0;
    case "salt_the_wound_yellow":
      return NumAttacksHit();
    case "unified_decree_yellow":
      return 3;
    case "plasma_barrel_shot":
      return 1 + $combatChainState[$CCS_NumBoosted];
    case "overblast_red":
    case "overblast_yellow":
    case "overblast_blue":
      return $combatChainState[$CCS_NumBoosted];
    case "raydn_duskbane":
      return GetClassState($mainPlayer, $CS_NumCharged) > 0 ? 3 : 0;
    case "valiant_thrust_red":
    case "valiant_thrust_yellow":
    case "valiant_thrust_blue":
      return GetClassState($mainPlayer, $CS_NumCharged) > 0 ? 3 : 0;
    case "courageous_steelhand_red":
      return GetClassState($mainPlayer, $CS_NumCharged) > 0 ? 3 : 0;
    case "courageous_steelhand_yellow":
      return GetClassState($mainPlayer, $CS_NumCharged) > 0 ? 2 : 0;
    case "courageous_steelhand_blue":
      return GetClassState($mainPlayer, $CS_NumCharged) > 0 ? 1 : 0;
    case "galaxxi_black":
      return GetClassState($mainPlayer, $CS_NumPlayedFromBanish) > 0 ? 2 : 0;
    case "piercing_shadow_vise_red":
    case "piercing_shadow_vise_yellow":
    case "piercing_shadow_vise_blue":
      return GetClassState($defPlayer, $CS_ArcaneDamageTaken) > 0 ? 2 : 0;
    case "tremor_of_iarathael_red":
    case "tremor_of_iarathael_yellow":
    case "tremor_of_iarathael_blue":
      return GetClassState($mainPlayer, $CS_CardsBanished) > 0 ? 2 : 0;
    case "stony_woottonhog_red":
    case "stony_woottonhog_yellow":
    case "stony_woottonhog_blue":
      return NumCardsNonEquipBlocking() < 2 ? 1 : 0;
    case "surging_militia_red":
    case "surging_militia_yellow":
    case "surging_militia_blue":
      return NumCardsNonEquipBlocking();
    case "yinti_yanti_red":
    case "yinti_yanti_yellow":
    case "yinti_yanti_blue":
      return count($mainAuras) >= 1 ? 1 : 0;
    case "stir_the_wildwood_red":
    case "stir_the_wildwood_yellow":
    case "stir_the_wildwood_blue":
      return GetClassState($defPlayer, $CS_ArcaneDamageTaken) >= 1 ? 2 : 0;
    case "burgeoning_red":
    case "burgeoning_yellow":
    case "burgeoning_blue":
      return $from == "ARS" ? 1 : 0;
    case "break_tide_yellow":
      return (ComboActive() ? 3 : 0);
    case "winds_of_eternity_blue":
      return (ComboActive() ? 2 : 0);
    case "hundred_winds_red":
    case "hundred_winds_yellow":
    case "hundred_winds_blue":
      return (ComboActive() ? NumChainLinksWithName("Hundred Winds") - 1 : 0);
    case "in_the_swing_red":
      return 3;
    case "in_the_swing_yellow":
      return 2;
    case "in_the_swing_blue":
      return 1;
    case "swarming_gloomveil_red":
      return (GetClassState($mainPlayer, $CS_NumAuras) >= 2 ? 1 : 0);
    case "shrill_of_skullform_red":
    case "shrill_of_skullform_yellow":
    case "shrill_of_skullform_blue":
      return (GetClassState($mainPlayer, $CS_NumAuras) > 0 ? 3 : 0);
    case "dawnblade_resplendent":
      return GetClassState($mainPlayer, $CS_AtksWWeapon) >= 1 ? 1 : 0;
    case "beast_mode_red":
      return GetClassState($mainPlayer, $CS_HaveIntimidated) > 0 ? 2 : 0;
    case "phoenix_form_red":
      return (NumChainLinksWithName("Phoenix Flame") >= 2 ? 2 : 0);
    case "combustion_point_red":
      return 1;
    case "lava_burst_red":
      return (RuptureActive() ? 3 : 0);
    case "phoenix_flame_red":
      return (NumDraconicChainLinks() >= 2 ? 1 : 0);
    case "rapid_reflex_red":
      return 3;
    case "rapid_reflex_yellow":
      return 2;
    case "rapid_reflex_blue":
      return 1;
    case "tiger_swipe_red":
      return (ComboActive() ? 2 : 0);
    case "pouncing_qi_red":
    case "pouncing_qi_yellow":
    case "pouncing_qi_blue":
      return (ComboActive() ? 1 : 0);
    case "qi_unleashed_red":
    case "qi_unleashed_yellow":
    case "qi_unleashed_blue":
      return (ComboActive() ? 4 : 0);
    case "sneak_attack_red":
    case "sneak_attack_yellow":
    case "sneak_attack_blue":
      return (NumAttackReactionsPlayed() > 0 ? 4 : 0);
    case "dishonor_blue":
      return (ComboActive() ? 2 : 0);
    case "silverwind_shuriken_blue":
      return 1;
    case "spinning_wheel_kick_red":
    case "spinning_wheel_kick_yellow":
    case "spinning_wheel_kick_blue":
      return (ComboActive() ? 1 : 0);
    case "descendent_gustwave_red":
    case "descendent_gustwave_yellow":
    case "descendent_gustwave_blue":
      return (ComboActive() ? 2 : 0);
    case "widowmaker_red":
    case "widowmaker_yellow":
    case "widowmaker_blue":
      return NumCardsDefended() < 2 ? 3 : 0;
    case "fisticuffs":
      return 1;
    case "feisty_locals_red":
    case "feisty_locals_yellow":
    case "feisty_locals_blue":
      return (CachedNumActionBlocked() > 0 ? 2 : 0);
    case "freewheeling_renegades_red":
    case "freewheeling_renegades_yellow":
    case "freewheeling_renegades_blue":
      return (CachedNumActionBlocked() > 0 ? -2 : 0);
    case "beaming_blade":
      return GetClassState($mainPlayer, $CS_NumYellowPutSoul) > 0 ? 5 : 0;
    case "searing_ray_red":
    case "searing_ray_yellow":
    case "searing_ray_blue":
      return (SearchPitchForColor($mainPlayer, 2) > 0 ? 2 : 0);
    case "battlefield_breaker_red":
    case "battlefield_breaker_yellow":
    case "battlefield_breaker_blue":
      return GetClassState($mainPlayer, $CS_Num6PowBan) > 0 ? 1 : 0;
    case "soul_butcher_red":
    case "soul_butcher_yellow":
    case "soul_butcher_blue":
      $theirSoul = &GetSoul($defPlayer);
      return (count($theirSoul) > 0 ? 2 : 0);
    case "mechanical_strength_red":
    case "mechanical_strength_blue":
      return EvoUpgradeAmount($mainPlayer);
    case "teklo_leveler":
      return EvoUpgradeAmount($mainPlayer) >= 4;
    case "annihilator_engine_red":
    case "terminator_tank_red":
    case "war_machine_red":
      return EvoUpgradeAmount($mainPlayer) >= 4 ? 3 : 0;
    case "mechanical_strength_red":
    case "mechanical_strength_yellow":
    case "mechanical_strength_blue":
      return EvoUpgradeAmount($mainPlayer);
    case "fender_bender_red":
    case "fender_bender_yellow":
    case "fender_bender_blue":
    case "panel_beater_red":
    case "panel_beater_yellow":
    case "panel_beater_blue":
      return NumEquipBlock();
    case "show_no_mercy_red":
      $hand = &GetHand($defPlayer);
      return $combatChain[0] == "show_no_mercy_red" && count($hand) == 0 ? 3 : 0;
    case "beast_mode_red":
    case "beast_mode_yellow":
    case "beast_mode_blue":
      return GetClassState($mainPlayer, $CS_HaveIntimidated) > 0 ? 2 : 0;
    case "take_the_upper_hand_red":
      return 3;
    case "take_the_upper_hand_yellow":
      return 2;
    case "take_the_upper_hand_blue":
      return 1;
    case "rising_power_red":
    case "rising_power_yellow":
    case "rising_power_blue":
      return GetClassState($mainPlayer, $CS_NumCardsDrawn) >= 1 ? 1 : 0;
    case "second_tenet_of_chi_tide_blue":
      return GetClassState($mainPlayer, $CS_Transcended) > 0 ? 2 : 0;
    case "droplet_blue":
    case "rising_tide_blue":
    case "spillover_blue":
    case "tidal_surge_blue":
      return GetClassState($mainPlayer, $CS_NumBluePlayed) > 1 ? 2 : 0;
    case "bonds_of_agony_blue":
      return NumAttackReactionsPlayed() > 2 ? 3 : 0;
    case "double_trouble_red":
    case "double_trouble_yellow":
    case "double_trouble_blue":
      return NumAttackReactionsPlayed() > 1 ? 2 : 0;
    case "pick_to_pieces_red":
    case "pick_to_pieces_yellow":
    case "pick_to_pieces_blue":
      return NumAttackReactionsPlayed() > 0 ? 1 : 0;
    case "rowdy_locals_blue":
      return CachedNumActionBlocked() > 0 ? 2 : 0;
    case "crackling_red":
    case "crackling_yellow":
    case "star_fall":
      return GetClassState($mainPlayer, $CS_NumLightningPlayed) > 0;
    case "felling_of_the_crown_red":
      return SearchCount(SearchMultiZone($mainPlayer, "MYBANISH:talent=EARTH")) >= 4 ? 4 : 0;
    case "plow_under_yellow":
      return SearchCount(SearchMultiZone($mainPlayer, "MYBANISH:talent=EARTH")) >= 4 ? 4 : 0;
    case "strength_of_four_seasons_red":
    case "strength_of_four_seasons_yellow":
    case "strength_of_four_seasons_blue":
      return SearchCount(SearchMultiZone($mainPlayer, "MYBANISH:talent=EARTH")) >= 4 ? 4 : 0;
    case "second_strike_red":
    case "second_strike_yellow":
    case "second_strike_blue":
      return (GetClassState($mainPlayer, $CS_DamageDealt) + GetClassState($mainPlayer, $CS_ArcaneDamageDealt)) > 0 ? 1 : 0;
    case "arcanic_spike_red":
    case "arcanic_spike_yellow":
    case "arcanic_spike_blue":
      return GetClassState($defPlayer, $CS_ArcaneDamageTaken) > 0 ? 2 : 0;
    case "hit_the_high_notes_red":
    case "hit_the_high_notes_yellow":
    case "hit_the_high_notes_blue":
      return (GetClassState($mainPlayer, $CS_NumAuras) > 0 ? 2 : 0);
    case "fast_and_furious_red":
      return (GetClassState($mainPlayer, $CS_NumCranked)) > 0 ? 1 : 0;
    case "summit_the_unforgiving":
      return (CheckHeavy($mainPlayer)) ? 2 : 0;
    case "plunge_the_prospect_red":
    case "plunge_the_prospect_yellow":
    case "plunge_the_prospect_blue":
      return (IsHeroAttackTarget() && CheckMarked($defPlayer)) ? 1 : 0;
    case "grow_claws_red":
    case "grow_claws_yellow":
    case "grow_claws_blue":
      return isPreviousLinkDraconic() ? 1 : 0;
    case "hunts_end_red":
      return 4;
    case "jagged_edge_red":
      return 3;
    case "diced_red":
    case "diced_yellow":
    case "diced_blue":
      return 1;
    case "hand_of_vengeance":
      return 1;
    case "hunt_to_the_ends_of_rathe_red":
      return CheckMarked($defPlayer) ? 2 : 0;
    case "cut_through_red":
    case "cut_through_yellow":
    case "cut_through_blue":
      $numDaggerHits = 0;
        for($i=0; $i<count($chainLinks); ++$i)
        {
          if(CardSubType($chainLinks[$i][0]) == "Dagger" && $chainLinkSummary[$i*ChainLinkSummaryPieces()] > 0) ++$numDaggerHits;
        }
        $numDaggerHits += $combatChainState[$CCS_FlickedDamage];
      return $numDaggerHits > 0 ? 1 : 0;
    case "to_the_point_red":
      return CheckMarked($defPlayer) ? 4 : 3;
    case "to_the_point_yellow":
      return CheckMarked($defPlayer) ? 3 : 2;
    case "to_the_point_blue":
      return CheckMarked($defPlayer) ? 2 : 1;
    case "incision_red": return 3;
    case "incision_yellow": return 2;
    case "incision_blue": return 1;
    case "outed_red": return CheckMarked($defPlayer) ? 1 : 0;
    case "retrace_the_past_blue":
      return (SearchCurrentTurnEffectsForIndex("retrace_the_past_blue", $mainPlayer) != -1 ? 2 : 0);
    default:
      return 0;
  }
}

function BlockModifier($cardID, $from, $resourcesPaid)
{
  global $defPlayer, $CS_CardsBanished, $mainPlayer, $CS_ArcaneDamageTaken, $CombatChain, $chainLinks, $CS_NumClashesWon, $CS_Num6PowBan, $CS_NumCrouchingTigerCreatedThisTurn;
  global $CS_NumBluePlayed;
  $blockModifier = 0;
  $cardType = CardType($cardID);
  if ($cardType == "AA") $blockModifier += CountCurrentTurnEffects("art_of_war_yellow-1", $defPlayer);
  if ($cardType == "AA") $blockModifier += CountCurrentTurnEffects("potion_of_ironhide_blue", $defPlayer);
  if ($cardType == "AA") $blockModifier += CountCurrentTurnEffects("ROGUE802", $defPlayer);
  if ((DelimStringContains($cardType, "E") || SubtypeContains($cardID, "Evo")) && (SearchCurrentTurnEffects("scramble_pulse_red", $mainPlayer) || SearchCurrentTurnEffects("scramble_pulse_yellow", $mainPlayer) || SearchCurrentTurnEffects("scramble_pulse_blue", $mainPlayer))) {
    $countScramblePulse = 0 + CountCurrentTurnEffects("scramble_pulse_red", $mainPlayer);
    $countScramblePulse += CountCurrentTurnEffects("scramble_pulse_yellow", $mainPlayer);
    $countScramblePulse += CountCurrentTurnEffects("scramble_pulse_blue", $mainPlayer);
    $blockModifier -= 1 * $countScramblePulse;
  }
  if (SearchCurrentTurnEffects("pulse_of_isenloft_blue", $defPlayer) && ($cardType == "AA" || DelimStringContains($cardType, "A")) && (TalentContains($cardID, "ICE", $defPlayer) || TalentContains($cardID, "EARTH", $defPlayer) || TalentContains($cardID, "ELEMENTAL", $defPlayer))) $blockModifier += 1;
  if (SearchCurrentTurnEffects("fabricate_red", $defPlayer) && SubtypeContains($cardID, "Evo", $defPlayer) && ($from == "EQUIP" || $from == "CC")) $blockModifier += CountCurrentTurnEffects("fabricate_red", $defPlayer);
  $defAuras = &GetAuras($defPlayer);
  $attackID = $CombatChain->AttackCard()->ID();
  for ($i = 0; $i < count($defAuras); $i += AuraPieces()) {
    if ($defAuras[$i] == "stonewall_confidence_red" && CardCost($cardID, $from) >= 3) $blockModifier += 4;
    if ($defAuras[$i] == "stonewall_confidence_yellow" && CardCost($cardID, $from) >= 3) $blockModifier += 3;
    if ($defAuras[$i] == "stonewall_confidence_blue" && CardCost($cardID, $from) >= 3) $blockModifier += 2;
    if ($defAuras[$i] == "forged_for_war_yellow" && $cardType == "E") $blockModifier += 1;
    if ($defAuras[$i] == "embodiment_of_earth" && DelimStringContains($cardType, "A")) $blockModifier += 1;
    if ($defAuras[$i] == "stacked_in_your_favor_red" && $cardType == "AA") $blockModifier += 3;
    if ($defAuras[$i] == "stacked_in_your_favor_yellow" && $cardType == "AA") $blockModifier += 2;
    if ($defAuras[$i] == "stacked_in_your_favor_blue" && $cardType == "AA") $blockModifier += 1;
  }
  $blockModifier += ItemBlockModifier($cardID);
  switch ($cardID) {
    case "unmovable_red":
    case "unmovable_yellow":
    case "unmovable_blue":
      $blockModifier += $from == "ARS" ? 1 : 0;
      break;
    case "staunch_response_red":
    case "staunch_response_yellow":
    case "staunch_response_blue":
      $blockModifier += ($resourcesPaid >= 6 ? 3 : 0);
      break;
    case "arcanite_skullcap":
      $blockModifier += (PlayerHasLessHealth($defPlayer) ? 1 : 0);
      break;
    case "springboard_somersault_yellow":
      $blockModifier += ($from == "ARS" ? 2 : 0);
      break;
    case "impenetrable_belief_red":
    case "impenetrable_belief_yellow":
    case "impenetrable_belief_blue":
      return GetClassState($mainPlayer, $CS_CardsBanished) >= 3 ? 2 : 0;
    case "yinti_yanti_red":
    case "yinti_yanti_yellow":
    case "yinti_yanti_blue":
      return count($defAuras) >= 1 ? 1 : 0;
    case "sigil_of_suffering_red":
    case "sigil_of_suffering_yellow":
    case "sigil_of_suffering_blue":
      return GetClassState($mainPlayer, $CS_ArcaneDamageTaken) > 0 ? 1 : 0;
    case "wax_on_red":
    case "wax_on_yellow":
    case "wax_on_blue":
      return (CardCost($attackID) == 0 && CardType($attackID) == "AA" ? 2 : 0);
    case "blazen_yoroi":
      $blockModifier += (count($chainLinks) >= 3 ? 4 : 0);
      break;
    case "shield_wall_red":
    case "shield_wall_yellow":
    case "shield_wall_blue":
      $blockModifier += SearchCharacter($defPlayer, subtype: "Off-Hand", class: "GUARDIAN") != "" ? 4 : 0;
      break;
    case "diabolic_offering_blue":
      $blockModifier += GetClassState($defPlayer, $CS_Num6PowBan) > 0 ? 6 : 0;
      break;
    case "bastion_of_unity":
      $blockModifier += CountCurrentTurnEffects($cardID, $defPlayer);
      break;
    case "steel_street_enforcement_blue":
      $blockModifier += EvoUpgradeAmount($defPlayer);
      break;
    case "teklonetic_force_field_red":
    case "teklonetic_force_field_yellow":
    case "teklonetic_force_field_blue":
      if (CachedOverpowerActive()) $blockModifier += 2;
      break;
    case "raw_meat":
      CountAura("agility", $defPlayer) > 0 ? $blockModifier += 1 : 0; //Agility
      CountAura("might", $defPlayer) > 0 ? $blockModifier += 1 : 0; //Might
      break;
    case "stonewall_impasse":
      $blockModifier += SearchCurrentTurnEffects($cardID, $defPlayer);
      break;
    case "stand_ground":
      CountAura("might", $defPlayer) > 0 ? $blockModifier += 1 : 0; //Might
      CountAura("vigor", $defPlayer) > 0 ? $blockModifier += 1 : 0; //Vigor
      break;
    case "boast_blue":
      $blockModifier += (2 * GetClassState($defPlayer, $CS_NumClashesWon));
      break;
    case "parry_blade":
      if (IsWeaponAttack()) $blockModifier += 2;
      break;    
    case "beckon_applause":
      CountAura("agility", $defPlayer) > 0 ? $blockModifier += 1 : 0; //Agility
      CountAura("vigor", $defPlayer) > 0 ? $blockModifier += 1 : 0; //Vigor
      break;
    case "standing_order_red":
      if (SearchCurrentTurnEffects($cardID, $defPlayer, true)) $blockModifier += 2;
      break;
    case "big_blue_sky_blue":
      if (SearchCurrentTurnEffects($cardID, $defPlayer)) $blockModifier += SearchPitchForColor($defPlayer, 3);
      break;
    case "wash_away_blue":
      if (GetClassState($defPlayer, $CS_NumBluePlayed) > 1) $blockModifier += 2;
      break;
    case "territorial_domain_blue":
      if (GetClassState($defPlayer, $CS_NumCrouchingTigerCreatedThisTurn) > 0) $blockModifier += 3;
      break;
    case "helm_of_lignum_vitae": //helm of lignum vitae
      if (SearchCount(SearchBanish($defPlayer, talent: "EARTH")) >= 4) $blockModifier += 1;
      break;
    case "heavy_industry_surveillance":
      if (SearchCurrentTurnEffects($cardID, $defPlayer)) $blockModifier += CountCurrentTurnEffects($cardID, $defPlayer);
        break;
    case "heavy_industry_ram_stop":
      if (SearchCurrentTurnEffects($cardID, $defPlayer)) $blockModifier += CountCurrentTurnEffects($cardID, $defPlayer);
        break;
    case "red_alert_visor":
    case "red_alert_vest":
    case "red_alert_gloves":
    case "red_alert_boots":
      if (NumAttackReactionsPlayed() > 0) $blockModifier += 1;
      break;
    case "blade_beckoner_helm":
    case "blade_beckoner_plating":
    case "blade_beckoner_gauntlets":
    case "blade_beckoner_boots":
      if (IsWeaponAttack()) $blockModifier += 1;
      break;
    case "quickdodge_flexors":
      if (SearchCurrentTurnEffects($cardID, $defPlayer)) $blockModifier += 2;
      break;
    default:
      break;
  }
  return $blockModifier;
}

function PlayBlockModifier($cardID)
{
  switch ($cardID) {
    case "reinforce_the_line_red":
      return 4;
    case "reinforce_the_line_yellow":
      return 3;
    case "reinforce_the_line_blue":
      return 2;
    case "summerwood_shelter_red":
      return 4;
    case "summerwood_shelter_yellow":
      return 3;
    case "summerwood_shelter_blue":
      return 2;
    case "celestial_resolve_red":
      return 5;
    case "celestial_resolve_yellow":
      return 4;
    case "celestial_resolve_blue":
      return 3;
    default:
      return 0;
  }
}

function OnDefenseReactionResolveEffects($from, $cardID)
{
  global $currentTurnEffects, $mainPlayer, $defPlayer, $combatChain, $CS_NumBlueDefended;
  $blockedFromHand = 0;
  for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
    if(DelimStringContains(CardType($combatChain[$i]), "DR")) {
      if (ColorContains($combatChain[$i], 3, $defPlayer)) IncrementClassState($defPlayer, $CS_NumBlueDefended);
      if ($combatChain[$i + 2] == "HAND") ++$blockedFromHand;
    }
  }
  switch ($combatChain[0]) {
    case "zephyr_needle":
    case "zephyr_needle":
      EvaluateCombatChain($totalAttack, $totalBlock);
      break;
    case "decimator_great_axe":
      if (!SearchCurrentTurnEffects("decimator_great_axe", $mainPlayer)) {
        $nonEquipBlockingCards = GetChainLinkCards($defPlayer, "", "E", exclCardSubTypes: "Evo");
        if ($nonEquipBlockingCards != "") {
          $options = GetChainLinkCards($defPlayer);
          AddCurrentTurnEffect("decimator_great_axe", $mainPlayer);
          AddDecisionQueue("CHOOSECOMBATCHAIN", $mainPlayer, $options);
          AddDecisionQueue("HALVEBASEDEFENSE", $defPlayer, "-", 1);
        }
      }
      break;
    default:
      break;
  }
  switch ($cardID) {
    case "tripwire_trap_red":
      if (!IsAllyAttacking()) AddLayer("TRIGGER", $defPlayer, $cardID);
      AddDecisionQueue("TRIPWIRETRAP", $defPlayer, "-", 1);
      break;
    case "pitfall_trap_yellow":
      if (!IsAllyAttacking()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "rockslide_trap_blue":
      AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "buzzsaw_trap_blue":
      if (!IsAllyAttacking() && HasIncreasedAttack()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "collapsing_trap_blue":
      if (!IsAllyAttacking() && DoesAttackHaveGoAgain()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "spike_pit_trap_blue":
      if (!IsAllyAttacking() && NumAttackReactionsPlayed() > 0) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "boulder_trap_yellow":
      if (!IsAllyAttacking() && HasIncreasedAttack()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "pendulum_trap_yellow":
      if (!IsAllyAttacking() && NumAttackReactionsPlayed() > 0) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "tarpit_trap_yellow":
      if (DoesAttackHaveGoAgain()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "bloodrot_trap_red":
      if (!IsAllyAttacking() && NumAttackReactionsPlayed() > 0) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "frailty_trap_red":
      if (!IsAllyAttacking() && DoesAttackHaveGoAgain()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "inertia_trap_red":
      if (!IsAllyAttacking() && HasIncreasedAttack()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "hunted_or_hunter_red":
      if (!IsAllyAttacking() && NumAttackReactionsPlayed() > 0) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "smoke_out_red":
      if (ColorContains($combatChain[0], 1, $mainPlayer)) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "lair_of_the_spider_red":
      if (!IsAllyAttacking() && DoesAttackHaveGoAgain()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "den_of_the_spider_red":
      if (!IsAllyAttacking() && HasIncreasedAttack()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
    case "chain_reaction_yellow":
      if (DoesAttackHaveGoAgain()) AddLayer("TRIGGER", $defPlayer, $cardID);
      break;
  }
  if ($blockedFromHand > 0 && SearchCharacterActive($mainPlayer, "mark_of_lightning", true) && (TalentContains($combatChain[0], "LIGHTNING", $mainPlayer) || TalentContains($combatChain[0], "ELEMENTAL", $mainPlayer))) {
    AddLayer("TRIGGER", $mainPlayer, "mark_of_lightning");
  }
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $defPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "nerve_scalpel":
        case "nerve_scalpel":
          $count = ModifyBlockForType("DR", -1); //AR is handled in OnBlockResolveEffects
          $remove = $count > 0;
          break;
        default:
          break;
      }
    } else {
      switch ($currentTurnEffects[$i]) {
        case "call_down_the_lightning_yellow":
          if ($from == "HAND") CallDownLightning();
          break;
        default:
          break;
      }
    }
    if ($remove) RemoveCurrentTurnEffect($i);
  }
  ProcessMirageOnBlock(count($combatChain) - CombatChainPieces());
}

function OnBlockResolveEffects($cardID = "")
{
  global $combatChain, $defPlayer, $mainPlayer, $currentTurnEffects, $combatChainState, $CCS_WeaponIndex, $CombatChain, $CS_NumBlueDefended;
  //This is when blocking fully resolves, so everything on the chain from here is a blocking card except the first
  for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
    if (SearchCurrentTurnEffects("ROGUE802", $defPlayer) && CardType($combatChain[$i]) == "AA") CombatChainPowerModifier($i, 1);
    $effectAttackModifier = EffectsAttackYouControlModifiers($combatChain[$i], $defPlayer);
    if ($effectAttackModifier != 0) CombatChainPowerModifier($i, $effectAttackModifier);
    $auraAttackModifier = AurasAttackYouControlModifiers($combatChain[$i], $defPlayer);
    if ($auraAttackModifier != 0) CombatChainPowerModifier($i, $auraAttackModifier);
    else ProcessPhantasmOnBlock($i);
    ProcessMirageOnBlock($i);
  }
  if ($CombatChain->HasCurrentLink()) {
    switch ($combatChain[0]) {
      case "zephyr_needle":
      case "zephyr_needle":
        EvaluateCombatChain($totalAttack, $totalBlock);
        break;
      case "endless_winter_red":
        if (SearchCurrentTurnEffects($combatChain[0], $defPlayer)) {
          AddLayer("TRIGGER", $defPlayer, $combatChain[0]);
        }
        break;
      case "give_and_take_red":
        $NumActionsBlocking = NumActionsBlocking();
        for ($i = 0; $i < $NumActionsBlocking; ++$i) {
          AddLayer("TRIGGER", $defPlayer, $combatChain[0]);
        }
        break;
      case "decimator_great_axe":
        $nonEquipBlockingCards = "";
        if (!SearchCurrentTurnEffects("decimator_great_axe", $mainPlayer)) {
          $nonEquipBlockingCards = GetChainLinkCards($defPlayer, "", exclCardTypes: "E", exclCardSubTypes: "Evo");
          if ($nonEquipBlockingCards != "") {
            $options = GetChainLinkCards($defPlayer);
            AddCurrentTurnEffect("decimator_great_axe", $mainPlayer);
            AddDecisionQueue("CHOOSECOMBATCHAIN", $mainPlayer, $options);
            AddDecisionQueue("HALVEBASEDEFENSE", $mainPlayer, "-", 1);
          }
        }
        break;
      case "hot_streak":
        $character = &GetPlayerCharacter($mainPlayer);
        if (NumAttacksBlocking() > 0 && SearchCurrentTurnEffectsForUniqueID($character[$combatChainState[$CCS_WeaponIndex] + 11] == -1)) {
          AddCurrentTurnEffect($combatChain[0], $mainPlayer, "CC", $character[$combatChainState[$CCS_WeaponIndex] + 11]);
        }
        break;
      case "spark_spray_yellow":
      case "spark_spray_blue":
        AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_pay_1_to_buff_".CardLink($cardID, $cardID), 0, 1);
        AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
        AddDecisionQueue("PASSPARAMETER", $mainPlayer, 1, 1);
        AddDecisionQueue("PAYRESOURCES", $mainPlayer, "<-", 1);
        AddDecisionQueue("ADDTRIGGER", $mainPlayer, $combatChain[0], 1);
        break;
      default:
        break;
    }
  }
  $blockedFromHand = 0;
  $blockedWithIce = 0;
  $blockedWithEarth = 0;
  $blockedWithAura = 0;
  for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
    if (ColorContains($combatChain[$i], 3, $defPlayer)) IncrementClassState($defPlayer, $CS_NumBlueDefended);
    if ($combatChain[$i + 2] == "HAND") ++$blockedFromHand;
    if (TalentContains($combatChain[$i], "ICE", $defPlayer)) ++$blockedWithIce;
    if (TalentContains($combatChain[$i], "EARTH", $defPlayer)) ++$blockedWithEarth;
    if (SubtypeContains($combatChain[$i], "Aura", $defPlayer)) ++$blockedWithAura;
  }
  for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
    if (($blockedFromHand >= 2 && $combatChain[$i + 2] == "HAND") || ($blockedFromHand >= 1 && $combatChain[$i + 2] != "HAND")) UnityEffect($combatChain[$i]);
    if($cardID == "" && HasGalvanize($combatChain[$i])) AddLayer("TRIGGER", $defPlayer, $combatChain[$i], $i);
    elseif($cardID != "" && $combatChain[$i] == $cardID && HasGalvanize($combatChain[$i])) AddLayer("TRIGGER", $defPlayer, $cardID, $i);
    if ($cardID != "") { //Code for when a card is pulled as a defending card on the chain
      $defendingCard = $cardID;
      $i = count($combatChain) - CombatChainPieces();
    }
    if (SearchCurrentTurnEffects("commanding_performance_red", $mainPlayer) != "" && TypeContains($combatChain[$i], "AA", $defPlayer) && ClassContains($combatChain[0], "WARRIOR", $mainPlayer) && IsHeroAttackTarget() && SearchLayersForCardID("commanding_performance_red") == -1) AddLayer("TRIGGER", $mainPlayer, "commanding_performance_red", $defPlayer);
    $defendingCard = $combatChain[$i];
    switch ($defendingCard) {//code for Jarl's armor
      case "ollin_ice_cap":
        $sub = TalentContains($defendingCard, "ICE", $defPlayer) ? 1 : 0; //necessary for a fringe case where the helm but not the other blocking card loses its talent
        if ($blockedWithIce - $sub > 0) AddLayer("TRIGGER", $mainPlayer, $defendingCard, $i);
        break;
      case "tectonic_crust":
        $sub = TalentContains($defendingCard, "EARTH", $defPlayer) == true ? 1 : 0; //necessary for a fringe case where the chest but not the other blocking card loses its talent
        if ($blockedWithEarth - $sub > 0) AddLayer("TRIGGER", $defPlayer, $defendingCard, $i);
        break;
      case "rootbound_trunks":
        if ($blockedWithAura > 0) AddLayer("TRIGGER", $defPlayer, $defendingCard, $i);
        break;
      default:
        break;
    }
    switch ($defendingCard) {
      case "stalagmite_bastion_of_isenloft":
        if (!IsAllyAttacking()) AddLayer("TRIGGER", $mainPlayer, $defendingCard);
        else WriteLog("<span style='color:red;'>No frostbite is created because there is no attacking hero when allies attack.</span>");
        break;
      case "ironhide_helm":
      case "ironhide_plate":
      case "ironhide_gauntlet":
      case "ironhide_legs":
      case "ironhide_gauntlet":
      case "ironhide_legs"://Ironhide
      case "pack_call_yellow"://Pack Call
      case "rampart_of_the_rams_head"://Rampart of the Ram's Head
      case "phantasmal_footsteps"://Phantasmal Footsteps
      case "flameborn_retribution_red"://Flameborn Retribution
      case "crown_of_providence"://Crown of Providence
      case "flex_red":
      case "flex_yellow":
      case "flex_blue"://Flex
      case "fyendals_fighting_spirit_red":
      case "fyendals_fighting_spirit_yellow":
      case "fyendals_fighting_spirit_blue"://Fyendal's Fighting Spirit
      case "brothers_in_arms_red":
      case "brothers_in_arms_yellow":
      case "brothers_in_arms_blue"://Brothers in Arms
      case "hornets_sting"://Hornet's Sting
      case "wayfinders_crest"://Wayfinder's Crest
      case "vambrace_of_determination"://Vambrace of Determination
      case "soulbond_resolve"://Soulbond Resolve
      case "scowling_flesh_bag":
      case "firewall_red":
      case "firewall_yellow":
      case "firewall_blue":
      case "civic_peak":
      case "civic_duty":
      case "civic_guide":
      case "civic_steps":
      case "tiger_eye_reflex_yellow":
      case "tiger_eye_reflex_blue":
      case "crowd_control_red":
      case "crowd_control_yellow":
      case "song_of_the_shining_knight_blue": // Crowd Control
      case "pack_call_red":
      case "pack_call_yellow":
      case "pack_call_blue":
      case "stonewall_impasse":
      case "gauntlets_of_iron_will":
      case "golden_glare":
      case "trounce_red":
      case "test_of_agility_red":
      case "clash_of_might_red":
      case "clash_of_might_yellow":
      case "clash_of_might_blue":
      case "test_of_might_red":
      case "wall_of_meat_and_muscle_red":
      case "clash_of_agility_red":
      case "clash_of_agility_yellow":
      case "clash_of_agility_blue":
      case "run_into_trouble_red":
      case "clash_of_vigor_red":
      case "clash_of_vigor_yellow":
      case "clash_of_vigor_blue":
      case "hearty_block_red":
      case "test_of_vigor_red":
      case "standing_order_red":
      case "test_of_strength_red"://Clash blocks
      case "evo_magneto_blue_equip":
      case "stride_of_reprisal":
      case "traverse_the_universe":
      case "mask_of_wizened_whiskers":
      case "stonewall_gauntlet":
      case "helm_of_halos_grace":
      case "bracers_of_bellonas_grace":
      case "warpath_of_winged_grace":
      case "canopy_shelter_blue":
      case "heavy_industry_surveillance":
      case "heavy_industry_ram_stop":
      case "barkskin_of_the_millennium_tree":
      case "flash_of_brilliance"://flash of brilliance
      case "unforgetting_unforgiving_red"://Unforgiving Unforgetting
      case "mask_of_deceit":
      case "kabuto_of_imperial_authority"://Kabuto of Imperial Authority
      case "thick_hide_hunter_yellow"://Thick Hide Hunter
        AddLayer("TRIGGER", $defPlayer, $defendingCard, $i);
        break;
      case "apex_bonebreaker":
        $num6Block = 0;
        for ($j = CombatChainPieces(); $j < count($combatChain); $j += CombatChainPieces()) {
          if (ModifiedAttackValue($combatChain[$j], $defPlayer, "CC", "apex_bonebreaker") >= 6) ++$num6Block;
        }
        if ($num6Block) {
          AddLayer("TRIGGER", $defPlayer, $defendingCard, $i);
        }
        break;
      case "defender_of_daybreak_red":
      case "defender_of_daybreak_yellow":
      case "defender_of_daybreak_blue":
        if (TalentContains($combatChain[0], "SHADOW", $mainPlayer)) AddCurrentTurnEffect($defendingCard, $defPlayer);
        break;
      case "attune_with_cosmic_vibrations_blue":
        if (!IsAllyAttacking()) {
          $deck = new Deck($mainPlayer);
          if ($deck->Reveal(1) && PitchValue($deck->Top()) == 3) {
            AddLayer("TRIGGER", $defPlayer, $defendingCard, $i);
          }
        }
        break;
      case "battlefront_bastion_blue": // Battlefront Bastion
      case "battlefront_bastion_red":
      case "battlefront_bastion_yellow":
      case "battlefront_bastion_blue":
        if (NumCardsBlocking() <= 1) AddLayer("TRIGGER", $defPlayer, $defendingCard, $i);
        break;
      case "face_purgatory":
        $conditionsMet = 0;
        for ($j = CombatChainPieces(); $j < count($combatChain); $j += CombatChainPieces()) {
          if (CardType($combatChain[$j]) == "AA") {
            ++$conditionsMet; 
            break;
          }
        }
        for ($k = CombatChainPieces(); $k < count($combatChain); $k += CombatChainPieces()) {
          if (DelimStringContains(CardType($combatChain[$k]), "A")) {
            ++$conditionsMet; 
            break;
          }
        }
        if ($conditionsMet == 2 && !IsAllyAttacking()) {
          AddLayer("TRIGGER", $defPlayer, $defendingCard, $i);
        }
        break;
      case "ten_foot_tall_and_bulletproof_red":
        AddNextTurnEffect($defendingCard, $defPlayer);
        break;
      case "quickdodge_flexors":
        $char = &GetPlayerCharacter($defPlayer);
        for ($j = 0; $j < count($char); $j += CharacterPieces()) {
          if ($char[$j] == $defendingCard) $char[$j+7] = "1";
        }
        break;
      default:
        break;
    }
  }
  if ($blockedFromHand > 0 && SearchCharacterActive($mainPlayer, "mark_of_lightning", true) && (TalentContains($combatChain[0], "LIGHTNING", $mainPlayer) || TalentContains($combatChain[0], "ELEMENTAL", $mainPlayer))) {
    AddLayer("TRIGGER", $mainPlayer, "mark_of_lightning");
  }
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $defPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "spiders_bite":
        case "spiders_bite":
          $count = ModifyBlockForType("AA", 0);
          $remove = $count > 0;
          break;
        case "nerve_scalpel":
        case "nerve_scalpel":
          $count = ModifyBlockForType("AR", 0); //DR could not possibly be blocking at this time, see OnDefenseReactionResolveEffects
          $remove = $count > 0;
          break;
        case "orbitoclast":
        case "orbitoclast":
          $count = ModifyBlockForType("A", 0);
          $remove = $count > 0;
          break;
        case "scale_peeler":
        case "scale_peeler":
          $count = ModifyBlockForType("E", 0);
          $remove = $count > 0;
          break;
        default:
          break;
      }
    }
    if ($currentTurnEffects[$i + 1] == $mainPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "call_down_the_lightning_yellow":
          if ($blockedFromHand >= 1) CallDownLightning();
          break;
        default:
          break;
      }
    }
    if ($remove) RemoveCurrentTurnEffect($i);
  }
}

function GetDefendingCardsFromCombatChainLink($chainLink, $defPlayer)
{
  // returns array of equipments played by the defending hero which is still on the chain
  $defendingCards = array();
  for ($i = 0; $i < count($chainLink); $i += ChainLinksPieces()) {
    if ($chainLink[$i + 2] == 1 && $chainLink[$i + 1] == $defPlayer) {
      array_push($defendingCards, $chainLink[$i]);
    }
  }
  return $defendingCards;
}

function BeginningReactionStepEffects()
{
  global $combatChain, $mainPlayer, $CombatChain;
  if (!$CombatChain->HasCurrentLink()) return "";
  switch ($combatChain[0]) {
    case "cyclone_roundhouse_yellow":
      if (ComboActive()) {
        AddLayer("TRIGGER", $mainPlayer, "cyclone_roundhouse_yellow");
      }
  }
}

function ModifyBlockForType($type, $amount)
{
  global $combatChain, $defPlayer;
  $count = 0;
  for ($i = count($combatChain) - CombatChainPieces(); $i > 0; $i -= CombatChainPieces()) {
    WriteLog($combatChain[$i] . "-" . CardTypeExtended($combatChain[$i]));
    if ($combatChain[$i + 1] != $defPlayer) continue;
    if (!DelimStringContains(CardTypeExtended($combatChain[$i]), $type)) continue;
    ++$count;
    $combatChain[$i + 6] += $amount;
    if ($type == "DR") return $count;
  }
  return $count;
}

function OnBlockEffects($index, $from)
{
  global $currentTurnEffects, $CombatChain, $currentPlayer, $combatChainState, $CCS_WeaponIndex, $defPlayer;
  global $Card_BlockBanner;
  $chainCard = $CombatChain->Card($index);
  $cardType = CardType($chainCard->ID());
  $cardSubtype = CardSubType($chainCard->ID());
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "flic_flak_red":
        case "flic_flak_yellow":
        case "flic_flak_blue":
          if (HasCombo($chainCard->ID())) $chainCard->ModifyDefense(2);
          $remove = true;
          break;
        case "endless_winter_red":
          if ($cardType == "DR") PlayAura("frostbite", $currentPlayer, effectController: $otherPlayer);
          break;
        case "withstand_red":
        case "withstand_yellow":
        case "withstand_blue":
          if (ClassContains($chainCard->ID(), "GUARDIAN", $currentPlayer) && CardSubType($chainCard->ID()) == "Off-Hand") {
            if ($currentTurnEffects[$i] == "withstand_red") $amount = 6;
            else if ($currentTurnEffects[$i] == "withstand_yellow") $amount = 5;
            else $amount = 4;
            $chainCard->ModifyDefense($amount);
            $remove = true;
          }
          break;
        case "spiders_bite":
        case "spiders_bite":
          if ($cardType == "AA") $chainCard->ModifyDefense(-1);
          break;
        case "nerve_scalpel":
        case "nerve_scalpel":
          if ($cardType == "AR") $chainCard->ModifyDefense(-1);
          break;
        case "orbitoclast":
        case "orbitoclast":
          if (DelimStringContains($cardType, "A")) $chainCard->ModifyDefense(-1);
          $splitCard = explode("_", $chainCard->ID());
          if ($splitCard[count($splitCard) - 1] == "equip") {
            // TODO, check for bugs here
            $id = implode("_", array_splice($splitCard, 0, count($splitCard) - 1));
            if (CardType($id) != $cardType) $chainCard->ModifyDefense(-1);
          }
          break;
        case "scale_peeler":
        case "scale_peeler":
          if ($cardType == "E" || DelimStringContains($cardSubtype, "Evo")) $chainCard->ModifyDefense(-1);
          break;
        case $Card_BlockBanner:
          if (DelimStringContains($cardType, "A") || $cardType == "AA") {
            $chainCard->ModifyDefense(1);
            $remove = true;
          }
          break;
        default:
          break;
      }
    } else if ($currentTurnEffects[$i + 1] == $otherPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "plow_through_red":
        case "plow_through_yellow":
        case "plow_through_blue":
          if ($cardType == "AA" && NumAttacksBlocking() == 1) {
            AddCharacterEffect($otherPlayer, $combatChainState[$CCS_WeaponIndex], $currentTurnEffects[$i]);
            WriteLog(CardLink($currentTurnEffects[$i], $currentTurnEffects[$i]) . " gives your weapon +1 for the rest of the turn");
          }
          break;
        default:
          break;
      }
    }
    if ($remove) RemoveCurrentTurnEffect($i);
  }
  $currentTurnEffects = array_values($currentTurnEffects);
  switch ($CombatChain->AttackCard()->ID()) {
    case "cintari_saber":
    case "cintari_saber":
      if ($cardType == "AA" && NumAttacksBlocking() == 1) {
        AddCharacterEffect($otherPlayer, $combatChainState[$CCS_WeaponIndex], $CombatChain->AttackCard()->ID());
        WriteLog(CardLink($CombatChain->AttackCard()->ID(), $CombatChain->AttackCard()->ID()) . " got +1 for the rest of the turn.");
      }
      break;
    default:
      break;
  }
  switch ($CombatChain->Card($index)->ID()) {
    case "headliner_helm":
    case "stadium_centerpiece":
    case "ticket_puncher":
    case "grandstand_legplates":
    case "bloodied_oval":
      AddCurrentTurnEffect($CombatChain->Card($index)->ID(), $defPlayer);
      break;
    default:
      break;
  }
}

function CombatChainCloseAbilities($player, $cardID, $chainLink)
{
  global $chainLinkSummary, $mainPlayer, $defPlayer, $chainLinks;
  switch ($cardID) {
    case "swing_big_red":
      if (SearchCurrentTurnEffects($cardID, $mainPlayer, true) && $chainLinkSummary[$chainLink * ChainLinkSummaryPieces()] == 0 && $chainLinks[$chainLink][0] == $cardID && $chainLinks[$chainLink][1] == $player) {
        PlayAura("quicken", $defPlayer);
      }
      break;
    case "that_all_you_got_yellow":
      $AttackPowerValue = $chainLinkSummary[$chainLink * ChainLinkSummaryPieces() + 1];
      if ($AttackPowerValue <= 2) {
        Draw($player);
        WriteLog(CardLink($cardID, $cardID) . " drew a card");
      }
      break;
    case "regicide_blue":
      if ($player == $mainPlayer) PlayerLoseHealth($mainPlayer, GetHealth($mainPlayer));
      break;
    default:
      break;
  }
}

function NumNonEquipmentDefended()
{
  global $combatChain, $defPlayer;
  $number = 0;
  for ($i = 0; $i < count($combatChain); $i += CombatChainPieces()) {
    $cardType = CardType($combatChain[$i]);
    if ($combatChain[$i + 1] == $defPlayer && $cardType != "E" && $cardType != "C" && !DelimStringContains(CardSubType($combatChain[$i]), "Evo")) ++$number;
  }
  return $number;
}

function NumCardsDefended()
{
  global $combatChain, $defPlayer;
  $number = 0;
  for ($i = 0; $i < count($combatChain); $i += CombatChainPieces()) {
    if ($combatChain[$i + 1] == $defPlayer) ++$number;
  }
  return $number;
}

function CombatChainPlayAbility($cardID)
{
  global $combatChain, $defPlayer;
  for ($i = 0; $i < count($combatChain); $i += CombatChainPieces()) {
    switch ($combatChain[$i]) {
      case "sigil_of_parapets_blue":
        if (ClassContains($cardID, "WIZARD", $defPlayer)) {
          $combatChain[$i + 6] += 2;
          WriteLog(CardLink($combatChain[$i], $combatChain[$i]) . " gets +2 defense");
        }
        break;
      default:
        break;
    }
  }
}

function IsDominateActive()
{
  global $currentTurnEffects, $mainPlayer, $CCS_WeaponIndex, $combatChain, $combatChainState;
  global $CS_NumAuras, $CCS_NumBoosted, $chainLinks, $chainLinkSummary;
  if (count($combatChain) == 0) return false;
  if (SearchCurrentTurnEffectsForCycle("timidity_point_red", "timidity_point_yellow", "timidity_point_blue", $mainPlayer)) return false;
  $characterEffects = GetCharacterEffects($mainPlayer);
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    if ($currentTurnEffects[$i + 1] == $mainPlayer && IsCombatEffectActive($currentTurnEffects[$i]) && !IsCombatEffectLimited($i) && DoesEffectGrantDominate($currentTurnEffects[$i])) return true;
  }
  for ($i = 0; $i < count($characterEffects); $i += CharacterEffectPieces()) {
    if ($characterEffects[$i] == $combatChainState[$CCS_WeaponIndex]) {
      switch ($characterEffects[$i + 1]) {
        case "ironsong_determination_yellow":
          return true;
        default:
          break;
      }
    }
  }
  switch ($combatChain[0]) {
    case "open_the_center_red":
    case "open_the_center_yellow":
    case "open_the_center_blue":
      return (ComboActive() ? true : false);
    case "demolition_crew_red":
    case "demolition_crew_yellow":
    case "demolition_crew_blue":
      return true;
    case "arknight_ascendancy_red":
      return true;
    case "herald_of_erudition_yellow":
      return true;
    case "herald_of_tenacity_red":
    case "herald_of_tenacity_yellow":
    case "herald_of_tenacity_blue":
      return true;
    case "nourishing_emptiness_red":
      return SearchDiscard($mainPlayer, "AA") == "";
    case "overload_red":
    case "overload_yellow":
    case "overload_blue":
      return true;
    case "thump_red":
    case "thump_yellow":
    case "thump_blue":
      return HasIncreasedAttack();
    case "macho_grande_red":
    case "macho_grande_yellow":
    case "macho_grande_blue":
      return true;
    case "break_tide_yellow":
      return (ComboActive() ? true : false);
    case "payload_red":
    case "payload_yellow":
    case "payload_blue":
      return $combatChainState[$CCS_NumBoosted] > 0;
    case "drowning_dire_red":
    case "drowning_dire_yellow":
    case "drowning_dire_blue":
      return GetClassState($mainPlayer, $CS_NumAuras) > 0;
    case "fractal_replication_red":
      $hasDominate = false;
      for ($i = 0; $i < count($chainLinks); ++$i) {
        for ($j = 0; $j < count($chainLinks[$i]); $j += ChainLinksPieces()) {
          $isIllusionist = ClassContains($chainLinks[$i][$j], "ILLUSIONIST", $mainPlayer) || ($j == 0 && DelimStringContains($chainLinkSummary[$i * ChainLinkSummaryPieces() + 3], "ILLUSIONIST"));
          if ($chainLinks[$i][$j + 2] == "1" && $chainLinks[$i][$j] != "fractal_replication_red" && $isIllusionist && CardType($chainLinks[$i][$j]) == "AA") {
            if (!$hasDominate) $hasDominate = HasDominate($chainLinks[$i][$j]);
          }
        }
      }
      return $hasDominate;
    case "isolate_red":
    case "isolate_yellow":
    case "isolate_blue":
      return true;
    default:
      break;
  }
  return false;
}

function IsOverpowerActive()
{
  global $combatChain, $mainPlayer, $defPlayer, $currentTurnEffects, $CS_Num6PowBan, $CS_NumItemsDestroyed, $CS_NumAuras;
  if (count($combatChain) == 0) return false;
  if (SearchItemsForCard("overload_script_red", $mainPlayer) != "" && CardType($combatChain[0]) == "AA" && ClassContains($combatChain[0], "MECHANOLOGIST", $mainPlayer)) {
    return true;
  }
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
    if ($currentTurnEffects[$i + 1] == $mainPlayer && IsCombatEffectActive($currentTurnEffects[$i]) && !IsCombatEffectLimited($i) && DoesEffectGrantOverpower($currentTurnEffects[$i])) return true;
    if ($currentTurnEffects[$i + 1] == $mainPlayer && $currentTurnEffects[$i] == "double_down_red" && CachedWagerActive()) return true;
  }
  switch ($combatChain[0]) {
    case "merciless_battleaxe":
      return SearchCurrentTurnEffects("merciless_battleaxe", $mainPlayer);
    case "hanabi_blaster":
      return true;
    case "spectral_rider_red":
    case "spectral_rider_yellow":
    case "spectral_rider_blue":
      return SearchCurrentTurnEffects("spectral_rider_red", $mainPlayer);
    case "nitro_mechanoida":
      return true;
    case "glaring_impact_red":
    case "glaring_impact_yellow":
    case "glaring_impact_blue":
      return SearchCurrentTurnEffects($combatChain[0], $mainPlayer);
    case "wall_breaker_red":
    case "wall_breaker_yellow":
    case "wall_breaker_blue":
      return GetClassState($mainPlayer, $CS_Num6PowBan) > 0;
    case "annihilator_engine_red":
    case "terminator_tank_red":
    case "war_machine_red":
      return EvoUpgradeAmount($mainPlayer) >= 3;
    case "hydraulic_press_red":
    case "hydraulic_press_yellow":
    case "hydraulic_press_blue":
      return SearchCurrentTurnEffects($combatChain[0], $mainPlayer);
    case "moonshot_yellow":
      return CachedTotalAttack() >= 10;
    case "torque_tuned_red":
    case "torque_tuned_yellow":
    case "torque_tuned_blue":
      return GetClassState($mainPlayer, $CS_NumItemsDestroyed) > 0;
    case "bull_bar_red":
    case "bull_bar_yellow":
    case "bull_bar_blue":
      return SearchItemsByName($mainPlayer, "Hyper Driver") != "";
    case "over_the_top_red":
    case "over_the_top_yellow":
    case "over_the_top_blue":
      return HasIncreasedAttack();
    case "pay_up_red":
      return CountItem("gold", $defPlayer) > 0;
    case "vantage_point_red":
    case "vantage_point_yellow":
    case "vantage_point_blue":
      return GetClassState($mainPlayer, $CS_NumAuras) > 0;
    default:
      break;
  }
  return false;
}

function CanBlock($cardID)//checks if a block is legal, used for cards that force blocks
{
  // TODO;
  return true;
}

function IsWagerActive()
{
  global $combatChainState, $CCS_WagersThisLink;
  return intval($combatChainState[$CCS_WagersThisLink]) > 0;
}

function IsFusionActive()
{
  global $combatChainState, $CCS_AttackFused;
  return intval($combatChainState[$CCS_AttackFused]) > 0;
}

function CombatChainClosedTriggers()
{
  global $chainLinks, $mainPlayer, $defPlayer, $CS_HealthLost, $currentTurnEffects;
  for ($i = 0; $i < count($chainLinks); ++$i) {
    for ($j = 0; $j < count($chainLinks[$i]); $j += ChainLinksPieces()) {
      if ($chainLinks[$i][$j + 1] != $mainPlayer) continue;
      switch ($chainLinks[$i][$j]) {
        case "hell_hammer":
          $index = FindCharacterIndex($mainPlayer, "hell_hammer");
          if ($index > -1 && SearchCurrentTurnEffects("hell_hammer", $mainPlayer, true)) {
            BanishCardForPlayer("hell_hammer", $mainPlayer, "CC");
            DestroyCharacter($mainPlayer, $index, true);
          }
          break;
        case "widespread_annihilation_blue":
          if (GetClassState($mainPlayer, $CS_HealthLost) > 0) MZChooseAndBanish($mainPlayer, "MYHAND", "ARS,-");
          if (GetClassState($defPlayer, $CS_HealthLost) > 0) MZChooseAndBanish($defPlayer, "MYHAND", "ARS,-");
          break;
        case "widespread_destruction_yellow":
          if (GetClassState($mainPlayer, $CS_HealthLost) > 0) MZChooseAndBanish($mainPlayer, "MYARS", "ARS,-");
          if (GetClassState($defPlayer, $CS_HealthLost) > 0) MZChooseAndBanish($defPlayer, "MYARS", "ARS,-");
          break;
        case "widespread_ruin_red":
          if (GetClassState($mainPlayer, $CS_HealthLost) > 0) {
            $deck = new Deck($mainPlayer);
            $deck->BanishTop();
          }
          if (GetClassState($defPlayer, $CS_HealthLost) > 0) {
            $deck = new Deck($defPlayer);
            $deck->BanishTop();
          }
          break;
        case "deathly_wail_red":
        case "deathly_wail_yellow":
        case "deathly_wail_blue":
          $numRunechant = 0;
          if (GetClassState($mainPlayer, $CS_HealthLost) > 0) ++$numRunechant;
          if (GetClassState($defPlayer, $CS_HealthLost) > 0) ++$numRunechant;
          if ($numRunechant > 0) PlayAura("runechant", $mainPlayer, $numRunechant, effectSource: $chainLinks[$i][$j]);
          break;
        case "deathly_delight_red":
        case "deathly_delight_yellow":
        case "deathly_delight_blue":
          $numLife = 0;
          if (GetClassState($mainPlayer, $CS_HealthLost) > 0) ++$numLife;
          if (GetClassState($defPlayer, $CS_HealthLost) > 0) ++$numLife;
          if ($numLife > 0) GainHealth($numLife, $mainPlayer);
          break;
        case "eloquent_eulogy_red":
          $numEloquence = 0;
          if (GetClassState($mainPlayer, $CS_HealthLost) > 0) ++$numEloquence;
          if (GetClassState($defPlayer, $CS_HealthLost) > 0) ++$numEloquence;
          if ($numEloquence > 0) PlayAura("eloquence", $mainPlayer);
          break;
        default:
          break;
      }
    }
  }
  for ($i = count($currentTurnEffects) - CurrentTurnEffectPieces(); $i >= 0; $i -= CurrentTurnEffectPieces()) {
    if (!isset($currentTurnEffects[$i + 1])) continue;
    if (explode("-", $currentTurnEffects[$i])[0] == "kunai_of_retribution" && $currentTurnEffects[$i + 1] == $mainPlayer) {
      $uniqueID = explode("-", $currentTurnEffects[$i])[1];
      $index = FindCharacterIndexUniqueID($mainPlayer, $uniqueID);
      if ($index != -1) DestroyCharacter($mainPlayer, $index);
    }
  }
}


function CacheCombatResult()
{
  global $combatChain, $combatChainState, $CCS_CachedTotalAttack, $CCS_CachedTotalBlock, $CCS_CachedDominateActive, $CCS_CachedOverpowerActive;
  global $CSS_CachedNumActionBlocked, $CCS_CachedNumDefendedFromHand, $CCS_PhantasmThisLink, $CCS_AttackFused, $CCS_WagersThisLink;
  if (count($combatChain) == 0) return;
  $combatChainState[$CCS_CachedTotalAttack] = 0;
  $combatChainState[$CCS_CachedTotalBlock] = 0;
  EvaluateCombatChain($combatChainState[$CCS_CachedTotalAttack], $combatChainState[$CCS_CachedTotalBlock], secondNeedleCheck:true);
  $combatChainState[$CCS_CachedDominateActive] = (IsDominateActive() ? "1" : "0");
  $combatChainState[$CCS_CachedOverpowerActive] = (IsOverpowerActive() ? "1" : "0");
  $combatChainState[$CSS_CachedNumActionBlocked] = NumActionsBlocking();
  if ($combatChainState[$CCS_CachedNumDefendedFromHand] == 0) $combatChainState[$CCS_CachedNumDefendedFromHand] = NumDefendedFromHand();
  $combatChainState[$CCS_WagersThisLink] = (IsWagerActive() ? intval($combatChainState[$CCS_WagersThisLink]) : "0");
  $combatChainState[$CCS_PhantasmThisLink] = (IsPhantasmActive() ? "1" : "0");
  $combatChainState[$CCS_AttackFused] = (IsFusionActive() ? "1" : "0");
}

function CachedTotalAttack()
{
  global $combatChainState, $CCS_CachedTotalAttack;
  return $combatChainState[$CCS_CachedTotalAttack];
}

function CachedTotalBlock()
{
  global $combatChainState, $CCS_CachedTotalBlock;
  return $combatChainState[$CCS_CachedTotalBlock];
}

function CachedDominateActive()
{
  global $combatChainState, $CCS_CachedDominateActive;
  return ($combatChainState[$CCS_CachedDominateActive] == "1" ? true : false);
}

function CachedOverpowerActive()
{
  global $combatChainState, $CCS_CachedOverpowerActive;
  return ($combatChainState[$CCS_CachedOverpowerActive] == "1" ? true : false);
}

function CachedWagerActive()
{
  global $combatChainState, $CCS_WagersThisLink;
  if (isset($combatChainState[$CCS_WagersThisLink])) {
    return ($combatChainState[$CCS_WagersThisLink] >= "1" ? true : false);
  } else return false;
}

function CachedFusionActive()
{
  global $combatChainState, $CCS_AttackFused;
  if (isset($combatChainState[$CCS_AttackFused])) {
    return ($combatChainState[$CCS_AttackFused] == "1" ? true : false);
  } else return false;
}

function CachedPhantasmActive()
{
  global $combatChainState, $CCS_PhantasmThisLink;
  if (isset($combatChainState[$CCS_PhantasmThisLink])) {
    return ($combatChainState[$CCS_PhantasmThisLink] == "1" ? true : false);
  } else return false;
}

function CachedNumDefendedFromHand() //Reprise
{
  global $combatChainState, $CCS_CachedNumDefendedFromHand;
  return $combatChainState[$CCS_CachedNumDefendedFromHand];
}

function CachedNumActionBlocked()
{
  global $combatChainState, $CSS_CachedNumActionBlocked;
  return $combatChainState[$CSS_CachedNumActionBlocked];
}

function IsPiercingActive($cardID)
{
  global $CombatChain, $currentTurnEffects, $mainPlayer;
  if ($CombatChain->HasCurrentLink()) {
    if (HasPiercing($cardID)) return true;
    for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
      if (!isset($currentTurnEffects[$i + 1])) continue;
      if ($currentTurnEffects[$i + 1] == $mainPlayer && HasPiercing($currentTurnEffects[$i])) return true;
    }
  }
  return false;
}

function IsTowerActive()
{
  global $combatChain;
  return (CachedTotalAttack() >= 13 && HasTower($combatChain[0]));
}
