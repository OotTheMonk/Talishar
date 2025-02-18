<?php

include "Search.php";
include "CardLogic.php";
include "AuraAbilities.php";
include "ItemAbilities.php";
include "AllyAbilities.php";
include "PermanentAbilities.php";
include "LandmarkAbilities.php";
include "CharacterAbilities.php";
include "WeaponLogic.php";
include "MZLogic.php";
include "Classes/Banish.php";
include "Classes/Card.php";
include "Classes/CombatChain.php";
include "Classes/Deck.php";
include "Classes/Discard.php";
include "DecisionQueue/DecisionQueueEffects.php";
include "CurrentEffectAbilities.php";
include "CombatChain.php";

function DecisionQueueStaticEffect($phase, $player, $parameter, $lastResult)
{
  global $redirectPath, $playerID, $gameName, $currentPlayer, $combatChain, $CombatChain, $defPlayer, $combatChainState, $EffectContext, $chainLinks;
  global $CS_NumCharged, $otherPlayer, $CS_NumFusedEarth, $CS_NumFusedIce, $CS_NumFusedLightning, $CS_NextNAACardGoAgain, $CCS_AttackTarget;
  global $dqVars, $mainPlayer, $lastPlayed, $dqState, $CS_AbilityIndex, $CS_CharacterIndex, $CS_AdditionalCosts, $CS_AlluvionUsed, $CS_MaxQuellUsed;
  global $CS_ArcaneTargetsSelected, $inGameStatus, $CS_ArcaneDamageDealt, $MakeStartTurnBackup, $CCS_AttackTargetUID, $MakeStartGameBackup;
  global $CCS_AttackNumCharged, $layers, $CS_DamageDealt, $currentTurnEffects, $CCS_LinkBaseAttack;
  $rv = "";
  switch ($phase) {
    case "FINDINDICES":
      BuildMainPlayerGamestate();
      $parameters = explode(",", $parameter);
      $parameter = $parameters[0];
      if (count($parameters) > 1) $subparam = $parameters[1];
      else $subparam = "";
      switch ($parameter) {
        case "TRAPS":
          $rv = GetTrapIndices($player);
          break;
        case "GETINDICES":
          $rv = GetIndices($subparam);
          break;
        case "ARCANETARGET":
          $rv = GetArcaneTargetIndices($player, $subparam);
          break;
        case "DAMAGEPREVENTION":
          $rv = GetDamagePreventionIndices($player, $subparam);
          break;
        case "DAMAGEPREVENTIONTARGET":
          $rv = GetDamagePreventionTargetIndices();
          break;
        case "mugenshi_release_yellow":
          $rv = SearchDeckForCard($player, "lord_of_wind_blue");
          if ($rv != "") $rv = count(explode(",", $rv)) . "-" . $rv;
          break;
        case "lord_of_wind_blue":
          $rv = LordOfWindIndices($player);
          if ($rv != "") $rv = count(explode(",", $rv)) . "-" . $rv;
          break;
        case "lesson_in_lava_yellow":
          $rv = SearchDeck($player, "", "", $lastResult, 0, "WIZARD");
          break;
        case "rattle_bones_red":
          $rv = SearchDiscard($player, "AA", class:"RUNEBLADE");
          break;
        case "DECK":
          $rv = SearchDeck($player);
          break;
        case "TOPDECK":
          $deck = new Deck($player);
          if (!$deck->Empty()) $rv = "0";
          break;
        case "DECKTOPXINDICES":
          $deck = new Deck($player);
          $amount = ($subparam > $deck->RemainingCards() ? $deck->RemainingCards() : $subparam);
          $rv = GetIndices($amount);
          break;
        case "DECKTOPXREMOVE":
          $deck = new Deck($player);
          $rv = $deck->Top(true, $subparam);
          break;
        case "PERMSUBTYPE":
          if (DelimStringContains($subparam, "Aura")) $rv = SearchAura($player, "", $subparam);
          else $rv = SearchPermanents($player, "", $subparam);
          break;
        case "MZSTARTTURN":
          $rv = MZStartTurnIndices();
          break;
        case "HAND":
          $hand = &GetHand($player);
          $rv = GetIndices(count($hand), 0, HandPieces());
          break;
        //This one requires CHOOSEMULTIZONECANCEL
        case "HANDPITCH":
          $rv = SearchHand($player, "", "", -1, -1, "", "", false, false, $subparam);
          break;
        case "HANDCLASS":
          $rv = SearchHand($player, class:$subparam);
          break;
        case "HANDMINPOWER":
          $rv = SearchHand($player, minAttack: $subparam);
          break;
        case "HANDACTIONMAXCOST":
          $rv = CombineSearches(SearchHand($player, "A", "", $subparam), SearchHand($player, "AA", "", $subparam));
          break;
        case "MULTIHAND":
          $hand = &GetHand($player);
          if (count($hand) == 0) $rv = "";
          else $rv = count($hand) . "-" . GetIndices(count($hand), 0, HandPieces());
          break;
        case "MULTIBANISH":
          $banish = new Banish($player);
          $rv = $subparam . "-" . GetIndices($banish->NumCards() * BanishPieces(), 0, BanishPieces());
          break;
        case "MULTIHANDAA":
          $search = SearchHand($player, "AA");
          $rv = SearchCount($search) . "-" . $search;
          break;
        case "CROUCHINGTIGERHAND":
          $search = SearchHandForCardName($player, "Crouching Tiger");
          $rv = SearchCount($search) . "-" . $search;
          break;
        case "ARSENAL":
          $arsenal = &GetArsenal($player);
          $rv = GetIndices(count($arsenal), 0, ArsenalPieces());
          break;
        //These are needed because MZ search doesn't have facedown parameter
        case "ARSENALDOWN":
          $rv = GetArsenalFaceDownIndices($player);
          break;
        case "ITEMSMAX":
          $rv = SearchItems($player, "", "", $subparam);
          break;
        case "EQUIP":
          $rv = GetEquipmentIndices($player);
          break;
        case "EQUIP0":
          $rv = GetEquipmentIndices($player, 0, 0);
          break;
        case "EQUIP1":
          $rv = GetEquipmentIndices($player, 1, 0);
          break;
        case "EQUIPONCC":
          $rv = GetEquipmentIndices($player, onCombatChain: true);
          break;
        case "EQUIPCARD":
          $rv = SearchCharacterForCards($subparam, $player);
          break;
        case "CCAA":
          $rv = SearchCombatChainLink($player, "AA");
          break;
        case "CCDEFLESSX":
          $rv = SearchCombatChainLink($player, "", "", -1, -1, "", "", false, false, -1, false, -1, $subparam);
          if ($rv[0] == "0" && (strlen($rv) == 0 || $rv[1] == ",")) $rv = substr($rv, 2);
          break;
        case "MYHANDARROW":
          $rv = SearchHand($player, "", "Arrow");
          break;
        case "MYDISCARDARROW":
          $rv = SearchDiscard($player, "", "Arrow");
          break;
        case "MULTIACTIONSBANISH":
          $index = CombineSearches(SearchBanish($player, "AA"), SearchBanish($player, "A"));
          $rv = RemoveDuplicateCards($player, $index, GetBanish($player));
          break;
        case "MULTITRAPSBANISH":
          $rv = SearchDiscard($player, subtype: "Trap");
          break;
        case "MULTITRAPSHAND":
          $rv = SearchDeck($player, subtype: "Trap");
          break;
        case "GY":
          $discard = &GetDiscard($player);
          $rv = GetIndices(count($discard), 0, DiscardPieces());
          break;
        case "WEAPON":
          $rv = WeaponIndices($player, $player, $subparam);
          break;
        case "herald_of_rebirth_red":
        case "herald_of_rebirth_yellow":
        case "herald_of_rebirth_blue":
          $rv = SearchDiscard($player, "", "", -1, -1, "", "", false, true);
          break;
        case "SOULINDICES":
          $soul = &GetSoul($player);
          $rv = GetIndices(count($soul), 1, SoulPieces());
          break;
        case "beacon_of_victory_yellow-2":
          $rv = CombineSearches(SearchDeck($player, "A", "", $lastResult), SearchDeck($player, "AA", "", $lastResult));
          break;
        case "invert_existence_blue":
          $rv = UpTo2FromOpposingGraveyardIndices($player);
          break;//This makes sense because it's a multi
        case "pulse_of_candlehold_yellow":
          $rv = PulseOfCandleholdIndices($player);
          break;//This makes sense because it's a multi
        case "summerwood_shelter_red":
        case "summerwood_shelter_yellow":
        case "summerwood_shelter_blue":
          $rv = MZToIndices(SearchMultizone($player, "COMBATCHAINLINK:type=A;talent=EARTH,ELEMENTAL&COMBATCHAINLINK:type=AA;talent=EARTH,ELEMENTAL"));
          break;
        case "amulet_of_havencall_blue":
          $rv = SearchDeckForCard($player, "rally_the_rearguard_red", "rally_the_rearguard_yellow", "rally_the_rearguard_blue");
          break;
        case "HEAVE":
          $rv = HeaveIndices();
          break;
        case "BRAVOSTARSHOW":
          $rv = BravoStarOfTheShowIndices();
          break;
        case "DECKAURAMAXCOST":
          $rv = SearchDeck($player, "", "Aura", $subparam);
          break;
        case "CROWNOFREFLECTION":
          $rv = SearchHand($player, "", "Aura", -1, -1, "ILLUSIONIST");
          break;
        case "LIFEOFPARTY":
          $rv = LifeOfThePartyIndices();
          break;
        case "COALESCENTMIRAGE":
          $rv = SearchHand($player, "", "Aura", -1, 0, "ILLUSIONIST");
          break;
        case "MASKPOUNCINGLYNX":
          $rv = SearchDeck($player, "AA", "", -1, -1, "", "", false, false, -1, false, 2);
          break;
        case "SHATTER":
          $rv = ShatterIndices($player, $subparam);
          break;
        case "KNICKKNACK":
          $rv = KnickKnackIndices($player);
          break;
        case "CASHOUT":
          $rv = CashOutIndices($player);
          break;
        case "thaw_red":
          $rv = ThawIndices($player);
          break;
        case "QUELL":
          $rv = QuellIndices($player);
          break;
        case "SOUL":
          $rv = SearchSoul($player, talent: "LIGHT");
          break;
        case "spectral_shield":
          $rv = SearchAurasForCard("spectral_shield", $player);
          break;
        case "no_fear_red":
          $hand = &GetHand($player);
          $rv = [];
          for ($i = 0; $i < count($hand); $i += HandPieces()) {
            if (CardType($hand[$i]) == "AA" && ModifiedAttackValue($hand[$i], $player, "HAND", "no_fear_red") >= 6) array_push($rv, $i);
          }
          $rv = implode(",", $rv);
          $rv = SearchCount($rv) . "-" . $rv;
          break;
        case "sacred_art_undercurrent_desires_blue":
          $rv = UpTo2FromOpposingGraveyardIndices($player);
          break;
        default:
          $rv = "";
          break;
      }
      return ($rv == "" ? "PASS" : $rv);
    case "MULTIZONEINDICES":
      $rv = SearchMultizone($player, $parameter);
      return ($rv == "" ? "PASS" : $rv);
    case "BLOCKLESS0HAND":
      $hand = &GetHand($player);
      $countHand = count($hand);
      $cardList = "";
      for ($i = 0; $i < $countHand; $i++) {
        if (BlockValue($hand[$i]) < 0) {
          if ($cardList != "") $cardList = $cardList . ",";
          $cardList = $cardList . $i;
        }
      }
      $searchResult = SearchMultiZoneFormat($cardList, "THEIRHAND");
      $rv = CombineSearches($rv, $searchResult);
      return ($rv == "" ? "PASS" : $rv);
    case "PUTPLAY":
      $subtype = CardSubType($lastResult);
      if ($subtype == "Item") {
        if ($parameter == "False") PutItemIntoPlayForPlayer($lastResult, $player, mainPhase: $parameter);
        else PutItemIntoPlayForPlayer($lastResult, $player, ($parameter != "-" ? $parameter : 0));
      } else if (DelimStringContains($subtype, "Aura")) {
        PlayAura($lastResult, $player, effectController:$parameter);
        PlayAbility($lastResult, "-", 0);
      }
      return $lastResult;
    case "SEARCHCOMBATCHAIN":
      $cardIDList = "";
      $cardType = "";
      if ($parameter != "-") $cardType = $parameter;
      $otherPlayer = $player == 1 ? 2 : 1;
      $cardIDList = GetChainLinkCardIDs($otherPlayer, $cardType, exclCardTypes: "C");
      for ($i = 0; $i < count($chainLinks); ++$i) {
        for ($j = 0; $j < count($chainLinks[$i]); $j += ChainLinksPieces()) {
          if ($chainLinks[$i][$j + 1] != $otherPlayer || $chainLinks[$i][$j + 2] != "1") continue;
          if ($cardType != "" && !TypeContains($chainLinks[$i][$j], $cardType, $player)) continue;
          if ($cardIDList != "") $cardIDList .= ",";
          $cardIDList .= $chainLinks[$i][$j];
        }
      }
      return $cardIDList != "" ? $cardIDList : "PASS";
    case "PLAYABILITY":
      PlayAbility($lastResult, "-", 0);
      return $lastResult;
    case "DRAW":
      return Draw($player);
    case "MULTIBANISH":
      if ($lastResult == "") return $lastResult;
      $cards = explode(",", $lastResult);
      $params = explode(",", $parameter);
      if (count($params) < 3) array_push($params, "");
      $mzIndices = "";
      for ($i = 0; $i < count($cards); ++$i) {
        $index = BanishCardForPlayer($cards[$i], $player, $params[0], isset($params[1]) ? $params[1] : "-", isset($params[2]) ? $params[2] : "");
        if ($mzIndices != "") $mzIndices .= ",";
        $mzIndices .= "BANISH-" . $index;
      }
      $dqState[5] = $mzIndices;
      return $lastResult;
    case "REMOVECOMBATCHAIN":
      return $CombatChain->Remove($lastResult);
    case "COMBATCHAINPOWERMODIFIER":
      CombatChainPowerModifier($lastResult, $parameter);
      if ($parameter > 0) writelog(CardLink($combatChain[$lastResult], $combatChain[$lastResult]) . " gets +" . $parameter . " power");
      else if ($parameter < 0) writelog(CardLink($combatChain[$lastResult], $combatChain[$lastResult]) . " gets " . $parameter . " power");
      return $lastResult;
      case "COMBATCHAINDEFENSEMODIFIER":
        if ($parameter < 0) {
          $defense = BlockingCardDefense($lastResult);
          if ($parameter < $defense * -1) $parameter = $defense * -1;
        }
        $combatChain[$lastResult + 6] += $parameter;
        switch ($combatChain[0]) {
          case "zephyr_needle":
          case "zephyr_needle":
          EvaluateCombatChain($totalAttack, $totalBlock);
        }
        if ($parameter > 0) writelog(CardLink($combatChain[$lastResult], $combatChain[$lastResult]) . " gets +" . $parameter . " defense");
        else if ($parameter < 0) writelog(CardLink($combatChain[$lastResult], $combatChain[$lastResult]) . " gets " . $parameter . " defense");
        return $lastResult;
    case "HALVEBASEDEFENSE":
      $combatChain[$lastResult + 6] -= floor(BlockValue($combatChain[$lastResult]) / 2);
      return $lastResult;
    case "PUTCOMBATCHAINDEFENSE0":
      $index = GetCombatChainIndex($lastResult, $player);
      $combatChain[$index + 6] -= BlockValue($lastResult);
      return $lastResult;
    case "PUTINANYORDER":
      $deck = new Deck($player == 1 ? 2 : 1);
      $reorderCards = "";
      for ($i = 0; $i < $parameter; ++$i) {
        if ($deck->RemainingCards() > 0) {
          if ($reorderCards != "") $reorderCards .= ",";
          $reorderCards .= $deck->Top(remove: true);
        }
      }
      if ($reorderCards != "") {
        PrependDecisionQueue("CHOOSETOPOPPONENT", $player, $reorderCards);
        PrependDecisionQueue("SETDQCONTEXT", $player, "Choose a card to put on top of their deck");
      }
      return "";
    case "COMBATCHAINCHARACTERDEFENSEMODIFIER":
      $character = &GetPlayerCharacter($player);
      $index = FindCharacterIndex($player, $combatChain[$parameter]);
      $character[$index + 4] += $lastResult;
      return $lastResult;
    case "REMOVEDISCARD":
      $discard = &GetDiscard($player);
      $cardID = $discard[$lastResult];
      unset($discard[$lastResult + 1]);
      unset($discard[$lastResult]);
      $discard = array_values($discard);
      return $cardID;
    case "REMOVEMYHAND":
      $hand = &GetHand($player);
      $cardID = $hand[$lastResult];
      unset($hand[$lastResult]);
      $hand = array_values($hand);
      return $cardID;
    case "HANDCARD":
      $hand = &GetHand($player);
      $cardID = $hand[$lastResult];
      return $cardID;
    case "MULTIREMOVEDISCARD":
      $discard = &GetDiscard($player);
      $cards = "";
      if (!is_array($lastResult)) $lastResult = explode(",", $lastResult);
      $cardsRemoved = "";
      for ($i = 0; $i < count($lastResult); ++$i) {
        if ($cards != "") $cards .= ",";
        $cards .= $discard[$lastResult[$i]];
        if ($parameter == "1") WriteLog(CardLink($discard[$lastResult[$i]], $discard[$lastResult[$i]]));
        unset($discard[$lastResult[$i] + 1]);
        unset($discard[$lastResult[$i]]);
      }
      $discard = array_values($discard);
      return $cards;
    case "MULTIBANISHSOUL":
      if (!is_array($lastResult)) $lastResult = explode(",", $lastResult);
      for ($i = count($lastResult) - 1; $i >= 0; --$i) BanishFromSoul($player, $lastResult[$i]);
      return $lastResult;
    case "ADDHAND":
      AddPlayerHand($lastResult, $player, "-");
      return $lastResult;
    case "ADDHANDOWNER":
      $otherPlayer = $player == 1 ? 2 : 1;
      if (substr($combatChain[$lastResult + 2], 0, 5) == "THEIR") AddPlayerHand($combatChain[$lastResult], $otherPlayer, "CC");
      else AddPlayerHand($combatChain[$lastResult], $player, "CC");
      return $lastResult;
    case "ADDMYPITCH":
      $pitch = &GetPitch($player);
      WriteLog("Player " . $player . " pitched " . CardLink($lastResult, $lastResult));
      array_push($pitch, $lastResult);
      return $lastResult;
    case "PITCHABILITY":
      PitchAbility($lastResult);
      return $lastResult;
    case "ADDARSENAL":
      $params = explode("-", $parameter);
      $from = (count($params) > 0 ? $params[0] : "-");
      $facing = (count($params) > 1 ? $params[1] : "DOWN");
      $deck = new Deck($player);
      if (!ArsenalFull($player)) {
        AddArsenal($deck->Top(), $player, $from, $facing);
        return $lastResult;
      } else {
        writelog("Player $player arsenal is full, no card was puit in arsenal");
        return "PASS";
      }
    case "ADDARSENALFROMDECK": //needed for schism so pass doesn't skip the other player
      $params = explode("-", $parameter);
      $from = (count($params) > 0 ? $params[0] : "-");
      $facing = (count($params) > 1 ? $params[1] : "DOWN");
      $deck = new Deck($player);
      if (!ArsenalFull($player) && $deck->RemainingCards() > 0) {
        AddArsenal($deck->Top(), $player, $from, $facing);
        RemoveDeck($player, 0);
        return $lastResult;
      } else {
        return $lastResult;
      }
    case "TURNCHARACTERFACEUP":
      $character = &GetPlayerCharacter($player);
      $character[$lastResult + 12] = "UP";
      return $lastResult;
    case "TURNARSENALFACEUP":
      $arsenal = &GetArsenal($player);
      $arsenal[$parameter + 1] = "UP";
      ArsenalTurnFaceUpAbility($arsenal[$parameter], $player);
      return $parameter;
    case "REMOVEARSENAL":
      $index = $lastResult;
      $arsenal = &GetArsenal($player);
      $cardToReturn = $arsenal[$index];
      RemoveArsenalEffects($player, $cardToReturn, $arsenal[$index + 5]);
      for ($i = $index + ArsenalPieces() - 1; $i >= $index; --$i) {
        unset($arsenal[$i]);
      }
      $arsenal = array_values($arsenal);
      return $cardToReturn;
    case "MULTIADDHAND":
      $cards = explode(",", $lastResult);
      $hand = &GetHand($player);
      $log = "";
      for ($i = 0; $i < count($cards); ++$i) {
        if ($parameter == "1") {
          if ($log != "") $log .= ", ";
          if ($i != 0 && $i == count($cards) - 1) $log .= "and ";
          $log .= CardLink($cards[$i], $cards[$i]);
        }
        array_push($hand, $cards[$i]);
      }
      if ($log != "") WriteLog($log . " added to hand");
      return $lastResult;
    case "MULTIREMOVEHAND":
      $cards = "";
      $hand = &GetHand($player);
      if (!is_array($lastResult)) $lastResult = explode(",", $lastResult);
      for ($i = 0; $i < count($lastResult); ++$i) {
        if ($cards != "") $cards .= ",";
        $cards .= $hand[$lastResult[$i]];
        unset($hand[$lastResult[$i]]);
      }
      $hand = array_values($hand);
      return $cards;
    case "DESTROYCHARACTER":
      DestroyCharacter($player, $lastResult);
      return $lastResult;
    case "DESTROYEQUIPDEF0":
      $character = &GetPlayerCharacter($defPlayer);
      if (BlockValue($character[$lastResult]) + $character[$lastResult + 4] <= 0 && BlockValue($character[$lastResult]) != -1) {
        WriteLog(CardLink($character[$lastResult], $character[$lastResult]) . " was destroyed");
        DestroyCharacter($defPlayer, $lastResult);
      }
      return "";
    case "CHARFLAGDESTROY":
      $character = &GetPlayerCharacter($player);
      $character[$parameter + 7] = 1;
      return $lastResult;
    case "ADDCHARACTEREFFECT":
      $characterEffects = &GetCharacterEffects($player);
      array_push($characterEffects, $lastResult);
      array_push($characterEffects, $parameter);
      return $lastResult;
    case "ADDMZBUFF":
      $lrArr = explode("-", $lastResult);
      $characterEffects = &GetCharacterEffects($player);
      array_push($characterEffects, $lrArr[1]);
      array_push($characterEffects, $parameter);
      return $lastResult;
    case "ADDMZUSES":
      $lrArr = explode("-", $lastResult);
      switch ($lrArr[0]) {
        case "MYCHAR":
        case "THEIRCHAR":
          AddCharacterUses($player, $lrArr[1], $parameter);
          break;
        default:
          break;
      }
      return $lastResult;
    case "ADDMZUSESBLOODONHERHANDS":
      $lrArr = explode("-", $lastResult);
      $character = &GetPlayerCharacter($player);
      switch ($lrArr[0]) {
        case "MYCHAR":
        case "THEIRCHAR":
          if ($character[$lrArr[1] + 5] < 2 && SearchCurrentTurnEffects("blood_on_her_hands_yellow", $player, returnUniqueID: true) != $character[$lrArr[1] + 11]) {
            AddCurrentTurnEffect("blood_on_her_hands_yellow", $player, uniqueID: $character[$lrArr[1] + 11]);
            AddCharacterUses($player, $lrArr[1], $parameter);
            break;
          }
        default:
          break;
      }
      return $lastResult;
    case "MZOP":
      $paramArr = explode(",", $parameter);
      $parameter = $paramArr[0];
      switch ($parameter) {
        case "FREEZE":
          MZFreeze($lastResult);
          break;
        case "GAINCONTROL":
          MZGainControl($player, $lastResult);
          break;
        case "GETCARDID":
          return GetMZCard($player, $lastResult);
        case "GETCARDINDEX":
          $mzArr = explode("-", $lastResult);
          return $mzArr[1];
        case "GETCARDINDICES":
          $arr = explode(",", $lastResult);
          $output = [];
          for ($i = 0; $i < count($arr); ++$i) {
            $mzArr = explode("-", $arr[$i]);
            array_push($output, $mzArr[1]);
          }
          return implode(",", $output);
        case "GETUNIQUEID":
          $mzArr = explode("-", $lastResult);
          $zone = &GetMZZone($player, $mzArr[0]);
          switch ($mzArr[0]) {
            case "ALLY":
            case "MYALLY":
            case "THEIRALLY":
              return $zone[$mzArr[1] + 5];
            case "BANISH":
            case "MYBANISH":
            case "THEIRBANISH":
              return $zone[$mzArr[1] + 2];
            default:
              return "-1";
          }
        case "LASTMZINDEX":
          return MZLastIndex($player, $lastResult);
        case "SETPIECE":
          $pieceArr = explode("=", $paramArr[1]);
          $mzArr = explode("-", $lastResult);
          $zone = &GetMZZone($player, $mzArr[0]);
          $zone[$mzArr[1] + $pieceArr[0]] = $pieceArr[1];
          break;
        case "TURNBANISHFACEDOWN":
          $mzArr = explode("-", $lastResult);
          TurnBanishFaceDown(substr($mzArr[0], 0, 2) == "MY" ? $player : ($player == 1 ? 2 : 1), $mzArr[1]);
          break;
        case "ADDITIONALUSE":
          $mzArr = explode("-", $lastResult);
          $character = &GetPlayerCharacter($player);
          ++$character[$mzArr[1] + 5];
          if ($character[$mzArr[1] + 1] == 1) $character[$mzArr[1] + 1] = 2;
          break;
        case "ADDSUBCARD":
          $mzArr = explode("-", $lastResult);
          $character = &GetPlayerCharacter($player);
          if ($character[$mzArr[1]] == "teklovossen_the_mechropotentb") {
            if ($character[10] != "-") {
              $character[10] .= "," . $paramArr[1];
              ++$character[2]; // Update the counter
            } else $character[10] = $paramArr[1];
            break;
          } else if ($character[$mzArr[1] + 10] != "-") {
            $character[$mzArr[1] + 10] .= "," . $paramArr[1];
          } else $character[$mzArr[1] + 10] = $paramArr[1];
          ++$character[$mzArr[1] + 2]; // Update the counter
          break;
        case "REMOVEATKCOUNTER":
          $auras = &GetAuras($player);
          $mzArr = explode("-", $lastResult);
          switch ($mzArr[0]) {
            case "MYAURAS":
              --$auras[$mzArr[1] + 3];
              break;
            default:
              break;
          }
          break;
        case "TRANSFERATKCOUNTER":
          $auras = &GetAuras($player);
          $mzArr = explode("-", $lastResult);
          switch ($mzArr[0]) {
            case "MYAURAS":
              --$auras[$mzArr[1] + 3];
              ++$auras[count($auras) - AuraPieces() + 3];
              break;
            default:
              break;
          }
          break;
        default:
          break;
      }
      return $lastResult;
    case "OP":
      $params = explode("-", $parameter);
      switch ($params[0]) {
        case "DESTROYFROZENARSENAL":
          DestroyFrozenArsenal($player);
          return "";
        case "GIVEATTACKGOAGAIN":
          GiveAttackGoAgain();
          return $lastResult;
        case "BOOST":
          if (is_numeric($lastResult)) return DoBoost($player, $params[1], intval($lastResult));
          return DoBoost($player, $params[1]);
        case "REMOVECARD":
          if ($lastResult == "") return $dqVars[0];
          $cards = explode(",", $dqVars[0]);
          for ($i = 0; $i < count($cards); ++$i) {
            if ($cards[$i] == $lastResult) {
              unset($cards[$i]);
              $cards = array_values($cards);
              break;
            }
          }
          return implode(",", $cards);
        case "LOSEHEALTH":
          LoseHealth($lastResult, $player);
          return $lastResult;
        case "BANISHHAND":
          BanishHand($player);
          return $lastResult;
        case "DOCRANK":
          switch ($params[1]) {
            case "MainPhaseTrue":
              DoCrank($player, $lastResult, true);
              return $lastResult;
            case "MainPhaseFalse":
              DoCrank($player, $lastResult, false);
              return $lastResult;
            default:
              return $lastResult;
          }
        default:
          return $lastResult;
      }
    case "FILTER":
      $params = explode("-", $parameter);
      $from = $params[0];
      $relationship = $params[1];//exclude other or include
      $type = $params[2];
      $compare = $params[3];
      $input = [];
      switch ($from) {
        case "LastResult":
          $input = explode(",", $lastResult);
          for ($i = 0; $i < count($input); ++$i) $input[$i] = $input[$i] . "-" . $input[$i];
          break;
        case "CombatChain":
          $lastResultArr = explode(",", $lastResult);
          for ($i = 0; $i < count($lastResultArr); ++$i) array_push($input, $combatChain[$lastResultArr[$i] + CCOffset($type)] . "-" . $lastResultArr[$i]);
        default:
          break;
      }
      $output = [];
      for ($i = 0; $i < count($input); ++$i) {
        $inputArr = explode("-", $input[$i]);
        $passFilter = ($relationship == "include" ? false : true);
        switch ($type) {
          case "type":
            if (CardType($inputArr[0]) == $compare) $passFilter = !$passFilter;
            break;
          case "subtype":
            if (SubtypeContains($inputArr[0], $compare, $player)) $passFilter = !$passFilter;
            break;
          case "player":
            if ($inputArr[0] == $compare) $passFilter = !$passFilter;
            break;
          default:
            break;
        }
        if ($passFilter) array_push($output, $inputArr[1]);
      }
      return (count($output) > 0 ? implode(",", $output) : "PASS");
    case "PASSPARAMETER":
      return $parameter;
    case "DISCARDCARD":
      $params = explode("-", $parameter);
      CardDiscarded($player, $lastResult, count($params) > 1 ? $params[1] : "");
      AddGraveyard($lastResult, $player, $params[0], count($params) > 1 ? $params[1] : "");
      return $lastResult;
    case "BANISHCARD":
      $params = explode(",", $parameter);
      BanishCardForPlayer($lastResult, $player, $params[0], count($params) > 1 ? $params[1] : "", count($params) > 2 ? $params[2] : "");
      return $lastResult;
    case "ADDDISCARD":
      AddGraveyard($lastResult, $player, $parameter);
      return $lastResult;
    case "ADDBOTDECK":
      $deck = new Deck($player);
      $deck->AddBottom($lastResult);
      if($parameter != "Skip") WriteLog("⤵️A card was put on the bottom of the deck.");
      return $lastResult;
    case "ADDTOPDECK":
      $deck = new Deck($player);
      $deck->AddTop($lastResult);
      return $lastResult;
    case "ADDTOPORBOT":
      $deck = new Deck($player);
      $card = explode(",", $lastResult)[0];
      $loc = explode(",", $lastResult)[1];
      if ($loc == "TOP") $deck->AddTop($card);
      else $deck->AddBOTTOM($card);
      return $card;
    case "REMOVEDECK":
      $deck = new Deck($player);
      $deck->Remove($lastResult);
      return $deck->Remove($lastResult);
    case "MULTIADDDECK":
      $deck = new Deck($player);
      $cards = explode(",", $lastResult);
      for ($i = 0; $i < count($cards); ++$i) $deck->AddBottom($cards[$i]);
      return $lastResult;
    case "MULTIADDTOPDECK":
      $deck = new Deck($player);
      $cards = explode(",", $lastResult);
      for ($i = 0; $i < count($cards); ++$i) {
        if ($parameter == "1") WriteLog(CardLink($cards[$i], $cards[$i]));
        $deck->AddTop($cards[$i]);
      }
      return $lastResult;
    case "MULTIREMOVEDECK":
      if (is_array($lastResult)) $lastResult = ($lastResult == [] ? "" : implode(",", $lastResult));
      $deck = new Deck($player);
      return $deck->Remove($lastResult);
    case "PLAYAURA":
      $params = explode("-", $parameter);
      if (isset($params[1])) PlayAura($params[0], $player, $params[1]);
      else PlayAura($params[0], $player);
      break;
    case "DESTROYALLY":
      DestroyAlly($player, $lastResult);
      break;
    case "PARAMDELIMTOARRAY":
      return explode(",", $parameter);
    case "ADDSOUL":
      AddSoul($lastResult, $player, $parameter);
      return $lastResult;
    case "SHUFFLEDECK":
      $zone = &GetDeck($player);
      $destArr = [];
      if ($parameter == "SKIPSEED") {
        global $randomSeeded;
        $randomSeeded = true;
      }
      while (count($zone) > 0) {
        $index = GetRandom(0, count($zone) - 1);
        array_push($destArr, $zone[$index]);
        unset($zone[$index]);
        $zone = array_values($zone);
      }
      $zone = $destArr;
      if ($parameter != "SKIPSEED") WriteLog("🔄Player " . $player . " deck was shuffled");
      return $lastResult;
    case "EXHAUSTCHARACTER":
      $character = &GetPlayerCharacter($player);
      $character[$parameter + 1] = 1;
      return $parameter;
    case "DECKCARDS":
      $indices = explode(",", $parameter);
      $deck = &GetDeck($player);
      $rv = "";
      for ($i = 0; $i < count($indices); ++$i) {
        if (count($deck) <= $i) continue;
        if ($rv != "") $rv .= ",";
        $rv .= $deck[$i];
      }
      return ($rv == "" ? "PASS" : $rv);
    case "DESTROYTOPCARD":
      $deck = new Deck($player);
      WriteLog("Destroyed " . CardLink($deck->Top(), $deck->Top()) . " on top of Player " . $player . " deck");
      AddGraveyard($deck->Top(remove: true), $player, "DECK");
      return $lastResult;
    case "SHOWMODES":
      if (is_array($lastResult)) $modes = $lastResult;
      else {
        $modes = explode(",", $lastResult);
      }
      $text = "";
      for ($i = 0; $i < count($modes); ++$i) {
        if ($text != "") $text .= ", ";
        if ($i > 0 && $i == count($modes) - 1) $text .= " and ";
        $text .= GamestateUnsanitize($modes[$i]);
      }
      if ($text == "") $text = "None";
      WriteLog("Selected mode" . (count($modes) > 1 ? "s" : "") . " for " . CardLink($parameter, $parameter) . (count($modes) > 1 ? " are" : " is") . ": " . $text);
      return $lastResult;
    case "REVEALCARDS":
      $cards = (is_array($lastResult) ? implode(",", $lastResult) : $lastResult);
      $revealed = RevealCards($cards, $player);
      return ($revealed ? $lastResult : "PASS");
    case "REVEALHANDCARDS":
      $indices = (is_array($lastResult) ? $lastResult : explode(",", $lastResult));
      $hand = &GetHand($player);
      $cards = "";
      if (empty($hand)) return "PASS";
      for ($i = 0; $i < count($indices); ++$i) {
        if (isset($hand[$indices[$i]])) { 
          if ($cards != "") $cards .= ",";
          $cards .= $hand[$indices[$i]];
        } 
      }
      $revealed = RevealCards($cards, $player);
      return ($revealed ? $cards : "PASS");
    case "SHOWHANDWRITELOG":
      $hand = &GetHand($player);
      $cards = "";
      for ($i = 0; $i < count($hand); ++$i) {
        if ($cards != "") $cards .= ", ";
        $cards .= CardLink($hand[$i], $hand[$i]);
      }
      WriteLog("Hand content: $cards.");
      return $lastResult;
    case "WRITELOG":
      WriteLog(GamestateUnsanitize($parameter));
      return $lastResult;
    case "WRITELOGCARDLINK":
      $params = explode("_", $parameter);
      Writelog(CardLink($params[0], $params[0]) . " was choosen");
      return $lastResult;
    case "WRITELOGLASTRESULT":
      WriteLog("<b>" . $lastResult . "<b> was selected.");
      return $lastResult;
    case "ADDCURRENTEFFECT":
      $params = explode("!", $parameter);
      AddCurrentTurnEffect($params[0], $player, (count($params) > 1 ? $params[1] : ""));
      return "1";
    case "ADDCURRENTEFFECTNEXTATTACK":
      $params = explode("!", $parameter);
      AddCurrentTurnEffectNextAttack($params[0], $player, (count($params) > 1 ? $params[1] : ""));
      return "1";
    case "SEARCHCURRENTEFFECTPASS":
      return SearchCurrentTurnEffects($parameter, $player) ? "PASS" : "1";
    case "REMOVECURRENTTURNEFFECT":
      SearchCurrentTurnEffects($parameter, $player, true);
      return $lastResult;
    case "ADDCURRENTEFFECTLASTRESULT":
      $params = explode("!", $parameter);
      AddCurrentTurnEffect($params[0] . $lastResult, $player, (count($params) > 1 ? $params[1] : ""));
      return $lastResult;
    case "ADDSTASISTURNEFFECT":
      $character = &GetPlayerCharacter($player);
      WriteLog(CardLink($character[$lastResult], $character[$lastResult]) . " can't be activated this turn.");
      $effect = $parameter . $character[$lastResult];
      AddCurrentTurnEffect($effect, $player);
      AddNextTurnEffect($effect, $player);
      if ($player == $mainPlayer) AddNextTurnEffect($effect, $player, numTurns: 2); //If played at instant speed from Dash
      return $lastResult;
    case "EQUIPCANTDEFEND":
      $character = &GetPlayerCharacter($player);
      WriteLog(CardLink($character[$lastResult], $character[$lastResult]) . " can't defend this turn.");
      $effect = $parameter . $character[$lastResult];
      AddCurrentTurnEffect($effect, $player);
      return $lastResult;
    case "ADDCURRENTANDNEXTTURNEFFECT":
      AddCurrentTurnEffect($parameter, $player);
      AddNextTurnEffect($parameter, $player);
      return "1";
    case "ADDTHEIRNEXTTURNEFFECT":
      $numTurns = $player == $mainPlayer ? 2 : 1;
      AddNextTurnEffect($parameter, $player, numTurns: $numTurns);
      return "1";
    case "ADDLIMITEDCURRENTEFFECT":
      $params = explode(",", $parameter);
      AddCurrentTurnEffect($params[0], $player, $params[1], $lastResult);
      return $lastResult;
    case "ADDAIMCOUNTER":
      $arsenal = &GetArsenal($player);
      $arsenal[$lastResult + 3] += 1;
      return $lastResult;
    case "OPTX":
      Opt("NA", $parameter);
      return $lastResult;
    case "SETCLASSSTATE":
      $data = is_array($lastResult) ? implode(",", $lastResult) : $lastResult;
      SetClassState($player, $parameter, $data);
      return $lastResult;
    case "GETCLASSSTATE":
      return GetClassState($player, $parameter);
    case "GAINACTIONPOINTS":
      GainActionPoints($parameter, $player);
      return $lastResult;
    case "EQUALPASS":
      if ($lastResult == $parameter) return "PASS";
      return 1;
    case "NOTEQUALPASS":
      if ($lastResult != $parameter) return "PASS";
      return 1;
    case "NOPASS":
      if ($lastResult == "NO") return "PASS";
      return 1;
    case "NULLPASS":
      if ($lastResult == "") return "PASS";
      return $lastResult;
    case "ELSE":
      if ($lastResult == "PASS") return "0";
      else if ($lastResult == "NO") return "NO";
      else return "PASS";
    case "LESSTHANPASS":
      if ($lastResult < $parameter) return "PASS";
      return $lastResult;
    case "GREATERTHANPASS":
      if ($lastResult > $parameter) return "PASS";
      return $lastResult;
    case "REVERTGAMESTATEIFNULL":
      if ($lastResult == ""){
        WriteLog(GamestateUnsanitize($parameter), highlight: true);
        RevertGamestate();
      }
      return $lastResult;
    case "EQUIPDEFENSE":
      $char = &GetPlayerCharacter($player);
      $defense = BlockValue($char[$lastResult]) + $char[$lastResult + 4];
      if ($defense < 0) $defense = 0;
      return $defense;
    case "ALLCARDTYPEORPASS":
      $cards = explode(",", $lastResult);
      for ($i = 0; $i < count($cards); ++$i) {
        if (!TypeContains($cards[$i], $parameter, $player)) return "PASS";
      }
      return $lastResult;
    case "NONECARDTYPEORPASS":
      $cards = explode(",", $lastResult);
      for ($i = 0; $i < count($cards); ++$i) {
        if (TypeContains($cards[$i], $parameter, $player)) return "PASS";
      }
      return $lastResult;
    case "NONECARDPITCHORPASS":
      $cards = explode(",", $lastResult);
      for ($i = 0; $i < count($cards); ++$i) {
        if (PitchValue($cards[$i]) == $parameter) return "PASS";
      }
      return $lastResult;
    case "ALLCARDSUBTYPEORPASS":
      $cards = explode(",", $lastResult);
      for ($i = 0; $i < count($cards); ++$i) {
        if (!SubtypeContains($cards[$i], $parameter, $player)) return "PASS";
      }
      return $lastResult;
    case "ALLCARDTALENTORPASS":
      $cards = explode(",", $lastResult);
      for ($i = 0; $i < count($cards); ++$i) {
        if (!TalentContains($cards[$i], $parameter, $player)) return "PASS";
      }
      return $lastResult;
    case "ALLCARDPITCHORPASS":
      $cards = explode(",", $lastResult);
      for ($i = 0; $i < count($cards); ++$i) {
        if (PitchValue($cards[$i]) != $parameter) return "PASS";
      }
      return $lastResult;
    case "ALLCARDSCOMBOORPASS":
      $cards = explode(",", $lastResult);
      for ($i = 0; $i < count($cards); ++$i) {
        if (!HasCombo($cards[$i])) return "PASS";
      }
      return $lastResult;
    case "ALLCARDMAXCOSTORPASS":
      $cards = explode(",", $lastResult);
      for ($i = 0; $i < count($cards); ++$i) {
        if (CardCost($cards[$i]) > $parameter) return "PASS";
      }
      return $lastResult;
    case "ALLCARDCLASSORPASS":
      $cards = explode(",", $lastResult);
      for ($i = 0; $i < count($cards); ++$i) {
        if (!ClassContains($cards[$i], $parameter, $player)) return "PASS";
      }
      return $lastResult;
    case "CLASSSTATEGREATERORPASS":
      $parameters = explode("-", $parameter);
      $state = $parameters[0];
      $threshold = $parameters[1];
      if (GetClassState($player, $state) < $threshold) return "PASS";
      return 1;
    case "CHARREADYORPASS":
      $char = &GetPlayerCharacter($player);
      if ($char[$parameter + 1] != 2) return "PASS";
      return 1;
    case "LORDOFWIND":
      $number = 0;
      if ($lastResult != "") {
        $number = count(explode(",", $lastResult));
      }
      AddResourceCost($player, $number);
      AddCurrentTurnEffect("lord_of_wind_blue-" . $number, $player);
      return $number;
    case "VOFTHEVANGUARD":
      if ($parameter == "1" && TalentContains($lastResult, "LIGHT", $player)) {
        WriteLog(CardLink("v_of_the_vanguard_yellow", "v_of_the_vanguard_yellow") . " gives all attacks on this combat chain +1");
        AddCurrentTurnEffect("v_of_the_vanguard_yellow", $player);
      }
      $hand = &GetHand($player);
      if (count($hand) > 0) {
        PrependDecisionQueue("VOFTHEVANGUARD", $player, "1", 1);
        PrependDecisionQueue("CHARGE", $player, "-", 1);
      }
      return "1";
    case "TRIPWIRETRAP":
      if ($lastResult == 0) {
        WriteLog("Hit effects are prevented by " . CardLink("tripwire_trap_red", "tripwire_trap_red") . " this chain link");
        HitEffectsPreventedThisLink();
        AddCurrentTurnEffect("tripwire_trap_red", $player);
      }
      return 1;
    case "GREATERTHAN0ORPASS":
      return $lastResult > 0 ? $lastResult : "PASS";
    case "ATTACKMODIFIER":
      $amount = intval($parameter);
      $combatChain[5] += $amount;
      CurrentEffectAfterPlayOrActivateAbility();
      return $parameter;
    case "SONATAARCANIX":
      $cards = explode(",", $lastResult);
      $numAA = 0;
      $numNAA = 0;
      $AAIndices = "";
      for ($i = 0; $i < count($cards); ++$i) {
        $cardType = CardType($cards[$i]);
        if (DelimStringContains($cardType, "A")) ++$numNAA;
        else if ($cardType == "AA") {
          ++$numAA;
          if ($AAIndices != "") $AAIndices .= ",";
          $AAIndices .= $i;
        }
      }
      $numMatch = ($numAA > $numNAA ? $numNAA : $numAA);
      if ($numMatch == 0) return "PASS";
      return $numMatch . "-" . $AAIndices . "-" . $numMatch;
    case "LOOKTOPDECK":
      $cards = explode(",", $lastResult);
      $cardsIndices = "";
      for ($i = 0; $i < count($cards); ++$i) {
        if ($cardsIndices != "") $cardsIndices .= ",";
        $cardsIndices .= $i;
      }
      return $cardsIndices;
    case "TOPDECKCHOOSE":
      $cards = explode(",", $lastResult);
      $params = explode(",", $parameter);
      $indices = "";
      $numMatch = 0;
      for ($i = 0; $i < count($cards); ++$i) {
        if (DelimStringContains(CardSubType($cards[$i]), $params[1])) {
          if ($indices != "") $indices .= ",";
          $indices .= $i;
          ++$numMatch;
        }
      }
      if ($numMatch == 0) return "PASS";
      return $params[0] . "-" . $indices . "-" . "0";
    case "SONATAARCANIXSTEP2":
      $numArcane = count(explode(",", $lastResult));
      DealArcane($numArcane, 0, "PLAYCARD", "sonata_arcanix_red", true);
      return 1;
    case "CHARGE":
      DQCharge();
      return "1";
    case "FINISHCHARGE":
      $otherPlayer = $player == 1 ? 2 : 1;
      //Abilities when you charge it
      global $Card_CourageBanner, $Card_QuickenBanner, $Card_SpellbaneBanner, $Card_LifeBanner, $Card_BlockBanner, $Card_ResourceBanner, $CS_DamageDealt;
      switch ($lastResult) {
        case $Card_CourageBanner:
          AddLayer("TRIGGER", $player, $lastResult);
          break;
        case $Card_QuickenBanner:
          AddLayer("TRIGGER", $player, $lastResult);
          break;
        case $Card_SpellbaneBanner:
          AddLayer("TRIGGER", $player, $lastResult);
          break;
        case $Card_LifeBanner:
          AddLayer("TRIGGER", $player, $lastResult);
          break;
        case $Card_BlockBanner:
          AddLayer("TRIGGER", $player, $lastResult);
          break;
        case $Card_ResourceBanner:
          AddLayer("TRIGGER", $player, $lastResult);
          break;
        default:
          break;
      }
      WriteLog("This card was charged: " . CardLink($lastResult, $lastResult));
      IncrementClassState($player, $CS_NumCharged);
      LogPlayCardStats($player, $lastResult, "HAND", "CHARGE");
      if (SearchCharacterActive($player, "soulbond_resolve") && GetClassState($otherPlayer, $CS_DamageDealt) <= 0 && GetClassState($otherPlayer, $CS_ArcaneDamageDealt) <= 0) AddCurrentTurnEffect("soulbond_resolve", $player);
      if (CardType($EffectContext) == "AA" || CardType($layers[0]) == "AA") ++$combatChainState[$CCS_AttackNumCharged];
      return $lastResult;
    case "DEALDAMAGE":
      $target = (is_array($lastResult) ? $lastResult : explode("-", $lastResult));
      $targetPlayer = ($target[0] == "MYCHAR" || $target[0] == "MYALLY" ? $player : ($player == 1 ? 1 : 2));
      $parameters = explode("-", $parameter);
      $damage = $parameters[0];
      $source = $parameters[1];
      $type = $parameters[2];
      if ($target[0] == "THEIRALLY" || $target[0] == "MYALLY") {
        $allies = &GetAllies($targetPlayer);
        if ($allies[$target[1] + 6] > 0) {
          $damage -= 3;
          if ($damage < 0) $damage = 0;
          --$allies[$target[1] + 6];
        }
        $allies[$target[1] + 2] -= $damage;
        if ($damage > 0) AllyDamageTakenAbilities($targetPlayer, $target[1]);
        if ($allies[$target[1] + 2] <= 0) DestroyAlly($targetPlayer, $target[1], uniqueID: $allies[$target[1] + 5]);
        return $damage;
      } else {
        PrependDecisionQueue("TAKEDAMAGE", $targetPlayer, $parameter);
        DoQuell($targetPlayer, $damage);
        if (SearchCurrentTurnEffects("morlock_hill_blue", $targetPlayer, true) && $damage >= GetHealth($targetPlayer)) PreventLethal($targetPlayer, $damage);
      }
      return $damage;
    case "TAKEDAMAGE":
      $otherPlayer = ($player == 1 ? 2 : 1);
      $params = explode("-", $parameter);
      $damage = intval($params[0]);
      $source = (count($params) > 1 ? $params[1] : "-");
      $type = (count($params) > 2 ? $params[2] : "-");
      if (!CanDamageBePrevented($player, $damage, "DAMAGE", $source)) $lastResult = 0;
      $damage -= intval($lastResult);
      $damage = DealDamageAsync($player, $damage, $type, $source);
      if ($type == "COMBAT") $dqState[6] = $damage;
      Writelog("Player $player took $damage damage from " . Cardlink($source, $source));
      return $damage;
    case "AFTERQUELL":
      $maxQuell = GetClassState($player, $CS_MaxQuellUsed);
      if ($lastResult > 0) WriteLog("Player $player prevented $lastResult damage with Quell", $player);
      if ($lastResult > $maxQuell) SetClassState($player, $CS_MaxQuellUsed, $lastResult);
      return $lastResult;
    case "SPELLVOIDCHOICES":
      $damage = $parameter;
      if ($lastResult != "PASS") {
        $prevented = ArcaneDamagePrevented($player, $lastResult);
        $damage -= $prevented;
        if ($damage < 0) $damage = 0;
        $dqVars[0] = $damage;
        if ($damage > 0) CheckSpellvoid($player, $damage);
      }
      PrependDecisionQueue("INCDQVAR", $player, "1", 1);
      return $prevented;
    case "THREATENARCANE":
      DealArcane(1, 2, "ABILITY", $parameter, true);
      return $lastResult;
    case "DEALARCANE":
      $dqState[7] = $lastResult;
      $target = explode("-", $lastResult);
      $targetPlayer = ($target[0] == "MYCHAR" || $target[0] == "MYALLY" ? $player : ($player == 1 ? 2 : 1));
      $parameters = explode("-", $parameter);
      $damage = $parameters[0];
      $source = $parameters[1];
      $type = $parameters[2];
      $sourceType = CardType($source);
      if ($type == "PLAYCARD") {
        $damage += ConsumeArcaneBonus($player);
        if (DelimStringContains($sourceType, "A") || $sourceType == "AA") $damage += CountCurrentTurnEffects("flicker_wisp_yellow", $player);
        WriteLog(CardLink($source, $source) . " is dealing " . $damage . " arcane damage");
      }
      if ($type == "ARCANESHOCK") {
        $damage += ConsumeArcaneBonus($player, noRemove: true);
        if (DelimStringContains($sourceType, "A") || $sourceType == "AA") $damage += CountCurrentTurnEffects("flicker_wisp_yellow", $player);
        WriteLog(CardLink($source, $source) . " is dealing " . $damage . " arcane damage");
      }
      if ($target[0] == "THEIRALLY" || $target[0] == "MYALLY") {
        $allies = &GetAllies($targetPlayer);
        if ($allies[$target[1] + 6] > 0) {
          $damage -= 3;
          if ($damage < 0) $damage = 0;
          --$allies[$target[1] + 6];
        }
        $allies[$target[1] + 2] -= $damage;
        $dqVars[0] = $damage;
        if ($damage > 0) AllyDamageTakenAbilities($targetPlayer, $target[1]);
        if ($allies[$target[1] + 2] <= 0) {
          DestroyAlly($targetPlayer, $target[1], uniqueID: $allies[$target[1] + 5]);
        } else {
          AppendClassState($player, $CS_ArcaneTargetsSelected, $lastResult);
        }
        return "";
      }
      AppendClassState($player, $CS_ArcaneTargetsSelected, $lastResult);
      $target = $targetPlayer;
      $arcaneBarrier = ArcaneBarrierChoices($target, $damage);
      PrependDecisionQueue("TAKEARCANE", $target, $damage . "-" . $source . "-" . $player);
      PrependDecisionQueue("PASSPARAMETER", $target, "{1}");
      CheckSpellvoid($target, $damage);
      PrependDecisionQueue("INCDQVAR", $target, "1", 1);
      DoQuell($target, $damage);
      PrependDecisionQueue("INCDQVAR", $target, "1", 1);
      PrependDecisionQueue("PAYRESOURCES", $target, "<-", 1);
      PrependDecisionQueue("ARCANECHOSEN", $target, "-", 1);
      PrependDecisionQueue("CHOOSEARCANE", $target, $arcaneBarrier, 1);
      PrependDecisionQueue("SETDQVAR", $target, "1", 1);
      PrependDecisionQueue("PASSPARAMETER", $target, "0", 1);
      PrependDecisionQueue("SETDQVAR", $target, "0", 1);
      PrependDecisionQueue("PASSPARAMETER", $target, $damage . "-" . $source, 1);
      return $parameter;
    case "ARCANEHITEFFECT":
      if ($dqVars[0] > 0) ArcaneHitEffect($player, $parameter, $dqState[7], $dqVars[0]); //player, source, target, damage
      if ($dqVars[0] > 0) IncrementClassState($player, $CS_ArcaneDamageDealt, $dqVars[0]);
      return $lastResult;
    case "ARCANECHOSEN":
      if ($lastResult > 0) {
        if (SearchCharacterActive($player, "alluvion_constellas")) {
          $char = &GetPlayerCharacter($player);
          $index = FindCharacterIndex($player, "alluvion_constellas");
          if ($char[$index + 2] < 4 && GetClassState($player, $CS_AlluvionUsed) == 0) {
            ++$char[$index + 2];
            SetClassState($player, $CS_AlluvionUsed, 1);
          }
        }
        LogDamagePreventedStats($player, $lastResult);
      }
      return $lastResult;
    case "TAKEARCANE":
      $parameters = explode("-", $parameter);
      $damage = $parameters[0];
      $source = $parameters[1];
      $playerSource = $parameters[2];
      if (!CanDamageBePrevented($player, $damage, "ARCANE", $source)) $lastResult = 0;
      $damage = DealDamageAsync($player, $damage - $lastResult, "ARCANE", $source);
      if ($damage < 0) $damage = 0;
      WriteLog("Player " . $playerSource . " is dealing $damage arcane damage from " . CardLink($source, $source), $player);
      if (DelimStringContains(CardSubType($source), "Ally") && $damage > 0) ProcessDealDamageEffect($source); // Interaction with Burn Them All! + Nekria
      $dqVars[0] = $damage;
      return $damage;
    case "PAYRESOURCES":
      $resources = &GetResources($player);
      $lastResult = intval($lastResult);
      if ($lastResult < 0) $resources[0] += (-1 * $lastResult);
      else if ($resources[0] > 0) {
        $res = $resources[0];
        $resources[0] -= $lastResult;
        $lastResult -= $res;
        if ($resources[0] < 0) $resources[0] = 0;
      }
      if ($lastResult > 0) {
        $hand = &GetHand($player);
        $char = &GetPlayerCharacter($player);
        if (count($hand) == 0 && $char[0] != "DUMMY") {
          WriteLog("You have resources to pay for, but have no cards to pitch. Reverting gamestate prior to that declaration.", highlight: true);
          RevertGamestate();
        }
        PrependDecisionQueue("PAYRESOURCES", $player, $parameter, 1);
        PrependDecisionQueue("SUBPITCHVALUE", $player, $lastResult, 1);
        PitchCard($player, skipGain: true);
      }
      return $parameter;
    case "ADDCLASSSTATE":
      $parameters = explode("-", $parameter);
      IncrementClassState($player, $parameters[0], $parameters[1]);
      return $lastResult;
    case "APPENDCLASSSTATE":
      $parameters = explode("-", $parameter);
      AppendClassState($player, $parameters[0], $parameters[1]);
      return $lastResult;
    case "AFTERFUSE":
      $params = explode("-", $parameter);
      $card = $params[0];
      $elements = $params[1];
      $elementArray = explode(",", $elements);
      for ($i = 0; $i < count($elementArray); ++$i) {
        $element = $elementArray[$i];
        switch ($element) {
          case "EARTH":
            IncrementClassState($player, $CS_NumFusedEarth);
            break;
          case "ICE":
            IncrementClassState($player, $CS_NumFusedIce);
            break;
          case "LIGHTNING":
            IncrementClassState($player, $CS_NumFusedLightning);
            break;
          default:
            break;
        }
        AppendClassState($player, $CS_AdditionalCosts, $elements);
        CurrentTurnFuseEffects($player, $element);
        AuraFuseEffects($player, $element);
        $lastPlayed[3] = (GetClassState($player, $CS_AdditionalCosts) == HasFusion($card) || IsAndOrFuse($card) ? "FUSED" : "UNFUSED");
      }
      return $lastResult;
    case "SUBPITCHVALUE":
      return $parameter - PitchValue($lastResult);
    case "GAINPITCHVALUE":
      $resources = &GetResources($player);
      $resources[0] += PitchValue($lastResult);
      return $lastResult;
    case "BUFFARCANE":
      AddCurrentTurnEffect($parameter . "," . $lastResult, $player);
      return $lastResult;
    case "BUFFARCANEPREVLAYER":
      global $layers;
      $index = 0;
      for ($index = 0; $index < count($layers) && $layers[$index] == "TRIGGER"; $index += LayerPieces()) ;
      AddCurrentTurnEffect("metacarpus_node", $player, "PLAY", $layers[$index + 6]);
      return $lastResult;
    case "LASTARSENALADDEFFECT":
      $params = explode(",", $parameter);
      $arsenal = &GetArsenal($player);
      if (count($arsenal) > 0 && count($params) == 2) AddCurrentTurnEffect($params[0], $player, $params[1], $arsenal[count($arsenal) - ArsenalPieces() + 5]);
      return $lastResult;
    case "INVERTEXISTENCE":
      if ($lastResult == "") {
        WriteLog("No cards were selected, " . CardLink("invert_existence_blue", "invert_existence_blue") . " did not banish any cards");
        return $lastResult;
      }
      $cards = explode(",", $lastResult);
      $numAA = 0;
      $numNAA = 0;
      $message = CardLink("invert_existence_blue", "invert_existence_blue") . " banished ";
      for ($i = 0; $i < count($cards); ++$i) {
        $type = CardType($cards[$i]);
        if ($type == "AA") ++$numAA;
        else if (DelimStringContains($type, "A")) ++$numNAA;
        if ($i >= 1) $message .= ", ";
        if ($i != 0 && $i == count($cards) - 1) $message .= "and ";
        $message .= CardLink($cards[$i], $cards[$i]);
      }
      WriteLog($message);
      if ($numAA == 1 && $numNAA == 1) DealArcane(2, 0, "PLAYCARD", "invert_existence_blue", true, $player);
      return $lastResult;
    case "ROUSETHEANCIENTS":
      $cards = (is_array($lastResult) ? $lastResult : explode(",", $lastResult));
      $totalAV = 0;
      for ($i = 0; $i < count($cards); ++$i) {
        $totalAV += intval(ModifiedAttackValue($cards[$i], $player, "HAND", source: "rouse_the_ancients_blue"));
      }
      if ($totalAV >= 13) {
        AddCurrentTurnEffect("rouse_the_ancients_blue", $player);
        WriteLog(CardLink("rouse_the_ancients_blue", "rouse_the_ancients_blue") . " got +7 and go again");
      }
      return $lastResult;
    case "TOOTHANDCLAW":
      $cards = (is_array($lastResult) ? $lastResult : explode(",", $lastResult));
      $totalReveal = 0;
      for ($i = 0; $i < count($cards); ++$i) {
        if (CardNameContains($cards[$i], "Crouching Tiger", $player)) ++$totalReveal;
      }
      if ($totalReveal > 0) GiveAttackGoAgain();
      if ($totalReveal > 1) AddCurrentTurnEffect("tooth_and_claw_red-BUFF", $player);
      if ($totalReveal > 2) Draw($player);
      return $lastResult;
    case "GIVEACTIONGOAGAIN":
      if ($parameter == "A") SetClassState($player, $CS_NextNAACardGoAgain, 1);
      else if ($parameter == "AA") GiveAttackGoAgain();
      return 1;
    case "PROCESSATTACKTARGET":
      $combatChainState[$CCS_AttackTarget] = $lastResult;
      $mzArr = explode("-", $lastResult);
      $zone = &GetMZZone($defPlayer, $mzArr[0]);
      $uid = "-";
      switch ($mzArr[0]) {
        case "MYALLY":
        case "THEIRALLY":
          $uid = $zone[$mzArr[1] + 5];
          break;
        case "MYAURAS":
        case "THEIRAURAS":
          $uid = $zone[$mzArr[1] + 6];
          break;
        default:
          break;
      }
      $combatChainState[$CCS_AttackTargetUID] = $uid;
      WriteLog("🎯".GetMZCardLink($defPlayer, $lastResult) . " was chosen as the attack target");
      return 1;
    case "STARTTURNABILITIES":
      StartTurnAbilities();
      return 1;
    case "DRAWTOINTELLECT":
      global $CS_NumCardsDrawn;
      $char = &GetPlayerCharacter($player);
      for ($i = 0; $i < CharacterIntellect($char[0]); ++$i) Draw($player, mainPhase: false, fromCardEffect: false);
      SetClassState($player, $CS_NumCardsDrawn, 0);//Don't make initial draw count for Hold the Line
      return 1;
    case "ROLLDIE":
      RollDie($player, true, $parameter == "1");
      return "";
    case "REROLLDIE":
      RollDie($player, true, $parameter == "1", true);
      return "";
    case "SETCOMBATCHAINSTATE":
      $combatChainState[$parameter] = $lastResult;
      return $lastResult;
    case "INCREMENTCOMBATCHAINSTATE":
      $combatChainState[$parameter] = $combatChainState[$parameter] + 1;
      return $lastResult;
    case "INCREMENTCOMBATCHAINSTATEBY":
      $combatChainState[$parameter] = $combatChainState[$parameter] + $lastResult;
      return $lastResult;
    case "SETLAYERTARGET":
      global $layers;
      $target = $lastResult;
      $targetArr = explode("-", $target);
      $otherPlayer = ($player == 1 ? 2 : 1);
      if ($targetArr[0] == "LAYER") $target = "LAYERUID-" . $layers[intval($targetArr[1]) + 6];
      if ($targetArr[0] == "THEIRDISCARD") {
        $discard = GetDiscard($otherPlayer);
        $target = "THEIRDISCARDUID-" . $discard[$targetArr[1] + 1];
      }
      if ($targetArr[0] == "MYDISCARD") {
        $discard = GetDiscard($player);
        $target = "MYDISCARDUID-" . $discard[$targetArr[1] + 1];
      }
      if ($targetArr[0] == "THEIRAURAS") {
        $auras = GetAuras($otherPlayer);
        $target = "THEIRAURASUID-" . $auras[$targetArr[1] + 6];
      }
      if ($targetArr[0] == "MYAURAS") {
        $auras = GetAuras($player);
        $target = "MYAURASUID-" . $auras[$targetArr[1] + 6];
      }
      if ($targetArr[0] == "THEIRCHAR") {
        $char = GetPlayerCharacter($otherPlayer);
        $target = "THEIRCHARUID-" . $char[$targetArr[1] + 11];
      }
      if ($targetArr[0] == "MYCHAR") {
        $char = GetPlayerCharacter($player);
        $target = "MYCHAR-" . $char[$targetArr[1] + 11];
      }
      if ($targetArr[0] == "COMBATCHAINATTACKS") {
        // It's not possible for this index to get messed up before resolution
        $target = $lastResult;
      }
      if ($targetArr[0] == "COMBATCHAIN") {
        $char = GetPlayerCharacter($otherPlayer);
        //right now only support targetting the active chain link
        $target = "COMBATCHAIN-" . $CombatChain->AttackCard()->UniqueID();
      }
      for ($i = 0; $i < count($layers); $i += LayerPieces()) {
        if ($layers[$i] == $parameter && $layers[$i + 3] == "-") {
          $layers[$i + 3] = $target;
        }
      }
      return $target;
    case "SHOWSELECTEDTARGET":
      $targetPlayer = (substr($lastResult, 0, 5) == "THEIR" ? ($player == 1 ? 2 : 1) : $player);
      WriteLog("Player " . $targetPlayer . " targeted " . GetMZCardLink($targetPlayer, $lastResult));
      return $lastResult;
    case "MULTIZONEFORMAT":
      return SearchMultizoneFormat($lastResult, $parameter);
    case "MULTIZONETOKENCOPY":
      $mzArr = explode("-", $lastResult);
      $source = $mzArr[0];
      $index = $mzArr[1];
      switch ($source) {
        case "MYAURAS":
          TokenCopyAura($player, $index);
          break;
        default:
          break;
      }
      return $lastResult;
    case "COUNTITEM":
      return CountItem($parameter, $player);
    case "FINDANDDESTROYITEM":
      $mzArr = explode("-", $parameter);
      $cardID = $mzArr[0];
      $number = $mzArr[1];
      $itemsLeftToDestroy = $mzArr[1];
      for ($i = 0; $i < $number; ++$i) {
        $index = GetItemIndex($cardID, $player);
        if ($index != -1) {
          DestroyItemForPlayer($player, $index);
          --$itemsLeftToDestroy;
        }
      }
      if ($itemsLeftToDestroy > 0) {
        $charIndex = FindCharacterIndex($player, "aurum_aegis");
        if ($charIndex != -1) DestroyCharacter($player, $charIndex);
      }
      return $lastResult;
    case "COUNTPARAM":
      $array = explode(",", $parameter);
      return count($array) . "-" . $parameter;
    case "VALIDATEALLSAMENAME":
      if ($parameter == "DECK") {
        $zone = &GetDeck($player);
      }
      if (count($lastResult) == 0) return "PASS";
      if (SearchCurrentTurnEffects("amnesia_red", $player)) return "PASS";
      $name = CardName($zone[$lastResult[0]]);
      for ($i = 1; $i < count($lastResult); ++$i) {
        if (CardName($zone[$lastResult[$i]]) != $name) {
          WriteLog("You selected cards that do not have the same name. Reverting gamestate prior to that effect.", highlight: true);
          RevertGamestate();
          return "PASS";
        }
      }
      return $lastResult;
    case "VALIDATEALLDIFFERENTNAME":
      if ($parameter == "DISCARD") {
        $zone = &GetDiscard($player);
      }
      if (count($lastResult) == 0) return "PASS";
      if (SearchCurrentTurnEffects("amnesia_red", $player)) return "PASS";
      $cardList = [];
      for ($i = 0; $i < count($lastResult); ++$i) {
        array_push($cardList, CardName($zone[$lastResult[$i]]));
      }
      if (count($cardList) !== count(array_unique($cardList))) {
        WriteLog("You selected cards that have the same name. Reverting gamestate prior to that effect.", highlight: true);
        RevertGamestate();
        return "PASS";
      }
      return $lastResult;
    case "PREPENDLASTRESULT":
      return $parameter . $lastResult;
    case "APPENDLASTRESULT":
      return $lastResult . $parameter;
    case "LASTRESULTPIECE":
      $pieces = explode("-", $lastResult);
      return $pieces[$parameter];
    case "IMPLODELASTRESULT":
      if (!is_array($lastResult)) return $lastResult;
      return ($lastResult == "" ? "PASS" : implode($parameter, $lastResult));
    case "VALIDATECOUNT":
      if (count($lastResult) != $parameter) {
        WriteLog("The count from the last step is incorrect. Reverting gamestate prior to that effect.", highlight: true);
        RevertGamestate();
        return "PASS";
      }
      return $lastResult;
    case "SOULHARVEST":
      $numBD = 0;
      $discard = GetDiscard($player);
      for ($i = 0; $i < count($lastResult); ++$i) {
        if (HasBloodDebt($discard[$lastResult[$i]])) ++$numBD;
      }
      if ($numBD > 0) AddCurrentTurnEffect("soul_harvest_blue," . $numBD, $player);
      return $lastResult;
    case "ADDATTACKCOUNTERS":
      $lastResultArr = explode("-", $lastResult);
      $zone = $lastResultArr[0];
      $zoneDS = &GetMZZone($player, $zone);
      $index = $lastResultArr[1];
      if ($zone == "MYCHAR" || $zone == "THEIRCHAR") $zoneDS[$index + 3] += $parameter;
      else if ($zone == "MYAURAS" || $zone == "THEIRAURAS") $zoneDS[$index + 3] += $parameter;
      else if ($zone == "MYALLY" || $zone == "THEIRALLY") $zoneDS[$index + 9] += $parameter;
      return $lastResult;
    case "ADDALLATTACKCOUNTERS":
      $lastResult = str_replace(",", "-", $lastResult);
      $lastResultArr = explode("-", $lastResult);
      $zone = $lastResultArr[0];
      $zoneDS = &GetMZZone($player, $zone);
      for ($i = 1; $i < count($lastResultArr); $i += 2) {
        if ($zone == "MYALLY" || $zone == "THEIRALLY") $zoneDS[$lastResultArr[$i] + 9] += $parameter;
        if ($zone == "MYAURAS" || $zone == "THEIRAURAS") $zoneDS[$lastResultArr[$i] + 3] += $parameter;
      }
      return $lastResult;
    case "MZADDCOUNTERS":
      $lastResultArr = explode("-", $lastResult);
      $zone = $lastResultArr[0];
      $index = $lastResultArr[1];
      $zoneDS = &GetMZZone($player, $zone);
      if ($zone == "MYAURAS" || $zone == "THEIRAURAS") $zoneDS[$index + 3] += $parameter;
      if ($zone == "MYALLY" || $zone == "THEIRALLY") $zoneDS[$index + 9] += $parameter;
      return $lastResult;
    case "MODDEFCOUNTER":
      if ($lastResult == "") return $lastResult;
      if (substr($lastResult, 0, 5) == "THEIR") {
        $index = intval(explode("-", $lastResult)[1]); 
        $player = $player == 1 ? 2 : 1;
      }
      elseif(substr($lastResult, 0, 5) == "MY") {
        $index = intval(explode("-", $lastResult)[1]);
      }
      else {
        $index = intval($lastResult);
      }
      $character = &GetPlayerCharacter($player);
      $character[$index + 4] = intval($character[$index + 4]) + $parameter;
      if ($parameter < 0) WriteLog(CardLink($character[$index], $character[$index]) . " gets a -1 counter.");
      return $lastResult;
    case "REMOVECOUNTER":
      $character = &GetPlayerCharacter($player);
      $character[$lastResult + 2] -= 1;
      WriteLog(CardLink($parameter, $parameter) . " removed a counter from " . CardLink($character[$lastResult], $character[$lastResult]));
      return $lastResult;
    case "FINALIZEDAMAGE":
      $params = explode(",", $parameter);
      $damage = $dqVars[0];
      $damageThreatened = $params[0];
      if ($damage > $damageThreatened)//Means there was excess damage prevention prevention
      {
        $damage = $damageThreatened;
        $dqVars[0] = $damage;
        $dqState[6] = $damage;
      }
      return FinalizeDamage($player, $damage, $damageThreatened, $params[1], $params[2]);
    case "SETSCOURDQVAR":
      $targetType = 0;
      $myCount = SearchCount(SearchAura($currentPlayer, "", "", 0));
      $otherPlayerCount = SearchCount(SearchAura(($currentPlayer == 1 ? 2 : 1), "", "", 0));
      if($lastResult > $myCount) {
        $targetType = 1;
      }
      elseif ($lastResult > $otherPlayerCount) {
        $targetType = 4;
      }
      WriteLog($lastResult . " " . $targetType . " " . $myCount . " " . $otherPlayerCount);
      $dqVars[$parameter] = $targetType;
      return $lastResult;
    case "SETDQVAR":
      $dqVars[$parameter] = $lastResult;
      return $lastResult;
    case "MZSETDQVAR":
      $cardID = GetMZCard($player, $lastResult);
      $dqVars[$parameter] = $cardID;
      return $lastResult;
    case "INCDQVAR":
      $dqVars[$parameter] = intval($dqVars[$parameter]) + intval($lastResult);
      return $lastResult;
    case "DECDQVAR":
      $dqVars[$parameter] = intval($dqVars[$parameter]) - 1;
      return $lastResult;
    case "INCDQVARIFNOTPASS":
      if ($lastResult != "PASS") $dqVars[$parameter] = intval($dqVars[$parameter]) + 1;
      return $lastResult;
    case "DECDQVARIFNOTPASS":
      if ($lastResult != "PASS") $dqVars[$parameter] = intval($dqVars[$parameter]) - 1;
      return $lastResult;
    case "DIVIDE":
      return floor($lastResult / $parameter);
    case "DQVARPASSIFSET":
      if ($dqVars[$parameter] == "1") return "PASS";
      return "PROCEED";
    case "LORDSUTCLIFFE":
      if ($lastResult == "PASS") return $lastResult;
      LordSutcliffeAfterDQ($player, $parameter);
      return $lastResult;
    case "BINGO":
      if ($lastResult == "") WriteLog("No card was revealed for " . CardLink("bingo_red", "bingo_red"));
      $cardType = CardType($lastResult);
      if ($cardType == "AA") {
        WriteLog(CardLink("bingo_red", "bingo_red") . " gained go again");
        GiveAttackGoAgain();
      } else if (DelimStringContains($cardType, "A")) {
        WriteLog(CardLink("bingo_red", "bingo_red") . " draw a card");
        Draw($player);
      } else WriteLog(CardLink("bingo_red", "bingo_red") . "... did not hit the mark");
      return $lastResult;
    case "ALREADYBLOCKING":
      $alreadyBlocking = false;
      for ($i = 0; $i < count($combatChain); $i += CombatChainPieces()) {
        if ($combatChain[$i] == $parameter) return "PASS";
      }
      return $parameter;
    case "ADDCARDTOCHAINASDEFENDINGCARD":
      if ($parameter == "EQUIP") {
        $character = &GetPlayerCharacter($player);
        for ($i = 0; $i < count($character); $i += CharacterPieces()) {
          if ($character[$i] == $lastResult) {
            $character[$i + 6] = 1;
            PlayCard($lastResult, $parameter, -1, $i, $character[$i + 11]);
            break;
          }
        }
      }
      else AddCombatChain($lastResult, $player, $parameter, 0, -1);
      OnBlockResolveEffects($lastResult);
      OnDefenseReactionResolveEffects("CC", cardID: $lastResult);
      return $lastResult;
    case "ATTACKWITHIT":
      PlayCardSkipCosts($lastResult, "DECK");
      return $lastResult;
    case "HEAVE":
      PrependDecisionQueue("PAYRESOURCES", $player, "<-");
      AddArsenal($lastResult, $player, "HAND", "UP");
      PlayAura("seismic_surge", $player, HeaveValue($lastResult));
      WriteLog("You must pay " . HeaveValue($lastResult) . " resources to heave this");
      return HeaveValue($lastResult);
    case "BRAVOSTARSHOW":
      $hand = &GetHand($player);
      $cards = "";
      $hasLightning = false;
      $hasIce = false;
      $hasEarth = false;
      for ($i = 0; $i < count($lastResult); ++$i) {
        if ($cards != "") $cards .= ",";
        $card = $hand[$lastResult[$i]];
        if (TalentContains($card, "LIGHTNING")) $hasLightning = true;
        if (TalentContains($card, "ICE")) $hasIce = true;
        if (TalentContains($card, "EARTH")) $hasEarth = true;
        $cards .= $card;
      }
      if (RevealCards($cards, $player) && $hasLightning && $hasIce && $hasEarth) {
        WriteLog("Bravo, Star of the Show gives the next attack with cost 3 or more +2, Dominate, and go again");
        AddCurrentTurnEffect("bravo_star_of_the_show", $player);
      }
      return $lastResult;
    case "SETDQCONTEXT":
      $dqState[4] = implode("_", explode(" ", $parameter));
      return $lastResult;
    case "AFTERDIEROLL":
      AfterDieRoll($player);
      return $lastResult;
    case "MODAL":
      $params = explode(",", $parameter);
      return ModalAbilities($player, $params[0], $lastResult, isset($params[1]) ? $params[1] : -1);
    case "MELD":
      MeldCards($player, $parameter, $lastResult);
      return $lastResult;
    case "SCOUR":
      $params = explode(",", $parameter);
      if($params[1] == "MY") $target = 4;
      else $target = 1;
      DealArcane($params[0], $target, "PLAYCARD", "scour_blue");
      return "";
    case "SETABILITYTYPE":
      $lastPlayed[2] = $lastResult;
      $index = GetAbilityIndex($parameter, GetClassState($player, $CS_CharacterIndex), $lastResult);
      SetClassState($player, $CS_AbilityIndex, $index);
      $names = explode(",", GetAbilityNames($parameter, GetClassState($player, $CS_CharacterIndex)));
      if($names[$index] == "-") $names[$index] = "Ability";
      WriteLog(GamestateUnsanitize($names[$index]) . " was chosen.");
      return $lastResult;
    case "SETABILITYTYPEATTACK":
      $index = GetAbilityIndex($parameter, GetClassState($player, $CS_CharacterIndex), "Attack");
      SetClassState($player, $CS_AbilityIndex, $index);
      return $lastResult;
    case "SETABILITYTYPEABILITY":
      $index = GetAbilityIndex($parameter, GetClassState($player, $CS_CharacterIndex), "Ability");
      SetClassState($player, $CS_AbilityIndex, $index);
      return $lastResult;
    case "SETABILITYTYPEATTACKREACTION":
      $index = GetAbilityIndex($parameter, GetClassState($player, $CS_CharacterIndex), "Attack Reaction");
      SetClassState($player, $CS_AbilityIndex, $index);
      return $lastResult;
    case "SETABILITYTYPEDEFENSEREACTION":
      $index = GetAbilityIndex($parameter, GetClassState($player, $CS_CharacterIndex), "Defense Reaction");
      SetClassState($player, $CS_AbilityIndex, $index);
      return $lastResult;
    case "SETABILITYTYPEACTION":
      $index = GetAbilityIndex($parameter, GetClassState($player, $CS_CharacterIndex), "Action");
      SetClassState($player, $CS_AbilityIndex, $index);
      return $lastResult;
    case "MZSTARTTURNABILITY":
      MZStartTurnAbility($player, $lastResult);
      return "";
    case "MZDAMAGE":
      $lastResultArr = explode(",", $lastResult);
      $params = explode(",", $parameter);
      for ($i = 0; $i < count($lastResultArr); ++$i) {
        $mzIndex = explode("-", $lastResultArr[$i]);
        $target = (substr($mzIndex[0], 0, 2) == "MY") ? $player : ($player == 1 ? 2 : 1);
        DamageTrigger($target, $params[0], $params[1], GetMZCard($target, $lastResultArr[$i]));
      }
      return $lastResult;
    case "MZDESTROY":
      return MZDestroy($player, $lastResult, allArsenal: $parameter);
    case "MZUNDESTROY":
      return MZUndestroy($player, $parameter, $lastResult);
    case "MZBANISH":
      return MZBanish($player, $parameter, $lastResult);
    case "MZREMOVE":
      return MZRemove($player, $lastResult);
    case "MZDISCARD":
      return MZDiscard($player, $parameter, $lastResult);
    case "MZADDZONE":
      return MZAddZone($player, $parameter, $lastResult);
    case "MZREVEAL":
      return MZReveal($player, $parameter, $lastResult);
    case "MZBOUNCE":
      return MZBounce($player, $lastResult);
    case "MZBOTTOM":
      return MZBottom($player, $lastResult);
    case "MZSWITCHPLAYER":
      return MZSwitchPlayer($lastResult);
    case "GAINRESOURCES":
      GainResources($player, $parameter);
      return $lastResult;
    case "TRANSFORM":
      $params = explode(",", $parameter);
      return "ALLY-" . ResolveTransform($player, $lastResult, $params[0], $params[1]);
    case "TRANSFORMPERMANENT":
      $params = explode(",", $parameter);
      return "PERMANENT-" . ResolveTransformPermanent($player, $lastResult, $parameter);
    case "TRANSFORMAURA":
      return "AURA-" . ResolveTransformAura($player, $lastResult, $parameter);
    case "TRANSFORMHERO":
      return ResolveTransformHero($player, $parameter, $lastResult);
    case "AFTERTHAW":
      $otherPlayer = ($player == 1 ? 2 : 1);
      $params = explode("-", $lastResult);
      if ($params[0] == "MYAURAS") DestroyAura($player, $params[1]);
      else if($params[0] == "MYCHAR") DestroyAura($player, $params[1], "", "EQUIP");
      else UnfreezeMZ($player, $params[0], $params[1]);
      return "";
    case "SUCCUMBTOWINTER":
      $params = explode("-", $lastResult);
      $otherPlayer = ($player == 1 ? 2 : 1);
      if ($params[0] == "THEIRALLY") {
        $allies = &GetAllies($otherPlayer);
        WriteLog(CardLink($params[2], $params[2]) . " destroyed your frozen ally");
        if ($allies[$params[1] + 8] == "1") DestroyAlly($otherPlayer, $params[1], uniqueID: $allies[$params[1] + 5]);
      } else {
        DestroyFrozenArsenal($otherPlayer);
        WriteLog(CardLink($params[2], $params[2]) . " destroyed your frozen arsenal card");
        break;
      }
      return $lastResult;
    case "STARTGAME":
      $inGameStatus = "1";
      $MakeStartTurnBackup = true;
      $MakeStartGameBackup = true;
      return 0;
    case "QUICKREMATCH":
      $currentTime = round(microtime(true) * 1000);
      SetCachePiece($gameName, 2, $currentTime);
      SetCachePiece($gameName, 3, $currentTime);
      ClearGameFiles($gameName);
      include "MenuFiles/ParseGamefile.php";
      header("Location: " . $redirectPath . "/Start.php?gameName=$gameName&playerID=$playerID");
      exit;
    case "REMATCH":
      global $GameStatus_Rematch, $inGameStatus;
      if ($lastResult == "YES") {
        $inGameStatus = $GameStatus_Rematch;
        ClearGameFiles($gameName);
      }
      return 0;
    case "PLAYERTARGETEDABILITY":
      PlayerTargetedAbility($player, $parameter, $lastResult);
      return "";
    case "DQPAYORDISCARD":
      PayOrDiscard($player, $parameter);
      return "";
    case "SPECIFICCARD":
      $parameterArr = explode(",", $parameter);
      $parameter = $parameterArr[0];
      if (count($parameterArr) > 1) $initiator = $parameterArr[1];
      else $initiator = "";
      return SpecificCardLogic($player, $parameter, $lastResult, $initiator);
    case "HYPERDRIVER":
      $index = SearchItemsForUniqueID($parameter, $player);
      $items = &GetItems($player);
      --$items[$index + 1];
      GainResources($player, 1);
      if ($items[$index + 1] <= 0) DestroyItemForPlayer($player, $index);
      return $lastResult;
    case "MZADDCOUNTERANDEFFECT":
      $lastResultArr = explode(",", $lastResult);
      $otherPlayer = ($player == 1 ? 2 : 1);
      $params = explode(",", $parameter);
      for ($i = 0; $i < count($lastResultArr); ++$i) {
        $mzIndex = explode("-", $lastResultArr[$i]);
        switch ($mzIndex[0]) {
          case "MYARS":
            $arsenal = &GetArsenal($currentPlayer);
            $arsenal[$mzIndex[1] + 3] = 1;
            WriteLog(CardLink($arsenal[$mzIndex[1]], $arsenal[$mzIndex[1]]) . " gained an aim counter.");
            AddCurrentTurnEffect($params[0], $currentPlayer, "HAND", $arsenal[$mzIndex[1] + 5]);
            break;
          default:
            break;
        }
      }
      return $lastResult;
    case "MZADDCOUNTER":
      $lastResultArr = explode(",", $lastResult);
      $otherPlayer = ($player == 1 ? 2 : 1);
      $params = explode(",", $parameter);
      for ($i = 0; $i < count($lastResultArr); ++$i) {
        $mzIndex = explode("-", $lastResultArr[$i]);
        switch ($mzIndex[0]) {
          case "MYITEMS":
            $items = &GetItems($player);
            $items[$mzIndex[1] + 1] += 1;
            WriteLog(CardLink($items[$mzIndex[1]], $items[$mzIndex[1]]) . " gained a steam counter.");
            break;
          case "MYARS":
            $arsenal = &GetArsenal($currentPlayer);
            $arsenal[$mzIndex[1] + 3] = 1;
            WriteLog(CardLink($arsenal[$mzIndex[1]], $arsenal[$mzIndex[1]]) . " gained an aim counter.");
            break;
          case "LAYER":
            AddCurrentTurnEffect($params[0]."-".GetMZCard($currentPlayer, $lastResultArr[$i]), $currentPlayer, "LAYER");
            WriteLog(GetMZCardLink($currentPlayer, $lastResultArr[$i]) . " gained a steam counter.");
            break;
          default:
            break;
        }
      }
      return $lastResult;
    case "SUPERCELL":
      $lastResultArr = explode(",", $lastResult);
      for ($i = 0; $i < count($lastResultArr); ++$i) {
        $mzIndex = explode("-", $lastResultArr[$i]);
        switch ($mzIndex[0]) {
          case "MYITEMS":
            $items = &GetItems($player);
            $items[$mzIndex[1] + 1] += $parameter;
            WriteLog(CardLink($items[$mzIndex[1]], $items[$mzIndex[1]]) . " gained a steam counter.");
            break;
        }
      }
      $dqVars[0] = str_replace($lastResult, "", $dqVars[0]);
      if (substr($dqVars[0], 0, 1) == ",") $dqVars[0] = substr($dqVars[0], 1);
      if (substr($dqVars[0], -1) == ",") $dqVars[0] = rtrim($dqVars[0], ",");
      $dqVars[0] = str_replace(",,", ",", $dqVars[0]);
      return $lastResult;
    case "MZREMOVECOUNTER":
      $lastResultArr = explode(",", $lastResult);
      $otherPlayer = ($player == 1 ? 2 : 1);
      $params = explode(",", $parameter);
      $removedSteamCounterCount = 0;
      for ($i = count($lastResultArr); $i > 0; --$i) {
        $mzIndex = explode("-", $lastResultArr[$i]);
        switch ($mzIndex[0]) {
          case "THEIRITEMS":
          case "MYITEMS":
            $controller = str_starts_with($mzIndex[0], "MY") ? $player : $otherPlayer;
            $items = &GetItems($controller);
            $removedSteamCounterCount = $items[$mzIndex[1] + 1];
            $items[$mzIndex[1] + 1] = 0;
            WriteLog(CardLink($items[$mzIndex[1]], $items[$mzIndex[1]]) . " lost all their steam counters");
            if (DestroyItemWithoutSteamCounter($items[$mzIndex[1]], $controller)) {
              DestroyItemForPlayer($controller, $mzIndex[1]);
            }
            break;
          case "THEIRCHAR":
          case "MYCHAR":
            $controller = str_starts_with($mzIndex[0], "MY") ? $player : $otherPlayer;
            $characters = &GetPlayerCharacter($controller);
            $removedSteamCounterCount = $characters[$mzIndex[1] + 2];
            $characters[$mzIndex[1] + 2] = 0;
            WriteLog(CardLink($characters[$mzIndex[1]], $characters[$mzIndex[1]]) . " lost all their steam counters");
            break;
          default:
            break;
        }
      }
      return $controller . "-" . $removedSteamCounterCount;
    case "SYSTEMFAILURE":
      $lastResultArr = explode("-", $lastResult);
      if (count($lastResultArr) < 2) return "";
      $removedSteamCounterCount = $lastResultArr[1];
      if ($removedSteamCounterCount >= 2) {
        return DealDamageAsync($lastResultArr[0], 2);
      }
      return "";
    case "ONHITEFFECT":
      $cardID = $lastResult;
      $location = $dqVars[2];
      AddOnHitTrigger($cardID);
      if (DelimStringContains($location, "COMBATCHAINATTACKS", true) && TypeContains($cardID, "AA")) { //Kiss of Death added effects
        $index = intval(explode("-", $location)[1]) / ChainLinksPieces();
        $activeEffects = explode(",", $chainLinks[$index][6]);
        foreach ($activeEffects as $effect) {
          AddEffectHitTrigger($effect, $cardID);
          AddOnHitTrigger($effect, source:$cardID);
          AddCardEffectHitTrigger($effect, $cardID);
        }
      }
      for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
        if ($currentTurnEffects[$i] == "celestial_kimono") AddLayer("TRIGGER", $currentTurnEffects[$i + 1], "celestial_kimono");
        if (IsCombatEffectActive($currentTurnEffects[$i], flicked: true) && $currentTurnEffects[$i + 1] == $mainPlayer) {
          AddCardEffectHitTrigger($currentTurnEffects[$i], $cardID); // Effects that do not gives it's effect to the attack
        }
      }
      MainCharacterHitTrigger($cardID);
      ArsenalHitEffects(); // should be reworked to add a triggered-layer, but not urgent
      AuraHitEffects($cardID);
      ItemHitTrigger($cardID);
      //mask of momentum
      $mainChar = &GetPlayerCharacter($mainPlayer);
      $momIndex = FindCharacterIndex($mainPlayer, "mask_of_momentum");
      if($momIndex != -1 && $mainChar[$momIndex + 5] > 0){
        --$mainChar[$momIndex + 5];
        $count = CountCurrentTurnEffects($characterID, $mainPlayer);
        if($mainCharacter[$momIndex + 1] == 2 && $count <= HitsInRow() && $count <= count($chainLinks) && $count <= 3) {
          AddCurrentTurnEffect("mask_of_momentum", $mainPlayer);
        }
      }
      //handling flick knives and mark
      if (CheckMarked($defPlayer)) {
        RemoveMark($defPlayer);
      }
      return $parameter;
    case "AWAKEN":
      $mzArr = explode("-", $parameter);
      $permanents = &GetPermanents($player);
      $cardID = $permanents[$mzArr[1]];
      $newCardID = match($cardID) {
        "figment_of_erudition_yellow" => "suraya_archangel_of_erudition",
        "figment_of_judgment_yellow" => "themis_archangel_of_judgment",
        "figment_of_protection_yellow" => "aegis_archangel_of_protection",
        "figment_of_ravages_yellow" => "sekem_archangel_of_ravages",
        "figment_of_rebirth_yellow" => "avalon_archangel_of_rebirth",
        "figment_of_tenacity_yellow" => "metis_archangel_of_tenacity",
        "figment_of_triumph_yellow" => "victoria_archangel_of_triumph",
        "figment_of_war_yellow" => "bellona_archangel_of_war"
      };
      WriteLog(CardLink($cardID, $cardID) . " awakened into " . CardLink($newCardID, $newCardID));
      RemovePermanent($player, $mzArr[1]);
      PlayAlly($newCardID, $player);
      return "1";
    case "PROCESSDAMAGEPREVENTION":
      $mzIndex = explode("-", $lastResult);
      $params = explode("-", $parameter);
      switch ($mzIndex[0]) {
        case "MYAURAS":
          $damage = AuraTakeDamageAbility($player, intval($mzIndex[1]), $params[0], $params[1], $params[2]);
          break;
        case "MYCHAR":
          $damage = CharacterTakeDamageAbility($player, intval($mzIndex[1]), $params[0], $params[1]);
          break;
        case "MYALLY":
          $damage = AllyTakeDamageAbilities($player, intval($mzIndex[1]), $params[0], $params[1]);
          break;
        case "MYITEMS":
          $damage = ChosenItemTakeDamageAbilities($player, intval($mzIndex[1]), $params[0], $params[1]);
          break;
        default:
          break;
      }
      if ($damage < 0) $damage = 0;
      $dqVars[0] = $damage;
      $dqState[6] = $damage;
      if ($damage > 0) AddDamagePreventionSelection($player, $damage, $params[2], $params[1]);
      return $damage;
    case "EQUIPCARDINVENTORY":
      if (str_contains($parameter, "-")) {
        $from = explode('-', $parameter)[1];
        $parameter = explode('-', $parameter)[0];
        if ($from == "INVENTORY") {
          $inventory = &GetInventory($player);
          $indexToRemove = array_search($parameter, $inventory);
          if ($indexToRemove !== false) unset($inventory[$indexToRemove]);
        }
      }
      if (DelimStringContains(CardType($parameter), "W", true)) EquipWeapon($player, $parameter);
      else EquipEquipment($player, $parameter);
      return "";
    case "LISTDRACDAGGERGRAVEYARD":
      return ListDracDaggersGraveyard($player);
    case "EQUIPCARDGRAVEYARD":
      $index = SearchGetFirstIndex(SearchMultizone($currentPlayer, "MYDISCARD:cardID=" . $parameter));
      RemoveGraveyard($currentPlayer, $index);
      if (CardType($parameter) == "W") EquipWeapon($player, $parameter);
      else EquipEquipment($player, $parameter);
      return "";
    case "EQUIPCARD":
      $params = explode('-', $parameter);
      EquipEquipment($player, $params[0], $params[1]);
      return "";
    case "REMOVEMODULAR":
      $character = &GetPlayerCharacter($player);
      $index = -1;
      for ($i = 0; $i < count($character); $i += CharacterPieces()) {
        if ($character[$i + 11] == $parameter) {
          $index = $i;
          break;
        }
      }
      RemoveCharacter($player, $index);
      $effectIndex = -1;
      for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectPieces()) {
        if (DelimStringContains($currentTurnEffects[$i], $parameter, partial:true)) {
          $effectIndex = $i;
          break;
        }
      }
      RemoveCurrentTurnEffect($effectIndex);
      if ($index == -1) WriteLog("Something went horribly wrong, please submit a bug report");
      return "";
    case "ROGUEMIRRORGAMESTART":
      $deck = &GetDeck($player);
      for ($mirrorAmount = 0; $mirrorAmount < 7; ++$mirrorAmount) {
        array_unshift($deck, $lastResult);
      }
      return $lastResult;
    case "ROGUEMIRRORTURNSTART":
      $deck = &GetDeck($player);
      $hand = &GetHand($player);
      if (count($deck) > 3) {
        $optionOne = rand(0, count($deck) - 1);
        $optionTwo = rand(0, count($deck) - 1);
        $optionThree = rand(0, count($deck) - 1);
        if ($optionOne == $optionTwo) {
          if ($optionOne == 0) ++$optionTwo;
          else --$optionTwo;
        }
        for ($i = 0; $i < 5 && ($optionThree == $optionOne || $optionThree == $optionOne); ++$i) {
          if ($optionThree <= 4) $optionThree += 3;
          else --$optionThree;
        }
        $deck[$optionOne] = $hand[$lastResult];
        $deck[$optionTwo] = $hand[$lastResult];
        $deck[$optionThree] = $hand[$lastResult];
      } else {
        for ($deckCount = 0; $deckCount < count($deck); ++$deckCount) {
          $deck[$deckCount] = $hand[$lastResult];
        }
      }
      return $lastResult;
    case "ROGUEDECKCARDSTURNSTART":
      $deck = &GetDeck($player);
      $hand = &GetHand($player);
      array_unshift($deck, $hand[$lastResult]);
      return $lastResult;
    case "GETTARGETOFATTACK":
      $params = explode(",", $parameter);
      if ((CardType($params[0]) == "AA" && (GetResolvedAbilityType($params[0], $params[1]) == "") || GetResolvedAbilityType($params[0], $params[1]) == "AA")) GetTargetOfAttack($params[0]);
      return $lastResult;
    case "INTIMIDATE":
      $otherPlayer = $player == 1 ? 2 : 1;
      if ($parameter != "-") $player = $parameter;
      else $player = $lastResult == "MYCHAR-0" ? $currentPlayer : $otherPlayer;
      WriteLog("Player {$player} was targeted to intimidate.");
      $hand = &GetHand($player);
      if (count($hand) == 0) return; //Intimidate did nothing because there are no cards in their hand
      $index = GetRandom() % count($hand);
      BanishCardForPlayer($hand[$index], $player, "HAND", "INT");
      RemoveHand($player, $index);
      WriteLog("Player {$player} banishes a card face down");
      return $player;
    case "REMOVESUBCARD":
      $char = &GetPlayerCharacter($player);
      $subcards = explode(",", $char[$parameter + 10]);
      $subcardsCount = count($subcards);
      $cardID = "";
      for ($i = 0; $i < $subcardsCount; $i++) {
        if (is_array($lastResult)) {
          if (in_array($subcards[$i], $lastResult)) {
            if ($cardID == "") $cardID = $subcards[$i];
            else $cardID = $cardID . "," . $subcards[$i];
            array_splice($lastResult, array_search($subcards[$i], $lastResult), 1);
            array_splice($subcards, $i, 1);
            $i--;
            $subcardsCount--;
            if (count($lastResult) == 0) break;
          }
        } else {
          if ($subcards[$i] == $lastResult) {
            $cardID = $subcards[$i];
            array_splice($subcards, $i, 1);
            break;
          }
        }
      }
      $char[$parameter + 10] = implode(",", $subcards);
      UpdateSubcardCounterCount($currentPlayer, $parameter);
      return $cardID;
    case "REMOVESOUL":
      $char = &GetPlayerCharacter($player);
      for ($i = 0; $i < count($lastResult); $i++) {
        RemoveSoul($player, SearchSoulForIndex($lastResult[$i], $player));
      }
      return $lastResult;
    case "REMOVECOUNTERAURAORDESTROY":
      $auras = &GetAuras($player);
      $index = SearchAurasForUniqueID($parameter, $player);
      if ($lastResult == "YES") {
        --$auras[$index + 2];
        WriteLog("Player " . $playerID . " removed a counter from " . CardLink($auras[$index], $auras[$index]) . ".");
      } else {
        DestroyAuraUniqueID($player, $auras[$index + 6]);
        WriteLog("Player " . $playerID . " did not remove a counter and " . CardLink($auras[$index], $auras[$index]) . " was destroyed");
      }
      return "";
    case "REMOVECOUNTERITEMORDESTROY":
      $items = &GetItems($player);
      if ($lastResult == "YES") --$items[$parameter + 1];
      else {
        DestroyItemForPlayer($player, $parameter);
        WriteLog(CardLink($items[$parameter], $items[$parameter]) . " was destroyed");
      }
      return "";
    case "ADDBOTTOMREMOVETOP":
      $deck = new Deck($player);
      $card = $deck->AddBottom($deck->Top(remove: true), "DECK");
      WriteLog("Player " . $player . " put " . CardLink($card, $card) . " on the bottom of the deck and Clash again!");
      return "";
    case "CLASH":
      ClashLogic($parameter, $player);
      return "";
    case "WONCLASH":
      $winner = $dqVars[0];
      $params = explode(",", $parameter);
      if ($winner > 0) {
        WonClashAbility($winner, $params[0], $params[1]);
      }
      else {
        $char = GetPlayerCharacter($mainPlayer);
        $defChar = GetPlayerCharacter($defPlayer);
        if ($char[0] == "brutus_summa_rudis") {
          AddLayer("TRIGGER", $mainPlayer, $char[0], $parameter);
        }
        elseif ($defChar[0] == "brutus_summa_rudis") {
          AddLayer("TRIGGER", $defPlayer, $defChar[0], $parameter);
        }
      }
      if ($params[0] == "trounce_red") {
        $p1Deck = new Deck(1);
        $p1Deck->AddBottom($p1Deck->Top(remove: true));
        $p2Deck = new Deck(2);
        $p2Deck->AddBottom($p2Deck->Top(remove: true));
        Clash("trounce_red-" . $winner, effectController: $defPlayer);
      }
      return "";
    case "DEAL1DAMAGE":
      DamageTrigger($player, damage: 1, type: "DAMAGE", source: $parameter);
      return "";
    case "REMOVEINDICESIFACTIVECHAINLINK":
      $indices = explode(",", $lastResult);
      $char = GetPlayerCharacter($player);
      for ($i = 0; $i < count($indices); $i++) {
        $option = explode("-", $indices[$i]);
        if ($option[0] == "MYCHAR") {
          if ($char[$option[1]] == $combatChain[0] && $char[$option[1] + 11] == $combatChain[8]) {
            $lastResult = str_replace($indices[$i], "", $lastResult);
            $lastResult = rtrim($lastResult, ",");
            $lastResult = ltrim($lastResult, ",");
            $lastResult = str_replace(",,", ",", $lastResult);
          }
        }
      }
      return $lastResult;
    case "CHANGESHIYANA":
      $otherPlayer = ($player == 1 ? 2 : 1);
      $otherChar = GetPlayerCharacter($otherPlayer);
      if ($lastResult != "shiyana_diamond_gemini" && $otherChar[0] != "DUMMY") {
        $lifeDifference = GeneratedCharacterHealth("shiyana_diamond_gemini") - GeneratedCharacterHealth($otherChar[0]);
        if ($lifeDifference > 0) LoseHealth($lifeDifference, $player);
        elseif ($lifeDifference < 0) GainHealth(abs($lifeDifference), $player, true, false);
      }
      if ($otherChar[0] == "victor_goldmane_high_and_mighty" || $otherChar[0] == "victor_goldmane") {
        AddCurrentTurnEffect($otherChar[0] . "-1", $mainPlayer);
      }
      return $lastResult;
    case "ALREADYDEAD":
      $type = CardType($lastResult);
      switch ($type) {
        case "E":
          BanishCardForPlayer($lastResult, $defPlayer, "CC", "-", $mainPlayer);
          $index = FindCharacterIndex($defPlayer, $lastResult);
          DestroyCharacter($defPlayer, $index, wasBanished: true);
          break;
        default:
          BanishCardForPlayer($lastResult, $defPlayer, "CC", "REMOVEGRAVEYARD", $mainPlayer);
          $index = GetCombatChainIndex($lastResult, $defPlayer);
          if ($CombatChain->Remove($index) == "") {
            for ($i = 0; $i < count($chainLinks); ++$i) {
              for ($j = 0; $j < count($chainLinks[$i]); $j += ChainLinksPieces()) {
                if ($chainLinks[$i][$j] == $lastResult) $chainLinks[$i][$j + 2] = 0;
              }
            }
          }
          break;
      }
      WriteLog(CardLink($lastResult, $lastResult) . " was banished.");
      return $lastResult;
    case "POWDERKEG":
      $index = FindCharacterIndex($defPlayer, $lastResult);
      DestroyCharacter($defPlayer, $index);
      WriteLog(CardLink($lastResult, $lastResult) . " was destroyed");
      DestroyItemForPlayer($player, SearchItemForIndex("powder_keg_blue", $player));
      return $lastResult;
    case "ADDTRIGGER":
      $params = explode(",", $parameter);
      if (count($params) < 2) $target = $lastResult;
      else $target = $params[1];
      // for now only do this for blast to oblivion
      if ($params[0] == "blast_to_oblivion_red" || $params[0] == "blast_to_oblivion_yellow" || $params[0] == "blast_to_oblivion_blue") {
        if (DelimStringContains($lastResult, "THEIR", true)) $targettedPlayer = $player == 1 ? 2 : 1;
        else $targettedPlayer = $player;
        WriteLog(GetMZCardLink($targettedPlayer, $lastResult) . " targetted by " . CardLink($params[0], $params[0]) . "'s trigger");
      }
      AddLayer("TRIGGER", $player, $params[0], $target);
      return $lastResult;
    case "UNDERCURRENTDESIRES":
      if ($lastResult == "") {
        WriteLog("No cards were selected, " . CardLink("sacred_art_undercurrent_desires_blue", "sacred_art_undercurrent_desires_blue") . " did not banish any cards");
        return $lastResult;
      }
      $cards = explode(",", $lastResult);
      $message = CardLink("sacred_art_undercurrent_desires_blue", "sacred_art_undercurrent_desires_blue") . " banished ";
      for ($i = 0; $i < count($cards); ++$i) {
        if ($i >= 1) $message .= ", ";
        if ($i != 0 && $i == count($cards) - 1) $message .= "and ";
        $message .= CardLink($cards[$i], $cards[$i]);
      }
      WriteLog($message);
      return $lastResult;
    case "AMULETOFOBLATION":
      $params = explode("!", $parameter);
      $target = GetMZCard($mainPlayer, $params[1] . "-" . $lastResult);
      AddCurrentTurnEffect($params[0] . $target, GetMZCard($mainPlayer, $params[1] . "-" . $lastResult + 1), (count($params) > 1 ? $params[1] : ""));
      WriteLog(CardLink("amulet_of_oblation_blue", "amulet_of_oblation_blue") . " targeted " . CardLink($target, $target));
      return $lastResult;
    case "FABRICATE":
      $char = &GetPlayerCharacter($currentPlayer);
      $inventory = &GetInventory($currentPlayer);
      $equipments = "";
      foreach ($inventory as $cardID) {
        if (TypeContains($cardID, "E", $currentPlayer) && CardNameContains($cardID, "Proto", $currentPlayer, true)) {
          switch (CardSubType($cardID)) {
            case "Base,Head":
              if (!SearchCharacterAliveSubtype($currentPlayer, "Head")) {
                if ($equipments != "") $equipments .= ",";
                $equipments .= $cardID;
              }
              break;
            case "Base,Chest":
              if (!SearchCharacterAliveSubtype($currentPlayer, "Chest")) {
                if ($equipments != "") $equipments .= ",";
                $equipments .= $cardID;
              }
              break;
            case "Base,Arms":
              if (!SearchCharacterAliveSubtype($currentPlayer, "Arms")) {
                if ($equipments != "") $equipments .= ",";
                $equipments .= $cardID;
              }
              break;
            case "Base,Legs":
              if (!SearchCharacterAliveSubtype($currentPlayer, "Legs")) {
                if ($equipments != "") $equipments .= ",";
                $equipments .= $cardID;
              }
              break;
            default:
              break;
          }
        }
      }
      WriteLog($equipments);
      if ($equipments == "") {
        WriteLog("🚫Proto Equipments not found in your inventory");
        return "PASS";
      } else return $equipments;
    case "VISITTHEGOLDENANVIL":
      $equipments = "";
      $char = &GetPlayerCharacter($currentPlayer);
      $inventory = &GetInventory($currentPlayer);
      foreach ($inventory as $cardID) {
        if (TypeContains($cardID, "W", $currentPlayer)) {
          if ($char[CharacterPieces() + 1] == 0 || (Is1H($cardID) && $char[CharacterPieces() * 2 + 1] == 0)) {
            if ($equipments != "") $equipments .= ",";
            $equipments .= $cardID;
          }
        }
        if (TypeContains($cardID, "E", $currentPlayer)) {
          switch (CardSubType($cardID)) {
            case "Head":
              if (!SearchCharacterAliveSubtype($currentPlayer, "Head")) {
                if ($equipments != "") $equipments .= ",";
                $equipments .= $cardID;
              }
              break;
            case "Chest":
              if (!SearchCharacterAliveSubtype($currentPlayer, "Chest")) {
                if ($equipments != "") $equipments .= ",";
                $equipments .= $cardID;
              }
              break;
            case "Arms":
              if (!SearchCharacterAliveSubtype($currentPlayer, "Arms")) {
                if ($equipments != "") $equipments .= ",";
                $equipments .= $cardID;
              }
              break;
            case "Legs":
              if (!SearchCharacterAliveSubtype($currentPlayer, "Legs")) {
                if ($equipments != "") $equipments .= ",";
                $equipments .= $cardID;
              }
              break;
            case "Off-Hand":
              if (!SearchCharacterAliveSubtype($currentPlayer, "Off-Hand")) {
                if ($equipments != "") $equipments .= ",";
                $equipments .= $cardID;
              }
              break;
            case "Quiver":
              if (!SearchCharacterAliveSubtype($currentPlayer, "Quiver")) {
                if ($equipments != "") $equipments .= ",";
                $equipments .= $cardID;
              }
              break;
            default:
              break;
          }
        }
      }
      return $equipments;
    case "LISTEMPTYEQUIPSLOTS":
      $character = &GetPlayerCharacter($player);
      $available = array_filter(["Head", "Chest", "Arms", "Legs"], function ($slot) use ($character) {
        for ($i = 0; $i < count($character); $i += CharacterPieces()) {
          $subtype = CardSubType($character[$i], $character[$i + 11]);
          if (DelimStringContains($subtype, $slot)) return false;
        }
        return true;
      });
      return empty($available) ? "PASS" : implode(",", $available);
      case "LISTEXPOSEDEQUIPSLOTS":
        $character = &GetPlayerCharacter($player);
        $available = array_filter(["Head", "Chest", "Arms", "Legs"], function ($slot) use ($character) {
          for ($i = 0; $i < count($character); $i += CharacterPieces()) {
            $subtype = CardSubType($character[$i], $character[$i + 11]);
            $status = $character[$i + 1];
            if (DelimStringContains($subtype, $slot) && $status != 0) return false;
          }
          return true;
        });
        return empty($available) ? "PASS" : implode(",", $available);
    case "TRANSCEND":
      $params = explode(",", $parameter);
      Transcend($player, $params[0], $params[1]);
      return $lastResult;
    case "CURRENTEFFECTAFTERPLAYORACTIVATEABILITY";
      CurrentEffectAfterPlayOrActivateAbility();
      return $lastResult;
    case "ENIGMAMOON":
      $character = &GetPlayerCharacter($player);
      $MZZone = explode("-", $lastResult);
      $character[$MZZone[1] + 12] = "UP";
      if (hasWard($character[$MZZone[1]], $player)) PlayAura("spectral_shield", $player, 3);
      return $lastResult;
    case "BLAZE":
      $character = &GetPlayerCharacter($player);
      $character[2] += $parameter;
      return $lastResult;
    case "BLAZEPAYCOST":
      $character = &GetPlayerCharacter($player);
      $character[2] -= $lastResult;
      return $lastResult;
    case "LOGPLAYCARDSTATS":
      $param = explode(",", $parameter);
      LogPlayCardStats($player, $param[0], $param[1], $param[2]);
      return $lastResult;
    case "GAINLIFE":
      $param = explode(",", $parameter);
      GainHealth($param[0], $player);
      return $lastResult;
    case "GETCARDSFORDECOMPOSE":
      $rv = SearchMultizone($player, $parameter); // I want multizone to return a blank string if no results so I built this
      return $rv;
    case "REMOVEPREVIOUSCHOICES":
      $lastResult = str_replace($parameter, "", $lastResult);
      $lastResult = trim($lastResult, ",");
      $lastResult = rtrim($lastResult, ",");
      $lastResult = str_replace(",,", ",", $lastResult);
      return $lastResult;
    case "GONEINAFLASH":
      AddLayer("TRIGGER", $player, "gone_in_a_flash_red");
      return $lastResult;
    case "TRUCE":
      if (SearchCurrentTurnEffects("truce_blue", $defPlayer, remove: true)){
        $theirAuras = &GetAuras($defPlayer);
        for ($i = count($theirAuras) - AuraPieces(); $i >= 0; $i -= AuraPieces()) {
          switch ($theirAuras[$i]) {
            case "truce_blue":
              AddLayer("TRIGGER", $defPlayer, $theirAuras[$i], "truce_blue-2", uniqueID: $theirAuras[$i + 6]);
              break;
            default:
              break;
          }
        }
      }
      return $lastResult;
    case "NEGATE":
      NegateLayer($lastResult);
      return $lastResult;
    case "VERDANCE":
      DealArcane(1, 3, "ABILITY", $parameter);
      return $lastResult;
    case "BRUTUS":
      $otherPlayer = $player == 1 ? 2 : 1;
      if($lastResult == "MYCHAR-0") $dqVars[0] = $player;
      else $dqVars[0] = $otherPlayer;
      AddDecisionQueue("WONCLASH", $player, $parameter);
      return $lastResult;
    case "SUMMERSFALL":
      $params = explode(",", $parameter);
      if($dqVars[0] == "YES") {
        if($lastResult == "PASS") {
          AddDecisionQueue("ADDTRIGGER", $currentPlayer, $parameter);
        }
        else {
          AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
          AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $params[0], 1);
          AddDecisionQueue("ADDTRIGGER", $currentPlayer, $params[0], 1);
        }
      }
      return $lastResult;
    case "UNDERTRAPDOOR":
      AddCurrentTurnEffect("under_the_trapdoor_blue", $currentPlayer, "", $parameter);
      return $lastResult;
    case "CURRENTATTACKBECOMES":
      WriteLog(CardLink($combatChain[0], $combatChain[0]) . " copy and become " . CardLink($lastResult, $lastResult));
      $combatChainState[$CCS_LinkBaseAttack] = AttackValue($lastResult);
      $combatChain[0] = $lastResult;
      return $lastResult;
    case "EXTRAATTACK":
      $ind = explode("-", $parameter)[1];
      $char = &GetPlayerCharacter($player);
      $char[$ind+5]++;
      if ($char[$ind+1] == 1) $char[$ind+1]++;
      return $lastResult;
    case "PERFORATE":
      $ind = explode("-", $parameter)[1];
      $char = &GetPlayerCharacter($player);
      AddCurrentTurnEffect("perforate_yellow", $player,"", $char[$ind+11]);
      return $lastResult;
    case "ADDONHITMARK":
      $ind = explode("-", $parameter)[1];
      $char = &GetPlayerCharacter($player);
      AddCurrentTurnEffect("long_whisker_loyalty_red-MARK" . "," . $char[$ind+11], $player,"", $char[$ind+11]);
      return $lastResult;
    case "PROVOKE":
      $handInd = explode("-", $lastResult)[1];
      $hand = &GetHand($player);
      $cardID = $hand[$handInd];
      //Right now it's unclear what happens to action cards selected when they can't be blocked with (eg dominate)
      //I'm implementing it right now as the effect failing
      if (CardType($cardID) == "A" | CardType($cardID) == "AA") {
        if (CanBlock($cardID)) {
          AddCombatChain($cardID, $player, "HAND", 0, -1);
          OnBlockResolveEffects($cardID);
          unset($hand[$handInd]);
          $hand = array_values($hand);
        }
      }
      else {
        AddGraveyard($cardID, $player, "HAND");
        unset($hand[$handInd]);
        $hand = array_values($hand);
      }
      return $lastResult;
    case "COMPARENUMBERS":
      $otherPlayer = $player == 1 ? 2 : 1;
      WriteLog("Player " . $player . " chose number " . $dqVars[0]);
      WriteLog("Player " . $otherPlayer . " chose number " . $dqVars[1]);
      if ($dqVars[0] > $dqVars[1]) return $player;
      elseif ($dqVars[0] < $dqVars[1]) return $otherPlayer;
      return "PASS";
    case "CHAOSTRANSFORM":
      ChaosTransform($parameter, $player, true, $lastResult);
      return $lastResult;
    case "LEAPFROG":
      // remove leapfrog from current link
      for ($i = 0; $i < count($chainLinks); ++$i) {
        for ($j = ChainLinksPieces(); $j < count($chainLinks[$i]); $j += ChainLinksPieces()) {
          if ($chainLinks[$i][$j] == $parameter) {
            $chainLinks[$i][$j+2] = 0;
          }
        }
      }
      $char = &GetPlayerCharacter($player);
      for ($i = 0; $i < count($char); $i += CharacterPieces()) {
        if ($char[$i] == $parameter) {
          $ind = $i;
          break;
        }
      }
      $originUniqueID = $char[$ind + 11];
      # check if it's already there
      $onCombatChain = false;
      for ($i = 0; $i < count($combatChain); $i += CombatChainPieces()) {
        if ($combatChain[$i+8] == $originUniqueID) {
          $onCombatChain = true;
          break;
        }
      }
      if (!$onCombatChain) AddCombatChain($parameter, $player, "EQUIP", "-", $originUniqueID);
      $char[$ind + 6] = 1;
      return $lastResult;
    case "SPURLOCKED":
      $otherPlayer = $player == 1 ? 2 : 1;
      if($lastResult == "PASS") {
        WriteLog("🎲 Nothing Happened");
      }
      elseif($lastResult == $player) {
        LoseHealth($dqVars[0], $player);
        AddDecisionQueue("MULTIZONEINDICES", $player, "MYDECK:maxCost=" . $dqVars[0], 1);
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
        AddDecisionQueue("MZADDZONE", $player, "MYHAND,DECK", 1);
        AddDecisionQueue("MZREMOVE", $player, "-", 1);
        AddDecisionQueue("REVEALCARDS", $player, "-", 1);
        AddDecisionQueue("SHUFFLEDECK", $player, "-", 1);  
      }
      else {
        LoseHealth($dqVars[1], $otherPlayer);
        AddDecisionQueue("MULTIZONEINDICES", $otherPlayer, "MYDECK:maxCost=" . $dqVars[1], 1);
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $otherPlayer, "<-", 1);
        AddDecisionQueue("MZADDZONE", $otherPlayer, "MYHAND,DECK", 1);
        AddDecisionQueue("MZREMOVE", $otherPlayer, "-", 1);
        AddDecisionQueue("REVEALCARDS", $otherPlayer, "-", 1);
        AddDecisionQueue("SHUFFLEDECK", $otherPlayer, "-", 1);  
      }
      return $lastResult;
    case "TRAPDOOR":
      $deck = &GetDeck($player);
      $index = explode("-", $lastResult)[1];
      BanishCardForPlayer($deck[$index], $player, "DECK", "TRAPDOOR");
      RemoveDeck($player, $index);
      WriteLog("Player {$player} banishes a card face down");
      return $lastResult;
    case "HUNTSMANMARK":
      if ($lastResult != "PASS") {
        $otherPlayer = $player == 1 ? 2 : 1;
        // the parameter should only be -1 if passed by flick knives
        // in which case we don't need to destroy the dagger
        $index = $parameter == -1 ? -1 : SearchCharacterForUniqueID($parameter, $player);
        if ($index != -1) DestroyCharacter($player, $index);
        MarkHero($otherPlayer);
      }
      return $lastResult;
    case "IFTYPEREVEALED":
      $cards = explode(",", $lastResult);
      foreach ($cards as $cardID) {
        if (CardType($cardID) == $parameter) {
          return $cardID;
        }
      }
      return "PASS";
    case "MARKHERO":
      MarkHero($player);
      return $lastResult;
    case "CHAINREACTION":
      AddCurrentTurnEffect("chain_reaction_yellow-" . $lastResult, $player);
      return $lastResult;
    case "NULLTIMEZONE":
      $params = explode(",", $parameter);
      $items = &GetItems($player);
      $items[$params[0]+8] = $params[1];
      return $lastResult;
    default:
      return "NOTSTATIC";
  }
}
