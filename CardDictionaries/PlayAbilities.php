<?php

  function TCCPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
  {
    global $mainPlayer, $currentPlayer, $defPlayer;
    $rv = "";
    $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
    switch($cardID) {
      case "TCC035":
        AddCurrentTurnEffect($cardID, $defPlayer);
        return "";
      case "TCC057":
        $numPitch = SearchCount(SearchPitch($currentPlayer));
        AddCurrentTurnEffect($cardID . "," . ($numPitch*2), $currentPlayer);
        return "";
      case "TCC058":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "TCC061":
        MZMoveCard($currentPlayer, "MYDISCARD:class=BARD;type=AA", "MYHAND", may:false, isSubsequent:false);
        return "";
      case "TCC064":
        PlayAura("WTR225", $otherPlayer);
        return "";
      case "TCC065":
        GainHealth(1, $otherPlayer);
        return "";
      case "TCC066": case "TCC067"://TODO: Add right Aura
        PlayAura("DTD232", $otherPlayer);
        return "";
      case "TCC068":
        Draw($otherPlayer);
        return "";
      case "TCC079":
        Draw($currentPlayer);
        return "";
      case "TCC083":
        AddCurrentTurnEffectFromCombat($cardID, $currentPlayer);
        break;
      default: return "";
    }
  }

?>
