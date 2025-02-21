<?php

function ARCMechanologistPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
{
  global $currentPlayer, $CS_NumBoosted, $actionPoints, $CS_PlayIndex;
  global $CombatChain, $CS_LastDynCost;
  $rv = "";
  switch($cardID) {
    case "teklo_plasma_pistol":
      $abilityType = GetResolvedAbilityType($cardID);
      if($abilityType == "A")
      {
        $index = GetClassState($currentPlayer, $CS_PlayIndex);
        $character = new Character($currentPlayer, $index);
        $character->numCounters = 1;
        $character->Finished();
      }
      return "";
    case "teklo_foundry_heart":
      $deck = new Deck($currentPlayer);
      for($i = 0; $i < 2 && !$deck->Empty(); ++$i) {
        $banished = $deck->BanishTop();
        if(ClassContains($banished, "MECHANOLOGIST", $currentPlayer)) GainResources($currentPlayer, 1);
      }
      return "";
    case "achilles_accelerator":
      GainActionPoints(1, $currentPlayer);
      return "";
    case "high_octane_red":
      Draw($currentPlayer);
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "spark_of_genius_yellow":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDECK:subtype=Item;class=MECHANOLOGIST;minCost=" . (GetClassState($currentPlayer, $CS_LastDynCost) / 2) . ";maxCost=" . (GetClassState($currentPlayer, $CS_LastDynCost) / 2));
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      AddDecisionQueue("PUTPLAY", $currentPlayer, 0, 1);
      AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
      if(GetClassState($currentPlayer, $CS_NumBoosted) > 0) AddDecisionQueue("DRAW", $currentPlayer, "-");
      return "";
    case "induction_chamber_red":
      if($from == "PLAY") {
        $items = &GetItems($currentPlayer);
        if($CombatChain->HasCurrentLink()) GiveAttackGoAgain();
        else $items[GetClassState($currentPlayer, $CS_PlayIndex)+1] = 1;
      }
      return $rv;
    case "pour_the_mold_red": case "pour_the_mold_yellow": case "pour_the_mold_blue":
      if($cardID == "pour_the_mold_red") $maxCost = 2;
      else if($cardID == "pour_the_mold_yellow") $maxCost = 1;
      else $maxCost = 0;
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYHAND:subtype=Item;maxCost=$maxCost;class=MECHANOLOGIST");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      AddDecisionQueue("PUTPLAY", $currentPlayer, (GetClassState($currentPlayer, $CS_NumBoosted) > 0 ? 1 : 0), 1);
      return "";
    case "aether_sink_yellow":
      $index = GetClassState($currentPlayer, $CS_PlayIndex);
      $items = &GetItems($currentPlayer);
      if($index != -1) {
        $items[$index+1] = ($items[$index + 1] == 0 ? 1 : 0);
        if($items[$index+1] == 0) {
          AddCurrentTurnEffect($cardID, $currentPlayer);
          $items[$index+2] = 2;
        }
      }
      return $rv;
    case "cognition_nodes_blue":
      if($from == "PLAY") {
        $items = &GetItems($currentPlayer);
        if(!$CombatChain->HasCurrentLink()) $items[GetClassState($currentPlayer, $CS_PlayIndex) + 1] = 1;
      }
      return $rv;
    case "convection_amplifier_red":
      $index = GetClassState($currentPlayer, $CS_PlayIndex);
      $items = &GetItems($currentPlayer);
      if($index != -1) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
        --$items[$index+1];
        if($items[$index+1] <= 0) DestroyItemForPlayer($currentPlayer, $index);
        $rv = "Gives your next attack this turn Dominate";
      }
      return $rv;
    case "locked_and_loaded_red": case "locked_and_loaded_yellow": case "locked_and_loaded_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      $boosted = GetClassState($currentPlayer, $CS_NumBoosted) > 0;
      if($boosted) Opt($cardID, 1);
      return "";
    case "dissipation_shield_yellow":
      AddCurrentTurnEffect($cardID . "-" . $additionalCosts, $currentPlayer, "PLAY");
      $rv = "";
      return $rv;
    case "optekal_monocle_blue":
      if($from == "PLAY") {
        $index = GetClassState($currentPlayer, $CS_PlayIndex);
        $items = &GetItems($currentPlayer);
        if($index != -1) {
          PlayerOpt($currentPlayer, 1);
          --$items[$index+1];
          if($items[$index+1] <= 0) DestroyItemForPlayer($currentPlayer, $index);
        }
      }
      return $rv;
    default: return "";
  }
}

function ARCMechanologistHitEffect($cardID, $from)
{
  global $mainPlayer, $combatChainState, $CCS_GoesWhereAfterLinkResolves;
  switch ($cardID) {
    case "pedal_to_the_metal_red": case "pedal_to_the_metal_yellow": case "pedal_to_the_metal_blue":
      AddCurrentTurnEffectFromCombat($cardID, $mainPlayer);
      break;
    case "cognition_nodes_blue":
      if(substr($from, 0, 5) != "THEIR") $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "BOTDECK";
      else $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "THEIRBOTDECK";
      break;
    case "over_loop_red": case "over_loop_yellow": case "over_loop_blue":
      if(substr($from, 0, 5) != "THEIR") $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "BOTDECK";
      else $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "THEIRBOTDECK";
      break;
    default: break;
  }
  return "";
}

function HasBoost($cardID, $player)
{
  if(SearchCurrentTurnEffects("evo_speedslip_blue", $player) && TypeContains($cardID, "AA", $player)) return true;
  switch ($cardID) {
    case "pedal_to_the_metal_red": case "pedal_to_the_metal_yellow": case "pedal_to_the_metal_blue":
    case "over_loop_red": case "over_loop_yellow": case "over_loop_blue":
    case "throttle_red": case "throttle_yellow": case "throttle_blue":
    case "zero_to_sixty_red": case "zero_to_sixty_yellow": case "zero_to_sixty_blue":
    case "zipper_hit_red": case "zipper_hit_yellow": case "zipper_hit_blue":
    case "high_speed_impact_red": case "high_speed_impact_yellow": case "high_speed_impact_blue":
    case "combustible_courier_red": case "combustible_courier_yellow": case "combustible_courier_blue":
    case "tbone_red": case "tbone_yellow": case "tbone_blue":
    case "zoom_in_red": case "zoom_in_yellow": case "zoom_in_blue":
    case "pulsewave_harpoon_red":
    case "scramble_pulse_red": case "scramble_pulse_yellow": case "scramble_pulse_blue":
		case "crankshaft_red": case "crankshaft_yellow": case "crankshaft_blue":
		case "jump_start_red": case "jump_start_yellow": case "jump_start_blue":
    case "under_loop_red":
    case "heist_red": case "steel_street_hoons_blue": case "twin_drive_red":
    case "bull_bar_red": case "bull_bar_yellow": case "bull_bar_blue":
    case "spring_a_leak_red": case "spring_a_leak_yellow": case "spring_a_leak_blue":
    case "zero_to_fifty_red": case "zero_to_fifty_yellow": case "zero_to_fifty_blue":
    case "razzle_dazzle_red": case "razzle_dazzle_yellow": case "razzle_dazzle_blue":
    case "full_tilt_red": case "full_tilt_yellow": case "full_tilt_blue":
    case "gas_guzzler_red": case "gas_guzzler_yellow": case "gas_guzzler_blue":
    case "big_bertha_red": case "big_bertha_yellow": case "big_bertha_blue":
    case "rev_up_red": case "rev_up_yellow": case "rev_up_blue":
    case "data_link_red": case "data_link_yellow": case "data_link_blue":
    case "dive_through_data_red": case "dive_through_data_yellow": case "dive_through_data_blue":
    case "sprocket_rocket_red": case "sprocket_rocket_yellow": case "sprocket_rocket_blue":
    case "dumpster_dive_red": case "dumpster_dive_yellow": case "dumpster_dive_blue":
    case "expedite_red": case "expedite_yellow": case "expedite_blue":
    case "metex_red": case "metex_yellow": case "metex_blue":
    case "out_pace_red": case "out_pace_yellow": case "out_pace_blue":
    case "lay_waste_red": case "lay_waste_yellow": case "lay_waste_blue":
    case "fender_bender_red": case "fender_bender_yellow": case "fender_bender_blue":
    case "panel_beater_red": case "panel_beater_yellow": case "panel_beater_blue":
    case "under_loop_red": case "under_loop_yellow": case "under_loop_blue":
    case "fast_and_furious_red":
      return true;
    default: return false;
  }
}

function Boost($cardID)
{
  global $currentPlayer;
  if(SearchCurrentTurnEffects("evo_speedslip_blue", $currentPlayer, true) && HasBoost($cardID, $currentPlayer)) {
      $amountBoostChoices = "0,1,2";
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many times you want to activate boost on " . CardLink($cardID, $cardID));
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, $amountBoostChoices);
      AddDecisionQueue("OP", $currentPlayer, "BOOST-".$cardID, 1);
  } else {
    AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_boost");
    AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
    AddDecisionQueue("OP", $currentPlayer, "BOOST-".$cardID, 1);
  }
}

function DoBoost($player, $cardID, $boostCount=1)
{
  global $combatChainState, $CS_NumBoosted, $CCS_NumBoosted, $CCS_IsBoosted;
  $deck = new Deck($player);
  $isGoAgainGranted = false;
  for ($i = 0; $i < $boostCount; $i++) {
    if($deck->Empty()) { WriteLog("Could not boost"); return; }
    ItemBoostEffects();
    GainActionPoints(CountCurrentTurnEffects("high_octane_red", $player), $player);
    GainResources($player, CountCurrentTurnEffects("heavy_industry_power_plant", $player));
    $boostedCardID = $deck->Top(remove:true);
    SelfBoostEffects($player, $boostedCardID, $cardID);
    CharacterBoostAbilities($player);
    OnBoostedEffects($player, $boostedCardID);
    $skipBanish = false;
    if (CardNameContains($boostedCardID, "Hyper Driver", $player)) {
      $skipBanish = EquipmentBoostEffect($player, "hyperx3", $boostedCardID);
    }
    if(CardSubType($boostedCardID) == "Item" && SearchCurrentTurnEffects("bios_update_red-2", $player, true)) {
      $skipBanish = true;
      PutItemIntoPlayForPlayer($boostedCardID, $player);
    }
    if(SearchCurrentTurnEffects("viziertronic_model_i", $player)) {
      AddLayer("TRIGGER", $player, "viziertronic_model_i");
    }
    if (!$skipBanish) BanishCardForPlayer($boostedCardID, $player, "DECK", "BOOST");
    $grantsGA = ClassContains($boostedCardID, "MECHANOLOGIST", $player);
    WriteLog("Boost banished " . CardLink($boostedCardID, $boostedCardID) . " and " . ($grantsGA ? "gets" : "doesn't get") . " go again.");
    IncrementClassState($player, $CS_NumBoosted);
    ++$combatChainState[$CCS_NumBoosted];
    $combatChainState[$CCS_IsBoosted] = 1;
    if($grantsGA) {
      GiveAttackGoAgain();
      $isGoAgainGranted = true;
    }
  }
  return $isGoAgainGranted;
}

function OnBoostedEffects($player, $boosted)
{
  switch($boosted)
  {
    case "big_bertha_red": case "big_bertha_yellow": case "big_bertha_blue":
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose a Hyper Driver to get a steam counter", 1);
      AddDecisionQueue("MULTIZONEINDICES", $player, "MYITEMS:isSameName=hyper_driver_red");
      AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
      AddDecisionQueue("MZADDCOUNTER", $player, "-", 1);
      break;
    case "fast_and_furious_red":
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose an item with cranked to get a steam counter", 1);
      AddDecisionQueue("MULTIZONEINDICES", $player, "MYITEMS:hasCrank=true");
      AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
      AddDecisionQueue("MZADDCOUNTER", $player, "-", 1);
      break;
    default: break;
  }
}

function SelfBoostEffects($player, $boosted, $cardID)
{
  switch($cardID) {
    case "sprocket_rocket_red": case "sprocket_rocket_yellow": case "sprocket_rocket_blue":
    case "dumpster_dive_red": case "dumpster_dive_yellow": case "dumpster_dive_blue":
      if(SubtypeContains($boosted, "Item", $player) || IsEquipment($boosted, $player)) AddCurrentTurnEffect($cardID, $player);
      break;
    default: break;
  }
}

function ItemBoostEffects()
{
  global $currentPlayer;
  $items = &GetItems($currentPlayer);
  for($i = count($items) - ItemPieces(); $i >= 0; $i -= ItemPieces()) {
    switch($items[$i]) {
      case "hyper_driver_red":
      case "hyper_driver_red": case "hyper_driver_yellow": case "hyper_driver_blue": case "hyper_driver":
        if($items[$i+2] == 2) {
          AddLayer("TRIGGER", $currentPlayer, $items[$i], $i, "-", $items[$i + 4]);
          $items[$i+2] = 1;
        }
        break;
      case "teklo_pounder_blue":
        if($items[$i+2] == 2) {
          WriteLog(CardLink($items[$i], $items[$i]) . " gives the attack +2");
          --$items[$i+1];
          $items[$i+2] = 1;
          AddCurrentTurnEffect("teklo_pounder_blue", $currentPlayer, "PLAY");
          if($items[$i+1] <= 0) DestroyItemForPlayer($currentPlayer, $i);
        }
        break;
      case "hadron_collider_red": case "hadron_collider_yellow": case "hadron_collider_blue":
        AddCurrentTurnEffect($items[$i] . "," . $items[$i+1], $currentPlayer, "PLAY");
        DestroyItemForPlayer($currentPlayer, $i);
        break;
      default: break;
    }
  }
}

function EquipmentBoostEffect($player, $charID, $cardID) {
  if (!FindCharacterIndex($player, "hyperx3")) return false;
  if (SearchCharacterForCard($player, $charID)) {
    $chars = &GetPlayerCharacter($player);
    $index = FindCharacterIndex($player, $charID);
    AddSubcardToChar($chars, $index, $cardID);
    OnBoostCardPutUnderCharacter($chars, $index, $charID, $player);
    return true;
  }
  return false;
}

function OnBoostCardPutUnderCharacter(&$chars, $index, $charID, $player) {
  switch ($charID) {
    case "hyperx3":
      if ($chars[$index+1] != 1 && $chars[$index+2] >= 3) {
        Draw($player, true, true);
        $chars[$index+1] = 1;
      }
      break;
    default:
      break;
  }
}

function AddSubcardToChar(&$chars, $index, $cardID) {
  if (isSubcardEmpty($chars, $index)) $chars[$index+10] = $cardID;
  else $chars[$index+10] = $chars[$index+10] . "," . $cardID;
  $chars[$index+2]++;
}

function isSubcardEmpty($chars, $index)
{
  return $chars[$index+10] == '-';
}
