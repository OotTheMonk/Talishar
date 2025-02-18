<?php

  function ELERunebladePlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
  {
    global $currentPlayer, $otherPlayer, $CS_NumNonAttackCards, $CS_NumAttackCards, $combatChainState, $CCS_WeaponIndex;
    global $CS_NextNAAInstant, $CS_ArcaneDamageDealt;
    $rv = "";
    switch($cardID)
    {
      case "blossoming_spellblade_red":
        if(DelimStringContains($additionalCosts, "EARTH") && DelimStringContains($additionalCosts, "LIGHTNING")) {
          AddCurrentTurnEffect($cardID, $currentPlayer);
          DealArcane(1, 0, "PLAYCARD", $cardID, false);
        }
        return "";
      case "flicker_wisp_yellow":
        DealArcane(1, 0, "PLAYCARD", $cardID);
        return "";
      case "force_of_nature_blue":
        AddCurrentTurnEffect($cardID . "-HIT", $currentPlayer);
        return "";
      case "explosive_growth_red": case "explosive_growth_yellow": case "explosive_growth_blue":
        DealArcane(1, 0, "PLAYCARD", $cardID);
        return "";
      case "rites_of_lightning_red": case "rites_of_lightning_yellow": case "rites_of_lightning_blue":
        AddDecisionQueue("CLASSSTATEGREATERORPASS", $currentPlayer, $CS_ArcaneDamageDealt . "-1", 1);
        AddDecisionQueue("OP", $currentPlayer, "GIVEATTACKGOAGAIN", 1);
        return "";
      case "rites_of_replenishment_red": case "rites_of_replenishment_yellow": case "rites_of_replenishment_blue":
        if(GetClassState($currentPlayer, $CS_ArcaneDamageDealt) > 0) MZMoveCard($currentPlayer, "MYDISCARD:type=A", "MYBOTDECK", may:true);
        return "";
      case "bramble_spark_red": case "bramble_spark_yellow": case "bramble_spark_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "rosetta_thorn":
        if(GetClassState($currentPlayer, $CS_NumNonAttackCards) > 0 && GetClassState($currentPlayer, $CS_NumAttackCards) > 0)
        {
          DealArcane(2, 0, "PLAYCARD", $cardID);
        }
        return $rv;
      case "duskblade":
        if(GetClassState($currentPlayer, $CS_NumNonAttackCards) > 0 && GetClassState($currentPlayer, $CS_NumAttackCards) > 0)
        {
          $character = &GetPlayerCharacter($currentPlayer);
          ++$character[$combatChainState[$CCS_WeaponIndex]+3];
        }
        return $rv;
      case "spellbound_creepers":
        SetClassState($currentPlayer, $CS_NextNAAInstant, 1);
        return "";
      case "sutcliffes_suede_hides":
        GiveAttackGoAgain();
        return "";
      case "sigil_of_suffering_red": case "sigil_of_suffering_yellow": case "sigil_of_suffering_blue":
        if(!IsAllyAttacking()) DealArcane(1, 1, "PLAYCARD", $cardID);
        return "";
      case "singeing_steelblade_red": case "singeing_steelblade_yellow": case "singeing_steelblade_blue":
        DealArcane(1, 0, "PLAYCARD", $cardID);
        return "";
      default: return "";
    }
  }

  function ELERunebladeHitEffect($cardID)
  {
    switch($cardID)
    {
      default: break;
    }
  }

?>
