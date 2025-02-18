<?php

function ARCWizardPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
{
  global $currentPlayer, $CS_NextWizardNAAInstant, $CS_ArcaneDamageTaken;
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  switch ($cardID) {
    case "kano_dracai_of_aether":
    case "kano":
      AddDecisionQueue("DECKCARDS", $currentPlayer, "0");
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
      AddDecisionQueue("ALLCARDTYPEORPASS", $currentPlayer, "A", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Do you want to banish <0> with ". CardLink($cardID, $cardID)."?");
      AddDecisionQueue("YESNO", $currentPlayer, "whether to banish a card with ". CardLink($cardID, $cardID), 1);
      AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
      AddDecisionQueue("PARAMDELIMTOARRAY", $currentPlayer, "0", 1);
      AddDecisionQueue("MULTIREMOVEDECK", $currentPlayer, "0", 1);
      AddDecisionQueue("MULTIBANISH", $currentPlayer, "DECK,INST", 1);
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, "{0}");
      AddDecisionQueue("NONECARDTYPEORPASS", $currentPlayer, "A");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, CardLink($cardID, $cardID)." shows the top of your deck is <0>");
      AddDecisionQueue("OK", $currentPlayer, "whether to banish a card with ". CardLink($cardID, $cardID), 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "-");
      return "";
    case "crucible_of_aetherweave":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "storm_striders":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      SetClassState($currentPlayer, $CS_NextWizardNAAInstant, 1);
      return "";
    case "robe_of_rapture":
      GainResources($currentPlayer, 3);
      return "";
    case "blazing_aether_red":
      $damage = GetClassState($otherPlayer, $CS_ArcaneDamageTaken);
      DealArcane($damage, 0, "PLAYCARD", $cardID, resolvedTarget: $target);
      return "";
    case "sonic_boom_yellow":
      DealArcane(ArcaneDamage($cardID), 1, "PLAYCARD", $cardID, resolvedTarget: $target);
      AddDecisionQueue("LESSTHANPASS", $currentPlayer, 1);
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
      AddDecisionQueue("DECKCARDS", $currentPlayer, "0", 1);
      AddDecisionQueue("SETDQVAR", $currentPlayer, "1", 1);
      AddDecisionQueue("ALLCARDTYPEORPASS", $currentPlayer, "A", 1);
      AddDecisionQueue("ALLCARDCLASSORPASS", $currentPlayer, "WIZARD", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose if you want to banish <1> with " . CardLink($cardID, $cardID), 1);
      AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_banish_the_card", 1);
      AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
      AddDecisionQueue("PARAMDELIMTOARRAY", $currentPlayer, "0", 1);
      AddDecisionQueue("MULTIREMOVEDECK", $currentPlayer, "0", 1);
      AddDecisionQueue("MULTIBANISH", $currentPlayer, "DECK,sonic_boom_yellow-{0}", 1);
      AddDecisionQueue("ELSE", $currentPlayer, "-");
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, "{1}", 1);
      AddDecisionQueue("LESSTHANPASS", $currentPlayer, 1, 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, CardLink($cardID, $cardID)." shows the top of your deck is <1>", 1);
      AddDecisionQueue("OK", $currentPlayer, "whether to banish a card with ". CardLink($cardID, $cardID), 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "-");
      return "";
    case "forked_lightning_red":
      $arcaneBonus = ConsumeArcaneBonus($currentPlayer);
      $damage = ArcaneDamage($cardID) + $arcaneBonus;
      DealArcane($damage, 0, "PLAYCARD", $cardID, resolvedTarget: $target);
      DealArcane($damage, 0, "PLAYCARD", $cardID, resolvedTarget: $target);
      return "";
    case "lesson_in_lava_yellow":
      DealArcane(ArcaneDamage($cardID), 1, "PLAYCARD", $cardID, resolvedTarget: $target);
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
      AddDecisionQueue("LESSTHANPASS", $currentPlayer, 1);
      AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_tutor_a_card", 1);
      AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, "{0}", 1);
      AddDecisionQueue("FINDINDICES", $currentPlayer, $cardID, 1);
      AddDecisionQueue("MAYCHOOSEDECK", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
      AddDecisionQueue("MULTIADDTOPDECK", $currentPlayer, "-", 1);
      AddDecisionQueue("REVEALCARDS", $currentPlayer, "-", 1);
      return "";
    case "tome_of_aetherwind_red":
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts, 1);
      AddDecisionQueue("MODAL", $currentPlayer, "TOMEOFAETHERWIND", 1);
      return "";
    case "absorb_in_aether_red":
    case "absorb_in_aether_yellow":
    case "absorb_in_aether_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "aether_spindle_red":
    case "aether_spindle_yellow":
    case "aether_spindle_blue":
      DealArcane(ArcaneDamage($cardID), 1, "PLAYCARD", $cardID, resolvedTarget: $target);
      AddDecisionQueue("OPTX", $currentPlayer, "<-", 1);
      return "";
    case "stir_the_aetherwinds_red":
    case "stir_the_aetherwinds_yellow":
    case "stir_the_aetherwinds_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      SetClassState($currentPlayer, $CS_NextWizardNAAInstant, 1);
      return "";
    case "aether_flare_red":
    case "aether_flare_yellow":
    case "aether_flare_blue":
      DealArcane(ArcaneDamage($cardID), 1, "PLAYCARD", $cardID, resolvedTarget: $target);
      AddDecisionQueue("BUFFARCANE", $currentPlayer, $cardID, 1);
      return "";
    case "index_red":
    case "index_yellow":
    case "index_blue":
      if ($cardID == "index_red") $count = 5;
      else if ($cardID == "index_yellow") $count = 4;
      else $count = 3;
      AddDecisionQueue("FINDINDICES", $currentPlayer, "DECKTOPXREMOVE," . $count);
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
      AddDecisionQueue("CHOOSECARD", $currentPlayer, "<-", 1);
      AddDecisionQueue("MULTIADDTOPDECK", $currentPlayer, "DECK");
      AddDecisionQueue("OP", $currentPlayer, "REMOVECARD", 1);
      AddDecisionQueue("CHOOSEBOTTOM", $currentPlayer, "<-", 1);
      return "";
    case "reverberate_red":
    case "reverberate_yellow":
    case "reverberate_blue":
      DealArcane(ArcaneDamage($cardID), 1, "PLAYCARD", $cardID, resolvedTarget: $target);
      AddDecisionQueue("LESSTHANPASS", $currentPlayer, 1);
      AddDecisionQueue("SETDQVAR", $currentPlayer, "1", 1);
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYHAND:type=A;class=WIZARD;maxCost={1}", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to banish or pass", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZBANISH", $currentPlayer, "HAND,INST," . $currentPlayer, 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      return "";
    case "scalding_rain_red":
    case "scalding_rain_yellow":
    case "scalding_rain_blue":
    case "zap_red":
    case "zap_yellow":
    case "zap_blue":
    case "voltic_bolt_red":
    case "voltic_bolt_yellow":
    case "voltic_bolt_blue":
      DealArcane(ArcaneDamage($cardID), 0, "PLAYCARD", $cardID, resolvedTarget: $target);
      return "";
    default:
      return "";
  }

}

function ARCWizardHitEffect($cardID)
{
  return "";
}

//Parameters:
//Player = Player controlling the arcane effects
//target =
//0: My Hero + Their Hero
//1: Their Hero only
//2: Any Target
//3: Their Hero + Their Allies
//4: My Hero only (For afflictions)
function DealArcane($damage, $target = 0, $type = "PLAYCARD", $source = "NA", $fromQueue = false, $player = 0, $mayAbility = false, $limitDuplicates = false, $skipHitEffect = false, $resolvedTarget = "-", $nbArcaneInstance = 1, $isPassable = 0, $meldState = "-")
{
  global $currentPlayer, $CS_ArcaneTargetsSelected;
  if ($player == 0) $player = $currentPlayer;
  $otherPlayer = $player == 1 ? 2 : 1;
  if ($damage > 0) {
    $damage += CurrentEffectArcaneModifier($source, $player, meldState: $meldState) * $nbArcaneInstance;
    $damage += CurrentEffectDamageModifiers($player, $source, $type);
    if ($type != "PLAYCARD") WriteLog(CardLink($source, $source) . " is dealing " . $damage . " arcane damage.");
    if ($fromQueue) {
      if (!$limitDuplicates) {
        PrependDecisionQueue("PASSPARAMETER", $player, "{0}");
        PrependDecisionQueue("SETCLASSSTATE", $player, $CS_ArcaneTargetsSelected); //If already selected for arcane multiselect (e.g. Singe/Azvolai)
        PrependDecisionQueue("PASSPARAMETER", $player, "-");
      }
      if (!$skipHitEffect) PrependDecisionQueue("ARCANEHITEFFECT", $player, $source, 1);
      PrependDecisionQueue("DEALARCANE", $player, $damage . "-" . $source . "-" . $type, 1);
      if ($resolvedTarget != "") {
        PrependDecisionQueue("PASSPARAMETER", $player, $resolvedTarget);
      } else {
        PrependDecisionQueue("SETDQVAR", $player, "0", 1);
        if ($mayAbility) PrependDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
        else PrependDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
        PrependDecisionQueue("SETDQCONTEXT", $player, "Choose a target for <0>");
        PrependDecisionQueue("FINDINDICES", $player, "ARCANETARGET," . $target);
        PrependDecisionQueue("SETDQVAR", $player, "0");
        PrependDecisionQueue("PASSPARAMETER", $player, $source);
      }
    } else {
      if ($resolvedTarget != "-") {
        AddDecisionQueue("PASSPARAMETER", $player, $resolvedTarget, ($isPassable ? 1 : 0));
      } else {
        AddDecisionQueue("PASSPARAMETER", $player, $source, ($isPassable ? 1 : 0));
        AddDecisionQueue("SETDQVAR", $player, "0", ($isPassable ? 1 : 0));
        AddDecisionQueue("FINDINDICES", $player, "ARCANETARGET," . $target, ($isPassable ? 1 : 0));
        AddDecisionQueue("SETDQCONTEXT", $player, "Choose a target for <0>", ($isPassable ? 1 : 0));
        $allies = GetAllies($player);
        $theirAllies = GetAllies($otherPlayer);
        if(ShouldAutotargetOpponent($player) && $target == 0) {
          AddDecisionQueue("PASSPARAMETER", $player, "THERICHAR-0", 1);
        }
        elseif (ShouldAutotargetOpponent($player) && ($target == 2 || $target == 3) && count($allies) <= 0 && count($theirAllies) <= 0) {
          AddDecisionQueue("PASSPARAMETER", $player, "THERICHAR-0", 1);
        }
        else{
          if ($mayAbility) AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
          else AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
        }
        AddDecisionQueue("SETDQVAR", $player, "0", 1);
      }
      AddDecisionQueue("DEALARCANE", $player, $damage . "-" . $source . "-" . $type, 1);
      if (!$skipHitEffect) AddDecisionQueue("ARCANEHITEFFECT", $player, $source, 1);
      if (!$limitDuplicates) {
        AddDecisionQueue("PASSPARAMETER", $player, "-");
        AddDecisionQueue("SETCLASSSTATE", $player, $CS_ArcaneTargetsSelected);
        AddDecisionQueue("PASSPARAMETER", $player, "{0}");
      }
    }
  }
}


//target type return values
//-1: no target
//0: My Hero + Their Hero
//1: Their Hero only
//2: Any Target
//3: Their Hero + Their Allies
//4: My Hero only (For afflictions)
function PlayRequiresTarget($cardID, $from)
{
  switch ($cardID) {
    case "blazing_aether_red":
      return 0;//Blazing Aether
    case "sonic_boom_yellow":
      return 1;//Sonic Boom
    case "forked_lightning_red":
      return 1;//Forked Lightning
    case "lesson_in_lava_yellow":
      return 1;//Lesson in Lava
    case "aether_spindle_red":
    case "aether_spindle_yellow":
    case "aether_spindle_blue":
      return 1;//Aether Spindle
    case "aether_flare_red":
    case "aether_flare_yellow":
    case "aether_flare_blue":
      return 1;//Aether Flare
    case "reverberate_red":
    case "reverberate_yellow":
    case "reverberate_blue":
      return 1;//Reverberate
    case "scalding_rain_red":
    case "scalding_rain_yellow":
    case "scalding_rain_blue":
      return 0;//Scalding Rain
    case "zap_red":
    case "zap_yellow":
    case "zap_blue":
      return 0;//Zap
    case "voltic_bolt_red":
    case "voltic_bolt_yellow":
    case "voltic_bolt_blue":
      return 0;//Voltic Bolt
    case "chain_lightning_yellow":
      return 1;//Chain Lightning
    case "foreboding_bolt_red":
    case "foreboding_bolt_yellow":
    case "foreboding_bolt_blue":
      return 0;//Foreboding Bolt
    case "rousing_aether_red":
    case "rousing_aether_yellow":
    case "rousing_aether_blue":
      return 0;//Rousing Aether
    case "snapback_red":
    case "snapback_yellow":
    case "snapback_blue":
      return 0;//Snapback
    case "aether_wildfire_red":
      return 1;//Aether Wildfire
    case "emeritus_scolding_red":
    case "emeritus_scolding_yellow":
    case "emeritus_scolding_blue":
      return 0;//Emeritus Scolding
    case "timekeepers_whim_red":
    case "timekeepers_whim_yellow":
    case "timekeepers_whim_blue":
      return 0;//Timekeeper's Whim
    case "encase_red":
      return 2;//Encase
    case "freezing_point_red":
      return 0;//Freezing Point
    case "ice_eternal_blue":
      return 0;//Ice Eternal
    case "succumb_to_winter_red":
    case "succumb_to_winter_yellow":
    case "succumb_to_winter_blue":
      return 2;//Succumb to Winter
    case "aether_icevein_red":
    case "aether_icevein_yellow":
    case "aether_icevein_blue":
      return 2;//Aether Icevein
    case "icebind_red":
    case "icebind_yellow":
    case "icebind_blue":
      return 2;//Icebind
    case "polar_cap_red":
    case "polar_cap_yellow":
    case "polar_cap_blue":
      return 2;//Polar Cap
    case "aether_hail_red":
    case "aether_hail_yellow":
    case "aether_hail_blue":
      return 2;//Aether Hail
    case "frosting_red":
    case "frosting_yellow":
    case "frosting_blue":
      return 2;//Frosting
    case "ice_bolt_red":
    case "ice_bolt_yellow":
    case "ice_bolt_blue":
      return 2;//Ice Bolt
    case "waning_moon":
      return 0;//Waning Moon
    case "dampen_red":
    case "dampen_yellow":
    case "dampen_blue":
      return 2;//Dampen
    case "aether_dart_red":
    case "aether_dart_yellow":
    case "aether_dart_blue":
      return 2;//Aether Dart
    case "singe_red":
    case "singe_yellow":
    case "singe_blue":
      return 1;//Singe
    case "mind_warp_yellow":
      return 0;//Mind Warp
    case "swell_tidings_red":
      return 0;//Swell Tidings
    case "aether_quickening_red":
    case "aether_quickening_yellow":
    case "aether_quickening_blue":
      return 0;//Aether Quickening
    case "prognosticate_red":
    case "prognosticate_yellow":
    case "prognosticate_blue":
      return 0;//Prognosticate
    case "sap_red":
    case "sap_yellow":
    case "sap_blue":
      return 0;//Sap
    case "destructive_aethertide_blue":
      return 2;//Destructive Aethertide
    case "eternal_inferno_red"://eternal inferno
      return 2;
    case "chorus_of_the_amphitheater_red":
    case "chorus_of_the_amphitheater_yellow":
    case "chorus_of_the_amphitheater_blue":
      return (GetResolvedAbilityType($cardID, "HAND") == "A") ? 2 : -1; //Chorus of Amphitheater
    case "pop_the_bubble_red":
    case "pop_the_bubble_yellow":
    case "pop_the_bubble_blue":
      return 0;//Pop the Bubble
    case "arcane_twining_red":
    case "arcane_twining_yellow":
    case "arcane_twining_blue":
      return (GetResolvedAbilityType($cardID, "HAND") == "A") ? 2 : -1; //Arcane Twining
    case "etchings_of_arcana_red":
    case "etchings_of_arcana_yellow":
    case "etchings_of_arcana_blue":
      return 0;//Etchings of Arcana
    case "open_the_flood_gates_red": 
    case "open_the_flood_gates_yellow": 
    case "open_the_flood_gates_blue":
      return 0; //Open the Flood Gates
    case "overflow_the_aetherwell_red":
    case "overflow_the_aetherwell_yellow":
    case "overflow_the_aetherwell_blue":
      return 0;//Overflow the Aetherwell
    case "perennial_aetherbloom_red":
    case "perennial_aetherbloom_yellow":
    case "perennial_aetherbloom_blue":
      return 0;//Perennial Aetherbloom
    case "trailblazing_aether_red":
    case "trailblazing_aether_yellow":
    case "trailblazing_aether_blue":
      return 0;//Trailblazing Aether
    case "aether_arc_blue":
      return 1;
    default:
      return -1;
  }
}

//Parameters:
//Player = Player controlling the arcane effects
//target =
// 0: My Hero + Their Hero
// 1: Their Hero only
// 2: Any Target
// 3: Their Hero + Their Allies
// 4: My Hero only (For afflictions)
// 5: Their Allies only
function GetArcaneTargetIndices($player, $target): string
{
  global $CS_ArcaneTargetsSelected;
  $otherPlayer = ($player == 1 ? 2 : 1);
  if ($target == 4) return "MYCHAR-0";
  if ($target != 4 && $target != 5) $rv = "THEIRCHAR-0";
  else $rv = "";
  if (($target == 0 && !ShouldAutotargetOpponent($player)) || $target == 2) $rv .= ",MYCHAR-0";
  if ($target == 2) {
    $theirAllies = &GetAllies($otherPlayer);
    for ($i = 0; $i < count($theirAllies); $i += AllyPieces()) $rv .= ",THEIRALLY-" . $i;
    $myAllies = &GetAllies($player);
    for ($i = 0; $i < count($myAllies); $i += AllyPieces()) $rv .= ",MYALLY-" . $i;
  } else if ($target == 3 || $target == 5) {
    $theirAllies = &GetAllies($otherPlayer);
    for ($i = 0; $i < count($theirAllies); $i += AllyPieces()) {
      if ($rv != "") $rv .= ",";
      $rv .= "THEIRALLY-" . $i;
    }
  }
  $targets = explode(",", $rv);
  $targetsSelected = GetClassState($player, $CS_ArcaneTargetsSelected);
  for ($i = count($targets) - 1; $i >= 0; --$i) if (DelimStringContains($targetsSelected, $targets[$i])) unset($targets[$i]);
  return implode(",", $targets);
}

//Visual used for current effect only
//!Effects are never removed from here
function ArcaneModifierAmount($source, $player, $index) 
{
  global $currentTurnEffects;
    $effectArr = explode(",", $currentTurnEffects[$index]);
    if ($currentTurnEffects[$index + 1] != $player || $source != $effectArr[0]) return 0;
    switch ($effectArr[0]) {
      case "crucible_of_aetherweave":
        return 1;
      case "tome_of_aetherwind_red":
        return 1;
      case "absorb_in_aether_red":
      case "absorb_in_aether_yellow":
      case "absorb_in_aether_blue":
        return 2;
      case "stir_the_aetherwinds_red":
        return 3;
      case "stir_the_aetherwinds_yellow":
        return 2;
      case "stir_the_aetherwinds_blue":
        return 1;
      case "aether_flare_red":
      case "aether_flare_yellow":
      case "aether_flare_blue":
        return $effectArr[1];
      case "metacarpus_node":
        return 1;
      case "cindering_foresight_red":
      case "cindering_foresight_yellow":
      case "cindering_foresight_blue":
        return 1;
      case "rousing_aether_red":
      case "rousing_aether_yellow":
      case "rousing_aether_blue":
        return 1;
      case "surgent_aethertide":
        return $effectArr[1];
      case "blessing_of_aether_red":
        return 3;
      case "blessing_of_aether_yellow":
        return 2;
      case "blessing_of_aether_blue":
        return 1;
      case "tempest_aurora_red":
      case "tempest_aurora_yellow":
      case "tempest_aurora_blue":
        return 1;  
      case "aether_wildfire_red":
        return $effectArr[1];
      case "rampant_growth__life_yellow":
        return $effectArr[1];
      case "will_of_arcana_blue":
      case "staff_of_verdant_shoots-AMP":
      case "sigil_of_aether_blue"://sigil of aether
      case "high_voltage_blue":
      case "arcane_twining_red":
      case "arcane_twining_yellow":
      case "arcane_twining_blue":
      case "photon_splicing_red":
      case "photon_splicing_yellow":
      case "photon_splicing_blue":
      case "kindle_red":
      case "hold_focus":
        return 1;
      case "volzar_the_lightning_rod":
        return $effectArr[1];
      case "channel_the_millennium_tree_red":
        return 3;
      case "aether_bindings_of_the_third_age-AMP":
        return 1;
      case "chorus_of_the_amphitheater_red":
      case "chorus_of_the_amphitheater_yellow":
      case "chorus_of_the_amphitheater_blue":
        return 1;
      case "exploding_aether_red":
      case "exploding_aether_yellow":
      case "exploding_aether_blue":
        return $effectArr[1];
        break;
      default:
        break;
    }
  return 0;
}

function CurrentEffectArcaneModifier($source, $player, $meldState = "-"): int|string
{
  global $currentTurnEffects;
  $modifier = 0;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $remove = false;
    $effectArr = explode(",", $currentTurnEffects[$i]);
    switch ($effectArr[0]) {
      case "aether_wildfire_red":
        $cardType = CardType($source);
        if ((DelimStringContains($cardType, "A") || $cardType == "AA")
          && (!HasMeld($source) || DelimStringContains($meldState, "A"))) $modifier += $effectArr[1];
        break;
      case "rampant_growth__life_yellow":
        if ($currentTurnEffects[$i + 1] != $player) break;
        $modifier += $effectArr[1];
        $remove = true;
        break;
      case "will_of_arcana_blue":
      case "staff_of_verdant_shoots-AMP":
      case "sigil_of_aether_blue"://sigil of aether
      case "high_voltage_blue":
      case "arcane_twining_red":
      case "arcane_twining_yellow":
      case "arcane_twining_blue":
      case "photon_splicing_red":
      case "photon_splicing_yellow":
      case "photon_splicing_blue":
      case "kindle_red":
      case "hold_focus":
        if ($currentTurnEffects[$i + 1] != $player) break;
        $modifier += 1;
        $remove = true;
        break;
      case "volzar_the_lightning_rod":
        if ($currentTurnEffects[$i + 1] != $player) break;
        $modifier += $effectArr[1];
        $remove = true;
        break;
      case "channel_the_millennium_tree_red":
        if ($currentTurnEffects[$i + 1] != $player) break;
        $modifier += 3;
        $remove = true;
        break;
      case "aether_bindings_of_the_third_age-AMP":
        if ($currentTurnEffects[$i + 1] != $player) break;
        $modifier += 1;
        $remove = true;
        break;
      case "chorus_of_the_amphitheater_red":
      case "chorus_of_the_amphitheater_yellow":
      case "chorus_of_the_amphitheater_blue":
        if ($currentTurnEffects[$i + 1] != $player) break;
        $modifier += 1;
        break;
      case "exploding_aether_red":
      case "exploding_aether_yellow":
      case "exploding_aether_blue":
        if ($currentTurnEffects[$i + 1] != $player) break;
        $modifier += $effectArr[1];
        $remove = true;
        break;
      default:
        break;
    }
    if ($remove) RemoveCurrentTurnEffect($i);
  }
  return $modifier;
}

function ArcaneDamage($cardID): int
{
  //Blaze - Replacement effects aren't considered when evaluating how much an effect does so Emeritus Scolding (blu) would require 2 counters.
  return match ($cardID) {
    "voltic_bolt_red", "timekeepers_whim_red", "freezing_point_red", "ice_bolt_red", "succumb_to_winter_red", "aether_icevein_red", "swell_tidings_red" => 5,
    "aether_spindle_red", "scalding_rain_red", "voltic_bolt_yellow", "rousing_aether_red", "emeritus_scolding_red", "aether_wildfire_red", "timekeepers_whim_yellow", "dampen_red", "ice_bolt_yellow", "aether_hail_red", "polar_cap_red",
    "succumb_to_winter_yellow", "aether_icevein_yellow", "aether_quickening_red", "eternal_inferno_red", "chorus_of_the_amphitheater_red", "photon_splicing_red" => 4,
    "sonic_boom_yellow", "lesson_in_lava_yellow", "aether_spindle_yellow", "aether_flare_red", "reverberate_red", "scalding_rain_yellow", "zap_red", "voltic_bolt_blue", "emeritus_scolding_yellow", "timekeepers_whim_blue", "aether_quickening_yellow",
    "prognosticate_red", "sap_red", "chain_lightning_yellow", "foreboding_bolt_red", "rousing_aether_yellow", "snapback_red", "aether_dart_red", "dampen_yellow", "ice_bolt_blue", "frosting_red", "aether_hail_yellow",
    "polar_cap_yellow", "succumb_to_winter_blue", "aether_icevein_blue", "encase_red", "icebind_red", "chorus_of_the_amphitheater_yellow", "pop_the_bubble_red", "arcane_twining_red", "etchings_of_arcana_red", "open_the_flood_gates_red", "overflow_the_aetherwell_red", "perennial_aetherbloom_red", "trailblazing_aether_red", "glyph_overlay_red",
    "photon_splicing_yellow" => 3,
    "forked_lightning_red", "foreboding_bolt_yellow", "rousing_aether_blue", "snapback_yellow", "emeritus_scolding_blue", "aether_dart_yellow", "dampen_blue", "frosting_yellow", "aether_hail_blue", "polar_cap_blue", "icebind_yellow",
    "mind_warp_yellow", "aether_quickening_blue", "prognosticate_yellow", "sap_yellow", "chorus_of_the_amphitheater_blue", "pop_the_bubble_yellow", "arcane_twining_yellow", "etchings_of_arcana_yellow", "open_the_flood_gates_yellow", "overflow_the_aetherwell_yellow", "perennial_aetherbloom_yellow", "trailblazing_aether_yellow", "glyph_overlay_yellow", "aether_spindle_blue",
    "aether_flare_yellow", "reverberate_yellow", "scalding_rain_blue", "zap_yellow", "photon_splicing_blue" => 2,
    "aether_flare_blue", "reverberate_blue", "zap_blue", "foreboding_bolt_blue", "snapback_blue", "aether_dart_blue", "singe_red", "singe_yellow", "singe_blue", "frosting_blue", "icebind_blue",
    "prognosticate_blue", "sap_blue", "aether_arc_blue", "destructive_aethertide_blue", "pop_the_bubble_blue", "arcane_twining_blue", "etchings_of_arcana_blue", "open_the_flood_gates_blue", "overflow_the_aetherwell_blue", "perennial_aetherbloom_blue", "trailblazing_aether_blue", "glyph_overlay_blue" => 1,
    "scour_blue" => 0,
    default => -1,
  };
}

function ActionsThatDoXArcaneDamage($cardID)
{
  switch ($cardID) {
    case "blazing_aether_red":
    case "scour_blue":
    case "ice_eternal_blue":
      return true;
    default:
      return false;
  }
}

function ActionsThatDoArcaneDamage($cardID, $playerID)
{
  switch ($cardID) {
    case "sonic_boom_yellow":
    case "forked_lightning_red":
    case "lesson_in_lava_yellow":
    case "aether_spindle_red":
    case "aether_spindle_yellow":
    case "aether_spindle_blue":
    case "aether_flare_red":
    case "aether_flare_yellow":
    case "aether_flare_blue":
    case "reverberate_red":
    case "reverberate_yellow":
    case "reverberate_blue":
    case "scalding_rain_red":
    case "scalding_rain_yellow":
    case "scalding_rain_blue":
    case "zap_red":
    case "zap_yellow":
    case "zap_blue":
    case "voltic_bolt_red":
    case "voltic_bolt_yellow":
    case "voltic_bolt_blue":
      return true;
    case "chain_lightning_yellow":
    case "foreboding_bolt_red":
    case "foreboding_bolt_yellow":
    case "foreboding_bolt_blue":
    case "rousing_aether_red":
    case "rousing_aether_yellow":
    case "rousing_aether_blue":
    case "snapback_red":
    case "snapback_yellow":
    case "snapback_blue":
      return true;
    case "aether_wildfire_red":
    case "emeritus_scolding_red":
    case "emeritus_scolding_yellow":
    case "emeritus_scolding_blue":
    case "timekeepers_whim_red":
    case "timekeepers_whim_yellow":
    case "timekeepers_whim_blue":
      return true;
    case "encase_red":
    case "freezing_point_red":
    case "succumb_to_winter_red":
    case "succumb_to_winter_yellow":
    case "succumb_to_winter_blue":
    case "aether_icevein_red":
    case "aether_icevein_yellow":
    case "aether_icevein_blue":
    case "icebind_red":
    case "icebind_yellow":
    case "icebind_blue":
    case "polar_cap_red":
    case "polar_cap_yellow":
    case "polar_cap_blue":
    case "aether_hail_red":
    case "aether_hail_yellow":
    case "aether_hail_blue":
    case "frosting_red":
    case "frosting_yellow":
    case "frosting_blue":
    case "ice_bolt_red":
    case "ice_bolt_yellow":
    case "ice_bolt_blue":
    case "dampen_red":
    case "dampen_yellow":
    case "dampen_blue":
    case "aether_dart_red":
    case "aether_dart_yellow":
    case "aether_dart_blue":
    case "singe_red":
    case "singe_yellow":
    case "singe_blue":
      return true;
    case "mind_warp_yellow":
    case "swell_tidings_red":
    case "aether_quickening_red":
    case "aether_quickening_yellow":
    case "aether_quickening_blue":
    case "prognosticate_red":
    case "prognosticate_yellow":
    case "prognosticate_blue":
    case "sap_red":
    case "sap_yellow":
    case "sap_blue":
      return true;
    case "aether_arc_blue":
      return true;
    case "vaporize__shock_yellow":
    case "burn_up__shock_red":
    case "pulsing_aether__life_red":
    case "comet_storm__shock_red":
    case "null__shock_yellow":
    case "destructive_aethertide_blue":
    case "eternal_inferno_red":
    case "chorus_of_the_amphitheater_red":
    case "chorus_of_the_amphitheater_yellow":
    case "chorus_of_the_amphitheater_blue":
    case "glyph_overlay_red":
    case "glyph_overlay_yellow":
    case "glyph_overlay_blue":
    case "pop_the_bubble_red":
    case "pop_the_bubble_yellow":
    case "pop_the_bubble_blue":
    case "arcane_twining_red":
    case "arcane_twining_yellow":
    case "arcane_twining_blue":
    case "etchings_of_arcana_red":
    case "etchings_of_arcana_yellow":
    case "etchings_of_arcana_blue":
    case "open_the_flood_gates_red":
    case "open_the_flood_gates_yellow":
    case "open_the_flood_gates_blue":
    case "overflow_the_aetherwell_red":
    case "overflow_the_aetherwell_yellow":
    case "overflow_the_aetherwell_blue":
    case "perennial_aetherbloom_red":
    case "perennial_aetherbloom_yellow":
    case "perennial_aetherbloom_blue":
    case "trailblazing_aether_red":
    case "trailblazing_aether_yellow":
    case "trailblazing_aether_blue":
    case "photon_splicing_red":
    case "photon_splicing_yellow":
    case "photon_splicing_blue":
    case "regrowth__shock_blue":
      return true;
    default:
      return false;
  }
}

function ArcaneBarrierChoices($playerID, $max)
{
  global $currentTurnEffects;
  $barrierArray = [];
  for ($i = 0; $i < 4; ++$i) $barrierArray[$i] = 0;
  $character = GetPlayerCharacter($playerID);
  $total = 0;
  for ($i = 0; $i < count($character); $i += CharacterPieces()) {
    if ($character[$i + 1] == 0 || $character[$i + 12] == "DOWN") continue;
    switch ($character[$i]) {
      case "achilles_accelerator":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "skullbone_crosswrap":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "bulls_eye_bracers":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "crown_of_dichotomy":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "storm_striders":
        ++$barrierArray[2];
        $total += 2;
        break;
      case "robe_of_rapture":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "arcanite_skullcap":
        if (PlayerHasLessHealth($playerID)) {
          ++$barrierArray[3];
          $total += 3;
        }
        break;
      case "nullrune_hood":
      case "nullrune_robe":
      case "nullrune_gloves":
      case "nullrune_boots":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "skullhorn":
        ++$barrierArray[2];
        $total += 2;
        break;
      case "viziertronic_model_i":
        ++$barrierArray[2];
        $total += 2;
        break;
      case "metacarpus_node":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "heart_of_ice":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "vexing_quillhand":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "crown_of_reflection":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "arcane_lantern":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "silent_stilettos":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "tide_flippers":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "alluvion_constellas":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "spellfire_cloak":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "trench_of_sunken_treasure":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "spoiled_skull":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "grimoire_of_the_haunt":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "dyadic_carapace":
        ++$barrierArray[2];
        $total += 2;
        break;
      case "evo_recall_blue":
      case "evo_recall_blue_equip":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "evo_heartdrive_blue":
      case "evo_heartdrive_blue_equip":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "evo_shortcircuit_blue":
      case "evo_shortcircuit_blue_equip":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "evo_speedslip_blue":
      case "evo_speedslip_blue_equip":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "hidden_agenda":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "lightning_greaves":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "widow_veil_respirator":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "widow_back_abdomen":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "widow_claw_tarsus":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "widow_web_crawler":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "adaptive_dissolver":
        ++$barrierArray[1];
        $total += 1;
        break;
      case "calming_cloak":
      case "calming_gesture":
        ++$barrierArray[1];
        $total += 1;
        break;
      default:
        break;
    }
  }
  $items = GetItems($playerID);
  for ($i = 0; $i < count($items); $i += ItemPieces()) {
    switch ($items[$i]) {
      case "rusted_relic_blue":
        ++$barrierArray[1];
        $total += 1;
        break;
      default:
        break;
    }
  }
  $allies = GetAllies($playerID);
  for ($i = 0; $i < count($allies); $i += AllyPieces()) {
    switch ($allies[$i]) {
      case "aether_ashwing":
        ++$barrierArray[1];
        $total += 1;
        break;
      default:
        break;
    }
  }
  for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectsPieces()) {
    switch ($currentTurnEffects[$i]) {
      case "aether_sink_yellow":
        ++$barrierArray[2];
        $total += 2;
        break;
      default:
        break;
    }
  }
  $choiceArray = [];
  array_push($choiceArray, 0);
  if ($barrierArray[1] > 0) array_push($choiceArray, 1);
  if ($barrierArray[2] > 0 || ($barrierArray[1] > 1 && $max > 1 && $total >= 2)) array_push($choiceArray, 2);
  if ($barrierArray[3] > 0 || ($max > 2 && $total >= 3)) array_push($choiceArray, 3);
  for ($i = 4; $i <= $max; ++$i) {
    if ($i <= $total) array_push($choiceArray, $i);
  }
  return implode(",", $choiceArray);
}

function CheckSpellvoid($player, $damage)
{
  $spellvoidChoices = SearchSpellvoidIndices($player);
  if ($spellvoidChoices != "") {
    PrependDecisionQueue("SPELLVOIDCHOICES", $player, $damage, 1);
    PrependDecisionQueue("MAYCHOOSEMULTIZONE", $player, $spellvoidChoices);
    PrependDecisionQueue("SETDQCONTEXT", $player, "Choose if you want to use a Spellvoid equipment");
  }
}


function ArcaneHitEffect($player, $source, $target, $damage)
{
  switch ($source) {
    case "encase_red":
      if (MZIsPlayer($target) && $damage > 0) {
        AddDecisionQueue("SPECIFICCARD", MZPlayerID($player, $target), "ENCASEDAMAGE", 1);
      }
      break;
    case "aether_icevein_red":
    case "aether_icevein_yellow":
    case "aether_icevein_blue":
      if (MZIsPlayer($target)) PayOrDiscard(MZPlayerID($player, $target), 2, true);
      break;
    case "icebind_red":
    case "icebind_yellow":
    case "icebind_blue":
      if (MZIsPlayer($target) && $damage > 0) {
        AddDecisionQueue("MULTIZONEINDICES", $player, "THEIRARS", 1);
        AddDecisionQueue("SETDQCONTEXT", $player, "Choose a card to freeze", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
        AddDecisionQueue("MZOP", $player, "FREEZE", 1);
      }
      break;
    case "polar_cap_red":
    case "polar_cap_yellow":
    case "polar_cap_blue":
      if (MZIsPlayer($target) && $damage > 0) {
        AddDecisionQueue("PLAYAURA", MZPlayerID($player, $target), "frostbite-1", 1);
      }
      break;
    case "sigil_of_aether_blue":
      AddCurrentTurnEffect($source, $player);
      Writelog(CardLink($source, $source) . " is amping 1");
      break;
    default:
      break;
  }

  if ($damage > 0 && CardType($source) != "W" && SearchCurrentTurnEffects("conduit_of_frostburn", $player, true)) AddDecisionQueue("OP", MZPlayerID($player, $target), "DESTROYFROZENARSENAL");
  $auras = &GetAuras($player);
  for ($i = 0; $i < count($auras); $i += AuraPieces()) {
    switch ($auras[$i]) {
      case "ring_of_roses_yellow":
        if ($auras[$i+5] > 0) {
          AddLayer("TRIGGER", $player, $auras[$i]);
          $auras[$i+5] -= 1;
        }
        break;
      default:
        break;
    }
  }

  if (HasSurge($source) && $damage > ArcaneDamage($source)) {
    ProcessSurge($source, $player, $target);
  }
}

function ProcessSurge($cardID, $player, $target)
{
  global $mainPlayer;
  $targetPlayer = MZPlayerID($player, $target);
  switch ($cardID) {
    case "mind_warp_yellow":
      $hand = &GetHand($targetPlayer);
      $numToDraw = count($hand) - 1;
      if ($numToDraw < 0) $numToDraw = 0;
      $deck = &GetDeck($targetPlayer);
      while (count($hand) > 0) array_push($deck, array_shift($hand));
      for ($i = 0; $i < $numToDraw; ++$i) Draw($targetPlayer);
      WriteLog("Mind Warp warps the target's mind.");
      AddDecisionQueue("SHUFFLEDECK", $targetPlayer, "-");
      break;
    case "swell_tidings_red":
      PlayAura("ponder", $player);
      WriteLog(CardLink($cardID, $cardID) . " created a " . CardLink("ponder", "ponder") . " token");
      break;
    case "aether_quickening_red":
    case "aether_quickening_yellow":
    case "aether_quickening_blue":
      if (CurrentEffectPreventsGoAgain($cardID) || $player != $mainPlayer) break;
      GainActionPoints();
      WriteLog(CardLink($cardID, $cardID) . " gained go again");
      break;
    case "prognosticate_red":
    case "prognosticate_yellow":
    case "prognosticate_blue":
      PlayerOpt($player, 1);
      break;
    case "sap_red":
    case "sap_yellow":
    case "sap_blue":
      AddDecisionQueue("MULTIZONEINDICES", $player, "THEIRCHAR:type=E;hasEnergyCounters=true");
      AddDecisionQueue("SETDQCONTEXT", $player, "Remove an energy counter from a card");
      AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
      AddDecisionQueue("MZOP", $player, "GETCARDINDEX", 1);
      AddDecisionQueue("REMOVECOUNTER", $targetPlayer, $cardID, 1);
      break;
    case "destructive_aethertide_blue":
      if (MZIsPlayer($target)) {
        AddDecisionQueue("MULTIZONEINDICES", $player, "THEIRARS", 1);
        AddDecisionQueue("SETDQCONTEXT", $player, "Choose a card you want to destroy from their arsenal", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
        AddDecisionQueue("MZDESTROY", $player, false, 1);
        }
      break;
    case "eternal_inferno_red"://eternal inferno
      BanishCardForPlayer("eternal_inferno_red", $player, "MYDISCARD", "TT", "eternal_inferno_red");
      $discard = &GetDiscard($player);
      for($i = 0; $i < DiscardPieces(); $i++){
        array_pop($discard);
      }
      break;
    case "pop_the_bubble_red":
    case "pop_the_bubble_yellow":
    case "pop_the_bubble_blue":
      MZChooseAndDestroy($player, "THEIRAURAS");
      break;
    case "etchings_of_arcana_red":
    case "etchings_of_arcana_yellow":
    case "etchings_of_arcana_blue":
      WriteLog("Surge active, returning a sigil from graveyard to hand");
      MZMoveCard($player, "MYDISCARD:subtype=Aura;nameIncludes=Sigil", "MYHAND", may: true);
      break;
    case "open_the_flood_gates_red": 
    case "open_the_flood_gates_yellow": 
    case "open_the_flood_gates_blue":
      WriteLog("Surge active, drawing 2 cards");
      Draw($player);
      Draw($player);
      break;
    case "overflow_the_aetherwell_red":
    case "overflow_the_aetherwell_yellow":
    case "overflow_the_aetherwell_blue":
      WriteLog("Surge active, gaining 2 resources");
      GainResources($player, 2);
      break;
    case "perennial_aetherbloom_red":
    case "perennial_aetherbloom_yellow":
    case "perennial_aetherbloom_blue": //perennial aetherbloom
      WriteLog("Surge active, returning on the bottom of the deck");
      AddBottomDeck($cardID, $player, "STACK"); //create a copy on the bottom
      $discard = &GetDiscard($player);
      for($i = 0; $i < DiscardPieces(); $i++){
        array_pop($discard);
      }
      break;
    case "glyph_overlay_red":
    case "glyph_overlay_yellow":
    case "glyph_overlay_blue":
      WriteLog("Surge Active, gaining 1 life and returning sigils to the deck");
      GainHealth(1, $player);
      $auras = &GetAuras($player);
      $sigilFound = false;
      for ($i = count($auras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
        $auraName = CardName($auras[$i]);
        if (DelimStringContains($auraName, "Sigil", partial: true)) {
          AddBottomDeck($auras[$i], $player, "STACK");
          RemoveAura($player, $i, $auras[$i + 4]);
          $sigilFound = true;
        }
      }
      if($sigilFound)AddDecisionQueue("SHUFFLEDECK", $player, "-");
      break;
    case "trailblazing_aether_red":
    case "trailblazing_aether_yellow":
    case "trailblazing_aether_blue":
      if (CurrentEffectPreventsGoAgain($cardID) || $player != $mainPlayer) break;
      GainActionPoints();
      WriteLog(CardLink($cardID, $cardID) . " gained go again");
      break;
    default:
      break;
  }
}
