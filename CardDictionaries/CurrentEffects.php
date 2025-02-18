<?php

function TCCEffectAttackModifier($cardID): int|string
{
  $idArr = explode(",", $cardID);
  $cardID = $idArr[0];
  return match ($cardID) {
    "crash_down_red" => 6,
    "earthlore_empowerment_red", "crash_down_yellow" => 5,
    "earthlore_empowerment_yellow" => 4,
    "final_act_red" => $idArr[1],
    "bittering_thorns_red", "might", "evo_scatter_shot_blue_equip", "growl_red", "growl_yellow" => 1,
    default => 0
  };
}

function TCCCombatEffectActive($cardID, $attackID): bool
{
  global $mainPlayer;
  $idArr = explode(",", $cardID);
  $cardID = $idArr[0];
  return match ($cardID) {
    "crash_down_red", "earthlore_empowerment_red", "crash_down_yellow", "earthlore_empowerment_yellow" => ClassContains($attackID, "GUARDIAN", $mainPlayer) && CardType($attackID) == "AA",
    "growl_red", "growl_yellow" => CardNameContains($attackID, "Crouching Tiger", $mainPlayer),
    "lay_down_the_law_red", "lay_down_the_law_red", "final_act_red", "bittering_thorns_red", "might", "evo_scatter_shot_blue_equip" => true,
    default => false
  };
}

function EVOEffectAttackModifier($cardID): int|string
{
  $idArr = explode(",", $cardID);
  $cardID = $idArr[0];
  return match ($cardID) {
    "hadron_collider_red", "hadron_collider_yellow", "hadron_collider_blue" => $idArr[1],
    "gigawatt_red", "gas_up_red", "quickfire_red", "recharge_red" => 4,
    "moonshot_yellow", "gigawatt_yellow", "gas_up_yellow", "quickfire_yellow", "recharge_yellow" => 3,
    "steel_street_hoons_blue", "big_shot_red", "burn_rubber_red", "smash_and_grab_red", "gigawatt_blue", "gas_up_blue", "quickfire_blue", "recharge_blue", "evo_face_breaker_red_equip" => 2,
    "cogwerx_base_arms", "evo_whizz_bang_yellow", "junkyard_dogg_red", "junkyard_dogg_yellow", "junkyard_dogg_blue", "emboldened_blade_blue", "evo_smoothbore_yellow_equip", "sprocket_rocket_red", "sprocket_rocket_yellow", "sprocket_rocket_blue", "dumpster_dive_red",
    "dumpster_dive_yellow", "dumpster_dive_blue" => 1,
    default => 0,
  };
}

function EVOCombatEffectActive($cardID, $attackID)
{
  global $mainPlayer, $combatChainState, $CCS_IsBoosted, $CS_NumItemsDestroyed;
  $idArr = explode(",", $cardID);
  $cardID = $idArr[0];
  return match ($cardID) {
    "cogwerx_base_arms", "gigawatt_red", "gigawatt_yellow", "gigawatt_blue" => ClassContains($attackID, "MECHANOLOGIST", $mainPlayer),
    "gas_up_red", "gas_up_yellow", "gas_up_blue", "quickfire_red", "quickfire_yellow", "quickfire_blue", "recharge_red", "recharge_yellow", "recharge_blue" => $combatChainState[$CCS_IsBoosted],
    "emboldened_blade_blue", "evo_command_center_yellow_equip", "evo_smoothbore_yellow_equip" => TypeContains($attackID, "W", $mainPlayer),
    "evo_whizz_bang_yellow", "hadron_collider_red", "hadron_collider_yellow", "hadron_collider_blue", "hydraulic_press_red", "hydraulic_press_yellow", "hydraulic_press_blue", "ratchet_up_red", "ratchet_up_yellow", "ratchet_up_blue", "junkyard_dogg_red",
    "junkyard_dogg_yellow", "junkyard_dogg_blue", "moonshot_yellow", "steel_street_hoons_blue", "fabricate_red", "big_shot_red", "burn_rubber_red", "smash_and_grab_red", "sprocket_rocket_red", "sprocket_rocket_yellow", "sprocket_rocket_blue",
    "dumpster_dive_red", "dumpster_dive_yellow", "dumpster_dive_blue", "evo_face_breaker_red_equip" => true,
    default => false
  };
}

function HVYEffectAttackModifier($cardID): int|string
{
  $idArr = explode(",", $cardID);
  $cardID = $idArr[0];
  return match ($cardID) {
    "bonebreaker_bellow_red", "bonebreaker_bellow_yellow", "bonebreaker_bellow_blue", "tenacity_yellow" => $idArr[1],
    "gauntlets_of_iron_will" => $idArr[1] == "ACTIVE" ? -1 : 0,
    "big_bop_red-BUFF", "bigger_than_big_red-BUFF", "fatal_engagement_red" => 5,
    "big_bop_yellow-BUFF", "bigger_than_big_yellow-BUFF", "fatal_engagement_yellow" => 4,
    "the_golden_son_yellow", "big_bop_blue-BUFF", "bigger_than_big_blue-BUFF", "commanding_performance_red-BUFF", "cut_the_deck_red", "fatal_engagement_blue", "agile_engagement_red", "vigorous_engagement_red", "draw_swords_red", "edge_ahead_red-BUFF",
    "engaged_swiftblade_red", "hold_em_red-BUFF", "lead_with_power_red", "lead_with_speed_red", "double_down_red-BUFF", "lead_with_heart_red", "down_but_not_out_red", "down_but_not_out_yellow", "down_but_not_out_blue", "money_where_ya_mouth_is_red-BUFF" => 3,
    "beast_mode_red", "beast_mode_yellow", "beast_mode_blue", "blade_flurry_red", "cut_the_deck_yellow", "agile_engagement_yellow", "vigorous_engagement_yellow", "draw_swords_yellow", "edge_ahead_yellow-BUFF", "engaged_swiftblade_yellow", "hold_em_yellow-BUFF",
    "lead_with_power_yellow", "lead_with_speed_yellow", "lead_with_heart_yellow", "standing_order_red", "money_where_ya_mouth_is_yellow-BUFF" => 2,
    "betsy_skin_in_the_game", "betsy", "primed_to_fight_red", "cut_the_deck_blue", "agile_engagement_blue", "vigorous_engagement_blue", "draw_swords_blue", "edge_ahead_blue-BUFF", "engaged_swiftblade_blue", "hold_em_blue-BUFF", "lead_with_power_blue",
    "lead_with_speed_blue", "lead_with_heart_blue", "money_where_ya_mouth_is_blue-BUFF", "might", "ancestral_harmony_blue" => 1,
    default => 0
  };
}

function HVYCombatEffectActive($cardID, $attackID)
{
  global $mainPlayer, $CombatChain;
  $idArr = explode(",", $cardID);
  $cardID = $idArr[0];
  return match ($cardID) {
    "bonebreaker_bellow_red", "bonebreaker_bellow_yellow", "bonebreaker_bellow_blue" => ClassContains($CombatChain->AttackCard()->ID(), "BRUTE", $mainPlayer),
    "big_bop_red-BUFF", "big_bop_yellow-BUFF", "big_bop_blue-BUFF", "bigger_than_big_red-BUFF", "bigger_than_big_yellow-BUFF", "bigger_than_big_blue-BUFF" => ClassContains($CombatChain->AttackCard()->ID(), "GUARDIAN", $mainPlayer),
    "kassai_of_the_golden_sand", "kassai" => TypeContains($attackID, "W", $mainPlayer) && !IsAllyAttackTarget(),
    "blade_flurry_red" => TypeContains($attackID, "W", $mainPlayer),
    "commanding_performance_red", "commanding_performance_red-BUFF", "draw_swords_red", "draw_swords_yellow", "draw_swords_blue", "edge_ahead_red-BUFF", "edge_ahead_yellow-BUFF", "edge_ahead_blue-BUFF", "engaged_swiftblade_red",
    "engaged_swiftblade_yellow", "engaged_swiftblade_blue", "hold_em_red-BUFF", "hold_em_yellow-BUFF", "hold_em_blue-BUFF" => ClassContains($CombatChain->AttackCard()->ID(), "WARRIOR", $mainPlayer),
    "lead_with_power_red", "lead_with_power_yellow", "lead_with_power_blue" => ClassContains($CombatChain->AttackCard()->ID(), "BRUTE", $mainPlayer) || ClassContains($CombatChain->AttackCard()->ID(), "GUARDIAN", $mainPlayer),
    "lead_with_speed_red", "lead_with_speed_yellow", "lead_with_speed_blue" => ClassContains($CombatChain->AttackCard()->ID(), "BRUTE", $mainPlayer) || ClassContains($CombatChain->AttackCard()->ID(), "WARRIOR", $mainPlayer),
    "double_down_red-BUFF" => CachedWagerActive(),
    "lead_with_heart_red", "lead_with_heart_yellow", "lead_with_heart_blue" => ClassContains($CombatChain->AttackCard()->ID(), "WARRIOR", $mainPlayer) || ClassContains($CombatChain->AttackCard()->ID(), "GUARDIAN", $mainPlayer),
    "ancestral_harmony_blue" => HasCombo($attackID),
    "luminaris_angels_glow-1" => str_contains(NameOverride($CombatChain->AttackCard()->ID(), $mainPlayer), "Herald"),
    "luminaris_angels_glow-2" => DelimStringContains(CardSubType($CombatChain->AttackCard()->ID()), "Angel"),
    "coercive_tendency_blue" => ClassContains($CombatChain->AttackCard()->ID(), "ASSASSIN", $mainPlayer),
    "betsy_skin_in_the_game", "betsy", "primed_to_fight_red", "stonewall_impasse", "gauntlets_of_iron_will", "good_time_chapeau-PAID", "the_golden_son_yellow", "big_bop_red", "big_bop_yellow", "big_bop_blue", "bigger_than_big_red",
    "bigger_than_big_yellow", "bigger_than_big_blue", "hood_of_red_sand", "talk_a_big_game_blue", "wage_might_red", "wage_might_yellow", "wage_might_blue", "wage_agility_red", "wage_agility_yellow", "wage_agility_blue", "wage_vigor_red", "wage_vigor_yellow",
    "wage_vigor_blue", "headliner_helm", "stadium_centerpiece", "ticket_puncher", "grandstand_legplates", "bloodied_oval", "standing_order_red", "down_but_not_out_red", "down_but_not_out_yellow", "down_but_not_out_blue", "wage_gold_red", "wage_gold_yellow",
    "wage_gold_blue", "tenacity_yellow", "money_where_ya_mouth_is_red-BUFF", "money_where_ya_mouth_is_yellow-BUFF", "money_where_ya_mouth_is_blue-BUFF", "agility", "might", "cut_the_deck_red", "cut_the_deck_yellow", "cut_the_deck_blue",
    "fatal_engagement_red", "fatal_engagement_yellow", "fatal_engagement_blue", "agile_engagement_red", "agile_engagement_yellow", "agile_engagement_blue", "vigorous_engagement_red", "vigorous_engagement_yellow", "vigorous_engagement_blue" => true,
    default => false
  };
}

?>
