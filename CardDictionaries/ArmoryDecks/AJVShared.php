<?php

function AJVAbilityType($cardID): string
{
  return match ($cardID) {
    "summit_the_unforgiving" => "AA",
    "gauntlets_of_the_boreal_domain" => "A",
    default => ""
  };
}

function AJVAbilityCost($cardID)
{
  return match ($cardID) {
    "summit_the_unforgiving" => 6,
    "gauntlets_of_the_boreal_domain" => 2,
    default => 0
  };
}

function AJVPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
{
  global $currentPlayer;
  switch ($cardID) {
    case "gauntlets_of_the_boreal_domain":
      if(SearchCardList($additionalCosts, $currentPlayer, talent:"EARTH") != "") AddCurrentTurnEffect("gauntlets_of_the_boreal_domain-E", $currentPlayer);
      if(SearchCardList($additionalCosts, $currentPlayer, talent:"ICE") != "") AddCurrentTurnEffect("gauntlets_of_the_boreal_domain-I", $currentPlayer);
      return "";
    case "crumble_to_eternity_blue":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRCHAR:type=E&MYCHAR:type=E");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose an equipment to add a -1 defense counter", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MODDEFCOUNTER", $currentPlayer, "-1", 1);
      return "";
    case "frozen_to_death_blue":
      if (DelimStringContains($additionalCosts, "ICE")) {
        Mangle();
      }
      $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
      FrostbiteExposed($otherPlayer, $currentPlayer);
      return "";
    default:
      return "";
  }
}

function AJVCombatEffectActive($cardID, $attackID)
{
switch($cardID) {
    case "summit_the_unforgiving": return true;
    // case "gauntlets_of_the_boreal_domain": return CardNameContains($attackID, "Mangle");//check if I need to do this or the next 2 line
    case "gauntlets_of_the_boreal_domain-E": return CardNameContains($attackID, "Mangle");
    case "gauntlets_of_the_boreal_domain-I": return CardNameContains($attackID, "Mangle");
    case "crumble_to_eternity_blue": return true;
    default: return false;
}
}

function AJVHitEffect($cardID) {
  global $currentPlayer, $defPlayer;
  switch($cardID)
  {
    case "summit_the_unforgiving":
      if(IsHeroAttackTarget()) {
        FrostbiteExposed($defPlayer, $currentPlayer);
      }
      break;
    default:
      break;
  }
}

function AJVAbilityHasGoAgain($cardID) {
  switch($cardID)
  {
    case "gauntlets_of_the_boreal_domain":
      return true;
    default:
      return false;
  }
}

function AJVEffectAttackModifier($cardID): int
{
  return match ($cardID) {
    "gauntlets_of_the_boreal_domain-E" => 2,
    default => 0,
  };
}

function FrostbiteExposed($otherPlayer, $player, $may=false) {
  AddDecisionQueue("LISTEXPOSEDEQUIPSLOTS", $otherPlayer, "-");
  AddDecisionQueue("SETDQCONTEXT", $player, "Choose an exposed equipment zone to " . CardLink("frostbite", "frostbite"), 1);
  AddDecisionQueue("BUTTONINPUT", $player, "<-", 1);
  AddDecisionQueue("SETDQVAR", $player, "0", 1);
  AddDecisionQueue("EQUIPCARD", $otherPlayer, "frostbite-{0}", 1);
}

function CheckHeavy($player) {
  $weapons = SearchCharacter($player, type:"W");
  $numWeapons = $weapons != "" ? count(explode(",", $weapons)) : 0;
  $offHands = SearchCharacter($player, subtype:"Off-Hand");
  $numOffHands = $offHands != "" ? count(explode(",", $offHands)) : 0;
  return $numWeapons + $numOffHands == 1;
}

?>