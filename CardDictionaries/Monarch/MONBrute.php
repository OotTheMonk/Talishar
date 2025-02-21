<?php

  function MONBrutePlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
  {
    global $currentPlayer;
    $rv = "";
    switch($cardID)
    {
      case "hexagore_the_death_hydra":
        $numBD = SearchCount(SearchBanish($currentPlayer, "", "", -1, -1, "", "", true));
        $damage = 6 - $numBD;
        WriteLog("Player " . $currentPlayer . " lost " . $damage . " life");
        DamageTrigger($currentPlayer, $damage, "PLAYCARD", $cardID);
        return "";
      case "shadow_of_blasmophet_red":
        Draw($currentPlayer);
        $card = DiscardRandom();
        if(ModifiedAttackValue($card, $currentPlayer, "HAND", source:$cardID) >= 6) {
          MZMoveCard($currentPlayer, "MYDECK:bloodDebtOnly=true", "MYBANISH,DECK,-", may:true);
          AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
        }
        return "";
      case "deadwood_rumbler_red": case "deadwood_rumbler_yellow": case "deadwood_rumbler_blue":
        Draw($currentPlayer);
        $card = DiscardRandom();
        if(ModifiedAttackValue($card, $currentPlayer, "HAND", source:$cardID) >= 6) {
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD&THEIRDISCARD");
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to banish with " . CardLink($cardID, $cardID), 1);
          AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("MZBANISH", $currentPlayer, "GY,-," . $currentPlayer, 1);
          AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        }
        return "";
      case "unworldly_bellow_red": case "unworldly_bellow_yellow": case "unworldly_bellow_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "ravenous_meataxe":
        Draw($currentPlayer);
        $card = DiscardRandom();
        if(ModifiedAttackValue($card, $currentPlayer, "HAND", source:$cardID) >= 6) AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "tear_limb_from_limb_blue":
        Draw($currentPlayer);
        $card = DiscardRandom();
        if(ModifiedAttackValue($card, $currentPlayer, "HAND", source:$cardID) >= 6) AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "pulping_red": case "pulping_yellow": case "pulping_blue":
        Draw($currentPlayer);
        $card = DiscardRandom();
        if(ModifiedAttackValue($card, $currentPlayer, "HAND", source:$cardID) >= 6) AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      default: return "";
    }
  }

  function MONBruteHitEffect($cardID)
  {
    switch($cardID)
    {
      default: break;
    }
  }

  function RandomBanish3GY($cardID, $modifier = "NA")
  {
    global $currentPlayer;
    $hand = &GetHand($currentPlayer);
    $discard = &GetDiscard($currentPlayer);
    if(count($discard) < 3) return;
    $BanishedIncludes6 = 0;
    $diabolicOfferingCount = 0;
    for($i = 0; $i < 3; $i++) {
      $index = GetRandom(0, count($discard)/DiscardPieces()-1) * DiscardPieces();
      if(ModifiedAttackValue($discard[$index], $currentPlayer, "GY", source:$cardID) >= 6) ++$BanishedIncludes6;
      elseif($discard[$index] == "diabolic_offering_blue") ++$diabolicOfferingCount;
      $cardID = RemoveDiscard($currentPlayer, $index);
      BanishCardForPlayer($cardID, $currentPlayer, "DISCARD", $modifier);
      $discard = array_values($discard);
    }
    if($BanishedIncludes6 > 0) $BanishedIncludes6 += $diabolicOfferingCount;
    return $BanishedIncludes6 > 3 ? 3 : $BanishedIncludes6;
  }

  function LadyBarthimontAbility($player, $index)
  {
    $deck = new Deck($player);
    if($deck->Empty()) return;
    $topDeck = $deck->BanishTop("-", $player);
    if(ModifiedAttackValue($topDeck, $player, "DECK", source:"lady_barthimont") >= 6) {
      $arsenal = &GetArsenal($player);
      ++$arsenal[$index+3];
      AddCurrentTurnEffect("lady_barthimont", $player);
      if($arsenal[$index+3] == 2) MentorTrigger($player, $index);
    }
  }

?>
