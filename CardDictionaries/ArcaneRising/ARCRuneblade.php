<?php


  function ARCRunebladePlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
  {
    global $currentPlayer;
    switch($cardID)
    {
      case "grasp_of_the_arknight":
        PlayAura("runechant", $currentPlayer);
        return "";
      case "crown_of_dichotomy":
        MZMoveCard($currentPlayer, "MYDISCARD:type=A&MYDISCARD:type=AA", "MYTOPDECK");
        AddDecisionQueue("SPECIFICCARD", $currentPlayer, "CROWNOFDICHOTOMY", 1);
        return "";
      case "mordred_tide_red":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "become_the_arknight_blue":
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYHAND:type=A&MYHAND:type=AA");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to discard", 1);
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
        AddDecisionQueue("DISCARDCARD", $currentPlayer, "HAND-".$currentPlayer, 1);
        AddDecisionQueue("SPECIFICCARD", $currentPlayer, "BECOMETHEARKNIGHT", 1);
        return "";
      case "tome_of_the_arknight_blue":
        $deck = new Deck($currentPlayer);
        $deck->Reveal(2);
        $cards = explode(",", $deck->Top(amount:2));
        $type1 = CardType($cards[0]);
        $type2 = CardType($cards[1]);
        if((DelimStringContains($type1, "AA") && DelimStringContains($type2, "A")) || (DelimStringContains($type2, "AA") && DelimStringContains($type1, "A"))) {
          $deck->Top(remove:true, amount:2);
          AddPlayerHand($cards[0], $currentPlayer, "HAND");
          AddPlayerHand($cards[1], $currentPlayer, "HAND");
        }
        return "";
      case "spellblade_assault_red": case "spellblade_assault_yellow": case "spellblade_assault_blue":
        PlayAura("runechant", $currentPlayer, 2);
        return "";
      case "reduce_to_runechant_red": case "reduce_to_runechant_yellow": case "reduce_to_runechant_blue":
        PlayAura("runechant", $currentPlayer);
        return "";
      case "oath_of_the_arknight_red": case "oath_of_the_arknight_yellow": case "oath_of_the_arknight_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        PlayAura("runechant", $currentPlayer);
        return "";
      case "drawn_to_the_dark_dimension_red": case "drawn_to_the_dark_dimension_yellow": case "drawn_to_the_dark_dimension_blue": Draw($currentPlayer); return "";
      case "spellblade_strike_red": case "spellblade_strike_yellow": case "spellblade_strike_blue": PlayAura("runechant", $currentPlayer); return "";
      case "read_the_runes_red": PlayAura("runechant", $currentPlayer, 3); return "";
      case "read_the_runes_yellow": PlayAura("runechant", $currentPlayer, 2); return "";
      case "read_the_runes_blue": PlayAura("runechant", $currentPlayer); return "";
      default: return "";
    }
  }

  function ARCRunebladeHitEffect($cardID)
  {
    global $combatChainState, $mainPlayer, $CCS_DamageDealt;
    switch($cardID)
    {
      case "nebula_blade":
        PlayAura("runechant", $mainPlayer);
        break;
      case "arknight_ascendancy_red":
        $damageDone = $combatChainState[$CCS_DamageDealt];
        PlayAura("runechant", $mainPlayer, $damageDone);
        break;
      default: break;
    }
    return "";
  }

  function NumRunechants($player)
  {
    $auras = &GetAuras($player);
    $count = 0;
    for($i=0; $i<count($auras); $i+=AuraPieces())
    {
      if(CardNameContains($auras[$i], "Runechant", $player)) ++$count;
    }
    return $count;
  }

  function ViseraiPlayCard($cardID)
  {
    global $currentPlayer, $CS_NumNonAttackCards;
    $target = CardType($cardID) == "A" ? 1 : 0;
    if(ClassContains($cardID, "RUNEBLADE", $currentPlayer) && GetClassState($currentPlayer, $CS_NumNonAttackCards) > $target) {
      PlayAura("runechant", $currentPlayer);
    }
  }

?>
