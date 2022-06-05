<?php


  function UPRNinjaCardType($cardID)
  {
    switch($cardID)
    {
      case "UPR044": return "C";
      case "UPR048": return "AA";
      case "UPR057": case "UPR058": case "UPR059": return "A";
      default: return "";
    }
  }

  function UPRNinjaCardSubType($cardID)
  {
    switch($cardID)
    {

      default: return "";
    }
  }

  //Minimum cost of the card
  function UPRNinjaCardCost($cardID)
  {
    switch($cardID)
    {
      case "UPR048": return 0;
      case "UPR057": case "UPR058": case "UPR059": return 0;
      default: return 0;
    }
  }

  function UPRNinjaPitchValue($cardID)
  {
    switch($cardID)
    {
      case "UPR048": return 1;
      case "UPR057": return 1;
      case "UPR058": return 2;
      case "UPR059": return 3;
      default: return 0;
    }
  }

  function UPRNinjaBlockValue($cardID)
  {
    switch($cardID)
    {
      case "UPR048": return 3;
      case "UPR057": case "UPR058": case "UPR059": return 3;
      default: return -1;
    }
  }

  function UPRNinjaAttackValue($cardID)
  {
    switch($cardID)
    {
      case "UPR048": return 3;
      default: return 0;
    }
  }

  function UPRNinjaPlayAbility($cardID, $from, $resourcesPaid)
  {
    global $currentPlayer;
    $rv = "";
    switch($cardID)
    {
      case "UPR044": case "UPR045":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "GYCARD,UPR101");
        AddDecisionQueue("CHOOSEDISCARD", $currentPlayer, "<-", 1);
        AddDecisionQueue("REMOVEDISCARD", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDHAND", $currentPlayer, "-", 1);
        return "";
      case "UPR057": case "UPR058": case "UPR059":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        AddDecisionQueue("FINDINDICES", $currentPlayer, "GYCARD,UPR101");
        AddDecisionQueue("CHOOSEDISCARD", $currentPlayer, "<-", 1);
        AddDecisionQueue("REMOVEDISCARD", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDHAND", $currentPlayer, "-", 1);
        return "";
      default: return "";
    }
  }

  function UPRNinjaHitEffect($cardID)
  {
    global $mainPlayer;
    switch($cardID)
    {
      case "UPR048":
        if(NumPhoenixFlameChainLinks() >= 3)
        {
          Draw($mainPlayer);
          Draw($mainPlayer);
          Draw($mainPlayer);
        }
      default: break;
    }
  }

?>
