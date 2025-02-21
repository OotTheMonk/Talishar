<?php

  function DVRAbilityType($cardID) {
    switch($cardID) {
      case "dawnblade_resplendent": return "AA";
      case "blossom_of_spring": return "A";
      default: return "";
    }
  }

  function DVRAbilityCost($cardID) {
    switch($cardID) {
      case "dawnblade_resplendent": return 1;
      case "blossom_of_spring": return 0;
      default: return "";
    }
  }

  function DVRPlayAbility($cardID) {
    global $currentPlayer;
    switch($cardID)
    {
      case "blossom_of_spring":
        GainResources($currentPlayer, 1);
        return "";
      case "glistening_steelblade_yellow":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        AddCurrentTurnEffect($cardID . "-1", $currentPlayer);
        return "";
      case "en_garde_red":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "run_through_yellow":
        GiveAttackGoAgain();
        AddCurrentTurnEffectFromCombat($cardID, $currentPlayer);
        return "";
      case "thrust_red":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "on_a_knife_edge_yellow":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "visit_the_blacksmith_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "blade_flash_blue":
        GiveAttackGoAgain();
        return "";
    }
  }

function DVREffectAttackModifier($cardID)
{
  $params = explode(",", $cardID);
  $cardID = $params[0];
  switch($cardID) {
    case "en_garde_red": return 3;
    case "run_through_yellow": return 2;
    case "thrust_red": return 3;
    case "visit_the_blacksmith_blue": return 1;
    default: return 0;
  }
}

function DVRCombatEffectActive($cardID, $attackID)
{
  global $mainPlayer;
  $params = explode(",", $cardID);
  $cardID = $params[0];
  switch($cardID) {
    case "glistening_steelblade_yellow": case "glistening_steelblade_yellow-1": return CardNameContains($attackID, "Dawnblade", $mainPlayer, true); 
    case "en_garde_red": return TypeContains($attackID, "W", $mainPlayer);
    case "run_through_yellow": case "thrust_red": case "on_a_knife_edge_yellow": case "visit_the_blacksmith_blue": case "blade_flash_blue": return CardSubType($attackID) == "Sword";
    default: return false;
  }
}

function HalaGoldenhelmAbility($player, $index)
{
  GiveAttackGoAgain();
  $rv = CardLink("hala_goldenhelm", "hala_goldenhelm") . " gave the attack go again";
  $arsenal = &GetArsenal($player);
  ++$arsenal[$index+3];
  if($arsenal[$index+3] >= 2) {
    $rv .= " and searches for Glistening Steelblade";
    MentorTrigger($player, $index, specificCard:"glistening_steelblade_yellow");
  }
  WriteLog($rv);
}

function DoriQuicksilverProdigyEffect()
{
  global $mainPlayer, $combatChainState, $CCS_WeaponIndex;
  $char = &GetPlayerCharacter($mainPlayer);
  $char[1] = 1;
  $char[$combatChainState[$CCS_WeaponIndex]+1] = 2;
  ++$char[$combatChainState[$CCS_WeaponIndex]+5];
}
