<?php

function ModalAbilities($player, $card, $lastResult, $index=-1)
{
  global $combatChain, $defPlayer, $CombatChain, $combatChainState, $CS_ModalAbilityChoosen;
  if(isset($lastResult[0])) SetClassState($player, $CS_ModalAbilityChoosen, $card."-".$lastResult[0]);
  AddDecisionQueue("CURRENTEFFECTAFTERPLAYORACTIVATEABILITY", $player, "<-");
  switch($card)
  {
    case "ESTRIKE":
      switch($lastResult) {
        case "Draw_a_Card": return Draw($player);
        case "Buff_Power": AddCurrentTurnEffect("WTR159", $player); return 1;
        case "Go_Again": GiveAttackGoAgain(); return 2;
      }
      return $lastResult;
    case "JUSTANICK":
      switch($lastResult) {
        case "Buff_Power": AddCurrentTurnEffect("MST105-BUFF", $player); break;
        case "Gain_On-Hit": AddCurrentTurnEffect("MST105-HIT", $player); break;
        case "Both": AddCurrentTurnEffect("MST105-BUFF", $player); AddCurrentTurnEffect("MST105-HIT", $player); break;
      }
      return $lastResult;
    case "MAUL":
      switch($lastResult) {
        case "Buff_Power": AddCurrentTurnEffect("MST162-BUFF", $player); break;
        case "Gain_On-Hit": AddCurrentTurnEffect("MST162-HIT", $player); break;
        case "Both": AddCurrentTurnEffect("MST162-BUFF", $player); AddCurrentTurnEffect("MST162-HIT", $player); break;
      }
      return $lastResult;
    case "LEVELSOFENLIGHTENMENT":
      if(!is_array($lastResult)) $lastResult = explode(",", $lastResult);
      for($i = 0; $i < count($lastResult); ++$i) {
        switch($lastResult[$i]) {
          case "Draw_a_card": {
            Draw($player);
            break;
          }
          case "Buff_Power": {
            AddCurrentTurnEffect("MST077", $player);
            break;
          }
          case "Go_again": {
            GiveAttackGoAgain();
            break;
          }
        }
      }
      return $lastResult;
    case "TARANTULATOXIN":
      switch($lastResult) {
        case "Buff_Power": AddCurrentTurnEffect("HNT015", $player); break;
        case "Reduce_Block":
          Shred($player, -3);
          break;
        case "Both":
          Shred($player, -3);
          AddCurrentTurnEffect("HNT015", $player); 
          break;
      }
      return $lastResult;
    case "TWOSIDES":
      switch($lastResult) {
        case "Buff_Dagger": AddCurrentTurnEffect("HNT051-DAGGER", $player); break;
        case "Buff_Stealth": AddCurrentTurnEffect("HNT051-ATTACK", $player); break;
      }
      return $lastResult;
    case "LONGWHISKER":
      if(!is_array($lastResult)) $lastResult = explode(",", $lastResult);
      for($i = 0; $i < count($lastResult); ++$i) {
        $mode = $lastResult[$i];
        switch($mode) {
          case "Buff_Power": {
            AddCurrentTurnEffect("HNT102-BUFF", $player);
            break;
          }
          case "Additional_Attack": {
            AddDecisionQueue("MULTIZONEINDICES", $player, "MYCHAR:subtype=Dagger", 1);
            AddDecisionQueue("SETDQCONTEXT", $player, "Choose a dagger to attack an additional time", 1);
            AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
            AddDecisionQueue("EXTRAATTACK", $player, "<-", 1);
            break;
          }
          case "Mark": {
            AddDecisionQueue("MULTIZONEINDICES", $player, "MYCHAR:subtype=Dagger", 1);
            AddDecisionQueue("SETDQCONTEXT", $player, "Choose a dagger to add an on-hit mark effect to", 1);
            AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
            AddDecisionQueue("ADDONHITMARK", $player, "<-", 1);
            break;
          }
        }
      }
      return $lastResult;
    case "MICROPROCESSOR":
      $deck = new Deck($player);
      $items = &GetItems($player);
      $modalities = explode(",", $items[$index+8]);
      $indexToRemove = array_search($lastResult, $modalities);
      unset($modalities[$indexToRemove]);
      $items[$index+8] = implode(",", $modalities);
      switch($lastResult) {
        case "Opt":
          WriteLog(Cardlink("EVR070","EVR070") . " let you Opt 1");
          Opt("EVR070", 1);
          break;
        case "Draw_then_top_deck":
          if(!$deck->Empty()) {
            WriteLog(Cardlink("EVR070","EVR070") . " let you draw a card then put one on top");
            Draw($player);
            HandToTopDeck($player);
          }
          break;
        case "Banish_top_deck":
          if(!$deck->Empty()) {
            $card = $deck->Top(remove:true);
            BanishCardForPlayer($card, $player, "DECK", "-");
            WriteLog(Cardlink("EVR070","EVR070") . " banished " . CardLink($card, $card));
          }
          break;
        default: break;
      }
      return "";
    case "TWINTWISTERS":
      switch($lastResult) {
        case "Hit_Effect":
          AddCurrentTurnEffect("EVR047-1", $player);
          return 1;
        case "1_Attack":
          AddCurrentTurnEffect("EVR047-2", $player);
          return 2;
      }
      return $lastResult;
    case "SHIVER":
      $arsenal = &GetArsenal($player);
      switch($lastResult) {
        case "1_Attack":
          AddCurrentTurnEffect("ELE033-1", $player, "HAND", $arsenal[count($arsenal) - ArsenalPieces() + 5]);
          return 1;
        case "Dominate":
          AddCurrentTurnEffect("ELE033-2", $player, "HAND", $arsenal[count($arsenal) - ArsenalPieces() + 5]);
          return 1;
      }
      return $lastResult;
    case "VOLTAIRE":
      $arsenal = &GetArsenal($player);
      switch ($lastResult) {
        case "1_Attack":
          AddCurrentTurnEffect("ELE034-1", $player, "HAND", $arsenal[count($arsenal) - ArsenalPieces() + 5]);
          return 1;
        case "Go_again":
          AddCurrentTurnEffect("ELE034-2", $player, "HAND", $arsenal[count($arsenal) - ArsenalPieces() + 5]);
          return 1;
      }
      return $lastResult;
    case "KORSHEM":
      switch($lastResult) {
          case "Gain_a_resource": GainResources($player, 1); return 1;
          case "Gain_a_life": GainHealth(1, $player); return 2;
          case "1_Attack": AddCurrentTurnEffect("ELE000-1", $player); return 3;
          case "1_Defense": AddCurrentTurnEffect("ELE000-2", $player); return 4;
          default: break;
        }
      return $lastResult;
    case "ARTOFWAR":
      $params = explode(",", $lastResult);
      for($i = 0; $i < count($params); ++$i) {
        switch($params[$i]) {
          case "Buff_your_attack_action_cards_this_turn":
            AddCurrentTurnEffect("ARC160-1", $player);
            if($player == $defPlayer) {
              for($j = CombatChainPieces(); $j < count($combatChain); $j += CombatChainPieces()) {
                if(CardType($combatChain[$j]) == "AA") CombatChainPowerModifier($j, 1);
              }
            }
            break;
          case "Your_next_attack_action_card_gains_go_again":
            AddCurrentTurnEffectNextAttack("ARC160-3", $player);
            break;
          case "Defend_with_attack_action_cards_from_arsenal":
            AddCurrentTurnEffect("ARC160-2", $player);
            break;
          case "Banish_an_attack_action_card_to_draw_2_cards":
            PrependDecisionQueue("DRAW", $player, "-", 1);
            PrependDecisionQueue("DRAW", $player, "-", 1);
            PrependDecisionQueue("MZREMOVE", $player, "-", 1);
            PrependDecisionQueue("MZADDZONE", $player, "MYBANISH,HAND", 1);
            PrependDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
            PrependDecisionQueue("SETDQCONTEXT", $player, "Choose a card to banish", 1);
            PrependDecisionQueue("MULTIZONEINDICES", $player, "MYHAND:type=AA");
            break;
          default: break;
        }
      }
      return $lastResult;
    case "FABRICATE":
      $params = explode(",", $lastResult);
      $GoesToGraveyard = true;
      for($i = 0; $i < count($params); ++$i) {
        switch($params[$i]) {
          case "Equip_Proto_equipment":
            AddDecisionQueue("FABRICATE", $player, "-");
            AddDecisionQueue("SETDQCONTEXT", $player, "Choose a Proto card to equip", 1);
            AddDecisionQueue("CHOOSECARD", $player, "<-", 1);
            AddDecisionQueue("APPENDLASTRESULT", $player, "-INVENTORY", 1);
            AddDecisionQueue("EQUIPCARDINVENTORY", $player, "<-", 1);
            break;
          case "Evo_permanents_get_+1_block":
            AddCurrentTurnEffect("EVO146", $player);
            break;
          case "Put_this_under_an_Evo_permanent":
            AddDecisionQueue("MULTIZONEINDICES", $player, "MYCHAR:subtype=Evo");
            AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
            AddDecisionQueue("MZOP", $player, "ADDSUBCARD,EVO146", 1);
            $GoesToGraveyard = false;
            break;
          case "Banish_an_Evo_and_draw_a_card":
            MZChooseAndBanish($player, "MYHAND:subtype=Evo", "HAND,-", may:true);
            AddDecisionQueue("DRAW", $player, "-", 1);
            break;
          default: break;
        }
      }
      if ($GoesToGraveyard) AddGraveyard("EVO146", $player, "HAND");
      return $lastResult;
    case "COAXCOMMOTION":
      if(!is_array($lastResult)) return $lastResult;
      for($i = 0; $i < count($lastResult); ++$i) {
        switch($lastResult[$i]) {
          case "Quicken_token":
            PlayAura("WTR225", 1);
            PlayAura("WTR225", 2);
            break;
          case "Draw_card":
            Draw($player);
            Draw($player == 1 ? 2 : 1);
            break;
          case "Gain_life":
            GainHealth(1, $player);
            GainHealth(1, ($player == 1 ? 2 : 1));
            break;
          default: break;
        }
      }
      return $lastResult;
    case "MON260": case "MON261": case "MON262":
      switch($lastResult) {
        case "Buff_Power": AddCurrentTurnEffect("$card-1", $player); return 1;
        case "Go_Again": AddCurrentTurnEffect("$card-2", $player); return 2;
      }
      return $lastResult;
    case "TOMEOFAETHERWIND":
      $params = explode(",", $lastResult);
      for($i = 0; $i < count($params); ++$i) {
        switch($params[$i]) {
          case "Buff_Arcane": AddCurrentTurnEffect("ARC122", $player); break;
          case "Draw_card": Draw($player); break;
          default: break;
        }
      }
      return $lastResult;
    case "BLOODONHERHANDS":
      $choices = explode(",", $lastResult);
      for($i=0; $i<count($choices); ++$i)
      {
        switch($choices[$i])
        {
          case "Buff_Weapon":
            AddDecisionQueue("FINDINDICES", $player, "WEAPON");
            AddDecisionQueue("SETDQCONTEXT", $player, "Choose a weapon to give +1", 1);
            AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
            AddDecisionQueue("ADDMZBUFF", $player, "EVR055-1", 1);
            break;
          case "Go_Again":
            AddDecisionQueue("FINDINDICES", $player, "WEAPON");
            AddDecisionQueue("SETDQCONTEXT", $player, "Choose a weapon to give go again", 1);
            AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
            AddDecisionQueue("ADDMZBUFF", $player, "EVR055-2", 1);
            break;
          case "Attack_Twice":
            AddDecisionQueue("FINDINDICES", $player, "WEAPON");
            AddDecisionQueue("SETDQCONTEXT", $player, "Choose a weapon to give a second attack", 1);
            AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
            AddDecisionQueue("ADDMZUSESBLOODONHERHANDS", $player, "1", 1);
            break;
          default: break;
        }
      }
      return $lastResult;
    case "JINGLEWOOD":
      switch($lastResult[0])
      {
        case "Might_(+1)": PlayAura("HVY241", $defPlayer); break;
        case "Vigor_(Resource)": PlayAura("HVY242", $defPlayer); break;
        case "Quicken_(Go Again)": PlayAura("WTR225", $defPlayer); break;
        default: break;
      }
      return $lastResult;
    case "ADAPTIVEPLATING":
      if(is_array($lastResult) && count($lastResult) > 0) $lastResult = $lastResult[0];
      if($lastResult != "None") EquipEquipment($player, "EVO013", $lastResult);
      return $lastResult;
    case "ADAPTIVEDISSOLVER":
      if(is_array($lastResult) && count($lastResult) > 0) $lastResult = $lastResult[0];
      if($lastResult != "None") EquipEquipment($player, "ROS246", $lastResult);
      return $lastResult;
    case "UPTHEANTE":
      $numNewWagers = 0;
      $params = explode(",", $lastResult);
      for($i = 0; $i < count($params); ++$i) {
        switch($params[$i]) {
          case "Wager_Agility":
            if(IsHeroAttackTarget()) {
              AddCurrentTurnEffect("HVY103-1", $player);
              AddOnWagerEffects(canPass:false);
              ++$numNewWagers;
            }
            break;
          case "Wager_Gold":
              if(IsHeroAttackTarget()) {
              AddCurrentTurnEffect("HVY103-2", $player);
              AddOnWagerEffects(canPass:false);
              ++$numNewWagers;
            }
            break;
          case "Wager_Vigor":
            if(IsHeroAttackTarget()) {
              AddCurrentTurnEffect("HVY103-3", $player);
              AddOnWagerEffects(canPass:false);
              ++$numNewWagers;
            }
            break;
          case "Buff_Attack":
            global $CCS_WagersThisLink;
            $CombatChain->AttackCard()->ModifyPower(intval($combatChainState[$CCS_WagersThisLink]) + $numNewWagers);
            break;
          default: break;
        }
      }
      return $lastResult;
      case "SKYWARDSERENADE":
        $params = explode(",", $lastResult);
        for($i = 0; $i < count($params); ++$i) {
          switch($params[$i]) {
            case "create_embodiment_of_lightning":
              PlayAura("embodiment-of-lightning-0", $player, isToken:true, effectController:$player, effectSource:"skyward-serenade-2");
              break;
            case "search_for_skyzyk":
              $search = "MYDECK:isSameName=skyzyk";
              $fromMod = "Deck,TT"; //pull it out of the deck, playable "This Turn"
              AddDecisionQueue("MULTIZONEINDICES", $player, $search, 1);
              AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
              AddDecisionQueue("MZBANISH", $player, $fromMod, 1);
              AddDecisionQueue("MZREMOVE", $player, "-", 1);
              AddDecisionQueue("SHUFFLEDECK", $player, "-", 1);
              break;
            case "buff_next_attack":
              AddCurrentTurnEffectNextAttack("skyward-serenade-2", $player);
              break;
            default: break;
          }
        }
        return $lastResult;
    default: return "";
  }
}

function PlayerTargetedAbility($player, $card, $lastResult)
{
  global $dqVars;
  $target = ($lastResult == "Target_Opponent" ? ($player == 1 ? 2 : 1) : $player);
  $params = explode("-", $card);
  switch($params[0])
  {
    case "CORONETPEAK":
      AddDecisionQueue("DQPAYORDISCARD", $target, "1");
      return "";
    case "WINTERSBITE":
      AddDecisionQueue("DQPAYORDISCARD", $target, $params[1]);
      return "";
    case "IMPERIALWARHORN":
      if($lastResult == "Target_Opponent" || $lastResult == "Target_Both_Heroes")
      {
        if(IsRoyal($player)) ImperialWarHorn($player, "THEIR");
        else ImperialWarHorn(($player == 1 ? 2 : 1), "MY");
      }
      if($lastResult == "Target_Yourself" || $lastResult == "Target_Both_Heroes") ImperialWarHorn($player, "MY");
      return "";
    case "PRY":
      $zone = $target == $player ? "HAND" : "THEIRHAND";
      AddDecisionQueue("FINDINDICES", $target, "HAND");
      AddDecisionQueue("PREPENDLASTRESULT", $target, $dqVars[0] . "-", 1);
      AddDecisionQueue("APPENDLASTRESULT", $target, "-" . $dqVars[0], 1);
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose " . $dqVars[0] . " card" . ($dqVars[0] > 1 ? "s" : ""), 1);
      AddDecisionQueue("MULTICHOOSEHAND", $target, "<-", 1);
      AddDecisionQueue("IMPLODELASTRESULT", $target, ",", 1);
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose a card", 1);
      AddDecisionQueue("CHOOSE" . $zone, $player, "<-", 1);
      AddDecisionQueue("MULTIREMOVEHAND", $target, "-", 1);
      AddDecisionQueue("ADDBOTDECK", $target, "-", 1);
      AddDecisionQueue("DRAW", $target, "-", 1);
      return "";
    case "AMULETOFECHOES":
      PummelHit($target);
      PummelHit($target);
      return "";
    case "DTD178": case "DTD179": case "DTD180":
      if($params[0] == "DTD178") $pitchTarget = 1;
      else if($params[0] == "DTD179") $pitchTarget = 2;
      else $pitchTarget = 3;
      $deck = new Deck($target);
      $banished = $deck->BanishTop();
      if(PitchValue($banished) == $pitchTarget) LoseHealth(1, $target);
      return "";
    case "BURDENSOFTHEPAST":
      $defenseReactionsInDiscard = SearchDiscard($target, "DR", getDistinctCardNames: true);
      WriteLog("Player {$target} was targeted. Burdens of the Past prevents the play of the folowing defense reactions: <b>" . (str_replace("_", " ", $defenseReactionsInDiscard)) . "</b>");
      AddCurrentTurnEffect("OUT187," . $defenseReactionsInDiscard, $target);
      if(SearchCount(SearchDiscard($target, "DR")) >= 10) {
        WriteLog("Player {$player} draws a card as target hero has at least 10 defense reactions in their graveyard.");
        Draw($player);
      }
      return "";
    default: return $lastResult;
  }
}

function filterIndices($indices, $zone, $dqVars, $condition) {
  $filteredIndices = array_filter($indices, function($index) use ($zone, $dqVars, $condition) {
      $block = isset($zone[$index]) ? BlockValue($zone[$index]) : -1;
      $type = CardType($zone[$index]);
      return $block > -1 && $condition($block, $dqVars) && (DelimStringContains($type, "A") || $type == "AA");
  });
  $filteredIndices = implode(",", $filteredIndices);
  return $filteredIndices == "" ? "PASS" : $filteredIndices;
}

function SpecificCardLogic($player, $card, $lastResult, $initiator)
{
  global $dqVars, $CS_DamageDealt, $CS_AdditionalCosts, $EffectContext, $CombatChain, $CS_PlayCCIndex, $CS_PowDamageDealt;
  $otherPlayer = ($player == 1) ? 2 : 1;
  $params = explode("-", $card);
  switch($params[0])
  {
    case "RIGHTEOUSCLEANSING":
      $numBanished = SearchCount($lastResult);
      $numLeft = 5 - $numBanished;
      $deck = new Deck($player == 1 ? 2 : 1);
      $reorderCards = "";
      for($i = 0; $i < $numLeft; ++$i) {
        if($deck->RemainingCards() > 0) {
          if($reorderCards != "") $reorderCards .= ",";
          $reorderCards .= $deck->Top(remove:true);
        }
      }
      if($reorderCards != "") {
        PrependDecisionQueue("CHOOSETOPOPPONENT", $player, $reorderCards);
        PrependDecisionQueue("SETDQCONTEXT", $player, "Choose a card to put on top of their deck");
      }
      return "";
    case "PULSEWAVEHARPOONFILTER":
      $indices = is_array($lastResult) ? $lastResult : explode(",", $lastResult);
      $hand = GetHand($player);
      if (empty($hand)) return "PASS";
      return filterIndices($indices, $hand, $dqVars, function($block, $dqVars) { return $block <= $dqVars[0]; });
    case "PULSEWAVEPROTOCOLFILTER":
      $indices = is_array($lastResult) ? $lastResult : explode(",", $lastResult);
      $hand = GetHand($player);
      return filterIndices($indices, $hand, $dqVars, function($block, $dqVars) { return $block < $dqVars[0]; });
    case "SIFT":
      $numCards = SearchCount($lastResult);
      WriteLog("<b>$numCards cards</b> were put at the bottom of the deck.");
      for ($i = 0; $i < $numCards; ++$i) {
        Draw($player);
      }
      return "1";
    case "ENCASEDAMAGE":
      $character = &GetPlayerCharacter($player);
      $character[8] = 1;//Freeze their character
      for ($i = CharacterPieces(); $i < count($character); $i += CharacterPieces()) {
        if (CardType($character[$i]) == "E" && $character[$i + 1] != 0) $character[$i + 8] = 1;//Freeze their equipment
      }
      return $lastResult;
    case "BLESSINGOFFOCUS":
      $deck = new Deck($player);
      if($deck->Reveal() && CardSubType($deck->Top()) == "Arrow") {
        if(!ArsenalFull($player)) { AddArsenal($deck->Top(true), $player, "DECK", "UP", 1); }
        else WriteLog("Your arsenal is full");
      }
      return $lastResult;
    case "EVENBIGGERTHANTHAT":
      $deck = new Deck($player);
      $modifiedAttack = ModifiedAttackValue($deck->Top(), $player, "DECK", source:"");
      if($deck->Reveal() && $modifiedAttack > GetClassState(($player == 1 ? 1 : 2), piece: $CS_DamageDealt)) {
        WriteLog(CardLink($params[1], $params[1]) . " draw a card and created a " . CardLink("WTR225", "WTR225") . " token");
        Draw($player);
        PlayAura("WTR225", $player);
      }
      return $lastResult;
    case "KRAKENAETHERVEIN":
      if(intval($lastResult) > 0) {
        for ($i = 0; $i < $lastResult; ++$i) Draw($player);
      }
      return $lastResult;
    case "SCEPTEROFPAIN":
      global $dqVars;
      if(intval($dqVars[0]) > 0) {
        PlayAura("ARC112", $player, number:intval($dqVars[0]));
      }
      return $lastResult;
    case "AERTHERARC":
      global $dqVars;
      if(intval($dqVars[0]) > 0) {
        PlayAura("DYN244", $player);
      }
      return $lastResult;
    case "KNICKKNACK":
      PrependDecisionQueue("PUTPLAY", $player, "-", 1);
      PrependDecisionQueue("MAYCHOOSEDECK", $player, "<-", 1);
      PrependDecisionQueue("FINDINDICES", $player, "KNICKKNACK");
      for($i = 0; $i < $lastResult; ++$i) {
        PrependDecisionQueue("PUTPLAY", $player, "-", 1);
        PrependDecisionQueue("MAYCHOOSEDECK", $player, "<-", 1);
        PrependDecisionQueue("FINDINDICES", $player, "KNICKKNACK");
      }
      return "";
    case "BECOMETHEARKNIGHT":
      $type = (CardType($lastResult) == "A" ? "AA" : "A");
      PrependDecisionQueue("MULTIADDHAND", $player, "-", 1);
      PrependDecisionQueue("REVEALCARDS", $player, "-", 1);
      PrependDecisionQueue("MZREMOVE", $player, "-", 1);
      PrependDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
      PrependDecisionQueue("MULTIZONEINDICES", $player, "MYDECK:type=$type;class=RUNEBLADE");
      return 1;
    case "HOPEMERCHANTHOOD":
      $count = SearchCount($lastResult);
      for($i = 0; $i < $count; ++$i) Draw($player);
      WriteLog(CardLink("WTR151", "WTR151") . " shuffled and drew " . $count . " cards");
      return "1";
    case "CASHOUTCONTINUE":
      PrependDecisionQueue("SPECIFICCARD", $player, "CASHOUTCONTINUE", 1);
      PrependDecisionQueue("SETCLASSSTATE", $player, $CS_AdditionalCosts, 1);
      PrependDecisionQueue("PASSPARAMETER", $player, GetClassState($player, $CS_AdditionalCosts)+1, 1);
      PrependDecisionQueue("MZDESTROY", $player, "-", 1);
      PrependDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
      PrependDecisionQueue("FINDINDICES", $player, "CASHOUT");
      return "";
    case "SANDSKETCH":
      $discarded = DiscardRandom($player, "WTR009");
      if(ModifiedAttackValue($discarded, $player, "HAND", source:"WTR009") >= 6) GainActionPoints(2, $player);
      return "1";
    case "REMEMBRANCE":
      $cards = "";
      $deck = new Deck($player);
      $discard = new Discard($player);
      sort($lastResult);
      for($i = count($lastResult)-1; $i >= 0; --$i) {
        $cardID = $discard->Remove($lastResult[$i]);
        $deck->AddBottom($cardID, "GY");
        if($cards != "") $cards .= ", ";
        if($i == 0) $cards .= "and ";
        $cards .= CardLink($cardID, $cardID);
      }
      WriteLog("The following cards where shuffled: " . $cards);
      return "1";
    case "QUIVEROFABYSSALDEPTH":
      $cards = "";
      $deck = new Deck($player);
      $discard = new Discard($player);
      sort($lastResult);
      for($i = count($lastResult)-1; $i >= 0; --$i) {
        $cardID = $discard->Remove($lastResult[$i]);
        $deck->AddBottom($cardID, "GY");
        if($cards != "") $cards .= ", ";
        if($i == 0) $cards .= "and ";
        $cards .= CardLink($cardID, $cardID);
      }
      WriteLog(CardLink("OUT095", "OUT095") . " shuffled into your deck " . $cards);
      return "1";
    case "PLASMAMAINLINE":
      $items = &GetItems($player);
      $lastResultArr = explode(",", $lastResult);
      $PMIndex = SearchItemsForUniqueID($lastResultArr[0], $player);
      $targetIndex = SearchItemsForUniqueID($lastResultArr[1], $player);
      ++$items[$targetIndex + 1];
      if(--$items[$PMIndex + 1] == 0) DestroyItemForPlayer($player, $PMIndex);
      return $lastResult;
    case "TOMEOFDUPLICITY":
      $cards = explode(",", $lastResult);
      $mzIndices = "";
      $mod = (CardType($cards[0]) == "A" ? "INST" : "-");
      for($i = 0; $i < count($cards); ++$i) {
        $index = BanishCardForPlayer($cards[$i], $player, "DECK", $mod);
        WriteLog(CardLink($cards[$i], $cards[$i]) . " was banished.");
        if($mzIndices != "") $mzIndices .= ",";
        $mzIndices .= "BANISH-" . $index;
      }
      $dqState[5] = $mzIndices;
      return $lastResult;
    case "SANDSCOURGREATBOW":
      if($lastResult == "NO") LoadArrow($player);
      else {
        $deck = new Deck($player);
        $cardID = $deck->Top(remove:true);
        AddArsenal($cardID, $player, "DECK", "UP");
      }
      return $lastResult;
    case "SOULREAPING":
      $cards = $lastResult != "" ? explode(",", $lastResult) : [];
      if(count($cards) > 0) AddCurrentTurnEffect("MON199", $player);
      $numBD = 0;
      for($i = 0; $i < count($cards); ++$i) if (HasBloodDebt($cards[$i])) {
        ++$numBD;
      }
      GainResources($player, $numBD);
      return 1;
    case "DIMENXXIONALGATEWAY":
      if(ClassContains($lastResult, "RUNEBLADE", $player)) DealArcane(1, 0, "PLAYCARD", $EffectContext, true);
      if(TalentContains($lastResult, "SHADOW", $player)) {
        PrependDecisionQueue("MULTIBANISH", $player, "DECK,-", 1);
        PrependDecisionQueue("MULTIREMOVEDECK", $player, "<-", 1);
        PrependDecisionQueue("FINDINDICES", $player, "TOPDECK", 1);
        PrependDecisionQueue("NOPASS", $player, "-", 1);
        PrependDecisionQueue("YESNO", $player, "if_you_want_to_banish_the_card", 1);
      }
      return $lastResult;
    case "BEASTWITHIN":
      $deck = new Deck($player);
      if($deck->Empty()) {
        LoseHealth(9999, $player);
        WriteLog("💀 Your deck has no cards, so " . CardLink("CRU007", "CRU007") . " continues damaging you until you die.");
        return 1;
      }
      $card = $deck->BanishTop("-", $player);
      LoseHealth(1, $player);
      if(ModifiedAttackValue($card, $player, "DECK", source:"CRU007") >= 6) {
        $banish = new Banish($player);
        RemoveBanish($player, ($banish->NumCards()-1)*BanishPieces());
        AddPlayerHand($card, $player, "BANISH");
      } else PrependDecisionQueue("SPECIFICCARD", $player, "BEASTWITHIN");
      return 1;
    case "CROWNOFDICHOTOMY":
      $lastType = CardType($lastResult);
      $newType = ($lastType == "A" ? "AA" : "A");
      MZMoveCard($player, "MYDISCARD:type=" . $newType, "MYTOPDECK");
      return 1;
    case "PICKACARD":
      $index = explode("-", $dqVars[0])[1];
      $hand = &GetHand(($player == 1 ? 2 : 1));
      $chosenName = CardName($hand[$index]);
      $rand = GetRandom(0, count($hand) - 1);
      if(RevealCards($hand[$rand], $player) && $chosenName == CardName($hand[$rand])) {
        WriteLog("Bingo! Your opponent tossed you a silver.");
        PutItemIntoPlayForPlayer("EVR195", $player);
      }
      return $lastResult;
    case "GENESIS":
      AddSoul($lastResult, $player, "HAND", false);
      if(TalentContains($lastResult, "LIGHT", $player)) Draw($player, false);
      if(ClassContains($lastResult, "ILLUSIONIST", $player)) PlayAura("MON104", $player);
      return 1;
    case "SPOILEDSKULL":
      $banish = new Banish($player);
      $index = implode(",", $lastResult);
      $cleanIndexes = RemoveCardSameNames($player, $index, GetBanish($player));
      if(count(explode(",", $cleanIndexes)) < 3) {
        WriteLog("You selected cards that have the same name. Reverting gamestate prior to that effect.", highlight: true);
        RevertGamestate();
      }
      else {
        $rand = GetRandom(0, count($lastResult) - 1);
        $card = $banish->Card($lastResult[$rand]);
        $card->SetModifier("TT");
        WriteLog("You may play " . CardLink($card->ID(), $card->ID()) . " this turn");  
      }
      return $lastResult;
    case "ALLURINGINDUCEMENT":
      global $combatChain, $combatChainState, $CCS_LinkBaseAttack;
      $combatChain[0] = $lastResult;
      $combatChainState[$CCS_LinkBaseAttack] = AttackValue($combatChain[0]);
      return $lastResult;
    case "CONSTRUCTNITROMECHANOID":
      sort($lastResult);
      for($i = count($lastResult)-1; $i >= 0; --$i) {
        RemoveItemAndAddAsSubcardToCharacter($player, $lastResult[$i], $initiator);
      }
      return $lastResult;
    case "SYSTEMRESET":
      $destroyedItems = [];
      for($i=count($lastResult)-1; $i>=0; --$i) {
        $cardID = DestroyItemForPlayer($player, $lastResult[$i], skipDestroy:true);
        $banishIndex = BanishCardForPlayer($cardID, $player, "PLAY", "-", banishedBy:"EVO145");
        $cardID = RemoveBanish($player, $banishIndex);
        array_push($destroyedItems, $cardID);
      }
      for($i=0; $i<count($destroyedItems); ++$i) {
        PutItemIntoPlayForPlayer($destroyedItems[$i], $player);
      }
      return $lastResult;
    case "TICKTOCKCLOCK":
      DamageTrigger($player, $dqVars[0]+1, "DAMAGE", "EVO074");
      return $lastResult;
    case "EVOBREAKER":
      if($lastResult == "PASS") {
        if($dqVars[0] != "-") {
          $char = &GetPlayerCharacter($player);
          $hyperdriverArr = explode(",", $dqVars[0]);
          $index = $hyperdriverArr[0];
          $count = count($hyperdriverArr);
          for($i=1; $i<$count; ++$i) {
            CharacterAddSubcard($player, $index, $hyperdriverArr[$i]);
          }
          AddCurrentTurnEffect($char[$index] . "-" . (($count-1)*2), $player);
        }
        return $lastResult;
      }
      else if($lastResult != "-") {
        if($dqVars[0] == "-") $dqVars[0] = $lastResult;
        else $dqVars[0] .= "," . $lastResult;
      }
      PrependDecisionQueue("SPECIFICCARD", $player, "EVOBREAKER");
      PrependDecisionQueue("MZREMOVE", $player, "-", 1);
      PrependDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
      PrependDecisionQueue("SETDQCONTEXT", $player, "Choose a Hyper Driver to transform (or pass)", 1);
      PrependDecisionQueue("MULTIZONEINDICES", $player, "MYITEMS:isSameName=ARC036", 1);
      return $lastResult;
    case "HYPERSCRAPPER":
      global $CombatChain;
      $scrappedAmount = count($lastResult);
      $scrappedHyperDriverAmount = 0;
      sort($lastResult);
      $discard = new Discard($player);
      for ($i = $scrappedAmount - 1; $i >= 0; $i--) {
        $cardID = $discard->Remove($lastResult[$i]);
        BanishCardForPlayer($cardID, $player, "DISCARD", banishedBy: "EVO100");
        if (CardName($cardID) == "Hyper Driver") $scrappedHyperDriverAmount++;
      }
      if ($scrappedHyperDriverAmount >= 3) {
        GainResources($player, 6);
        GiveAttackGoAgain();
      }
      $CombatChain->AttackCard()->ModifyPower(+$scrappedAmount);
      return $scrappedAmount;
    case "MEGANETICLOCKWAVE":
      $cardID = GetMZCard($player, $lastResult);
      WriteLog(CardLink($cardID, $cardID) . " was targeted.");
      AddCurrentTurnEffect("EVO143", $player, uniqueID: $cardID);
      return $lastResult;
    case "NOFEAR":
      if(!is_array($lastResult)) $lastResult = [];
      for($i=count($lastResult)-1; $i>=0; --$i) {
        $cardID = RemoveHand($player, $lastResult[$i]);
        BanishCardForPlayer($cardID, $player, "HAND", "NOFEAR", banishedBy:"HVY016");
      }
      SetClassState($player, $CS_AdditionalCosts, count($lastResult));
      return "";
    case "RAISEANARMY":
      if($dqVars[0] > 0) {
        --$dqVars[0];
        AddDecisionQueue("MULTIZONEINDICES", $player, "MYITEMS:isSameName=DYN243&MYCHAR:cardID=HVY051", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
        AddDecisionQueue("MZDESTROY", $player, "-", 1);
        AddDecisionQueue("SPECIFICCARD", $player, "RAISEANARMY", 1);
      }
      return "";
    case "GOLDENANVIL":
      if($dqVars[0] > 0) {
        --$dqVars[0];
        AddDecisionQueue("MULTIZONEINDICES", $player, "MYITEMS:isSameName=DYN243&MYCHAR:cardID=HVY051", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
        AddDecisionQueue("MZDESTROY", $player, "-", 1);
        AddDecisionQueue("SPECIFICCARD", $player, "GOLDENANVIL", 1);
      }
      return "";
    case "MURKYWATER":
      $discard = GetDiscard($player);
      $cardList = [];
      for($i=2; $i>=0; $i--) {
        WriteLog(CardLink($discard[$lastResult[$i]], $discard[$lastResult[$i]]) . " was banished.");
        BanishCardForPlayer($discard[$lastResult[$i]], $player, "GY", "FACEDOWN", "MST233");
        array_push($cardList, $discard[$lastResult[$i]]);
      }
      if(!ArsenalFull($player)) {
          $rand = GetRandom(0, 2);
          AddArsenal($cardList[$rand], $player, "BANISH", "DOWN");
          RemoveBanish($player, SearchBanishForCard($player, $cardList[$rand]));
        }
      for($j=2; $j>=0; $j--) {
        $index = SearchDiscardForCard($player, $cardList[$j]);
        $index = explode(",", $index)[0]; //in case the search returns 2 cards
        RemoveGraveyard($player, $index);
      }
      return $lastResult;
    case "SILVERTHETIPADDARSENAL":
      $log = "";
      if($lastResult != "") {
        AddArsenal($lastResult, $player, "DECK", "UP");
        $log .= CardLink($lastResult, $lastResult);
        return $lastResult;
      }
      if ($log != "") WriteLog($log . " added to arsenal");
      return $lastResult;
    case "FELLINGOFTHECROWN":
      BottomDeck($player);
      BottomDeck($otherPlayer);
      return $lastResult;
    case "PLOWUNDER":
      AddDecisionQueue("FINDINDICES", $player, "ARSENAL");
      AddDecisionQueue("CHOOSEARSENAL", $player, "<-", 1);
      AddDecisionQueue("REMOVEARSENAL", $player, "-", 1);
      AddDecisionQueue("ADDBOTDECK", $player, "-", 1);
      AddDecisionQueue("FINDINDICES", $otherPlayer, "ARSENAL");
      AddDecisionQueue("CHOOSEARSENAL", $otherPlayer, "<-", 1);
      AddDecisionQueue("REMOVEARSENAL", $otherPlayer, "-", 1);
      AddDecisionQueue("ADDBOTDECK", $otherPlayer, "-", 1);
      return $lastResult;
    case "BLOSSOMINGDECAY":
      GainHealth(1, $player);
      return $lastResult;
    case "ROOTBOUNDCARAPACE":
      $index = GetClassState($player, $CS_PlayCCIndex);
      $CombatChain->Card($index)->ModifyDefense(1);
      return $lastResult;
    case "CADAVEROUSTILLING":
      $index = GetClassState($player, $CS_PlayCCIndex);
      $CombatChain->Card($index)->ModifyPower(2);
      return $lastResult;
    case "SUMMERSFALL":
      if($params[1] != "NONE")
      {
        AddDecisionQueue("PASSPARAMETER", $player, $params[1] . "-" . $params[2]);
        AddDecisionQueue("MZBOTTOM", $player, "-", 1);
      }
      return $lastResult;
    case "BLOODSPATTEREDVEST":
      $char = &GetPlayerCharacter($player);
      $index = FindCharacterIndex($player, "HNT168");
      if($index != -1){
        GainResources($player, 1);
        if(++$char[$index + 2] >= 3) {
          DestroyCharacter($player, $index); # If it has three counters blow it up
          WriteLog(CardLink("HNT168", "HNT168") . " got too dirty...");
        }
      }
      return $lastResult;
    default: return "";
  }

}
function PitchCard($player, $search="MYHAND:pitch=1&MYHAND:pitch=2&MYHAND:pitch=3", $skipGain=false)
{
  if(!$skipGain) PrependDecisionQueue("GAINPITCHVALUE", $player, "-", 1);
  PrependDecisionQueue("PITCHABILITY", $player, "-", 1);
  PrependDecisionQueue("ADDMYPITCH", $player, "-", 1);
  PrependDecisionQueue("REMOVEMYHAND", $player, "-", 1);
  PrependDecisionQueue("CHOOSEHANDCANCEL", $player, "<-", 1);
  PrependDecisionQueue("SETDQCONTEXT", $player, "Choose a card to pitch", 1);
  PrependDecisionQueue("MZOP", $player, "GETCARDINDICES", 1);
  PrependDecisionQueue("MULTIZONEINDICES", $player, $search, 1);
}

function MeldCards($player, $cardID, $lastResult){
  if($lastResult == "Both") $names = explode(" // ", CardName($cardID));
  else $names[] = GamestateUnsanitize($lastResult);
  if($lastResult == "Both") {
    AddLayer("MELD", $player, $cardID);
    $meldState = CardType($cardID);
  }
  else $meldState = "I";
  for ($i=count($names)-1; $i >= 0 ; --$i) { 
    switch ($names[$i]) {
      case "Life":
        GainHealth(1, $player);
        break;
      case "Shock":
        $type = count($names) > 1 && IsDoubleArcane($cardID) ? "ARCANESHOCK" : "PLAYCARD";
        DealArcane(1, 2, $type, $cardID, false, $player, meldState: $meldState);
        break;
      default:
        if($lastResult != "Both") {
          ProcessMeld($player, $cardID, additionalCosts:$lastResult);
        }
      break;
    }
  }
}