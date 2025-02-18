<?php

  function MONRunebladePlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
  {
    global $currentPlayer;
    $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
    $rv = "";
    switch($cardID)
    {
      case "chane_bound_by_shadow": case "chane":
        PlayAura("soul_shackle", $currentPlayer, 1, true);
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "invert_existence_blue":
        AddDecisionQueue("FINDINDICES", $otherPlayer, $cardID);
        AddDecisionQueue("MULTICHOOSETHEIRDISCARD", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEDISCARD", $otherPlayer, "-", 1);
        AddDecisionQueue("MULTIBANISH", $otherPlayer, "DISCARD", 1);
        AddDecisionQueue("INVERTEXISTENCE", $currentPlayer, "-", 1);
        return "";
      case "unhallowed_rites_red": case "unhallowed_rites_yellow": case "unhallowed_rites_blue":
        MZMoveCard($currentPlayer, "MYDISCARD:bloodDebtOnly=true;type=A", "MYBOTDECK", may:true);
        return "";
      case "dimenxxional_gateway_red": case "dimenxxional_gateway_yellow": case "dimenxxional_gateway_blue":
        if($cardID == "dimenxxional_gateway_red") $optAmt = 3;
        else if($cardID == "dimenxxional_gateway_yellow") $optAmt = 2;
        else $optAmt = 1;
        Opt($cardID, $optAmt);
        AddDecisionQueue("FINDINDICES", $currentPlayer, "TOPDECK");
        AddDecisionQueue("DECKCARDS", $currentPlayer, "<-", 1);
        AddDecisionQueue("REVEALCARDS", $currentPlayer, "-", 1);
        AddDecisionQueue("SPECIFICCARD", $currentPlayer, "DIMENXXIONALGATEWAY", 1);
        return "";
      case "seeping_shadows_red": case "seeping_shadows_yellow": case "seeping_shadows_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "bounding_demigon_red": case "bounding_demigon_yellow": case "bounding_demigon_blue":
        if($from == "BANISH") AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "rift_bind_red": case "rift_bind_yellow": case "rift_bind_blue":
        if($from == "BANISH") AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "rifted_torment_red": case "rifted_torment_yellow": case "rifted_torment_blue":
        if($from == "BANISH") DealArcane(1, 0, "PLAYCARD", $cardID);
        return "";
      case "seeds_of_agony_red": case "seeds_of_agony_yellow": case "seeds_of_agony_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "dread_scythe":
        if(!IsAllyAttackTarget()) DealArcane(1, 0, "PLAYCARD", $cardID);
        return "";
      case "aether_ironweave":
        GainResources($currentPlayer, 2);
        return "";
      case "sonata_arcanix_red":
        $xVal = $resourcesPaid/2;
        $numRevealed = 3 + $xVal;
        WriteLog(CardLink($cardID, $cardID) . " reveals " . $numRevealed . " cards.");
        AddDecisionQueue("FINDINDICES", $currentPlayer, "DECKTOPXINDICES," . $numRevealed);
        AddDecisionQueue("DECKCARDS", $currentPlayer, "<-", 1);
        AddDecisionQueue("REVEALCARDS", $currentPlayer, "-", 1);
        AddDecisionQueue("SONATAARCANIX", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTICHOOSEDECK", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEDECK", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIADDHAND", $currentPlayer, "1", 1);
        AddDecisionQueue("SONATAARCANIXSTEP2", $currentPlayer, "-", 1);
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
        return "";
      case "vexing_malice_red": case "vexing_malice_yellow": case "vexing_malice_blue":
        DealArcane(2, 0, "PLAYCARD", $cardID);
        return "";
      case "arcanic_crackle_red": case "arcanic_crackle_yellow": case "arcanic_crackle_blue":
        DealArcane(1, 0, "PLAYCARD", $cardID);
        return "";
      default: return "";
    }
  }

  function MONRunebladeHitEffect($cardID)
  {
    global $mainPlayer;
    switch($cardID) {
      case "galaxxi_black":
        if(IsHeroAttackTarget()) DealArcane(1, 0, "PLAYCARD", "galaxxi_black", false, $mainPlayer);
        break;
      default: break;
    }
  }

  function UpTo2FromOpposingGraveyardIndices($player)
  {
    $discard = new Discard($player);
    if($discard->Empty()) return "";
    $rv = ($discard->NumCards() == 1 ? "1" : "2") . "-";
    $rv .= GetIndices($discard->NumCards()*DiscardPieces(), 0, DiscardPieces());
    return $rv;
  }

  function DimenxxionalCrossroadsPassive($cardID, $from)
  {
    global $currentPlayer, $CS_NumAttackCards, $CS_NumNonAttackCards;
    if($from != "BANISH") return;
    $type = CardType($cardID);
    if(($type == "AA" && GetClassState($currentPlayer, $CS_NumAttackCards) == 0) || DelimStringContains($type, "A") && GetClassState($currentPlayer, $CS_NumNonAttackCards) == 1)
    {
      DealArcane(1, 0, "PLAYCARD", "dimenxxional_crossroads_yellow");
    }
  }

  function LordSutcliffeAbility($player, $index)
  {
    global $currentPlayer;
    WriteLog(CardLink("lord_sutcliffe", "lord_sutcliffe") . " deals 1 arcane damage to each player");
    DealArcane(1, 0, "ABILITY", "lord_sutcliffe", false, 1);
    AddDecisionQueue("LESSTHANPASS", $currentPlayer, "1");
    AddDecisionQueue("LORDSUTCLIFFE", $currentPlayer, $index, 1);
    DealArcane(1, 0, "ABILITY", "lord_sutcliffe", false, 2);
    AddDecisionQueue("LESSTHANPASS", $currentPlayer, "1");
    AddDecisionQueue("LORDSUTCLIFFE", $currentPlayer, $index, 1);
  }

  function LordSutcliffeAfterDQ($player, $parameter)
  {
    $index = $parameter;
    $arsenal = &GetArsenal($player);
    if(!ArsenalEmpty($player)) {
      $arsenal[$index+3] += 1;
      if($arsenal[$index+3] >= 3) MentorTrigger($player, $index);
    }
  }

?>
