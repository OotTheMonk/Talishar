<?php

  function ELEAbilityCost($cardID)
  {
    switch($cardID)
    {
      case "korshem_crossroad_of_elements": return 0;
      case "oldhim_grandfather_of_eternity": case "oldhim": return 3;
      case "winters_wail": return 3;
      case "lexi_livewire": case "lexi": return 0;
      case "shiver": case "voltaire_strike_twice": return 1;
      case "crown_of_seeds": return 1;
      case "plume_of_evergrowth": return 3;
      case "heart_of_ice": return 1;
      case "coat_of_frost": return 0;
      case "shock_charmers": return 2;
      case "shock_striker_red": case "shock_striker_yellow": case "shock_striker_blue": return 2;
      case "titans_fist": return 3;
      case "rosetta_thorn": case "duskblade": case "spellbound_creepers": case "sutcliffes_suede_hides": return 1;
      default: return 0;
    }
  }

  function ELEAbilityType($cardID, $index=-1, $from="")
  {
    switch($cardID)
    {
      case "korshem_crossroad_of_elements": return "I";
      case "oldhim_grandfather_of_eternity": case "oldhim": return "DR";
      case "winters_wail": return "AA";
      case "lexi_livewire": case "lexi": return "A";
      case "shiver": case "voltaire_strike_twice": return "I";
      case "crown_of_seeds": return "I";
      case "plume_of_evergrowth": return "I";
      case "amulet_of_earth_blue": 
        if($from == "PLAY") return "I";
        else return "A";
      case "heart_of_ice": return "A";
      case "coat_of_frost": return "A";
      case "amulet_of_ice_blue": 
        if($from == "PLAY") return "I";
        else return "A";
      case "shock_charmers": return "I";
      case "shock_striker_red": case "shock_striker_yellow": case "shock_striker_blue": 
        if($from == "PLAY") return "I";
        else return "AA";
      case "amulet_of_lightning_blue": 
        if($from == "PLAY") return "I";
        else return "A";
      case "titans_fist": return "AA";
      case "honing_hood": return "I";
      case "rosetta_thorn": case "duskblade": return "AA";
      case "spellbound_creepers": return "I";
      case "sutcliffes_suede_hides": return "AR";
      case "ragamuffins_hat": case "runaways": return "I";
      case "deep_blue": case "cracker_jax": return "A";
      default: return "";
    }
  }

  function ELEAbilityHasGoAgain($cardID)
  {
    switch($cardID)
    {
      case "lexi_livewire": case "lexi": return true;
      case "heart_of_ice": return true;
      case "coat_of_frost": return true;
      case "deep_blue": case "cracker_jax": return true;
      default: return false;
    }
  }

  function ELEEffectAttackModifier($cardID)
  {
    global $combatChainState, $CCS_AttackFused;
    switch($cardID)
    {
      case "korshem_crossroad_of_elements-1": return 1;
      case "oaken_old_red": return 2;
      case "entangle_red": case "entangle_yellow": case "entangle_blue": return -2;
      case "emerging_avalanche_red": case "strength_of_sequoia_red": return 3;
      case "emerging_avalanche_yellow": case "strength_of_sequoia_yellow": return 2;
      case "emerging_avalanche_blue": case "strength_of_sequoia_blue": return 1;
      case "shiver-1": return 1;
      case "voltaire_strike_twice-1": return 1;
      case "frost_lock_blue-2": return 1;
      case "ice_storm_red-1": return 3;
      case "force_of_nature_blue": return 1;
      case "explosive_growth_red": case "explosive_growth_yellow": case "explosive_growth_blue": return 1;
      case "stir_the_wildwood_red": case "stir_the_wildwood_yellow": case "stir_the_wildwood_blue": return 2;
      case "bramble_spark_red-FUSE": return 3;
      case "bramble_spark_yellow-FUSE": return 2;
      case "bramble_spark_blue-FUSE": return 1;
      case "fulminate_yellow-BUFF": return 3;
      case "invigorate_red": return 4;
      case "invigorate_yellow": return 3;
      case "invigorate_blue": return 2;
      case "pulse_of_volthaven_red": return 4;
      case "weave_earth_red": return 3 + ($combatChainState[$CCS_AttackFused] ? 1 : 0);
      case "weave_earth_yellow": return 2 + ($combatChainState[$CCS_AttackFused] ? 1 : 0);
      case "weave_earth_blue": return 1 + ($combatChainState[$CCS_AttackFused] ? 1 : 0);
      case "earthlore_surge_red": return 5;
      case "earthlore_surge_yellow": return 4;
      case "earthlore_surge_blue": return 3;
      case "amulet_of_earth_blue": return 1;
      case "ice_quake_red": return 3;
      case "ice_quake_yellow": return 2;
      case "ice_quake_blue": return 1;
      case "weave_ice_red": return 3;
      case "weave_ice_yellow": return 2;
      case "weave_ice_blue": return 1;
      case "weave_lightning_red": return 3;
      case "weave_lightning_yellow": return 2;
      case "weave_lightning_blue": return 1;
      case "lightning_press_red": return 3;
      case "lightning_press_yellow": return 2;
      case "lightning_press_blue": return 1;
      case "tear_asunder_blue": return 1;
      case "embolden_red": return 5;
      case "embolden_yellow": return 4;
      case "embolden_blue": return 3;
      case "seek_and_destroy_red": return 3;
      case "over_flex_red": return 4;
      case "over_flex_yellow": return 3;
      case "over_flex_blue": return 2;
      case "cracker_jax": return 1;
      default: return 0;
    }
  }

  function ELECombatEffectActive($cardID, $attackID)
  {
    global $combatChainState, $CCS_AttackFused, $mainPlayer;
    switch($cardID)
    {
      case "korshem_crossroad_of_elements-1": return true;
      case "korshem_crossroad_of_elements-2": return true;
      case "winters_wail": return true;
      case "endless_winter_red": return true;
      case "oaken_old_red": return true;
      case "entangle_red": case "entangle_yellow": case "entangle_blue": return true;
      case "glacial_footsteps_red": case "glacial_footsteps_yellow": case "glacial_footsteps_blue": return true;
      case "mulch_red": case "mulch_yellow": case "mulch_blue": return true;
      case "snow_under_red": case "snow_under_yellow": case "snow_under_blue": return true;
      case "emerging_avalanche_red": case "emerging_avalanche_yellow": case "emerging_avalanche_blue": return CardType($attackID) == "AA";
      case "strength_of_sequoia_red": case "strength_of_sequoia_yellow": case "strength_of_sequoia_blue": return CardType($attackID) == "AA";
      case "lexi_livewire-1": return true;
      case "shiver-1": case "shiver-2": return CardSubtype($attackID) == "Arrow";
      case "voltaire_strike_twice-1": case "voltaire_strike_twice-2": return CardSubtype($attackID) == "Arrow";
      case "frost_lock_blue-2": return true;
      case "ice_storm_red-1": case "ice_storm_red-2": return CardSubtype($attackID) == "Arrow";
      case "blizzard_bolt_red": case "blizzard_bolt_yellow": case "blizzard_bolt_blue": return true;
      case "buzz_bolt_red": case "buzz_bolt_yellow": case "buzz_bolt_blue": return true;
      case "chilling_icevein_red": case "chilling_icevein_yellow": case "chilling_icevein_blue": return true;
      case "flake_out_red": case "flake_out_yellow": case "flake_out_blue": return true;
      case "frazzle_red": case "frazzle_yellow": case "frazzle_blue": return true;
      case "blossoming_spellblade_red": return true;
      case "force_of_nature_blue": return true;
      case "force_of_nature_blue-HIT": return cardType($attackID) == "AA";
      case "explosive_growth_red": case "explosive_growth_yellow": case "explosive_growth_blue": return true;
      case "stir_the_wildwood_red": case "stir_the_wildwood_yellow": case "stir_the_wildwood_blue": return true;
      case "bramble_spark_red-FUSE": case "bramble_spark_yellow-FUSE": case "bramble_spark_blue-FUSE": return CardType($attackID) == "AA";
      case "fulminate_yellow-BUFF": case "fulminate_yellow-GA": return CardType($attackID) == "AA";
      case "flashfreeze_red-DOM": case "flashfreeze_red-DOMATK": case "flashfreeze_red-BUFF": return true;
      case "entwine_ice_red": case "entwine_ice_yellow": case "entwine_ice_blue": return true;
      case "invigorate_red": case "invigorate_yellow": case "invigorate_blue": return $combatChainState[$CCS_AttackFused] == 1;
      case "pulse_of_volthaven_red": return TalentContainsAny($attackID, "ICE,LIGHTNING,ELEMENTAL", $mainPlayer);
      case "weave_earth_red": case "weave_earth_yellow": case "weave_earth_blue":
        return TalentContainsAny($attackID, "EARTH,ELEMENTAL",$mainPlayer) && CardType($attackID) == "AA";
      case "earthlore_surge_red": case "earthlore_surge_yellow": case "earthlore_surge_blue": return CardType($attackID) == "AA";
      case "amulet_of_earth_blue": return CardType($attackID) == "AA";
      case "blizzard_blue": return true;
      case "ice_quake_red": case "ice_quake_yellow": case "ice_quake_blue": return true;
      case "ice_quake_red-HIT": case "ice_quake_yellow-HIT": case "ice_quake_blue-HIT": return true;
      case "weave_ice_red": case "weave_ice_yellow": case "weave_ice_blue":
        return CardType($attackID) == "AA" && TalentContainsAny($attackID, "ICE,ELEMENTAL",$mainPlayer);
      case "chill_to_the_bone_red": case "chill_to_the_bone_yellow": case "chill_to_the_bone_blue": return TalentContainsAny($attackID, "ICE,ELEMENTAL",$mainPlayer);
      case "polar_blast_red": case "polar_blast_yellow": case "polar_blast_blue": return true;
      case "shock_charmers": return CardType($attackID) == "AA";
      case "flash_red": return CardCost($attackID) >= 0;
      case "flash_yellow": return CardCost($attackID) >= 1;
      case "flash_blue": return CardCost($attackID) >= 2;
      case "weave_lightning_red": case "weave_lightning_yellow": case "weave_lightning_blue": return TalentContainsAny($attackID, "LIGHTNING,ELEMENTAL",$mainPlayer) && CardType($attackID) == "AA";
      case "lightning_press_red": case "lightning_press_yellow": case "lightning_press_blue": return true;
      case "shock_striker_red": case "shock_striker_yellow": case "shock_striker_blue": return true;
      case "electrify_red": case "electrify_yellow": case "electrify_blue": return CardType($attackID) == "AA";
      case "amulet_of_lightning_blue": return CardType($attackID) == "AA" || CardType($attackID) == "A";
      case "rampart_of_the_rams_head": return true;
      case "tear_asunder_blue": return ClassContains($attackID, "GUARDIAN", $mainPlayer);
      case "embolden_red": case "embolden_yellow": case "embolden_blue": return ClassContains($attackID, "GUARDIAN", $mainPlayer) && CardType($attackID) == "AA";
      case "seek_and_destroy_red": return CardSubtype($attackID) == "Arrow";
      case "boltn_shot_red": case "boltn_shot_yellow": case "boltn_shot_blue": return true;
      case "over_flex_red": case "over_flex_yellow": case "over_flex_blue": return CardSubtype($attackID) == "Arrow";
      case "cracker_jax": return CardType($attackID) == "AA";
      default: return false;
    }
  }

  function PlayerHasFused($player)
  {
    global $CS_NumFusedEarth, $CS_NumFusedIce, $CS_NumFusedLightning;
    return GetClassState($player, $CS_NumFusedEarth) > 0 || GetClassState($player, $CS_NumFusedIce) > 0 || GetClassState($player, $CS_NumFusedLightning) > 0;
  }

?>
