<?php

//Every single function with the exception of the PlayCardAttempt() functions at the bottom of this file are entirely used to make sure the card/ability the AI wants to play is actually legal before it attempts anything.
//This might be a bit to dig through, but you don't really need to understand any of this to create priority arrays.
//If you do have questions though, just ping me, @Etasus, and I can help you out.

function CardIsBlockable($storedPriorityNode)
{
  global $combatChain, $combatChainState, $CCS_NumChainLinks, $currentPlayer, $turn;
  if($storedPriorityNode[3] == 0) return false;
  if($turn[0] == "B" && CardType($storedPriorityNode[0]) == "DR") return false;
  if($storedPriorityNode[1] == "Character")
  {
    $character = &GetPlayerCharacter($currentPlayer);
    if($character[$storedPriorityNode[2]+6] == 1 || $character[$storedPriorityNode[2]+1] != 2) return false;
    //WriteLog("character[i+6]->".$character[$storedPriorityNode[2]+6]);
  }
  switch($combatChain[0])
  {
    case "find_center_blue": return !(ComboActive() && CardCost($storedPriorityNode[0]) < $combatChainState[$CCS_NumChainLinks]);
    case "herons_flight_red": return false; //I have no idea how to make Heron's Flight work, so I'm just gonna say it's unblockable. This is so edge case that no one will know for a while lmfaooooo
    case "crane_dance_red":
    case "crane_dance_yellow":
    case "crane_dance_blue": return !(ComboActive() && AttackValue($storedPriorityNode[0]) > $combatChainState[$CCS_NumChainLinks]);
    default: return true;
  }
}

function CardIsPlayable($storedPriorityNode, $hand, $resources)
{
  if(CardIsPrevented($storedPriorityNode[0])) return false;
  switch($storedPriorityNode[1])
  {
    case "Hand":
      $index = $storedPriorityNode[2];
      $baseCost = CardCost($storedPriorityNode[0]);
      break;
    case "Arsenal":
      if(ArsenalIsFrozen($storedPriorityNode)) return false;
      $index = -1;
      $baseCost = CardCost($storedPriorityNode[0]);
      break;
    case "Character":
      if(CharacterIsUsed($storedPriorityNode)) return false;
      $index = -1;
      $baseCost = AbilityCost($storedPriorityNode[0]);
      break;
    case "Item":
      $index = -1;
      $baseCost = AbilityCost($storedPriorityNode[0]);
      break;
    case "Ally":
      $index = -1;
      $baseCost = AbilityCost($storedPriorityNode[0]);
      break;
    case "Banish":
      $index = $storedPriorityNode[2];
      $baseCost = CardCost($storedPriorityNode[0]);
      break;
    default:
      WriteLog("ERROR: AI is storedPriorityNode an uncheckable card for playability. Please log a bug report.");
      return false;
  }
  $finalCost = $baseCost + RogSelfCostMod($storedPriorityNode[0]) + RogCharacterCostMod($storedPriorityNode[0]) + RogAuraCostMod($storedPriorityNode[0]) + RogEffectCostMod($storedPriorityNode[0]);
  $totalPitch = $resources[0];
  for($i = 0; $i < count($hand); ++$i)
  {
    if($i != $index) $totalPitch = $totalPitch + PitchValue($hand[$i]);
  }
  return $finalCost <= $totalPitch;
}

function ReactionCardIsPlayable($storedPriorityNode, $hand, $resources)
{
  return CardIsPlayable($storedPriorityNode, $hand, $resources) && ReactionRequirementsMet($storedPriorityNode);
}

function CardIsPitchable($storedPriorityNode)
{
  global $currentTurnEffects, $currentPlayer;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "frost_lock_blue": return CardCost($storedPriorityNode[0]) != 0;
        default:
          break;
      }
    }
  }
  return true;
}

function CardIsArsenalable($storedPriorityNode)
{
  return true; //currently there are no cards in the game that prevent something from being arsenaled. When there is, this is an easy entry point to set it up.
}

function RogSelfCostMod($cardID)
{
  global $CS_NumCharged, $currentPlayer, $combatChain, $CS_LayerTarget;
  switch ($cardID) {
    case "arknight_ascendancy_red":
      return (-1 * NumRunechants($currentPlayer));
    case "ninth_blade_of_the_blood_oath_yellow":
      return (-1 * NumRunechants($currentPlayer));
    case "reduce_to_runechant_red":
    case "reduce_to_runechant_yellow":
    case "reduce_to_runechant_blue":
      return (-1 * NumRunechants($currentPlayer));
    case "amplify_the_arknight_red":
    case "amplify_the_arknight_yellow":
    case "amplify_the_arknight_blue":
      return (-1 * NumRunechants($currentPlayer));
    case "drawn_to_the_dark_dimension_red":
    case "drawn_to_the_dark_dimension_yellow":
    case "drawn_to_the_dark_dimension_blue":
      return (-1 * NumRunechants($currentPlayer));
    case "rune_flash_red":
    case "rune_flash_yellow":
    case "rune_flash_blue":
      return (-1 * NumRunechants($currentPlayer));
    case "bolting_blade_yellow":
      return (-1 * (2 * GetClassState($currentPlayer, $CS_NumCharged)));
    case "blinding_beam_red":
    case "blinding_beam_yellow":
    case "blinding_beam_blue":
      return TalentContains($combatChain[GetClassState($currentPlayer, $CS_LayerTarget)], "SHADOW") ? -1 : 0;
    case "jump_start_red":
    case "jump_start_yellow":
    case "jump_start_blue":
      $numHypers = 0;
      $numHypers += CountItem("hyper_driver_red", $currentPlayer);
      $numHypers += CountItem("hyper_driver_yellow", $currentPlayer);
      $numHypers += CountItem("hyper_driver_blue", $currentPlayer);
      return $numHypers > 0 ? -1 : 0;
    case "pummel_red": case "pummel_yellow": case "pummel_blue":
      if(GetPlayerCharacter($currentPlayer)[0] == "ROGUE030"){
        return -1;
      }
    default:
      return 0;
  }
}

function RogCharacterCostMod($cardID) //this currently serves no purpose except to give us an entry point for future effects as we make them, like Kassai.
{
  global $currentPlayer;
  $modifier = 0;
  return $modifier;
}

function RogAuraCostMod($cardID)
{
  global $currentPlayer;
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  $myAuras = &GetAuras($currentPlayer);
  $theirAuras = &GetAuras($otherPlayer);
  $modifier = 0;
  for ($i = count($myAuras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    switch ($myAuras[$i]) {
      case "frostbite":
        $modifier += 1;
        break;
      default:
        break;
    }
  }

  for ($i = count($theirAuras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
    switch ($theirAuras[$i]) {
      case "channel_lake_frigid_blue":
        $modifier += 1;
        break;
      default:
        break;
    }
  }
  return $modifier;
}

function RogEffectCostMod($cardID)
{
  global $currentTurnEffects, $currentPlayer, $CS_PlayUniqueID;
  $from = "-"; //I currently don't want to figure out how "from" works, so I'm just gonna do this and hope for the best. If something breaks, we'll fix it.
  $costModifier = 0;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "cartilage_crush_red":
        case "cartilage_crush_yellow":
        case "cartilage_crush_blue":
          if (IsAction($cardID)) $costModifier += 1;
          break;
        case "seismic_surge":
          if (ClassContains($cardID, "GUARDIAN", $currentPlayer) && CardType($cardID) == "AA") $costModifier -= 1;
          break;
        case "heartened_cross_strap":
          if (CardType($cardID) == "AA") $costModifier -= 2;
          break;
        case "courage_of_bladehold":
          if (TypeContains($cardID, "W", $currentPlayer) && CardSubType($cardID) == "Sword") $costModifier -= 1;
          break;
        case "dauntless_red-2":
        case "dauntless_yellow-2":
        case "dauntless_blue-2":
          if (CardType($cardID) == "DR") $costModifier += 1;
          break;
        case "bloodsheath_skeleta-AA":
          if (CardType($cardID) == "AA") $costModifier -= CountAura("runechant", $currentPlayer);
          break;
        case "bloodsheath_skeleta-NAA":
          if (CardType($cardID) == "A") $costModifier -= CountAura("runechant", $currentPlayer);
          break;
        case "hamstring_shot_red":
        case "hamstring_shot_yellow":
        case "hamstring_shot_blue":
          if (CardType($cardID) == "AA" || GetAbilityType($cardID, -1, $from) == "AA") $costModifier += 1;
          break;
        case "frost_lock_blue-1":
          $costModifier += 1;
          break;
        case "cold_wave_red":
        case "cold_wave_yellow":
        case "cold_wave_blue":
          $costModifier += 1;
          break;
        case "heart_of_ice":
          $costModifier += 1;
          break;
        case "amulet_of_ignition_yellow":
          if (IsStaticType(CardType($cardID), $from, $cardID)) $costModifier -= 1;
          break;
        case "blood_of_the_dracai_red":
          if (TalentContains($cardID, "DRACONIC", $currentPlayer) && $from != "PLAY" && $from != "EQUIP") $costModifier -= 1;
          break;
        case "rising_resentment_red":
        case "rising_resentment_yellow":
        case "rising_resentment_blue":
          if (GetClassState($currentPlayer, $CS_PlayUniqueID) == $currentTurnEffects[$i + 2]) --$costModifier;
          break;
        case "alluvion_constellas":
          if (IsStaticType(CardType($cardID), $from, $cardID) && DelimStringContains(CardSubType($cardID), "Staff")) $costModifier -= 3;
          break;
        case "ROGUE519":
          if (IsStaticType(CardType($cardID), $from, $cardID)) $costModifier -= 1;
          break;
        default:
          break;
      }
    }
  }
  return $costModifier;
}

function CardIsPrevented($cardID)
{
  global $currentTurnEffects, $currentPlayer;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "crush_the_weak_red":
        case "crush_the_weak_yellow":
        case "crush_the_weak_blue": return AttackValue($cardID) <= 3;
        case "frost_lock_blue": return CardCost($cardID) == 0;
        default:
          break;
      }
    }
  }
  return false;
}

function CharacterIsUsed($storedPriorityNode)
{
  global $currentPlayer;
  $character = &GetPlayerCharacter($currentPlayer);
  if($character[$storedPriorityNode[2]+5] < 1 || $character[$storedPriorityNode[2]+1] != 2) return true;
  else return false;
}

function ArsenalIsFrozen($storedPriorityNode)
{
  global $currentPlayer;
  $arsenal = &GetArsenal($currentPlayer);
  if($arsenal[$storedPriorityNode[2]+4] == 1) return true;
  else return false;
}

function ReactionRequirementsMet($storedPriorityNode)
{
  global $combatChain, $mainPlayer, $currentPlayer, $CS_NumNonAttackCards, $CS_AtksWWeapon, $CS_NumFusedIce, $CS_NumFusedEarth, $CS_NumFusedLightning;
  switch($storedPriorityNode[0])
  {
    case "breaking_scales": return HasCombo($combatChain[0]);
    case "ancestral_empowerment_red": return CardType($combatChain[0]) == "AA" && ClassContains($combatChain[0], "NINJA", $mainPlayer);
    case "glint_the_quicksilver_blue": case "rout_red": case "singing_steelblade_yellow":
    case "overpower_red": case "overpower_yellow": case "overpower_blue":
    case "ironsong_response_red": case "ironsong_response_yellow": case "ironsong_response_blue":
    case "biting_blade_red": case "biting_blade_yellow": case "biting_blade_blue":
    case "stroke_of_foresight_red": case "stroke_of_foresight_yellow": case "stroke_of_foresight_blue": return TypeContains($combatChain[0], "W", $mainPlayer);
    case "snapdragon_scalers": return CardType($combatChain[0]) == "AA" && CardCost($combatChain[0]) <= 1;
    case "pummel_red": case "pummel_yellow": case "pummel_blue": return CardSubtype($combatChain[0]) == "Club" || CardSubtype($combatChain[0]) == "Hammer" || (CardType($combatChain[0]) == "AA" && CardCost($combatChain[0]) >= 2);
    case "razor_reflex_red": case "razor_reflex_yellow": case "razor_reflex_blue": return CardSubtype($combatChain[0]) == "Sword" || CardSubtype($combatChain[0]) == "Dagger" || (CardType($combatChain[0]) == "AA" && CardCost($combatChain[0]) <= 1);
    case "twinning_blade_yellow": return CardSubtype($combatChain[0]) == "Sword";
    case "unified_decree_yellow":
    case "out_for_blood_red": case "out_for_blood_yellow": case "out_for_blood_blue": return TypeContains($combatChain[0], "W", $mainPlayer);
    case "lunging_press_blue": return CardType($combatChain[0]) == "AA";
    case "courageous_steelhand_red": case "courageous_steelhand_yellow": case "courageous_steelhand_blue": return true;
    case "sutcliffes_suede_hides": return CardType($combatChain[0]) == "AA" && GetClassState($currentPlayer, $CS_NumNonAttackCards) > 0;
    case "shatter_yellow": return TypeContains($combatChain[0], "W", $mainPlayer) && !Is1H($combatChain[0]);
    case "blade_runner_red": case "blade_runner_yellow": case "blade_runner_blue": return TypeContains($combatChain[0], "W", $mainPlayer) && Is1H($combatChain[0]);
    case "in_the_swing_red": case "in_the_swing_yellow": case "in_the_swing_blue": return GetClassState($currentPlayer, $CS_AtksWWeapon) >= 1;
    case "run_through_yellow":
    case "thrust_red":
    case "blade_flash_blue": return CardSubtype($combatChain[0]) == "Sword";
    case "combustion_point_red": return CardType($combatChain[0]) == "AA" && (ClassContains($combatChain[0], "NINJA", $mainPlayer) || TalentContains($combatChain[0], "DRACONIC", $mainPlayer));
    case "liquefy_red": return CardType($combatChain[0]) == "AA";
    case "tide_flippers": return CardType($combatChain[0]) == "AA" && AttackValue($combatChain[0]) <= 2;
    case "rapid_reflex_red": case "rapid_reflex_yellow": case "rapid_reflex_blue": return CardType($combatChain[0]) == "AA" && CardCost($combatChain[0]) == 0;
    case "puncture_red": case "puncture_yellow": case "puncture_blue": return CardSubtype($combatChain[0]) == "Sword" || CardSubtype($combatChain[0]) == "Dagger";
    case "blacktek_whisperers": case "mask_of_perdition": return ClassContains($combatChain[0], "ASSASSIN", $mainPlayer) && CardType($combatChain[0]) == "AA";
    case "cut_to_the_chase_red": case "cut_to_the_chase_yellow": case "cut_to_the_chase_blue": return ClassContains($combatChain[0], "ASSASSIN", $mainPlayer) && CardType($combatChain[0]) == "AA" && ContractType($combatChain[0]) != "";
    case "amulet_of_earth_blue": return GetClassState($currentPlayer, $CS_NumFusedEarth) > 0;
    case "amulet_of_ice_blue": return GetClassState($currentPlayer, $CS_NumFusedIce) > 0;
    case "lightning_press_red": case "lightning_press_yellow": case "lightning_press_blue": return CardType($combatChain[0]) == "AA" && CardCost($combatChain[0]) <= 1;
    case "amulet_of_lightning_blue": return GetClassState($currentPlayer, $CS_NumFusedLightning) > 0;
    case "blade_flurry_red": return TypeContains($combatChain[0], "W", $mainPlayer);
    case "hunts_end_red": return CardSubtype($combatChain[0]) == "Dagger";
    default: return true;
  }
}


//This is just a way for me to seperate the actual act of playing a card from the main AI function block. They basically just check where the card is being played from, and then make the relevant inputs.
function BlockCardAttempt($storedPriorityNode)
{
  global $currentPlayer;
  switch($storedPriorityNode[1])
  {
    case "Hand":
      ProcessInput($currentPlayer, 27, "", $storedPriorityNode[2], 0, "");
      CacheCombatResult();
      break;
    case "Character":
      ProcessInput($currentPlayer, 3, "", $storedPriorityNode[2], 0, "");
      CacheCombatResult();
      break;
    default: WriteLog("ERROR: AI attempting to block with an unblockable card. Please log a bug report."); break;
  }
}

function PlayCardAttempt($storedPriorityNode)
{
  global $currentPlayer;
  switch($storedPriorityNode[1])
  {
    case "Hand":
      ProcessInput($currentPlayer, 27, "", $storedPriorityNode[2], 0, "");
      CacheCombatResult();
      break;
    case "Arsenal":
      ProcessInput($currentPlayer, 5, "", $storedPriorityNode[2], 0, "");
      CacheCombatResult();
      break;
    case "Character":
      ProcessInput($currentPlayer, 3, "", $storedPriorityNode[2], 0, "");
      CacheCombatResult();
      break;
    case "Item":
      ProcessInput($currentPlayer, 10, "", $storedPriorityNode[2], 0, "");
      CacheCombatResult();
      break;
    case "Ally":
      ProcessInput($currentPlayer, 24, "", $storedPriorityNode[2], 0, "");
      CacheCombatResult();
      break;
    case "Banish":
      ProcessInput($currentPlayer, 14, "", $storedPriorityNode[2], 0, "");
      CacheCombatResult();
      break;
    default: WriteLog("ERROR: AI attempting to play an unplayable card. Please log a bug report."); PassInput(); break;
  }
}

function PitchCardAttempt($storedPriorityNode)
{
  global $currentPlayer;
  switch($storedPriorityNode[1])
  {
    case "Hand":
      ProcessInput($currentPlayer, 27, "", $storedPriorityNode[2], 0, "");
      CacheCombatResult();
      break;
    default: WriteLog("ERROR: AI attempting to pitch an unpitchable card. Please log a bug report."); break;
  }
}

function ArsenalCardAttempt($storedPriorityNode)
{
  global $currentPlayer;
  switch($storedPriorityNode[1])
  {
    case "Hand":
      ProcessInput($currentPlayer, 4, "", $storedPriorityNode[0], 0, "");
      CacheCombatResult();
      break;
    default: WriteLog("ERROR: AI attempting to arsenal an unarsenalable card. Please log a bug report."); break;
  }
}

function FixHand($currentPlayer)
{
  $hand = &GetHand($currentPlayer);
  $fix = [];
  for($i = 0; $i < count($hand); ++$i)
  {
    if($hand[$i] != "") array_push($fix, $hand[$i]);
  }
  $hand = $fix;
}
?>
