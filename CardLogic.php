<?php

include "CardDictionary.php";
include "CoreLogic.php";

function PummelHit($player = -1, $passable = false, $fromDQ = false)
{
  global $defPlayer;
  if ($player == -1) $player = $defPlayer;
  if ($fromDQ) {
    PrependDecisionQueue("DISCARDCARD", $player, "HAND", 1);
    PrependDecisionQueue("MULTIREMOVEHAND", $player, "-", 1);
    if ($passable) PrependDecisionQueue("MAYCHOOSEHAND", $player, "<-", 1);
    else PrependDecisionQueue("CHOOSEHAND", $player, "<-", 1);
    PrependDecisionQueue("SETDQCONTEXT", $player, "Choose a card to discard", 1);
    PrependDecisionQueue("FINDINDICES", $player, "HAND", ($passable ? 1 : 0));
  } else {
    AddDecisionQueue("FINDINDICES", $player, "HAND", ($passable ? 1 : 0));
    AddDecisionQueue("SETDQCONTEXT", $player, "Choose a card to discard", 1);
    if ($passable) AddDecisionQueue("MAYCHOOSEHAND", $player, "<-", 1);
    else AddDecisionQueue("CHOOSEHAND", $player, "<-", 1);
    AddDecisionQueue("MULTIREMOVEHAND", $player, "-", 1);
    AddDecisionQueue("DISCARDCARD", $player, "HAND", 1);
  }
}

function HandToTopDeck($player)
{
  AddDecisionQueue("FINDINDICES", $player, "HAND");
  AddDecisionQueue("MAYCHOOSEHAND", $player, "<-", 1);
  AddDecisionQueue("MULTIREMOVEHAND", $player, "-", 1);
  AddDecisionQueue("MULTIADDTOPDECK", $player, "-", 1);
}

function BottomDeck($player = "", $mayAbility = false, $shouldDraw = false)
{
  global $currentPlayer;
  if ($player == "") $player = $currentPlayer;
  AddDecisionQueue("FINDINDICES", $player, "HAND");
  AddDecisionQueue("SETDQCONTEXT", $player, "Put_a_card_from_your_hand_on_the_bottom_of_your_deck.");
  if ($mayAbility) AddDecisionQueue("MAYCHOOSEHAND", $player, "<-", 1);
  else AddDecisionQueue("CHOOSEHAND", $player, "<-", 1);
  AddDecisionQueue("REMOVEMYHAND", $player, "-", 1);
  AddDecisionQueue("ADDBOTDECK", $player, "-", 1);
  if ($shouldDraw) AddDecisionQueue("DRAW", $player, "-", 1);
}

function BottomDeckMultizone($player, $zone1, $zone2, $isMandatory = false, $context = "Choose a card to sink (or Pass)")
{
  AddDecisionQueue("MULTIZONEINDICES", $player, $zone1 . "&" . $zone2, 1);
  AddDecisionQueue("SETDQCONTEXT", $player, $context, 1);
  AddDecisionQueue($isMandatory ? "CHOOSEMULTIZONE" : "MAYCHOOSEMULTIZONE", $player, "<-", 1);
  AddDecisionQueue("MZREMOVE", $player, "-", 1);
  AddDecisionQueue("ADDBOTDECK", $player, "-", 1);
}

function AddCurrentTurnEffectNextAttack($cardID, $player, $from = "", $uniqueID = -1)
{
  global $combatChain, $layers;
  if (count($layers) > 0 && (CardType($layers[0]) == "AA" || CardType($layers[0]) == "W" || SubtypeContains($layers[0], "Ally"))) {
    AddCurrentTurnEffectFromCombat($cardID, $player, $uniqueID);
  } else if (count($combatChain) > 0) AddCurrentTurnEffectFromCombat($cardID, $player, $uniqueID);
  else AddCurrentTurnEffect($cardID, $player, $from, $uniqueID);
}

function AddCurrentTurnEffect($cardID, $player, $from = "", $uniqueID = -1)
{
  global $currentTurnEffects, $combatChain;
  $card = explode("-", $cardID)[0];
  if (CardType($card) == "A" && !CanPlayAsInstant($cardID, -1, $from) && count($combatChain) > 0 && IsCombatEffectActive($cardID) && !IsCombatEffectPersistent($cardID) && $from != "PLAY") {
    AddCurrentTurnEffectFromCombat($cardID, $player, $uniqueID);
    return;
  }
  array_push($currentTurnEffects, $cardID);
  array_push($currentTurnEffects, $player);
  array_push($currentTurnEffects, $uniqueID);
  array_push($currentTurnEffects, CurrentTurnEffectUses($cardID));
}

function AddAfterResolveEffect($cardID, $player, $from = "", $uniqueID = -1)
{
  global $afterResolveEffects, $combatChain;
  $card = explode("-", $cardID)[0];
  if (CardType($card) == "A" && count($combatChain) > 0 && !IsCombatEffectPersistent($cardID) && $from != "PLAY") {
    AddCurrentTurnEffectFromCombat($cardID, $player, $uniqueID);
    return;
  }
  array_push($afterResolveEffects, $cardID);
  array_push($afterResolveEffects, $player);
  array_push($afterResolveEffects, $uniqueID);
  array_push($afterResolveEffects, CurrentTurnEffectUses($cardID));
}

function CopyCurrentTurnEffectsFromAfterResolveEffects()
{
  global $currentTurnEffects, $afterResolveEffects;
  for ($i = 0; $i < count($afterResolveEffects); $i += CurrentTurnEffectPieces()) {
    array_push($currentTurnEffects, $afterResolveEffects[$i]);
    array_push($currentTurnEffects, $afterResolveEffects[$i + 1]);
    array_push($currentTurnEffects, $afterResolveEffects[$i + 2]);
    array_push($currentTurnEffects, $afterResolveEffects[$i + 3]);
  }
  $afterResolveEffects = [];
}

//This is needed because if you add a current turn effect from combat, it could get deleted as part of the combat resolution
function AddCurrentTurnEffectFromCombat($cardID, $player, $uniqueID = -1)
{
  global $currentTurnEffectsFromCombat;
  array_push($currentTurnEffectsFromCombat, $cardID);
  array_push($currentTurnEffectsFromCombat, $player);
  array_push($currentTurnEffectsFromCombat, $uniqueID);
  array_push($currentTurnEffectsFromCombat, CurrentTurnEffectUses($cardID));
}

function CopyCurrentTurnEffectsFromCombat()
{
  global $currentTurnEffects, $currentTurnEffectsFromCombat;
  for ($i = 0; $i < count($currentTurnEffectsFromCombat); $i += CurrentTurnEffectPieces()) {
    array_push($currentTurnEffects, $currentTurnEffectsFromCombat[$i]);
    array_push($currentTurnEffects, $currentTurnEffectsFromCombat[$i + 1]);
    array_push($currentTurnEffects, $currentTurnEffectsFromCombat[$i + 2]);
    array_push($currentTurnEffects, $currentTurnEffectsFromCombat[$i + 3]);
  }
  $currentTurnEffectsFromCombat = [];
}

function RemoveCurrentTurnEffect($index)
{
  global $currentTurnEffects;
  unset($currentTurnEffects[$index + 3]);
  unset($currentTurnEffects[$index + 2]);
  unset($currentTurnEffects[$index + 1]);
  unset($currentTurnEffects[$index]);
  $currentTurnEffects = array_values($currentTurnEffects);
}

function CurrentTurnEffectPieces()
{
  return 4;
}

function CurrentTurnEffectUses($cardID)
{
  // TODO see what breaks this
  $effectID = ExtractCardID($cardID);
  switch ($effectID) {
    case "steadfast_red":
      return 6;
    case "steadfast_yellow":
      return 5;
    case "steadfast_blue":
      return 4;
    case "blood_of_the_dracai_red":
      return 3;
    case "uprising_red":
      return 4;
    case "oasis_respite_red":
      return 4;
    case "oasis_respite_yellow":
      return 3;
    case "oasis_respite_blue":
      return 2;
    case "art_of_the_dragon_blood_red":
      return 3;
    case "shelter_from_the_storm_red":
    case "calming_breeze_red":
      return 3;
    default:
      return 1;
  }
}

function AddNextTurnEffect($cardID, $player, $uniqueID = -1, $numTurns = 1)
{
  global $nextTurnEffects;
  array_push($nextTurnEffects, $cardID);
  array_push($nextTurnEffects, $player);
  array_push($nextTurnEffects, $uniqueID);
  array_push($nextTurnEffects, CurrentTurnEffectUses($cardID));
  array_push($nextTurnEffects, $numTurns);
}

function IsCombatEffectLimited($index)
{
  global $currentTurnEffects, $combatChain, $mainPlayer, $combatChainState, $CCS_WeaponIndex, $CCS_AttackUniqueID;
  if (count($combatChain) == 0 || $currentTurnEffects[$index + 2] == -1) return false;
  $attackSubType = CardSubType($combatChain[0]);
  if (DelimStringContains($attackSubType, "Ally")) {
    $allies = &GetAllies($mainPlayer);
    if (count($allies) < $combatChainState[$CCS_WeaponIndex] + 5) return false;
    if ($allies[$combatChainState[$CCS_WeaponIndex] + 5] != $currentTurnEffects[$index + 2]) return true;
  } else {
    return $combatChainState[$CCS_AttackUniqueID] != $currentTurnEffects[$index + 2];
  }
  return false;
}

function PrependLayer($cardID, $player, $parameter, $target = "-", $additionalCosts = "-", $uniqueID = "-")
{
  global $layers;
  array_push($layers, $cardID);
  array_push($layers, $player);
  array_push($layers, $parameter);
  array_push($layers, $target);
  array_push($layers, $additionalCosts);
  array_push($layers, $uniqueID);
  array_push($layers, GetUniqueId($cardID, $player));
  return count($layers);//How far it is from the end
}

function PopLayer()
{
  global $layers;
  for ($i=0; $i < LayerPieces(); $i++) { 
    array_pop($layers);
  }
  return count($layers);//How far it is from the end
}

function AddLayer($cardID, $player, $parameter, $target = "-", $additionalCosts = "-", $uniqueID = "-")
{
  global $layers, $dqState;
  //Layers are on a stack, so you need to push things on in reverse order
  array_unshift($layers, GetUniqueId($cardID, $player));
  array_unshift($layers, $uniqueID);
  array_unshift($layers, $additionalCosts);
  array_unshift($layers, $target);
  array_unshift($layers, $parameter);
  array_unshift($layers, $player);
  array_unshift($layers, $cardID);
  if ($cardID == "TRIGGER") {
    $orderableIndex = isset($dqState[8]) ? intval($dqState[8]) : -1;
    if ($orderableIndex == -1) $dqState[8] = 0;
    else $dqState[8] += LayerPieces();
  } else $dqState[8] = -1;//If it's not a trigger, it's not orderable
  return count($layers);//How far it is from the end
}

function AddDecisionQueue($phase, $player, $parameter, $subsequent = 0, $makeCheckpoint = 0)
{
  global $decisionQueue;
  if (count($decisionQueue) == 0) $insertIndex = 0;
  else {
    $insertIndex = count($decisionQueue) - DecisionQueuePieces();
    if (!IsGamePhase($decisionQueue[$insertIndex])) //Stack must be clear before you can continue with the step
    {
      $insertIndex = count($decisionQueue);
    }
  }

  $parameter = str_replace(" ", "_", $parameter);
  array_splice($decisionQueue, $insertIndex, 0, $phase);
  array_splice($decisionQueue, $insertIndex + 1, 0, $player);
  array_splice($decisionQueue, $insertIndex + 2, 0, $parameter);
  array_splice($decisionQueue, $insertIndex + 3, 0, $subsequent);
  array_splice($decisionQueue, $insertIndex + 4, 0, $makeCheckpoint);
}

function PrependDecisionQueue($phase, $player, $parameter, $subsequent = 0, $makeCheckpoint = 0)
{
  global $decisionQueue;
  $parameter = str_replace(" ", "_", $parameter);
  array_unshift($decisionQueue, $makeCheckpoint);
  array_unshift($decisionQueue, $subsequent);
  array_unshift($decisionQueue, $parameter);
  array_unshift($decisionQueue, $player);
  array_unshift($decisionQueue, $phase);
}

function IsDecisionQueueActive()
{
  global $dqState;
  return $dqState[0] == "1";
}

function ProcessDecisionQueue()
{
  global $turn, $decisionQueue, $dqState;
  if ($dqState[0] != "1") {
    if (count($turn) < 3) $turn[2] = "-";
    $dqState[0] = "1"; //If the decision queue is currently active/processing
    $dqState[1] = $turn[0];
    $dqState[2] = $turn[1];
    $dqState[3] = $turn[2];
    $dqState[4] = "-"; //DQ helptext initial value
    $dqState[5] = "-"; //Decision queue multizone indices
    $dqState[6] = "0"; //Damage dealt
    $dqState[7] = "0"; //Target
  }
  ContinueDecisionQueue("");
}

function CloseDecisionQueue($skip=false)
{
  global $turn, $decisionQueue, $dqState, $combatChain, $currentPlayer, $mainPlayer;
  $dqState[0] = "0";
  $turn[0] = $dqState[1];
  $turn[1] = $dqState[2];
  $turn[2] = $dqState[3];
  $dqState[4] = "-"; //Clear the context, just in case
  $dqState[5] = "-"; //Clear Decision queue multizone indices
  $dqState[6] = "0"; //Damage dealt
  $dqState[7] = "0"; //Target
  if(!$skip) $dqState[8] = "-1"; //Orderable index (what layer after which triggers can be reordered)
  $decisionQueue = [];
  if (($turn[0] == "D" || $turn[0] == "A") && count($combatChain) == 0) {
    $currentPlayer = $mainPlayer;
    $turn[0] = "M";
  }
}

function ShouldHoldPriorityNow($player)
{
  global $layerPriority, $layers;
  if ($layerPriority[$player - 1] != "1") return false;
  if ($layers[count($layers) - LayerPieces()] == "ENDPHASE") return false;
  $currentLayer = $layers[count($layers) - LayerPieces()];
  $layerType = CardType($currentLayer);
  if (HoldPrioritySetting($player) == 3 && $layerType != "AA" && $layerType != "W") return false;
  return true;
}

function IsGamePhase($phase)
{
  switch ($phase) {
    case "RESUMEPAYING":
    case "RESUMEPLAY":
    case "RESOLVECHAINLINK":
    case "RESOLVECOMBATDAMAGE":
    case "PASSTURN":
      return true;
    default:
      return false;
  }
}

//Must be called with the my/their context
function ContinueDecisionQueue($lastResult = "")
{
  global $decisionQueue, $turn, $currentPlayer, $makeCheckpoint, $otherPlayer;
  global $layers, $layerPriority, $dqVars, $dqState, $CS_AbilityIndex, $CS_AdditionalCosts, $mainPlayer, $CS_LayerPlayIndex;
  global $CS_ResolvingLayerUniqueID, $makeBlockBackup, $defPlayer;
  if (count($decisionQueue) == 0 || IsGamePhase($decisionQueue[0])) {
    if (count($decisionQueue) > 0 && $currentPlayer != $decisionQueue[1]) {
    }
    if (count($decisionQueue) == 0 && count($layers) > 0) {
      $priorityHeld = 0;
      if ($mainPlayer == 1) {
        if (ShouldHoldPriorityNow(1)) {
          AddDecisionQueue("INSTANT", 1, "-");
          $priorityHeld = 1;
          $layerPriority[0] = 0;
        }
        if (ShouldHoldPriorityNow(2)) {
          AddDecisionQueue("INSTANT", 2, "-");
          $priorityHeld = 1;
          $layerPriority[1] = 0;
        }
      } else {
        if (ShouldHoldPriorityNow(2)) {
          AddDecisionQueue("INSTANT", 2, "-");
          $priorityHeld = 1;
          $layerPriority[1] = 0;
        }
        if (ShouldHoldPriorityNow(1)) {
          AddDecisionQueue("INSTANT", 1, "-");
          $priorityHeld = 1;
          $layerPriority[0] = 0;
        }
      }
      if ($priorityHeld) {
        ContinueDecisionQueue("");
      } else {
        if (RequiresDieRoll($layers[0], explode("|", $layers[2])[0], $layers[1])) {
          RollDie($layers[1]);
          ContinueDecisionQueue("");
          return;
        }
        CloseDecisionQueue();
        $cardID = array_shift($layers);
        $player = array_shift($layers);
        $parameter = array_shift($layers);
        $target = array_shift($layers);
        $additionalCosts = array_shift($layers);
        $uniqueID = array_shift($layers);
        $layerUniqueID = array_shift($layers);
        SetClassState($player, $CS_ResolvingLayerUniqueID, $layerUniqueID);
        $params = explode("|", $parameter);
        if ($currentPlayer != $player) {
          $currentPlayer = $player;
          $otherPlayer = $currentPlayer == 1 ? 2 : 1;
          BuildMyGamestate($currentPlayer);
        }
        $layerPriority[0] = ShouldHoldPriority(1);
        $layerPriority[1] = ShouldHoldPriority(2);
        if ($cardID == "ENDTURN") EndStep();
        else if ($cardID == "ENDPHASE") FinishTurnPass();
        else if ($cardID == "RESUMETURN") $turn[0] = "M";
        else if ($cardID == "LAYER") ProcessLayer($player, $parameter);
        else if ($cardID == "FINALIZECHAINLINK") FinalizeChainLink($parameter);
        else if ($cardID == "ATTACKSTEP") {
          $turn[0] = "B";
          $currentPlayer = $defPlayer;
          $makeBlockBackup = 1;
        } else if ($cardID == "DEFENDSTEP") {
          $turn[0] = "A";
          $currentPlayer = $mainPlayer;
          BeginningReactionStepEffects();
          ProcessDecisionQueue();
        } else if ($cardID == "TRIGGER") {
          ProcessTrigger($player, $parameter, $uniqueID, $target, $additionalCosts, $params[0]);
          ProcessDecisionQueue();
        } else if ($cardID == "MELD") {
          ProcessMeld($player, $parameter, $cardID);
          ProcessDecisionQueue();
        } else {
          SetClassState($player, $CS_AbilityIndex, isset($params[2]) ? $params[2] : "-"); //This is like a parameter to PlayCardEffect and other functions
          PlayCardEffect($cardID, $params[0], isset($params[1]) ? $params[1] : 0, $target, $additionalCosts, isset($params[3]) ? $params[3] : "-1", isset($params[2]) ? $params[2] : -1);
          ClearDieRoll($player);
        }
      }
    } else if (count($decisionQueue) > 0 && $decisionQueue[0] == "RESUMEPLAY") {
      if ($currentPlayer != $decisionQueue[1]) {
        $currentPlayer = $decisionQueue[1];
        $otherPlayer = $currentPlayer == 1 ? 2 : 1;
        BuildMyGamestate($currentPlayer);
      }
      $params = explode("|", $decisionQueue[2]);
      CloseDecisionQueue(true);
      if ($params[2] == "") $params[2] = 0;
      if ($turn[0] == "B" && count($layers) == 0) { //If a layer is not created
        PlayCardEffect($params[0], $params[1], $params[2], "-", $params[3], $params[4]);
      } else {
        //params 3 = ability index
        //params 4 = Unique ID
        $additionalCosts = GetClassState($currentPlayer, $CS_AdditionalCosts);
        if ($additionalCosts == "") $additionalCosts = "-";
        $layerIndex = count($layers) - GetClassState($currentPlayer, $CS_LayerPlayIndex);
        $layers[$layerIndex + 2] = $params[1] . "|" . $params[2] . "|" . $params[3] . "|" . $params[4];
        $layers[$layerIndex + 4] = $additionalCosts;
        ProcessDecisionQueue();
        return;
      }
    } else if (count($decisionQueue) > 0 && $decisionQueue[0] == "RESUMEPAYING") {
      $player = $decisionQueue[1];
      $params = explode("-", $decisionQueue[2]); //Parameter
      if ($lastResult == "") $lastResult = 0;
      CloseDecisionQueue();
      if ($currentPlayer != $player) {
        $currentPlayer = $player;
        $otherPlayer = $currentPlayer == 1 ? 2 : 1;
        BuildMyGamestate($currentPlayer);
      }
      PlayCard($params[0], $params[1], $lastResult, $params[2], isset($params[4]) ? $params[4] : -1);
    } else if (count($decisionQueue) > 0 && $decisionQueue[0] == "RESOLVECHAINLINK") {
      CloseDecisionQueue();
      ResolveChainLink();
    } else if (count($decisionQueue) > 0 && $decisionQueue[0] == "RESOLVECOMBATDAMAGE") {
      $parameter = $decisionQueue[2];
      if ($parameter != "-") $damageDone = $parameter;
      else $damageDone = $dqState[6];
      CloseDecisionQueue();
      if(!IsGameOver()) ResolveCombatDamage($damageDone);
    } else if (count($decisionQueue) > 0 && $decisionQueue[0] == "PASSTURN") {
      CloseDecisionQueue();
      PassTurn();
    } else {
      CloseDecisionQueue();
      FinalizeAction();
    }
    return;
  }
  $phase = array_shift($decisionQueue); 
  $player = array_shift($decisionQueue);
  $parameter = array_shift($decisionQueue);
  //WriteLog($phase . " " . $player . " " . $parameter . " " . $lastResult);//Uncomment this to visualize decision queue execution
  if (count($dqVars) > 0) {
    if (str_contains($parameter, "{0}")) $parameter = str_replace("{0}", $dqVars[0], $parameter);
    if (str_contains($parameter, "<0>")) $parameter = str_replace("<0>", CardLink($dqVars[0], $dqVars[0]), $parameter);
    if (count($dqVars) > 1 && str_contains($parameter, "{1}")) $parameter = str_replace("{1}", $dqVars[1], $parameter);
  }
  if (count($dqVars) > 1) $parameter = str_replace("<1>", CardLink($dqVars[1], $dqVars[1]), $parameter);
  $subsequent = array_shift($decisionQueue);
  $makeCheckpoint = array_shift($decisionQueue);
  $turn[0] = $phase;
  $turn[1] = $player;
  $currentPlayer = $player;
  $turn[2] = ($parameter == "<-" ? $lastResult : $parameter);
  $return = "PASS";
  if ($subsequent != 1 || is_array($lastResult) || strval($lastResult) != "PASS") $return = DecisionQueueStaticEffect($phase, $player, ($parameter == "<-" ? $lastResult : $parameter), $lastResult);
  if ($parameter == "<-" && !is_array($lastResult) && $lastResult == "-1") $return = "PASS"; //Collapse the rest of the queue if this decision point has invalid parameters
  if (is_array($return) || strval($return) != "NOTSTATIC") {
    if ($phase != "SETDQCONTEXT") $dqState[4] = "-"; //Clear out context for static states -- context only persists for one choice
    ContinueDecisionQueue($return);
  } else {
  }
}

function ProcessLayer($player, $parameter)
{
  switch ($parameter) {
    case "PHANTASM":
      PhantasmLayer();
      break;
    case "MIRAGE":
      MirageLayer();
      break;
    default:
      break;
  }
}

function AddOnHitTrigger($cardID, $uniqueID = -1, $source="-"): void
{
  global $mainPlayer, $combatChain;
  $defPlayer = $mainPlayer == 1 ? 0 : 1;
  switch ($cardID) {
    case "mugenshi_release_yellow":
    case "hurricane_technique_yellow":
    case "pounding_gale_red":
    case "whelming_gustwave_red":
    case "whelming_gustwave_yellow":
    case "whelming_gustwave_blue":
    case "dawnblade":
    case "snatch_red":
    case "snatch_yellow":
    case "snatch_blue":
    case "pedal_to_the_metal_red":
    case "pedal_to_the_metal_yellow":
    case "pedal_to_the_metal_blue":
    case "cognition_nodes_blue":
    case "over_loop_red":
    case "over_loop_yellow":
    case "over_loop_blue":
    case "red_in_the_ledger_red":
    case "endless_arrow_red":
    case "hamstring_shot_red":
    case "hamstring_shot_yellow":
    case "hamstring_shot_blue":
    case "salvage_shot_red":
    case "salvage_shot_yellow":
    case "salvage_shot_blue":
    case "searing_shot_red":
    case "searing_shot_yellow":
    case "searing_shot_blue":
    case "nebula_blade":
    case "arknight_ascendancy_red":
    case "command_and_conquer_red":
    case "life_for_a_life_red":
    case "life_for_a_life_yellow":
    case "life_for_a_life_blue":
    case "pursuit_of_knowledge_blue":
    case "cadaverous_contraband_red":
    case "cadaverous_contraband_yellow":
    case "cadaverous_contraband_blue":
    case "fervent_forerunner_red":
    case "fervent_forerunner_yellow":
    case "fervent_forerunner_blue":
    case "moon_wish_red":
    case "moon_wish_yellow":
    case "moon_wish_blue":
    case "rifting_red":
    case "rifting_yellow":
    case "rifting_blue":
    case "rushing_river_red":
    case "rushing_river_yellow":
    case "rushing_river_blue":
    case "soulbead_strike_red":
    case "soulbead_strike_yellow":
    case "soulbead_strike_blue":
    case "torrent_of_tempo_red":
    case "torrent_of_tempo_yellow":
    case "torrent_of_tempo_blue":
    case "bittering_thorns_yellow":
    case "whirling_mist_blossom_yellow":
    case "high_speed_impact_red":
    case "high_speed_impact_yellow":
    case "high_speed_impact_blue":
    case "combustible_courier_red":
    case "combustible_courier_yellow":
    case "combustible_courier_blue":
    case "remorseless_red":
    case "pathing_helix_red":
    case "pathing_helix_yellow":
    case "pathing_helix_blue":
    case "sleep_dart_red":
    case "sleep_dart_yellow":
    case "sleep_dart_blue":
    case "dread_triptych_blue":
    case "consuming_volition_red":
    case "consuming_volition_yellow":
    case "consuming_volition_blue":
    case "meat_and_greet_red":
    case "meat_and_greet_yellow":
    case "meat_and_greet_blue":
    case "coax_a_commotion_red":
    case "promise_of_plenty_red":
    case "promise_of_plenty_yellow":
    case "promise_of_plenty_blue":
    case "herald_of_erudition_yellow":
    case "herald_of_judgment_yellow":
    case "herald_of_triumph_red":
    case "herald_of_triumph_yellow":
    case "herald_of_triumph_blue":
    case "herald_of_protection_red":
    case "herald_of_protection_yellow":
    case "herald_of_protection_blue":
    case "herald_of_ravages_red":
    case "herald_of_ravages_yellow":
    case "herald_of_ravages_blue":
    case "herald_of_rebirth_red":
    case "herald_of_rebirth_yellow":
    case "herald_of_rebirth_blue":
    case "herald_of_tenacity_red":
    case "herald_of_tenacity_yellow":
    case "herald_of_tenacity_blue":
    case "wartune_herald_red":
    case "wartune_herald_yellow":
    case "wartune_herald_blue":
    case "galaxxi_black":
    case "bolt_of_courage_red":
    case "bolt_of_courage_yellow":
    case "bolt_of_courage_blue":
    case "engulfing_light_red":
    case "engulfing_light_yellow":
    case "engulfing_light_blue":
    case "nourishing_emptiness_red":
    case "brandish_red":
    case "brandish_yellow":
    case "brandish_blue":
    case "overload_red":
    case "overload_yellow":
    case "overload_blue":
    case "illuminate_red":
    case "illuminate_yellow":
    case "illuminate_blue":
    case "rising_solartide_red":
    case "rising_solartide_yellow":
    case "rising_solartide_blue":
    case "soul_harvest_blue":
    case "lunartide_plunderer_red":
    case "lunartide_plunderer_yellow":
    case "lunartide_plunderer_blue":
    case "oldhim_grandfather_of_eternity":
    case "oldhim":
    case "endless_winter_red":
    case "entangle_red":
    case "entangle_yellow":
    case "entangle_blue":
    case "thump_red":
    case "thump_yellow":
    case "thump_blue":
    case "awakening_blue":
    case "tear_asunder_blue":
    case "embolden_red":
    case "embolden_yellow":
    case "embolden_blue":
    case "light_it_up_yellow":
    case "boltn_shot_red":
    case "boltn_shot_yellow":
    case "boltn_shot_blue":
    case "frost_fang_red":
    case "frost_fang_yellow":
    case "frost_fang_blue":
    case "icy_encounter_red":
    case "icy_encounter_yellow":
    case "icy_encounter_blue":
    case "pulverize_red":
    case "break_tide_yellow":
    case "spring_tidings_yellow":
    case "winds_of_eternity_blue":
    case "ride_the_tailwind_red":
    case "ride_the_tailwind_yellow":
    case "ride_the_tailwind_blue":
    case "battering_bolt_red":
    case "fatigue_shot_red":
    case "fatigue_shot_yellow":
    case "fatigue_shot_blue":
    case "timidity_point_red":
    case "timidity_point_yellow":
    case "timidity_point_blue":
    case "runic_reclamation_red":
    case "swarming_gloomveil_red":
    case "drowning_dire_red":
    case "drowning_dire_yellow":
    case "drowning_dire_blue":
    case "reek_of_corruption_red":
    case "reek_of_corruption_yellow":
    case "reek_of_corruption_blue":
    case "fractal_replication_red":
    case "bingo_red":
    case "dustup_red":
    case "dustup_yellow":
    case "dustup_blue":
    case "kyloria":
    case "nekria":
    case "vynserakai":
    case "phoenix_form_red":
    case "engulfing_flamewave_red":
    case "engulfing_flamewave_yellow":
    case "engulfing_flamewave_blue":
    case "mounting_anger_red":
    case "mounting_anger_yellow":
    case "mounting_anger_blue":
    case "rising_resentment_red":
    case "rising_resentment_yellow":
    case "rising_resentment_blue":
    case "soaring_strike_red":
    case "soaring_strike_yellow":
    case "soaring_strike_blue":
    case "take_the_tempo_red":
    case "liquefy_red":
    case "breaking_point_red":
    case "stoke_the_flames_red":
    case "erase_face_red":
    case "vipox_red":
    case "tiger_swipe_red":
    case "flex_claws_red":
    case "flex_claws_yellow":
    case "flex_claws_blue":
    case "jubeel_spellbane":
    case "urgent_delivery_red":
    case "urgent_delivery_yellow":
    case "urgent_delivery_blue":
    case "spiders_bite":
    case "spiders_bite":
    case "eradicate_yellow":
    case "regicide_blue":
    case "leave_no_witnesses_red":
    case "surgical_extraction_blue":
    case "plunder_the_poor_red":
    case "plunder_the_poor_yellow":
    case "plunder_the_poor_blue":
    case "rob_the_rich_red":
    case "rob_the_rich_yellow":
    case "rob_the_rich_blue":
    case "annihilate_the_armed_red":
    case "annihilate_the_armed_yellow":
    case "annihilate_the_armed_blue":
    case "fleece_the_frail_red":
    case "fleece_the_frail_yellow":
    case "fleece_the_frail_blue":
    case "nix_the_nimble_red":
    case "nix_the_nimble_yellow":
    case "nix_the_nimble_blue":
    case "sack_the_shifty_red":
    case "sack_the_shifty_yellow":
    case "sack_the_shifty_blue":
    case "slay_the_scholars_red":
    case "slay_the_scholars_yellow":
    case "slay_the_scholars_blue":
    case "heat_seeker_red":
    case "immobilizing_shot_red":
    case "drill_shot_red":
    case "drill_shot_yellow":
    case "drill_shot_blue":
    case "hemorrhage_bore_red":
    case "hemorrhage_bore_yellow":
    case "hemorrhage_bore_blue":
    case "nerve_scalpel":
    case "nerve_scalpel":
    case "orbitoclast":
    case "orbitoclast":
    case "scale_peeler":
    case "scale_peeler":
    case "infiltrate_red":
    case "shake_down_red":
    case "infect_red":
    case "infect_yellow":
    case "infect_blue":
    case "sedate_red":
    case "sedate_yellow":
    case "sedate_blue":
    case "wither_red":
    case "wither_yellow":
    case "wither_blue":
    case "dishonor_blue":
    case "wander_with_purpose_yellow":
    case "recoil_red":
    case "recoil_yellow":
    case "recoil_blue":
    case "spinning_wheel_kick_red":
    case "spinning_wheel_kick_yellow":
    case "spinning_wheel_kick_blue":
    case "be_like_water_red":
    case "be_like_water_yellow":
    case "be_like_water_blue":
    case "deadly_duo_red":
    case "deadly_duo_yellow":
    case "deadly_duo_blue":
    case "onetwo_punch_red":
    case "onetwo_punch_yellow":
    case "onetwo_punch_blue":
    case "barbed_undertow_red":
    case "infecting_shot_red":
    case "infecting_shot_yellow":
    case "infecting_shot_blue":
    case "sedation_shot_red":
    case "sedation_shot_yellow":
    case "sedation_shot_blue":
    case "withering_shot_red":
    case "withering_shot_yellow":
    case "withering_shot_blue":
    case "stab_wound_blue":
    case "plunge_red":
    case "plunge_yellow":
    case "plunge_blue":
    case "death_touch_red":
    case "death_touch_yellow":
    case "death_touch_blue":
    case "amnesia_red":
    case "humble_red":
    case "humble_yellow":
    case "humble_blue":
    case "wreck_havoc_red":
    case "wreck_havoc_yellow":
    case "wreck_havoc_blue":
    case "cut_down_to_size_red":
    case "cut_down_to_size_yellow":
    case "cut_down_to_size_blue":
    case "destructive_deliberation_red":
    case "destructive_deliberation_yellow":
    case "destructive_deliberation_blue":
    case "lay_to_rest_red":
    case "lay_to_rest_yellow":
    case "lay_to_rest_blue":
    case "flail_of_agony":
    case "hungering_demigon_red":
    case "hungering_demigon_yellow":
    case "hungering_demigon_blue":
    case "nasreth_the_soul_harrower":
    case "censor_red":
    case "mischievous_meeps_red":
    case "mauling_qi_red":
    case "under_loop_red":
    case "jinglewood_smash_hit":
    case "bittering_thorns_red":
    case "banksy":
    case "annihilator_engine_red":
    case "terminator_tank_red":
    case "war_machine_red":
    case "heist_red":
    case "spring_a_leak_red":
    case "spring_a_leak_yellow":
    case "spring_a_leak_blue":
    case "data_link_red":
    case "data_link_yellow":
    case "data_link_blue":
    case "dive_through_data_red":
    case "dive_through_data_yellow":
    case "dive_through_data_blue":
    case "expedite_red":
    case "expedite_yellow":
    case "expedite_blue":
    case "metex_red":
    case "metex_yellow":
    case "metex_blue":
    case "under_loop_red":
    case "under_loop_yellow":
    case "under_loop_blue":
    case "already_dead_red":
    case "intoxicating_shot_blue":
    case "send_packing_yellow":
    case "millers_grindstone":
    case "command_respect_red":
    case "command_respect_yellow":
    case "command_respect_blue":
    case "concuss_red":
    case "concuss_yellow":
    case "concuss_blue":
    case "pay_up_red":
    case "down_but_not_out_red":
    case "down_but_not_out_yellow":
    case "down_but_not_out_blue":
    case "performance_bonus_red":
    case "performance_bonus_yellow":
    case "performance_bonus_blue":
    case "judge_jury_executioner_red":
    case "strength_rules_all_red":
    case "beckoning_mistblade":
    case "biting_breeze_red":
    case "biting_breeze_yellow":
    case "biting_breeze_blue":
    case "murky_water_red":
    case "earth_form_red":
    case "earth_form_yellow":
    case "earth_form_blue":
    case "lightning_form_red":
    case "lightning_form_yellow":
    case "lightning_form_blue":
    case "splintering_deadwood_red":
    case "splintering_deadwood_yellow":
    case "splintering_deadwood_blue":
    case "summit_the_unforgiving":
    case "devotion_never_dies_red":
      AddLayer("TRIGGER", $mainPlayer, $cardID, $cardID, "ONHITEFFECT");
      break;
    case "hunters_klaive":
    case "mark_of_the_huntsman":
      if (IsHeroAttackTarget()) AddLayer("TRIGGER", $mainPlayer, $cardID, $cardID, "ONHITEFFECT", $uniqueID);
      break;
    case "find_center_blue":
      if (ComboActive($cardID)) AddLayer("TRIGGER", $mainPlayer, $cardID, $cardID, "ONHITEFFECT");
      break;
    case "winters_wail":
      if (SearchCurrentTurnEffects($cardID, $mainPlayer)) AddLayer("TRIGGER", $mainPlayer, $cardID, $cardID, "ONHITEFFECT");
      break;
    case "blacktek_whisperers":
    case "mask_of_perdition":
      if(IsHeroAttackTarget() && ClassContains($combatChain[0], "ASSASSIN", $mainPlayer)) {
        AddLayer("TRIGGER", $mainPlayer, $cardID, $cardID, "ONHITEFFECT");
      }
      break;
    case "bonds_of_agony_blue":
      if (NumAttackReactionsPlayed() > 2 && IsHeroAttackTarget()) AddLayer("TRIGGER", $mainPlayer, $cardID, $cardID, "ONHITEFFECT");
      break;
    case "persuasive_prognosis_blue":
    case "art_of_desire_body_red":
    case "art_of_desire_soul_yellow":
    case "art_of_desire_mind_blue":
    case "bonds_of_attraction_red":
    case "bonds_of_attraction_yellow":
    case "bonds_of_attraction_blue":
    case "double_trouble_red":
    case "double_trouble_yellow":
    case "double_trouble_blue":
    case "bonds_of_memory_red":
    case "bonds_of_memory_yellow":
    case "bonds_of_memory_blue":
    case "desires_of_flesh_red":
    case "desires_of_flesh_yellow":
    case "desires_of_flesh_blue":
    case "impulsive_desire_red":
    case "impulsive_desire_yellow":
    case "impulsive_desire_blue":
    case "minds_desire_red":
    case "minds_desire_yellow":
    case "minds_desire_blue":
    case "rowdy_locals_blue":
    case "the_weakest_link_red":
    case "blanch_red":
    case "blanch_yellow":
    case "blanch_blue":
    case "factfinding_mission_red":
    case "factfinding_mission_yellow":
    case "factfinding_mission_blue":
    case "static_shock_red":
    case "static_shock_yellow":
    case "snuff_out_red":
    case "cut_through_the_facade_red":
    case "hand_behind_the_pen_red":
    case "smash_up_red":
    case "tongue_tied_red":
    case "splatter_skull_red":
    case "kiss_of_death_red":
    case "mark_the_prey_red":
    case "mark_the_prey_yellow":
    case "mark_the_prey_blue":
    case "tag_the_target_red":
    case "tag_the_target_yellow":
    case "tag_the_target_blue":
    case "trap_and_release_red":
    case "trap_and_release_yellow":
    case "trap_and_release_blue":
    case "pain_in_the_backside_red":
    case "pursue_to_the_edge_of_oblivion_red":
    case "pursue_to_the_pits_of_despair_red":
      if (IsHeroAttackTarget()) AddLayer("TRIGGER", $mainPlayer, $cardID, $cardID, "ONHITEFFECT");
      break;
    case "stone_rain_red":
      if (IsHeroAttackTarget() && HasAimCounter()) AddLayer("TRIGGER", $mainPlayer, $cardID, $cardID, "ONHITEFFECT");
      break;
    case "mark_of_the_black_widow_red":
    case "mark_of_the_black_widow_yellow":
    case "mark_of_the_black_widow_blue":
    case "mark_of_the_funnel_web_red":
    case "mark_of_the_funnel_web_yellow":
    case "mark_of_the_funnel_web_blue":
      if (IsHeroAttackTarget() && CheckMarked($defPlayer)) AddLayer("TRIGGER", $mainPlayer, $cardID, $cardID, "ONHITEFFECT");
      break;
    case "burning_blade_dance_red":
    case "hot_on_their_heels_red":
    case "mark_with_magma_red":
      if (IsHeroAttackTarget() && NumDraconicChainLinks() > 1) AddLayer("TRIGGER", $mainPlayer, $cardID, $cardID, "ONHITEFFECT");
      break;
    case "art_of_the_dragon_claw_red":
    case "art_of_the_dragon_scale_red":
      if (IsHeroAttackTarget() && SearchCurrentTurnEffects($cardID, $mainPlayer)) AddLayer("TRIGGER", $mainPlayer, $cardID, $cardID, "ONHITEFFECT");
      break;
    default:
      break;
  }
}

function AddCrushEffectTrigger($cardID)
{
  global $mainPlayer;
  switch ($cardID) {
    case "crippling_crush_red":
    case "spinal_crush_red":
    case "cranial_crush_blue":
    case "disable_red":
    case "disable_yellow":
    case "disable_blue":
    case "buckling_blow_red":
    case "buckling_blow_yellow":
    case "buckling_blow_blue":
    case "cartilage_crush_red":
    case "cartilage_crush_yellow":
    case "cartilage_crush_blue":
    case "crush_confidence_red":
    case "crush_confidence_yellow":
    case "crush_confidence_blue":
    case "debilitate_red":
    case "debilitate_yellow":
    case "debilitate_blue":
    case "mangle_red":
    case "righteous_cleansing_yellow":
    case "crush_the_weak_red":
    case "crush_the_weak_yellow":
    case "crush_the_weak_blue":
    case "chokeslam_red":
    case "chokeslam_yellow":
    case "chokeslam_blue":
    case "star_struck_yellow":
    case "boulder_drop_yellow":
    case "boulder_drop_blue":
      AddLayer("TRIGGER", $mainPlayer, $cardID, $cardID, "CRUSHEFFECT");
      break;
    default:
      break;
  }
}

function AddTowerEffectTrigger($cardID)
{
  global $mainPlayer;
  switch ($cardID) {
    case "colossal_bearing_red":
    case "colossal_bearing_red":
    case "smack_of_reality_red":
    case "smack_of_reality_red":
      AddLayer("TRIGGER", $mainPlayer, $cardID, $cardID, "TOWEREFFECT");
      break;
    default:
      break;
  }
}

function AddCardEffectHitTrigger($cardID, $sourceID = "-") // Effects that do not gives it's effect to the attack so still triggers when Stamp Confidance is in the arena
{
  global $mainPlayer, $defPlayer, $CombatChain, $combatChain;
  $source = $sourceID != "-" ? $sourceID : $CombatChain->AttackCard()->ID();
  if (SearchCurrentTurnEffects("dense_blue_mist_blue-HITPREVENTION", $defPlayer)) return false;
  $effects = explode(',', $cardID);
  $parameter = explode("-", $effects[0])[0];
  switch ($effects[0]) {
    case "plunder_run_red-1":
    case "plunder_run_yellow-1":
    case "plunder_run_blue-1":
    case "spoils_of_war_red-2":
    case "eclipse_existence_blue":
    case "ice_quake_red-HIT":
    case "ice_quake_yellow-HIT":
    case "ice_quake_blue-HIT":
    case "chill_to_the_bone_red":
    case "chill_to_the_bone_yellow":
    case "chill_to_the_bone_blue":
    case "shock_charmers":
    case "electrify_red":
    case "electrify_yellow":
    case "electrify_blue":
    case "outland_skirmish_red-1":
    case "outland_skirmish_yellow-1":
    case "outland_skirmish_blue-1":
    case "smashing_good_time_red-1":
    case "smashing_good_time_yellow-1":
    case "smashing_good_time_blue-1":
    case "glistening_steelblade_yellow-1":
    case "mask_of_shifting_perspectives":
    case "premeditate_red_1":
    case "target_totalizer":
    case "hack_to_reality_yellow-HIT":
      AddLayer("TRIGGER", $mainPlayer, $parameter, $cardID, "EFFECTHITEFFECT", $source);
      break;
    case "arakni_black_widow-HIT":
      // trigger cases: 1. stealth AA hit, 2. active chain chelicera hit, 3. flicked kiss
      if (TypeContains($sourceID, "AA", $mainPlayer) || (IsHeroAttackTarget() && $sourceID == "-")) {
        AddLayer("TRIGGER", $mainPlayer, $parameter, $cardID, "EFFECTHITEFFECT", $source);
      }
      break;
    case "arakni_funnel_web-HIT":
      // trigger cases: 1. stealth AA hit, 2. active chain chelicera hit, 3. flicked kiss
      if (TypeContains($sourceID, "AA", $mainPlayer) || (IsHeroAttackTarget() && $sourceID == "-")) {
        AddLayer("TRIGGER", $mainPlayer, $parameter, $cardID, "EFFECTHITEFFECT", $source);
      }
      break;
    case "burn_up__shock_red":
      if (IsHeroAttackTarget()) {
        AddLayer("TRIGGER", $mainPlayer, $parameter, $cardID, "EFFECTHITEFFECT", $source);
      }
      break;
    case "force_of_nature_blue-HIT":
      AddLayer("TRIGGER", $mainPlayer, "force_of_nature_blue", "force_of_nature_blue-TRIGGER", "EFFECTHITEFFECT", $source);
      break;
    case "succumb_to_temptation_yellow":
      if (CardType($CombatChain->AttackCard()->ID()) == "AA" && ClassContains($CombatChain->AttackCard()->ID(), "RUNEBLADE", $mainPlayer) && IsHeroAttackTarget()) {
        AddLayer("TRIGGER", $mainPlayer, $parameter, $cardID, "EFFECTHITEFFECT", $source);
      }
      break;
    case "savor_bloodshed_red-HIT":
      if(IsHeroAttackTarget() && CheckMarked($defPlayer)) {
        AddLayer("TRIGGER", $mainPlayer, $parameter, $cardID, "EFFECTHITEFFECT", $source);
      }
      break;
    case "poisoned_blade_red":
    case "poisoned_blade_yellow":
    case "poisoned_blade_blue":
      if(IsHeroAttackTarget() && (SubtypeContains($CombatChain->AttackCard()->ID(), "Dagger") || SubtypeContains($sourceID, "Dagger"))) {
        AddLayer("TRIGGER", $mainPlayer, $parameter, $cardID, "EFFECTHITEFFECT", $source);
      }
    default:
      break;
  }
}

function AddEffectHitTrigger($cardID, $source="-"): void // Effects that gives effect to the attack (keywords "attack gains/gets")
{
  global $mainPlayer, $Card_LifeBanner, $Card_ResourceBanner, $layers, $defPlayer;
  $effects = explode(',', $cardID);
  $parameter = explode("-", $effects[0])[0];
  switch ($effects[0]) {
    case "warriors_valor_red":
    case "warriors_valor_yellow":
    case "warriors_valor_blue":
    case "natures_path_pilgrimage_red":
    case "natures_path_pilgrimage_yellow":
    case "natures_path_pilgrimage_blue":
    case "pummel_red":
    case "pummel_yellow":
    case "pummel_blue":
    case "razor_reflex_red":
    case "razor_reflex_yellow":
    case "razor_reflex_blue":
    case "poison_the_tips_yellow":
    case "mauvrion_skies_red":
    case "mauvrion_skies_yellow":
    case "mauvrion_skies_blue":
    case "lumina_ascension_yellow":
    case "seek_enlightenment_red":
    case "seek_enlightenment_yellow":
    case "seek_enlightenment_blue":
    case "dusk_path_pilgrimage_red":
    case "dusk_path_pilgrimage_yellow":
    case "dusk_path_pilgrimage_blue":
    case "shadow_puppetry_red":
    case "warmongers_recital_red":
    case "warmongers_recital_yellow":
    case "warmongers_recital_blue":
    case "oaken_old_red":
    case "mulch_red":
    case "mulch_yellow":
    case "mulch_blue":
    case "snow_under_red":
    case "snow_under_yellow":
    case "snow_under_blue":
    case "frost_lock_blue-2":
    case "ice_storm_red-2":
    case "buzz_bolt_red":
    case "buzz_bolt_yellow":
    case "buzz_bolt_blue":
    case "flashfreeze_red-BUFF":
    case "shock_striker_red":
    case "shock_striker_yellow":
    case "shock_striker_blue":
    case "tear_asunder_blue":
    case "seek_and_destroy_red":
    case "twin_twisters_red-1":
    case "twin_twisters_yellow-1":
    case "twin_twisters_blue-1":
    case "life_of_the_party_red-1":
    case "life_of_the_party_yellow-1":
    case "life_of_the_party_blue-1":
    case "high_striker_red":
    case "high_striker_yellow":
    case "high_striker_blue":
    case "buckle_blue":
    case "cleave_red":
    case "dead_eye_yellow":
    case "runic_reaping_red-HIT":
    case "runic_reaping_yellow-HIT":
    case "runic_reaping_blue-HIT":
    case "spike_with_bloodrot_red":
    case "spike_with_frailty_red":
    case "spike_with_inertia_red":
    case "melting_point_red":
    case "lace_with_bloodrot_red":
    case "lace_with_frailty_red":
    case "lace_with_inertia_red":
    case "concealed_blade_blue":
    case "toxic_tips":
    case "toxicity_red":
    case "toxicity_yellow":
    case "toxicity_blue":
    case "beckoning_light_red":
    case "spirit_of_war_red":
    case "light_the_way_red":
    case "light_the_way_yellow":
    case "light_the_way_blue":
    case "lumina_lance_yellow-2":
    case "lumina_lance_yellow-3":
    case "ironsong_versus":
    case $Card_LifeBanner:
    case $Card_ResourceBanner:
    case "smash_and_grab_red":
    case "evo_command_center_yellow_equip":
    case "kassai_of_the_golden_sand":
    case "kassai":
    case "talk_a_big_game_blue":
    case "hood_of_red_sand":
      AddLayer("TRIGGER", $mainPlayer, $parameter, $cardID, "EFFECTHITEFFECT", $source);
      break;
    case "just_a_nick_red-HIT":
    case "maul_yellow-HIT":
      AddLayer("TRIGGER", $mainPlayer, $parameter, $cardID, "EFFECTHITEFFECT");
      break;
    case "two_sides_to_the_blade_red-ATTACK":
    case "long_whisker_loyalty_red-MARK":
    case "twist_and_turn_red":
    case "twist_and_turn_yellow":
    case "twist_and_turn_blue":
    case "hunt_a_killer_red":
    case "hunt_a_killer_yellow":
    case "hunt_a_killer_blue":
    case "sworn_vengeance_red":
    case "sworn_vengeance_yellow":
    case "sworn_vengeance_blue":
      AddLayer("TRIGGER", $mainPlayer, $parameter, $cardID, "EFFECTHITEFFECT");
      break;
    case "searing_gaze_red":
    case "stabbing_pain_red":
      if (IsHeroAttackTarget() && NumDraconicChainLinks() > 1) AddLayer("TRIGGER", $mainPlayer, $parameter, $cardID, "EFFECTHITEFFECT");
      break;
    case "scar_tissue_red":
    case "scar_tissue_yellow":
    case "scar_tissue_blue":
    case "imperial_seal_of_command_red-HIT":
      if (IsHeroAttackTarget()) AddLayer("TRIGGER", $mainPlayer, $parameter, $cardID, "EFFECTHITEFFECT");
      break;
    case "take_a_stab_red":
    case "take_a_stab_yellow":
    case "take_a_stab_blue":
      if (IsHeroAttackTarget() && CheckMarked($defPlayer)) AddLayer("TRIGGER", $mainPlayer, $parameter, $cardID, "EFFECTHITEFFECT");
      break;
    default:
      break;
  }
}

function AddCharacterPlayCardTrigger($cardID, $playType, $from)
{
  global $mainPlayer;
  $otherPlayer = $mainPlayer == 1 ? 2 : 1;
  $mainChar = GetPlayerCharacter($mainPlayer);
  for ($i = 0; $i < count($mainChar); $i += CharacterPieces()) {
    switch ($mainChar[$i]) {
      default:
        break;
    }
  }
  $otherChar = GetPlayerCharacter($otherPlayer);
  for ($i = 0; $i < count($otherChar); $i += CharacterPieces()) {
    switch ($otherChar[$i]) {
      case "leap_frog_vocal_sac":
      case "leap_frog_slime_skin":
      case "leap_frog_gloves":
      case "leap_frog_leggings":
        if ($playType == "AR" && SearchCharacterActive($otherPlayer, $otherChar[$i], checkGem: true)) {
          AddLayer("TRIGGER", $otherPlayer, $otherChar[$i]);
        }
        break;
      default:
        break;
    }
  }
}

function ProcessMainCharacterHitEffect($cardID, $player, $target)
{
  global $combatChain, $mainPlayer, $layers, $defPlayer;
  $character = &GetPlayerCharacter($player);
  if (CardType($target) == "AA" && SearchCurrentTurnEffects("tarpit_trap_yellow", $mainPlayer, count($layers) <= LayerPieces())) {
    WriteLog("Hit effect prevented by " . CardLink("tarpit_trap_yellow", "tarpit_trap_yellow"));
    return true;
  }
  switch ($cardID) {
    case "katsu_the_wanderer":
    case "katsu":
      KatsuHit();
      break;
    case "refraction_bolters":
      $index = FindCharacterIndex($player, $cardID);
      AddDecisionQueue("YESNO", $player, "if_you_want_to_destroy_".Cardlink($cardID, $cardID)."_to_get_Go_Again");
      AddDecisionQueue("NOPASS", $player, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $player, "MYCHAR-$index", 1);
      AddDecisionQueue("MZDESTROY", $player, "-", 1);
      AddDecisionQueue("OP", $player, "GIVEATTACKGOAGAIN", 1);
      AddDecisionQueue("WRITELOG", $player, Cardlink($cardID, $cardID)." was destroyed", 1);
      break;
    case "mask_of_momentum":
      Draw($player);
      break;
    case "vest_of_the_first_fist":
      $index = FindCharacterIndex($player, $cardID);
      AddDecisionQueue("YESNO", $player, "if_you_want_to_destroy_".Cardlink($cardID, $cardID)."_to_gain_2_resources");
      AddDecisionQueue("NOPASS", $player, "");
      AddDecisionQueue("PASSPARAMETER", $player, "MYCHAR-" . $index, 1);
      AddDecisionQueue("MZDESTROY", $player, "-", 1);
      AddDecisionQueue("GAINRESOURCES", $player, 2, 1);
      break;
    case "breeze_rider_boots":
      $index = FindCharacterIndex($player, $cardID);
      AddDecisionQueue("YESNO", $player, "if_you_want_to_destroy_".Cardlink($cardID, $cardID));
      AddDecisionQueue("NOPASS", $player, "-");
      AddDecisionQueue("PASSPARAMETER", $player, $index, 1);
      AddDecisionQueue("DESTROYCHARACTER", $player, "-", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $player, $character[$index], 1);
      break;
    case "briar_warden_of_thorns":
    case "briar":
      PlayAura("embodiment_of_earth", $player);
      break;
    case "mask_of_the_pouncing_lynx":
      $index = FindCharacterIndex($player, $cardID);
      AddDecisionQueue("YESNO", $player, "to_destroy_".Cardlink($cardID, $cardID));
      AddDecisionQueue("NOPASS", $player, "-");
      AddDecisionQueue("PASSPARAMETER", $player, $index, 1);
      AddDecisionQueue("DESTROYCHARACTER", $player, "-", 1);
      AddDecisionQueue("FINDINDICES", $player, "MASKPOUNCINGLYNX", 1);
      AddDecisionQueue("MAYCHOOSEDECK", $player, "<-", 1);
      AddDecisionQueue("MULTIBANISH", $player, "DECK,TT", 1);
      AddDecisionQueue("SETDQVAR", $player, "0", 1);
      AddDecisionQueue("WRITELOG", $player, "<0> was banished.", 1);
      AddDecisionQueue("SHUFFLEDECK", $player, "-", 1);
      break;
    case "grains_of_bloodspill":
      $hand = &GetHand($player);
      $resources = &GetResources($player);
      if (TypeContains($combatChain[0], "W", $mainPlayer) && (Count($hand) > 0 || $resources[0] > 0)) {
        AddDecisionQueue("YESNO", $player, "if you want to pay 1 to create a " . CardLink("vigor", "vigor"), 0, 1);
        AddDecisionQueue("NOPASS", $player, "-", 1);
        AddDecisionQueue("PASSPARAMETER", $player, "1", 1);
        AddDecisionQueue("PAYRESOURCES", $player, "<-", 1);
        AddDecisionQueue("WRITELOG", $player, CardLink($cardID, $cardID) . " created a " . CardLink("vigor", "vigor") . " token ", 1);
        AddDecisionQueue("PASSPARAMETER", $player, "vigor", 1);
        AddDecisionQueue("PUTPLAY", $player, "-", 1);
      }
      break;
    case "aether_crackers":
      $index = FindCharacterIndex($player, $cardID);
      AddDecisionQueue("YESNO", $player, "if_you_want_to_destroy_".Cardlink($cardID, $cardID)."_to_deal_one_arcane");
      AddDecisionQueue("NOPASS", $player, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $player, $index, 1);
      AddDecisionQueue("DESTROYCHARACTER", $player, "-", 1);
      AddDecisionQueue("DEALARCANE", $player, "1" . "-" . "aether_crackers" . "-" . "TRIGGER", 1);
      AddDecisionQueue("WRITELOG", $player, Cardlink($cardID, $cardID) . " were destroyed", 1);
      break;
    case "arakni_marionette":
    case "arakni_web_of_deceit": //arakni
      GiveAttackGoAgain();
      break;
    case "arakni_tarantula":
      WriteLog(CardLink($cardID, $cardID) . "'s venom saps 1 life from " . $defPlayer);
      PlayerLoseHealth($defPlayer, 1);
      break;
    case "cindra_dracai_of_retribution":
    case "cindra":
    case "fang_dracai_of_blades":
    case "fang"://Fang and Cindra
      PlayAura("fealty", $player);
      break;
    case "blood_splattered_vest":
      AddDecisionQueue("YESNO", $player, "to_add_a_stain_counter_to_".Cardlink($cardID, $cardID));
      AddDecisionQueue("NOPASS", $player, "-");
      AddDecisionQueue("SPECIFICCARD", $player, "BLOODSPATTEREDVEST", 1);
      break;
    default:
      break;
  }
}

function ProcessItemsEffect($cardID, $player, $target, $uniqueID)
{
  global $layers, $combatChainState, $CCS_GoesWhereAfterLinkResolves;
  $otherPlayer = ($player == 1 ? 2 : 1);
  if (CardType($target) == "AA" && SearchCurrentTurnEffects("tarpit_trap_yellow", $player, count($layers) <= LayerPieces())) {
    WriteLog("Hit effect prevented by " . CardLink("tarpit_trap_yellow", "tarpit_trap_yellow"));
    return true;
  }
  switch ($cardID) {
    case "powder_keg_blue":
      AddDecisionQueue("YESNO", $player, "if_you_want_to_destroy_" . CardLink($cardID, $cardID) . "_and_a_defending_equipment?");
      AddDecisionQueue("NOPASS", $player, "-");
      AddDecisionQueue("SEARCHCOMBATCHAIN", $player, "E", 1);
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose a defending equipment to destroy", 1);
      AddDecisionQueue("CHOOSECARDID", $player, "<-", 1);
      AddDecisionQueue("POWDERKEG", $player, "-", 1);
      break;
    case "tick_tock_clock_red":
      $index = SearchItemsForUniqueID($uniqueID, $player);
      DestroyItemForPlayer($player, $index);
      AddDecisionQueue("PASSPARAMETER", $player, "0");
      AddDecisionQueue("SETDQVAR", $player, "0");
      for ($i = 0; $i < 2; ++$i) {
        AddDecisionQueue("MULTIZONEINDICES", $player, "THEIRITEMS&MYITEMS", 1);
        AddDecisionQueue("SETDQCONTEXT", $player, "Choose an item to destroy");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
        AddDecisionQueue("MZDESTROY", $player, "-");
        AddDecisionQueue("INCDQVARIFNOTPASS", $player, "0");
      }
      AddDecisionQueue("SPECIFICCARD", $otherPlayer, "TICKTOCKCLOCK");
      break;
    case "boom_grenade_red":
    case "boom_grenade_yellow":
    case "boom_grenade_blue":
      if ($cardID == "boom_grenade_red") $amount = 4;
      else if ($cardID == "boom_grenade_yellow") $amount = 3;
      else $amount = 2;
      DamageTrigger($otherPlayer, $amount, "DAMAGE", $cardID);
      DestroyItemForPlayer($player, SearchItemsForUniqueID($uniqueID, $player));
      break;
    case "autosave_script_blue":
      $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "BOTDECK";
      break;
    default:
      break;
  }
}

function ProcessTrigger($player, $parameter, $uniqueID, $target = "-", $additionalCosts = "-", $from = "-")
{
  global $combatChain, $CS_NumNonAttackCards, $CS_ArcaneDamageDealt, $CS_NumRedPlayed, $CS_DamageTaken, $EffectContext, $CombatChain, $CCS_GoesWhereAfterLinkResolves;
  global $CID_BloodRotPox, $CID_Inertia, $CID_Frailty, $mainPlayer, $combatChainState, $CCS_WeaponIndex, $defPlayer, $CS_NumEarthBanished;
  global $CS_DamagePrevention, $chainLinks;
  $items = &GetItems($player);
  $auras = &GetAuras($player);
  $parameter = ShiyanaCharacter($parameter);
  $EffectContext = $parameter;
  $otherPlayer = ($player == 1 ? 2 : 1);
  if ($additionalCosts == "ONHITEFFECT") {
    ProcessHitEffect($target, $combatChain[2], $uniqueID);
    return;
  }
  if ($additionalCosts == "CRUSHEFFECT") {
    ProcessCrushEffect($target);
    return;
  }
  if ($additionalCosts == "TOWEREFFECT") {
    ProcessTowerEffect($target);
    return;
  }
  if ($additionalCosts == "EFFECTHITEFFECT") {
    if (EffectHitEffect($target, $combatChain[2], $uniqueID)) {
      $index = FindCurrentTurnEffectIndex($player, $target);
      if ($index != -1) RemoveCurrentTurnEffect($index);
    }
    return;
  }
  if ($additionalCosts == "MAINCHARHITEFFECT") {
    ProcessMainCharacterHitEffect($parameter, $player, $target);
    return;
  }
  if ($additionalCosts == "ITEMHITEFFECT") {
    ProcessItemsEffect($parameter, $player, $target, $uniqueID);
    return;
  }
  switch ($parameter) {
    case "HEAVE":
      Heave();
      break;
    case "heart_of_fyendal_blue":
      if (PlayerHasLessHealth($player)) GainHealth(1, $player);
      break;
    case "rhinar_reckless_rampage":
    case "rhinar":
    case "rhinar":
      Intimidate();
      break;
    case "forged_for_war_yellow":
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "show_time_blue":
      Draw($player);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "blessing_of_deliverance_red":
    case "blessing_of_deliverance_yellow":
    case "blessing_of_deliverance_blue":
      if ($parameter == "blessing_of_deliverance_red") $amount = 3;
      else if ($parameter == "blessing_of_deliverance_yellow") $amount = 2;
      else $amount = 1;
      BlessingOfDeliveranceDestroy($amount);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "emerging_power_red":
    case "emerging_power_yellow":
    case "emerging_power_blue":
      AddCurrentTurnEffect($parameter, $player);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "stonewall_confidence_red":
    case "stonewall_confidence_yellow":
    case "stonewall_confidence_blue":
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "seismic_surge":
      AddCurrentTurnEffect($parameter, $player);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "katsu_the_wanderer":
    case "katsu":
      KatsuHit();
      break;
    case "mask_of_momentum":
      Draw($player);
      break;
    case "steelblade_supremacy_red":
      Draw($mainPlayer);
      break;
    case "eye_of_ophidia_blue":
      Opt($parameter, 2);
      break;
    case "teklo_core_blue":
      $index = SearchItemsForUniqueID($uniqueID, $player);
      --$items[$index + 1];
      GainResources($player, 2);
      if ($items[$index + 1] <= 0) DestroyItemForPlayer($player, $index);
      break;
    case "dissipation_shield_yellow":
      $index = SearchItemsForUniqueID($uniqueID, $player);
      --$items[$index + 1];
      if ($items[$index + 1] <= 0) DestroyItemForPlayer($player, $index);
      break;
    case "viserai_rune_blood":
    case "viserai":
      ViseraiPlayCard($target);
      break;
    case "bloodspill_invocation_red":
    case "bloodspill_invocation_yellow":
    case "bloodspill_invocation_blue":
      if ($parameter == "bloodspill_invocation_red") $amount = 3;
      else if ($parameter == "bloodspill_invocation_yellow") $amount = 2;
      else $amount = 1;
      WriteLog(CardLink($parameter, $parameter) . " created $amount runechants");
      PlayAura("runechant", $player, $amount);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "runechant":
      DealArcane(1, 1, "RUNECHANT", "runechant", player: $player);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "chains_of_eminence_red":
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "arknight_shard_blue":
      PlayAura("runechant", $player);
      break;
    case "beast_within_yellow":
      AddDecisionQueue("SPECIFICCARD", $player, "BEASTWITHIN");
      break;
    case "massacre_red":
      Intimidate();
      break;
    case "stamp_authority_blue":
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "towering_titan_red":
    case "towering_titan_yellow":
    case "towering_titan_blue":
      AddCurrentTurnEffect($parameter, $player);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "emerging_dominance_red":
    case "emerging_dominance_yellow":
    case "emerging_dominance_blue":
      AddCurrentTurnEffect($parameter, $player);
      DestroyAuraUniqueID($player, $uniqueID);
      break;  
    case "zephyr_needle":
    case "zephyr_needle":
      EvaluateCombatChain($totalAttack, $totalBlock, secondNeedleCheck: true);
      for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
        $blockVal = (intval(BlockValue($combatChain[$i])) + BlockModifier($combatChain[$i], "CC", 0) + $combatChain[$i + 6]);
        if ($totalBlock > 0 && ($blockVal > $totalAttack) && $combatChain[$i + 1] == $defPlayer) {
          DestroyCurrentWeapon();
        }
      }
      break;
    case "zen_state":
      $index = SearchAurasForUniqueID($uniqueID, $player);
      if ($auras[$index + 2] == 0) {
        DestroyAuraUniqueID($player, $uniqueID);
      } else {
        --$auras[$index + 2];
      }
      break;
    case "shiyana_diamond_gemini":
      AddDecisionQueue("MULTIZONEINDICES", $player, "MYCHAR:type=C&THEIRCHAR:type=C");
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose which hero to copy");
      AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
      AddDecisionQueue("MZOP", $player, "GETCARDID", 1);
      AddDecisionQueue("CHANGESHIYANA", $player, "<-", 1);
      AddDecisionQueue("APPENDLASTRESULT", $player, "-SHIYANA", 1);
      AddDecisionQueue("ADDCURRENTANDNEXTTURNEFFECT", $player, "<-", 1);
      break;
    case "data_doll_mkii":
      $index = SearchBanishForCard($player, $target);
      if($index == -1) break;
      PutItemIntoPlayForPlayer($target, $player);
      RemoveBanish($player, $index);
      break;
    case "viziertronic_model_i":
      AddDecisionQueue("DRAW", $player, "-", 1);
      MZMoveCard($player, "MYHAND", "MYTOPDECK", silent: true);
      break;
    case "tripwire_trap_red":
      TrapTriggered($parameter);
      AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_pay_1_to_allow_hit_effects_this_chain_link", 1, 1);
      AddDecisionQueue("NOPASS", $mainPlayer, $parameter, 1);
      AddDecisionQueue("PAYRESOURCES", $mainPlayer, "1", 1);
      AddDecisionQueue("ELSE", $mainPlayer, "-");
      AddDecisionQueue("TRIPWIRETRAP", $mainPlayer, "-", 1);
      break;
    case "pitfall_trap_yellow":
      TrapTriggered($parameter);
      AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_pay_1_to_avoid_taking_2_damage", 1, 1);
      AddDecisionQueue("NOPASS", $mainPlayer, $parameter, 1);
      AddDecisionQueue("PAYRESOURCES", $mainPlayer, "1", 1);
      AddDecisionQueue("ELSE", $mainPlayer, "-");
      AddDecisionQueue("TAKEDAMAGE", $mainPlayer, "2-" . $parameter, 1);
      break;
    case "rockslide_trap_blue":
      TrapTriggered($parameter);
      if (!IsAllyAttacking()) {
        AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_pay_1_to_avoid_your_attack_getting_-2", 1, 1);
        AddDecisionQueue("NOPASS", $mainPlayer, $parameter, 1);
        AddDecisionQueue("PAYRESOURCES", $mainPlayer, "1", 1);
        AddDecisionQueue("ELSE", $mainPlayer, "-");
        AddDecisionQueue("ATTACKMODIFIER", $player, "-2", 1);
      } else {
        AddDecisionQueue("ATTACKMODIFIER", $mainPlayer, "-2", 1);
      }
      break;
    case "dread_triptych_blue":
      if (GetClassState($player, $CS_NumNonAttackCards) > 0) PlayAura("runechant", $player);
      if (GetClassState($player, $CS_ArcaneDamageDealt) > 0) PlayAura("runechant", $player);
      break;
    case "runeblood_barrier_yellow":
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "metacarpus_node":
      AddDecisionQueue("YESNO", $player, "if_you_want_to_pay_1_to_give_+1_arcane_damage");
      AddDecisionQueue("NOPASS", $player, "-", 1, 1);
      AddDecisionQueue("PAYRESOURCES", $player, "1", 1);
      AddDecisionQueue("PASSPARAMETER", $player, "1", 1);
      AddDecisionQueue("BUFFARCANEPREVLAYER", $player, "metacarpus_node", 1);
      AddDecisionQueue("CHARFLAGDESTROY", $player, FindCharacterIndex($player, "metacarpus_node"), 1);
      break;
    case "BLOODDEBT":
      $numBloodDebt = SearchCount(SearchBanish($mainPlayer, "", "", -1, -1, "", "", true));
      $char = &GetPlayerCharacter($mainPlayer);
      if ($char[0] == "levia_redeemed" && +$char[1] == 2) {
        $deck = new Deck($mainPlayer);
        for ($i = 0; $i < $numBloodDebt; ++$i) $deck->BanishTop();
        return;
      }
      $health = &GetHealth($mainPlayer);
      if ($numBloodDebt > 0) {
        if ($health > 13 && $health - $numBloodDebt <= 13) {
          $numBloodDebt -= ($health - 13);
          $health = 13;
          if (SearchInventoryForCard($mainPlayer, "levia_redeemed") != "") {
            AddDecisionQueue("YESNO", $mainPlayer, "if you want to transform into ".CardLink("levia_redeemed", "levia_redeemed"));
            AddDecisionQueue("NOPASS", $mainPlayer, "-");
            AddDecisionQueue("PASSPARAMETER", $mainPlayer, $numBloodDebt, 1);
            AddDecisionQueue("TRANSFORMHERO", $mainPlayer, "levia_redeemed", 1);
            AddDecisionQueue("ELSE", $mainPlayer, "-");
          }
        }
        AddDecisionQueue("PASSPARAMETER", $mainPlayer, $numBloodDebt, 1);
        AddDecisionQueue("OP", $mainPlayer, "LOSEHEALTH", 1);
        AddDecisionQueue("WRITELOG", $mainPlayer, "Player $mainPlayer lost $numBloodDebt life due to Blood Debt ", 1);
      }
      break;
    case "merciful_retribution_yellow":
      DealArcane(1, 0, "STATIC", $parameter, false, $player);
      break;
    case "phantasmal_footsteps":
      $hand = &GetHand($player);
      $resources = &GetResources($player);
      if (Count($hand) > 0 || $resources[0] > 0) {
        if ($player == $defPlayer) {
          AddDecisionQueue("SETDQCONTEXT", $player, "Choose how much to pay for " . CardLink($parameter, $parameter));
          AddDecisionQueue("BUTTONINPUT", $player, "0,1");
          AddDecisionQueue("PAYRESOURCES", $player, "<-", 1);
          AddDecisionQueue("LESSTHANPASS", $player, "1", 1);
          AddDecisionQueue("PASSPARAMETER", $player, $target, 1);
          if (!SearchCurrentTurnEffects("phantasmal_footsteps", $player)) AddDecisionQueue("ADDCURRENTEFFECT", $player, "phantasmal_footsteps", 1);
        } else {
          AddDecisionQueue("YESNO", $player, "if_you_want_to_pay_1_to_gain_an_action_point", 0, 1);
          AddDecisionQueue("NOPASS", $player, "-", 1);
          AddDecisionQueue("PASSPARAMETER", $player, 1, 1);
          AddDecisionQueue("PAYRESOURCES", $player, "<-", 1);
          AddDecisionQueue("GAINACTIONPOINTS", $player, "1", 1);
          AddDecisionQueue("WRITELOG", $player, "Gained_an_action_point_from_" . CardLink("phantasmal_footsteps", "phantasmal_footsteps"), 1);
        }
      }
      break;
    case "hooves_of_the_shadowbeast":
      $index = FindCharacterIndex($player, $parameter);
      AddDecisionQueue("CHARREADYORPASS", $player, $index);
      AddDecisionQueue("YESNO", $player, "if_you_want_to_destroy_".Cardlink($parameter, $parameter)."_to_gain_an_action_point", 1);
      AddDecisionQueue("NOPASS", $player, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $player, $index, 1);
      AddDecisionQueue("DESTROYCHARACTER", $player, "-", 1);
      AddDecisionQueue("GAINACTIONPOINTS", $player, 1, 1);
      AddDecisionQueue("WRITELOG", $player, "Gained_an_action_point_from_".Cardlink($parameter, $parameter), 1);
      break;
    case "soul_shackle":
      $deck = new Deck($player);
      $deck->BanishTop(banishedBy: $parameter);
      break;
    case "ironhide_helm":
    case "ironhide_plate":
    case "ironhide_gauntlet":
    case "ironhide_legs":
    case "ironhide_gauntlet":
    case "ironhide_legs":
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose how much to pay for " . CardLink($parameter, $parameter));
      AddDecisionQueue("BUTTONINPUT", $player, "0,1");
      AddDecisionQueue("PAYRESOURCES", $player, "<-", 1);
      AddDecisionQueue("LESSTHANPASS", $player, "1", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $player, $parameter, 1);
      AddDecisionQueue("PASSPARAMETER", $player, 2, 1);
      AddDecisionQueue("COMBATCHAINCHARACTERDEFENSEMODIFIER", $player, $target, 1);
      break;
    case "endless_winter_red":
      for ($i = 1; $i < count($combatChain); $i += CombatChainPieces()) if ($combatChain[$i] == $player) PlayAura("frostbite", $player, effectController: $mainPlayer);
      break;
    case "emerging_avalanche_red":
    case "emerging_avalanche_yellow":
    case "emerging_avalanche_blue":
      AddCurrentTurnEffect($parameter, $player);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "strength_of_sequoia_red":
    case "strength_of_sequoia_yellow":
    case "strength_of_sequoia_blue":
      AddCurrentTurnEffect($parameter, $player);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "briar_warden_of_thorns":
    case "briar":
      PlayAura("embodiment_of_lightning", $player);
      break;
    case "bramble_spark_red":
    case "bramble_spark_yellow":
    case "bramble_spark_blue":
      DealArcane(1, 0, "PLAYCARD", $target, true);
      break;
    case "embodiment_of_earth":
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "embodiment_of_lightning":
      WriteLog(CardLink($parameter, $parameter) . " grants go again");
      GiveAttackGoAgain();
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "frostbite":
      $location = $additionalCosts == "EQUIP" ? "EQUIP" : "AURAS";
      DestroyAuraUniqueID($player, $uniqueID, $location);
      break;
    case "mark_of_lightning":
      $index = FindCharacterIndex($player, $parameter);
      AddDecisionQueue("YESNO", $player, "destroy_".Cardlink($parameter, $parameter)."_to_have_the_attack_deal_1_damage");
      AddDecisionQueue("NOPASS", $player, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $player, $index, 1);
      AddDecisionQueue("DESTROYCHARACTER", $player, "-", 1);
      AddDecisionQueue("DEALDAMAGE", $otherPlayer, 1 . "-" . $combatChain[0] . "-" . "TRIGGER", 1);
      break;
    case "channel_thunder_steppe_yellow":
      AddDecisionQueue("YESNO", $player, "do_you_want_to_pay_1_to_give_your_action_go_again", 0, 1);
      AddDecisionQueue("NOPASS", $player, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $player, 1, 1);
      AddDecisionQueue("PAYRESOURCES", $player, "<-", 1);
      AddDecisionQueue("GIVEACTIONGOAGAIN", $player, $target, 1);
      break;
    case "rampart_of_the_rams_head":
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose how much to pay for " . CardLink($parameter, $parameter));
      AddDecisionQueue("BUTTONINPUT", $player, "0,1");
      AddDecisionQueue("PAYRESOURCES", $player, "<-", 1);
      AddDecisionQueue("LESSTHANPASS", $player, "1", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $player, "rampart_of_the_rams_head", 1);
      break;
    case "embolden_red":
    case "embolden_yellow":
    case "embolden_blue":
      AddCurrentTurnEffect($parameter, $player);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "seek_and_destroy_red": case "plan_for_the_worst_blue":
      DestroyArsenal($target, effectController: $player);
      DiscardHand($target, false);
      break;
    case "sting_of_sorcery_blue":
      if(count($combatChain) > 0) DealArcane(1, 0, "PLAYCARD", $combatChain[0]);
      break;
    case "stalagmite_bastion_of_isenloft":
      PlayAura("frostbite", $player, effectController: $defPlayer);
      break;
    case "dissolution_sphere_yellow":
      $index = SearchItemsForUniqueID($uniqueID, $player);
      --$items[$index + 1];
      if ($items[$index + 1] < 0) DestroyItemForPlayer($player, $index);
      break;
    case "signal_jammer_blue":
      $index = SearchItemsForUniqueID($uniqueID, $player);
      if ($items[$index + 1] > 0) {
        AddDecisionQueue("YESNO", $player, "if_you_want_to_remove_a_Steam_Counter_and_keep_" . CardLink($items[$index], $items[$index]) . "_and_keep_it_in_play?", 1);
        AddDecisionQueue("REMOVECOUNTERITEMORDESTROY", $player, $index, 1);
      } else {
        WriteLog(CardLink($items[$index], $items[$index]) . " was destroyed");
        DestroyItemForPlayer($player, $index);
      }
      break;
    case "runeblood_incantation_red":
    case "runeblood_incantation_yellow":
    case "runeblood_incantation_blue":
      $index = SearchAurasForUniqueID($uniqueID, $player);
      if ($index == -1) break;
      $auras = &GetAuras($player);
      if ($auras[$index + 2] == 0) DestroyAuraUniqueID($player, $uniqueID);
      else {
        --$auras[$index + 2];
        PlayAura("runechant", $player);
      }
      break;
    case "iyslander":
    case "iyslander_stormbind":
    case "iyslander":
      PlayAura("frostbite", $otherPlayer, effectController: $player);
      break;
    case "pyroglyphic_protection_red":
    case "pyroglyphic_protection_yellow":
    case "pyroglyphic_protection_blue":
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "haze_bending_blue":
      PlayAura("spectral_shield", $player);
      break;
    case "pack_call_yellow":
      $deck = new Deck($player);
      if ($deck->Reveal() && ModifiedAttackValue($deck->Top(), $player, "DECK", source: "pack_call_yellow") < 6) {
        $card = $deck->AddBottom($deck->Top(remove: true), "DECK");
        WriteLog(CardLink("pack_call_yellow", "pack_call_yellow") . " put " . CardLink($card, $card) . " on the bottom of your deck");
      }
      break;
    case "burn_them_all_red":
      DealArcane(1, 1, "STATIC", $combatChain[0], false, $mainPlayer);
      break;
    case "mounting_anger_red":
    case "mounting_anger_yellow":
    case "mounting_anger_blue":
    case "rising_resentment_red":
    case "rising_resentment_yellow":
    case "rising_resentment_blue":
    case "soaring_strike_red":
    case "soaring_strike_yellow":
    case "soaring_strike_blue":
      $numDraconicLinks = NumDraconicChainLinks();
      MZMoveCard($mainPlayer, "MYHAND:type=AA;maxCost=" . ($numDraconicLinks > 0 ? $numDraconicLinks - 1 : -2), "MYBANISH,HAND,TT", may: true);
      AddDecisionQueue("PASSPARAMETER", $mainPlayer, "MYBANISH", 1);
      AddDecisionQueue("MZOP", $mainPlayer, "LASTMZINDEX", 1);
      AddDecisionQueue("MZOP", $mainPlayer, "GETUNIQUEID", 1);
      AddDecisionQueue("ADDLIMITEDCURRENTEFFECT", $mainPlayer, $parameter . ",HIT", 1);
      break;
    case "flameborn_retribution_red":
      if (GetClassState($player, $CS_DamageTaken) > 0) MZMoveCard($player, "MYDISCARD:isSameName=phoenix_flame_red", "MYHAND", may: true);
      break;
    case "flamecall_awakening_red":
      if (GetClassState($player, $CS_NumRedPlayed) > 1 && CanRevealCards($player)) {
        MZMoveCard($player, "MYDECK:isSameName=phoenix_flame_red", "MYHAND", may: true);
        AddDecisionQueue("SHUFFLEDECK", $player, "-", 1);
      }
      return "";
    case "insidious_chill_blue":
      $index = SearchAurasForUniqueID($uniqueID, $player);
      if ($index == -1) break;
      $auras = &GetAuras($player);
      --$auras[$index + 2];
      PayOrDiscard($target, 2, true);
      if ($auras[$index + 2] == 0) {
        WriteLog(CardLink($auras[$index], $auras[$index]) . " is destroyed");
        DestroyAura($player, $index);
      }
      break;
    case "isenhowl_weathervane_red":
    case "isenhowl_weathervane_yellow":
    case "isenhowl_weathervane_blue":
      if ($parameter == "isenhowl_weathervane_red") $numFrostbite = 4;
      else if ($parameter == "isenhowl_weathervane_yellow") $numFrostbite = 3;
      else $numFrostbite = 2;
      PlayAura("frostbite", $target, $numFrostbite, effectController: $player);
      break;
    case "read_the_ripples_red":
    case "read_the_ripples_yellow":
    case "read_the_ripples_blue":
      $i = SearchAurasForUniqueID($uniqueID, $player);
      if ($i == -1) break;
      $auras = &GetAuras($player);
      if ($auras[$i] == "read_the_ripples_red") $numOpt = 3;
      else if ($auras[$i] == "read_the_ripples_yellow") $numOpt = 2;
      else $numOpt = 1;
      for ($j = 0; $j < $numOpt; ++$j) PlayerOpt($player, 1);
      AddDecisionQueue("DRAW", $player, "-", 1);
      DestroyAura($player, $i);
      break;
    case "crown_of_providence":
      BottomDeckMultizone($player, "MYHAND", "MYARS");
      AddDecisionQueue("DRAW", $player, "-", 1);
      break;
    case "fog_down_yellow":
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "flex_red":
    case "flex_yellow":
    case "flex_blue":
      ChooseToPay($player, $parameter, "0,2");
      AddDecisionQueue("PASSPARAMETER", $player, $target, 1);
      AddDecisionQueue("COMBATCHAINPOWERMODIFIER", $player, "2", 1);
      break;
    case "fyendals_fighting_spirit_red":
    case "fyendals_fighting_spirit_yellow":
    case "fyendals_fighting_spirit_blue":
      if (PlayerHasLessHealth($player)) GainHealth(1, $player);
      break;
    case "brothers_in_arms_red":
    case "brothers_in_arms_yellow":
    case "brothers_in_arms_blue":
      ChooseToPay($player, $parameter, "0,1");
      AddDecisionQueue("PASSPARAMETER", $player, $target, 1);
      AddDecisionQueue("COMBATCHAINDEFENSEMODIFIER", $player, "2", 1);
      break;
    case "sigil_of_protection_red":
    case "sigil_of_protection_yellow":
    case "sigil_of_protection_blue":
    case "sigil_of_brilliance_yellow":
    case "sigil_of_earth_blue":
    case "sigil_of_lightning_blue":
    case "sigil_of_the_arknight_blue":
    case "sigil_of_deadwood_blue":
    case "sigil_of_temporal_manipulation_blue":
    case "sigil_of_forethought_blue":
    case "sigil_of_cycles_blue":
    case "sigil_of_fyendal_blue":
      DestroyAuraUniqueID($player, $uniqueID); //destroy sigils at start of action phase
      break;
    case "dracona_optimai":
      $deck = new Deck($player);
      if ($deck->Reveal(3)) {
        $cards = explode(",", $deck->Top(amount: 3));
        $numRed = 0;
        for ($j = 0; $j < count($cards); ++$j) if (PitchValue($cards[$j]) == 1) ++$numRed;
        if ($numRed > 0) DealArcane($numRed * 2, 2, "ABILITY", $combatChain[0], false, $player);
      }
      break;
    case "tomeltai":
      $deck = new Deck($player);
      if ($deck->Reveal(2)) {
        $cards = explode(",", $deck->Top(amount: 2));
        $numRed = 0;
        for ($j = 0; $j < count($cards); ++$j) if (PitchValue($cards[$j]) == 1) ++$numRed;
        if ($numRed > 0) {
          $otherPlayer = ($player == 1 ? 2 : 1);
          AddDecisionQueue("FINDINDICES", $otherPlayer, "EQUIP");
          AddDecisionQueue("CHOOSETHEIRCHARACTER", $player, "<-", 1);
          AddDecisionQueue("MODDEFCOUNTER", $otherPlayer, (-1 * $numRed), 1);
          AddDecisionQueue("DESTROYEQUIPDEF0", $player, "-", 1);
        }
      }
      break;
    case "dominia":
      $deck = new Deck($player);
      if ($deck->Reveal(1)) {
        if (PitchValue($deck->Top()) == 1) {
          $otherPlayer = ($player == 1 ? 2 : 1);
          AddDecisionQueue("SHOWHANDWRITELOG", $otherPlayer, "<-", 1);
          AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
          AddDecisionQueue("CHOOSETHEIRHAND", $player, "<-", 1);
          AddDecisionQueue("MULTIREMOVEHAND", $otherPlayer, "-", 1);
          AddDecisionQueue("MULTIBANISH", $otherPlayer, "HAND,-", 1);
        }
      }
      break;
    case "azvolai":
      DealArcane(1, 2, "PLAYCARD", $combatChain[0], false, $mainPlayer, true, true);
      DealArcane(1, 2, "PLAYCARD", $combatChain[0], false, $mainPlayer, true, false);
      break;
    case "beaten_trackers":
      $index = FindCharacterIndex($player, $parameter);
      AddDecisionQueue("CHARREADYORPASS", $player, $index);
      AddDecisionQueue("YESNO", $player, "if_you_want_to_destroy_".Cardlink($parameter, $parameter)."_to_gain_an_action_point", 1);
      AddDecisionQueue("NOPASS", $player, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $player, $index, 1);
      AddDecisionQueue("DESTROYCHARACTER", $player, "-", 1);
      AddDecisionQueue("GAINACTIONPOINTS", $player, 1, 1);
      AddDecisionQueue("WRITELOG", $player, "Player_" . $player . "_gained_an_action_point_from_" . CardLink("beaten_trackers", "beaten_trackers"), 1);
      break;
    case "skull_crack_red":
      GainResources($player, 1);
      break;
    case "berserk_yellow":
      $deck = new Deck($player);
      if ($deck->Reveal() && ModifiedAttackValue($deck->Top(), $player, "DECK", source: "berserk_yellow") >= 6) {
        Draw($player);
        WriteLog(CardLink($parameter, $parameter) . " drew a card");
      }
      break;
    case "reincarnate_red":
    case "reincarnate_yellow":
    case "reincarnate_blue":
      $index = SearchGetLastIndex(SearchMultizone($player, "MYDISCARD:cardID=" . $parameter));
      RemoveGraveyard($player, $index);
      $deck = new Deck($player);
      $deck->AddBottom($parameter, "GY");
      break;
    case "plasma_mainline_red":
      $targetIndex = SearchItemsForUniqueID($target, $player);
      AddDecisionQueue("YESNO", $player, "if_you_want_to_move_a_steam_counter_to_" . CardLink($items[$targetIndex], $items[$targetIndex]));
      AddDecisionQueue("NOPASS", $player, "-");
      AddDecisionQueue("PASSPARAMETER", $player, $uniqueID . "," . $target, 1);
      AddDecisionQueue("SPECIFICCARD", $player, "PLASMAMAINLINE", 1);
      break;
    case "crankshaft_red":
    case "crankshaft_yellow":
    case "crankshaft_blue":
      AddDecisionQueue("MULTIZONEINDICES", $player, "MYITEMS:isSameName=hyper_driver_red");
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose a ".Cardlink("hyper_driver", "hyper_driver")." to get a steam counter", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
      AddDecisionQueue("MZADDCOUNTER", $player, "-", 1);
      break;
    case "hyper_driver_red":
    case "hyper_driver_red":
    case "hyper_driver_yellow":
    case "hyper_driver_blue":
    case "hyper_driver":
      AddDecisionQueue("HYPERDRIVER", $player, $uniqueID, 1);
      break;
    case "arakni_huntsman":
    case "arakni":
      AddDecisionQueue("DECKCARDS", $otherPlayer, "0", 1);
      AddDecisionQueue("SETDQVAR", $player, "0", 1);
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose if you want to sink <0>", 1);
      AddDecisionQueue("YESNO", $player, "if_you_want_to_sink_the_opponent's_card", 1);
      AddDecisionQueue("NOPASS", $player, $parameter, 1);
      AddDecisionQueue("WRITELOG", $player, "<b>Arakni</b> sunk the top card", 1);
      AddDecisionQueue("FINDINDICES", $otherPlayer, "TOPDECK", 1);
      AddDecisionQueue("MULTIREMOVEDECK", $otherPlayer, "<-", 1);
      AddDecisionQueue("ADDBOTDECK", $otherPlayer, "Skip", 1);
      AddDecisionQueue("ELSE", $player, "-");
      AddDecisionQueue("WRITELOG", $player, "<b>Arakni</b> left the top card there", 1);
      break;
    case "vynnset_iron_maiden":
    case "vynnset":
      AddDecisionQueue("YESNO", $player, "if you want to pay 1 life for " . CardLink($parameter, $parameter), 1);
      AddDecisionQueue("NOPASS", $player, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $player, "1", 1);
      AddDecisionQueue("OP", $player, "LOSEHEALTH", 1);
      if (!SearchCurrentTurnEffects($parameter, $player)) { //The effect only apply to one event of damage. Anti-duplicate.
        AddDecisionQueue("ADDCURRENTEFFECT", $player, $parameter, 1);
      }
      break;
    case "hornets_sting":
      $deck = new Deck($player);
      if ($deck->Reveal()) {
        if (CardSubType($deck->Top()) == "Arrow") {
          if (IsAllyAttacking()) {
            $allyIndex = "THEIRALLY-" . $combatChainState[$CCS_WeaponIndex];
            AddDecisionQueue("PASSPARAMETER", $player, $allyIndex, 1);
          } else AddDecisionQueue("MULTIZONEINDICES", $player, "THEIRCHAR:type=C", 1);
          AddDecisionQueue("SETDQCONTEXT", $player, "Choose a target to deal 1 damage");
          AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
          AddDecisionQueue("MZDAMAGE", $player, "1,DAMAGE," . $parameter, 1);
          WriteLog(CardLink($parameter, $parameter) . " deals 1 damage");
        } else {
          WriteLog("The card was put on the bottom of your deck");
          $deck->AddBottom($deck->Top(remove: true), "DECK");
        }
      }
      break;
    case "heat_seeker_red":
      $deck = new Deck($player);
      if (!$deck->Empty() && !ArsenalFull($player)) AddArsenal($deck->Top(remove: true), $player, "DECK", "UP");
      break;
    case "wave_of_reality":
      PlayAura("spectral_shield", $player);
      break;
    case "tome_of_aeo_blue":
      Draw($player);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "crown_of_dominion":
      PutItemIntoPlayForPlayer("gold", $player, effectController: $player);
      WriteLog(CardLink($parameter, $parameter) . " created a Gold token");
      break;
    case "ponder":
      Draw($player, false);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "plague_hive_yellow":
      $rand = GetRandom(1, 3);
      switch ($rand) {
        case 1:
          $auraCreated = "inertia";
          break;
        case 2:
          $auraCreated = "frailty";
          break;
        case 3:
          $auraCreated = "bloodrot_pox";
          break;
        default:
          break;
      }
      WriteLog(CardLink("plague_hive_yellow", "plague_hive_yellow") . " created a " . CardLink($auraCreated, $auraCreated));
      PlayAura($auraCreated, $otherPlayer, effectController: $player);
      break;
    case "cyclone_roundhouse_yellow":
      $cardsToBanish = array();
      for ($i = 0; $i < count($chainLinks); $i++) {
        if (count($chainLinks[$i]) == ChainLinksPieces()) continue;
        $defendingCards = GetDefendingCardsFromCombatChainLink($chainLinks[$i], $defPlayer);
        if (count($defendingCards) > 0) {
          $randomIndex = GetRandom(0, count($defendingCards) - 1);
          array_push($cardsToBanish, $defendingCards[$randomIndex]);
        }
      }
      $defendingCards = GetChainLinkCards($defPlayer);
      if ($defendingCards != "") {
        $defendingCards = explode(",", $defendingCards);
        $randomIndex = GetRandom(0, count($defendingCards) - 1);
        AddDecisionQueue("PASSPARAMETER", $defPlayer, $defendingCards[$randomIndex]);
        AddDecisionQueue("REMOVECOMBATCHAIN", $defPlayer, "-", 1);
        array_push($cardsToBanish, $combatChain[$defendingCards[$randomIndex]]);
      }
      for ($i = 0; $i < count($cardsToBanish); $i++)
        BanishCardForPlayer($cardsToBanish[$i], $defPlayer, "CC");
      for ($i = 0; $i < count($cardsToBanish); $i++) {
        AddDecisionQueue("PASSPARAMETER", $defPlayer, $cardsToBanish[$i]);
        AddDecisionQueue("REMOVECOMBATCHAIN", $defPlayer, "-", 1);
        AddDecisionQueue("MULTIBANISH", $defPlayer, "CC,-", 1);
      }
      break;
    case "riptide_lurker_of_the_deep":
    case "riptide":
      SuperReload();
      break;
    case "crows_nest":
      $arsenal = &GetArsenal($player);
      AddDecisionQueue("YESNO", $player, "if you want to pay 1 to put an aim counter on the arrow");
      AddDecisionQueue("NOPASS", $player, "-");
      AddDecisionQueue("PAYRESOURCES", $player, "1", 1);
      AddDecisionQueue("PASSPARAMETER", $player, count($arsenal) - ArsenalPieces(), 1);
      AddDecisionQueue("ADDAIMCOUNTER", $player, "-", 1);
      break;
    case "wayfinders_crest":
      LookAtTopCard($player, "wayfinders_crest");
      break;
    case "buzzsaw_trap_blue":
      AddCurrentTurnEffect($parameter, $mainPlayer);
      TrapTriggered($parameter);
      break;
    case "collapsing_trap_blue":
      $hand = &GetHand($mainPlayer);
      $numDraw = count($hand) - 1;
      DiscardHand($mainPlayer);
      for ($i = 0; $i < $numDraw; ++$i) Draw($mainPlayer);
      if ($numDraw > 0) WriteLog("Player $mainPlayer discarded their hand and drew $numDraw cards");
      else WriteLog("Player $mainPlayer discarded their hand.");
      TrapTriggered($parameter);
      break;
    case "spike_pit_trap_blue":
      $deck = new Deck($mainPlayer);
      $topDeck = $deck->Top(remove: true);
      AddGraveyard($topDeck, $mainPlayer, "DECK");
      $numName = SearchCount(SearchMultizone($mainPlayer, "MYDISCARD:isSameName=" . $topDeck));
      LoseHealth($numName, $mainPlayer);
      WriteLog(Cardlink($topDeck, $topDeck) . " was put in the graveyard. Player $mainPlayer lost $numName life");
      TrapTriggered($parameter);
      break;
    case "boulder_trap_yellow":
      AddDecisionQueue("FINDINDICES", $mainPlayer, "EQUIP");
      AddDecisionQueue("CHOOSETHEIRCHARACTER", $player, "<-", 1);
      AddDecisionQueue("MODDEFCOUNTER", $mainPlayer, "-1", 1);
      WriteLog("Trap triggered and puts a -1 counter on an equipment");
      TrapTriggered($parameter);
      break;
    case "pendulum_trap_yellow":
      $deck = new Deck($mainPlayer);
      $rv = "put  ";
      for ($i = 0; $i < 2; ++$i) {
        $cardRemoved = $deck->Top(remove: true);
        AddGraveyard($cardRemoved, $mainPlayer, "DECK");
        if ($i == 0) $rv .= Cardlink($cardRemoved, $cardRemoved);
        else $rv .= " and " . Cardlink($cardRemoved, $cardRemoved) . " into the graveyard";
      }
      WriteLog($rv);
      TrapTriggered($parameter);
      break;
    case "tarpit_trap_yellow":
      AddCurrentTurnEffect($parameter, $mainPlayer);
      if (!IsAllyAttacking()) TrapTriggered($parameter);
      break;
    case "virulent_touch_red":
    case "virulent_touch_yellow":
    case "virulent_touch_blue":
      WriteLog(CardLink($parameter, $parameter) . " creates a Bloodrot Pox from being blocked from hand.");
      PlayAura($CID_BloodRotPox, $defPlayer, effectController:$mainPlayer);
      break;
    case "bloodrot_trap_red":
      PlayAura($CID_BloodRotPox, $mainPlayer, effectController: $defPlayer);
      TrapTriggered($parameter);
      break;
    case "frailty_trap_red":
      PlayAura($CID_Frailty, $mainPlayer, effectController: $defPlayer);
      TrapTriggered($parameter);
      break;
    case "inertia_trap_red":
      PlayAura($CID_Inertia, $mainPlayer, effectController: $defPlayer);
      TrapTriggered($parameter);
      break;
    case "vambrace_of_determination":
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose how much to pay for " . CardLink($parameter, $parameter));
      AddDecisionQueue("BUTTONINPUT", $player, "0,1");
      AddDecisionQueue("PAYRESOURCES", $player, "<-", 1);
      AddDecisionQueue("LESSTHANPASS", $player, "1", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $player, $parameter . "-BB", 1);
      AddDecisionQueue("PASSPARAMETER", $player, 1, 1);
      AddDecisionQueue("COMBATCHAINCHARACTERDEFENSEMODIFIER", $player, $target, 1);
      break;
    case "give_and_take_red":
      MZMoveCard($mainPlayer, "MYDISCARD:type=A;maxCost=" . CachedTotalAttack()-1 . "&MYDISCARD:type=AA;maxCost=" . CachedTotalAttack()-1, "MYTOPDECK", may: true);
      break;
    case "light_of_sol_yellow":
      $deck = new Deck($player);
      if ($deck->Reveal() && PitchValue($deck->Top()) == 2) {
        AddDecisionQueue("YESNO", $player, "if_you_want_to_put_the_card_in_your_soul");
        AddDecisionQueue("NOPASS", $player, "-");
        AddDecisionQueue("PASSPARAMETER", $player, "MYDECK-0", 1);
        AddDecisionQueue("MZADDZONE", $player, "MYSOUL,DECK", 1);
        AddDecisionQueue("MZREMOVE", $player, "-", 1);
        AddDecisionQueue("WRITELOG", $player, "Added to soul by Light of Sol", 1);
      }
      break;
    case "prism_awakener_of_sol":
    case "prism_advent_of_thrones":
      MZMoveCard($player, "MYDECK:subtype=Figment", "MYPERMANENTS", may: true);
      AddDecisionQueue("PLAYABILITY", $player, "-", 1);
      AddDecisionQueue("SHUFFLEDECK", $player, "-", 1);
      break;
    case "soulbond_resolve":
      Charge();
      break;
    case "banneret_of_courage_yellow":
      PlayAura("courage", $player);
      break;
    case "banneret_of_gallantry_yellow":
      PlayAura("quicken", $player);
      break;
    case "banneret_of_protection_yellow":
      PlayAura("spellbane_aegis", $player);
      break;
    case "banneret_of_resilience_yellow":
      AddCurrentTurnEffect("banneret_of_resilience_yellow", $player);
      break;
    case "banneret_of_salvation_yellow":
      AddCurrentTurnEffect("banneret_of_salvation_yellow", $player);
      break;
    case "banneret_of_vigor_yellow":
      AddCurrentTurnEffect("banneret_of_vigor_yellow", $player);
      break;
    case "scowling_flesh_bag":
      global $mainPlayer;
      Intimidate($mainPlayer);
      break;
    case $CID_BloodRotPox:
      $hand = &GetHand($player);
      $resources = &GetResources($player);
      if (Count($hand) > 0 || $resources[0] > 0) {
        AddDecisionQueue("YESNO", $player, "if_you_want_to_pay_3_to_avoid_taking_2_damage", 0, 1);
        AddDecisionQueue("NOPASS", $player, "-", 1);
        AddDecisionQueue("PASSPARAMETER", $player, "3", 1);
        AddDecisionQueue("PAYRESOURCES", $player, "3", 1);
        AddDecisionQueue("ELSE", $player, "-");
      }
      AddDecisionQueue("TAKEDAMAGE", $player, "2-bloodrot_pox", 1);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case $CID_Inertia:
      $deck = new Deck($player);
      WriteLog("Processing the end of turn effect of Inertia.");
      for ($i = 0; $i < count(GetArsenal($player)) + count(GetHand($player)); $i++) {
        BottomDeckMultizone($player, "MYHAND", "MYARS", true, "Choose a card from your hand or arsenal to add on the bottom of your deck");
      }
      AddDecisionQueue("WRITELOG", $player, ("The cards and arsenal of Player " . $player . " was put on the bottom of their deck."));
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case $CID_Frailty:
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "eloquence":
      global $CS_NextNAACardGoAgain;
      SetClassState($player, $CS_NextNAACardGoAgain, 1);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "levia_redeemed":
      $banish = &GetBanish($player);
      for ($i = count($banish) - BanishPieces(); $i >= 0; $i -= BanishPieces()) {
        if ($banish[$i + 1] == "levia_redeemed") {
          TurnBanishFaceDown($player, $i);
          break;
        }
      }
      break;
    case "firewall_red":
    case "firewall_yellow":
    case "firewall_blue":
      $deck = new Deck($player);
      if ($deck->Reveal()) {
        if (!SubtypeContains($deck->Top(), "Evo", $player)) {
          WriteLog("The card was put on the bottom of your deck");
          $deck->AddBottom($deck->Top(remove: true), "DECK");
        }
      }
      break;
    case "civic_peak":
      Draw($otherPlayer);
      break;
    case "civic_duty":
      PlayAura("vigor", $otherPlayer);
      break;
    case "civic_guide":
      PlayAura("might", $otherPlayer);
      break;
    case "tiger_eye_reflex_yellow":
    case "tiger_eye_reflex_blue":
      BanishCardForPlayer("crouching_tiger", $player, "-", "NT");
      break;
    case "civic_steps":
      PlayAura("quicken", $otherPlayer);
      break;//Quicken
    case "crowd_control_red":
    case "crowd_control_yellow":
    case "crowd_control_blue":
      ChooseToPay($player, $parameter, "0,3");
      AddDecisionQueue("PASSPARAMETER", $player, $target, 1);
      AddDecisionQueue("COMBATCHAINDEFENSEMODIFIER", $player, "1", 1); // Technically wrong, it should be +1 for each opposing heroes
      break;
    case "adaptive_plating":
    case "ratchet_up_red":
    case "ratchet_up_yellow":
    case "ratchet_up_blue":
    case "soup_up_red":
    case "soup_up_yellow":
    case "soup_up_blue":
    case "torque_tuned_red":
    case "torque_tuned_yellow":
    case "torque_tuned_blue":
    case "cognition_field_red":
    case "cognition_field_yellow":
    case "cognition_field_blue":
    case "infuse_alloy_red":
    case "infuse_alloy_yellow":
    case "infuse_alloy_blue":
    case "infuse_titanium_red":
    case "infuse_titanium_yellow":
    case "infuse_titanium_blue":
    case "steel_street_hoons_blue": //Galvanize
      MZChooseAndDestroy($player, "MYITEMS", may: true, context: "Choose an item to galvanize");
      AddDecisionQueue("PASSPARAMETER", $player, $target, 1);
      AddDecisionQueue("COMBATCHAINDEFENSEMODIFIER", $player, "2", 1);
      break;
    case "stasis_cell_blue":
      AddDecisionQueue("FINDINDICES", $otherPlayer, "EQUIP");
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose target equipment, it cannot be activated until the end of its controller next turn");
      AddDecisionQueue("CHOOSETHEIRCHARACTER", $player, "<-", 1);
      AddDecisionQueue("ADDSTASISTURNEFFECT", $otherPlayer, "stasis_cell_blue-", 1);
      break;
    case "evo_magneto_blue_equip":
      if (IsAllyAttacking()) {
        WriteLog("<span style='color:red;'>No damage is dealt because there is no attacking hero when allies attack.</span>");
      } else {
        $index = FindCharacterIndex($player, "evo_magneto_blue_equip");
        CharacterChooseSubcard($player, $index, isMandatory: false);
        AddDecisionQueue("ADDDISCARD", $player, "-", 1);
        AddDecisionQueue("MULTIZONEINDICES", $player, "THEIRITEMS:minCost=0;maxCost=1", 1);
        AddDecisionQueue("SETDQCONTEXT", $player, "Choose an item to gain control.", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
        AddDecisionQueue("MZOP", $player, "GAINCONTROL", 1);
      }
      break;
    case "kayo_armed_and_dangerous":
    case "kayo":
    case "canopy_shelter_blue":
      PlayAura("might", $player); //Might
      break;
    case "apex_bonebreaker":
      $num6Block = 0;
      for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
        if (ModifiedAttackValue($combatChain[$i], $player, "CC", "apex_bonebreaker") >= 6) ++$num6Block;
      }
      if ($num6Block) {
        PlayAura("might", $player);//Might
        WriteLog(CardLink("apex_bonebreaker", "apex_bonebreaker") . " created a " . CardLink("might", "might") . " token");
      }
      break;
    case "pack_call_red":
    case "pack_call_yellow":
    case "pack_call_blue":
      $deck = new Deck($player);
      if ($deck->Reveal() && ModifiedAttackValue($deck->Top(), $player, "DECK", source: $parameter) < 6) {
        $card = $deck->AddBottom($deck->Top(remove: true), "DECK");
        WriteLog(CardLink($parameter, $parameter) . " put " . CardLink($card, $card) . " on the bottom of your deck");
      }
      break;
    case "stonewall_impasse":
      Clash($parameter, effectController: $player);
      break;
    case "gauntlets_of_iron_will":
      AddCurrentTurnEffect("gauntlets_of_iron_will," . CachedTotalAttack(), $mainPlayer);
      break;
    case "golden_glare":
      $yellowPitchCards = 0;
      for ($i = CombatChainPieces(); $i < count($combatChain); $i += CombatChainPieces()) {
        if (PitchValue($combatChain[$i]) == 2) ++$yellowPitchCards;
      }
      if ($yellowPitchCards >= 2) {
        PutItemIntoPlayForPlayer("gold", $player, effectController: $player);
        WriteLog(CardLink("golden_glare", "golden_glare") . " created a Gold token");
      }
      break;
    case "the_golden_son_yellow":
      PutItemIntoPlayForPlayer("gold", $player, effectController: $player);
      WriteLog(CardLink($parameter, $parameter) . " created a Gold Token for Player " . $player);
      break;
    case "trounce_red":
      Clash($parameter, effectController: $player);
      break;
    case "thunk_red":
    case "thunk_yellow":
    case "thunk_blue":
      PlayAura("might", $player); //Vigor
      WriteLog(CardLink($parameter, $parameter) . " created a Might Token for Player " . $player);
      break;
    case "wallop_red":
    case "wallop_yellow":
    case "wallop_blue":
      PlayAura("vigor", $player); //Vigor
      WriteLog(CardLink($parameter, $parameter) . " created a Vigor Token for Player " . $player);
      break;
    case "commanding_performance_red":
      AddDecisionQueue("MULTIZONEINDICES", $player, "THEIRARS", 1);
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose which card you want to destroy from their arsenal", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
      AddDecisionQueue("MZDESTROY", $player, false, 1);
      break;
    case "no_fear_red":
      $banish = &GetBanish($player);
      $hand = &GetHand($player);
      for ($i = count($banish) - BanishPieces(); $i >= 0; $i -= BanishPieces()) {
        if ($banish[$i + 1] == "NOFEAR") {
          array_push($hand, $banish[$i]);
          RemoveBanish($player, $i);
        }
      }
      break;
    case "wall_of_meat_and_muscle_red":
      if (CountAura("might", $player) > 0) MZMoveCard($player, "MYDISCARD:type=AA", "MYTOPDECK", may: true);
      break;
    case "run_into_trouble_red":
      if (IsAllyAttacking()) {
        WriteLog("<span style='color:red;'>No damage is dealt because there is no attacking hero when allies attack.</span>");
      } else if (CountAura("agility", $player) > 0) {
        WriteLog(CardLink($parameter, $parameter) . " deals 1 damage");
        DealDamageAsync($otherPlayer, 1, "DAMAGE", $parameter);
      }
      break;
    case "hearty_block_red":
      if (CountAura("vigor", $player) > 0) GainHealth(1, $player);
      break;
    case "test_of_agility_red":
    case "clash_of_might_red":
    case "clash_of_might_yellow":
    case "clash_of_might_blue":
    case "test_of_might_red":
    case "clash_of_agility_red":
    case "clash_of_agility_yellow":
    case "clash_of_agility_blue":
    case "clash_of_vigor_red":
    case "clash_of_vigor_yellow":
    case "clash_of_vigor_blue":
    case "test_of_vigor_red":
    case "test_of_strength_red":
      Clash($parameter, effectController: $player);
      break;
    case "nasty_surprise_blue":
      PlayAura("agility", $player);//Agility
      PlayAura("might", $player);//Might
      PlayAura("vigor", $player);//Vigor
      WriteLog(CardLink($parameter, $parameter) . " created an " . CardLink("agility", "agility") . ", " . CardLink("might", "might") . " and " . CardLink("vigor", "vigor") . " tokens.");
      break;
    case "standing_order_red":
      MZMoveCard($player, "MYARS", "MYBOTDECK", may: true, silent: true);
      AddDecisionQueue("PASSPARAMETER", $player, $target, 1);
      AddDecisionQueue("COMBATCHAINPOWERMODIFIER", $player, "2", 1);
      AddDecisionQueue("COMBATCHAINDEFENSEMODIFIER", $player, "2", 1);
      break;
    case "hide_tanner":
      $index = FindCharacterIndex($player, $parameter);
      AddDecisionQueue("CHARREADYORPASS", $player, $index);
      AddDecisionQueue("YESNO", $player, "if_you_want_to_destroy_".Cardlink($parameter, $parameter)."_to_gain_2_".CardLink("might", "might")."_tokens", 1);
      AddDecisionQueue("NOPASS", $player, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $player, $index, 1);
      AddDecisionQueue("DESTROYCHARACTER", $player, "-", 1);
      AddDecisionQueue("PLAYAURA", $player, "might-2", 1);
      AddDecisionQueue("WRITELOG", $player, "Player_" . $player . "_gained_2_".Cardlink("might", "might")."_tokens_from_" . CardLink("hide_tanner", "hide_tanner"), 1);
      break;
    case "nuu_alluring_desire":
    case "nuu":
      NuuStaticAbility($target);
      break;
    case "meridian_pathway":
      AddDecisionQueue("YESNO", $player, "if_you_want_" . CardLink("meridian_pathway", "meridian_pathway") . "_to_gain_Ward_3");
      AddDecisionQueue("NOPASS", $player, "-");
      AddDecisionQueue("ADDCURRENTEFFECT", $player, "MERIDIANWARD", 1);
      break;
    case "waning_vengeance_red":
    case "waning_vengeance_yellow":
    case "waning_vengeance_blue":
      if (SearchPitchForColor($player, 3) > 0) PlayAura("spectral_shield", $player);
      break;
    case "attune_with_cosmic_vibrations_blue":
      $index = GetCombatChainIndex($parameter, $player);
      $chainCard = $CombatChain->Card($index);
      $chainCard->ModifyDefense(3);
      break;
    case "second_tenet_of_chi_moon_blue":
      Draw($player, effectSource:$parameter);
      WriteLog(CardLink($parameter, $parameter) . " draw a card.");
      break;
    case "essence_of_ancestry_body_red":
    case "essence_of_ancestry_soul_yellow":
    case "essence_of_ancestry_mind_blue":
      AddCurrentTurnEffect($parameter, $player, "PLAY");
      break;
    case "haunting_specter_red":
    case "haunting_specter_yellow":
    case "haunting_specter_blue":
      if (SearchAura($player, class: "ILLUSIONIST") < 0) PlayAura("spectral_shield", $player, numAttackCounters: 1);
      else PlayAura("spectral_shield", $player);
      break;
    case "vengeful_apparition_red":
    case "vengeful_apparition_yellow":
    case "vengeful_apparition_blue":
      AddCurrentTurnEffect($parameter . "-INST", $player, "PLAY");
      break;
    case "stride_of_reprisal":
      AddPlayerHand("crouching_tiger", $player, $parameter);
      break;
    case "mask_of_wizened_whiskers":
      MZMoveCard($player, "MYDISCARD:comboOnly=true", "MYBOTDECK");
      break;
    case "traverse_the_universe":
      MZMoveCard($player, "MYDECK:isSameName=MST099_inner_chi_blue", "MYHAND", may: true);
      AddDecisionQueue("SHUFFLEDECK", $player, "-");
      break;
    case "stonewall_gauntlet":
      if (HasIncreasedAttack()) {
        AddCurrentTurnEffect($parameter, $otherPlayer);
      }
      break;
    case "battlefront_bastion_blue":
    case "battlefront_bastion_red":
    case "battlefront_bastion_yellow":
    case "battlefront_bastion_blue":
      AddCurrentTurnEffect($parameter, $player, "CC");
      IncrementClassState($player, $CS_DamagePrevention, 1);
      break;
    case "stone_rain_red":
      $banish = &GetBanish($player);
      $hand = &GetHand($player);
      for ($i = count($banish) - BanishPieces(); $i >= 0; $i -= BanishPieces()) {
        if ($banish[$i + 1] == "STONERAIN") {
          array_push($hand, $banish[$i]);
          RemoveBanish($player, $i);
        }
      }
      break;
    case "helm_of_halos_grace":
      Charge();
      AddDecisionQueue("ALLCARDPITCHORPASS", $player, "2", 1);
      AddDecisionQueue("DRAW", $player, "-", 1);
      break;
    case "bracers_of_bellonas_grace":
      Charge();
      AddDecisionQueue("ALLCARDPITCHORPASS", $player, "2", 1);
      AddDecisionQueue("PLAYAURA", $player, "courage", 1);
      break;
    case "warpath_of_winged_grace":
      Charge();
      AddDecisionQueue("ALLCARDPITCHORPASS", $player, "2", 1);
      AddDecisionQueue("PLAYAURA", $player, "quicken-1", 1); // Quicken
      break;
    case "arc_lightning_yellow":
      DealArcane(1, 2, "PLAYCARD", "arc_lightning_yellow");
      break;
    case "verdance_thorn_of_the_rose": case "verdance":
      if(GetCharacterGemState($player, $parameter) != 0) {
        AddDecisionQueue("YESNO", $player, "if you want " . CardLink($parameter, $parameter) . " to deal arcane damage");
        AddDecisionQueue("NOPASS", $player, "-");
      }
      AddDecisionQueue("VERDANCE", $player, $parameter, 1);
      break;
    case "barkskin_of_the_millennium_tree":
      if(SearchCount(SearchBanish($player, talent: "EARTH")) >= 4){
        PlayAura("embodiment_of_earth", $player);
      }
      break;
    case "felling_of_the_crown_red":
      Decompose($player, "FELLINGOFTHECROWN");
      break;
    case "plow_under_yellow":
      Decompose($player, "PLOWUNDER");
      break;
    case "summers_fall_red":
    case "summers_fall_yellow":
    case "summers_fall_blue":
      $params = explode("-", $target);
      $zone = substr($params[0], 0, 5);
      if($zone == "THEIR") {
        $index = SearchAurasForUniqueID(isset($params[1]) ? $params[1] : -1, $otherPlayer);
      }
      else {
        $index = SearchAurasForUniqueID(isset($params[1]) ? $params[1] : -1, $player);
        $zone = "MY";
      }
      if($target == "NONE") {
        Decompose($player, "SUMMERSFALL", $target);
      }
      elseif($index >= 0) {
        Decompose($player, "SUMMERSFALL", $zone . "AURAS-" . $index);
      }
      else {
        WriteLog(CardLink($parameter, $parameter) . " layer fails as there are no remaining targets for the targeted effect.");
      }
      break;
    case "rootbound_carapace_red":
    case "rootbound_carapace_yellow":
    case "rootbound_carapace_blue":
      Decompose($player, "ROOTBOUNDCARAPACE");
      break;
    case "blossoming_decay_red":
    case "blossoming_decay_yellow":
    case "blossoming_decay_blue":
      Decompose($player, "BLOSSOMINGDECAY");
      break;
    case "cadaverous_tilling_red":
    case "cadaverous_tilling_yellow":
    case "cadaverous_tilling_blue":
      Decompose($player, "CADAVEROUSTILLING");
      break;
    case "channel_the_millennium_tree_red":
      AddCurrentTurnEffect($parameter, $player);
      break;
    case "earths_embrace_blue":
      PlayAura("embodiment_of_earth", $player);
      if(GetClassState($player, $CS_NumEarthBanished) == 0) DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "harvest_season_red":
    case "harvest_season_yellow":
    case "harvest_season_blue":
      $numHealthPointsGained = match ($parameter) {"harvest_season_red" => 3, "harvest_season_yellow" => 2, "harvest_season_blue" => 1};
      DestroyAuraUniqueID($player, $uniqueID);
      GainHealth($numHealthPointsGained, $player);
      break;
    case "strong_yield_red":
    case "strong_yield_yellow":
    case "strong_yield_blue":
      DestroyAuraUniqueID($player, $uniqueID);
      AddCurrentTurnEffect($parameter, $player, "PLAY");
      break;
    case "flash_of_brilliance": //flash of brilliance
      $hand = SearchHand($player, talent: "LIGHTNING");
      if (count(explode(",", $hand)) > 0) {
        AddDecisionQueue("FINDINDICES", $player, "HAND", 1);
        AddDecisionQueue("SETDQCONTEXT", $player, "Choose a lightning card from your hand to discard.", 1);
        MZMoveCard($player, "MYHAND:talent=LIGHTNING", "MYDISCARD", may:true, isReveal:true, isSubsequent:true);
        AddDecisionQueue("SETDQCONTEXT", $player, "Return an Aura to your hand.", 1);
        MZMoveCard($player, "MYAURAS", "MYHAND", isReveal:true, isSubsequent:true);
      }
      break;
    case "gone_in_a_flash_red":
      CleanUpCombatEffects();
      AddPlayerHand($combatChain[0], $mainPlayer, "CC");
      $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "-";
      if (SearchLayersForPhase("FINALIZECHAINLINK") == -1) {
        //only close the chain if removed before the resolution step
        CloseCombatChain(false);
      }
      break;
    case "channel_lightning_valley_yellow":
      WriteLog(CardLink($parameter, $parameter) . " draws a card");
      Draw($player);
      break;
    case "blast_to_oblivion_red":
    case "blast_to_oblivion_yellow":
    case "blast_to_oblivion_blue":
      MZBounce($player, $target);
      break;
    case "electromagnetic_somersault_red":
    case "electromagnetic_somersault_yellow":
    case "electromagnetic_somersault_blue":
      $prevLink = $chainLinks[count($chainLinks) - 1];
      $indices = array();
      $index = -1;
      for ($i = 0; $i < count($prevLink); $i += ChainLinksPieces()) {
        if ($target == $prevLink[$i+7] && $prevLink[$i+2] == 1) {
          array_push($indices, $i);
        }
      }
      if (count($indices) == 1) {
        $index = $indices[0];
      }
      else if (count($indices) > 1) { //if there are two copies of the same card on the link, assume the player chose their own card
        // fix later
        foreach ($indices as $i) {
          if ($prevLink[$i + 1] == $player) $index = $i; 
        }
        if ($index == -1) $index = $indices[0];
      }
      if ($index != -1)
      {
        AddPlayerHand($target, $prevLink[$index + 1], "CC");
        $chainLinks[count($chainLinks) - 1][$index + 2] = 0;
      }
      break;
    case "face_purgatory":
      PummelHit($otherPlayer);
      Draw($player);
      break;
    case "malefic_incantation_red":
    case "malefic_incantation_yellow":
    case "malefic_incantation_blue":
      // this is the only place that it will destroy if there are no counters. may need to refactor if anything ever can remove counters arbitrarly.
      $index = SearchAurasForUniqueID($uniqueID, $player);
      if ($index == -1) break;
      $auras = &GetAuras($player);
      --$auras[$index + 2];
      PlayAura("runechant", $player);
      if ($auras[$index + 2] == 0) DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "arcane_cussing_red":
    case "arcane_cussing_yellow":
    case "arcane_cussing_blue":
      $numRunechantsCreated = match ($parameter) {"arcane_cussing_red" => 3, "arcane_cussing_yellow" => 2, "arcane_cussing_blue" => 1};
      PlayAura("runechant", $player, $numRunechantsCreated);
      break;
    case "aether_bindings_of_the_third_age":
      WriteLog(CardLink("aether_bindings_of_the_third_age", "aether_bindings_of_the_third_age") . " is amping 1");
      AddCurrentTurnEffect("aether_bindings_of_the_third_age-AMP", $player);//amp for aether bindings
    case "sigil_of_aether_blue"://Sigil of aether
      if($additionalCosts == "Arcane") DealArcane(1, 2, "STATIC", "sigil_of_aether_blue", false, $player);
      else DestroyAuraUniqueID($player, $uniqueID); //destroy sigils at start of action phase
      break;
    case "truce_blue":
      if($target == "truce_blue-1") {
      WriteLog("Congrats! You didn't kill each other!🤝");
      DestroyAuraUniqueID($defPlayer, $uniqueID);
      GainHealth(3, $defPlayer);
      GainHealth(3, $mainPlayer);
      }
      else {
        DestroyAuraUniqueID($defPlayer, $uniqueID);
        Draw($defPlayer, false);
      }
      break;
    case "hard_knuckle":
      $index = FindCharacterIndex($mainPlayer, "hard_knuckle");
      AddDecisionQueue("YESNO", $mainPlayer, "to_destroy_".Cardlink($parameter, $parameter));
      AddDecisionQueue("NOPASS", $mainPlayer, "-");
      AddDecisionQueue("PASSPARAMETER", $mainPlayer, $index, 1);
      AddDecisionQueue("DESTROYCHARACTER", $mainPlayer, "-", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $mainPlayer, "hard_knuckle", 1);
      break;
    case "spark_spray_yellow":
    case "spark_spray_blue":
      AddDecisionQueue("ADDCURRENTEFFECT", $mainPlayer, $parameter, 1);
      break;
    case "heavy_industry_surveillance":
      AddDecisionQueue("DECKCARDS", $defPlayer, "0");
      AddDecisionQueue("YESNO", $defPlayer, "if_you_want_to_banish_the_top_card_of_your_deck_with_" . CardLink($parameter, $parameter), 1);
      AddDecisionQueue("NOPASS", $defPlayer, "-", 1);
      AddDecisionQueue("PARAMDELIMTOARRAY", $defPlayer, "0", 1);
      AddDecisionQueue("MULTIREMOVEDECK", $defPlayer, "0", 1);
      AddDecisionQueue("MULTIBANISH", $defPlayer, "DECK,-", 1);
      AddDecisionQueue("SETDQVAR", $defPlayer, "0", 1);
      AddDecisionQueue("WRITELOG", $defPlayer, "<0> was banished.", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $defPlayer, "heavy_industry_surveillance", 1);
      break;
    case "heavy_industry_ram_stop":
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose how much to pay for " . CardLink($parameter, $parameter));
      AddDecisionQueue("BUTTONINPUT", $player, "0,1");
      AddDecisionQueue("PAYRESOURCES", $player, "<-", 1);
      AddDecisionQueue("LESSTHANPASS", $player, "1", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $player, $parameter, 1);
      break;
    case "brutus_summa_rudis":
      AddDecisionQueue("MULTIZONEINDICES", $player, "MYCHAR:type=C&THEIRCHAR:type=C", 1);
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose which hero win the clash", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
      AddDecisionQueue("BRUTUS", $player, $target, 1);
      break;
    case "jarl_vetreidi":
      FrostBiteExposed($otherPlayer, $player);
      break;
    case "unforgetting_unforgiving_red":
      if(!IsAllyAttacking() && SearchCharacter($otherPlayer, hasNegCounters: true) != "") {
        $search = "MYDECK:cardID=mangle_red";
        $fromMod = "Deck,NT"; //pull it out of the deck, playable "Next Turn"
        AddDecisionQueue("YESNO", $player, "if_you_want_to_search_for_a_".CardLink("mangle_red", "mangle_red") ."_and_banish_it");
        AddDecisionQueue("NOPASS", $player, "-");
        AddDecisionQueue("MULTIZONEINDICES", $player, $search, 1);
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
        AddDecisionQueue("MZBANISH", $player, $fromMod, 1);
        AddDecisionQueue("MZREMOVE", $player, "-", 1);
        AddDecisionQueue("SHUFFLEDECK", $player, "-", 1);
      }
      break;
    case "crumble_to_eternity_blue": // Pop Crumble to Eternity and add Dominate to the next attack.
      AddCurrentTurnEffect($parameter, $player);
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "ollin_ice_cap":
      //check how this works with allies
      PlayAura("frostbite", $player, effectController: $defPlayer);
      break;
    case "tectonic_crust":
      PlayAura("seismic_surge", $defPlayer, effectController: $defPlayer);
      break;
    case "rootbound_trunks":
      PlayAura("embodiment_of_earth", $defPlayer, effectController: $defPlayer);
      break;
    case "schism_of_chaos_blue":
      AddDecisionQueue("SHUFFLEDECK", $mainPlayer, "-");
      AddDecisionQueue("ADDARSENALFROMDECK", $mainPlayer, "-");
      AddDecisionQueue("SHUFFLEDECK", $defPlayer, "-");
      AddDecisionQueue("ADDARSENALFROMDECK", $defPlayer, "-");
      break;
    case "mask_of_deceit":
      $char = &GetPlayerCharacter($player);
      if (CheckMarked($mainPlayer)) AddDecisionQueue("CHOOSECARD", $player, "arakni_black_widow,arakni_funnel_web,arakni_orbweaver,arakni_redback,arakni_tarantula,arakni_trapdoor");
      else AddDecisionQueue("PASSPARAMETER", $player, -1);
      AddDecisionQueue("CHAOSTRANSFORM", $player, $char[0], 1);
      break;
    case "hunted_or_hunter_red":
      WriteLog("The Hunter has become the hunted");
      LoseHealth(1, $mainPlayer);
      if (!IsAllyAttacking()) TrapTriggered($parameter);
      break;
    case "blood_runs_deep_red":
      ThrowWeapon("Dagger", "blood_runs_deep_red");
      ThrowWeapon("Dagger", "blood_runs_deep_red");
      break;
    case "ignite_red":
      AddCurrentTurnEffect("ignite_red", $player);
      break;
    case "prowess_of_agility_blue":
      $index = SearchAurasForUniqueID($uniqueID, $player);
      AddDecisionQueue("YESNO", $player, "if_you_want_to_destroy_".Cardlink("prowess_of_agility_blue","prowess_of_agility_blue")."_and_draw");
      AddDecisionQueue("NOPASS", $player, "-");
      AddDecisionQueue("PASSPARAMETER", $player, "$index", 1);
      AddDecisionQueue("PREPENDLASTRESULT", $player, "MYAURAS-", 1);
      AddDecisionQueue("MZDESTROY", $player, "-", 1);
      AddDecisionQueue("DRAW", $player, "-", 1);
    case "kabuto_of_imperial_authority":
      AddCurrentTurnEffect($parameter, $mainPlayer);
      break;
    case "sharpened_senses_yellow":
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "smoke_out_red":
      MarkHero($mainPlayer);
      break;
    case "fealty":
      DestroyAuraUniqueID($player, $uniqueID);
      break;
    case "leap_frog_vocal_sac":
    case "leap_frog_slime_skin":
    case "leap_frog_gloves":
    case "leap_frog_leggings":
      AddDecisionQueue("YESNO", $player, "if_you_want_to_add_".Cardlink($parameter,$parameter)."_to_active_chain_link");
      AddDecisionQueue("NOPASS", $player, "-", 1);
      AddDecisionQueue("LEAPFROG", $player, $parameter, 1);
      break;
    case "lair_of_the_spider_red":
    case "den_of_the_spider_red":
      WriteLog("The Hunter stumbles into the spider");
      MarkHero($mainPlayer);
      if (!IsAllyAttacking()) TrapTriggered($parameter);
      break;
    case "thick_hide_hunter_yellow":
      DiscardRandom();
      break;
    case "chain_reaction_yellow":
      $arsenal = &GetArsenal($player);
      for ($i = 0; $i < count($arsenal); $i += ArsenalPieces()) {
        if (CardType($arsenal[$i]) == "A" && $arsenal[$i + 1] == "DOWN"){
          AddDecisionQueue("YESNO", $player, "if_you_want_to_turn_your_arsenal_face_up");
          AddDecisionQueue("NOPASS", $player, "-");
          AddDecisionQueue("TURNARSENALFACEUP", $player, $i, 1);
          AddDecisionQueue("PASSPARAMETER", $player, $arsenal[$i + 5], 1);
          AddDecisionQueue("CHAINREACTION", $player, "-", 1);
        }
      }
      if (!IsAllyAttacking()) TrapTriggered($parameter);
      break;
    case "douse_in_runeblood_red":
      $startingRunechants = CountAura("runechant", $player);
      PlayAura("runechant", $player, GetClassState($player, $CS_NumNonAttackCards), isToken:true);
      if (CountAura("runechant", $player) - $startingRunechants >= 3) GiveAttackGoAgain();
      break;
    case "ring_of_roses_yellow":
      GainHealth(1, $player);
      break;
    default:
      break;
  }
}

function GetDQHelpText()
{
  global $dqState;
  if (count($dqState) < 5) return "-";
  return $dqState[4];
}

function FinalizeAction()
{
  global $currentPlayer, $mainPlayer, $actionPoints, $turn, $combatChain, $defPlayer, $makeBlockBackup, $mainPlayerGamestateStillBuilt;
  BuildMainPlayerGamestate();
  if ($turn[0] == "M") {
    if (count($combatChain) > 0) //Means we initiated a chain link
    {
      $turn[0] = "B";
      $currentPlayer = $defPlayer;
      $turn[2] = "";
      $makeBlockBackup = 1;
    } else {
      if ($actionPoints > 0 || ShouldHoldPriority($mainPlayer)) {
        $turn[0] = "M";
        $currentPlayer = $mainPlayer;
        $turn[2] = "";
      } else {
        $currentPlayer = $mainPlayer;
        BeginTurnPass();
      }
    }
  } else if ($turn[0] == "A") {
    $currentPlayer = $mainPlayer;
    $turn[2] = "";
  } else if ($turn[0] == "D") {
    $turn[0] = "A";
    $currentPlayer = $mainPlayer;
    $turn[2] = "";
  } else if ($turn[0] == "B") {
    $turn[0] = "B";
  }
  return 0;
}

function IsReactionPhase()
{
  global $turn, $dqState;
  if ($turn[0] == "A" || $turn[0] == "D") return true;
  if (count($dqState) >= 2 && ($dqState[1] == "A" || $dqState[1] == "D")) return true;
  return false;
}

//Return whether priority should be held for the player by default/settings
function ShouldHoldPriority($player, $layerCard = "")
{
  global $mainPlayer;
  $prioritySetting = HoldPrioritySetting($player);
  if ($prioritySetting == 0 || $prioritySetting == 1) return 1;
  if (($prioritySetting == 2 || $prioritySetting == 3) && $player != $mainPlayer) return 1;
  return 0;
}

function GiveAttackGoAgain()
{
  global $combatChainState, $CCS_CurrentAttackGainedGoAgain;
  $combatChainState[$CCS_CurrentAttackGainedGoAgain] = 1;
}

function TopDeckToArsenal($player)
{
  $deck = new Deck($player);
  if (ArsenalFull($player) || $deck->Empty()) return;
  AddArsenal($deck->Top(remove: true), $player, "DECK", "DOWN");
}

function DiscardHand($player, $mainPhase = true)
{
  $hand = &GetHand($player);
  for ($i = count($hand) - HandPieces(); $i >= 0; $i -= HandPieces()) {
    DiscardCard($player, $i, mainPhase: $mainPhase);
  }
}

function Opt($cardID, $amount)
{
  global $currentPlayer;
  PlayerOpt($currentPlayer, $amount);
}

function PlayerOpt($player, $amount, $optKeyword = true)
{
  $char = GetPlayerCharacter($player);
  $heroID = ShiyanaCharacter($char[0]);
  $heroStatus = $char[1];
  AddDecisionQueue("FINDINDICES", $player, "DECKTOPXREMOVE," . $amount);
  AddDecisionQueue("OPT", $player, "<-", 1);
  if ($heroID == "blaze_firemind" && $heroStatus < 3) AddDecisionQueue("BLAZE", $player, $amount, 1);
}

function BanishRandom($player, $source)
{
  $hand = &GetHand($player);
  if (count($hand) == 0) return "";
  $index = GetRandom() % count($hand);
  $banished = $hand[$index];
  unset($hand[$index]);
  $hand = array_values($hand);
  BanishCardForPlayer($banished, $player, "HAND");
  return $banished;
}

function DiscardRandom($player = "", $source = "", $effectController = "")
{
  global $currentPlayer;
  if ($player == "") $player = $currentPlayer;
  if ($effectController == "") $effectController = $currentPlayer;
  $hand = &GetHand($player);
  if (count($hand) == 0) return "";
  if (count($hand) > 1) $index = GetRandom(0, count($hand) - 1);
  else $index = 0;
  $discarded = $hand[$index];
  unset($hand[$index]);
  $hand = array_values($hand);
  CardDiscarded($player, $discarded, $source);
  AddGraveyard($discarded, $player, "HAND", $effectController);
  DiscardedAtRandomEffects($player, $discarded, $source);
  return $discarded;
}

function DiscardedAtRandomEffects($player, $discarded, $source)
{
  global $mainPlayer;
  if (SearchCurrentTurnEffects("berserk_yellow", $player) && ModifiedAttackValue($discarded, $player, "GY", "HAND") >= 6) {
    $index = SearchGetFirstIndex(SearchMultizone($player, "MYDISCARD:cardID=" . $discarded));
    RemoveGraveyard($player, $index);
    BanishCardForPlayer($discarded, $player, "GY", "-", $player);
    AddLayer("TRIGGER", $player, "berserk_yellow");
  }
  $character = GetPlayerCharacter($player);
  $index = FindCharacterIndex($player, "beaten_trackers");
  if ($index >= 0 && IsCharacterAbilityActive($player, $index, checkGem: true) && $player == $mainPlayer && ModifiedAttackValue($discarded, $player, "GY", "HAND") >= 6) {
    AddLayer("TRIGGER", $player, $character[$index]);
  }
  $index = FindCharacterIndex($player, "hide_tanner");
  if ($index >= 0 && IsCharacterAbilityActive($player, $index, checkGem: true) && $player == $mainPlayer && ModifiedAttackValue($discarded, $player, "GY", "HAND") >= 6) {
    AddLayer("TRIGGER", $player, $character[$index]);
  }
  switch ($discarded) {
    case "skull_crack_red":
      AddLayer("TRIGGER", $player, $discarded);
      break;
    case "reincarnate_red":
    case "reincarnate_yellow":
    case "reincarnate_blue":
      AddLayer("TRIGGER", $player, $discarded);
      break;
    default:
      break;
  }
}

function DiscardCard($player, $index, $source = "", $effectController = "", $mainPhase = true)
{
  $hand = &GetHand($player);
  $discarded = RemoveHand($player, $index);
  CardDiscarded($player, $discarded, $source, mainPhase: $mainPhase);
  AddGraveyard($discarded, $player, "HAND", $effectController);
  return $discarded;
}

function CardDiscarded($player, $discarded, $source = "", $mainPhase = true)
{
  global $CS_Num6PowDisc, $mainPlayer, $layers;
  AddEvent("DISCARD", $discarded);
  $modifiedAttack = ModifiedAttackValue($discarded, $player, "HAND", $source);
  if ($modifiedAttack >= 6) {
    $character = &GetPlayerCharacter($player);
    $characterID = ShiyanaCharacter($character[0]);
    if (($characterID == "rhinar_reckless_rampage" || $characterID == "rhinar" || $characterID == "rhinar") && $character[1] == 2 && $player == $mainPlayer && $mainPhase) { //Rhinar
      AddLayer("TRIGGER", $mainPlayer, $character[0]);
    } else if (($characterID == "kayo_armed_and_dangerous" || $characterID == "kayo") && $character[1] == 2 && $player == $mainPlayer && $mainPhase) { //Kayo, Armed and Dangerous
      AddLayer("TRIGGER", $mainPlayer, $character[0]);
      $character[1] = 1;
    }
    IncrementClassState($player, $CS_Num6PowDisc);
  }
  if ($discarded == "massacre_red" && $source != "" && ClassContains($source, "BRUTE", $mainPlayer) && CardType($source) == "AA") {
    WriteLog(CardLink("massacre_red", "massacre_red") . " intimidated because it was discarded by a Brute attack action card.");
    AddLayer("TRIGGER", $mainPlayer, $discarded);
  }
  WriteLog(CardLink($discarded, $discarded) . " was discarded");
}

function ModifiedAttackValue($cardID, $player, $from, $source = "")
{
  global $CS_Num6PowBan;
  if ($cardID == "") return 0;
  $attack = AttackValue($cardID);
  if ($cardID == "mutated_mass_blue") return SearchPitchForNumCosts($player) * 2;
  else if ($cardID == "fractal_replication_red") return FractalReplicationStats("Attack");
  else if ($cardID == "spectral_procession_red") return CountAura("spectral_shield", $player);
  else if ($cardID == "diabolic_offering_blue") return GetClassState($player, $CS_Num6PowBan) > 0 ? 6 : 0;
  else if ($cardID == "nitro_mechanoidb") return SearchCurrentTurnEffects("galvanic_bender-UNDER", $player) > 0 ? 6 : 5;
  if ($from != "CC") {
    $char = &GetPlayerCharacter($player);
    $characterID = ShiyanaCharacter($char[0]);
    if (($characterID == "kayo_armed_and_dangerous" || $characterID == "kayo") && $char[1] < 3 && CardType($cardID) == "AA") ++$attack;
  } else {
    // effect that only affect CC
    $attack += EffectDefenderAttackModifiers($cardID);
  }
  $attack += ItemsAttackModifiers($cardID, $player, $from);
  return $attack;
}

function Intimidate($player = "")
{
  global $currentPlayer, $defPlayer, $CS_HaveIntimidated;
  IncrementClassState($currentPlayer, $CS_HaveIntimidated);
  if (!ShouldAutotargetOpponent($currentPlayer)) {
    AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:type=C&THEIRCHAR:type=C", 1);
    AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose hero to intimidate.", 1);
    AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
    AddDecisionQueue("INTIMIDATE", $currentPlayer, "-", 1);
    return;
  }

  if ($player != "") AddDecisionQueue("INTIMIDATE", $currentPlayer, $player, 1);
  else AddDecisionQueue("INTIMIDATE", $currentPlayer, $defPlayer, 1);
}

function GamblersGlovesReroll($player, $target){
  $gamblersGlovesIndex = FindCharacterIndex($player, "gamblers_gloves");
  AddDecisionQueue("YESNO", $player, "if_you_want_to_destroy_".Cardlink("gamblers_gloves", "gamblers_gloves")."_to_reroll_the_result");
  AddDecisionQueue("NOPASS", $player, "-");
  AddDecisionQueue("PASSPARAMETER", $player, $gamblersGlovesIndex, 1);
  AddDecisionQueue("DESTROYCHARACTER", $player, "-", 1);
  AddDecisionQueue("REROLLDIE", $target, "1", 1);
}

function DestroyFrozenArsenal($player)
{
  $arsenal = &GetArsenal($player);
  $otherPlayer = $player == 1 ? 2 : 1;
  for ($i = 0; $i < count($arsenal); $i += ArsenalPieces()) {
    if ($arsenal[$i + 4] == "1") {
      DestroyArsenal($player, effectController: $otherPlayer);
    }
  }
}

function CanGainAttack($cardID)
{
  global $combatChain, $mainPlayer;
  if (SearchCurrentTurnEffects("buzzsaw_trap_blue", $mainPlayer)) return false;
  return !SearchCurrentTurnEffects("chokeslam_red", $mainPlayer) || CardType($combatChain[0]) != "AA";
}

function IsWeaponGreaterThanTwiceBasePower()
{
  global $combatChain, $mainPlayer, $CS_NumCharged, $CS_NumYellowPutSoul;
  if (count($combatChain) == 0) return false;
  if (TypeContains($combatChain[0], "W", $mainPlayer) && CachedTotalAttack() > (AttackValue($combatChain[0]) * 2)) return true;
  $char = &GetPlayerCharacter($mainPlayer);
  if ($char[CharacterPieces()] == "raydn_duskbane" && GetClassState($mainPlayer, $CS_NumCharged) > 0) return true;
  if ($char[CharacterPieces()] == "beaming_blade" && GetClassState($mainPlayer, $CS_NumYellowPutSoul) > 0) return true;
  return false;
}

function HasEnergyCounters($array, $index)
{
  switch ($array[$index]) {
    case "fyendals_spring_tunic":
    case "alluvion_constellas":
    case "blaze_firemind":
      return $array[$index + 2] > 0;
    default:
      return false;
  }
}

function HasAttackCounters($zone, $array, $index)
{
  switch ($zone) {
    case "AURAS":
      return $array[$index + 3] > 0;
    default:
      return false;
  }
}

function IsEnergyCounters($cardID)
{
  switch ($cardID) {
    case "fyendals_spring_tunic":
    case "alluvion_constellas":
    case "blaze_firemind":
      return true;
    default:
      return false;
  }
}

function HasHauntCounters($cardID)
{
  switch ($cardID) {
    case "ghostly_touch":
      return true;
    default:
      return false;
  }
}

function HasVerseCounters($cardID)
{
  switch ($cardID) {
    case "runeblood_incantation_red":
    case "runeblood_incantation_yellow":
    case "runeblood_incantation_blue":
    case "malefic_incantation_red":
    case "malefic_incantation_yellow":
    case "malefic_incantation_blue":
      return true;
    default:
      return false;
  }
}

function HasDoomCounters($cardID)
{
  switch ($cardID) {
    case "looming_doom_blue":
    case "chains_of_mephetis_blue":
      return true;
    default:
      return false;
  }
}

function HasRustCounters($cardID)
{
  switch ($cardID) {
    case "talishar_the_lost_prince":
      return true;
    default:
      return false;
  }
}

function HasFlowCounters($cardID)
{
  switch ($cardID) {
    case "channel_mount_heroic_red":
    case "channel_lake_frigid_blue":
    case "channel_thunder_steppe_yellow":
    case "channel_the_bleak_expanse_blue":
    case "channel_the_millennium_tree_red":
    case "channel_lightning_valley_yellow":
    case "channel_mount_isen_blue":
      return true;
    default:
      return false;
  }
}

function HasFrostCounters($cardID)
{
  switch ($cardID) {
    case "insidious_chill_blue":
      return true;
    default:
      return false;
  }
}

function HasBalanceCounters($cardID)
{
  switch ($cardID) {
    case "zen_state":
      return true;
    default:
      return false;
  }
}

function HasBindCounters($cardID)
{
  switch ($cardID) {
    case "spellbound_creepers":
      return true;
    default:
      return false;
  }
}

function HasStainCounters($cardID)
{
  switch ($cardID) {
    case "blood_splattered_vest":
      return true;
    default:
      return false;
  }
}

function HasSteamCounter($array, $index, $player)
{
  if (CardType($array[$index]) == 'E') return EquipmentsUsingSteamCounter($array[$index]);
  if (ClassContains($array[$index], "MECHANOLOGIST", $player)) {
    if ($array[$index] == "nitro_mechanoida") return false;
    if (CardType($array[$index]) == 'W') return $array[$index + 2] > 0;
    if (SubtypeContains($array[$index], "Item", $player)) return $array[$index + 1] > 0;
  }
  return false;
}

function ProcessMeld($player, $parameter, $additionalCosts="")
{
  // handles running the left side of meld cards
  global $CS_ArcaneDamageDealt, $CS_HealthGained, $CS_AdditionalCosts;
  switch ($parameter) {
    case "thistle_bloom__life_yellow":
      PlayAura("runechant", $player, GetClassState($player, $CS_HealthGained));
      break;
    case "arcane_seeds__life_red":
      PlayAura("runechant", $player);
      PlayAura("runechant", $player);
      break;
    case "vaporize__shock_yellow":
      $arcaneDamageDealt = GetClassState($player, $CS_ArcaneDamageDealt);
      AddDecisionQueue("MULTIZONEINDICES", $player, "THEIRAURAS:minCost=0;maxCost=" . $arcaneDamageDealt . "&MYAURAS:minCost=0;maxCost=" . $arcaneDamageDealt, 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
      AddDecisionQueue("MZDESTROY", $player, "-", 1);
      for($i=0; $i<$arcaneDamageDealt; ++$i) {
        AddDecisionQueue("MULTIZONEINDICES", $player, "THEIRAURAS:type=T&MYAURAS:type=T");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
        AddDecisionQueue("MZDESTROY", $player, "-", 1);
      }
      break;
    case "burn_up__shock_red":
      AddCurrentTurnEffect($parameter, $player);
      break;
    case "rampant_growth__life_yellow":
      $ampAmount = GetClassState($player, $CS_HealthGained);
      AddCurrentTurnEffect($parameter . "," . $ampAmount, $player, "ABILITY");
      WriteLog(CardLink($parameter, $parameter) . " is amping " . $ampAmount);
      break;
    case "pulsing_aether__life_red":
      $meldState = (GetClassState($player, $CS_AdditionalCosts) == "Both") ? "I,A" : "A";
      DealArcane(4, 2, "PLAYCARD", $parameter, player:$player, meldState:$meldState);
      break;
    case "null__shock_yellow":
      if (GetClassState($player, $CS_ArcaneDamageDealt) > 0) {
        AddDecisionQueue("MULTIZONEINDICES", $player, "LAYER:type=I;minCost=0;maxCost=".GetClassState($player, $CS_ArcaneDamageDealt)-1);
        AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
        AddDecisionQueue("NEGATE", $player, "<-", 1);
      }
      break;
    case "comet_storm__shock_red":
      $meldState = (GetClassState($player, $CS_AdditionalCosts) == "Both") ? "I,A" : "A";
      DealArcane(5, 2, "PLAYCARD", $parameter, player:$player, meldState: $meldState);
      break;
    case "regrowth__shock_blue":
      if (GetClassState($player, $CS_ArcaneDamageDealt) > 0) {
        MZMoveCard($player, "MYDISCARD:type=AA;minCost=0;maxCost=" . GetClassState($player, $CS_ArcaneDamageDealt)-1, "MYHAND", DQContext: "Choose an attack action card with cost less than " . GetClassState($player, $CS_ArcaneDamageDealt)-1);
      }
      break;
    default:
      break;
  }
  ResolveGoAgain($parameter, $player, "MELD", additionalCosts: $additionalCosts);
  if(GetClassState($player, $CS_AdditionalCosts) == "Both" || $additionalCosts == "MELD") ResolveGoesWhere("GY", $parameter, $player, "MELD"); //Only needs to be handled specifically here when playing both side of a Meld card
}
