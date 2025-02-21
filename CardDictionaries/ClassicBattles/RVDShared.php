<?php

  function RVDEffectAttackModifier($cardID) {
    switch($cardID) {
      default: return 0;
    }
  }

  function RVDAbilityType($cardID) {
    switch($cardID) {
      case "bone_basher": return "AA";
      case "blossom_of_spring": return "A";
      default: return "";
    }
  }

  function RVDAbilityCost($cardID) {
    switch($cardID) {
      case "bone_basher": return 2;
      case "blossom_of_spring": return 0;
      default: return "";
    }
  }

function RVDPlayAbility($cardID)
{
  global $currentPlayer;
  $rv = "";
  switch($cardID) {
    case "blossom_of_spring":
      GainResources($currentPlayer, 1);
      return "";
    case "wrecking_ball_red":
      Draw($currentPlayer);
      $card = DiscardRandom();
      $rv = "Discarded " . CardLink($card, $card);
      if(ModifiedAttackValue($card, $currentPlayer, "HAND", source:"wrecking_ball_red") >= 6) {
        Intimidate();
      }
      return "";
    case "clearing_bellow_blue":
      Intimidate();
      return "";
    default: return "";
  }
}

function ChiefRukutanAbility($player, $index)
{
  $rv = CardLink("chief_rukutan", "chief_rukutan") . " Intimidates";
  Intimidate();
  $arsenal = &GetArsenal($player);
  ++$arsenal[$index+3];
  if($arsenal[$index+3] == 2) {
    $rv .= " and searches for an Alpha Rampage";
    MentorTrigger($player, $index, specificCard:"alpha_rampage_red");
  }
  WriteLog($rv);
}
