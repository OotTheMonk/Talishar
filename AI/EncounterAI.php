<?php

include "EncounterPriorityValues.php";
include "EncounterPriorityLogic.php";
include "EncounterPlayLogic.php";
include_once "PlayerMacros.php";

function EncounterAI()
{
  global $currentPlayer, $p2CharEquip, $decisionQueue, $mainPlayer, $mainPlayerGamestateStillBuilt, $actionPoints;
  $AIDebug = false;
  //$AIDebug = true;
  $currentPlayerIsAI = ($currentPlayer == 2 && IsEncounterAI($p2CharEquip[0])) ? true : false;
  if(!IsGameOver() && $currentPlayerIsAI)
  {
    $isBowActive = false;
    for($logicCount=0; $logicCount<=30 && $currentPlayerIsAI; ++$logicCount)
    {
      global $turn;
      FixHand($currentPlayer);
      $hand = &GetHand($currentPlayer);
      $character = &GetPlayerCharacter($currentPlayer);
      $arsenal = &GetArsenal($currentPlayer);
      $resources = &GetResources($currentPlayer);
      $items = &GetItems($currentPlayer);
      $allies = &GetAllies($currentPlayer);
      $banish = &GetBanish($currentPlayer);
      //LogHandArray($hand);
      CacheCombatResult();
      if(count($decisionQueue) > 0)
      {
        if($AIDebug) WriteLog("AI Branch - Decision Queue");
        global $EffectContext;
        if($EffectContext == "bloodrot_pox")
        {
          if($AIDebug) WriteLog("AI Branch - Bloodrot");
          ContinueDecisionQueue("NO");
          continue;
        }
        if($decisionQueue[0] == "SHIVER")
        {
          if($AIDebug) WriteLog("AI Branch - Shiver");
          $options = explode(",", $turn[2]);
          ContinueDecisionQueue($options[1]);
        }
        if($isBowActive)//was the last action a bow action?
        {
          if($AIDebug) WriteLog("AI Branch - Bow Active");
          $optionIndex = 0;
          $index = 0;
          $largestIndex = 0;

          for($i = 0; $i < count($hand); ++$i)//find the highest priority arrow and choose it
          {
            if(CardSubtype($hand[0]) == "Arrow")
            {
              if(GetPriority($hand[$largestIndex], $character[0], 2) <= GetPriority($hand[$i], $character[0], 2))
              {
                $largestIndex = $i;
                $optionIndex = $index;
              }
              ++$index;
            }
          }
          $options = explode(",", $turn[2]);
          ContinueDecisionQueue($options[$optionIndex]);
        }
        else if($turn[0] == "INPUTCARDNAME")
        {
          if($AIDebug) WriteLog("AI Branch - Input Arcane");
          ProcessInput($currentPlayer, 30, "-", 0, 0, "-", false, "Crouching Tiger");
        }
        else
        {
          if($AIDebug) WriteLog("AI Branch - DQ First Option");
          $options = explode(",", $turn[2]);
          $choice = $options[0];
          //Some things automatically adjust the data
          if($turn[0] == "CHOOSEDECK" || $turn[0] == "MAYCHOOSEDECK") {
            $deck = &GetDeck($currentPlayer);
            $choice = $deck[$choice*DeckPieces()];
          }
          ContinueDecisionQueue($choice);//Just pick the first option
        }
      }
      else if($turn[0] == "B")//The player is attacking the AI
      {
        if($AIDebug) WriteLog("AI Branch - Block");
        $priortyArray = GeneratePriorityValues($hand, $character, $arsenal, $items, $allies, $banish, "Block"); //Generate the priority values array. Found in EncounterPriorityLogic.php
        //LogPriorityArray($priortyArray);
        $found = false;
        while (count($priortyArray) > 0 && !$found) { //Grabs items from the array until it finds one it can play.
          $storedPriorityNode = $priortyArray[count($priortyArray)-1];
          array_pop($priortyArray); //grabs the last item in the array (highest priority), and removes it from the array, storing it in $storedPriorityNode
          //WriteLog("CardID=" . $storedPriorityNode[0] . ", Where=" . $storedPriorityNode[1] . ", Index=" . $storedPriorityNode[2] . ", Priority=" . $storedPriorityNode[3]);
          if(CardIsBlockable($storedPriorityNode)) $found = true; //If the card can be played/blocked with/activated. Found in EncounterPlayLogic.php
        }
        //WriteLog("CardID=" . $storedPriorityNode[0] . ", Where=" . $storedPriorityNode[1] . ", Index=" . $storedPriorityNode[2] . ", Priority=" . $storedPriorityNode[3]);
        $health = &GetHealth($currentPlayer);
        //If something was found, that thing is able to block (not prio 0), and either the attack is lethal or the AI wants to block with it efficiently, it attempts to block. Otherwise it passes.
        //WriteLog("found->".$found.",prio->".$storedPriorityNode[3].",cachedattack->".CachedTotalAttack().",cachedblock->".CachedTotalBlock().",health->".$health.",attackvalue->".AttackValue("brand_with_cinderclaw_yellow"));
        if(ShouldBlock($found, $storedPriorityNode))
        {
          BlockCardAttempt($storedPriorityNode); //attempts to play the card. Found in EncounterPlayLogic.php;
        }
        else
        {
          PassInput();
        }
      }
      else if($turn[0] == "M" && $mainPlayer == $currentPlayer && $actionPoints > 0)//AIs turn
      {
        if($AIDebug) WriteLog("AI Branch - AI's Turn");
        $priortyArray = GeneratePriorityValues($hand, $character, $arsenal, $items, $allies, $banish, "Action");
        //LogPriorityArray($priortyArray);
        $found = false;
        while (count($priortyArray) > 0 && !$found) {
          $storedPriorityNode = $priortyArray[count($priortyArray)-1];
          array_pop($priortyArray);
          //WriteLog("CardID=" . $storedPriorityNode[0] . ", Where=" . $storedPriorityNode[1] . ", Index=" . $storedPriorityNode[2] . ", Priority=" . $storedPriorityNode[3]);
          if(CardIsPlayable($storedPriorityNode, $hand, $resources))
          {
            //Only attempt to play the card if you have excess resources compared to what needs to be saved
            if($storedPriorityNode[0] != "Hand" || count($hand) > 1 || ResourcesNeededToSave($character[0]) >= ($resources[0] - CardCost($storedPriorityNode[0])))
            {
              //WriteLog("found " . $storedPriorityNode[0]);
              $found = true;
            }
          }
        }
        if($found == true && $storedPriorityNode[3] != 0)
        {
          if(CardSubtype($storedPriorityNode[0]) == "Bow" ) $isBowActive = true;
          PlayCardAttempt($storedPriorityNode);
          CacheCombatResult();
        }
        else
        {
          PassInput();
        }
      }
      else if($turn[0] == "A" && $mainPlayer == $currentPlayer)//attack reaction phase
      {
        if($AIDebug) WriteLog("AI Branch - Attack Reactions");
        $priortyArray = GeneratePriorityValues($hand, $character, $arsenal, $items, $allies, $banish, "Reaction");
        //LogPriorityArray($priortyArray);
        $found = false;
        while (count($priortyArray) > 0 && !$found) {
          $storedPriorityNode = $priortyArray[count($priortyArray)-1];
          array_pop($priortyArray);
          if(ReactionCardIsPlayable($storedPriorityNode, $hand, $resources)) $found = true;
          //WriteLog("CardID=" . $storedPriorityNode[0] . ", Where=" . $storedPriorityNode[1] . ", Index=" . $storedPriorityNode[2] . ", Priority=" . $storedPriorityNode[3] . ", Found=" . $found);
        }
        if($found == true && $storedPriorityNode[3] != 0)
        {
          PlayCardAttempt($storedPriorityNode);
          CacheCombatResult();
        }
        else
        {
          PassInput();
        }
      }
      else if($turn[0] == "D" && $mainPlayer != $currentPlayer)//Defense reaction phase
      {
        if($AIDebug) WriteLog("AI Branch - Defense Reactions");
        $priortyArray = GeneratePriorityValues($hand, $character, $arsenal, $items, $allies, $banish, "Reaction");
        //LogPriorityArray($priortyArray);
        $found = false;
        while (count($priortyArray) > 0 && !$found) {
          $storedPriorityNode = $priortyArray[count($priortyArray)-1];
          array_pop($priortyArray);
          if(ReactionCardIsPlayable($storedPriorityNode, $hand, $resources)) $found = true;
          //WriteLog("CardID=" . $storedPriorityNode[0] . ", Where=" . $storedPriorityNode[1] . ", Index=" . $storedPriorityNode[2] . ", Priority=" . $storedPriorityNode[3] . ", Found=" . $found);
        }
        $threatened = CachedTotalAttack() - CachedTotalBlock();
        //if($found == true && $threatened > 0 && $storedPriorityNode[3] != 0)
        if($found == true && ShouldBlock($found, $storedPriorityNode) && $storedPriorityNode[3] != 0)
        {
          PlayCardAttempt($storedPriorityNode);
          CacheCombatResult();
        }
        else
        {
          PassInput();
        }
      }
      else if($turn[0] == "P" && $mainPlayer == $currentPlayer)//pitch phase
      {
        if($AIDebug) WriteLog("AI Branch - Pitch");
        $priortyArray = GeneratePriorityValues($hand, $character, $arsenal, $items, $allies, $banish, "Pitch");
        //LogPriorityArray($priortyArray);
        $found = false;
        while (count($priortyArray) > 0 && !$found) {
          $storedPriorityNode = $priortyArray[count($priortyArray)-1];
          array_pop($priortyArray);
          //WriteLog("CardID=" . $storedPriorityNode[0] . ", Where=" . $storedPriorityNode[1] . ", Index=" . $storedPriorityNode[2] . ", Priority=" . $storedPriorityNode[3]);
          if(CardIsPitchable($storedPriorityNode)) $found = true;
        }
        if($found == true && $storedPriorityNode[3] != 0)
        {
          PitchCardAttempt($storedPriorityNode);
        }
        else
        {
          PassInput();
        }
      }
      else if($turn[0] == "PDECK")//choosing which card to bottom from pitch
      {
        if($AIDebug) WriteLog("AI Branch - Pitch Deck");
        $pitch = &GetPitch($currentPlayer);
        ProcessInput($currentPlayer, 6, "", $pitch[0], 0, "");
        CacheCombatResult();
      }
      else if($turn[0] == "ARS" && $mainPlayer = $currentPlayer)//choose a card to arsenal
      {
        if($AIDebug) WriteLog("AI Branch - Choose Arsenal");
        $priortyArray = GeneratePriorityValues($hand, $character, $arsenal, $items, $allies, $banish, "ToArsenal");
        //LogPriorityArray($priortyArray);
        $found = false;
        while (count($priortyArray) > 0 && !$found) {
          $storedPriorityNode = $priortyArray[count($priortyArray)-1];
          array_pop($priortyArray);
          //WriteLog("CardID=" . $storedPriorityNode[0] . ", Where=" . $storedPriorityNode[1] . ", Index=" . $storedPriorityNode[2] . ", Priority=" . $storedPriorityNode[3]);
          if(CardIsArsenalable($storedPriorityNode)) $found = true;
        }
        if($found == true && $storedPriorityNode[3] != 0)
        {
          ArsenalCardAttempt($storedPriorityNode);
        }
        else
        {
          PassInput();
        }
      }
      else if($turn[0] == "OPT" && $mainPlayer = $currentPlayer)
      {
        if($AIDebug) WriteLog("AI Branch - Opt");
        $options = explode(",", $turn[2]);
        ProcessInput($currentPlayer, 107, $options[0], 0, 0, "");
        CacheCombatResult();
      }
      else if($turn[0] == "HANDTOPBOTTOM"  && $mainPlayer = $currentPlayer)
      {
        if($AIDebug) WriteLog("AI Branch - Hand Top/Bottom");
        $options = explode(",", $turn[2]);
        ProcessInput($currentPlayer, 12, $options[0], 0, 0, "");
        CacheCombatResult();
      }
      else
      {
        if($AIDebug) WriteLog("AI Branch - Pass");
        PassInput();
      }
      ProcessMacros();
      $currentPlayerIsAI = ($currentPlayer == 2 ? true : false);
      if($logicCount == 30 && $currentPlayerIsAI)
      {
        for($i=0; $i<=30 && $currentPlayerIsAI; ++$i)
        {
          PassInput();
          $currentPlayerIsAI = ($currentPlayer == 2 ? true : false);
        }
      }
    }
  }
}

function IsEncounterAI($enemyHero)
{
  global $p2IsAI;
  return str_contains($enemyHero, "ROGUE") || $p2IsAI == "1";
}

function ShouldBlock($found, $storedPriorityNode)
{
  global $currentPlayer;
  $health = &GetHealth($currentPlayer);
  $threatened = CachedTotalAttack() - CachedTotalBlock();
  if(!$found || $threatened == 0) return false;
  if(IsFirstTurn() && ($threatened > 1 || !DoesAttackHaveGoAgain())) return true;//Make AI more likely to block on turn 0
  //If something was found, that thing is able to block (not prio 0), and either the attack is lethal or the AI wants to block with it efficiently, it attempts to block. Otherwise it passes.
  //WriteLog("found->".$found.",prio->".$storedPriorityNode[3].",cachedattack->".CachedTotalAttack().",cachedblock->".CachedTotalBlock().",health->".$health.",attackvalue->".AttackValue("brand_with_cinderclaw_yellow"));
  if($storedPriorityNode[3] != 0 &&
((CachedTotalAttack() - CachedTotalBlock() >= $health && $storedPriorityNode[3] != 0) || (CachedTotalAttack() - CachedTotalBlock() >= BlockValue($storedPriorityNode[0]) && 2.1 <= $storedPriorityNode[3] && $storedPriorityNode[3] <= 2.9)))
  {
    return true;
  }
  else
  {
    return false;
  }
}

function IsFirstTurn()
{
  global $mainPlayer, $firstPlayer, $currentTurn;
  return $mainPlayer == $firstPlayer && $currentTurn == 1;
}

function LogPriorityArray($priorityArray)
{
  WriteLog("Priority Array:");
  for($i = 0; $i < count($priorityArray); ++$i)
  {
    WriteLog("[" . $priorityArray[$i][0] . "," . $priorityArray[$i][1] . "," . $priorityArray[$i][2] . "," . $priorityArray[$i][3] . "]");
  }
}

function LogHandArray($hand)
{
  $rv = "Hand=[";
  for($i = 0; $i < count($hand); ++$i)
  {
    if($i != 0) $rv.=",";
    $rv.=$hand[$i];
  }
  WriteLog($rv . "]");
}

function ResourcesNeededToSave($aiID)
{
  switch($aiID)
  {
    case "ROGUE027": return 1;
    default: return 0;
  }
}

?>
