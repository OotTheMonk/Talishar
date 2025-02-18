<?php

  function UPRNinjaPlayAbility($cardID)
  {
    global $currentPlayer;
    switch($cardID)
    {
      case "fai_rising_rebellion": case "fai":
        if(SearchCurrentTurnEffects("amnesia_red", $currentPlayer)) return "";
        MZMoveCard($currentPlayer, "MYDISCARD:isSameName=phoenix_flame_red", "MYHAND");
        return "";
      case "heat_wave":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "spreading_flames_red":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "combustion_point_red":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "CCDEFLESSX," . NumDraconicChainLinks()-1);
        AddDecisionQueue("FILTER", $currentPlayer, "CombatChain-exclude-type-E", 1);
        AddDecisionQueue("FILTER", $currentPlayer, "CombatChain-exclude-subtype-evo", 1);
        AddDecisionQueue("FILTER", $currentPlayer, "CombatChain-include-player-" . ($currentPlayer == 1 ? 2 : 1), 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to banish", 1);
        AddDecisionQueue("CHOOSECOMBATCHAIN", $currentPlayer, "<-", 1);
        AddDecisionQueue("REMOVECOMBATCHAIN", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIBANISH", ($currentPlayer == 1 ? 2 : 1), "CC,-", 1);
        return "";
      case "rise_from_the_ashes_red": case "rise_from_the_ashes_yellow": case "rise_from_the_ashes_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        MZMoveCard($currentPlayer, "MYDISCARD:isSameName=phoenix_flame_red", "MYHAND", may:true);
        return "";
      case "brand_with_cinderclaw_red": case "brand_with_cinderclaw_yellow": case "brand_with_cinderclaw_blue":
        AddCurrentTurnEffectFromCombat($cardID, $currentPlayer);
        return "";
      case "tide_flippers":
        GiveAttackGoAgain();
        return "";
      default: return "";
    }
  }

  function UPRNinjaHitEffect($cardID)
  {
    global $mainPlayer, $combatChainState;
    switch($cardID) {
      case "phoenix_form_red":
        if(IsHeroAttackTarget() && NumChainLinksWithName("Phoenix Flame") >= 3) {
          Draw($mainPlayer);
          Draw($mainPlayer);
          Draw($mainPlayer);
        }
        break;
      case "engulfing_flamewave_red": case "engulfing_flamewave_yellow": case "engulfing_flamewave_blue":
        $deck = new Deck($mainPlayer);
        if($deck->Reveal() && CardType($deck->Top()) == "AA" && CardCost($deck->Top()) < NumDraconicChainLinks()) $deck->BanishTop("TT", $mainPlayer);
        break;
      case "mounting_anger_red": case "mounting_anger_yellow": case "mounting_anger_blue":
      case "rising_resentment_red": case "rising_resentment_yellow": case "rising_resentment_blue":
      case "soaring_strike_red": case "soaring_strike_yellow": case "soaring_strike_blue":
        AddLayer("TRIGGER", $mainPlayer, $cardID);
        break;
      case "take_the_tempo_red":
        $deck = new Deck($mainPlayer);
        if(HitsInCombatChain() >= 2) $deck->BanishTop(CardType($deck->Top()) == "AA" ? "NT" : "-", $mainPlayer);
        break;
      default: break;
    }
  }

?>
