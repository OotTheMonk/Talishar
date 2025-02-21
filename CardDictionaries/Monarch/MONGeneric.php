<?php

  function MONGenericPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "-")
  {
    global $currentPlayer, $combatChainState, $CCS_CurrentAttackGainedGoAgain, $CombatChain, $CS_PlayIndex;
    $rv = "";
    switch($cardID)
    {
      case "blood_drop_brocade": GainResources($currentPlayer, 1); return "";
      case "stubby_hammerers": AddCurrentTurnEffect($cardID, $currentPlayer); return "";
      case "time_skippers": GainActionPoints(2, $currentPlayer); return "";
      case "exude_confidence_red":
        if($from == "PLAY") $CombatChain->AttackCard()->ModifyPower(2);
        return "";
      case "seek_horizon_red": case "seek_horizon_yellow": case "seek_horizon_blue":
        if($additionalCosts != "-") AddDecisionQueue("OP", $currentPlayer, "GIVEATTACKGOAGAIN", 1);
        return "";
      case "captains_call_red": case "captains_call_yellow": case "captains_call_blue":
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts, 1);
        AddDecisionQueue("MODAL", $currentPlayer, $cardID, 1);
        return "";
      case "adrenaline_rush_red": case "adrenaline_rush_yellow": case "adrenaline_rush_blue":
        if(PlayerHasLessHealth($currentPlayer)) { AddCurrentTurnEffect($cardID, $currentPlayer); $rv = "Gets +3 power"; }
        return $rv;
      case "belittle_red": case "belittle_yellow": case "belittle_blue":
        if(DelimStringContains($additionalCosts, "BELITTLE") && CanRevealCards($currentPlayer)) {
          MZMoveCard($currentPlayer, "MYDECK:isSameName=minnowism_red", "MYHAND", may:true, isReveal:true);
          AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
        }
        return "";
      case "frontline_scout_red": case "frontline_scout_yellow": case "frontline_scout_blue":
        if(!IsAllyAttackTarget()) {
          $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
          AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
          AddDecisionQueue("REVEALHANDCARDS", $otherPlayer, "-", 1);
        }
        if($from == "ARS") GiveAttackGoAgain();
        return "";
      case "pound_for_pound_red": case "pound_for_pound_yellow": case "pound_for_pound_blue":
        if(PlayerHasLessHealth($currentPlayer)) { AddCurrentTurnEffect($cardID, $currentPlayer); }
        return "";
      case "rally_the_rearguard_red": case "rally_the_rearguard_yellow": case "rally_the_rearguard_blue":
        if($from == "PLAY") {
          $CombatChain->AbilityCard()->ModifyDefense(3);
        }
        return "";
      case "minnowism_red": case "minnowism_yellow": case "minnowism_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "warmongers_recital_red": case "warmongers_recital_yellow": case "warmongers_recital_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "memorial_ground_red": case "memorial_ground_yellow": case "memorial_ground_blue": 
        $params = explode("-", $target);
        $index = SearchdiscardForUniqueID($params[1], $currentPlayer);
        if($index != -1) {
          AddDecisionQueue("PASSPARAMETER", $currentPlayer, "MYDISCARD-".$index, 1);
          AddDecisionQueue("MZADDZONE", $currentPlayer, "MYTOPDECK", 1);
          AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        }
        else {
          WriteLog(CardLink($cardID, $cardID) . " layer fails as there are no remaining targets for the targeted effect.");
          return "FAILED";
        }
        return "";
      default: return "";
    }
  }

  function MONGenericHitEffect($cardID)
  {
    global $mainPlayer;
    switch($cardID)
    {
      case "nourishing_emptiness_red": if(SearchDiscard($mainPlayer, "AA") == "") { AddCurrentTurnEffectFromCombat($cardID, $mainPlayer); } break;
      case "brandish_red": case "brandish_yellow": case "brandish_blue": AddCurrentTurnEffectFromCombat($cardID, $mainPlayer); break;
      case "overload_red": case "overload_yellow": case "overload_blue": GiveAttackGoAgain(); break;
      default: break;
    }
  }

  function ExudeConfidenceReactionsPlayable()
  {
    global $CombatChain, $defPlayer;
    $found = false;
    for($i=1; $i<$CombatChain->NumCardsActiveLink(); ++$i) {
      $card = $CombatChain->Card($i, cardNumber:true);
      $attackValue = ModifiedAttackValue($card->ID(), $defPlayer, "CC", source:"exude_confidence_red");
      if(!IsAllyAttackTarget() && $card->PlayerID() == $defPlayer && $attackValue >= CachedTotalAttack()) $found = true;
    }
    return $found;
  }

?>
