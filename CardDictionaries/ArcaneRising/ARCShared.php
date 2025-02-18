<?php

  function ARCAbilityCost($cardID)
  {
    global $CS_PlayIndex, $currentPlayer, $CombatChain;
    switch($cardID)
    {
      case "teklo_plasma_pistol":
        $abilityType = GetResolvedAbilityType($cardID);
        return ($abilityType == "A" ? 1 : 0);
      case "teklo_foundry_heart": return 1;
      case "induction_chamber_red":
        $abilityType = GetResolvedAbilityType($cardID);
        return $CombatChain->HasCurrentLink() ? 0 : 1;
      case "aether_sink_yellow":
        $items = &GetItems($currentPlayer);
        return ($items[GetClassState($currentPlayer, $CS_PlayIndex) + 1] > 0 ? 0 : 1);
      case "cognition_nodes_blue":
        $abilityType = GetResolvedAbilityType($cardID);
        return $CombatChain->HasCurrentLink() ? 0 : 1;
      case "death_dealer": return 1;
      case "nebula_blade": return 2;
      case "grasp_of_the_arknight": return 2 + NumRunechants($currentPlayer);
      case "crown_of_dichotomy": return 1;
      case "kano_dracai_of_aether": case "kano": return 3;
      case "crucible_of_aetherweave": return 1;
      case "storm_striders": return 1;
      case "robe_of_rapture": return 0;
      case "mage_master_boots": return 1;
      default: return 0;
    }
  }

  function ARCAbilityType($cardID, $index=-1)
  {
    global $currentPlayer, $CS_PlayIndex, $CombatChain;
    $items = &GetItems($currentPlayer);
    switch($cardID)
    {
      case "teklo_plasma_pistol":
        return "A";
      case "teklo_foundry_heart": return "A";
      case "achilles_accelerator": return "I";
      case "induction_chamber_red": return $CombatChain->HasCurrentLink() ? "AR" : "A";
      case "aether_sink_yellow":
        if($index == -1) $index = GetClassState($currentPlayer, $CS_PlayIndex);
        if(isset($items[$index + 1])) return ($items[$index+1] > 0 ? "I" : "A");
        else return "A";
      case "cognition_nodes_blue":
        if($index == -1) $index = GetClassState($currentPlayer, $CS_PlayIndex);
        return ($CombatChain->HasCurrentLink() ? "AR" : "A");
      case "convection_amplifier_red": return "A";
      case "dissipation_shield_yellow": return "I";
      case "optekal_monocle_blue": return "A";
      case "azalea_ace_in_the_hole": case "azalea": case "death_dealer": case "skullbone_crosswrap": case "bulls_eye_bracers": return "A";
      case "nebula_blade": return "AA";
      case "grasp_of_the_arknight": return "A";
      case "crown_of_dichotomy": return "A";
      case "kano_dracai_of_aether": case "kano": case "crucible_of_aetherweave": case "storm_striders": return "I";
      case "robe_of_rapture": return "A";
      case "talismanic_lens": return "I";
      case "bracers_of_belief": case "mage_master_boots": return "A";
      default: return "";
    }
  }

  function ARCAbilityHasGoAgain($cardID)
  {
    global $currentPlayer, $CS_PlayIndex, $CombatChain;
    switch($cardID)
    {
      case "teklo_plasma_pistol":
        $abilityType = GetResolvedAbilityType($cardID);
        return $abilityType == "A";
      case "teklo_foundry_heart": return true;
      case "induction_chamber_red":
        return !$CombatChain->HasCurrentLink();
      case "aether_sink_yellow":
        $items = &GetItems($currentPlayer);
        return ($items[GetClassState($currentPlayer, $CS_PlayIndex)+1] > 0 ? true : false);
      case "cognition_nodes_blue":
        $items = &GetItems($currentPlayer);
        return ($items[GetClassState($currentPlayer, $CS_PlayIndex)+1] > 0 ? true : false);
      case "convection_amplifier_red": return true;
      case "optekal_monocle_blue": return true;
      case "azalea_ace_in_the_hole": case "azalea": case "death_dealer": case "skullbone_crosswrap": case "bulls_eye_bracers": return true;
      case "grasp_of_the_arknight": return true;
      case "bracers_of_belief": case "mage_master_boots": return true;
      default: return false;
    }
  }

  function ARCEffectAttackModifier($cardID)
  {
    switch($cardID)
    {
      case "locked_and_loaded_red": return 3;
      case "locked_and_loaded_yellow": return 2;
      case "locked_and_loaded_blue": return 1;
      case "bulls_eye_bracers": return 1;
      case "take_aim_red": return 3;
      case "take_aim_yellow": return 2;
      case "take_aim_blue": return 1;
      case "head_shot_red": case "head_shot_yellow": case "head_shot_blue": return 2;
      case "oath_of_the_arknight_red": return 3;
      case "oath_of_the_arknight_yellow": return 2;
      case "oath_of_the_arknight_blue": return 1;
      case "bracers_of_belief-1": return 1; case "bracers_of_belief-2": return 2; case "bracers_of_belief-3": return 3;
      case "art_of_war_yellow-1": return 1;
      case "plunder_run_red-2": return 3;
      case "plunder_run_yellow-2": return 2;
      case "plunder_run_blue-2": return 1;
      case "come_to_fight_red": return 3;
      case "come_to_fight_yellow": return 2;
      case "come_to_fight_blue": return 1;
      case "force_sight_red": return 3;
      case "force_sight_yellow": return 2;
      case "force_sight_blue": return 1;
      default: return 0;
    }
  }

function ARCCombatEffectActive($cardID, $attackID)
{
  global $combatChainState, $CCS_AttackPlayedFrom, $mainPlayer;
  switch($cardID) {
    case "pedal_to_the_metal_red": case "pedal_to_the_metal_yellow": case "pedal_to_the_metal_blue": return true;
    case "convection_amplifier_red": return CardType($attackID) == "AA";
    case "locked_and_loaded_red": case "locked_and_loaded_yellow": case "locked_and_loaded_blue": return CardType($attackID) == "AA" && ClassContains($attackID, "MECHANOLOGIST", $mainPlayer);
    case "azalea_ace_in_the_hole": case "azalea": return CardSubType($attackID) == "Arrow" && $combatChainState[$CCS_AttackPlayedFrom] == "ARS";
    case "bulls_eye_bracers": return CardSubType($attackID) == "Arrow" && $combatChainState[$CCS_AttackPlayedFrom] == "ARS";
    case "rapid_fire_yellow": return CardSubType($attackID) == "Arrow";
    case "take_aim_red": case "take_aim_yellow": case "take_aim_blue": return ClassContains($attackID, "RANGER", $mainPlayer) && CardType($attackID) == "AA";
    case "head_shot_red": case "head_shot_yellow": case "head_shot_blue": return $cardID == $attackID;
    case "oath_of_the_arknight_red": case "oath_of_the_arknight_yellow": case "oath_of_the_arknight_blue": return ClassContains($attackID, "RUNEBLADE", $mainPlayer);
    case "bracers_of_belief-1": case "bracers_of_belief-2": case "bracers_of_belief-3": return CardType($attackID) == "AA";
    case "art_of_war_yellow-1": case "art_of_war_yellow-3": return CardType($attackID) == "AA";
    case "plunder_run_red-1": case "plunder_run_yellow-1": case "plunder_run_blue-1": return CardType($attackID) == "AA";
    case "plunder_run_red-2": case "plunder_run_yellow-2": case "plunder_run_blue-2": return CardType($attackID) == "AA";
    case "come_to_fight_red": case "come_to_fight_yellow": case "come_to_fight_blue": return CardType($attackID) == "AA";
    case "force_sight_red": case "force_sight_yellow": case "force_sight_blue": return CardType($attackID) == "AA";
    default: return false;
  }
}

?>
