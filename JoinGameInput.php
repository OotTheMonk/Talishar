<?php

  include "Libraries/HTTPLibraries.php";

  $gameName=$_GET["gameName"];
  if(!IsGameNameValid($gameName)) { echo("Invalid game name."); exit; }
  $playerID=$_GET["playerID"];
  $deck=$_GET["deck"];
  $decklink=$_GET["fabdb"];
  $decksToTry = TryGet("decksToTry");
  $set=TryGet("set");

  if($decklink == "" && $deck == "")
  {
    switch ($decksToTry) {
      case '1': $decklink = "https://fabdb.net/decks/pExqQzqV"; break;
      case '2': $decklink = "https://fabdb.net/decks/dLAwdlEX"; break;
      case '3': $decklink = "https://fabdb.net/decks/zLNlGaOr"; break;
      default: $decklink = "https://fabdb.net/decks/pExqQzqV"; break;
    }
  }

  if($deck == "" && !IsDeckLinkValid($decklink)) {
      echo '<b>' . "Deck link is not valid: " . $decklink . '</b>';
      exit;
  }
  //TODO: Validate $deck

  include "HostFiles/Redirector.php";
  include "CardDictionary.php";
  include "MenuFiles/ParseGamefile.php";

  if($decklink != "")
  {
    $decklink = explode("/", $decklink);
    $slug = $decklink[count($decklink)-1];
    $apiLink = "https://api.fabdb.net/decks/" . $slug;
    $apiDeck = @file_get_contents($apiLink);
    if($apiDeck === FALSE) { echo  '<b>' . "Deck link is not valid: " . implode("/", $decklink) . '</b>'; exit; }
    $deckObj = json_decode($apiDeck);
    $cards = $deckObj->{'cards'};
    $deckCards = "";
    $sideboardCards = "";
    $headSideboard = ""; $chestSideboard = ""; $armsSideboard = ""; $legsSideboard = ""; $offhandSideboard = "";
    $unsupportedCards = "";
    $character = ""; $head = ""; $chest = ""; $arms = ""; $legs = ""; $offhand = "";
    $weapon1 = "";
    $weapon2 = "";
    $weaponSideboard = "";
    for($i=0; $i<count($cards); ++$i)
    {
      $count = $cards[$i]->{'total'};
      $numSideboard = $cards[$i]->{'sideboardTotal'};
      $printings = $cards[$i]->{'printings'};
      $printing = $printings[0];
      $sku = $printing->{'sku'};
      $id = $sku->{'sku'};
      $id = explode("-", $id)[0];
      $id = GetAltCardID($id);
      $cardType = CardType($id);
      if($cardType == "") //Card not supported, error
      {
          if($unsupportedCards != "") $unsupportedCards .= " ";
          $unsupportedCards .= $id;
      }
      else if($cardType == "C")
      {
        $character = $id;
      }
      else if($cardType == "W")
      {
        for($j=0; $j<($count-$numSideboard); ++$j)
        {
          if($weapon1 == "") $weapon1 = $id;
          else if($weapon2 == "") $weapon2 = $id;
          else
          {
            if($weaponSideboard != "") $weaponSideboard .= " ";
            $weaponSideboard .= $id;
          }
        }
        for($j=0; $j<$numSideboard; ++$j)
        {
            if($weaponSideboard != "") $weaponSideboard .= " ";
            $weaponSideboard .= $id;
        }
      }
      else if($cardType == "E")
      {
        $subtype = CardSubType($id);
        if($numSideboard == 0)
        {
          switch($subtype)
          {
            case "Head": if($head == "") $head = $id; else { if($headSideboard != "") $headSideboard .= " "; $headSideboard .= $id; } break;
            case "Chest": if($chest == "") $chest = $id; else { if($chestSideboard != "") $chestSideboard .= " "; $chestSideboard .= $id; } break;
            case "Arms": if($arms == "") $arms = $id; else { $armsSideboard .= " "; $armsSideboard .= $id; } break;
            case "Legs": if($legs == "") $legs = $id; else { if($legsSideboard != "") $legsSideboard .= " "; $legsSideboard .= $id; }break;
            case "Off-Hand": if($offhand == "") $offhand = $id; else { if($offhandSideboard != "") $offhandSideboard .= " "; $offhandSideboard .= $id; } break;
            default: break;
          }
        }
        else {
          switch($subtype)
          {
            case "Head": if($headSideboard != "") $headSideboard .= " "; $headSideboard .= $id; break;
            case "Chest": if($chestSideboard != "") $chestSideboard .= " "; $chestSideboard .= $id; break;
            case "Arms": if($armsSideboard != "") $armsSideboard .= " "; $armsSideboard .= $id; break;
            case "Legs": if($legsSideboard != "") $legsSideboard .= " "; $legsSideboard .= $id; break;
            case "Off-Hand": if($offhandSideboard != "") $offhandSideboard .= " "; $offhandSideboard .= $id; break;
            default: break;
          }
        }
      }
      else
      {
        for($j=0; $j<($count-$numSideboard); ++$j)
        {
          if($deckCards != "") $deckCards .= " ";
          $deckCards .= $id;
        }
        for($j=0; $j<$numSideboard; ++$j)
        {
          if($sideboardCards != "") $sideboardCards .= " ";
          $sideboardCards .= $id;
        }
      }
    }

    if($unsupportedCards != "")
    {
      echo("The following cards are not yet supported: " . $unsupportedCards);
    }
    //We have the decklist, now write to file
    $filename = "./Games/" . $gameName . "/p" . $playerID . "Deck.txt";
    $deckFile = fopen($filename, "a");
    $charString = $character;
    if($weapon1 != "") $charString .= " " . $weapon1;
    if($weapon2 != "") $charString .= " " . $weapon2;
    if($offhand != "") $charString .= " " . $offhand;
    if($head != "") $charString .= " " . $head;
    if($chest != "") $charString .= " " . $chest;
    if($arms != "") $charString .= " " . $arms;
    if($legs != "") $charString .= " " . $legs;
    fwrite($deckFile, $charString . "\r\n");
    fwrite($deckFile, $deckCards . "\r\n");
    fwrite($deckFile, $headSideboard . "\r\n");
    fwrite($deckFile, $chestSideboard . "\r\n");
    fwrite($deckFile, $armsSideboard . "\r\n");
    fwrite($deckFile, $legsSideboard . "\r\n");
    fwrite($deckFile, $offhandSideboard . "\r\n");
    fwrite($deckFile, $weaponSideboard . "\r\n");
    fwrite($deckFile, $sideboardCards);
    fclose($deckFile);
  }
  else
  {
    $deckOptions = explode("-", $deck);
    if($deckOptions[0] == "DRAFT")
    {
      if($set == "WTR") $deckFile = "./WTRDraftFiles/Games/" . $deckOptions[1] . "/LimitedDeck.txt";
      else $deckFile = "./DraftFiles/Games/" . $deckOptions[1] . "/LimitedDeck.txt";
    }
    else
    {
      switch($deck)
      {
        case "oot": $deckFile = "p1Deck.txt"; break;
        case "shawn": $deckFile = "shawnTAD.txt"; break;
        case "dori": $deckFile = "Dori.txt"; break;
        case "katsu": $deckFile = "Katsu.txt"; break;
        default: $deckFile = "p1Deck.txt"; break;
      }
    }
    copy($deckFile,"./Games/" . $gameName . "/p" . $playerID ."Deck.txt");
  }

  if($playerID == 2)
  {
    $filename = "./Games/" . $gameName . "/GameFile.txt";
    $gameFile = fopen($filename, "w");

    $attemptCount = 0;
    while(!flock($gameFile, LOCK_EX) && $attemptCount < 5) {  // acquire an exclusive lock
      sleep(1);
      ++$attemptCount;
    }
    if($attemptCount >= 5)
    {
      header("Location: " . $redirectorPath . "MainMenu.php");//We never actually got the lock
    }
    flock($gameFile, LOCK_UN);    // release the lock
    fclose($gameFile);
    $gameStatus = 4;
    include "MenuFiles/WriteGamefile.php";

    //fwrite($gameFile, "\r\n");
    //fwrite($gameFile, "1\r\n2\r\n4");
  }

  header("Location: " . $redirectPath . "/GameLobby.php?gameName=$gameName&playerID=$playerID");

function GetAltCardID($cardID)
{
  switch($cardID)
  {
    case "BOL002": return "MON405";
    case "BOL006": return "MON400";
    case "CHN002": return "MON407";
    case "CHN006": return "MON401";
    case "LEV002": return "MON406";
    case "PSM002": return "MON404";
    case "PSM007": return "MON402";
  }
  return $cardID;
}

?>
