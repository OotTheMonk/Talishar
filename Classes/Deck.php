<?php
// Deck Class to handle interactions involving the deck

class Deck {

  // Properties
  private $deck = [];
  private $playerID;

  // Constructor
  function __construct($playerID) {
    $this->deck = &GetDeck($playerID);
    $this->playerID = $playerID;
  }

  // Methods
  function Empty() {
    return count($this->deck) == 0;
  }

  function RemainingCards() {
    // Code to return the number of remaining cards in the deck
    return count($this->deck);
  }

  function Remove($indices) {
    $indexArr = explode(",", $indices);
    $cardIDs = "";
    for($i=count($indexArr)-1; $i>= 0; --$i) {
      if($cardIDs != "") $cardIDs .= ",";
      $cardIDs .= $this->deck[$indexArr[$i]];
      unset($this->deck[$indexArr[$i]]);
      $this->deck = array_values($this->deck);
    }
    return $cardIDs;
  }

  function Reveal($revealCount=1) {
    // Code the reveal x number of cards from the top of the deck
    if(CanRevealCards($this->playerID)) {
      if($this->RemainingCards() > 0) {
        for($revealedCards = 0; $revealedCards < $revealCount && count($this->deck) > $revealedCards; $revealedCards++) {
          WriteLog("Player " . $this->playerID . " reveals " . CardLink($this->deck[$revealedCards], $this->deck[$revealedCards]));
          AddEvent("REVEAL", $this->deck[$revealedCards]);
        }
        if(SearchLandmark("korshem_crossroad_of_elements")) KorshemRevealAbility($this->playerID);
        return true;
      } else {
        WriteLog("Your deck is empty. Nothing was revealed.");
        return false;
      }
    }
    return false;
  }

  function Top($remove = false, $amount = 1)
  {
    $rv = "";
    for($i=0; $i<$amount && count($this->deck) > ($remove ? 0 : $i); ++$i)
    {
      if($rv != "") $rv .= ",";
      $rv .= ($remove ? array_shift($this->deck) : $this->deck[$i]);
    }
    return $rv;
  }

  function BanishTop($modifier = "-", $banishedBy = "", $amount=1) {
    global $currentPlayer;
    if($this->Empty()) return "";
    if($banishedBy == "") $banishedBy = $currentPlayer;
    for($i=0; $i<$amount; ++$i)
    {
      $cardID = $this->Remove(0);
      $cardType = CardType($cardID);
      if($modifier == "TCC" && $cardType != "AR" && $cardType != "I" && $cardType != "AA" && !CanPlayAsInstant($cardID)) $modifier = "-";
      WriteLog(CardLink($cardID, $cardID). " was banished.");
      BanishCardForPlayer($cardID, $this->playerID, "DECK", $modifier, $banishedBy);
    }
    return $cardID;
  }

  function AddTop($cardID, $from="GY")
  {
    array_unshift($this->deck, $cardID);
    return $cardID;
  }

  function AddBottom($cardID, $from="GY")
  {
    array_push($this->deck, $cardID);
    return $cardID;
  }

  function Opt($topCardID, $bottomCardID, $from="GY")
  {
    $this->deck = array_merge($topCardID, $this->deck);
    $this->deck = array_merge($this->deck, $bottomCardID);
    return true;
  }
}
