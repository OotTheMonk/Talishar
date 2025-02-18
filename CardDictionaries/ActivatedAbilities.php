<?php

function AKOAbilityCost($cardID): int
{
  return 0;
}

function AKOAbilityType($cardID, $index = -1, $from = "-"): string
{
  return match ($cardID) {
    "savage_sash" => "A",
    default => ""
  };
}

function AKOAbilityHasGoAgain($cardID): bool
{
  return match ($cardID) {
    "savage_sash" => true,
    default => false
  };
}

function HVYAbilityCost($cardID): int
{
  return match ($cardID) {
    "high_riser", "millers_grindstone", "glory_seeker", "sheltered_cove" => 3,
    "ball_breaker", "mini_meataxe", "graven_call" => 2,
    "hot_streak", "parry_blade", "prized_galea", "hood_of_red_sand", "cintari_sellsword" => 1,
    default => 0
  };
}

function HVYAbilityType($cardID, $index = -1, $from = "-"): string
{
  return match ($cardID) {
    "cintari_sellsword", "parry_blade", "hot_streak", "mini_meataxe", "high_riser", "millers_grindstone", "ball_breaker" => "AA",
    "vigor_girth", "flat_trackers", "gauntlet_of_might", "kassai", "monstrous_veil", "good_time_chapeau", "kassai_of_the_golden_sand", "knucklehead" => "A",
    "hood_of_red_sand", "prized_galea" => "AR",
    "balance_of_justice", "glory_seeker", "sheltered_cove" => "I",
    "graven_call" => $from == "GY" ? "I" : "AA",
    default => ""
  };
}

function HVYAbilityHasGoAgain($cardID): bool
{
  return match ($cardID) {
    "monstrous_veil", "good_time_chapeau", "kassai_of_the_golden_sand", "kassai", "gauntlet_of_might", "flat_trackers", "vigor_girth", "gold" => true,
    default => false
  };
}

function TCCAbilityCost($cardID): int
{
  return match ($cardID) {
    "teklo_blaster", "hammer_of_havenhold" => 3,
    "jinglewood_smash_hit" => GetResolvedAbilityType($cardID) == "A" ? 3 : 0,
    default => 0
  };
}

function TCCAbilityType($cardID, $index = -1): string
{
  return match ($cardID) {
    "teklo_blaster", "hammer_of_havenhold" => "AA",
    "jinglewood_smash_hit", "nom_de_plume", "heartthrob", "fiddledee", "quickstep" => "A",
    "mask_of_three_tails", "blood_scent", "pouncing_paws" => "I",
    default => ""
  };
}

function TCCAbilityHasGoAgain($cardID): bool
{
  global $CombatChain;
  return match ($cardID) {
    "nom_de_plume", "heartthrob", "fiddledee", "quickstep" => true,
    "jinglewood_smash_hit" => !$CombatChain->HasCurrentLink(),
    default => false
  };
}

function EVOAbilityCost($cardID): int
{
  global $currentPlayer;
  return match ($cardID) {
    "teklo_leveler" => (EvoUpgradeAmount($currentPlayer) == 1 ? 3 : ((EvoUpgradeAmount($currentPlayer) >= 2) ? 1 : 0)),
    "teklovossen_esteemed_magnate", "teklovossen_the_mechropotent", "teklovossen" => 3,
    "maxx_the_hype_nitro", "backup_protocol_blu_blue", "backup_protocol_yel_yellow", "shriek_razors", "warband_of_bellona", "backup_protocol_red_red", "maxx_nitro" => 2,
    "cogwerx_base_legs", "cogwerx_base_arms", "cogwerx_base_chest", "cogwerx_base_head", "banksy" => 1,
    default => 0
  };
}

function EVOAbilityType($cardID, $index = -1, $from = ""): string
{
  global $currentPlayer, $CS_NumCranked, $CS_NumBoosted;
  return match ($cardID) {
    "maxx_the_hype_nitro", "maxx_nitro" => GetClassState($currentPlayer, $CS_NumBoosted) > 0 ? "A" : "",
    "banksy" => GetClassState($currentPlayer, $CS_NumCranked) > 0 ? "AA" : "",
    "teklo_leveler" => EvoUpgradeAmount($currentPlayer) >= 1 ? "AA" : "",
    "prismatic_lens_yellow", "quantum_processor_yellow", "fuel_injector_blue", "steam_canister_blue", "dissolving_shield_red", "backup_protocol_red_red", "backup_protocol_yel_yellow", "backup_protocol_blu_blue", "dissolving_shield_yellow", "dissolving_shield_blue" => $from == "PLAY" ? "I" : "A",
    "teklovossen_the_mechropotent", "symbiosis_shot" => "AA",
    "teklovossen_esteemed_magnate", "cogwerx_base_legs", "cogwerx_base_arms", "cogwerx_base_chest", "cogwerx_base_head", "evo_thruster_yellow_equip", "evo_smoothbore_yellow_equip", "evo_engine_room_yellow_equip", "evo_command_center_yellow_equip", "evo_charging_rods_yellow_equip", "evo_cogspitter_yellow_equip", "evo_battery_pack_yellow_equip", "evo_data_mine_yellow_equip", "teklovossen" => "I",
    "stasis_cell_blue", "medkit_blue", "warband_of_bellona", "grinding_gears_blue" => "A",
    "shriek_razors" => "AR",
    "adaptive_plating" => "A",
    default => ""
  };
}

function EVOAbilityHasGoAgain($cardID): bool
{
  return match ($cardID) {
    "stasis_cell_blue", "warband_of_bellona" => true,
    default => false
  };
}

function DestroyTopCardTarget($player): void
{
  $otherPlayer = ($player == 1 ? 2 : 1);
  AddDecisionQueue("PASSPARAMETER", $player, "ELSE");
  AddDecisionQueue("SETDQVAR", $player, "1");
  if (ShouldAutotargetOpponent($player)) {
    AddDecisionQueue("PASSPARAMETER", $player, "Target_Opponent");
  } else {
    AddDecisionQueue("SETDQCONTEXT", $player, "Choose target hero");
    AddDecisionQueue("BUTTONINPUT", $player, "Target_Opponent,Target_Yourself");
  }
  AddDecisionQueue("EQUALPASS", $player, "Target_Opponent");
  AddDecisionQueue("DESTROYTOPCARD", $player, "0", 1);
  AddDecisionQueue("SETDQVAR", $player, "1", 1);
  AddDecisionQueue("PASSPARAMETER", $player, "{1}");

  AddDecisionQueue("NOTEQUALPASS", $player, "ELSE");
  AddDecisionQueue("DESTROYTOPCARD", $otherPlayer, "0", 1);
  AddDecisionQueue("SETDQVAR", $otherPlayer, "1", 1);
}

function DestroyTopCardOpponent($player): void
{
  $otherPlayer = ($player == 1 ? 2 : 1);
  AddDecisionQueue("WRITELOG", $otherPlayer, "Destroys the top card of Player " . $otherPlayer . " deck", 1);
  AddDecisionQueue("DESTROYTOPCARD", $otherPlayer, "0", 1);
}

function DestroyItemWithoutSteamCounter($cardID, $player): bool
{
  if (CardNameContains($cardID, "Hyper Driver", $player)) return true;
  return match ($cardID) {
    "optekal_monocle_blue", "teklo_core_blue", "convection_amplifier_red", "plasma_mainline_red", "teklo_pounder_blue", "absorption_dome_yellow" => true,
    default => false
  };
}

function ASBAbilityType($cardID, $index = -1): string
{
  return match ($cardID) {
    "solar_plexus" => "I",
    default => ""
  };
}

?>
