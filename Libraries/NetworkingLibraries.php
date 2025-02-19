<?php
function ProcessInput($playerID, $mode, $buttonInput, $cardID, $chkCount, $chkInput, $isSimulation = false, $inputText = "")
{
  global $gameName, $currentPlayer, $mainPlayer, $turn, $CS_CharacterIndex, $CS_PlayIndex, $decisionQueue, $CS_NextNAAInstant, $skipWriteGamestate, $combatChain, $landmarks;
  global $SET_PassDRStep, $actionPoints, $currentPlayerActivity, $redirectPath, $CS_PlayedAsInstant;
  global $dqState, $layers, $CS_ArsenalFacing, $CCS_HasAimCounter, $combatChainState;
  global $roguelikeGameID, $CS_SkipAllRunechants;
  $otherPlayer = ($playerID == 1 ? 2 : 1);
  switch ($mode) {
    case 0:
      break; //Deprecated
    case 1:
      break; //Deprecated
    case 2: //Play card from hand - DEPRECATED
      break;
    case 3: //Play equipment/hero ability
      $index = $cardID;
      $found = -1;
      $character = &GetPlayerCharacter($playerID);
      $cardID = $character[$index];
      if ($index != -1 && IsPlayable($character[$index], $turn[0], "CHAR", $index)) {
        SetClassState($playerID, $CS_CharacterIndex, $index);
        SetClassState($playerID, $CS_PlayIndex, $index);
        $character = &GetPlayerCharacter($playerID);
        if ($turn[0] == "B") $character[$index + 6] = 1;
        elseif ($turn[0] == "D" && canBeAddedToChainDuringDR($cardID)) {
          $character[$index + 1] = 1;
          $character[$index + 6] = 1;
        }
        else EquipPayAdditionalCosts($index, "EQUIP");
        PlayCard($cardID, "EQUIP", -1, $index, $character[$index + 11]);
      } else {
        echo("Play equipment ability " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 4: //Add something to your arsenal
      $found = HasCard($cardID);
      if ($turn[0] == "ARS" && $found >= 0) {
        $hand = &GetHand($playerID);
        unset($hand[$found]);
        $hand = array_values($hand);
        AddArsenal($cardID, $currentPlayer, "HAND", "DOWN");
        PassTurn();
      } else {
        echo($cardID . " " . $turn[0] . "<BR>");
        echo("Add to arsenal " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 5: //Card Played from Arsenal
      $index = $cardID;
      $arsenal = &GetArsenal($playerID);
      if ($index < count($arsenal)) {
        $cardToPlay = $arsenal[$index];
        if (!IsPlayable($cardToPlay, $turn[0], "ARS", $index)) break;
        $uniqueID = $arsenal[$index + 5];
        SetClassState($playerID, $CS_ArsenalFacing, $arsenal[$index + 1]);
        if ($arsenal[$index + 3] > 0 && CardSubType($cardToPlay) == "Arrow") $combatChainState[$CCS_HasAimCounter] = 1;
        if(!IsStaticType(CardType($arsenal[$index], "ARS"), "ARS", $arsenal[$index])) RemoveArsenal($playerID, $index);
        PlayCard($cardToPlay, "ARS", -1, -1, $uniqueID);
      } else {
        echo("Play from arsenal " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 6: //Pitch Deck
      if ($turn[0] != "PDECK") break;
      $found = PitchHasCard($cardID);
      if ($found >= 0) {
        PitchDeck($currentPlayer, $found);
        PassTurn(); //Resume passing the turn
      } else {
        echo("Pitch deck " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 7: //Number input
      if ($turn[0] == "DYNPITCH" || $turn[0] == "CHOOSENUMBER") {
        ContinueDecisionQueue($buttonInput);
      } else {
        echo("Number input " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 8:
    case 9: //OPT, CHOOSETOP, CHOOSEBOTTOM
      if ($turn[0] == "CHOOSETOP" || $turn[0] == "CHOOSEBOTTOM") {
        $options = explode(",", $turn[2]);
        $found = -1;
        for ($i = 0; $i < count($options); ++$i) {
          if ($options[$i] == $buttonInput) {
            $found = $i;
            break;
          }
        }
        if ($found == -1) break; //Invalid input
        $deck = new Deck($playerID);
        if ($mode == 8) {
          $deck->AddTop($buttonInput);
          WriteLog("Player " . $playerID . " put a card on top of the deck");
        } else if ($mode == 9) {
          $deck->AddBottom($buttonInput);
          WriteLog("Player " . $playerID . " put a card on the bottom of the deck");
        }
        unset($options[$found]);
        $options = array_values($options);
        if (count($options) > 0) PrependDecisionQueue($turn[0], $currentPlayer, implode(",", $options));
        ContinueDecisionQueue($buttonInput);
      } else {
        echo("Opt " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 10: //Item ability
      $index = $cardID; //Overridden to be index instead
      $items = &GetItems($playerID);
      if ($index >= count($items)) break; //Item doesn't exist
      $cardID = $items[$index];
      if (!IsPlayable($cardID, $turn[0], "PLAY", $index)) break; //Item not playable
      --$items[$index + 3];
      SetClassState($playerID, $CS_PlayIndex, $index);
      $set = CardSet($cardID);
      PlayCard($cardID, "PLAY", -1, $index, $items[$index + 4]);
      break;
    case 11: //CHOOSEDECK
      if ($turn[0] == "CHOOSEDECK" || $turn[0] == "MAYCHOOSEDECK" || $turn[0] == "CHOOSETHEIRDECK") {
        if ($turn[0] == "CHOOSETHEIRDECK") $player = $playerID == 1 ? 2 : 1;
        else $player = $playerID;
        $deck = new Deck($player);
        $index = $cardID;
        $cardID = $deck->Remove($index);
        ContinueDecisionQueue($cardID);
      }
      break;
    case 12: //HANDTOP
      if ($turn[0] == "HANDTOPBOTTOM") {
        $hand = &GetHand($playerID);
        $deck = new Deck($playerID);
        $deck->AddTop($buttonInput);
        $index = SearchHandForCard($playerID, $buttonInput);
        unset($hand[$index]);
        $hand = array_values($hand);
        ContinueDecisionQueue($buttonInput);
        WriteLog("Player " . $playerID . " put a card on the top of the deck.");
      }
      break;
    case 13: //HANDBOTTOM
      if ($turn[0] == "HANDTOPBOTTOM") {
        $hand = &GetHand($playerID);
        $deck = new Deck($playerID);
        $deck->AddBottom($buttonInput);
        $index = SearchHandForCard($playerID, $buttonInput);
        unset($hand[$index]);
        $hand = array_values($hand);
        ContinueDecisionQueue($buttonInput);
        WriteLog("Player " . $playerID . " put a card on the bottom of the deck.");
      }
      break;
    case 14: //Banish
      $index = $cardID;
      $banish = &GetBanish($playerID);
      $theirChar = &GetPlayerCharacter($playerID == 1 ? 2 : 1);
      $otherPlayer = $playerID == 1 ? 2 : 1;
      if ($index < 0 || $index >= count($banish)) {
        echo("Banish Index " . $index . " Invalid Input<BR>");
        return false;
      }
      $cardID = $banish[$index];
      if (!IsPlayable($cardID, $turn[0], "BANISH", $index)) break;
      if ($banish[$index + 1] == "INST") SetClassState($currentPlayer, $CS_NextNAAInstant, 1);
      if ($banish[$index + 1] == "MON212" && TalentContains($theirChar[0], "LIGHT", $currentPlayer)) AddCurrentTurnEffect("MON212", $currentPlayer);
      SetClassState($currentPlayer, $CS_PlayIndex, $index);
      if (CanPlayAsInstant($cardID, $index, "BANISH")) SetClassState($currentPlayer, $CS_PlayedAsInstant, "1");
      if (!PlayableFromBanish($cardID, $banish[$index + 1], true)) SearchCurrentTurnEffects("DTD564", $currentPlayer, true);
      if ($banish[$index + 1] == "MST236") {
        SearchCurrentTurnEffects("MST236-3", $currentPlayer, true);
        $currentPlayerBanish = new Banish($currentPlayer);
        $otherPlayerBanish = new Banish($otherPlayer);
        $currentPlayerBanish->UnsetModifier("MST236");
        $otherPlayerBanish->UnsetModifier("MST236");
      }
      if($banish[$index + 1] == "ELE064") AddCurrentTurnEffect("ELE064", $currentPlayer, uniqueID:$cardID);
      PlayCard($cardID, "BANISH", -1, $index, $banish[$index + 2]);
      break;
    case 15: // Their Banish
      $index = $cardID;
      $otherPlayer = ($playerID == 1 ? 2 : 1);
      $theirBanish = &GetBanish($otherPlayer);
      $theirChar = &GetPlayerCharacter($otherPlayer);
      if ($index < 0 || $index >= count($theirBanish)) {
        echo("Banish Index " . $index . " Invalid Input<BR>");
        return false;
      }
      $cardID = $theirBanish[$index];
      if (!IsPlayable($cardID, $turn[0], "THEIRBANISH", $index)) break;
      SetClassState($currentPlayer, $CS_PlayIndex, $index);
      PlayCard($cardID, "THEIRBANISH", -1, $index, $theirBanish[$index + 2]);
      break;
    case 16:
    case 18: //Decision Queue (15 and 18 deprecated)
      if (count($decisionQueue) > 0) {
        $index = $cardID;
        $isValid = false;
        $validInputs = explode(",", $turn[2]);
        for ($i = 0; $i < count($validInputs); ++$i) {
          if ($validInputs[$i] == $index) $isValid = true;
        }
        if ($isValid) ContinueDecisionQueue($index);
      }
      break;
    case 17: //BUTTONINPUT
      if (($turn[0] == "BUTTONINPUT" || $turn[0] == "CHOOSEARCANE" || $turn[0] == "BUTTONINPUTNOPASS" || $turn[0] == "CHOOSEFIRSTPLAYER")) {
        ContinueDecisionQueue($buttonInput);
      }
      break;
    case 19: //MULTICHOOSE X - multi choice CHOOSEMULTIZONE
      if ($turn[0] == "CHOOSEMULTIZONE" || $turn[0] == "MAYCHOOSEMULTIZONE") {
        $options = explode(",", $turn[2]);
        $limit1 = explode("-", $options[0]);
        $limit2 = explode("-", $options[1]);

        // either or both limit options exists this is just a safe-guard way to handle it
        $maxSelect = 0;
        $minSelect = 0;
        $limitOffset = 0;
        switch ($limit1[0]) {
          case "MAXCOUNT":
            $maxSelect = intval($limit1[1]);
            $limitOffset++;
            break;
          case "MINCOUNT":
            $minSelect = intval($limit1[1]);
            $limitOffset++;
            break;
          default:
            break;
        }
        switch ($limit2[0]) {
          case "MAXCOUNT":
            $maxSelect = intval($limit2[1]);
            $limitOffset++;
            break;
          case "MINCOUNT":
            $minSelect = intval($limit2[1]);
            $limitOffset++;
            break;
          default:
            break;
        }

        $selectionCount = count($chkInput);
        if ($maxSelect < $selectionCount) { // we won't revert the gamestate as may the opponent is requested to choose (EVO143)
          WriteLog("Player " . $playerID . " selected " . $selectionCount . " items, but a maximum of " . $maxSelect . " is allowed.", highlight: true);
          $skipWriteGamestate = true;
          break;
        } else if ($selectionCount < $minSelect) {
          WriteLog("Player " . $playerID . " selected " . $selectionCount . " items, but a minimum of " . $maxSelect . " is requested.", highlight: true);
          $skipWriteGamestate = true;
          break;
        }

        $input = "";
        for ($i = 0; $i < count($chkInput); ++$i) {
          $index = intval($chkInput[$i]);
          if ($index < 0 || $index >= count($options)) {
            WriteLog($selectionCount);
            WriteLog("An unvalid option was selected. Please try selecting the items again, if you feel experienced a bug please report it.", highlight: true);
            $skipWriteGamestate = true;
            break;
          }
          if ($input != "") $input .= ",";
          $input .= $options[$index + $limitOffset];
        }
        if (!$skipWriteGamestate) {
          ContinueDecisionQueue($input);
        }
        break;
      } else if (substr($turn[0], 0, 11) != "MULTICHOOSE" && substr($turn[0], 0, 14) != "MAYMULTICHOOSE") break;
      $params = explode("-", $turn[2]);
      $maxSelect = intval($params[0]);
      $options = explode(",", $params[1]);
      if (count($params) > 2) $minSelect = intval($params[2]);
      else $minSelect = -1;
      if (count($chkInput) > $maxSelect) {
        WriteLog("You selected " . count($chkInput) . " items, but a maximum of " . $maxSelect . " is allowed. Reverting gamestate prior to that effect.", highlight: true);
        RevertGamestate();
        $skipWriteGamestate = true;
        break;
      }
      if ($minSelect != -1 && count($chkInput) < $minSelect && count($chkInput) < count($options)) {
        WriteLog("You selected " . count($chkInput) . " items, but a minimum of " . $minSelect . " is requested. Reverting gamestate prior to that effect.", highlight: true);
        RevertGamestate();
        $skipWriteGamestate = true;
        break;
      }
      $input = [];
      for ($i = 0; $i < count($chkInput); ++$i) {
        if ($chkInput[$i] < 0 || $chkInput[$i] >= count($options)) {
          WriteLog("You selected option " . $chkInput[$i] . " but that was not one of the original options. Reverting gamestate prior to that effect.", highlight: true);
          RevertGamestate();
          $skipWriteGamestate = true;
          break;
        } else {
          array_push($input, $options[$chkInput[$i]]);
        }
      }
      if (!$skipWriteGamestate) {
        ContinueDecisionQueue($input);
      }
      break;
    case 20: //YESNO
      if (($turn[0] == "YESNO" || $turn[0] == "DOCRANK") && ($buttonInput == "YES" || $buttonInput == "NO")) ContinueDecisionQueue($buttonInput);
      break;
    case 21: //Combat chain ability
      $index = $cardID; //Overridden to be index instead
      $cardID = $combatChain[$index];
      if (AbilityPlayableFromCombatChain($cardID) && IsPlayable($cardID, $turn[0], "PLAY", $index)) {
        SetClassState($playerID, $CS_PlayIndex, $index);
        PlayCard($cardID, "PLAY", -1, -1, $combatChain[$index + 7]);
      }
      break;
    case 22: //Aura ability
      $index = $cardID; //Overridden to be index instead
      $auras = &GetAuras($playerID);
      if ($index >= count($auras)) break; //Item doesn't exist
      $cardID = $auras[$index];
      if (!IsPlayable($cardID, $turn[0], "PLAY", $index)) break; //Aura ability not playable
      $names = GetAbilityNames($cardID, $index);
      if ($names == "") $auras[$index + 1] = 1; //Set status to used - for now
      SetClassState($playerID, $CS_PlayIndex, $index);
      PlayCard($cardID, "PLAY", -1, $index, $auras[$index + 6]);
      break;
    case 23: //CHOOSECARD
      if ($turn[0] == "CHOOSECARD" || $turn[0] == "MAYCHOOSECARD") {
        $options = explode(",", $turn[2]);
        $found = -1;
        for ($i = 0; $i < count($options); ++$i) {
          if ($options[$i] == $buttonInput) {
            $found = $i;
            break;
          }
        }
        if ($found == -1) break; //Invalid input
        unset($options[$found]);
        $options = array_values($options);
        ContinueDecisionQueue($buttonInput);
      }
      break;
    case 24: //Ally Ability
      $allies = &GetAllies($currentPlayer);
      $index = $cardID; //Overridden to be index instead
      if ($index >= count($allies)) break; //Ally doesn't exist
      $cardID = $allies[$index];
      if (!IsPlayable($cardID, $turn[0], "PLAY", $index)) break; //Ally not playable
      $allies[$index + 1] = 1;
      SetClassState($playerID, $CS_PlayIndex, $index);
      PlayCard($cardID, "PLAY", -1, $index, $allies[$index + 5]);
      break;
    case 25: //Landmark Ability
      $index = $cardID;
      if ($index >= count($landmarks)) break; //Landmark doesn't exist
      $cardID = $landmarks[$index];
      if (!IsPlayable($cardID, $turn[0], "PLAY", $index)) break; //Landmark not playable
      SetClassState($playerID, $CS_PlayIndex, $index);
      PlayCard($cardID, "PLAY", -1);
      break;
    case 26: //Change setting
      $userID = "";
      if (!$isSimulation) {
        include "MenuFiles/ParseGamefile.php";
        include_once "./includes/dbh.inc.php";
        include_once "./includes/functions.inc.php";
        if ($playerID == 1) $userID = $p1id;
        else $userID = $p2id;
      }
      $params = explode("-", $buttonInput);
      ChangeSetting($playerID, $params[0], $params[1], $userID);
      break;
    case 27: //Play card from hand by index
      $found = $cardID;
      if ($found >= 0) {
        //Player actually has the card, now do the effect
        //First remove it from their hand
        $hand = &GetHand($playerID);
        if ($found >= count($hand)) break;
        $cardID = $hand[$found];
        if (!IsPlayable($cardID, $turn[0], "HAND", $found)) break;
        unset($hand[$found]);
        $hand = array_values($hand);
        PlayCard($cardID, "HAND");
      }
      break;
    case 28:
      break; //Deprecated
    case 29: //CHOOSETOPOPPONENT
      if ($turn[0] == "CHOOSETOPOPPONENT") {
        $otherPlayer = ($playerID == 1 ? 2 : 1);
        $options = explode(",", $turn[2]);
        $found = -1;
        for ($i = 0; $i < count($options); ++$i) {
          if ($options[$i] == $buttonInput) {
            $found = $i;
            break;
          }
        }
        if ($found == -1) break; //Invalid input
        $deck = new Deck($otherPlayer);
        $deck->AddTop($buttonInput);
        unset($options[$found]);
        $options = array_values($options);
        if (count($options) > 0) {
          PrependDecisionQueue($turn[0], $currentPlayer, implode(",", $options));
        }
        ContinueDecisionQueue($buttonInput);
      } else {
        echo("Choose top opponent " . $turn[0] . " Invalid Input<BR>");
        return false;
      }
      break;
    case 30: //String input
      $cardName = CardName(strtoupper($inputText));
      if ($cardName != "") $inputText = $cardName;
      if ($turn[2] == "OUT052" && $inputText == "Head Leads the Tail") //Validate the name
      {
        WriteLog("Must name another card");
        break;
      }
      ContinueDecisionQueue(GamestateSanitize($inputText));
      break;
    case 31: //Move layer deeper
      $index = $buttonInput;
      if ($index >= $dqState[8]) break;
      $layer = [];
      for ($i = $index; $i < $index + LayerPieces(); ++$i) array_push($layer, $layers[$i]);
      $counter = 0;
      for ($i = $index + LayerPieces(); $i < ($index + LayerPieces() * 2); ++$i) {
        $layers[$i - LayerPieces()] = $layers[$i];
        $layers[$i] = $layer[$counter++];
      }
      break;
    case 32: //Move layer up
      $index = $buttonInput;
      if ($index == 0) break;
      $layer = [];
      for ($i = $index; $i < $index + LayerPieces(); ++$i) array_push($layer, $layers[$i]);
      $counter = 0;
      for ($i = $index - LayerPieces(); $i < $index; ++$i) {
        $layers[$i + LayerPieces()] = $layers[$i];
        $layers[$i] = $layer[$counter++];
      }
      break;
    case 33: //Fully re-order layers
      break;
    case 34: //Permanent ability
      $index = $cardID; //Overridden to be index instead
      $permanents = &GetPermanents($playerID);
      if ($index >= count($permanents)) break; //Permanent doesn't exist
      $cardID = $permanents[$index];
      if (!IsPlayable($cardID, $turn[0], "PLAY", $index)) break; //Permanent not playable
      SetClassState($playerID, $CS_PlayIndex, $index);
      PlayCard($cardID, "PLAY", -1, $index);
      break;
    case 35: //Play card from deck
      $index = $cardID; //Overridden to be index instead
      $deck = &GetDeck($playerID);
      if ($index >= count($deck)) break;
      $cardID = $deck[$index];
      if (!IsPlayable($cardID, $turn[0], "DECK", $index)) break;
      unset($deck[$index]);
      $deck = array_values($deck);
      PlayCard($cardID, "DECK");
      break;
    case 36: //Play card from graveyard
      $index = $cardID;
      $discard = &GetDiscard($playerID);
      $theirChar = &GetPlayerCharacter($playerID == 1 ? 2 : 1);
      if ($index < 0 || $index >= count($discard)) {
        echo("Graveyard Index " . $index . " Invalid Input<BR>");
        return false;
      }
      $cardID = $discard[$index];
      if (!IsPlayable($cardID, $turn[0], "GY", $index)) break;
      if ($discard[$index + 1] == "INST") SetClassState($currentPlayer, $CS_NextNAAInstant, 1);
      SetClassState($currentPlayer, $CS_PlayIndex, $index);
      if (CanPlayAsInstant($cardID, $index, "GY")) SetClassState($currentPlayer, $CS_PlayedAsInstant, "1");
      PlayCard($cardID, "GY", -1, $index, isset($discard[$index + 2]) ? $discard[$index + 2] : -1);
      break;
    case 99: //Pass
      if (CanPassPhase($turn[0])) {
        PassInput(false);
      }
      break;
    case 100: //Break Chain
      if ($currentPlayer == $mainPlayer && count($combatChain) == 0) {
        ResetCombatChainState();
        ProcessDecisionQueue();
      }
      break;
    case 101: //Pass block and Reactions
      ChangeSetting($playerID, $SET_PassDRStep, 1);
      if (CanPassPhase($turn[0])) {
        PassInput(false);
      }
      break;
    case 102: //Toggle equipment Active
      $index = $buttonInput;
      $char = &GetPlayerCharacter($playerID);
      $char[$index + 9] = ($char[$index + 9] == "1" ? "0" : "1");
      break;
    case 103: //Toggle my permanent Active
      $input = explode("-", $buttonInput);
      $index = $input[1];
      switch ($input[0]) {
        case "AURAS":
          $zone = &GetAuras($playerID);
          $offset = 7;
          break;
        case "ITEMS":
          $zone = &GetItems($playerID);
          $offset = 5;
          break;
        case "HERO":
          $zone = &GetPlayerCharacter($playerID);
          $offset = 9;
          break;
        default:
          $zone = &GetAuras($playerID);
          $offset = 7;
          break;
      }
      if (($index + $offset) > count($zone)) break;
      $zone[$index + $offset] = ($zone[$index + $offset] == "1" ? "0" : "1");
      break;
    case 104: //Toggle other player permanent Active
      $input = explode("-", $buttonInput);
      $index = $input[1];
      switch ($input[0]) {
        case "AURAS":
          $zone = &GetAuras($playerID == 1 ? 2 : 1);
          $offset = 8;
          break;
        case "ITEMS":
          $zone = &GetItems($playerID == 1 ? 2 : 1);
          $offset = 6;
          break;
        default:
          $zone = &GetAuras($playerID == 1 ? 2 : 1);
          $offset = 8;
          break;
      }
      if (($index + $offset) > count($zone)) break;
      $zone[$index + $offset] = ($zone[$index + $offset] == "1" ? "0" : "1");
      break;
    case 105: //Skip all runechants
      SetClassState($playerID, $CS_SkipAllRunechants, 1);
      break;
    case 10000: //Undo
      $format = GetCachePiece($gameName, 13);
      $char = &GetPlayerCharacter($otherPlayer);
      if (($format != 1 && $format != 3) || $char[0] == "DUMMY" || $turn[0] == "P" || AlwaysAllowUndo($otherPlayer)) {
        RevertGamestate();
        $skipWriteGamestate = true;
        WriteLog("Player " . $playerID . " undid their last action");
      } else {
        //It's competitive queue, so we must request confirmation
        WriteLog("Player " . $playerID . " requests to undo the last action");
        AddEvent("REQUESTUNDO", $playerID);
      }
      break;
    case 10001:
      RevertGamestate("preBlockBackup.txt");
      $skipWriteGamestate = true;
      WriteLog("Player " . $playerID . " canceled their blocks");
      break;
    case 10002:
      WriteLog("Player " . $playerID . " manually added 1 action point", highlight: true);
      ++$actionPoints;
      break;
    case 10003: //Undo/Revert to prior turn
      $format = GetCachePiece($gameName, 13);
      $char = &GetPlayerCharacter($otherPlayer);
      if (($format != 1 && $format != 3) || $char[0] == "DUMMY" || $turn[0] == "P" || AlwaysAllowUndo($otherPlayer)) {
        RevertGamestate($buttonInput);
        WriteLog("Player " . $playerID . " reverted back to a prior turn");
      } else {
        //It's competitive queue, so we must request confirmation
        WriteLog("Player " . $playerID . " requests to undo the last action");
        if ($buttonInput == "beginTurnGamestate.txt") AddEvent("REQUESTTHISTURNUNDO", $playerID);
        else if ($buttonInput == "lastTurnGamestate.txt") AddEvent("REQUESTLASTTURNUNDO", $playerID);
      }
      break;
    case 10004:
      if ($actionPoints > 0) {
        WriteLog("Player " . $playerID . " manually subtracted 1 action point.", highlight: true);
        --$actionPoints;
      }
      break;
    case 10005:
      WriteLog("Player " . $playerID . " manually subtracted 1 life from themself", highlight: true);
      LoseHealth(1, $playerID);
      break;
    case 10006:
      WriteLog("Player " . $playerID . " manually added 1 life to themself", highlight: true);
      $health = &GetHealth($playerID);
      $health += 1;
      break;
    case 10007:
      //WriteLog("Player " . $playerID ." manually added 1 life point to themselves.", highlight: true);
      WriteLog("Subtracting life from your opponent is not allowed");
      //LoseHealth(1, ($playerID == 1 ? 2 : 1));
      break;
    case 10008:
      WriteLog("Player " . $playerID . " manually added 1 life to their opponent", highlight: true);
      $health = &GetHealth($playerID == 1 ? 2 : 1);
      $health += 1;
      break;
    case 10009:
      WriteLog("Player " . $playerID . " manually drew a card for themself", highlight: true);
      Draw($playerID, false);
      break;
    case 10010:
      WriteLog("Player " . $playerID . " manually drew a card for their opponent", highlight: true);
      Draw(($playerID == 1 ? 2 : 1), false);
      break;
    case 10011:
      WriteLog("Player " . $playerID . " manually added a card to their hand", highlight: true);
      $hand = &GetHand($playerID);
      array_push($hand, $cardID);
      break;
    case 10012:
      WriteLog("Player " . $playerID . " manually added a resource to their pool", highlight: true);
      $resources = &GetResources($playerID);
      $resources[0] += 1;
      break;
    case 10013:
      WriteLog("Player " . $playerID . " manually added a resource to their opponent's pool", highlight: true);
      $resources = &GetResources($playerID == 1 ? 2 : 1);
      $resources[0] += 1;
      break;
    case 10014:
      WriteLog("Player " . $playerID . " manually removed a resource from their opponent's pool", highlight: true);
      $resources = &GetResources($playerID == 1 ? 2 : 1);
      $resources[0] -= 1;
      break;
    case 10015:
      WriteLog("Player " . $playerID . " manually removed a resource from their pool", highlight: true);
      $resources = &GetResources($playerID);
      $resources[0] -= 1;
      break;
    case 10016:
      WriteLog("Player " . $playerID . " manually removed their arsenal", highlight: true);
      $cardID = RemoveArsenal($playerID, 0);
      AddGraveyard($cardID, $playerID, "ARS");
      break;
    case 100000: //Quick Rematch
      if ($isSimulation) return;
      if ($turn[0] != "OVER") break;
      $otherPlayer = ($playerID == 1 ? 2 : 1);
      $char = &GetPlayerCharacter($otherPlayer);
      if ($char[0] != "DUMMY") {
        AddDecisionQueue("YESNO", $otherPlayer, "if you want a <b>Quick Rematch</b>?");
        AddDecisionQueue("NOPASS", $otherPlayer, "-", 1);
        AddDecisionQueue("QUICKREMATCH", $otherPlayer, "-", 1);
        AddDecisionQueue("OVER", $playerID, "-");
      } else {
        AddDecisionQueue("QUICKREMATCH", $otherPlayer, "-", 1);
      }
      ProcessDecisionQueue();
      break;
    case 100001: //Main Menu
      if ($isSimulation) return;
      header("Location: " . $redirectPath . "/MainMenu.php");
      exit;
    case 100002: //Concede
      if ($isSimulation) return;
      include_once "./includes/dbh.inc.php";
      include_once "./includes/functions.inc.php";
      $conceded = true;
      if (!IsGameOver()) PlayerLoseHealth($playerID, GetHealth($playerID));
      break;
    case 100003: //Report Bug
      if ($isSimulation) return;
      ReportBug();
      break;
    case 100004: //Full Rematch
      if ($isSimulation) return;
      if ($turn[0] != "OVER") break;
      $otherPlayer = ($playerID == 1 ? 2 : 1);
      AddDecisionQueue("YESNO", $otherPlayer, "if you want a <b>Rematch</b>?");
      AddDecisionQueue("REMATCH", $otherPlayer, "-", 1);
      ProcessDecisionQueue();
      break;
    case 100007: //Claim Victory when opponent is inactive
      if ($isSimulation) return;
      if ($currentPlayerActivity == 2) {
        include_once "./includes/dbh.inc.php";
        include_once "./includes/functions.inc.php";
        $otherPlayer = ($playerID == 1 ? 2 : 1);
        if (!IsGameOver()) PlayerLoseHealth($otherPlayer, GetHealth($otherPlayer));
        WriteLog("🚩The opponent forfeit due to inactivity.");
      }
      break;
    case 100010: //Grant badge
      if ($isSimulation) return;
      include "MenuFiles/ParseGamefile.php";
      include_once "./includes/dbh.inc.php";
      include_once "./includes/functions.inc.php";
      $myName = ($playerID == 1 ? $p1uid : $p2uid);
      $theirName = ($playerID == 1 ? $p2uid : $p1uid);
      if ($playerID == 1) $userID = $p1id;
      else $userID = $p2id;
      if ($userID != "") {
        AwardBadge($userID, 3);
        WriteLog($myName . " gave a badge to " . $theirName);
      }
      break;
    case 100011: //Resume adventure (roguelike)
      if ($roguelikeGameID == "") break;
      header("Location: " . $redirectPath . "/Roguelike/ContinueAdventure.php?gameName=" . $roguelikeGameID . "&playerID=1&health=" . GetHealth(1));
      break;
    case 100012: //Create Replay
      if (!file_exists("./Games/" . $gameName . "/origGamestate.txt")) {
        WriteLog("Failed to create replay; original gamestate file failed to create.");
        return true;
      }
      include "MenuFiles/ParseGamefile.php";
      WriteLog("Player " . $playerID . " saved this game as a replay.");
      $pid = ($playerID == 1 ? $p1id : $p2id);
      $path = "./Replays/" . $pid . "/";
      if (!file_exists($path)) {
        mkdir($path, 0777, true);
      }
      if (!file_exists($path . "counter.txt")) $counter = 1;
      else {
        $counterFile = fopen($path . "counter.txt", "r");
        $counter = fgets($counterFile);
        fclose($counterFile);
      }
      mkdir($path . $counter . "/", 0777, true);
      copy("./Games/" . $gameName . "/origGamestate.txt", "./Replays/" . $pid . "/" . $counter . "/origGamestate.txt");
      copy("./Games/" . $gameName . "/commandfile.txt", "./Replays/" . $pid . "/" . $counter . "/replayCommands.txt");
      $counterFile = fopen($path . "counter.txt", "w");
      fwrite($counterFile, $counter + 1);
      fclose($counterFile);
      break;
    case 100013: //Enable Spectate
      SetCachePiece($gameName, 9, "1");
      break;
    case 100014: //Report Player
      if ($isSimulation) return;
      $reportCount = 0;
      $folderName = "./BugReports/" . $gameName . "-" . $reportCount;
      while ($reportCount < 5 && file_exists($folderName)) {
        ++$reportCount;
        $folderName = "./BugReports/" . $gameName . "-" . $reportCount;
      }
      if ($reportCount == 3) {
        WriteLog("⚠️Report file is full for this game. Please use discord for further reports.", highlight: true);
      }
      mkdir($folderName, 0700, true);
      copy("./Games/$gameName/gamestate.txt", $folderName . "/gamestate.txt");
      copy("./Games/$gameName/gamestateBackup.txt", $folderName . "/gamestateBackup.txt");
      copy("./Games/$gameName/gamelog.txt", $folderName . "/gamelog.txt");
      copy("./Games/$gameName/beginTurnGamestate.txt", $folderName . "/beginTurnGamestate.txt");
      copy("./Games/$gameName/lastTurnGamestate.txt", $folderName . "/lastTurnGamestate.txt");
      WriteLog("🚨Thank you for reporting a player. The chat log has been saved on the server. Please report it to a mod on Discord with the game number for reference ($gameName).", highlight: true);
      break;
    case 100015: // request to enable chat
      include "MenuFiles/ParseGamefile.php";
      $myName = ($playerID == 1 ? $p1uid : $p2uid);
      if ($playerID == 1) SetCachePiece($gameName, 15, 1);
      else if ($playerID == 2) SetCachePiece($gameName, 16, 1);
      if (GetCachePiece($gameName, 15) != 1 || GetCachePiece($gameName, 16) != 1) {
        if (!str_contains($myName, "Omegaeclipse") && !str_contains($myName, "OmegaEclipse")) {
          AddEvent("REQUESTCHAT", $playerID);
        }
        $theirChar = &GetPlayerCharacter($playerID == 1 ? 2 : 1);
        if ($theirChar[0] == "DUMMY") WriteLog("The dummy beeps at you");
        else WriteLog($myName . " wants to enable chat");
      }
      break;
    case 100016://Confirm Undo
      RevertGamestate();
      $skipWriteGamestate = true;
      WriteLog("Player " . $playerID . " allowed undoing the last action");
      break;
    case 100017://Decline Undo
      WriteLog("Player " . $playerID . " declined the undo request");
      break;
    case 100018://Confirm this turn undo
      RevertGamestate("beginTurnGamestate.txt");
      WriteLog("Player " . $playerID . " reverted to the start of the turn");
      break;
    case 100019://Confirm last turn undo
      RevertGamestate("lastTurnGamestate.txt");
      WriteLog("Player " . $playerID . " reverted to the last turn");
      break;
    default:
      break;
  }
  return true;
}

function IsModeAsync($mode)
{
  switch ($mode) {
    case 26:
      return true;
    case 102:
      return true;
    case 103:
      return true;
    case 104:
      return true;
    case 10000:
      return true;
    case 10003:
      return true;
    case 100000:
      return true;
    case 100001:
      return true;
    case 100002:
      return true;
    case 100003:
      return true;
    case 100004:
      return true;
    case 100007:
      return true;
    case 100010:
      return true;
    case 100012:
      return true;
    case 100015:
      return true;
    case 100016://Confirm Undo
    case 100017://Decline Undo
    case 100018://Confirm This Turn Undo
    case 100019://Confirm Last Turn Undo
      return true;
  }
  return false;
}

function IsModeAllowedForSpectators($mode)
{
  switch ($mode) {
    case 100001:
      return true;
    default:
      return false;
  }
}

function PitchHasCard($cardID)
{
  global $currentPlayer;
  return SearchPitchForCard($currentPlayer, $cardID);
}

function HasCard($cardID)
{
  global $currentPlayer;
  $cardType = CardType($cardID);
  if ($cardType == "C" || $cardType == "E" || $cardType == "W") {
    $character = &GetPlayerCharacter($currentPlayer);
    for ($i = 0; $i < count($character); $i += CharacterPieces()) {
      if ($character[$i] == $cardID) return $i;
    }
  } else {
    $hand = &GetHand($currentPlayer);
    for ($i = 0; $i < count($hand); ++$i) {
      if ($hand[$i] == $cardID) return $i;
    }
  }
  return -1;
}

function PassInput($autopass = true)
{
  global $turn, $currentPlayer, $mainPlayer;
  if ($turn[0] == "B") {
    $uniqueID = SearchCurrentTurnEffects("EVO143", $mainPlayer, returnUniqueID: true);
    if ($uniqueID != -1) {
      $playerChar = &GetPlayerCharacter($currentPlayer);
      $charID = FindCharacterIndex($currentPlayer, $uniqueID);
      if ($playerChar[$charID + 6] != 1 && BlockValue($playerChar[$charID]) >= 0) {
        WriteLog("Player " . $currentPlayer . " must block with " . CardLink($uniqueID, $uniqueID) . " due to the effect of " . CardLink("EVO143", "EVO143") . ".");
        return;
      }
    }
  }
  if ($turn[0] == "ENDPHASE" || $turn[0] == "MAYMULTICHOOSETEXT" || $turn[0] == "MAYCHOOSECOMBATCHAIN" || $turn[0] == "MAYCHOOSEMULTIZONE" || $turn[0] == "MAYMULTICHOOSEHAND" || $turn[0] == "MAYCHOOSEHAND" || $turn[0] == "MAYCHOOSEDISCARD" || $turn[0] == "MAYCHOOSEARSENAL" || $turn[0] == "MAYCHOOSEPERMANENT" || $turn[0] == "MAYCHOOSEDECK" || $turn[0] == "MAYCHOOSEMYSOUL" || $turn[0] == "INSTANT" || $turn[0] == "OK" || $turn[0] == "MULTISHOWCARDSDECK" || $turn[0] == "MULTISHOWCARDSTHEIRDECK" || $turn[0] == "MAYCHOOSECARD") {
    ContinueDecisionQueue("PASS");
  } else {
    if ($autopass == true) WriteLog("Player " . $currentPlayer . " auto-passed");
    else WriteLog("Player " . $currentPlayer . " passed");
    if (Pass($turn, $currentPlayer)) {
      if ($turn[0] == "M") {
        BeginTurnPass();
      } else PassTurn();
    }
  }
}

function Pass(&$turn, &$currentPlayer)
{
  global $mainPlayer, $defPlayer;
  if ($turn[0] == "M" || $turn[0] == "ARS") {
    return 1;
  } else if ($turn[0] == "B") {
    AddLayer("DEFENDSTEP", $mainPlayer, "-");
    OnBlockResolveEffects();
    ProcessDecisionQueue();
  } else if ($turn[0] == "A") {
    if (count($turn) >= 3 && $turn[2] == "D") {
      return BeginChainLinkResolution();
    } else {
      $currentPlayer = $defPlayer;
      $turn[0] = "D";
      $turn[2] = "A";
    }
  } else if ($turn[0] == "D") {
    if (count($turn) >= 3 && $turn[2] == "A") {
      return BeginChainLinkResolution();
    } else {
      $currentPlayer = $mainPlayer;
      $turn[0] = "A";
      $turn[2] = "D";
    }
  }
  return 0;
}

function BeginChainLinkResolution()
{
  global $mainPlayer, $turn;
  $turn[0] = "M";
  ChainLinkBeginResolutionEffects();
  AddDecisionQueue("RESOLVECHAINLINK", $mainPlayer, "-");
  ProcessDecisionQueue();
}

function NuuStaticAbility($banishedBy)
{
  global $combatChain, $mainPlayer, $defPlayer, $CombatChain, $chainLinks;
  $prevLink = $chainLinks[count($chainLinks) - 1];
  if (count($prevLink) > 0) {
    for ($i = 0; $i < count($prevLink); $i += ChainLinksPieces()) {
      if ($defPlayer == $prevLink[$i+1]) {
        $originalID = GetCardIDBeforeTransform($prevLink[$i]);
        $cardType = CardType($prevLink[$i]);
        if ($cardType === "E" && CardType($originalID) === "A" && $prevLink[$i] !== "EVO410b" && $prevLink[$i] !== "DYN492b") {
          WriteLog(CardLink($prevLink[$i], $prevLink[$i]) . " was banished.");
          BanishCardForPlayer($originalID, $defPlayer, "CC", "Source-" . $banishedBy, $mainPlayer);
          $index = FindCharacterIndex($defPlayer, $prevLink[$i]);
          DestroyCharacter($defPlayer, $index, wasBanished: true);
        }
        if (DelimStringContains($cardType, "A") || $cardType === "AA") {
          WriteLog(CardLink($prevLink[$i], $prevLink[$i]) . " was banished.");
          BanishCardForPlayer($prevLink[$i], $defPlayer, "CC", "Source-" . $banishedBy, $banishedBy);
          $chainLinks[count($chainLinks) - 1][$i + 2] = 0;
        }
      }
    }
  }
}

function ChainLinkBeginResolutionEffects()
{
  global $combatChain, $mainPlayer, $defPlayer, $CCS_CombatDamageReplaced, $combatChainState, $CCS_WeaponIndex, $CID_BloodRotPox, $CS_Transcended;
  if (TypeContains($combatChain[0], "W", $mainPlayer)) {
    $mainCharacterEffects = &GetMainCharacterEffects($mainPlayer);
    $index = $combatChainState[$CCS_WeaponIndex];
    for ($i = 0; $i < count($mainCharacterEffects); $i += CharacterEffectPieces()) {
      if ($mainCharacterEffects[$i] == $index) {
        switch ($mainCharacterEffects[$i + 1]) {
          //CR 2.1 - 6.5.4. Standard-replacement: Third, each player applies any active standard-replacement effects they control
          //CR 2.1 - 6.5.5. Prevention: Fourth, each player applies any active prevention effects they control
          case "EVR054":
            $pendingDamage = CachedTotalAttack() - CachedTotalBlock();
            AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Currently $pendingDamage damage would be dealt. Do you want to destroy a defending equipment instead?");
            AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_destroy_a_blocking_equipment_instead_of_dealing_damage");
            AddDecisionQueue("NOPASS", $mainPlayer, "-");
            AddDecisionQueue("PASSPARAMETER", $mainPlayer, "1", 1);
            AddDecisionQueue("SETCOMBATCHAINSTATE", $mainPlayer, $CCS_CombatDamageReplaced, 1);
            AddDecisionQueue("FINDINDICES", $defPlayer, "SHATTER,$pendingDamage", 1);
            AddDecisionQueue("CHOOSETHEIRCHARACTER", $mainPlayer, "<-", 1);
            AddDecisionQueue("DESTROYCHARACTER", $defPlayer, "-", 1);
            break;
          case "HVY053":
            RemoveCurrentTurnEffect($i);
            break;
          default:
            break;
        }
      }
    }
  }
}

function ResolveChainLink()
{
  global $combatChain, $combatChainState, $mainPlayer, $defPlayer, $CCS_CombatDamageReplaced, $CCS_LinkTotalAttack;
  BuildMainPlayerGameState();

  $totalAttack = 0;
  $totalDefense = 0;
  EvaluateCombatChain($totalAttack, $totalDefense);

  $combatChainState[$CCS_LinkTotalAttack] = $totalAttack;

  LogCombatResolutionStats($totalAttack, $totalDefense);

  $target = explode("-", GetAttackTarget());
  if ($target[0] == "THEIRALLY") {
    $index = $target[1];
    $allies = &GetAllies($defPlayer);
    $totalAttack += CurrentEffectDamageModifiers($mainPlayer, $combatChain[0], "COMBAT");
    $totalAttack = AllyDamagePrevention($defPlayer, $index, $totalAttack, "COMBAT");
    if ($totalAttack < 0) $totalAttack = 0;
    if ($index < count($allies)) {
      $allies[$index + 2] = intval($allies[$index + 2]) - $totalAttack;
      if ($totalAttack > 0) AllyDamageTakenAbilities($defPlayer, $index);
      DamageDealtAbilities($mainPlayer, $totalAttack, "COMBAT", $combatChain[0]);
      if ($allies[$index + 2] <= 0) DestroyAlly($defPlayer, $index, false, true, $allies[$index + 5]);
    }
    AddDecisionQueue("RESOLVECOMBATDAMAGE", $mainPlayer, $totalAttack);
  } else {
    $damage = $combatChainState[$CCS_CombatDamageReplaced] === 1 ? 0 : $totalAttack - $totalDefense;
    DamageTrigger($defPlayer, $damage, "COMBAT", $combatChain[0]); //Include prevention
    AddDecisionQueue("RESOLVECOMBATDAMAGE", $mainPlayer, "-");
  }
  ProcessDecisionQueue();
}

function ResolveCombatDamage($damageDone)
{
  global $combatChain, $combatChainState, $currentPlayer, $mainPlayer, $currentTurnEffects;
  global $CCS_DamageDealt, $CCS_HitsWithWeapon, $EffectContext, $CS_HitsWithWeapon, $CS_DamageDealt, $CS_PowDamageDealt;
  global $CS_HitsWithSword, $CCS_CurrentAttackGainedGoAgain, $CCS_ChainLinkHitEffectsPrevented, $defPlayer;
  $wasHit = $damageDone > 0;
  PrependLayer("FINALIZECHAINLINK", $mainPlayer, "0");
  WriteLog("Combat resolved with " . ($wasHit ? "a hit for $damageDone damage" : "no hit"));
  if (DoesAttackHaveGoAgain()) $combatChainState[$CCS_CurrentAttackGainedGoAgain] = 1;
  if (!DelimStringContains(CardSubtype($combatChain[0]), "Ally")) {
    SetClassState($mainPlayer, $CS_DamageDealt, GetClassState($mainPlayer, $CS_DamageDealt) + $damageDone);
    if (!IsHeroAttackTarget()) SetClassState($mainPlayer, $CS_PowDamageDealt, GetClassState($mainPlayer, $CS_PowDamageDealt) + $damageDone);
  }
    if ($wasHit) {
    LogPlayCardStats($mainPlayer, $combatChain[0], "CC", "HIT");
    $combatChainState[$CCS_DamageDealt] = $damageDone;
    if (IsWeaponAttack()) {
      ++$combatChainState[$CCS_HitsWithWeapon];
      IncrementClassState($mainPlayer, $CS_HitsWithWeapon);
      if (SubtypeContains($combatChain[0], "Sword", $mainPlayer)) IncrementClassState($mainPlayer, $CS_HitsWithSword);
      if (SearchDynamicCurrentTurnEffectsIndex("HNT258-DMG", $defPlayer, lenght:10) != -1) {
        $index = SearchDynamicCurrentTurnEffectsIndex("HNT258-DMG", $defPlayer, lenght:10);
        $params = explode(",", $currentTurnEffects[$index]);
        $amount = $params[1];
        $uniqueID = $params[2];
        if($damageDone <= $amount && $uniqueID == $combatChain[8]) {
          DealDamageAsync($mainPlayer, $amount, "DAMAGE", "HNT258");
          RemoveCurrentTurnEffect($index);
        }
      }
    }
    if (!HitEffectsArePrevented($combatChain[0])) {
      for ($i = 0; $i < count($combatChain); $i += CombatChainPieces()) {
        if ($combatChain[$i + 1] == $mainPlayer) {
          $EffectContext = $combatChain[$i]; 
          AddOnHitTrigger($combatChain[$i], $combatChain[$i+8]);
          if ($damageDone >= 4) AddCrushEffectTrigger($combatChain[$i]);
          if (CachedTotalAttack() >= 13) AddTowerEffectTrigger($combatChain[$i]);
        }
      }
      if (IsHeroAttackTarget()) {
        $otherPlayer = ($mainPlayer == 1 ? 2 : 1);
        CheckHitContracts($mainPlayer, $otherPlayer);
      }
      for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
        if (IsCombatEffectActive($currentTurnEffects[$i])) {
          if ($currentTurnEffects[$i + 1] == $mainPlayer) {
            AddEffectHitTrigger($currentTurnEffects[$i]); // Effects that gives effect to the attack
          }
        }
      }
      $currentTurnEffects = array_values($currentTurnEffects);
      MainCharacterHitTrigger();
      MainCharacterHitEffects();
      ArsenalHitEffects();
      AuraHitEffects($combatChain[0]);
      ItemHitTrigger($combatChain[0]);
      AttackDamageAbilities(GetClassState($mainPlayer, $CS_DamageDealt));
    }
    for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
      if ($currentTurnEffects[$i] == "DYN213") AddLayer("TRIGGER", $currentTurnEffects[$i + 1], "DYN213");
      if (IsCombatEffectActive($currentTurnEffects[$i]) && $currentTurnEffects[$i + 1] == $mainPlayer && !$combatChainState[$CCS_ChainLinkHitEffectsPrevented]) {
        AddCardEffectHitTrigger($currentTurnEffects[$i]); // Effects that do not gives it's effect to the attack
      }
    }
    if (IsHeroAttackTarget()) {
      $otherPlayer = ($mainPlayer == 1 ? 2 : 1);
      if (CheckMarked($otherPlayer)) {
        WriteLog("Player " . $otherPlayer . " loses their mark");
        RemoveMark($otherPlayer);
      }
    }
  } else {
    NonHitEffects($combatChain[0]);
  }
  $character = &GetPlayerCharacter($mainPlayer);
  $charID = $character[0];
  $charID = ShiyanaCharacter($charID);
  $currentPlayer = $mainPlayer;
  ProcessDecisionQueue(); //Any combat related decision queue logic should be main player gamestate
}

function FinalizeChainLink($chainClosed = false)
{
  global $turn, $actionPoints, $combatChain, $mainPlayer, $currentPlayer, $combatChainState, $actionPoints, $CCS_DamageDealt;
  global $mainClassState, $CS_AtksWWeapon, $CCS_GoesWhereAfterLinkResolves, $CS_LastAttack, $CCS_LinkTotalAttack, $CS_NumSwordAttacks, $chainLinks, $chainLinkSummary;
  global $CS_AnotherWeaponGainedGoAgain, $CCS_HitThisLink, $CS_ModalAbilityChoosen, $CS_NumSpectralShieldAttacks, $CombatChain;
  BuildMainPlayerGameState();
  if (DoesAttackHaveGoAgain() && !$chainClosed) {
    if(SearchCurrentTurnEffects("ROS010", $currentPlayer)) {
      $count = CountCurrentTurnEffects("ROS010", $currentPlayer);
      for ($i=0; $i < $count; $i++) { 
        AddLayer("TRIGGER", $currentPlayer, "ROS010");
      }
    }
    GainActionPoints(1, $mainPlayer);
    if ($combatChain[0] == "DVR002" && SearchCharacterActive($mainPlayer, "DVR001")) DoriQuicksilverProdigyEffect();
    if (TypeContains($combatChain[0], "W", $mainPlayer) && GetClassState($mainPlayer, $CS_AnotherWeaponGainedGoAgain) == "-") SetClassState($mainPlayer, $CS_AnotherWeaponGainedGoAgain, $combatChain[0]);
  }
  array_push($chainLinkSummary, $combatChainState[$CCS_DamageDealt]);
  array_push($chainLinkSummary, $combatChainState[$CCS_LinkTotalAttack]);
  array_push($chainLinkSummary, TalentOverride(isset($combatChain[0]) ? $combatChain[0] : "", $mainPlayer));
  array_push($chainLinkSummary, ClassOverride(isset($combatChain[0]) ? $combatChain[0] : "", $mainPlayer));
  array_push($chainLinkSummary, SerializeCurrentAttackNames());
  $numHitsOnLink = ($combatChainState[$CCS_DamageDealt] > 0 ? 1 : 0);
  $numHitsOnLink += intval($combatChainState[$CCS_HitThisLink]);
  array_push($chainLinkSummary, $numHitsOnLink);
  array_push($chainLinkSummary, CurrentEffectBaseAttackSet());
  array_push($chainLinkSummary, GetClassState($mainPlayer, $CS_ModalAbilityChoosen));
  
  ResolveWagers();
  ResolutionStepEffectTriggers();
  ResolutionStepCharacterTriggers();
  ResolutionStepAttackTriggers();
  

  array_push($chainLinks, array());
  $CLIndex = count($chainLinks) - 1;
  for ($i = 1; $i < count($combatChain); $i += CombatChainPieces()) {
    $cardType = CardType($combatChain[$i - 1]);
    if ($cardType != "W" || $cardType != "E" || $cardType != "C") {
      $params = explode(",", GoesWhereAfterResolving($combatChain[$i - 1], "COMBATCHAIN", $combatChain[$i]));
      $goesWhere = $params[0];
      if ($i == 1 && $combatChainState[$CCS_GoesWhereAfterLinkResolves] != "GY") $goesWhere = $combatChainState[$CCS_GoesWhereAfterLinkResolves];
      ResolveGoesWhere($goesWhere, $combatChain[$i - 1], $combatChain[$i], "CC", "", count($params) > 1 ? $params[1] : "NA");
    }
    array_push($chainLinks[$CLIndex], $combatChain[$i - 1]); //Card ID
    array_push($chainLinks[$CLIndex], $combatChain[$i]); //Player ID
    array_push($chainLinks[$CLIndex], ($goesWhere == "GY" && $combatChain[$i + 1] != "PLAY" ? "1" : "0")); //Still on chain? 1 = yes, 0 = no
    array_push($chainLinks[$CLIndex], $combatChain[$i + 1]); //From
    array_push($chainLinks[$CLIndex], $combatChain[$i + 4]); //Attack Modifier
    array_push($chainLinks[$CLIndex], $combatChain[$i + 5]); //Defense Modifier
    array_push($chainLinks[$CLIndex], "-"); //Added On-hits (comma separated)
    array_push($chainLinks[$CLIndex], $combatChain[$i + 8]); //Original card ID, differs from CardID in case of copies
  }

  //Clean up combat effects that were used and are one-time
  CleanUpCombatEffects();
  CopyCurrentTurnEffectsFromCombat();
  ChainLinkResolvedEffects();

  //Don't change state until the end, in case it changes what effects are active
  if ($CombatChain->HasCurrentLink()) {
    if (TypeContains($combatChain[0], "W", $mainPlayer) && !$chainClosed) {
      ++$mainClassState[$CS_AtksWWeapon];
      if (CardSubtype($combatChain[0]) == "Sword") ++$mainClassState[$CS_NumSwordAttacks];
    }
    if (CardName($combatChain[0]) == "Spectral Shield") ++$mainClassState[$CS_NumSpectralShieldAttacks];
    SetClassState($mainPlayer, $CS_LastAttack, $combatChain[0]);
  }
  $combatChain = [];
  if ($chainClosed) {
    ResetCombatChainState();
    $turn[0] = "M";
    FinalizeAction();
  } else {
    ResetChainLinkState();
  }
  ProcessDecisionQueue();
}

function CleanUpCombatEffects($weaponSwap = false, $isSpectraTarget = false)
{
  global $currentTurnEffects, $combatChainState, $CCS_DamageDealt, $combatChain, $chainLinks;
  $effectsToRemove = [];
  $CLIndex = count($chainLinks) - 1;
  $addedOnHits = "";
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    $effectArr = explode(",", $currentTurnEffects[$i]);
    if (IsCombatEffectActive($effectArr[0], $isSpectraTarget) && !IsCombatEffectLimited($i) && !IsCombatEffectPersistent($effectArr[0]) && !AdministrativeEffect($effectArr[0])) {
      if ($weaponSwap && EffectHasBlockModifier($effectArr[0])) continue;
      --$currentTurnEffects[$i + 3];
      if ($currentTurnEffects[$i + 3] == 0) array_push($effectsToRemove, $i);
      if (AddedOnHit($currentTurnEffects[$i])) {
        //adding onhits after chain resolution
        $addedOnHits .= $currentTurnEffects[$i] . ",";
      }
    }
    if (substr($currentTurnEffects[$i], 0, 6) == "DYN065") array_push($effectsToRemove, $i);
    switch ($currentTurnEffects[$i]) {
      case "MON281":
      case "MON282":
      case "MON283":
      case "DYN079":
      case "DYN080":
      case "DYN081":
        array_push($effectsToRemove, $i);
        break;
      case "OUT108":
        if ($combatChainState[$CCS_DamageDealt] > 0 && CardType($combatChain[0]) == "AA") array_push($effectsToRemove, $i);
        break;
      default:
        break;
    }
  }
  if ($addedOnHits != "") $addedOnHits = substr($addedOnHits, 0, -1);
  if (isset($chainLinks[$CLIndex])) {
    if (isset($chainLinks[$CLIndex][6])) $chainLinks[$CLIndex][6] = $addedOnHits;
  } 
  for ($i = 0; $i < count($effectsToRemove); ++$i) RemoveCurrentTurnEffect($effectsToRemove[$i]);
}

function BeginTurnPass()
{
  global $mainPlayer, $layers;
  ResetCombatChainState(); // The combat chain must be closed prior to the turn ending. The close step is outlined in 7.8 - specifically: CR 2.1 - 7.8.7. Fifth and finally, the Close Step ends, and the Action Phase continues. The Action Phase will always continue after the combat chain is closed - so there is another round of priority windows

  // Only attempt to end turn if no triggers remain on stack
  if (count($layers) == 0 || $layers[0] != 'TRIGGER') {
    WriteLog("Main player passed priority. Attempting to end turn.");
    AddLayer("ENDTURN", $mainPlayer, "-");
  }

  ProcessDecisionQueue("");
}

function EndStep()
{
  global $mainPlayer, $turn;
  $turn[0] = "ENDPHASE";
  AddLayer("ENDPHASE", $mainPlayer, "-");
  AuraBeginEndPhaseTriggers();
  OpponentsAuraBeginEndPhaseTriggers();
  BeginEndPhaseEffectTriggers();
  UndoIntimidate(1);
  UndoIntimidate(2);
  RemoveBanishedCardFromGraveyard();
  if (HeaveIndices() != "") AddLayer("TRIGGER", $mainPlayer, "HEAVE");
  UndoShiyanaBaseLife();
}

function UndoShiyanaBaseLife() // Technically not a End Step Trigger but it's the last time she'll remember what she changed into
{
  global $mainPlayer, $defPlayer;
  $mainChar = GetPlayerCharacter($mainPlayer);
  $defChar = GetPlayerCharacter($defPlayer);
  if ($defChar[0] == "CRU097" && SearchCurrentTurnEffects($mainChar[0] . "-SHIYANA", $defPlayer)) {
    $lifeDifference = GeneratedCharacterHealth($mainChar[0]) - GeneratedCharacterHealth("CRU097");
    if ($lifeDifference > 0) LoseHealth($lifeDifference, $defPlayer);
    elseif ($lifeDifference < 0) GainHealth(abs($lifeDifference), $defPlayer, true, false);
  }
}

function UndoIntimidate($player)
{
  $banish = &GetBanish($player);
  $hand = &GetHand($player);
  for ($i = count($banish) - BanishPieces(); $i >= 0; $i -= BanishPieces()) {
    if ($banish[$i + 1] == "INT") {
      array_push($hand, $banish[$i]);
      RemoveBanish($player, $i);
      continue;
    }
    if ($banish[$i + 1] == "NOFEAR" && SearchLayersForCardID("HVY016") == -1) {
      AddLayer("TRIGGER", $player, "HVY016", "-");
    }
    if ($banish[$i + 1] == "STONERAIN" && SearchLayersForCardID("AAZ016") == -1) {
      AddLayer("TRIGGER", $player, "AAZ016", "-");
    }
  }
}

function RemoveBanishedCardFromGraveyard() //Already Dead code
{
  global $defPlayer;
  $banish = &GetBanish($defPlayer);
  for ($i = count($banish) - BanishPieces(); $i >= 0; $i -= BanishPieces()) {
    if ($banish[$i + 1] == "REMOVEGRAVEYARD") {
      $index = SearchGetFirstIndex(SearchMultizone($defPlayer, "MYDISCARD:cardID=" . $banish[$i]));
      RemoveGraveyard($defPlayer, $index);
    }
  }
}

//4.4.1. Players do not get priority during the End Phase
//CR 2.0 4.4.2.- Beginning of the end phase
function FinishTurnPass()
{
  global $mainPlayer, $turn;
  if ($turn[0] == "OVER") return;
  ClearLog();
  ResetCombatChainState();
  QuellEndPhase(1);
  QuellEndPhase(2);
  ItemEndTurnAbilities();
  AuraBeginEndPhaseAbilities();
  ArsenalBeginEndPhaseAbilities();
  MainCharacterBeginEndPhaseAbilities();
  LandmarkBeginEndPhaseAbilities();
  BeginEndPhaseEffects();
  PermanentBeginEndPhaseEffects();
  AddDecisionQueue("PASSTURN", $mainPlayer, "-");
  ProcessDecisionQueue("");
}

function PassTurn()
{
  global $playerID, $currentPlayer, $turn, $mainPlayer, $mainPlayerGamestateStillBuilt;
  if (!$mainPlayerGamestateStillBuilt) {
    BuildMainPlayerGameState();
  }
  $MainHand = GetHand($mainPlayer);
  if (EndTurnPitchHandling($playerID)) {
    if (EndTurnPitchHandling(($playerID == 1 ? 2 : 1))) {
      if (count($MainHand) > 0 && !ArsenalFull($mainPlayer) && $turn[0] != "ARS") {
        $currentPlayer = $mainPlayer;
        $turn[0] = "ARS";
      } else {
        FinalizeTurn();
      }
    }
  }
}

function FinalizeTurn()
{
  global $currentPlayer, $currentTurn, $turn, $combatChain, $actionPoints, $mainPlayer, $defPlayer, $currentTurnEffects, $nextTurnEffects;
  global $mainHand, $defHand, $currentTurnEffectsFromCombat, $mainCharacter, $defCharacter, $mainResources, $defResources;
  global $mainAuras, $firstPlayer, $lastPlayed, $layerPriority, $EffectContext;
  global $MakeStartTurnBackup;
  $EffectContext = "-";
  LogEndTurnStats($mainPlayer);
  CurrentEffectEndTurnAbilities();
  AuraEndTurnAbilities();
  AllyEndTurnAbilities();
  MainCharacterEndTurnAbilities();
  //4.4.3a Allies life totals are reset
  AllyBeginEndTurnEffects();
  //4.4.3b The turn player may put a card from their hand face down into an empty arsenal zone they own
  ArsenalEndTurn($mainPlayer);
  ArsenalEndTurn($defPlayer);
  //4.4.3c Each player puts all cards in their pitch zone (if any) on the bottom of their deck in any order.The order cards are put on the bottom of the deck this way is hidden information
  //Reset characters/equipment
  for ($i = 1; $i < count($mainCharacter); $i += CharacterPieces()) {
    if ($mainCharacter[$i - 1] == "CRU177" && $mainCharacter[$i + 1] >= 3) $mainCharacter[$i] = 0; //Destroy Talishar if >= 3 rust counters
    if ($mainCharacter[$i + 6] == 1) {
      DestroyCharacter($mainPlayer, $i-1); //Destroy if it was flagged for destruction
      $mainCharacter[$i + 6] = 0;
    }
    if ($mainCharacter[$i] != 0) {
      if ($mainCharacter[$i] != 4) $mainCharacter[$i] = 2;
      $mainCharacter[$i + 4] = CharacterNumUsesPerTurn($mainCharacter[$i - 1]);
    }
  }
  for ($i = 1; $i < count($defCharacter); $i += CharacterPieces()) {
    if ($defCharacter[$i + 6] == 1) {
      DestroyCharacter($defPlayer, $i-1); //Destroy if it was flagged for destruction
      $defCharacter[$i + 6] = 0;
    }
    if ($defCharacter[$i] == 1 || $defCharacter[$i] == 2) {
      if ($defCharacter[$i] != 4) $defCharacter[$i] = 2;
      $defCharacter[$i + 4] = CharacterNumUsesPerTurn($defCharacter[$i - 1]);
    }
  }
  //Reset Auras
  for ($i = 0; $i < count($mainAuras); $i += AuraPieces()) {
    $mainAuras[$i + 1] = 2;
  }
  //4.4.3d All players lose all action points and resources.
  $mainResources[0] = 0;
  $mainResources[1] = 0;
  $defResources[0] = 0;
  $defResources[1] = 0;
  $lastPlayed = [];
  // 4.4.3e The turn player draws cards until the number of cards in their hand is equal to their hero’s intellect
  if ($mainPlayer == $firstPlayer && $currentTurn == 1)//Defender draws up on turn 1
  {
    $toDraw = CharacterIntellect($defCharacter[0]) - count($defHand);
    for ($i = 0; $i < $toDraw; ++$i) {
      Draw($defPlayer, false, false);
    }
  }
  $toDraw = CharacterIntellect($mainCharacter[0]) - count($mainHand) + CurrentEffectIntellectModifier() + AuraIntellectModifier();
  for ($i = 0; $i < $toDraw; ++$i) {
    Draw($mainPlayer, false, false);
  }
  ResetMainClassState();
  ResetCharacterEffects();
  UnsetTurnBanish();
  AuraEndTurnCleanup();
  DoGamestateUpdate();
  //Update all the player neutral stuff
  if ($mainPlayer == 2) $currentTurn += 1;
  $turn[0] = "M";
  $turn[2] = "";
  $turn[3] = "";
  $actionPoints = 1;
  $combatChain = [];
  $currentTurnEffectsFromCombat = [];
  $currentTurnEffects = [];
  for ($i = count($nextTurnEffects) - NextTurnPieces(); $i >= 0; $i -= NextTurnPieces()) {
    if ($nextTurnEffects[$i + 4] == 1) {
      for ($j = 0; $j < NextTurnPieces(); ++$j) {
        if ($j < CurrentTurnEffectsPieces()) array_push($currentTurnEffects, $nextTurnEffects[$i + $j]);
        unset($nextTurnEffects[$i + $j]);
      }
    } else --$nextTurnEffects[$i + 4];
  }
  $nextTurnEffects = array_values($nextTurnEffects);
  $defPlayer = $mainPlayer;
  $mainPlayer = ($mainPlayer == 1 ? 2 : 1);
  $currentPlayer = $mainPlayer;
  BuildMainPlayerGameState();
  ResetMainClassState();
  //Start of turn effects
  if ($mainPlayer == 1) StatsStartTurn();
  StartTurnAbilities();
  $MakeStartTurnBackup = true;
  $layerPriority[0] = ShouldHoldPriority(1);
  $layerPriority[1] = ShouldHoldPriority(2);
  WriteLog("Player $mainPlayer's turn $currentTurn has begun");
  DoGamestateUpdate();
  ProcessDecisionQueue();
}

function PlayCard($cardID, $from, $dynCostResolved = -1, $index = -1, $uniqueID = -1)
{
  global $playerID, $turn, $currentPlayer, $actionPoints, $layers, $CombatChain;
  global $CS_NumActionsPlayed, $CS_NumNonAttackCards, $CS_NumPlayedFromBanish, $CS_DynCostResolved;
  global $CS_NumAttackCards, $CS_NumBloodDebtPlayed, $layerPriority, $CS_NumWizardNonAttack, $lastPlayed, $CS_PlayIndex, $CS_NumBluePlayed;
  global $decisionQueue, $CS_AbilityIndex, $CS_NumRedPlayed, $CS_PlayUniqueID, $CS_LayerPlayIndex, $CS_LastDynCost, $CS_NumCardsPlayed, $CS_NamesOfCardsPlayed, $CS_NumLightningPlayed;
  global $CS_PlayedAsInstant, $mainPlayer, $EffectContext, $combatChainState, $CCS_GoesWhereAfterLinkResolves, $CS_NumAttacks, $CCS_NumInstantsPlayedByAttackingPlayer;
  global $CCS_NextInstantBouncesAura, $CS_ActionsPlayed, $CS_AdditionalCosts, $CS_NumInstantPlayed;
  global $CS_NumDraconicPlayed, $currentTurnEffects, $CS_TunicTicks, $CCS_NumUsedInReactions, $CCS_NumReactionPlayedActivated, $CS_NumStealthAttacks;

  $otherPlayer = $currentPlayer == 1 ? 2 : 1;
  $resources = &GetResources($currentPlayer);
  $pitch = &GetPitch($currentPlayer);
  $dynCostResolved = intval($dynCostResolved);
  $layerPriority[0] = ShouldHoldPriority(1);
  $layerPriority[1] = ShouldHoldPriority(2);
  $cardType = CardType($cardID);
  $playingCard = $turn[0] != "P" && ($turn[0] != "B" || count($layers) > 0);  
  $mod = "";
  //manual tunic ticking
  if ($playingCard && $cardID == "WTR150" && ManualTunicSetting($currentPlayer) && GetClassState($currentPlayer, $CS_TunicTicks) == 0) {
    $character = &GetPlayerCharacter($currentPlayer);
    $cardIndex = FindCharacterIndex($currentPlayer, $cardID);
    if ($character[$cardIndex + 2] < 3) {
      ++$character[$cardIndex + 2];
      IncrementClassState($currentPlayer, $CS_TunicTicks);
      return;
    }
  }
  if ($dynCostResolved == -1) {
    //CR 5.1.1 Play a Card (CR 2.0) - Layer Created
    if ($playingCard) {
      SetClassState($currentPlayer, $CS_AbilityIndex, $index);
      $layerIndex = AddLayer($cardID, $currentPlayer, $from, "-", "-", $uniqueID);
      SetClassState($currentPlayer, $CS_LayerPlayIndex, $layerIndex);
    }
    //CR 5.1.2 Announce (CR 2.0)
    if ($from == "ARS") WriteLog("Player " . $playerID . " " . PlayTerm($turn[0]) . " " . CardLink($cardID, $cardID) . " from arsenal", $turn[0] != "P" ? $currentPlayer : 0);
    else WriteLog("Player " . $playerID . " " . PlayTerm($turn[0], $from, $cardID) . " " . CardLink($cardID, $cardID), $turn[0] != "P" ? $currentPlayer : 0);
    if ($turn[0] == "B" && TypeContains($cardID, "E", $currentPlayer)) SetClassState($currentPlayer, $CS_PlayUniqueID, $uniqueID);

    LogPlayCardStats($currentPlayer, $cardID, $from);
    if ($playingCard) {
      ClearAdditionalCosts($currentPlayer);
      MakeGamestateBackup();
      $lastPlayed = [];
      $lastPlayed[0] = $cardID;
      $lastPlayed[1] = $currentPlayer;
      $lastPlayed[2] = $cardType;
      $lastPlayed[3] = "-";
      SetClassState($currentPlayer, $CS_PlayUniqueID, $uniqueID);
    }
    if (count($layers) > 0 && $layers[count($layers) - LayerPieces()] == "ENDTURN") $layers[count($layers) - LayerPieces()] = "RESUMETURN"; //Means the defending player played something, so the end turn attempt failed
  }
  if ($turn[0] == "A" && $currentPlayer == $mainPlayer) {
    ++$combatChainState[$CCS_NumUsedInReactions];
  }
  if ($turn[0] != "P") {
    if ($dynCostResolved >= 0) {
      if ($playingCard && SearchCurrentTurnEffects("HNT167", $currentPlayer) && TypeContains($cardID, "AA") && !SearchCurrentTurnEffects("HNT167-ATTACK", $currentPlayer) && GetResolvedAbilityName($cardID, $from) != "Ability") {
        AddCurrentTurnEffect("HNT167-ATTACK", $currentPlayer);
      }
      SetClassState($currentPlayer, $CS_DynCostResolved, $dynCostResolved);
      $baseCost = ($from == "PLAY" || $from == "EQUIP" ? AbilityCost($cardID) : (CardCost($cardID, $from) + SelfCostModifier($cardID, $from)));
      if(HasMeld($cardID) && GetClassState($currentPlayer, $CS_AdditionalCosts) == "Both") $baseCost += $baseCost;
      if (!$playingCard) $resources[1] += $dynCostResolved;
      else {
        $isAlternativeCostPaid = IsAlternativeCostPaid($cardID, $from);
        if ($isAlternativeCostPaid) {
          $baseCost = 0;
          AddAdditionalCost($currentPlayer, "ALTERNATIVECOST");
        }
        $resources[1] += ($dynCostResolved > 0 ? $dynCostResolved + SelfCostModifier($cardID, $from) : $baseCost) + CurrentEffectCostModifiers($cardID, $from) + AuraCostModifier($cardID) + CharacterCostModifier($cardID, $from, $baseCost) + BanishCostModifier($from, $index, $baseCost);
        if ($isAlternativeCostPaid && $resources[1] > 0) WriteLog("<span style='color:red;'>Alternative costs do not offset additional costs.</span>");
      }
      if ($resources[1] < 0) $resources[1] = 0;
      LogResourcesUsedStats($currentPlayer, $resources[1]);
    } else {
      $dqCopy = $decisionQueue;
      $decisionQueue = [];
      //CR 5.1.3 Declare Costs Begin (CR 2.0)
      $resources[1] = 0;
      if ($playingCard && substr($from, 0, 5) == "THEIR") {
        if ((SearchCurrentTurnEffects("MST001", $currentPlayer) || SearchCurrentTurnEffects("MST002", $currentPlayer)) && ColorContains($cardID, 3, $otherPlayer)) {
          if($cardID == "WTR051" || $cardID == "WTR052" || $cardID == "WTR053") $dynCost = "0,4"; //It's cost when played by Nuu
          else $dynCost = 0; //If you are playing a card without paying its {r} cost, and part of that cost involves X, then you can only choose X=0.
          SetClassState($currentPlayer, $CS_LastDynCost, $dynCost);
        }
      } 
      elseif ($playingCard) $dynCost = DynamicCost($cardID); //CR 5.1.3a Declare variable cost (CR 2.0)
      else $dynCost = "";
      if ($playingCard) AddPrePitchDecisionQueue($cardID, $from, $index); //CR 5.1.3b,c Declare additional/optional costs (CR 2.0)
      if ($dynCost != "" || ($dynCost == 0 && substr($from, 0, 5) != "THEIR")) {
        AddDecisionQueue("DYNPITCH", $currentPlayer, $dynCost);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_LastDynCost);
      }
      //CR 5.1.4. Declare Modes and Targets
      //CR 5.1.4a Declare targets for resolution abilities
      if ($turn[0] != "B" || (count($layers) > 0 && $layers[0] != "")) GetLayerTarget($cardID, $from);
      //CR 5.1.4b Declare target of attack
      if ($turn[0] == "M" && $actionPoints > 0) AddDecisionQueue("GETTARGETOFATTACK", $currentPlayer, $cardID . "," . $from);
      if ($dynCost == "") AddDecisionQueue("PASSPARAMETER", $currentPlayer, "0");
      else AddDecisionQueue("GETCLASSSTATE", $currentPlayer, $CS_LastDynCost);
      AddDecisionQueue("RESUMEPAYING", $currentPlayer, $cardID . "-" . $from . "-" . $index . "-" . $uniqueID);
      $decisionQueue = array_merge($decisionQueue, $dqCopy);
      ProcessDecisionQueue();
      //MISSING CR 5.1.3d Decide if action that can be played as instant will be
      //MISSING CR 5.1.3e Decide order of costs to be paid
      return;
    }
  } else if ($turn[0] == "P") {
    $pitchValue = PitchValue($cardID);
    $resources[0] += $pitchValue;
    array_push($pitch, $cardID);
    if (CardCaresAboutPitch($turn[3])) AddAdditionalCost($currentPlayer, $cardID);
    PitchAbility($cardID);
  }
  //CR 2.0 5.1.7. Pay Asset-Costs
  if ($resources[0] < $resources[1] || (CardCareAboutChiPitch($cardID) && ($turn[0] != "B" || count($layers) > 0))) {
    if ($turn[0] != "P") {
      $turn[2] = $turn[0];
      $turn[3] = $cardID;
      $turn[4] = $from;
    }
    $turn[0] = "P";
    return; //We know we need to pitch more, short circuit here
  }
  $resources[0] -= $resources[1];
  $resourcesPaid = GetClassState($currentPlayer, $CS_DynCostResolved);
  $resources[1] = 0;
  if ($turn[0] == "P") {
    $turn[0] = $turn[2];
    $cardID = $turn[3];
    $from = $turn[4];
    $playingCard = $turn[0] != "P" && ($turn[0] != "B" || count($layers) > 0);
  }
  if (GetClassState($currentPlayer, $CS_LastDynCost) != 0 && DynamicCost($cardID) != "") WriteLog(CardLink($cardID, $cardID) . " was played with a cost of " . GetClassState($currentPlayer, $CS_LastDynCost) . ".");
  $cardType = CardType($cardID);
  $abilityType = "";
  $playType = $cardType;
  $EffectContext = $cardID;
  PlayerMacrosCardPlayed();
  //We've paid resources, now pay action points if applicable
  if ($playingCard) {
    if (ActionsThatDoArcaneDamage($cardID, $currentPlayer) || ActionsThatDoXArcaneDamage($cardID)) {
      if(!HasMeld($cardID) && (GetResolvedAbilityType($cardID) == "A" || GetResolvedAbilityType($cardID) == "") || HasMeld($cardID) && (GetClassState($currentPlayer, $CS_AdditionalCosts) != "Life" && GetClassState($currentPlayer, $CS_AdditionalCosts) != "Null"))
      {
        AssignArcaneBonus($currentPlayer);
      }
      else ClearNextCardArcaneBuffs($currentPlayer, $cardID, $from);
    } 
    $canPlayAsInstant = CanPlayAsInstant($cardID, $index, $from) || (DelimStringContains($cardType, "I") && $turn[0] != "M");
    SetClassState($currentPlayer, $CS_PlayedAsInstant, "0");
    IncrementClassState($currentPlayer, $CS_NumCardsPlayed);
    if($CombatChain->HasCurrentLink() && $CombatChain->AttackCard()->ID() == "ROS076" && DelimStringContains(CardType($cardID), "I") && $currentPlayer == $mainPlayer) {
      if(SearchCurrentTurnEffects("ROS076", $mainPlayer, true)) {
        AddDecisionQueue("YESNO", $mainPlayer, "if you want to return ".CardLink("ROS076", "ROS076")." to your hand?");
        AddDecisionQueue("NOPASS", $mainPlayer, "-");
        AddDecisionQueue("GONEINAFLASH", $mainPlayer, "-", 1);
      }
    }
    if($CombatChain->HasCurrentLink() && $CombatChain->AttackCard()->ID() == "HNT100" && $currentPlayer == $mainPlayer && !SearchCurrentTurnEffects("HNT100", $currentPlayer)) {
      if (!IsStaticType($cardType, $from, $cardID) && (TalentContains($cardID, "DRACONIC", $currentPlayer)) && GetResolvedAbilityType($cardID, $from) != "I") {
        AddCurrentTurnEffect("HNT100", $currentPlayer);
        GiveAttackGoAgain();
      }
    }
    if (IsStaticType($cardType, $from, $cardID)) {
      $playType = GetResolvedAbilityType($cardID, $from);
      $abilityType = $playType;
      PayAbilityAdditionalCosts($cardID, GetClassState($currentPlayer, $CS_AbilityIndex));
      ActivateAbilityEffects();
      if (GetResolvedAbilityType($cardID, $from) == "A" && !$canPlayAsInstant) {
        ResetCombatChainState();
      }
    } else {
      if (GetClassState($currentPlayer, $CS_NamesOfCardsPlayed) == "-") SetClassState($currentPlayer, $CS_NamesOfCardsPlayed, $cardID);
      else SetClassState($currentPlayer, $CS_NamesOfCardsPlayed, GetClassState($currentPlayer, $CS_NamesOfCardsPlayed) . "," . $cardID);
      if (DelimStringContains($cardType, "A") || $cardType == "AA"){
        if (GetClassState($currentPlayer, $CS_ActionsPlayed) == "-") SetClassState($currentPlayer, $CS_ActionsPlayed, $cardID);
        else SetClassState($currentPlayer, $CS_ActionsPlayed, GetClassState($currentPlayer, $CS_ActionsPlayed) . "," . $cardID);
      }
      if (DelimStringContains($cardType, "A") && !$canPlayAsInstant && !GoesOnCombatChain($turn[0], $layers[count($layers)-LayerPieces()], $from, $currentPlayer)) {
        ResetCombatChainState();
      }
      $remorselessCount = CountCurrentTurnEffects("CRU123-DMG", $playerID);
      if ((DelimStringContains($cardType, "A") || $cardType == "AA") && $remorselessCount > 0 && GetAbilityTypes($cardID, from: $from) == "") {
        WriteLog("Player " . $playerID . " lost " . $remorselessCount . " life to " . CardLink("CRU123", "CRU123"));
        LoseHealth($remorselessCount, $playerID);
      } elseif ((DelimStringContains($cardType, "A") || $cardType == "AA") && $remorselessCount > 0 && (GetResolvedAbilityType($cardID, $from) == "" || GetResolvedAbilityType($cardID, $from) == "AA" || GetResolvedAbilityType($cardID, $from) == "A")) {
        WriteLog("Player " . $playerID . " lost " . $remorselessCount . " life to " . CardLink("CRU123", "CRU123"));
        LoseHealth($remorselessCount, $playerID);
      }
      if (CardNameContains($cardID, "Moon Wish", $currentPlayer)) AddCurrentTurnEffect("ARC185-GA", $currentPlayer);
      if (ClassContains($cardID, "ILLUSIONIST", $currentPlayer) && DelimStringContains(CardSubType($cardID), "Aura") && CardCost($cardID, $from) <= 2 && SearchCurrentTurnEffects("MST155-INST", $currentPlayer, true)) {
        AddCurrentTurnEffect("MST155", $currentPlayer);
      }
      if (ClassContains($cardID, "ILLUSIONIST", $currentPlayer) && DelimStringContains(CardSubType($cardID), "Aura") && CardCost($cardID, $from) <= 1 && SearchCurrentTurnEffects("MST156-INST", $currentPlayer, true)) {
        AddCurrentTurnEffect("MST156", $currentPlayer);
      }
      if (ClassContains($cardID, "ILLUSIONIST", $currentPlayer) && DelimStringContains(CardSubType($cardID), "Aura") && CardCost($cardID, $from) <= 0 && SearchCurrentTurnEffects("MST157-INST", $currentPlayer, true)) {
        AddCurrentTurnEffect("MST157", $currentPlayer);
      }
      if (DelimStringContains(CardSubType($cardID), "Aura")) {
        SearchCurrentTurnEffects("ROS251", $currentPlayer, true);
      }
      CombatChainPlayAbility($cardID);
      ItemPlayAbilities($cardID, $from);
    }
    if (EffectPlayCardRestricted($cardID, $playType, $from, true)) return;
    if (TalentContains($cardID, "DRACONIC", $currentPlayer) && $from != "EQUIP" && $from != "PLAY" && GetResolvedAbilityName($cardID, $from) != "Ability") {
      IncrementClassState($currentPlayer, $CS_NumDraconicPlayed);
      SearchCurrentTurnEffects("HNT167", $currentPlayer, remove:true);
    }
    if (HasStealth($cardID) && (GetResolvedAbilityType($cardID, $from) == "AA" || GetResolvedAbilityType($cardID, $from) == "")) {
      IncrementClassState($currentPlayer, piece: $CS_NumStealthAttacks);
    }
    if (DelimStringContains($playType, "A") || DelimStringContains($playType, "AA")) {
      if($from == "BANISH") $mod = GetBanishModifier($index);
      if ($actionPoints > 0) {
        if(!$canPlayAsInstant) --$actionPoints;
        elseif(GetResolvedAbilityType($cardID, $from) == "AA") --$actionPoints;
        elseif(!$canPlayAsInstant && !IsMeldInstantName(GetClassState($currentPlayer, $CS_AdditionalCosts)) 
        && (GetResolvedAbilityType($cardID, $from) == "A" && $mod != "INST")) {
          --$actionPoints;
        }
        elseif(GetResolvedAbilityType($cardID, $from) == "A" && $mod != "INST" && GetAbilityNames($cardID, from: $from) != "") {
          --$actionPoints;
        }
      }
      if (DelimStringContains($cardType, "A") && $abilityType == "" && GetResolvedAbilityType($cardID, $from) != "I") {
        IncrementClassState($currentPlayer, $CS_NumNonAttackCards);
        if (ClassContains($cardID, "WIZARD", $currentPlayer)) {
          IncrementClassState($currentPlayer, $CS_NumWizardNonAttack);
        }
      }
      if (GetResolvedAbilityType($cardID, $from) != "I") IncrementClassState($currentPlayer, $CS_NumActionsPlayed);
    }
    if ($from == "BANISH" || $from == "THEIRBANISH") IncrementClassState($currentPlayer, $CS_NumPlayedFromBanish);
    if (HasBloodDebt($cardID)) IncrementClassState($currentPlayer, $CS_NumBloodDebtPlayed);
    if (ColorContains($cardID, 1, $currentPlayer) && $from != "PLAY" && GetResolvedAbilityType($cardID, $from) != "I") IncrementClassState($currentPlayer, $CS_NumRedPlayed);
    if (ColorContains($cardID, 3, $currentPlayer) && $from != "PLAY" && GetResolvedAbilityType($cardID, $from) != "I") IncrementClassState($currentPlayer, $CS_NumBluePlayed);
    if (TalentContains($cardID, "LIGHTNING", $currentPlayer) && $from != "EQUIP" && $from != "PLAY" && GetResolvedAbilityType($cardID, $from) != "I") {
      IncrementClassState($currentPlayer, $CS_NumLightningPlayed);
    }
    if(DelimStringContains($cardType, "I")) {
      if(!HasMeld($cardID)) IncrementClassState($currentPlayer, $CS_NumInstantPlayed);
      elseif($from != "MELD") IncrementClassState($currentPlayer, $CS_NumInstantPlayed);
    }
    if(DelimStringContains($cardType, "AR") || DelimStringContains($abilityType, "AR")) {
      ++$combatChainState[$CCS_NumReactionPlayedActivated];
    }
    if (($CombatChain->HasCurrentLink()) && $from != "EQUIP" && $from != "PLAY" && DelimStringContains($playType, "I") && GetResolvedAbilityType($cardID, $from) != "I" && $mainPlayer == $currentPlayer) {
      ++$combatChainState[$CCS_NumInstantsPlayedByAttackingPlayer];
      if ($combatChainState[$CCS_NextInstantBouncesAura] == 1) {
        $triggeredID = $CombatChain->AttackCard()->ID();
        $context = "Blast to Oblivion trigger: Choose an aura to return to its owner's hand (or pass)";
        $search = "THEIRAURAS:minCost=0;maxCost=1&THEIRAURAS:type=T&MYAURAS:minCost=0;maxCost=1&MYAURAS:type=T";
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, $search);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, $context);
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("ADDTRIGGER", $currentPlayer, $triggeredID, 1);
        $combatChainState[$CCS_NextInstantBouncesAura] = 0;
      }
    }
    AddCharacterPlayCardTrigger($cardID, $playType, $from);
    PayAdditionalCosts($cardID, $from, index: $index);
    ResetCardPlayed($cardID, $from);
  }
  if ($turn[0] == "B" && $cardType == "AA" && (GetResolvedAbilityType($cardID, $from) == "AA" || GetResolvedAbilityType($cardID, $from) == "")) IncrementClassState($currentPlayer, $CS_NumAttackCards); //Played or blocked
  if ($from == "BANISH") {
    $banish = new Banish($currentPlayer);
    $banish->Remove(GetClassState($currentPlayer, $CS_PlayIndex));
  } else if ($from == "THEIRBANISH") {
    $banish = new Banish($otherPlayer);
    $banish->Remove(GetClassState($currentPlayer, $CS_PlayIndex));
    $combatChainState[$CCS_GoesWhereAfterLinkResolves] == "THEIRDISCARD";
  }
  if ($turn[0] != "B" || (count($layers) > 0 && $layers[0] != "")) {
    if ($playType == "AA") IncrementClassState($currentPlayer, $CS_NumAttacks);
    MainCharacterPlayCardAbilities($cardID, $from);
    AuraPlayAbilities($cardID, $from);
    PermanentPlayAbilities($cardID, $from);
    if (SubtypeContains($cardID, "Evo", $currentPlayer, $uniqueID)) EvoOnPlayHandling($currentPlayer);
  }
  AddDecisionQueue("RESUMEPLAY", $currentPlayer, $cardID . "|" . $from . "|" . $resourcesPaid . "|" . GetClassState($currentPlayer, $CS_AbilityIndex) . "|" . GetClassState($currentPlayer, $CS_PlayUniqueID));
  ProcessDecisionQueue();
}

function PlayCardSkipCosts($cardID, $from)
{
  global $currentPlayer, $layers, $turn;
  $cardType = CardType($cardID);
  GetTargetOfAttack($cardID); // Not sure why this is needed (2x GetTargetOfAttack), but it works....
  if (($turn[0] == "M" || $turn[0] == "ATTACKWITHIT") && $cardType == "AA") GetTargetOfAttack($cardID);
  if ($turn[0] != "B" || (count($layers) > 0 && $layers[0] != "")) {
    GetLayerTarget($cardID, $from);
  }
  PlayCardEffect($cardID, $from, "Skipped");
}

function GetLayerTarget($cardID, $from)
{
  global $currentPlayer, $CombatChain;
  switch ($cardID) {
    case "CRU143":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:type=AA;class=RUNEBLADE");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose target Runeblade attack action card");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "CRU164":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "LAYER:type=I;maxCost=1");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "MON084":
    case "MON085":
    case "MON086":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "CCAA");
      AddDecisionQueue("CHOOSECOMBATCHAIN", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "MON303":
    case "MON304":
    case "MON305":
      $maxCost = 2;
      if ($cardID == "MON304") $maxCost = 1;
      elseif ($cardID == "MON305") $maxCost = 0;
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:maxCost=" . $maxCost . ";type=AA");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "ELE140":
    case "ELE141":
    case "ELE142":
      if($cardID == "ELE140") $minCost = 0;
      else if($cardID == "ELE141") $minCost = 1;
      else $minCost = 2;
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:type=A;talent=EARTH,ELEMENTAL;minCost=" . $minCost . "&MYDISCARD:type=AA;talent=EARTH,ELEMENTAL;minCost=" . $minCost);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose target action card");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "ELE183":
    case "ELE184":
    case "ELE185":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "COMBATCHAINLINK:maxCost=1;type=AA");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "EVR033":
    case "EVR034":
    case "EVR035":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "DAMAGEPREVENTIONTARGET");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a damage source for Steadfast");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "EVR124":
      global $CS_LastDynCost;
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, $cardID);
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
      AddDecisionQueue("GETCLASSSTATE", $currentPlayer, $CS_LastDynCost);
      AddDecisionQueue("SETSCOURDQVAR", $currentPlayer, "1");
      AddDecisionQueue("LESSTHANPASS", $currentPlayer, "1");
      AddDecisionQueue("FINDINDICES", $currentPlayer, "ARCANETARGET,{1}", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose target hero for <0>", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);  
      break;
    case "UPR169":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "LAYER:type=A");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "UPR004": //Invocations must target Ash
    case "UPR006":
    case "UPR007":
    case "UPR008":
    case "UPR009":
    case "UPR010":
    case "UPR011":
    case "UPR012":
    case "UPR013":
    case "UPR014":
    case "UPR015":
    case "UPR016":
    case "UPR017":
    case "UPR036":
    case "UPR037":
    case "UPR038":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYPERM:subtype=Ash");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose an Ash to transform");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "UPR039": //sand cover
    case "UPR040":
    case "UPR041":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYPERM:subtype=Ash");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose an Ash to grant ward");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "UPR183":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "DAMAGEPREVENTIONTARGET");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a damage source for Helio's Mitre");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "UPR221":
    case "UPR222":
    case "UPR223":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "DAMAGEPREVENTIONTARGET");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a damage source for Oasis Respite");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "DTD001":
    case "DTD002":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYPERM:subtype=Figment");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a figment to awaken");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "DTD038":
    case "DTD039":
    case "DTD040":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "CCDEFLESSX,999");
      AddDecisionQueue("CHOOSECOMBATCHAIN", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "DTD088":
    case "DTD089":
    case "DTD090":
      if($cardID == "DTD088") $targetPitch = 1;
      else if($cardID == "DTD089") $targetPitch = 2;
      else if($cardID == "DTD090") $targetPitch = 3;
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRAURAS:pitch=" . $targetPitch . "&MYAURAS:pitch=" . $targetPitch);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a target aura");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "MST097":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRDISCARD");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose target card");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "MST099":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:type=A&MYDISCARD:type=AA");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose target action card");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "MST105":
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, "COMBATCHAIN");
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID);
      break;
    case "MST134":
    case "MST135":
    case "MST136":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYAURAS:hasWard=true");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose target aura");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "ROS027":
      if($from != "HAND"){
        AddDecisionQueue("FINDINDICES", $currentPlayer, "DAMAGEPREVENTIONTARGET");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a damage source for Sanctuary of Aria");
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);  
        AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      }
      break;
    default:
      break;
  }
  $targetType = PlayRequiresTarget($cardID, $from);
  if ($targetType != -1) {
    AddDecisionQueue("PASSPARAMETER", $currentPlayer, $cardID);
    AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
    AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a target for <0>");
    AddDecisionQueue("FINDINDICES", $currentPlayer, "ARCANETARGET," . $targetType);
    AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a target for <0>");
    //TODO - Below two lines for may effects like Singe - too complicated for now but here it is for later
    //if($mayAbility) { AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1); }
    //else { AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1); }
    AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
    AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
    AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
  }
}

function AddPrePitchDecisionQueue($cardID, $from, $index = -1)
{
  global $currentPlayer, $CS_NumActionsPlayed, $CS_AdditionalCosts, $turn, $combatChainState, $CCS_EclecticMag, $CS_NextWizardNAAInstant, $CS_NextNAAInstant;
  global $actionPoints, $mainPlayer;
  $cardType = CardType($cardID);
  $mod = "";
  if($from == "BANISH") $mod = GetBanishModifier($index);
  if (IsStaticType($cardType, $from, $cardID)) {
    $names = GetAbilityNames($cardID, $index, $from);
    if ($names != "") {
      $names = str_replace("-,", "", $names);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which ability to activate");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, $names);
      AddDecisionQueue("SETABILITYTYPE", $currentPlayer, $cardID);
    }
  }
  if(HasMeld($cardID)) {
    $names = explode(" // ", CardName($cardID));
    $option = $names[0].",".$names[1].",Both";
    if (DelimStringContains($cardType, "A") && SearchCurrentTurnEffects("ARC043", $currentPlayer) && GetClassState($currentPlayer, $CS_NumActionsPlayed) >= 1) {
      $option = $names[1];
    } elseif (
      $mod != "INST" && $mod != "ELE064" 
      && $cardType != "I"
      && (!$combatChainState[$CCS_EclecticMag]
      && GetClassState($currentPlayer, $CS_NextWizardNAAInstant) == 0
      && GetClassState($currentPlayer, $CS_NextNAAInstant) == 0
      && ($actionPoints < 1 || $currentPlayer != $mainPlayer || $turn[0] == "INSTANT" || $turn[0] == "A")
      || SearchCurrentTurnEffects("WarmongersWar", $currentPlayer))
    ) {
        $option = $names[1];
    }
    AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which halves to activate");
    AddDecisionQueue("BUTTONINPUT", $currentPlayer, $option);
    AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
    AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
  }
  switch ($cardID) {
    case "WTR081":
      if (ComboActive($cardID)) {
        AddDecisionQueue("FINDINDICES", $currentPlayer, $cardID);
        AddDecisionQueue("MULTICHOOSEDISCARD", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEDISCARD", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIADDDECK", $currentPlayer, "-", 1);
        AddDecisionQueue("LORDOFWIND", $currentPlayer, "-", 1);
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
      }
      break;
    case "ARC185":
    case "ARC186":
    case "ARC187":
      HandToTopDeck($currentPlayer);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "ARC185", 1);
      break;
    case "CRU188":
      if (CountItem("CRU197", $currentPlayer) >= 4) //Copper
      {
        AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_pay_4_" . CardLink("CRU197", "CRU197"), 1);
        AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
        AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "CRU197-4", 1);
        AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "CRU188", 1);
        AddDecisionQueue("WRITELOG", $currentPlayer, CardLink("CRU197", "CRU197") . "_alternative_cost_was_paid.", 1);
      }
      if (CountItem("EVR195", $currentPlayer) >= 2) //Silver
      {
        AddDecisionQueue("SEARCHCURRENTEFFECTPASS", $currentPlayer, "CRU188");
        AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_pay_2_" . CardLink("EVR195", "EVR195"), 1);
        AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
        AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "EVR195-2", 1);
        AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "CRU188", 1);
        AddDecisionQueue("WRITELOG", $currentPlayer, CardLink("EVR195", "EVR195") . "_alternative_cost_was_paid.", 1);
      }
      if (CountItem("DYN243", $currentPlayer) >= 1) //Gold
      {
        AddDecisionQueue("SEARCHCURRENTEFFECTPASS", $currentPlayer, "CRU188");
        AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_pay_1_" . CardLink("DYN243", "DYN243"), 1);
        AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
        if (SearchCharacterAlive($currentPlayer, "HVY051")) {
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYITEMS:isSameName=DYN243&MYCHAR:cardID=HVY051", 1);
          AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
        } else
          AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "DYN243-1", 1);
        AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "CRU188", 1);
        AddDecisionQueue("WRITELOG", $currentPlayer, CardLink("DYN243", "DYN243") . "_alternative_cost_was_paid.", 1);
      }
      break;
    case "MON199":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "MULTIHAND");
      AddDecisionQueue("MULTICHOOSEHAND", $currentPlayer, "<-", 1);
      AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-", 1);
      AddDecisionQueue("MULTIBANISH", $currentPlayer, "HAND,NA", 1);
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "SOULREAPING", 1);
      break;
    case "MON257":
    case "MON258":
    case "MON259":
      HandToTopDeck($currentPlayer);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "MON257", 1);
      break;
    case "EVR161":
    case "EVR162":
    case "EVR163":
      global $chainLinkSummary;
      $attackNames = explode(",", $chainLinkSummary[count($chainLinkSummary) - ChainLinkSummaryPieces() + 4]);
      for ($i = 0; $i < count($attackNames); ++$i) {
        $attackName = GamestateUnsanitize($attackNames[$i]);
        if ($attackName == "Crazy Brew") {
          AddCurrentTurnEffect($cardID, $currentPlayer);
          AddDecisionQueue("PASSPARAMETER", $currentPlayer, "PASS");
        }
      }
      AddDecisionQueue("FINDINDICES", $currentPlayer, "LIFEOFPARTY");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
      break;
    //Windups
    case "HVY143":
    case "HVY144":
    case "HVY145":
    case "HVY163":
    case "HVY164":
    case "HVY165":
    case "HVY186":
    case "HVY187":
    case "HVY188":
    case "HVY209":
    case "ROS055":
    case "ROS056":
    case "ROS057":
    case "ROS104":
    case "ROS105":
    case "ROS106":
    case "HNT013":
    case "HNT044":
    case "HNT045":
    case "HNT046":
    case "HNT232":
    case "HNT233":
    case "HNT234":
      $names = GetAbilityNames($cardID, $index, $from);
      if (SearchCurrentTurnEffects("ARC043", $currentPlayer) && GetClassState($currentPlayer, $CS_NumActionsPlayed) >= 1) {
        AddDecisionQueue("SETABILITYTYPEABILITY", $currentPlayer, $cardID);
      } elseif ($names != "" && $from == "HAND") {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose to play the ability or attack");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, $names);
        AddDecisionQueue("SETABILITYTYPE", $currentPlayer, $cardID);
      } else {
        AddDecisionQueue("SETABILITYTYPEATTACK", $currentPlayer, $cardID);
      }
      break;
    case "HVY176":
      AddDecisionQueue("COUNTITEM", $currentPlayer, "DYN243"); //Gold
      AddDecisionQueue("LESSTHANPASS", $currentPlayer, "1");
      AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_pay_1_" . CardLink("DYN243", "DYN243"), 1);
      AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
      if (SearchCharacterAlive($currentPlayer, "HVY051")) {
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYITEMS:isSameName=DYN243&MYCHAR:cardID=HVY051", 1);
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
      } else AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "DYN243-1", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "HVY176-PAID", 1);
      break;
    case "MST131":
      $count = CountAuraAtkCounters($currentPlayer);
      if ($from != "PLAY" && $count >= 3) {
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYAURAS:hasAttackCounters=true");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose an aura to remove a -1 attack counter or pass");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-");
        AddDecisionQueue("MZOP", $currentPlayer, "REMOVEATKCOUNTER", 1);
        for ($i = 0; $i < 2; $i++) {
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYAURAS:hasAttackCounters=true", 1);
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose an aura to remove a -1 attack counter", 1);
          AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("MZOP", $currentPlayer, "REMOVEATKCOUNTER", 1);
        }
        AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
      }
      break;
    case "HER117":
      $char = &GetPlayerCharacter($currentPlayer);
      $numCounters = $char[2];
      $costChoices = "0";
      for ($i = 1; $i <= $numCounters; ++$i) {
        $costChoices .= "," . $i;
      }
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how much you want to pay");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, $costChoices);
      AddDecisionQueue("ADDCURRENTEFFECTLASTRESULT", $currentPlayer, "HER117-", 1);
      AddDecisionQueue("BLAZEPAYCOST", $currentPlayer, "<-", 1);
      break;
    case "ROS120":
    case "ROS169":
      AddDecisionQueue("SETABILITYTYPEABILITY", $currentPlayer, $cardID);
      break;
    case "ROS170":
    case "ROS171":
    case "ROS172":
    case "ROS186":
    case "ROS187":
    case "ROS188":
    case "ROS204":
    case "ROS205":
    case "ROS206":
    case "HNT257":
      $names = GetAbilityNames($cardID, $index, $from);
      if (SearchCurrentTurnEffects("ARC043", $currentPlayer) && GetClassState($currentPlayer, $CS_NumActionsPlayed) >= 1) {
        AddDecisionQueue("SETABILITYTYPEABILITY", $currentPlayer, $cardID);
      } elseif ($names != "" && $from == "HAND"){
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose to play the ability or the action");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, $names);
        AddDecisionQueue("SETABILITYTYPE", $currentPlayer, $cardID);
      } else{
        AddDecisionQueue("SETABILITYTYPEACTION", $currentPlayer, $cardID);
      }
      break;
    case "HNT222":
      $names = GetAbilityNames($cardID, $index, $from);
      if ($names == "Ability,Defense Reaction") {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose to play the defense reaction or ability");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, $names);
        AddDecisionQueue("SETABILITYTYPE", $currentPlayer, $cardID);
      } 
      elseif ($names == "-,Defense Reaction") {
        AddDecisionQueue("SETABILITYTYPEDEFENSEREACTION", $currentPlayer, $cardID);
      }
      else {
        AddDecisionQueue("SETABILITYTYPEABILITY", $currentPlayer, $cardID);
      }
      break;
    case "HNT258":
      $names = GetAbilityNames($cardID, $index, $from);
      if ($names == "Ability,Attack Reaction") {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose to play the attack reaction or ability");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, $names);
        AddDecisionQueue("SETABILITYTYPE", $currentPlayer, $cardID);
      } 
      elseif ($names == "-,Attack Reaction") {
        AddDecisionQueue("SETABILITYTYPEATTACKREACTION", $currentPlayer, $cardID);
      }
      else {
        AddDecisionQueue("SETABILITYTYPEABILITY", $currentPlayer, $cardID);
      }
      break;
    default:
      break;
  }
}

function GetTargetOfAttack($cardID = "")
{
  global $mainPlayer, $combatChainState, $CCS_AttackTarget;
  $defPlayer = $mainPlayer == 1 ? 2 : 1;
  $numTargets = 1;
  $targets = "THEIRCHAR-0";
  if (CanOnlyTargetHeroes($cardID)) {
    $combatChainState[$CCS_AttackTarget] = $targets;
  } else {
    $auras = &GetAuras($defPlayer);
    $arcLightIndex = -1;
    for ($i = 0; $i < count($auras); $i += AuraPieces()) {
      if (HasSpectra($auras[$i])) {
        $targets .= ",THEIRAURAS-" . $i;
        ++$numTargets;
        if ($auras[$i] == "MON005") $arcLightIndex = $i;
      }
    }
    $allies = &GetAllies($defPlayer);
    for ($i = 0; $i < count($allies); $i += AllyPieces()) {
      $targets .= ",THEIRALLY-" . $i;
      ++$numTargets;
    }
    if ($arcLightIndex > -1) $targets = "THEIRAURAS-" . $arcLightIndex;
    if ($numTargets > 1) {
      PrependDecisionQueue("PROCESSATTACKTARGET", $mainPlayer, "-");
      PrependDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, $targets);
      PrependDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a target for the attack");
    } else {
      $combatChainState[$CCS_AttackTarget] = "THEIRCHAR-0";
    }
  }
  AddDecisionQueue("TRUCE", $mainPlayer, "-");
}

function PayAbilityAdditionalCosts($cardID, $index)
{
  global $currentPlayer;
  switch ($cardID) {
    case "MON000":
      for ($i = 0; $i < 2; ++$i) {
        AddDecisionQueue("FINDINDICES", $currentPlayer, "HANDPITCH,2");
        AddDecisionQueue("CHOOSEHANDCANCEL", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-", 1);
        AddDecisionQueue("DISCARDCARD", $currentPlayer, "HAND-" . $currentPlayer, 1);
      }
      break;
    case "HNT003":
    case "HNT004":
    case "HNT005":
    case "HNT006":
    case "HNT007":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "HANDCLASS,ASSASSIN");
      AddDecisionQueue("CHOOSEHAND", $currentPlayer, "<-", 1);
      AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-", 1);
      AddDecisionQueue("DISCARDCARD", $currentPlayer, "HAND-" . $currentPlayer, 1);
      break;
    case "HNT056":
      $character = GetPlayerCharacter($currentPlayer);
      $uniqueID = $character[$index + 11];
      AddCurrentTurnEffect("$cardID-$uniqueID", $currentPlayer);
      break;
    default:
      break;
  }
}

function PayAdditionalCosts($cardID, $from, $index="-")
{
  global $currentPlayer, $CS_AdditionalCosts, $CS_CharacterIndex, $CS_PlayIndex, $CombatChain, $CS_NumBluePlayed, $combatChain, $combatChainState, $CCS_LinkBaseAttack;
  $cardSubtype = CardSubType($cardID);
  if ($from == "PLAY" && DelimStringContains($cardSubtype, "Item")) {
    PayItemAbilityAdditionalCosts($cardID, $from);
    return;
  } else if ($from == "PLAY" && DelimStringContains($cardSubtype, "Aura")) {
    PayAuraAbilityAdditionalCosts($cardID, $from);
    return;
  } else if ($from == "EQUIP") {
    switch ($cardID) {
      case "EVO434":
      case "EVO435":
      case "EVO436":
      case "EVO437":
      case "EVO446":
      case "EVO447":
      case "EVO448":
      case "EVO449":
        CharacterChooseSubcard($currentPlayer, GetClassState($currentPlayer, $CS_PlayIndex), fromDQ: true);
        AddDecisionQueue("ADDDISCARD", $currentPlayer, "-", 1);
        break;
      default:
        break;
    }
  }
  if (HasBoost($cardID, $currentPlayer) && $cardID != "EVO142") Boost($cardID);
  $fuseType = HasFusion($cardID);
  if ($fuseType != "") Fuse($cardID, $currentPlayer, $fuseType);
  if (HasScrap($cardID)) Scrap($currentPlayer);
  if (RequiresDiscard($cardID)) {
    $discarded = DiscardRandom($currentPlayer, $cardID);
    if ($discarded == "") {
      WriteLog("You do not have a card to discard. Reverting gamestate.", highlight: true);
      RevertGamestate();
      return;
    }
    SetClassState($currentPlayer, $CS_AdditionalCosts, $discarded);
  }
  if (RequiresBanish($cardID)) {
    $banished = BanishRandom($currentPlayer, $cardID);
    if ($banished == "") {
      WriteLog("You do not have a card to banish. Reverting gamestate.", highlight: true);
      RevertGamestate();
      return;
    }
    SetClassState($currentPlayer, $CS_AdditionalCosts, $banished);
  }
  if (HasBeatChest($cardID)) {
    AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to beat chest");
    AddDecisionQueue("FINDINDICES", $currentPlayer, "HANDMINPOWER,6");
    AddDecisionQueue("MAYCHOOSEHAND", $currentPlayer, "<-", 1);
    AddDecisionQueue("REMOVEMYHAND", $currentPlayer, "-", 1);
    AddDecisionQueue("DISCARDCARD", $currentPlayer, "HAND-" . $cardID, 1);
    if (!SearchCurrentTurnEffects("BEATCHEST", $currentPlayer)) { //Don't duplicate the effect icon
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "BEATCHEST", 1);
    }
  }
  switch ($cardID) {
    case "WTR159":
      $hand = &GetHand($currentPlayer);
      if (count($hand) == 0) {
        WriteLog("You do not have a card to sink. Reverting gamestate.", highlight: true);
        RevertGamestate();
        return;
      }
      BottomDeck();
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a mode");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Draw_a_Card,Buff_Power,Go_Again");
      AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      break;
    case "WTR179":
    case "WTR180":
    case "WTR181":
      $indices = SearchHand($currentPlayer, "", "", -1, 2);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to reveal");
      AddDecisionQueue("CHOOSEHANDCANCEL", $currentPlayer, $indices);
      AddDecisionQueue("REVEALHANDCARDS", $currentPlayer, "-");
      break;
    case "WTR182":
    case "WTR183":
    case "WTR184":
      $indices = SearchHand($currentPlayer, "", "", 1, 0);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to reveal");
      AddDecisionQueue("CHOOSEHANDCANCEL", $currentPlayer, $indices);
      AddDecisionQueue("REVEALHANDCARDS", $currentPlayer, "-");
      break;
    case "WTR185":
    case "WTR186":
    case "WTR187":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:cardID=WTR218;cardID=WTR219;cardID=WTR220");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZADDZONE", $currentPlayer, "MYBANISH,GY,-", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
      AddDecisionQueue("OP", $currentPlayer, "GIVEATTACKGOAGAIN", 1);
      break;
    case "WTR197":
    case "WTR198":
    case "WTR199":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:cardID=WTR221;cardID=WTR222;cardID=WTR223");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZADDZONE", $currentPlayer, "MYBANISH,GY,-", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
      break;
    case "ARC003":
      $abilityType = GetResolvedAbilityType($cardID);
      if ($abilityType == "AA") {
        $character = &GetPlayerCharacter($currentPlayer);
        $index = GetClassState($currentPlayer, $CS_CharacterIndex);
        $character[$index + 2] = 0;
      }
      break;
    case "ARC041":
      if (ArsenalHasFaceDownCard($currentPlayer)) {
        SetArsenalFacing("UP", $currentPlayer);
      }
      break;
    case "ARC122":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose 2 modes");
      AddDecisionQueue("MULTICHOOSETEXT", $currentPlayer, "2-Buff_Arcane,Buff_Arcane,Draw_card,Draw_card-2");
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      break;
    case "ARC160":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose 2 modes");
      AddDecisionQueue("MULTICHOOSETEXT", $currentPlayer, "2-Buff_your_attack_action_cards_this_turn,Your_next_attack_action_card_gains_go_again,Defend_with_attack_action_cards_from_arsenal,Banish_an_attack_action_card_to_draw_2_cards-2");
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      break;
    case "CRU097":
      $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
      $otherCharacter = &GetPlayerCharacter($otherPlayer);
      if (SearchCurrentTurnEffects($otherCharacter[0] . "-SHIYANA", $currentPlayer)) {
        PayAdditionalCosts($otherCharacter[0], $from);
      }
      break;
    case "CRU101":
      $abilityType = GetResolvedAbilityType($cardID);
      if ($abilityType == "AA") {
        $character = &GetPlayerCharacter($currentPlayer);
        $index = GetClassState($currentPlayer, $CS_CharacterIndex);
        $character[$index + 2] = 0;
      }
      break;
    case "MON001":
    case "MON002":
      BanishFromSoul($currentPlayer);
      break;
    case "MON029":
    case "MON030":
      BanishFromSoul($currentPlayer);
      break;
    case "MON033":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "SOULINDICES");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many cards to banish from your soul");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "GETINDICES,", 1);
      AddDecisionQueue("FINDINDICES", $currentPlayer, "<-", 1);
      AddDecisionQueue("MULTIBANISHSOUL", $currentPlayer, "-", 1);
      break;
    case "MON035":
      AddDecisionQueue("VOFTHEVANGUARD", $currentPlayer, "-");
      break;
    case "MON042":
    case "MON043":
    case "MON044":
    case "MON045":
    case "MON046":
    case "MON047":
    case "MON048":
    case "MON049":
    case "MON050":
    case "MON051":
    case "MON052":
    case "MON053":
    case "MON054":
    case "MON055":
    case "MON056":
      Charge();
      break;
    case "MON062":
      BanishFromSoul($currentPlayer);
      BanishFromSoul($currentPlayer);
      BanishFromSoul($currentPlayer);
      break;
    case "MST105":
      if ($combatChainState[$CCS_LinkBaseAttack] <= 1 && CardType($CombatChain->AttackCard()->ID()) == "AA" && HasStealth($combatChain[0])) $modalities = "Buff_Power,Gain_On-Hit,Both";
      elseif ($combatChainState[$CCS_LinkBaseAttack] <= 1 && CardType($CombatChain->AttackCard()->ID()) == "AA") $modalities = "Buff_Power";
      else $modalities = "Gain_On-Hit";
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a mode");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, $modalities);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      break;
    case "MON126":
    case "MON127":
    case "MON128":
    case "MON129":
    case "MON130":
    case "MON131":
    case "MON132":
    case "MON133":
    case "MON134":
    case "MON141":
    case "MON142":
    case "MON143":
      if (RandomBanish3GY($cardID) > 0) AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "MON135":
    case "MON136":
    case "MON137":
    case "MON147":
    case "MON148":
    case "MON149":
    case "MON150":
    case "MON151":
    case "MON152":
      RandomBanish3GY($cardID);
      break;
    case "MON156":
      MZMoveCard($currentPlayer, "MYHAND:bloodDebtOnly=true", "MYBANISH,HAND,-", may: true);
      AddDecisionQueue("OP", $currentPlayer, "GIVEATTACKGOAGAIN", 1);
      break;
    case "MST162":
      if ($combatChainState[$CCS_LinkBaseAttack] <= 1 && CardNameContains($combatChain[0], "Crouching Tiger", $currentPlayer)) $modalities = "Buff_Power,Gain_On-Hit,Both";
      elseif ($combatChainState[$CCS_LinkBaseAttack] <= 1 && CardType($combatChain[0]) == "AA") $modalities = "Buff_Power";
      else $modalities = "Gain_On-Hit";
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a mode");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, $modalities);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      break;
    case "MON195":
    case "MON196":
    case "MON197":
      MZMoveCard($currentPlayer, "MYHAND", "MYBANISH,HAND,-", may: true);
      AddDecisionQueue("ALLCARDTALENTORPASS", $currentPlayer, "SHADOW", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
      break;
    case "MON198":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "GY");
      AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "6-", 1);
      AddDecisionQueue("MULTICHOOSEDISCARD", $currentPlayer, "<-", 1, 1);
      AddDecisionQueue("VALIDATECOUNT", $currentPlayer, "6", 1);
      AddDecisionQueue("SOULHARVEST", $currentPlayer, "-", 1);
      AddDecisionQueue("MULTIREMOVEDISCARD", $currentPlayer, "-", 1);
      AddDecisionQueue("MULTIBANISH", $currentPlayer, "GY,-", 1);
      break;
    case "MON247":
      if (CanRevealCards($currentPlayer)) {
        AddDecisionQueue("FINDINDICES", $currentPlayer, "MULTIHANDAA");
        AddDecisionQueue("LESSTHANPASS", $currentPlayer, "1", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which cards to reveal", 1);
        AddDecisionQueue("MAYMULTICHOOSEHAND", $currentPlayer, "<-", 1);
        AddDecisionQueue("REVEALHANDCARDS", $currentPlayer, "-", 1);
        AddDecisionQueue("ROUSETHEANCIENTS", $currentPlayer, "-", 1);
      }
      break;
    case "MON251":
    case "MON252":
    case "MON253":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYHAND");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which cards to put on top of your deck (or pass)", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZADDZONE", $currentPlayer, "MYTOPDECK", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      break;
    case "MON260":
    case "MON261":
    case "MON262":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a mode");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Buff_Power,Go_Again");
      AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      break;
    case "MON266":
    case "MON267":
    case "MON268":
      if (CanRevealCards($currentPlayer)) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to reveal for Belittle");
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYHAND:type=AA;maxAttack=3");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZOP", $currentPlayer, "GETCARDID", 1);
        AddDecisionQueue("REVEALCARDS", $currentPlayer, "-", 1);
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "BELITTLE", 1);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
      }
      break;
    case "MON281":
    case "MON282":
    case "MON283":
      if ($from == "PLAY") {
        $hand = &GetHand($currentPlayer);
        if (count($hand) == 0) {
          WriteLog("This ability requires a discard as an additional cost, but you have no cards to discard. Reverting gamestate prior to the card declaration.", highlight: true);
          RevertGamestate();
        }
        $index = GetClassState($currentPlayer, $CS_PlayIndex);
        AddCurrentTurnEffect($cardID, $currentPlayer, "CC", $combatChain[$index + 7]);
        MZMoveCard($currentPlayer, "MYHAND", "MYDISCARD," . $currentPlayer, silent: true);
      }
      break;
    case "ELE031":
    case "ELE032":
      if (ArsenalHasFaceDownCard($currentPlayer)) {
        $cardFlipped = SetArsenalFacing("UP", $currentPlayer);
        AddAdditionalCost($currentPlayer, TalentOverride($cardFlipped, $currentPlayer));
        WriteLog("Lexi turns " . CardLink($cardFlipped, $cardFlipped) . " face up.");
      }
      break;
    case "ELE115":
      FaceDownArsenalBotDeck($currentPlayer);
      break;
    case "ELE116":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:type=I;talent=EARTH&MYDISCARD:type=A;talent=EARTH&MYDISCARD:type=AA;talent=EARTH");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose Earth action card or Earth instant card");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "ELE118":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "ARSENAL");
      AddDecisionQueue("CHOOSEARSENAL", $currentPlayer, "<-", 1);
      AddDecisionQueue("REMOVEARSENAL", $currentPlayer, "-", 1);
      AddDecisionQueue("ADDBOTDECK", $currentPlayer, "-", 1);
      break;
    case "ELE234":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "HAND");
      AddDecisionQueue("CHOOSEHANDCANCEL", $currentPlayer, "<-", 1);
      AddDecisionQueue("MULTIREMOVEHAND", $currentPlayer, "-", 1);
      AddDecisionQueue("ADDBOTDECK", $currentPlayer, "-", 1);
      break;
    case "EVR047":
    case "EVR048":
    case "EVR049":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a mode");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Hit_Effect,1_Attack");
      AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      break;
    case "EVR055":
      $numCopper = CountItem("CRU197", $currentPlayer);
      if ($numCopper == 0) return "No copper.";
      if ($numCopper > 6) $numCopper = 6;
      $buttons = "";
      for ($i = 0; $i <= $numCopper; ++$i) {
        if ($buttons != "") $buttons .= ",";
        $buttons .= $i;
      }
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many Copper to destroy");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, $buttons);
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
      AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "CRU197-", 1);
      AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "<-", 1);
      AddDecisionQueue("LASTRESULTPIECE", $currentPlayer, "1", 1);
      AddDecisionQueue("APPENDLASTRESULT", $currentPlayer, "-Buff_Weapon,Buff_Weapon,Go_Again,Go_Again,Attack_Twice,Attack_Twice", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose {0} modes");
      AddDecisionQueue("MULTICHOOSETEXT", $currentPlayer, "<-", 1);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      break;
    case "EVR158":
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, "0");
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
      AddDecisionQueue("FINDINDICES", $currentPlayer, "CASHOUT");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, 1, 1);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "CASHOUTCONTINUE", 1);
      break;
    case "EVR159":
      $numCopper = CountItem("CRU197", $currentPlayer);
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, "0");
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
      if ($numCopper > 0) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many Copper to pay");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, GetIndices($numCopper + 1));
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "CRU197-");
        AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "<-");
        AddDecisionQueue("LASTRESULTPIECE", $currentPlayer, "1", 1);
        AddDecisionQueue("DIVIDE", $currentPlayer, "4");
        AddDecisionQueue("INCDQVAR", $currentPlayer, "0");
      }
      $numSilver = CountItem("EVR195", $currentPlayer);
      if ($numSilver > 0) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many Silver to pay");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, GetIndices($numSilver + 1));
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "EVR195-");
        AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "<-");
        AddDecisionQueue("LASTRESULTPIECE", $currentPlayer, "1", 1);
        AddDecisionQueue("DIVIDE", $currentPlayer, "2");
        AddDecisionQueue("INCDQVAR", $currentPlayer, "0");
      }
      $numGold = CountItem("DYN243", $currentPlayer);
      if ($numGold > 0) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many Gold to pay");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, GetIndices($numGold + 1));
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "DYN243-");
        AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "<-");
        AddDecisionQueue("LASTRESULTPIECE", $currentPlayer, "1", 1);
        AddDecisionQueue("INCDQVAR", $currentPlayer, "0");
      }
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, "{0}");
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts);
      break;
    case "UPR094":
      MZMoveCard($currentPlayer, "MYDISCARD:isSameName=UPR101", "MYBANISH,GY,-", may: true);
      AddDecisionQueue("APPENDCLASSSTATE", $currentPlayer, $CS_AdditionalCosts . "-PHOENIXBANISH", 1);
      break;
    case "OUT001":
    case "OUT002":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYHAND");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to banish with Uzuri", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZBANISH", $currentPlayer, "HAND,UZURI," . $currentPlayer . ",1", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      break;
    case "OUT094":
      FaceDownArsenalBotDeck($currentPlayer);
      break;
    case "OUT139":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:subtype=Dagger&COMBATCHAINATTACKS:subtype=Dagger;type=AA");
      AddDecisionQueue("REMOVEINDICESIFACTIVECHAINLINK", $currentPlayer, "<-", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, "OUT139", 1);
      break;
    case "OUT148":
    case "OUT149":
    case "OUT150":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how much to pay for " . CardLink($cardID, $cardID));
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, "0,1");
      AddDecisionQueue("PAYRESOURCES", $currentPlayer, "<-", 1);
      AddDecisionQueue("LESSTHANPASS", $currentPlayer, "1", 1);
      AddDecisionQueue("APPENDCLASSSTATE", $currentPlayer, $CS_AdditionalCosts . "-PAY1", 1);
      AddDecisionQueue("WRITELOG", $currentPlayer, "Paid extra resource to throw a dagger", 1);
      break;
    case "OUT157":
      BottomDeckMultizone($currentPlayer, "MYHAND", "MYARS", true, "Choose a card from your hand or arsenal to add on the bottom of your deck");
      break;
    case "OUT195":
    case "OUT196":
    case "OUT197":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:maxAttack=1;minAttack=1");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to banish", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZBANISH", $currentPlayer, "GY,-," . $currentPlayer . ",1", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      AddDecisionQueue("APPENDCLASSSTATE", $currentPlayer, $CS_AdditionalCosts . "-BANISH1ATTACK", 1);
      break;
    case "DTD051":
    case "DTD052":
    case "DTD057":
    case "DTD058":
    case "DTD059":
    case "DTD063":
    case "DTD064":
    case "DTD065":
    case "DTD066":
    case "DTD067":
    case "DTD068":
      Charge();
      AddDecisionQueue("ALLCARDPITCHORPASS", $currentPlayer, "2", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
      break;
    case "DTD080":
      $soul = &GetSoul($currentPlayer);
      if (!TalentContains($CombatChain->AttackCard()->ID(), "LIGHT", $currentPlayer)) break;
      $numModes = count($soul) / SoulPieces() < 3 ? count($soul) / SoulPieces() : 3;
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose up to $numModes modes");
      AddDecisionQueue("MULTICHOOSETEXT", $currentPlayer, "$numModes-+2_Attack,Draw_on_hit,Go_again_on_hit");
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      break;
    case "DTD110":
      $banish = &GetBanish($currentPlayer);
      $index = count($banish) - BanishPieces();
      if (ModifiedAttackValue($banish[$index], $currentPlayer, "BANISH") >= 6) $banish[$index + 1] = "NT";
      break;
    case "DTD111":
      $banishedCards = BanishHand($currentPlayer);
      SetClassState($currentPlayer, $CS_AdditionalCosts, $banishedCards);
      break;
    case "EVO101":
      Scrap($currentPlayer);
      break;
    case "EVO140":
      global $CS_DynCostResolved;
      $xVal = GetClassState($currentPlayer, $CS_DynCostResolved) / 2;
      if (SearchCount(SearchMultizone($currentPlayer, "MYITEMS:isSameName=ARC036")) < $xVal) {
        WriteLog("You do not have enough Hyper Drivers. Reverting gamestate.", highlight: true);
        RevertGamestate();
        return;
      }
      for ($i = 0; $i < $xVal; ++$i) MZChooseAndDestroy($currentPlayer, "MYITEMS:isSameName=ARC036");
      break;
    case "EVO142":
      $amountBoostChoices = "0,1,2";
      if (SearchCurrentTurnEffects("MST231", $currentPlayer)) $amountBoostChoices = "0,1,2,3";
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many times you want to activate boost on " . CardLink($cardID, $cardID));
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, $amountBoostChoices);
      AddDecisionQueue("OP", $currentPlayer, "BOOST-" . $cardID, 1);
      break;
    case "EVO146":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose 2;");
      AddDecisionQueue("MULTICHOOSETEXT", $currentPlayer, "2-Equip_Proto_equipment,Evo_permanents_get_+1_block,Put_this_under_an_Evo_permanent,Banish_an_Evo_and_draw_a_card-2");
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      break;
    case "EVO410":
    case "DYN492a":
      if ($from == "EQUIP") {
        $character = &GetPlayerCharacter($currentPlayer);
        $index = GetClassState($currentPlayer, $CS_CharacterIndex);
        CharacterChooseSubcard($currentPlayer, $index, count: $cardID == "EVO410" ? 2 : 1);
        AddDecisionQueue("MULTIBANISH", $currentPlayer, "EQUIP,-", 1);
      }
      break;
    case "HVY016":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "HVY016");
      AddDecisionQueue("LESSTHANPASS", $currentPlayer, "1", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which cards to banish", 1);
      AddDecisionQueue("MAYMULTICHOOSEHAND", $currentPlayer, "<-", 1);
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "NOFEAR", 1);
      break;
    case "HVY059":
      if (CountItem("DYN243", $currentPlayer) > 0) {
        AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_pay_the_additional_cost_of_1_" . CardLink("DYN243", "DYN243"), 1);
        AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
        if (SearchCharacterAlive($currentPlayer, "HVY051")) {
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYITEMS:isSameName=DYN243&MYCHAR:cardID=HVY051", 1);
          AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
        } else AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "DYN243-1", 1);
        AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      }
      break;
    case "HVY090":
    case "HVY091":
      if ($from == "EQUIP") {
        MZMoveCard($currentPlayer, "MYDISCARD:pitch=1", "MYBANISH,GY,-");
        MZMoveCard($currentPlayer, "MYDISCARD:pitch=1", "MYBANISH,GY,-");
        MZMoveCard($currentPlayer, "MYDISCARD:pitch=2", "MYBANISH,GY,-");
        MZMoveCard($currentPlayer, "MYDISCARD:pitch=2", "MYBANISH,GY,-");
      }
      break;
    case "HVY099":
      MZMoveCard($currentPlayer, "MYDISCARD:pitch=1", "MYBANISH,GY");
      MZMoveCard($currentPlayer, "MYDISCARD:pitch=2", "MYBANISH,GY");
      break;
    case "HVY103":
      global $CS_LastDynCost;
      $dynCost = GetClassState($currentPlayer, $CS_LastDynCost);
      $max = $dynCost > 4 ? 4 : $dynCost + 1;
      $modalities = "Wager_Agility,Wager_Gold,Wager_Vigor,Buff_Attack";
      if ($max < 4) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, $max == 1 ? "Choose 1 mode" : "Choose " . $max . " modes");
        AddDecisionQueue("MULTICHOOSETEXT", $currentPlayer, $max . "-" . $modalities . "-" . $max);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
        AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      } else {
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, $modalities);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts);
        AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID);
      }
      break;
    case "HVY105":
      $numGold = CountItem("DYN243", $currentPlayer);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many Gold to pay");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, GetIndices($numGold + 1));
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SETDQVAR", $currentPlayer, 0, 1);
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "RAISEANARMY", 1);
      break;
    case "HVY245":
      if ($from == "GY") {
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "EVR195-2", 1);
        AddDecisionQueue("FINDANDDESTROYITEM", $currentPlayer, "<-", 1);
        $index = SearchGetFirstIndex(SearchMultizone($currentPlayer, "MYDISCARD:cardID=HVY245"));
        RemoveGraveyard($currentPlayer, $index);
      }
      break;
    case "MST010":
      $modalities = "Create_a_Fang_Strike_and_Slither,Banish_up_to_2_cards_in_an_opposing_hero_graveyard,Transcend";
      $numModes = GetClassState($currentPlayer, $CS_NumBluePlayed) > 1 ? 3 : 1;
      if ($numModes == 1) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose 1 mode");
        AddDecisionQueue("MULTICHOOSETEXT", $currentPlayer, "$numModes-" . $modalities);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
        AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      } else {
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, $modalities);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts);
        AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID);
      }
      break;
    case "MST032":
      $modalities = "Create_2_Spectral_Shield,Put_a_+1_counter_on_each_aura_with_ward_you_control,Transcend";
      $numModes = GetClassState($currentPlayer, $CS_NumBluePlayed) > 1 ? 3 : 1;
      if ($numModes == 1) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose 1 mode");
        AddDecisionQueue("MULTICHOOSETEXT", $currentPlayer, "$numModes-" . $modalities);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
        AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      } else {
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, $modalities);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts);
        AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID);
      }
      break;
    case "MST053":
      $modalities = "Create_2_Crouching_Tigers,Crouching_Tigers_Get_+1_this_turn,Transcend";
      $numModes = GetClassState($currentPlayer, $CS_NumBluePlayed) > 1 ? 3 : 1;
      if ($numModes == 1) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose 1 mode");
        AddDecisionQueue("MULTICHOOSETEXT", $currentPlayer, "$numModes-" . $modalities);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
        AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      } else {
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, $modalities);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts);
        AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID);
      }
      break;
    case "MST197":
    case "MST198":
    case "MST199":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYHAND");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which cards to put on the bottom of your deck (or pass)", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZADDZONE", $currentPlayer, "MYBOTDECK", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      break;
    case "MST232":
      $myHand = &GetHand($currentPlayer);
      $myArsenal = &GetArsenal($currentPlayer);
      if(count($myHand) + count($myArsenal) < 2) {
        WriteLog("No card in hand/arsenal to pay the cost of " . CardLink($cardID, $cardID) . ". Reverting the gamestate.", highlight:true);
        RevertGamestate();
      }
      MZMoveCard($currentPlayer, "MYHAND&MYARS", "MYBOTDECK", silent: true);
      MZMoveCard($currentPlayer, "MYHAND&MYARS", "MYBOTDECK", silent: true);
      break;
    case "MST226":
      $numGold = CountItem("DYN243", $currentPlayer);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many Gold to pay");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, GetIndices($numGold + 1));
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SETDQVAR", $currentPlayer, 0, 1);
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "GOLDENANVIL", 1);
      break;
    case "MST236":
      $num6Banished = RandomBanish3GY($cardID, $cardID);
      if ($num6Banished > 0) AddCurrentTurnEffect($cardID . "-1", $currentPlayer);
      if ($num6Banished > 1) AddCurrentTurnEffect($cardID . "-2", $currentPlayer);
      if ($num6Banished > 2) AddCurrentTurnEffect($cardID . "-3", $currentPlayer);
      break;
    case "ASB025":
      Charge();
      break;
    case "AAZ005":
      if (ArsenalHasFaceDownCard($currentPlayer)) {
        SetArsenalFacing("UP", $currentPlayer);
      }
      break;
    case "ROS019":
    case "ROS020":
      if(SearchCount(SearchMultiZone($currentPlayer, "MYHAND:type=I")) == 0) {
        WriteLog("No instant card in hand pay the discard cost of " . CardLink($cardID, $cardID) . ". Reverting the gamestate.", highlight:true);
        RevertGamestate();
      }
      $index = GetClassState($currentPlayer, $CS_PlayIndex);
      MZMoveCard($currentPlayer, "MYHAND:type=I", "MYDISCARD," . $currentPlayer);
      break;
    case "ROS035":
      AddDecisionQueue("FINDINDICES", $currentPlayer, "ARSENAL");
      AddDecisionQueue("CHOOSEARSENAL", $currentPlayer, "<-", 1);
      AddDecisionQueue("REMOVEARSENAL", $currentPlayer, "-", 1);
      AddDecisionQueue("ADDBOTDECK", $currentPlayer, "-", 1);
      break;
    case "HNT015":
      if (SubtypeContains($combatChain[0], "Dagger") && HasStealth($combatChain[0]) && NumCardsBlocking() > 0) $modalities = "Buff_Power,Reduce_Block,Both";
      elseif (SubtypeContains($combatChain[0], "Dagger")) $modalities = "Buff_Power";
      else $modalities = "Reduce_Block";
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a mode");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, $modalities);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      break;
    case "HNT051":
      if (SubtypeContains($combatChain[0], "Dagger", $currentPlayer) && HasStealth($combatChain[0]) && TypeContains($combatChain[0], "AA", $currentPlayer)) $modalities = "Buff_Dagger,Buff_Stealth";
      elseif (SubtypeContains($combatChain[0], "Dagger", $currentPlayer)) $modalities = "Buff_Dagger";
      elseif (TypeContains($combatChain[0], "AA", $currentPlayer) && HasStealth($combatChain[0])) $modalities = "Buff_Stealth";
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a mode");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, $modalities);
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      break;
    case "HNT102":
      if (SubtypeContains($combatChain[0], "Dagger")) $modalities = "Buff_Power,Additional_Attack,Mark";
      else $modalities = "Additional_Attack,Mark";
      $numModes = min(count(explode(",", $modalities)), NumDraconicChainLinks());
      if ($numModes > 0) {
        if ($numModes < 3) {
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, $numModes == 1 ? "Choose 1 mode" : "Choose " . $numModes . " modes");
          AddDecisionQueue("MULTICHOOSETEXT", $currentPlayer, $numModes . "-" . $modalities . "-" . $numModes);
          AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
          AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
        } else {
          AddDecisionQueue("PASSPARAMETER", $currentPlayer, $modalities);
          AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts);
          AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID);
        }
      }
      break;
    case "HNT173":
    case "HNT175":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:subtype=Dagger&COMBATCHAINATTACKS:subtype=Dagger;type=AA");
      AddDecisionQueue("REMOVEINDICESIFACTIVECHAINLINK", $currentPlayer, "<-", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
      AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      break;
    case "HNT257":
      if (GetResolvedAbilityType($cardID, $from) == "I")   
      {
        AddDecisionQueue("FINDINDICES", $currentPlayer, "SOULINDICES");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many cards to banish from your soul");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, "<-", 1);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "GETINDICES,", 1);
        AddDecisionQueue("FINDINDICES", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIBANISHSOUL", $currentPlayer, "-", 1);
      }
      break;
    case "HNT258":
      if (GetResolvedAbilityType($cardID, $from) == "I")   
      {
        AddDecisionQueue("FINDINDICES", $currentPlayer, "SOULINDICES");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose how many cards to banish from your soul");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, "<-", 1);
        AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "GETINDICES,", 1);
        AddDecisionQueue("FINDINDICES", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIBANISHSOUL", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRCHAR:type=W&THEIRAURAS:type=W", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a target weapon", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("SHOWSELECTEDTARGET", $currentPlayer, "-", 1);
        AddDecisionQueue("SETLAYERTARGET", $currentPlayer, $cardID, 1);
      }  
      break;
    default:
      break;
  }
  $ID = CardIdentifier($cardID);
  switch($ID) {
    case "skyward-serenade-2":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose 2;");
      AddDecisionQueue("MULTICHOOSETEXT", $currentPlayer, "2-create_embodiment_of_lightning,search_for_skyzyk,buff_next_attack-2");
      AddDecisionQueue("SETCLASSSTATE", $currentPlayer, $CS_AdditionalCosts, 1);
      AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      break;
    default:
      break;
  }
}

function PlayCardEffect($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "-", $uniqueID = "-1", $layerIndex = -1)
{
  global $turn, $combatChain, $currentPlayer, $mainPlayer, $defPlayer, $combatChainState, $CCS_AttackPlayedFrom, $CS_PlayIndex;
  global $CS_CharacterIndex, $CS_PlayCCIndex, $CCS_LinkBaseAttack;
  global $CCS_WeaponIndex, $EffectContext, $CCS_AttackFused, $CCS_AttackUniqueID, $CS_NumLess3PowAAPlayed, $layers;
  global $CS_NumDragonAttacks, $CS_NumAttackCards, $CS_NumIllusionistAttacks, $CS_NumIllusionistActionCardAttacks;
  global $SET_PassDRStep, $CS_NumBlueDefended, $CS_AdditionalCosts, $CombatChain, $CS_TunicTicks, $CS_NumTimesAttacked;
  global $currentTurnEffects;

  $otherPlayer = $currentPlayer == 1 ? 2 : 1;
  if ($additionalCosts == "-" || $additionalCosts == "") $additionalCosts = GetClassState($currentPlayer, $CS_AdditionalCosts);
  if ($layerIndex > -1) SetClassState($currentPlayer, $CS_PlayIndex, $layerIndex);
  $index = SearchForUniqueID($uniqueID, $currentPlayer);
  if ($cardID == "ARC003" || $cardID == "CRU101") $index = FindCharacterIndex($currentPlayer, $cardID);
  if ($currentPlayer == $mainPlayer && CardClass($cardID) == "MECHANOLOGIST" && CardType($cardID) == "AA") {
    $index = FindCharacterIndex($currentPlayer, "EVO410");
    if ($index != -1) {
      GiveAttackGoAgain();
      WriteLog(CardLink("EVO410", "EVO410") . " grants the attack go again.");
    }
  }
  if ($index > -1) SetClassState($currentPlayer, $CS_PlayIndex, $index);
  $definedCardType = CardType($cardID);
  $definedCardSubType = CardSubType($cardID);
  //Figure out where it goes
  $openedChain = false;
  $chainClosed = false;
  $skipDRResolution = false;
  $isSpectraTarget = HasSpectra(GetMzCard($currentPlayer, GetAttackTarget()));
  $isBlock = ($turn[0] == "B" && count($layers) == 0); //This can change over the course of the function; for example if a phantasm gets popped
  if(canBeAddedToChainDuringDR($cardID) && $turn[0] == "D") $isBlock = true;
  if(GoesOnCombatChain($turn[0], $cardID, $from, $currentPlayer)) {
    if ($from == "PLAY" && $uniqueID != "-1" && $index == -1 && count($combatChain) == 0 && !DelimStringContains(CardSubType($cardID), "Item")) {
      WriteLog(CardLink($cardID, $cardID) . " does not resolve because it is no longer in play.");
      return;
    }
    if ((in_array("FINALIZECHAINLINK", $layers) || count($combatChain) == 0) && (DelimStringContains($definedCardType, "DR") || DelimStringContains($definedCardType, "AR"))) {
      WriteLog(CardLink($cardID, $cardID) . " does not resolve because the combat chain closed.");
      AddGraveyard($cardID, $currentPlayer, $from, $currentPlayer);
      ContinueDecisionQueue();
      return;
    }
    if ($definedCardType == "DR" && $from == "HAND" && CachedDominateActive() && CachedNumDefendedFromHand() >= 1 && NumDefendedFromHand() >= 1) {
      $discard = new Discard($currentPlayer);
      $discard->Add($cardID, "LAYER");
      WriteLog(CardLink($cardID, $cardID) . " fail to resolve because dominate is active and there is already a card defending from hand.");
      $skipDRResolution = true;
    }
    if (!$isBlock && CardType($cardID) == "AR") {
      if (substr($from, 0, 5) == "THEIR") AddGraveyard($cardID, $otherPlayer, $from, $currentPlayer);
      else AddGraveyard($cardID, $currentPlayer, $from, $currentPlayer);
      if ($target != "-") {
        $missingTarget = false;
        switch ($cardID) {
          case "MST105":
            $targetUID = explode("-", $target)[1];
            if ($CombatChain->AttackCard()->UniqueID() != $targetUID) $missingTarget = true;
            break;
          default:
            break;
        }
        if ($missingTarget) {
          WriteLog(CardLink($cardID, $cardID) . " fails to resolve because the target is gone.");
          return;
        }
      }
      if (IsPlayRestricted($cardID, $restriction, $from, resolutionCheck: true) && $additionalCosts == "-") {
        WriteLog(CardLink($cardID, $cardID) . " fail to resolve because the target is no longer a legal target.");
        return;
      }
    }
    if ($resourcesPaid != "Skipped") {
      switch ($cardID) {
        case "OUT139":
        case "HNT173":
        case "HNT175":
          break;
        default:
          $target = GetMzCard($currentPlayer, GetAttackTarget());
          break;
      }
    }
    if (!$skipDRResolution && !$isSpectraTarget && $target != "") {
      $index = AddCombatChain($cardID, $currentPlayer, $from, $resourcesPaid, $uniqueID);
      if ($index == 0) {//if adding an attacking card
        for ($i = count(value: $currentTurnEffects) - CurrentTurnEffectPieces(); $i >= 0; $i -= CurrentTurnEffectPieces()) {
          if (IsCombatEffectActive($currentTurnEffects[$i]) && !IsCombatEffectLimited($i) && IsLayerContinuousBuff($currentTurnEffects[$i]) && $currentTurnEffects[$i + 1] == $mainPlayer) {
            if ($combatChain[10] == "-") $combatChain[10] = $currentTurnEffects[$i];
            else $combatChain[10] .= "," . $currentTurnEffects[$i];
            RemoveCurrentTurnEffect($i);
          }
        }
      }
    }
    if ($isSpectraTarget) {
      $goesWhere = GoesWhereAfterResolving($cardID, $from, $currentPlayer, additionalCosts: $additionalCosts);
      if(CardType($cardID) != "T" && CardType($cardID) != "Macro") { //Don't need to add to anywhere if it's a token
        ResolveGoesWhere($goesWhere, $cardID, $currentPlayer, $from);
      }
    }
    if ($index <= 0 && !$skipDRResolution || $isSpectraTarget) {
      ChangeSetting($defPlayer, $SET_PassDRStep, 0);
      $combatChainState[$CCS_AttackPlayedFrom] = $from;
      $chainClosed = ProcessAttackTarget();
      $baseAttackSet = CurrentEffectBaseAttackSet();
      if($baseAttackSet != -1) {
        $attackValue = $baseAttackSet;
      }
      else {
        if(TypeContains( $cardID, "W", $currentPlayer)) $attackValue = GeneratedAttackValue($cardID);
        else $attackValue = AttackValue($cardID);
      }
      if (EffectAttackRestricted($cardID, $definedCardType, $from, true)) return;
      $combatChainState[$CCS_LinkBaseAttack] = BaseAttackModifiers($cardID, $attackValue);
      $combatChainState[$CCS_AttackUniqueID] = $uniqueID;
      if ($definedCardType == "AA" && $attackValue < 3) IncrementClassState($currentPlayer, $CS_NumLess3PowAAPlayed);
      if ($definedCardType == "AA" && (GetResolvedAbilityType($cardID) == "" || GetResolvedAbilityType($cardID) == "AA") && (SearchCharacterActive($currentPlayer, "CRU002") || (SearchCharacterActive($currentPlayer, "CRU097") && SearchCurrentTurnEffects("CRU002-SHIYANA", $currentPlayer))) && $attackValue >= 6) KayoStaticAbility($cardID);
      $openedChain = true;
      if ($definedCardType != "AA") $combatChainState[$CCS_WeaponIndex] = GetClassState($currentPlayer, $CS_PlayIndex);
      if ($additionalCosts != "-" && HasFusion($cardID)) $combatChainState[$CCS_AttackFused] = 1;
      //Add attack step layer prior to anything that triggers in the attack step
      $cardType = $definedCardType;
      if (GetResolvedAbilityType($cardID, $from) != "") $cardType = GetResolvedAbilityType($cardID, $from);
      if (!$chainClosed && $cardType == "AA") AddLayer("ATTACKSTEP", $mainPlayer, "-"); //I haven't added this for weapon. I don't think it's needed yet.
      // If you attacked an aura with Spectra
      if (!$chainClosed && (DelimStringContains($definedCardType, "AA") || DelimStringContains($definedCardType, "W") || DelimStringContains($definedCardSubType, "Ally") || DelimStringContains($definedCardSubType, "Aura"))) {
        ArsenalAttackAbilities();
        OnAttackEffects($cardID);
      }
      if (!$chainClosed || $definedCardType == "AA" || GetResolvedAbilityType($cardID) == "AA") {
        IncrementClassState($currentPlayer, $CS_NumTimesAttacked);
        if (DelimStringContains(CardSubType($cardID), "Dragon")) IncrementClassState($currentPlayer, $CS_NumDragonAttacks);
        if (ClassContains($cardID, "ILLUSIONIST", $currentPlayer)) IncrementClassState($currentPlayer, $CS_NumIllusionistAttacks);
        if (ClassContains($cardID, "ILLUSIONIST", $currentPlayer) && $definedCardType == "AA") IncrementClassState($currentPlayer, $CS_NumIllusionistActionCardAttacks);
        AuraAttackAbilities($cardID);
        CharacterAttackAbilities($cardID);
      }
    } else { //On chain, but not index 0
      if ($definedCardType == "DR" && !$skipDRResolution) {
        OnDefenseReactionResolveEffects($from, $cardID);
        if (ColorContains($cardID, 3, $defPlayer)) IncrementClassState($defPlayer, $CS_NumBlueDefended);
      }
    }
    SetClassState($currentPlayer, $CS_PlayCCIndex, $index);
  } else if ($from != "PLAY" && $from != "EQUIP") {
    $cardSubtype = CardSubType($cardID);
    if (DelimStringContains($cardSubtype, "Aura")) PlayAura($cardID, $currentPlayer, from: $from, additionalCosts: $additionalCosts);
    else if (DelimStringContains($cardSubtype, "Item")) PutItemIntoPlayForPlayer($cardID, $currentPlayer, from: $from);
    else if ($cardSubtype == "Landmark") PlayLandmark($cardID, $currentPlayer, $from);
    else if (DelimStringContains($cardSubtype, "Figment")) PutPermanentIntoPlay($currentPlayer, $cardID, from: $from);
    else if (DelimStringContains($cardSubtype, "Evo")) EvoHandling($cardID, $currentPlayer, $from);
    else if ($definedCardType != "C" && $definedCardType != "E" && $definedCardType != "W" && $definedCardType != "Macro") {
      $goesWhere = GoesWhereAfterResolving($cardID, $from, $currentPlayer, additionalCosts: $additionalCosts);
      ResolveGoesWhere($goesWhere, $cardID, $currentPlayer, $from);
    }
  }
  //Resolve Effects
  if (!$isBlock && !$skipDRResolution) {
    CurrentEffectPlayOrActivateAbility($cardID, $from);
    if ($from != "PLAY") {
      CurrentEffectPlayAbility($cardID, $from);
      ArsenalPlayCardAbilities($cardID);
      CharacterPlayCardAbilities($cardID, $from);
    }
    if (!$chainClosed || $definedCardType == "AA") {
      if ($from == "PLAY" && DelimStringContains(CardSubType($cardID), "Ally")) AllyAttackAbilities($cardID);
      if ($from == "PLAY" && DelimStringContains(CardSubType($cardID), "Ally")) SpecificAllyAttackAbilities($cardID);
    }
    $EffectContext = $cardID;
    $playText = "";
    if (!$chainClosed) {
      if (IsModular($cardID)) $additionalCosts = $uniqueID; //to track which one to remove
      $playText = PlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts);
      if ($definedCardType == "AA" && (GetResolvedAbilityType($cardID, $from) == "AA" || GetResolvedAbilityType($cardID, $from) == "")) IncrementClassState($currentPlayer, $CS_NumAttackCards); //Played or blocked
    }
    CurrentEffectAfterPlayOrActivateAbility();
    if($playText != "FAILED") //Meaning that the layer hasn't failed and resolve.
    {
      if ($from != "EQUIP" && $from != "PLAY") WriteLog("Resolving play ability of " . CardLink($cardID, $cardID) . ($playText != "" ? ": " : ".") . $playText);
      else if ($from == "EQUIP" || $from == "PLAY") WriteLog("Resolving activated ability of " . CardLink($cardID, $cardID) . ($playText != "" ? ": " : ".") . $playText);
      if (!$openedChain) ResolveGoAgain($cardID, $currentPlayer, $from, additionalCosts: $additionalCosts);
    }
    CopyCurrentTurnEffectsFromAfterResolveEffects();
    CacheCombatResult();
    if (!$isBlock) ProcessAllMirage();
    if ($isSpectraTarget) CleanUpCombatEffects(isSpectraTarget: $isSpectraTarget);
  }
  if ($CS_CharacterIndex != -1 && CanPlayAsInstant($cardID)) RemoveCharacterEffects($currentPlayer, GetClassState($currentPlayer, $CS_CharacterIndex), "INSTANT");
  //Now determine what needs to happen next
  SetClassState($currentPlayer, $CS_PlayIndex, -1);
  SetClassState($currentPlayer, $CS_CharacterIndex, -1);
  ProcessDecisionQueue();
}

function ProcessAttackTarget()
{
  global $defPlayer;
  $target = explode("-", GetAttackTarget());
  if ($target[0] == "THEIRAURAS") {
    $auras = &GetAuras($defPlayer);
    if (HasSpectra($auras[$target[1]])) {
      CloseCombatChain();
      DestroyAura($defPlayer, $target[1]);
      return true;
    }
  }
  if($target[0] == "NA") return true; //Means the target is not legal anymore
  return false;
}

function WriteGamestate()
{
  global $gameName, $playerHealths;
  global $p1Hand, $p1Deck, $p1CharEquip, $p1Resources, $p1Arsenal, $p1Items, $p1Auras, $p1Discard, $p1Pitch, $p1Banish;
  global $p1ClassState, $p1CharacterEffects, $p1Soul, $p1CardStats, $p1TurnStats, $p1Allies, $p1Permanents, $p1Settings;
  global $p2Hand, $p2Deck, $p2CharEquip, $p2Resources, $p2Arsenal, $p2Items, $p2Auras, $p2Discard, $p2Pitch, $p2Banish;
  global $p2ClassState, $p2CharacterEffects, $p2Soul, $p2CardStats, $p2TurnStats, $p2Allies, $p2Permanents, $p2Settings;
  global $landmarks, $winner, $firstPlayer, $currentPlayer, $currentTurn, $turn, $actionPoints, $combatChain, $combatChainState;
  global $currentTurnEffects, $currentTurnEffectsFromCombat, $nextTurnEffects, $decisionQueue, $dqVars, $dqState;
  global $layers, $layerPriority, $mainPlayer, $lastPlayed, $chainLinks, $chainLinkSummary, $p1Key, $p2Key;
  global $permanentUniqueIDCounter, $inGameStatus, $animations, $currentPlayerActivity;
  global $p1TotalTime, $p2TotalTime, $lastUpdateTime;
  $filename = "./Games/" . $gameName . "/gamestate.txt";
  $handler = fopen($filename, "w");

  $lockTries = 0;
  while (!flock($handler, LOCK_EX) && $lockTries < 10) {
    usleep(100000); //50ms
    ++$lockTries;
  }

  if ($lockTries == 10) {
    fclose($handler);
    exit;
  }

  fwrite($handler, implode(" ", $playerHealths) . "\r\n");

  //Player 1
  fwrite($handler, implode(" ", $p1Hand) . "\r\n");
  fwrite($handler, implode(" ", $p1Deck) . "\r\n");
  fwrite($handler, implode(" ", $p1CharEquip) . "\r\n");
  fwrite($handler, implode(" ", $p1Resources) . "\r\n");
  fwrite($handler, implode(" ", $p1Arsenal) . "\r\n");
  fwrite($handler, implode(" ", $p1Items) . "\r\n");
  fwrite($handler, implode(" ", $p1Auras) . "\r\n");
  fwrite($handler, implode(" ", $p1Discard) . "\r\n");
  fwrite($handler, implode(" ", $p1Pitch) . "\r\n");
  fwrite($handler, implode(" ", $p1Banish) . "\r\n");
  fwrite($handler, implode(" ", $p1ClassState) . "\r\n");
  fwrite($handler, implode(" ", $p1CharacterEffects) . "\r\n");
  fwrite($handler, implode(" ", $p1Soul) . "\r\n");
  fwrite($handler, implode(" ", $p1CardStats) . "\r\n");
  fwrite($handler, implode(" ", $p1TurnStats) . "\r\n");
  fwrite($handler, implode(" ", $p1Allies) . "\r\n");
  fwrite($handler, implode(" ", $p1Permanents) . "\r\n");
  fwrite($handler, implode(" ", $p1Settings) . "\r\n");

  //Player 2
  fwrite($handler, implode(" ", $p2Hand) . "\r\n");
  fwrite($handler, implode(" ", $p2Deck) . "\r\n");
  fwrite($handler, implode(" ", $p2CharEquip) . "\r\n");
  fwrite($handler, implode(" ", $p2Resources) . "\r\n");
  fwrite($handler, implode(" ", $p2Arsenal) . "\r\n");
  fwrite($handler, implode(" ", $p2Items) . "\r\n");
  fwrite($handler, implode(" ", $p2Auras) . "\r\n");
  fwrite($handler, implode(" ", $p2Discard) . "\r\n");
  fwrite($handler, implode(" ", $p2Pitch) . "\r\n");
  fwrite($handler, implode(" ", $p2Banish) . "\r\n");
  fwrite($handler, implode(" ", $p2ClassState) . "\r\n");
  fwrite($handler, implode(" ", $p2CharacterEffects) . "\r\n");
  fwrite($handler, implode(" ", $p2Soul) . "\r\n");
  fwrite($handler, implode(" ", $p2CardStats) . "\r\n");
  fwrite($handler, implode(" ", $p2TurnStats) . "\r\n");
  fwrite($handler, implode(" ", $p2Allies) . "\r\n");
  fwrite($handler, implode(" ", $p2Permanents) . "\r\n");
  fwrite($handler, implode(" ", $p2Settings) . "\r\n");

  fwrite($handler, implode(" ", $landmarks) . "\r\n");
  fwrite($handler, $winner . "\r\n");
  fwrite($handler, $firstPlayer . "\r\n");
  fwrite($handler, $currentPlayer . "\r\n");
  fwrite($handler, $currentTurn . "\r\n");
  fwrite($handler, implode(" ", $turn) . "\r\n");
  fwrite($handler, $actionPoints . "\r\n");
  fwrite($handler, implode(" ", $combatChain) . "\r\n");
  fwrite($handler, implode(" ", $combatChainState) . "\r\n");
  fwrite($handler, implode(" ", $currentTurnEffects) . "\r\n");
  fwrite($handler, implode(" ", $currentTurnEffectsFromCombat) . "\r\n");
  fwrite($handler, implode(" ", $nextTurnEffects) . "\r\n");
  fwrite($handler, implode(" ", $decisionQueue) . "\r\n");
  fwrite($handler, implode(" ", $dqVars) . "\r\n");
  fwrite($handler, implode(" ", $dqState) . "\r\n");
  fwrite($handler, implode(" ", $layers) . "\r\n");
  fwrite($handler, implode(" ", $layerPriority) . "\r\n");
  fwrite($handler, $mainPlayer . "\r\n");
  fwrite($handler, implode(" ", $lastPlayed) . "\r\n");
  fwrite($handler, count($chainLinks) . "\r\n");
  for ($i = 0; $i < count($chainLinks); ++$i) {
    fwrite($handler, implode(" ", $chainLinks[$i]) . "\r\n");
  }
  fwrite($handler, implode(" ", $chainLinkSummary) . "\r\n");
  fwrite($handler, $p1Key . "\r\n");
  fwrite($handler, $p2Key . "\r\n");
  fwrite($handler, $permanentUniqueIDCounter . "\r\n");
  fwrite($handler, $inGameStatus . "\r\n"); //Game status -- 0 = START, 1 = PLAY, 2 = OVER
  fwrite($handler, implode(" ", $animations) . "\r\n"); //Animations
  fwrite($handler, $currentPlayerActivity . "\r\n"); //Current Player activity status -- 0 = active, 2 = inactive
  fwrite($handler, "\r\n"); //Unused
  fwrite($handler, "\r\n"); //Unused
  fwrite($handler, $p1TotalTime . "\r\n"); //Player 1 total time
  fwrite($handler, $p2TotalTime . "\r\n"); //Player 2 total time
  fwrite($handler, $lastUpdateTime . "\r\n"); //Last update time
  fclose($handler);
}

function AddEvent($type, $value)
{
  global $events;
  array_push($events, $type);
  array_push($events, $value);
}

function ReportBug()
{
  global $gameName;
  $bugCount = 0;
  $folderName = "./BugReports/" . $gameName . "-" . $bugCount;
  while ($bugCount < 5 && file_exists($folderName)) {
    ++$bugCount;
    $folderName = "./BugReports/" . $gameName . "-" . $bugCount;
  }
  if ($bugCount == 3) {
    WriteLog("⚠️ Bug report file is full for this game. Please use Discord to report further bugs.", highlight: true);
  }
  mkdir($folderName, 0700, true);
  copy("./Games/$gameName/gamestate.txt", $folderName . "/gamestate.txt");
  copy("./Games/$gameName/gamestateBackup.txt", $folderName . "/gamestateBackup.txt");
  copy("./Games/$gameName/gamelog.txt", $folderName . "/gamelog.txt");
  copy("./Games/$gameName/beginTurnGamestate.txt", $folderName . "/beginTurnGamestate.txt");
  copy("./Games/$gameName/lastTurnGamestate.txt", $folderName . "/lastTurnGamestate.txt");
  WriteLog("🐛 Thank you for reporting a bug. Please report it on Discord with the game number as reference ($gameName).");
}
