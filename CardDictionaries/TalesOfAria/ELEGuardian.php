<?php

  function ELEGuardianPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
  {
    global $currentPlayer, $CS_DamagePrevention;
    $rv = "";
    switch($cardID)
    {
      case "oldhim_grandfather_of_eternity": case "oldhim":
        if(SearchCardList($additionalCosts, $currentPlayer, talent:"EARTH") != "") IncrementClassState($currentPlayer, $CS_DamagePrevention, 2);
        if(SearchCardList($additionalCosts, $currentPlayer, talent:"ICE") != "")
        {
          if(IsAllyAttacking()) $rv .= "<span style='color:red;'>No card is put on top because there is no attacking hero when allies attack.</span>";
          else
          {
            $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
            AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
            AddDecisionQueue("CHOOSEHAND", $otherPlayer, "<-", 1);
            AddDecisionQueue("MULTIREMOVEHAND", $otherPlayer, "-", 1);
            AddDecisionQueue("MULTIADDTOPDECK", $otherPlayer, "-", 1);
            $rv .= "The opponent must put a card from their hand on top of their deck.";
          }
        }
        return $rv;
      case "winters_wail":
        if(SearchCardList($additionalCosts, $currentPlayer, talent:"ICE") != "") AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "oaken_old_red":
        if(DelimStringContains($additionalCosts, "ICE") && DelimStringContains($additionalCosts, "EARTH")) AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "awakening_blue":
        $num = GetHealth($currentPlayer == 1 ? 2 : 1) - GetHealth($currentPlayer);
        PlayAura("seismic_surge", $currentPlayer, $num * (DelimStringContains($additionalCosts, "EARTH") ? 2 : 1));
        MZMoveCard($currentPlayer, "MYDECK:type=AA;class=GUARDIAN;maxCost=" . CountAura("seismic_surge", $currentPlayer), "MYHAND", may:true, isReveal:true);
        return "";
      case "tear_asunder_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "embolden_red": case "embolden_yellow": case "embolden_blue":
        if(NumNonTokenAura($currentPlayer) > 1) { $rv = "Drew a card"; Draw($currentPlayer); }
        return $rv;
      default: return "";
    }
  }

  function ELEGuardianHitEffect($cardID)
  {
    global $defPlayer, $mainPlayer, $combatChainState, $CCS_AttackFused;
    switch($cardID)
    {
      case "winters_wail":
        if(IsHeroAttackTarget()) {
          PlayAura("frostbite", $defPlayer, effectController: $mainPlayer);
        }
        break;
      case "endless_winter_red":
        if(IsHeroAttackTarget()) {
          AddCurrentTurnEffect($cardID . "-HIT", $defPlayer);
          AddNextTurnEffect($cardID . "-HIT", $defPlayer);
        }
        break; 
      case "entangle_red": case "entangle_yellow": case "entangle_blue":
        if(IsHeroAttackTarget() && $combatChainState[$CCS_AttackFused]) AddNextTurnEffect($cardID, $defPlayer);
        break;
      case "thump_red": case "thump_yellow": case "thump_blue":
        if(IsHeroAttackTarget() && HasIncreasedAttack()) PummelHit();
        break;
      default: break;
    }
  }