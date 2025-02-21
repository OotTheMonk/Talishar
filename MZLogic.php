<?php

function MZDestroy($player, $lastResult, $effectController = "", $allArsenal = true)
{
  global $CombatChain, $chainLinks;
  $lastResultArr = explode(",", $lastResult);
  $otherPlayer = ($player == 1 ? 2 : 1);
  for ($i = count($lastResultArr) - 1; $i >= 0; $i--) {
    $mzIndex = explode("-", $lastResultArr[$i]);
    switch ($mzIndex[0]) {
      case "MYHAND":
        $lastResult = DiscardCard($player, $mzIndex[1], effectController: $effectController);
        break;
      case "THEIRHAND":
        $lastResult = DiscardCard($otherPlayer, $mzIndex[1], effectController: $effectController);
        break;
      case "MYCHAR":
        $lastResult = DestroyCharacter($player, $mzIndex[1]);
        break;
      case "THEIRCHAR":
        $lastResult = DestroyCharacter($otherPlayer, $mzIndex[1]);
        break;
      case "MYALLY":
        $lastResult = DestroyAlly($player, $mzIndex[1]);
        break;
      case "THEIRALLY":
        $lastResult = DestroyAlly($otherPlayer, $mzIndex[1]);
        break;
      case "MYAURAS":
        $lastResult = DestroyAura($player, $mzIndex[1]);
        break;
      case "THEIRAURAS":
        $lastResult = DestroyAura($otherPlayer, $mzIndex[1]);
        break;
      case "MYITEMS":
        $lastResult = DestroyItemForPlayer($player, $mzIndex[1]);
        break;
      case "THEIRITEMS":
        $lastResult = DestroyItemForPlayer($otherPlayer, $mzIndex[1]);
        break;
      case "MYARS":
        $lastResult = DestroyArsenal($player, $mzIndex[1], $effectController, $allArsenal);
        break;
      case "THEIRARS":
        $lastResult = DestroyArsenal($otherPlayer, $mzIndex[1], $effectController, $allArsenal);
        break;
      case "LANDMARK":
        $lastResult = DestroyLandmark($mzIndex[1]);
        break;
      case "COMBATCHAINLINK":
        $lastResult = $CombatChain->Remove($mzIndex[1]);
        break;
      case "COMBATCHAINATTACKS":
        $ind = intdiv($mzIndex[1],ChainLinksPieces());
        $lastResult = $chainLinks[$ind][0];
        $chainLinks[$ind][2] = 0;
        AddGraveyard($chainLinks[$ind][7], $player, "CC", $player);
        break;
      default:
        break;
    }
  }
  return $lastResult;
}

function MZRemove($player, $lastResult)
{
  $lastResultArr = explode(",", $lastResult);
  $otherPlayer = ($player == 1 ? 2 : 1);
  for ($i = count($lastResultArr) - 1; $i >= 0; --$i) {
    $mzIndex = explode("-", $lastResultArr[$i]);
    switch ($mzIndex[0]) {
      case "MYDISCARD":
        $lastResult = RemoveGraveyard($player, $mzIndex[1]);
        break;
      case "THEIRDISCARD":
        $lastResult = RemoveGraveyard($otherPlayer, $mzIndex[1]);
        break;
      case "MYBANISH":
        $lastResult = RemoveBanish($player, $mzIndex[1]);
        break;
      case "THEIRBANISH":
        $lastResult = RemoveBanish($otherPlayer, $mzIndex[1]);
        break;
      case "MYARS":
        $lastResult = RemoveArsenal($player, $mzIndex[1]);
        break;
      case "THEIRARS":
        $lastResult = RemoveArsenal($otherPlayer, $mzIndex[1]);
        break;
      case "MYPITCH":
        $lastResult = RemovePitch($player, $mzIndex[1]);
        break;
      case "THEIRPITCH":
        $lastResult = RemovePitch($otherPlayer, $mzIndex[1]);
        break;
      case "MYHAND":
        $lastResult = RemoveHand($player, $mzIndex[1]);
        break;
      case "THEIRHAND":
        $lastResult = RemoveHand($otherPlayer, $mzIndex[1]);
        break;
      case "MYAURAS":
        $lastResult = RemoveAura($player, $mzIndex[1], $mzIndex[1] + 6);
        break;
      case "THEIRAURAS":
        $lastResult = RemoveAura($otherPlayer, $mzIndex[1], $mzIndex[1] + 6);
        break;
      case "MYSOUL":
        $lastResult = RemoveSoul($player, $mzIndex[1]);
        break;
      case "THEIRSOUL":
        $lastResult = RemoveSoul($otherPlayer, $mzIndex[1]);
        break;
      case "MYDECK":
        $deck = new Deck($player);
        return $deck->Remove($mzIndex[1]);
      case "THEIRDECK":
        $deck = new Deck($otherPlayer);
        return $deck->Remove($mzIndex[1]);
      case "MYITEMS":
        $lastResult = RemoveItem($player, $mzIndex[1]);
        break;
      case "THEIRITEMS":
        $lastResult = RemoveItem($otherPlayer, $mzIndex[1]);
        break;
      default:
        break;
    }
  }
  return $lastResult;
}

function MZDiscard($player, $parameter, $lastResult)
{
  $lastResultArr = explode(",", $lastResult);
  $otherPlayer = ($player == 1 ? 2 : 1);
  $params = explode(",", $parameter);
  for ($i = count($lastResultArr) - 1; $i >= 0; $i--) {
    $mzIndex = explode("-", $lastResultArr[$i]);
    $cardOwner = (substr($mzIndex[0], 0, 2) == "MY" ? $player : $otherPlayer);
    $zone = &GetMZZone($cardOwner, $mzIndex[0]);
    $cardID = $zone[$mzIndex[1]];
    AddGraveyard($cardID, $cardOwner, $params[0], $params[1]);
    WriteLog(CardLink($cardID, $cardID) . " was discarded");
  }
  return $lastResult;
}

function MZReveal($player, $parameter, $lastResult)
{
  $lastResultArr = explode(",", $lastResult);
  $otherPlayer = ($player == 1 ? 2 : 1);
  for ($i = count($lastResultArr) - 1; $i >= 0; $i--) {
    $mzIndex = explode("-", $lastResultArr[$i]);
    $cardOwner = (substr($mzIndex[0], 0, 2) == "MY" ? $player : $otherPlayer);
    $zone = &GetMZZone($cardOwner, $mzIndex[0]);
    $cardID = $zone[$mzIndex[1]];
    WriteLog(CardLink($cardID, $cardID) . " was revealed");
  }
  return $lastResult;
}

function MZAddZone($player, $parameter, $lastResult)
{
  $lastResultArr = explode(",", $lastResult);
  $otherPlayer = ($player == 1 ? 2 : 1);
  $params = explode(",", $parameter);
  $deckIndexModifier = 0;
  if (str_contains($params[0], "-")) {
    $explodeArray = explode("-", $params[0]);
    $deckIndexModifier = $explodeArray[1];
    $params[0] = $explodeArray[0];
  }
  $cardIDs = [];
  for ($i = count($lastResultArr) - 1; $i >= 0; $i--) {
    $mzIndex = explode("-", $lastResultArr[$i]);
    $cardOwner = (substr($mzIndex[0], 0, 2) == "MY" ? $player : $otherPlayer);
    $zone = &GetMZZone($cardOwner, $mzIndex[0]);
    if(isset($zone[$mzIndex[1]])) array_push($cardIDs, $zone[$mzIndex[1]]);
  }
  for ($i = 0; $i < count($cardIDs); ++$i) {
    switch ($params[0]) {
      case "MYBANISH":
        if (count($params) < 4) array_push($params, $player);
        BanishCardForPlayer($cardIDs[$i], $player, $params[1], $params[2], isset($params[3]) ? $params[3] : "-");
        WriteLog(CardLink($cardIDs[$i], $cardIDs[$i]) . " was banished.");
        break;
      case "THEIRBANISH":
        if (count($params) < 4) array_push($params, $player);
        BanishCardForPlayer($cardIDs[$i], $otherPlayer, $params[1], isset($params[2]) ? $params[2] : "-", isset($params[3]) ? $params[3] : "-");
        WriteLog(CardLink($cardIDs[$i], $cardIDs[$i]) . " was banished.");
        break;
      case "MYHAND":
        AddPlayerHand($cardIDs[$i], $player, "-");
        break;
      case "MYTOPDECK":
        AddTopDeck($cardIDs[$i], $player, "-", $deckIndexModifier);
        break;
      case "MYBOTDECK":
        AddBottomDeck($cardIDs[$i], $player, "-");
        break;
      case "THEIRBOTDECK":
        AddBottomDeck($cardIDs[$i], $otherPlayer, "-");
        break;
      case "MYDISCARD":
        AddGraveyard($cardIDs[$i], $player, "-", isset($params[1]) ? $params[1] : "");
        break;
      case "THEIRDISCARD":
        AddGraveyard($cardIDs[$i], $otherPlayer, "-", isset($params[1]) ? $params[1] : "");
        break;
      case "MYARS":
        AddArsenal($cardIDs[$i], $player, $params[1], $params[2], isset($params[3]) ? $params[3] : "0");
        break;
      case "THEIRARS":
        AddArsenal($cardIDs[$i], $otherPlayer, $params[1], $params[2], isset($params[3]) ? $params[3] : "0");
        break;
      case "MYPERMANENTS":
        PutPermanentIntoPlay($player, $cardIDs[$i]);
        break;
      case "MYSOUL":
        AddSoul($cardIDs[$i], $player, $params[1]);
        break;
      case "MYITEMS":
        PutItemIntoPlayForPlayer($cardIDs[$i], $player);
        break;
      case "MYAURAS":
        PlayAura($cardIDs[$i], $player);
        PlayAbility($cardIDs[$i], "-", 0);
        break;
      case "THEIRHAND":
        AddPlayerHand($cardIDs[$i], $otherPlayer, "-");
        break;
      default:
        break;
    }
  }
  return $lastResult;
}

function MZUndestroy($player, $parameter, $lastResult)
{
  $lastResultArr = explode(",", $lastResult);
  $params = explode(",", $parameter);
  $otherPlayer = ($player == 1 ? 2 : 1);
  for ($i = count($lastResultArr) - 1; $i >= 0; $i--) {
    $mzIndex = explode("-", $lastResultArr[$i]);
    switch ($mzIndex[0]) {
      case "MYCHAR":
        UndestroyCharacter($player, $mzIndex[1]);
        break;
      default:
        break;
    }
  }
  return $lastResult;
}

function MZBanish($player, $parameter, $lastResult)
{
  $lastResultArr = explode(",", $lastResult);
  $params = explode(",", $parameter);
  $otherPlayer = ($player == 1 ? 2 : 1);
  for ($i = count($lastResultArr) - 1; $i >= 0; $i--) {
    $mzIndex = explode("-", $lastResultArr[$i]);
    $cardOwner = (substr($mzIndex[0], 0, 2) == "MY" ? $player : $otherPlayer);
    $zone = &GetMZZone($cardOwner, $mzIndex[0]);
    $modifier = count($params) > 1 ? $params[1] : "-";
    $banishedBy = count($params) > 2 ? $params[2] : "";
    if($params[0] == "-") {
      if (strpos($mzIndex[0], "MY") === 0) {
        $params[0] = substr($mzIndex[0], 2);
      } elseif (strpos($mzIndex[0], "THEIR") === 0) {
        $params[0] = substr($mzIndex[0], 5);
      } else {
        $params[0] = $mzIndex[0];
      }
    }
    BanishCardForPlayer($zone[$mzIndex[1]], $cardOwner, $params[0], $modifier, $banishedBy);
  }
  if (count($params) <= 3) WriteLog(CardLink($zone[$mzIndex[1]], $zone[$mzIndex[1]]) . " was banished.");
  return $lastResult;
}

function MZGainControl($player, $target)
{
  $targetArr = explode("-", $target);
  switch ($targetArr[0]) {
    case "MYITEMS":
    case "THEIRITEMS":
      StealItem(($player == 1 ? 2 : 1), $targetArr[1], $player);
      break;
    default:
      break;
  }
}

function MZBounce($player, $lastResult, $allArsenal = true)
{
  $lastResultArr = explode(",", $lastResult);
  $otherPlayer = ($player == 1 ? 2 : 1);
  for ($i = count($lastResultArr) - 1; $i >= 0; $i--) {
    $mzIndex = explode("-", $lastResultArr[$i]);
    switch ($mzIndex[0]) {
      case "MYAURAS":
        $auras = &GetAuras($player);
        $cardID = $auras[$mzIndex[1]];
        $cardOwner = substr($auras[$mzIndex[1]+9], 0, 5) == "THEIR"? $otherPlayer : $player;
        $lastResult = RemoveAura($player, $mzIndex[1]);
        AddPlayerHand($cardID, $cardOwner, "-");
        break;
      case "THEIRAURAS":
        $auras = &GetAuras($otherPlayer);
        $cardID = $auras[$mzIndex[1]];
        $cardOwner = substr($auras[$mzIndex[1]+9], 0, 5) == "THEIR"? $player : $otherPlayer;
        $lastResult = RemoveAura($otherPlayer, $mzIndex[1]);
        AddPlayerHand($cardID, $cardOwner, "-");
        break;
      default:
        break;
    }
  }
  return $lastResult;
}

function MZBottom($player, $lastResult, $allArsenal = true)
{
  $lastResultArr = explode(",", $lastResult);
  $otherPlayer = ($player == 1 ? 2 : 1);
  for ($i = count($lastResultArr) - 1; $i >= 0; $i--) {
    $mzIndex = explode("-", $lastResultArr[$i]);
    switch ($mzIndex[0]) {
      case "MYAURAS":
        $auras = &GetAuras($player);
        $cardID = $auras[$mzIndex[1]];
        $lastResult = RemoveAura($player, $mzIndex[1]);
        if (DelimStringContains(CardSubType($cardID), "Affliction")) {
          $player = ($player == 1 ? 2 : 1);
        }
        AddBottomDeck($cardID, $player, "-");
        break;
      case "THEIRAURAS":
        $auras = &GetAuras($otherPlayer);
        $cardID = $auras[$mzIndex[1]];
        $lastResult = RemoveAura($otherPlayer, $mzIndex[1]);
        if (DelimStringContains(CardSubType($cardID), "Affliction")) {
          $otherPlayer = ($otherPlayer == 1 ? 2 : 1);
        }
        AddBottomDeck($cardID, $otherPlayer, "-");
        break;
      default:
        break;
    }
  }
  return $lastResult;
}

function MZFreeze($target)
{
  global $currentPlayer;
  $pieces = explode("-", $target);
  $player = (substr($pieces[0], 0, 2) == "MY" ? $currentPlayer : ($currentPlayer == 1 ? 2 : 1));
  $zone = &GetMZZone($player, $pieces[0]);
  switch ($pieces[0]) {
    case "THEIRCHAR":
    case "MYCHAR":
      $zone[$pieces[1] + 8] = 1;
      break;
    case "THEIRALLY":
    case "MYALLY":
      $zone[$pieces[1] + 3] = 1;
      break;
    case "THEIRARS":
    case "MYARS":
      $zone[$pieces[1] + 4] = 1;
      break;
    case "THEIRITEMS":
    case "MYITEMS":
      $zone[$pieces[1] + 7] = 1;
      break;
    default:
      break;
  }
}

function IsFrozenMZ(&$array, $zone, $i)
{
  $offset = FrozenOffsetMZ($zone);
  if ($offset == -1) return false;
  return $array[$i + $offset] == "1";
}

function UnfreezeMZ($player, $zone, $index)
{
  $offset = FrozenOffsetMZ($zone);
  if ($offset == -1) return false;
  $array = &GetMZZone($player, $zone);
  $array[$index + $offset] = "0";
}

function FrozenOffsetMZ($zone)
{
  switch ($zone) {
    case "ARS":
    case "MYARS":
    case "THEIRARS":
      return 4;
    case "ALLY":
    case "MYALLY":
    case "THEIRALLY":
      return 3;
    case "CHAR":
    case "MYCHAR":
    case "THEIRCHAR":
      return 8;
    default:
      return -1;
  }
}

function MZIsPlayer($MZIndex)
{
  $indexArr = explode("-", $MZIndex);
  if (substr($indexArr[0], 0, 6) == "MYCHAR" || substr($indexArr[0], 0, 9) == "THEIRCHAR") return true;
  return false;
}

function MZPlayerID($me, $MZIndex)
{
  $indexArr = explode("-", $MZIndex);
  if (substr($indexArr[0], 0, 6) == "MYCHAR") return $me;
  if (substr($indexArr[0], 0, 9) == "THEIRCHAR") return ($me == 1 ? 2 : 1);
  return -1;
}

function GetMZCard($player, $MZIndex)
{
  $params = explode("-", $MZIndex);
  if (count($params) < 2) return "";
  if (substr($params[0], 0, 5) == "THEIR") $player = ($player == 1 ? 2 : 1);
  $zoneDS = &GetMZZone($player, $params[0]);
  $index = $params[1];
  if (isset($zoneDS[$index]) && ($zoneDS[$index] == "TRIGGER" || $zoneDS[$index] == "MELD")) $index += 2;
  if ($index == "" || !isset($zoneDS[$index])) return "";
  return $zoneDS[$index];
}

function MZStartTurnAbility($player, $MZIndex)
{
  $cardID = GetMZCard($player, $MZIndex);
  switch ($cardID) {
    case "thaw_red":
      AddDecisionQueue("PASSPARAMETER", $player, $MZIndex);
      AddDecisionQueue("MZREMOVE", $player, "-", 1);
      AddDecisionQueue("MULTIBANISH", $player, "GY,-", 1);
      AddDecisionQueue("FINDINDICES", $player, "thaw_red");
      AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
      AddDecisionQueue("AFTERTHAW", $player, "<-", 1);
      break;
    case "loyalty_beyond_the_grave_red":
      AddDecisionQueue("PASSPARAMETER", $player, $MZIndex);
      AddDecisionQueue("MZREMOVE", $player, "-", 1);
      AddDecisionQueue("MULTIBANISH", $player, "GY,-", 1);
      MZMoveCard($player, "MYDISCARD:isSameName=loyalty_beyond_the_grave_red", "MYBANISH", DQContext:"Choose a card named Loyalty Beyond the Grave to banish", isSubsequent:true);
      AddDecisionQueue("DRAW", $player, "-", 0);
      break;
    default:
      break;
  }
}

function MZMoveCard($player, $search, $where, $may = false, $isReveal = false, $silent = false, $isSubsequent = false, $DQContext = "")
{
  $otherPlayer = $player == 1 ? 2 : 1;
  if (str_contains($search, "DECK") && (SearchAurasForCard("channel_the_bleak_expanse_blue", $otherPlayer) != "" || SearchAurasForCard("channel_the_bleak_expanse_blue", $player) != "")) {
    WriteLog("Deck search prevented by " . CardLink("channel_the_bleak_expanse_blue", "channel_the_bleak_expanse_blue"));
    return "";
  }
  AddDecisionQueue("MULTIZONEINDICES", $player, $search, ($isSubsequent ? 1 : 0));
  if ($DQContext != "") AddDecisionQueue("SETDQCONTEXT", $player, $DQContext);
  if ($may) AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
  else AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
  AddDecisionQueue("MZSETDQVAR", $player, "0", 1);

  if ($silent);
  else if ($isReveal) AddDecisionQueue("REVEALCARDS", $player, "-", 1);
  else AddDecisionQueue("WRITELOG", $player, "Card chosen: <0>", 1);

  if ($where != "") AddDecisionQueue("MZADDZONE", $player, $where, 1);
  AddDecisionQueue("MZREMOVE", $player, "-", 1);
}

function MZChooseAndDestroy($player, $search, $may = false, $context = "")
{
  AddDecisionQueue("MULTIZONEINDICES", $player, $search);
  if ($context != "") AddDecisionQueue("SETDQCONTEXT", $player, $context);
  if ($may) AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
  else AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
  AddDecisionQueue("MZDESTROY", $player, "-", 1);
}

function MZChooseAndBanish($player, $search, $fromMod, $may = false)
{
  AddDecisionQueue("MULTIZONEINDICES", $player, $search);
  if ($may) AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
  else AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
  AddDecisionQueue("MZBANISH", $player, $fromMod, 1);
  AddDecisionQueue("MZREMOVE", $player, "-", 1);
}

function MZChooseAndBounce($player, $search, $may = false, $context = "")
{
  AddDecisionQueue("MULTIZONEINDICES", $player, $search);
  if ($context != "") AddDecisionQueue("SETDQCONTEXT", $player, $context);
  if ($may) AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
  else AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
  AddDecisionQueue("MZBOUNCE", $player, "-", 1); //Goes the the Owner's hand
}

function MZLastIndex($player, $zone)
{
  switch ($zone) {
    case "MYBANISH":
      $banish = &GetBanish($player);
      return "MYBANISH-" . count($banish) - BanishPieces();
    default:
      return "";
  }
}

function MZSwitchPlayer($zoneStr)
{
  $zoneArr = explode(",", $zoneStr);
  $zoneStr = "";
  foreach ($zoneArr as $zone) {
    if (str_contains($zone, "MY")) $zone = str_replace("MY", "THEIR", $zone);
    else if (str_contains($zone, "THEIR")) $zone = str_replace("THEIR", "MY", $zone);

    if ($zoneStr != "") $zoneStr .= ",";
    $zoneStr .= $zone;
  }
  return $zoneStr;
}
