<?php

function HNTAbilityType($cardID): string
{
  return match ($cardID) {
    "arakni_black_widow" => "AR",
    "arakni_funnel_web" => "AR",
    "arakni_orbweaver" => "I",
    "arakni_redback" => "AR",
    "arakni_tarantula" => "AR",
    "hunters_klaive" => "AA",
    "mark_of_the_huntsman" => "AA",
    "graphene_chelicera" => "AA",
    "cindra_dracai_of_retribution" => "I",
    "cindra" => "I",
    "kunai_of_retribution" => "AA",
    "obsidian_fire_vein" => "AA",
    "dragonscaler_flight_path" => "I",
    "vow_of_vengeance" => "AR",
    "heart_of_vengeance" => "I",
    "hand_of_vengeance" => "AR",
    "path_of_vengeance" => "AR",
    "coat_of_allegiance" => "A",
    "fealty" => "I",
    "danger_digits" => "AR",
    "starting_point" => "AR",
    "quickdodge_flexors" => "DR",
    "bunker_beard" => "DR",
    "imperial_seal_of_command_red" => "A",
    "tremorshield_sabatons" => "I",
    "misfire_dampener" => "I",
    "enchanted_quiver" => "I",
    "the_hand_that_pulls_the_strings" => "AR",
    default => ""
  };
}

function HNTAbilityCost($cardID): int
{
  global $currentPlayer, $mainPlayer;
  return match ($cardID) {
    "hunters_klaive" => 2,
    "mark_of_the_huntsman" => 2,
    "graphene_chelicera" => 1,
    "cindra_dracai_of_retribution" => 3 - ($mainPlayer == $currentPlayer ? NumDraconicChainLinks() : 0),
    "cindra" => 3 - ($mainPlayer == $currentPlayer ? NumDraconicChainLinks() : 0),
    "kunai_of_retribution" => 1,
    "obsidian_fire_vein" => 1,
    "dragonscaler_flight_path" => 3 - ($mainPlayer == $currentPlayer ? NumDraconicChainLinks() : 0),
    "quickdodge_flexors" => 1,
    default => 0
  };
}

function HNTAbilityHasGoAgain($cardID): bool
{
  return match ($cardID) {
    "coat_of_allegiance" => true,
    "imperial_seal_of_command_red" => true,
    default => false,
  };
}

function HNTEffectAttackModifier($cardID): int
{
  global $currentPlayer, $defPlayer;
  $otherPlayer = $currentPlayer == 1 ? 2 : 1;
  return match ($cardID) {
    "arakni_black_widow" => 3,
    "arakni_funnel_web" => 3,
    "arakni_orbweaver" => 3,
    "arakni_redback" => 3,
    "arakni_tarantula" => 3,
    "take_up_the_mantle_yellow" => 2,
    "take_up_the_mantle_yellow-FULL" => 3,
    "tarantula_toxin_red" => 3,
    "stains_of_the_redback_red" => 3,
    "stains_of_the_redback_yellow" => 2,
    "stains_of_the_redback_blue" => 1,
    "orbweaver_spinneret_red" => 3,
    "orbweaver_spinneret_yellow" => 2,
    "orbweaver_spinneret_blue" => 1,
    "two_sides_to_the_blade_red-DAGGER" => 3,
    "two_sides_to_the_blade_red-ATTACK" => 3,
    "wrath_of_retribution_red" => 1,
    "dragon_power_red" => 3,
    "dragon_power_yellow" => 3,
    "dragon_power_blue" => 3,
    "fire_tenet_strike_first_red" => 1,
    "fire_tenet_strike_first_yellow" => 1,
    "fire_tenet_strike_first_blue" => 1,
    "obsidian_fire_vein" => 1,
    "long_whisker_loyalty_red-BUFF" => 2,
    "affirm_loyalty_red" => 2,
    "endear_devotion_red" => 3,
    "fire_and_brimstone_red" => 1,
    "blistering_blade_red" => NumDraconicChainLinks() > 1 ? 3 : 2,
    "brothers_of_flame_red" => 4,
    "dynastic_dedication_red" => 3,
    "imperial_intent_red" => 2,
    "scalding_iron_red" => NumDraconicChainLinks(),
    "searing_gaze_red" => 2,
    "sisters_of_fire_red" => 3,
    "sizzling_steel_red" => NumDraconicChainLinks() > 1 ? 4 : 3,
    "stabbing_pain_red" => 3,
    "diced_red" => 3,
    "diced_yellow" => 2,
    "diced_blue" => 1,
    "twist_and_turn_red" => 4,
    "twist_and_turn_yellow" => 3,
    "twist_and_turn_blue" => 2,
    "power_stance_blue" => 1,
    "cut_deep_red" => 4,
    "cut_deep_yellow" => 3,
    "cut_deep_blue" => 2,
    "hunt_a_killer_red" => 4,
    "hunt_a_killer_yellow" => 3,
    "hunt_a_killer_blue" => 2,
    "knife_through_butter_red-BUFF" => 4,
    "knife_through_butter_yellow-BUFF" => 3,
    "knife_through_butter_blue-BUFF" => 2,
    "point_of_engagement_red-NEXTDAGGER" => 3,
    "point_of_engagement_red-MARKEDBUFF" => 1,
    "point_of_engagement_yellow-NEXTDAGGER" => 2,
    "point_of_engagement_yellow-MARKEDBUFF" => 1,
    "point_of_engagement_blue-NEXTDAGGER" => 1,
    "point_of_engagement_blue-MARKEDBUFF" => 1,
    "sworn_vengeance_red" => 3,
    "sworn_vengeance_yellow" => 2,
    "sworn_vengeance_blue" => 1,
    "hand_of_vengeance" => 1,
    "rake_over_the_coals_red" => 1,
    "tooth_of_the_dragon_red" => 3,
    "blessing_of_vynserakai_red" => 3,
    "up_sticks_and_run_red" => 4,
    "up_sticks_and_run_yellow" => 3,
    "up_sticks_and_run_blue" => 2,
    "savor_bloodshed_red" => 4,
    "cut_from_the_same_cloth_red" => 4,
    "cut_from_the_same_cloth_yellow" => 3,
    "cut_from_the_same_cloth_blue" => 2,
    "scar_tissue_red" => 3,
    "scar_tissue_yellow" => 2,
    "scar_tissue_blue" => 1,
    "take_a_stab_red" => 3,
    "take_a_stab_yellow" => 2,
    "take_a_stab_blue" => 1,
    "rotten_remains_blue" => 1,
    "dual_threat_yellow-AA" => 3,
    "dual_threat_yellow-WEAPON" => 3,
    "lay_low_yellow" => -1,
    "exposed_blue" => 1,
    "nip_at_the_heels_blue" => 1,
    "public_bounty_red" => 3,
    "public_bounty_yellow" => 2,
    "public_bounty_blue" => 1,
    "war_cry_of_themis_yellow" => 4,
    "war_cry_of_bellona_yellow-BUFF" => 2,
    "the_hand_that_pulls_the_strings" => IsRoyal($otherPlayer) ? 1 : 0,
    default => 0,
  };
}

function HNTCombatEffectActive($cardID, $attackID, $flicked = false): bool
{
  global $mainPlayer, $combatChainState, $CCS_WeaponIndex, $defPlayer, $combatChain;
  $dashArr = explode("-", $cardID);
  $cardID = $dashArr[0];
  if ($cardID == "long_whisker_loyalty_red" & count($dashArr) > 1) {
    if ($dashArr[1] == "BUFF") return SubtypeContains($attackID, "Dagger", $mainPlayer);
    if (DelimStringContains($dashArr[1], "MARK", true)) {
      $id = explode(",", $dashArr[1])[1];
      $character = &GetPlayerCharacter($mainPlayer);
      return $character[$combatChainState[$CCS_WeaponIndex] + 11] == $id;
    }
  }
  if ($cardID == "arakni_black_widow" && count($dashArr) > 1 && $dashArr[1] == "HIT") return HasStealth($attackID);
  if ($cardID == "arakni_funnel_web" && count($dashArr) > 1 && $dashArr[1] == "HIT") return HasStealth($attackID);
  if ($cardID == "fealty" && count($dashArr) > 1 && $dashArr[1] == "ATTACK") return DelimStringContains(CardType($attackID), "AA");
  if ($cardID == "dual_threat_yellow" && count($dashArr) > 1 && $dashArr[1] == "AA") return DelimStringContains(CardType($attackID), "AA");
  if ($cardID == "dual_threat_yellow" && count($dashArr) > 1 && $dashArr[1] == "WEAPON") return DelimStringContains(CardType($attackID), "W");
  if (($cardID == "knife_through_butter_red" || $cardID == "knife_through_butter_yellow" || $cardID == "knife_through_butter_blue") && count($dashArr) > 1 && $dashArr[1] == "BUFF") return SubtypeContains($attackID, "Dagger", $mainPlayer);
  if (($cardID == "point_of_engagement_red" || $cardID == "point_of_engagement_yellow" || $cardID == "point_of_engagement_blue") && count($dashArr) > 1) {
    switch ($dashArr[1]) {
      case "NEXTDAGGER":
        return SubtypeContains($attackID, "Dagger", $mainPlayer);
      case "MARKEDBUFF":
        return CheckMarked($defPlayer);
      default:
        break;
    }
  }
  if ($cardID == "imperial_seal_of_command_red" && count($dashArr) > 1 && $dashArr[1] == "HIT") return true;
  return match ($cardID) {
    "arakni_black_widow" => ClassContains($attackID, "ASSASSIN", $mainPlayer),
    "arakni_funnel_web" => ClassContains($attackID, "ASSASSIN", $mainPlayer),
    "arakni_orbweaver" => HasStealth($attackID),
    "arakni_redback" => ClassContains($attackID, "ASSASSIN", $mainPlayer),
    "arakni_tarantula" => true,
    "take_up_the_mantle_yellow" => HasStealth($attackID),
    "tarantula_toxin_red" => true,
    "stains_of_the_redback_red" => HasStealth($attackID),
    "stains_of_the_redback_yellow" => HasStealth($attackID),
    "stains_of_the_redback_blue" => HasStealth($attackID),
    "orbweaver_spinneret_red" => HasStealth($attackID),
    "orbweaver_spinneret_yellow" => HasStealth($attackID),
    "orbweaver_spinneret_blue" => HasStealth($attackID),
    "two_sides_to_the_blade_red" => true,
    "wrath_of_retribution_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "art_of_the_dragon_blood_red" => TalentContains($attackID, "DRACONIC", $mainPlayer),
    "art_of_the_dragon_claw_red" => TalentContains($attackID, "DRACONIC", $mainPlayer),
    "art_of_the_dragon_fire_red" => TalentContains($attackID, "DRACONIC", $mainPlayer),
    "art_of_the_dragon_scale_red" => TalentContains($attackID, "DRACONIC", $mainPlayer),
    "dragon_power_red" => true,
    "dragon_power_yellow" => true,
    "dragon_power_blue" => true,
    "fire_tenet_strike_first_red" => TalentContains($attackID, "DRACONIC", $mainPlayer),
    "fire_tenet_strike_first_yellow" => TalentContains($attackID, "DRACONIC", $mainPlayer),
    "fire_tenet_strike_first_blue" => TalentContains($attackID, "DRACONIC", $mainPlayer),
    "obsidian_fire_vein" => true,
    "hunts_end_red" => true,
    "affirm_loyalty_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "endear_devotion_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "fire_and_brimstone_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "blistering_blade_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "brothers_of_flame_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "dynastic_dedication_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "imperial_intent_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "scalding_iron_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "searing_gaze_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "sisters_of_fire_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "sizzling_steel_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "stabbing_pain_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "diced_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "diced_yellow" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "diced_blue" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "twist_and_turn_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "twist_and_turn_yellow" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "twist_and_turn_blue" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "jagged_edge_red" => true,
    "agility_stance_yellow" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "power_stance_blue" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "cut_deep_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "cut_deep_yellow" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "cut_deep_blue" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "hunt_a_killer_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "hunt_a_killer_yellow" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "hunt_a_killer_blue" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "knife_through_butter_red" => true,
    "knife_through_butter_yellow" => true,
    "knife_through_butter_blue" => true,
    "sworn_vengeance_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "sworn_vengeance_yellow" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "sworn_vengeance_blue" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "dragonscaler_flight_path" => TalentContains($attackID, "DRACONIC", $mainPlayer),
    "vow_of_vengeance" => true,
    "hand_of_vengeance" => true,
    "path_of_vengeance" => true,
    "rake_over_the_coals_red" => TalentContains($attackID, "DRACONIC", $mainPlayer),
    "blessing_of_vynserakai_red" => true,
    "tooth_of_the_dragon_red" => TalentContains($attackID, "DRACONIC", $mainPlayer),
    "up_sticks_and_run_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "up_sticks_and_run_yellow" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "up_sticks_and_run_blue" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "poisoned_blade_red" => SubtypeContains($attackID, "Dagger", $mainPlayer) || $flicked,
    "poisoned_blade_yellow" => SubtypeContains($attackID, "Dagger", $mainPlayer) || $flicked,
    "poisoned_blade_blue" => SubtypeContains($attackID, "Dagger", $mainPlayer) || $flicked,
    "savor_bloodshed_red" => SubtypeContains($attackID, "Dagger", $mainPlayer) || $flicked,
    "cut_from_the_same_cloth_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "cut_from_the_same_cloth_yellow" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "cut_from_the_same_cloth_blue" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "scar_tissue_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "scar_tissue_yellow" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "scar_tissue_blue" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "take_a_stab_red" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "take_a_stab_yellow" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "take_a_stab_blue" => SubtypeContains($attackID, "Dagger", $mainPlayer),
    "rotten_remains_blue" => true,
    "lay_low_yellow" => true,
    "exposed_blue" => true,
    "nip_at_the_heels_blue" => true,
    "trot_along_blue" => AttackValue($attackID, base:true) <= 3,
    "public_bounty_red" => true,
    "public_bounty_yellow" => true,
    "public_bounty_blue" => true,
    "retrace_the_past_blue" => true,
    "war_cry_of_themis_yellow" => SubtypeContains($attackID, "Angel", $mainPlayer),
    "war_cry_of_bellona_yellow" => CardNameContains($attackID, "Raydn", $mainPlayer, true),
    "the_hand_that_pulls_the_strings" => ContractType($attackID) != "",
    default => false,
  };
}

function HNTPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = ""): string
{
  global $currentPlayer, $CS_ArcaneDamagePrevention, $CS_NumSeismicSurgeDestroyed, $CombatChain, $CS_NumRedPlayed, $CS_AtksWWeapon, $CS_NumAttackCards;
  global $CS_NumNonAttackCards, $CS_NumBoosted, $combatChain, $CS_AdditionalCosts, $CS_DamageDealtToOpponent, $combatChainState, $CCS_WeaponIndex;
  global $chainLinks;
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  switch ($cardID) {
    case "arakni_black_widow":
    case "arakni_funnel_web":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      if (HasStealth($CombatChain->AttackCard()->ID())) AddCurrentTurnEffect("$cardID-HIT", $currentPlayer);
      break;
    case "arakni_orbweaver":
      EquipWeapon($currentPlayer, "graphene_chelicera");
      AddCurrentTurnEffectNextAttack($cardID, $currentPlayer);
      break;
    case "arakni_redback":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      if (HasStealth($CombatChain->AttackCard()->ID())) GiveAttackGoAgain();
      break;
    case "arakni_tarantula":
      AddCurrentTurnEffect("arakni_tarantula", $currentPlayer);
      break;
    case "under_the_trapdoor_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I") {
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:subtype=Trap");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        AddDecisionQueue("BANISHCARD", $currentPlayer, "DISCARD,TT", 1);
        AddDecisionQueue("UNDERTRAPDOOR", $currentPlayer, "<-", 1);
      }
      break;
    case "take_up_the_mantle_yellow":
      global $CombatChain;
      if (IsHeroAttackTarget() && CheckMarked($otherPlayer)) {
        AddCurrentTurnEffect("$cardID-FULL", $currentPlayer);
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:hasStealth=1");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "1", 1);
        AddDecisionQueue("MZOP", $currentPlayer, "GETCARDID", 1);
        AddDecisionQueue("CURRENTATTACKBECOMES", $currentPlayer, "-", 1);
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "{1}", 1);
        AddDecisionQueue("MZBANISH", $currentPlayer, "-", 1);
        AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      }
      else AddCurrentTurnEffect("$cardID", $currentPlayer);
      break;
    case "tarantula_toxin_red":
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts, 1);
      AddDecisionQueue("MODAL", $currentPlayer, "TARANTULATOXIN", 1);
      break;
    case "anaphylactic_shock_blue":
      if (GetClassState($otherPlayer, $CS_DamageDealtToOpponent)) LoseHealth(1, $otherPlayer);
      $allies = GetAllies($otherPlayer);
      for ($j = 0; $j < count($allies); $j += AllyPieces()) {
        if ($allies[$j + 10] > 0) --$allies[$j+2];
        if ($allies[$j+2] == 0) DestroyAlly($otherPlayer, $j);
      }
      break;
    case "bite_red":
    case "bite_yellow":
    case "bite_blue":
      if (IsHeroAttackTarget())
      {
        ThrowWeapon("Dagger", $cardID, true);
      }
      break;
    case "whittle_from_bone_red":
    case "whittle_from_bone_yellow":
    case "whittle_from_bone_blue":
      if (IsHeroAttackTarget() && CheckMarked($otherPlayer)) EquipWeapon($currentPlayer, "graphene_chelicera", $cardID);
      break;
    case "stains_of_the_redback_red":
    case "stains_of_the_redback_yellow":
    case "stains_of_the_redback_blue":
      GiveAttackGoAgain();
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "orbweaver_spinneret_red":
    case "orbweaver_spinneret_yellow":
    case "orbweaver_spinneret_blue":
      EquipWeapon($currentPlayer, "graphene_chelicera", $cardID);
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "reapers_call_red":
    case "reapers_call_yellow":
    case "reapers_call_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I") {
        MarkHero($otherPlayer);
      }
      break;
    case "scuttle_the_canal_red":
    case "scuttle_the_canal_yellow":
    case "scuttle_the_canal_blue":
      if (IsHeroAttackTarget() && CheckMarked($otherPlayer)) GiveAttackGoAgain();
      break;
    case "two_sides_to_the_blade_red":
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts, 1);
      AddDecisionQueue("MODAL", $currentPlayer, "TWOSIDES", 1);
      break;
    case "graphene_chelicera":
      if (IsHeroAttackTarget() && CheckMarked($otherPlayer)) GiveAttackGoAgain();
      break;
    case "cindra_dracai_of_retribution":
    case "cindra":
      RecurDagger($currentPlayer, 0);
      RecurDagger($currentPlayer, 1);
      break;
    case "blood_runs_deep_red":
      AddLayer("TRIGGER", $currentPlayer, "blood_runs_deep_red");
      break;
    case "ignite_red":
      AddLayer("TRIGGER", $currentPlayer, "ignite_red");
      break;
    case "demonstrate_devotion_red":
    case "display_loyalty_red":
      if (NumDraconicChainLinks() >= 2) {
        GiveAttackGoAgain();
        if(IsHeroAttackTarget()) {
          PlayAura("fealty", $currentPlayer);
        }
      }
      break;
    case "wrath_of_retribution_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "art_of_the_dragon_blood_red":
      $uniqueID = $CombatChain->AttackCard()->UniqueID();
      if(TalentContains($cardID, "DRACONIC", $currentPlayer)) {
        AddCurrentTurnEffect("$cardID-$uniqueID", $currentPlayer);
      }
      break;
    case "art_of_the_dragon_claw_red":
      if(TalentContains($cardID, "DRACONIC", $currentPlayer)) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
      }
      break;
    case "art_of_the_dragon_fire_red":
      if(TalentContains($cardID, "DRACONIC", $currentPlayer)) {
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:type=C&THEIRCHAR:type=C&MYALLY&THEIRALLY", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a target to deal 2 damage");
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZDAMAGE", $currentPlayer, "2,DAMAGE," . $cardID, 1);
      }
      break;
    case "art_of_the_dragon_scale_red":
      if(TalentContains($cardID, "DRACONIC", $currentPlayer)) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
      }
      break;
    case "dragon_power_red":
    case "dragon_power_yellow":
    case "dragon_power_blue":
      if(TalentContains($cardID, "DRACONIC", $currentPlayer)) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
      }
      break;
    case "silver_talons_red":
    case "silver_talons_yellow":
    case "silver_talons_blue":
      if(TalentContains($cardID, "DRACONIC", $currentPlayer) && IsHeroAttackTarget()) {
        ThrowWeapon("Dagger", $cardID, true);
      }
      break;
    case "fire_tenet_strike_first_red":
    case "fire_tenet_strike_first_yellow":
    case "fire_tenet_strike_first_blue":
      AddCurrentTurnEffectNextAttack($cardID, $currentPlayer);
      break;
    case "hunts_end_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "long_whisker_loyalty_red":
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts, 1);
      AddDecisionQueue("MODAL", $currentPlayer, "LONGWHISKER", 1);
      break;
    case "affirm_loyalty_red":
    case "endear_devotion_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      if (NumDraconicChainLinks() >=2) PlayAura("fealty", $currentPlayer);
      break;
    case "fire_and_brimstone_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      $character = &GetPlayerCharacter($currentPlayer);
      $weaponIndex1 = CharacterPieces();
      $weaponIndex2 = CharacterPieces() * 2;
      if(SubtypeContains($character[$weaponIndex1], "Dagger")) AddCharacterUses($currentPlayer, $weaponIndex1, 1);
      if(SubtypeContains($character[$weaponIndex2], "Dagger")) AddCharacterUses($currentPlayer, $weaponIndex2, 1);
      break;
    case "blistering_blade_red":
    case "brothers_of_flame_red":
    case "dynastic_dedication_red":
    case "imperial_intent_red":
    case "scalding_iron_red":
    case "searing_gaze_red":
    case "sisters_of_fire_red":
    case "sizzling_steel_red":
    case "stabbing_pain_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "jagged_edge_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "provoke_blue":
      $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
      if (TypeContains($CombatChain->AttackCard()->ID(), "W", $currentPlayer) && CanRevealCards($otherPlayer) && !IsAllyAttacking()) {
        AddDecisionQueue("MULTIZONEINDICES", $otherPlayer, "MYHAND", 1);
        AddDecisionQueue("SETDQCONTEXT", $otherPlayer, "Choose a card from hand, action card will be blocked with, non-actions discarded");
        AddDecisionQueue("CHOOSEMULTIZONE", $otherPlayer, "<-", 1);
        AddDecisionQueue("PROVOKE", $otherPlayer, "-", 1);
      }
      break;
    case "diced_red":
    case "diced_yellow":
    case "diced_blue":
      AddCurrentTurnEffectNextAttack($cardID, $currentPlayer);
      break;
    case "twist_and_turn_red":
    case "twist_and_turn_yellow":
    case "twist_and_turn_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "cut_deep_red":
    case "cut_deep_yellow":
    case "cut_deep_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "hunt_a_killer_red":
    case "hunt_a_killer_yellow":
    case "hunt_a_killer_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "knife_through_butter_red":
    case "knife_through_butter_yellow":
    case "knife_through_butter_blue":
      AddCurrentTurnEffect($cardID."-BUFF", $currentPlayer);
      AddCurrentTurnEffect($cardID."-GOAGAIN", $currentPlayer);
      break;
    case "point_of_engagement_red":
    case "point_of_engagement_yellow":
    case "point_of_engagement_blue":
      AddCurrentTurnEffect($cardID."-NEXTDAGGER", $currentPlayer);
      AddCurrentTurnEffect($cardID."-MARKEDBUFF", $currentPlayer);
      break;
    case "sworn_vengeance_red":
    case "sworn_vengeance_yellow":
    case "sworn_vengeance_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "dragonscaler_flight_path":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      $type = TypeContains($CombatChain->AttackCard()->ID(), "W", $currentPlayer);
      $subtype = SubtypeContains($CombatChain->AttackCard()->ID(), "Ally", $currentPlayer);
      if($type) {
        $character = &GetPlayerCharacter($currentPlayer);
        ++$character[$combatChainState[$CCS_WeaponIndex] + 5];
        if($character[$combatChainState[$CCS_WeaponIndex] + 1] == 1) $character[$combatChainState[$CCS_WeaponIndex] + 1] = 2;
      }
      elseif ($subtype) {
        $ally = &GetAllies($currentPlayer);
        $allyIndex = SearchAlliesForUniqueID($combatChain[8], $currentPlayer);
        if($allyIndex != -1) {
          $ally[$allyIndex + 1] = 2;
        }
      }
      break;
    case "vow_of_vengeance":
      MarkHero($otherPlayer);
      break;
    case "heart_of_vengeance":
    case "path_of_vengeance":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "coat_of_allegiance":
      GainResources($currentPlayer, 1);
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "oath_of_loyalty_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "hunt_to_the_ends_of_rathe_red":
      $otherChar = &GetPlayerCharacter($otherPlayer);
      if (CardNameContains($otherChar[0], "Arakni")) {
        MarkHero($otherPlayer);
      }
      break;
    case "bubble_to_the_surface_red":
        $cardRemoved = BubbleToTheSurface();
        if($cardRemoved == "") { AddCurrentTurnEffect("bubble_to_the_surface_red-7", $currentPlayer); return "You cannot reveal cards."; }
        else {
          BanishCardForPlayer($cardRemoved, $currentPlayer, "DECK", "TT", "bubble_to_the_surface_red");
        }
      break;
    case "drop_of_dragon_blood_red":
      GainResources($currentPlayer, 1);
      Draw($currentPlayer, effectSource:$cardID);
      break;
    case "rake_over_the_coals_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      AddCurrentTurnEffect($cardID, $otherPlayer);
      break;
    case "for_the_dracai_red": case "for_the_emperor_red": case "for_the_realm_red":
      if(IsHeroAttackTarget() && CheckMarked($otherPlayer)) {
        PlayAura("fealty", $currentPlayer);
      }
      break;
    case "hunt_the_hunter_red":
      if(GetClassState($currentPlayer, $CS_NumRedPlayed) > 1 && IsHeroAttackTarget()){
        MarkHero($otherPlayer);
      }
      break;
    case "pledge_fealty_red":
      PlayAura("fealty", $currentPlayer);
      break;
    case "proclaim_vengeance_red":
      $otherChar = &GetPlayerCharacter($otherPlayer);
      MarkHero($otherPlayer);
      if (CardNameContains($otherChar[0], "Arakni")) {
        GainResources($currentPlayer, 1);
      }
      break;
    case "tooth_of_the_dragon_red":
      AddCurrentTurnEffectNextAttack($cardID, $currentPlayer);
      break;
    case "fealty":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "danger_digits":
      ThrowWeapon("Dagger", $cardID, target:$target);
      break;
    case "throw_dagger_blue":
      ThrowWeapon("Dagger", $cardID, onHitDraw: true, target:$target);
      break;
    case "up_sticks_and_run_red":
    case "up_sticks_and_run_yellow":
    case "up_sticks_and_run_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      Retrieve($currentPlayer, "Dagger");
      break;
    case "pick_up_the_point_red":
    case "pick_up_the_point_yellow":
    case "pick_up_the_point_blue":
      Retrieve($currentPlayer, "Dagger");
      break;
    case "poisoned_blade_red":
    case "poisoned_blade_yellow":
    case "poisoned_blade_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "throw_yourself_at_them_red":
    case "throw_yourself_at_them_yellow":
    case "throw_yourself_at_them_blue":
      if(IsHeroAttackTarget()) ThrowWeapon("Dagger", $cardID, true);
      break;
    case "starting_point":
      GiveAttackGoAgain();
      break;
    case "perforate_yellow":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:subtype=Dagger", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a dagger to attack an additional time and discount", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("EXTRAATTACK", $currentPlayer, "<-", 1);
      AddDecisionQueue("PERFORATE", $currentPlayer, "<-", 1);
      AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
      break;
    case "savor_bloodshed_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      AddCurrentTurnEffect("$cardID-HIT", $currentPlayer);
      break;
    case "cut_from_the_same_cloth_red":
    case "cut_from_the_same_cloth_yellow":
    case "cut_from_the_same_cloth_blue":     
      AddCurrentTurnEffect($cardID, $currentPlayer);
      AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
      AddDecisionQueue("REVEALHANDCARDS", $otherPlayer, "-", 1);
      AddDecisionQueue("IFTYPEREVEALED", $otherPlayer, "AR", 1);
      AddDecisionQueue("MARKHERO", $otherPlayer, "-", 1);
      break;
    case "scar_tissue_red":
    case "scar_tissue_yellow":
    case "scar_tissue_blue":
    case "take_a_stab_red":
    case "take_a_stab_yellow":
    case "take_a_stab_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "quickdodge_flexors":
      $char = &GetPlayerCharacter($currentPlayer);
      // remove flexors from its previous link
      for ($i = 0; $i < count($chainLinks); ++$i) {
        for ($j = ChainLinksPieces(); $j < count($chainLinks[$i]); $j += ChainLinksPieces()) {
          if ($chainLinks[$i][$j] == "quickdodge_flexors") {
            $chainLinks[$i][$j+2] = 0;
          }
        }
      }
      for ($i = 0; $i < count($char); $i += CharacterPieces()) {
        if ($char[$i] == "quickdodge_flexors") {
          $ind = $i;
          break;
        }
      }
      if (!SearchCurrentTurnEffects("quickdodge_flexors", $currentPlayer)) {
        AddCurrentTurnEffect("quickdodge_flexors", $currentPlayer);
        AddDecisionQueue("CHARFLAGDESTROY", $currentPlayer, $ind, 1);
      }
      $char[$ind + 6] = 1;
      break;
    case "bunker_beard":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYARS:type=A&MYARS:type=AA");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to add as a defending card", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      AddDecisionQueue("ADDCARDTOCHAINASDEFENDINGCARD", $currentPlayer, "ARS", 1);
      break;
    case "rotten_remains_blue":
      $myMaxCards = SearchCount(SearchDiscard($currentPlayer, maxAttack:1, minAttack:1));
      $oppMaxCards = SearchCount(SearchDiscard($otherPlayer, maxAttack:1, minAttack:1));
      $maxCards = min($myMaxCards, $oppMaxCards);
      for ($i = 0; $i < $maxCards; $i++) {
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:maxAttack=1;minAttack=1",1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to banish", 1);
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZBANISH", $currentPlayer, "GY,-," . $currentPlayer . ",1", 1);
        AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRDISCARD:maxAttack=1;minAttack=1");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to banish", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZBANISH", $currentPlayer, "GY,-," . $currentPlayer . ",1", 1);
        AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
      }
      break;
    case "shelter_from_the_storm_red":
      if (GetResolvedAbilityType($cardID, "HAND") == "I") {
        AddCurrentTurnEffect($cardID, $currentPlayer);
      }
      break;
    case "dual_threat_yellow":
      if(GetClassState($currentPlayer, $CS_AtksWWeapon) > 0) AddCurrentTurnEffect($cardID."-AA", $currentPlayer);
      if(GetClassState($currentPlayer, $CS_NumAttackCards) > 0) AddCurrentTurnEffect($cardID."-WEAPON", $currentPlayer);
      break;
    case "sound_the_alarm_red";
      AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
      AddDecisionQueue("REVEALHANDCARDS", $otherPlayer, "-", 1);
      AddDecisionQueue("IFTYPEREVEALED", $otherPlayer, "AR", 1);
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDECK:type=DR", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
      AddDecisionQueue("REVEALCARDS", $currentPlayer, "-", 1);
      AddDecisionQueue("MULTIADDTOPDECK", $currentPlayer, "-", 1);
      break;
    case "imperial_seal_of_command_red":
      if($from == "PLAY") {
        AddCurrentTurnEffect($cardID, $currentPlayer);
        if(IsRoyal($currentPlayer)) AddCurrentTurnEffect($cardID."-HIT", $currentPlayer);
      }
      break;
    case "relentless_pursuit_blue":
      MarkHero($otherPlayer);
      break;
    case "calming_breeze_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "tipoff_red":
    case "tipoff_yellow":
    case "tipoff_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I") {
        MarkHero($otherPlayer);
      }
      break;
    case "lay_low_yellow":
      if(!IsAllyAttacking() && CheckMarked($otherPlayer)) {
        AddCurrentTurnEffectNextAttack($cardID, $otherPlayer);
      }
      break;
    case "exposed_blue";
      AddCurrentTurnEffect($cardID, $currentPlayer);
      MarkHero($otherPlayer);
      break;
    case "nip_at_the_heels_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "trot_along_blue":
      AddCurrentTurnEffectNextAttack($cardID, $currentPlayer);
      break;
    case "public_bounty_red":
    case "public_bounty_yellow":
    case "public_bounty_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      MarkHero($otherPlayer);
      break;
    case "thick_hide_hunter_yellow":
      DiscardRandom();
      break;
    case "tremorshield_sabatons":
      if(GetClassState($currentPlayer, $CS_NumSeismicSurgeDestroyed) > 0 || SearchAurasForCard("seismic_surge", $currentPlayer) != "") $prevent = 2;
      else $prevent = 1;
      IncrementClassState($currentPlayer, $CS_ArcaneDamagePrevention, $prevent);
      return CardLink($cardID, $cardID) . " prevent your next arcane damage by " . $prevent;
    case "roiling_fissure_blue":
      $maxSeismicCount = count(explode(",", SearchAurasForCard("seismic_surge", $currentPlayer)))+1;
      for($i=0; $i < $maxSeismicCount; ++$i) {
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRAURAS:minCost=0;maxCost=".$resourcesPaid."&MYAURAS:minCost=0;maxCost=".$resourcesPaid, 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose an aura with cost " . $resourcesPaid . " or less to destroy", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYAURAS:isCardID=seismic_surge", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a Seismic Surge to destroy or pass", 1);
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
      }
      break;
    case "retrace_the_past_blue":
      if (ComboActive($cardID)) {
        AddDecisionQueue("INPUTCARDNAME", $currentPlayer, "-");
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "retrace_the_past_blue-");
        AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "<-");
        AddDecisionQueue("WRITELOG", $currentPlayer, "📣<b>{0}</b> was chosen");
      }
      break;
    case "misfire_dampener":
      if(GetClassState($currentPlayer, $CS_NumBoosted) > 1) AddCurrentTurnEffect($cardID."-2", $currentPlayer);
      else AddCurrentTurnEffect($cardID."-1", $currentPlayer);
      break;
    case "null_time_zone_blue":
      AddDecisionQueue("INPUTCARDNAME", $currentPlayer, "-");
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
      AddDecisionQueue("WRITELOG", $currentPlayer, "📣<b>{0}</b> was chosen");
      AddDecisionQueue("NULLTIMEZONE", $currentPlayer, SearchItemForLastIndex($cardID, $currentPlayer).",{0}");
      return "";
    case "enchanted_quiver":
      $prevent = SearchArsenal($currentPlayer, subtype:"Arrow", faceUp:true) != "" ? 2 : 1;
      IncrementClassState($currentPlayer, $CS_ArcaneDamagePrevention, $prevent);
      return CardLink($cardID, $cardID) . " prevent your next arcane damage by " . $prevent;
    case "douse_in_runeblood_red":
      AddLayer("TRIGGER", $currentPlayer, "douse_in_runeblood_red");
      break;
    case "spur_locked_blue":
      AddDecisionQueue("CHOOSENUMBER", $currentPlayer, "1,2,3,4,5,6");
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
      AddDecisionQueue("CHOOSENUMBER", $otherPlayer, "1,2,3,4,5,6");
      AddDecisionQueue("SETDQVAR", $currentPlayer, "1");
      AddDecisionQueue("COMPARENUMBERS", $currentPlayer, "-");
      AddDecisionQueue("SPURLOCKED", $currentPlayer, "-");
      break;
    case "war_cry_of_themis_yellow":
      if (GetResolvedAbilityType($cardID, "HAND") == "A") {
        AddCurrentTurnEffectNextAttack($cardID, $currentPlayer);
      }
      else {
        for ($i = 0; $i < GetClassState($currentPlayer, piece: $CS_AdditionalCosts); $i++) {
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRBANISH&MYBANISH");
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to turn face-down");
          AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("MZOP", $currentPlayer, "TURNBANISHFACEDOWN", 1);
        }
      }
      break;
    case "war_cry_of_bellona_yellow":
      if (GetResolvedAbilityType($cardID, $from) == "AR") {
        AddCurrentTurnEffect($cardID."-BUFF", $currentPlayer);
      }
      else {
        $params = explode("-", $target);
        $uniqueID = $params[1];
        AddCurrentTurnEffect($cardID."-DMG,".$additionalCosts.",".$uniqueID, $currentPlayer);
      }
      break;
    case "cull_red":
      MZChooseAndBanish($currentPlayer, "MYHAND", "HAND,-");
      MZChooseAndBanish($otherPlayer, "MYHAND", "HAND,-");
      break;
    case "the_hand_that_pulls_the_strings":
      AddCurrentTurnEffect("the_hand_that_pulls_the_strings", $currentPlayer);
      SetArsenalFacing("UP", $currentPlayer);
      break;
    default:
      break;
  }
  return "";
}

function HNTHitEffect($cardID, $uniqueID = -1): void
{
  global $mainPlayer, $defPlayer, $CS_LastAttack, $CCS_GoesWhereAfterLinkResolves, $chainLinkSummary, $combatChainState;
  $dashArr = explode("-", $cardID);
  $cardID = $dashArr[0];
  switch ($cardID) {
    case "hunters_klaive":
      MarkHero($defPlayer);
      break;
    case "mark_of_the_huntsman":
      AddDecisionQueue("YESNO", $mainPlayer, "if you want to destroy " . CardLink($cardID, $cardID) . " and mark the opponent", 0, 1);
      AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
      AddDecisionQueue("HUNTSMANMARK", $mainPlayer, $uniqueID);
      break;
    case "kiss_of_death_red":
      WriteLog("Player $defPlayer loses 1 life.");
      LoseHealth(1, $defPlayer);
      break;
    case "mark_of_the_black_widow_red":
    case "mark_of_the_black_widow_yellow":
    case "mark_of_the_black_widow_blue":
      AddDecisionQueue("FINDINDICES", $defPlayer, "HAND");
      AddDecisionQueue("SETDQCONTEXT", $defPlayer, "Choose a card to banish", 1);
      AddDecisionQueue("CHOOSEHAND", $defPlayer, "<-", 1);
      AddDecisionQueue("MULTIREMOVEHAND", $defPlayer, "-", 1);
      AddDecisionQueue("BANISHCARD", $defPlayer, "HAND,-", 1);
      break;
    case "mark_of_the_funnel_web_red":
    case "mark_of_the_funnel_web_yellow":
    case "mark_of_the_funnel_web_blue":
      MZMoveCard($mainPlayer, "THEIRARS", "THEIRBANISH,ARS,-," . $mainPlayer, false);
      break;
    case "mark_the_prey_red":
    case "mark_the_prey_yellow":
    case "mark_the_prey_blue":
      MarkHero($defPlayer);
      break;
    case "burning_blade_dance_red":
      ThrowWeapon("Dagger", $cardID, true);
      break;
    case "hot_on_their_heels_red":
    case "mark_with_magma_red":
      MarkHero($defPlayer);
      break;
    case "devotion_never_dies_red":
      if(isPreviousLinkDraconic()) {
        $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "-"; 
        BanishCardForPlayer("devotion_never_dies_red", $mainPlayer, "COMBATCHAIN", "TT", $mainPlayer); # throw Devotion Never Dies to banish. it can be played this turn (TT)
      }
      break;
    case "art_of_the_dragon_claw_red":
      DestroyArsenal($defPlayer, effectController:$mainPlayer);
      break;
    case "art_of_the_dragon_scale_red":
      AddDecisionQueue("FINDINDICES", $defPlayer, "EQUIP");
      AddDecisionQueue("CHOOSETHEIRCHARACTER", $mainPlayer, "<-", 1);
      AddDecisionQueue("MODDEFCOUNTER", $defPlayer, "-1", 1);
      AddDecisionQueue("DESTROYEQUIPDEF0", $mainPlayer, "-", 1);
      break;
    case "tag_the_target_red":
    case "tag_the_target_yellow":
    case "tag_the_target_blue":
    case "trap_and_release_red":
    case "trap_and_release_yellow":
    case "trap_and_release_blue":
      MarkHero($defPlayer);
      break;
    case "pain_in_the_backside_red":
      ThrowWeapon("Dagger", $cardID, destroy: false);
      break;
    case "pursue_to_the_edge_of_oblivion_red":
    case "pursue_to_the_pits_of_despair_red":
      MarkHero($defPlayer);
      break;
    default:
      break;
  }
}

function MarkHero($player): string
{
  WriteLog("🎯Player " . $player . " is now marked!");
  if (!SearchCurrentTurnEffects("marked", $player)) AddCurrentTurnEffect("marked", $player);
  $character = &GetPlayerCharacter($player);
  $character[13] = 1;
  return "";
}

function CheckMarked($player): bool
{
  $character = &GetPlayerCharacter($player);
  return $character[13] == 1;
}

function RemoveMark($player)
{
  $effectIndex = SearchCurrentTurnEffectsForIndex("marked", $player);
  if ($effectIndex > -1) RemoveCurrentTurnEffect($effectIndex);
  $character = &GetPlayerCharacter($player);
  $character[13] = 0;
}

function RecurDagger($player, $mode) //$mode == 0 for left, and 1 for right
{
  $char = &GetPlayerCharacter($player);
  if ($char[CharacterPieces() * ($mode + 1) + 1] == 0) { //Only Equip if there is a broken weapon/off-hand
    AddDecisionQueue("LISTDRACDAGGERGRAVEYARD", $player, "-");
    AddDecisionQueue("NULLPASS", $player, "-", 1);
    AddDecisionQueue("SETDQCONTEXT", $player, "Choose a dagger to equip", 1);
    AddDecisionQueue("MAYCHOOSECARD", $player, "<-", 1);
    AddDecisionQueue("EQUIPCARDGRAVEYARD", $player, "<-", 1);
  }
}

function ListDracDaggersGraveyard($player) {
  $weapons = "";
  $char = &GetPlayerCharacter($player);
  $graveyard = &GetDiscard($player);
  foreach ($graveyard as $cardID) {
    if (TypeContains($cardID, "W", $player) && SubtypeContains($cardID, "Dagger")) {
      if (TalentContains($cardID, "DRACONIC")) {
        if ($weapons != "") $weapons .= ",";
        $weapons .= $cardID;
      }
    }
  }
  if ($weapons == "") {
    WriteLog("Player " . $player . " doesn't have any dagger in their graveyard");
  }
  return $weapons;
}

function ChaosTransform($characterID, $mainPlayer, $toAgent = false, $choice = -1)
{
  global $CS_OriginalHero;
  $char = &GetPlayerCharacter($mainPlayer);
  if ($characterID == "arakni_marionette" || $characterID == "arakni_web_of_deceit" || $toAgent) {
    if ($choice == -1) {
      $roll = GetRandom(1, 6);
      $transformTarget = match ($roll) {
        1 => "arakni_black_widow",
        2 => "arakni_funnel_web",
        3 => "arakni_orbweaver",
        4 => "arakni_redback",
        5 => "arakni_tarantula",
        6 => "arakni_trapdoor",
        default => $characterID,
      };
    }
    else $transformTarget = $choice;
    WriteLog(CardName($characterID) . " becomes " . CardName($transformTarget));
    if (GetClassState($mainPlayer, $CS_OriginalHero) == "-") {
      SetClassState($mainPlayer, $CS_OriginalHero, $characterID);
    }
  }
  else {
    $transformTarget = GetClassState($mainPlayer, $CS_OriginalHero);
    if ($transformTarget == "-"){
      WriteLog("Something has gone wrong, please submit a bug report");
      $transformTarget = "arakni_marionette";
    }
    SetClassState($mainPlayer, $CS_OriginalHero, "-");
  }
  $char[0] = $transformTarget;
  //don't trigger trapdoor if you transfrom from trapdoor into trapdoor
  if ($transformTarget == "arakni_trapdoor" && $characterID != "arakni_trapdoor") {
    AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_banish_a_card_to_".CardLink("arakni_trapdoor", "arakni_trapdoor")."?");
    AddDecisionQueue("NOPASS", $mainPlayer, "-");
    AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "MYDECK", 1);
    AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
    AddDecisionQueue("TRAPDOOR", $mainPlayer, "-", 1);
    AddDecisionQueue("SHUFFLEDECK", $mainPlayer, "-", 1);
  }
}

function AddedOnHit($cardID) //tracks whether a card adds an on-hit to its applicable attack (for kiss of death)
{
  return match($cardID) {
    "amulet_of_assertiveness_yellow" => true,
    "mask_of_perdition" => true,
    "spike_with_bloodrot_red" => true,
    "spike_with_frailty_red" => true,
    "spike_with_inertia_red" => true,
    "concealed_blade_blue" => true,
    "toxic_tips" => true,
    "toxicity_red" => true,
    "toxicity_yellow" => true,
    "toxicity_blue" => true,
    "just_a_nick_red-HIT" => true,
    "arakni_black_widow-HIT" => true,
    "arakni_funnel_web-HIT" => true,
    "two_sides_to_the_blade_red-ATTACK" => true,
    "scar_tissue_red" => true,
    "scar_tissue_yellow" => true,
    "scar_tissue_blue" => true,
    default => false
  };
}

function IsLayerContinuousBuff($cardID) {//tracks buffs that attach themselves to a card, even if it transforms
  //for now only tracking dagger buffs, ideally we'd want to track all static buffs
  return match($cardID) {
    "plunge_red" => true,
    "two_sides_to_the_blade_red-ATTACK" => true,
    "up_sticks_and_run_red" => true,
    "up_sticks_and_run_yellow" => true,
    "up_sticks_and_run_blue" => true,
    "savor_bloodshed_red" => true,
    "cut_from_the_same_cloth_red" => true,
    default => false
  };
}

function BubbleToTheSurface()
{
  global $currentPlayer;
  if(!CanRevealCards($currentPlayer)) return "";
    $cardRemoved = "";
    $deck = &GetDeck($currentPlayer);
    $cardsToReveal = "";
    for($i=0; $i<count($deck); ++$i)
    {
      if($cardsToReveal != "") $cardsToReveal .= ",";
      $cardsToReveal .= $deck[$i];
      if(PitchValue($deck[$i]) == 1)
            {
        $cardRemoved = $deck[$i];
        unset($deck[$i]);
        $deck = array_values($deck);
        RevealCards($cardsToReveal);
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
        return $cardRemoved;
      }
    }
    return $cardRemoved;
  }

  function Retrieve($player, $subtype)
  {
    if (SearchDiscard($player, subtype:$subtype, type:"W") != "") {
      AddDecisionQueue("YESNO", $player, "if_you_want_to_pay_a_resource_to_retrieve_a_$subtype");
      AddDecisionQueue("NOPASS", $player, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $player, "1", 1);
      AddDecisionQueue("WRITELOG", $player, "<b>Pitch cards to pay to retrieve</b>", 1);
      AddDecisionQueue("PAYRESOURCES", $player, "<-", 1);
      AddDecisionQueue("MULTIZONEINDICES", $player, "MYDISCARD:subtype=$subtype;type=W", 1);
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose a dagger to equip", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
      AddDecisionQueue("MZOP", $player, "GETCARDID", 1);
      AddDecisionQueue("EQUIPCARDGRAVEYARD", $player, "<-", 1);
    }
  }
