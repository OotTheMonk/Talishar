<?php

  function HasCrank($cardID, $player)
  {
    $char = &GetPlayerCharacter($player);
    if(CardNameContains($cardID, "Hyper Driver", $player) && (SearchCharacterForCard($player, "maxx_the_hype_nitro") || SearchCharacterForCard($player, "maxx_nitro")) && $char[1] < 3) return true;
    switch($cardID) {
      case "grinding_gears_blue": case "prismatic_lens_yellow": case "quantum_processor_yellow":
      case "tick_tock_clock_red":
      case "polarity_reversal_script_red": case "penetration_script_yellow": case "security_script_blue":
      case "backup_protocol_red_red": case "backup_protocol_yel_yellow": case "backup_protocol_blu_blue":
      case "boom_grenade_red": case "boom_grenade_yellow": case "boom_grenade_blue":
      case "dissolving_shield_red": case "dissolving_shield_yellow": case "dissolving_shield_blue":
      case "hadron_collider_red": case "hadron_collider_yellow": case "hadron_collider_blue":
      case "mini_forcefield_red": case "mini_forcefield_yellow": case "mini_forcefield_blue":
      case "overload_script_red": case "mhz_script_yellow": case "autosave_script_blue":
      case "cerebellum_processor_blue":
      case "null_time_zone_blue":
        return true;
      default: return false;
    }
  }

  function Crank($player, $index, $mainPhase="True")
  {
    $items = GetItems($player);
    PrependDecisionQueue("PASSPARAMETER", $player, "{0}");
    PrependDecisionQueue("OP", $player, "DOCRANK-MainPhase". $mainPhase, 1);
    PrependDecisionQueue("PASSPARAMETER", $player, $index, 1);
    PrependDecisionQueue("NOPASS", $player, "-");
    PrependDecisionQueue("DOCRANK", $player, "-");
    PrependDecisionQueue("SETDQCONTEXT", $player, "Do you want to Crank your " . CardLink($items[$index], $items[$index]) ."?", 1);
    PrependDecisionQueue("SETDQVAR", $player, "0");
  }

  function DoCrank($player, $index, $mainPhase=true)
  {
    global $CS_NumCranked;
    $items = &GetItems($player);
    if($items[$index+1] <= 0) return;
    --$items[$index+1];
    if($mainPhase) GainActionPoints(1, $player);
    WriteLog("Player $player cranked");
    IncrementClassState($player, $CS_NumCranked);
    if(CardName($items[$index]) == "Hyper Driver" && ($items[$index+1] <= 0)) DestroyItemForPlayer($player, $index);
  }

  function ProcessTowerEffect($cardID)
  {
    global $CombatChain, $mainPlayer, $defPlayer, $layers;
    if(!IsHeroAttackTarget()) return;
    if(CardType($cardID) == "AA" && SearchCurrentTurnEffects("tarpit_trap_yellow", $mainPlayer, count($layers) <= LayerPieces())) {
      WriteLog("Tower effect prevented by " . CardLink("tarpit_trap_yellow", "tarpit_trap_yellow"));
      return true;
    }
    switch($cardID)
    {
      case "colossal_bearing_red": case "colossal_bearing_red":
        AddDecisionQueue("FINDINDICES", $defPlayer, "EQUIP1", 1);
        AddDecisionQueue("CHOOSETHEIRCHARACTER", $mainPlayer, "<-", 1);
        AddDecisionQueue("DESTROYCHARACTER", $defPlayer, "-", 1);
        break;
      case "smack_of_reality_red": case "smack_of_reality_red":
        MZDestroy($mainPlayer, SearchMultizone($mainPlayer, "THEIRAURAS:type=T"), $mainPlayer);
      default: break;
    }
  }

  function Scrap($player)
  {
    global $CS_AdditionalCosts;
    MZMoveCard($player, "MYDISCARD:type=E&MYDISCARD:subtype=Item&MYDISCARD:subtype=Evo", "MYBANISH,GY,-", may:true);
    AddDecisionQueue("APPENDCLASSSTATE", $player, $CS_AdditionalCosts . "-SCRAP", 1);
  }

  function Clash($cardID, $effectController="")
  {
    if(IsAllyAttacking()){
      WriteLog("<span style='color:red;'>No clash is done because there is no attacking hero when allies attack.</span>");
    } else {
      WriteLog("⚔️CLASH⚔️");
      ClashLogic($cardID, $effectController);
      AddDecisionQueue("WONCLASH", 1, $cardID . "," . $effectController);
    }
  }

  function ClashLogic($cardID, $effectController="")
  {
    global $mainPlayer, $defPlayer, $dqVars;
    $p1Power = ""; $p2Power = "";
    for($i=1; $i<=2; ++$i) {
      $deck = new Deck($i);
      if($deck->Reveal()) {
        $power = $deck->Empty() ? 0 : ModifiedAttackValue($deck->Top(), $i, "DECK", source:$cardID);
        if(!TypeContains($deck->Top(), "AA")) $power = ""; //If you reveal a card with {p} and the opponent reveals a card without {p}, you win the clash.
        if($i == 1) $p1Power = $power;
        else $p2Power = $power;
      }
    }
    //DQVAR 0 = Winner
    if($p1Power >= 0 && ($p1Power > $p2Power || $p2Power == "")) {
      $dqVars[0] = 1;
      VictorAbility(2, $cardID, $effectController);
    }
    else if($p2Power >= 0 && ($p2Power > $p1Power || $p1Power == "")) {
      $dqVars[0] = 2;
      VictorAbility(1, $cardID, $effectController);
    }
    else {
      $dqVars[0] = 0;
      VictorAbility($mainPlayer, $cardID, $effectController);
      VictorAbility($defPlayer, $cardID, $effectController);
    }
  }

  function WonClashAbility($playerID, $cardID, $effectController="") {
    global $mainPlayer, $CS_NumClashesWon, $combatChainState, $CCS_WeaponIndex, $dqVars, $defPlayer;
    WriteLog("Player " . $playerID . " won the Clash");
    $numClashesWon = GetClassState($playerID, $CS_NumClashesWon) + 1;
    SetClassState($playerID, $CS_NumClashesWon, $numClashesWon);
    $deck = new Deck($playerID);
    switch ($deck->Top()) {
      case "the_golden_son_yellow":
      case "thunk_red": case "thunk_yellow": case "thunk_blue":
      case "wallop_red": case "wallop_yellow": case "wallop_blue":
        AddLayer("TRIGGER", $playerID, $deck->Top());
        break;
      default:
        break;
    }
    switch($cardID)
    {
      case "millers_grindstone":
        if($deck->Empty()) {
          break;
        }
        if ($playerID == $mainPlayer) DestroyTopCardOpponent($playerID);
        else {
          $character = &GetPlayerCharacter($mainPlayer);
          $index = $combatChainState[$CCS_WeaponIndex];
          --$character[$index + 3];
        }
        break;
      case "stonewall_impasse":
        if($playerID == $defPlayer) AddCurrentTurnEffect($cardID, $defPlayer, "CC");
        break;
      case "trounce_red": break;
      case "trounce_red-0": break;
      case "trounce_red-1":
        if($playerID == 1) {
          PutItemIntoPlayForPlayer("gold", $playerID, effectController:$defPlayer);
          PlayAura("might", $playerID); //Might
          PlayAura("vigor", $playerID); //Vigor
        }
        break;
      case "trounce_red-2":
        if($playerID == 2) {
          PutItemIntoPlayForPlayer("gold", $playerID, effectController:$defPlayer);
          PlayAura("might", $playerID); //Might
          PlayAura("vigor", $playerID); //Vigor
        }
        break;
      case "clash_of_might_red": case "clash_of_might_yellow": case "clash_of_might_blue":
        PlayAura("might", $playerID);//Might
        break;
      case "test_of_might_red":
        PlayAura("might", $playerID);//Might
        break;
      case "clash_of_agility_red": case "clash_of_agility_yellow": case "clash_of_agility_blue":
        PlayAura("agility", $playerID);//Agility
        break;
      case "test_of_agility_red":
        PlayAura("agility", $playerID);
        break;
      case "clash_of_vigor_red": case "clash_of_vigor_yellow": case "clash_of_vigor_blue":
        PlayAura("vigor", $playerID);//Vigor
        break;
      case "test_of_vigor_red": case "rising_energy_red": case "rising_energy_yellow":
        PlayAura("vigor", $playerID);//Vigor
        break;
      case "test_of_strength_red":
        PutItemIntoPlayForPlayer("gold", $playerID, effectController:$effectController);
        break;
      default: break;
    }
    }

  function VictorAbility($playerID, $cardID, $effectController="") {
    $otherPlayer = ($playerID == 1 ? 2 : 1);
    $char = &GetPlayerCharacter($playerID);
    $hero = ShiyanaCharacter($char[0], $playerID);
    if(($hero == "victor_goldmane_high_and_mighty" || $hero == "victor_goldmane") && CountItem("gold", $playerID) > 0 && $char[1] == 2) {
      $char[1] = 1;
      //This all has to be prepend for the case where it's a Victor mirror, one player wins, then the re-do causes that player to win
      PrependDecisionQueue("CLASH", $effectController, $cardID, 1);
      PrependDecisionQueue("ADDBOTTOMREMOVETOP", $otherPlayer, $hero, 1);
      PrependDecisionQueue("NOTEQUALPASS", $playerID, "Target_Opponent");
      PrependDecisionQueue("PASSPARAMETER", $playerID, "{1}");
      PrependDecisionQueue("CLASH", $effectController, $cardID, 1);
      PrependDecisionQueue("ADDBOTTOMREMOVETOP", $playerID, $hero, 1);
      PrependDecisionQueue("EQUALPASS", $playerID, "Target_Opponent", 1);
      PrependDecisionQueue("SETDQVAR", $playerID, "1");
      PrependDecisionQueue("BUTTONINPUT", $playerID, "Target_Opponent,Target_Yourself", 1);
      PrependDecisionQueue("SETDQCONTEXT", $playerID, "Choose target hero", 1);
      if(SearchCharacterAlive($playerID, "aurum_aegis")) {
        PrependDecisionQueue("MZDESTROY", $playerID, "-", 1);
        PrependDecisionQueue("MAYCHOOSEMULTIZONE", $playerID, "<-", 1);
        PrependDecisionQueue("MULTIZONEINDICES", $playerID, "MYITEMS:isSameName=gold&MYCHAR:cardID=aurum_aegis", 1);
      } else PrependDecisionQueue("FINDANDDESTROYITEM", $playerID, "gold-1", 1);
      PrependDecisionQueue("REMOVECURRENTTURNEFFECT", $playerID, $hero."-2", 1);
      PrependDecisionQueue("NOPASS", $playerID, "-", 1);
      PrependDecisionQueue("YESNO", $playerID, "if_you_want_to_destroy_1_" . CardLink("gold", "gold") ."_to_clash_again", 1);
      PrependDecisionQueue("SETDQVAR", $playerID, "1");
      PrependDecisionQueue("PASSPARAMETER", $playerID, "NO");
    }
  }

  function AskWager($cardID) {
    global $currentPlayer;
    AddDecisionQueue("PASSPARAMETER", $currentPlayer, $cardID);
    AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
    AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Do you want to wager for <0>?");
    AddDecisionQueue("YESNO", $currentPlayer, "-");
    AddDecisionQueue("NOPASS", $currentPlayer, "-");
    AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID . "!PLAY", 1);

    AddOnWagerEffects();
  }

  function AddOnWagerEffects($canPass = true) {
    global $currentPlayer, $CCS_WagersThisLink;
    AddDecisionQueue("INCREMENTCOMBATCHAINSTATE", $currentPlayer, $CCS_WagersThisLink, $canPass);
    $char = &GetPlayerCharacter($currentPlayer);
    $cardID = ShiyanaCharacter($char[0]);
    if($char[1] == 2 && ($cardID == "betsy_skin_in_the_game" || $cardID == "betsy")) {
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, $cardID, $canPass ? 1 : 0);
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Do you want to pay 2 for <0> to give overpower and +1?", 1);
      AddDecisionQueue("YESNO", $currentPlayer, "-", 1, 1);
      AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, 2, 1);
      AddDecisionQueue("PAYRESOURCES", $currentPlayer, "-", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
    }
  }

  function ResolveWagers() {
    global $mainPlayer, $defPlayer, $combatChainState, $CCS_DamageDealt, $currentTurnEffects, $EffectContext, $combatChain;
    $wonWager = $combatChainState[$CCS_DamageDealt] > 0 ? $mainPlayer : $defPlayer;
    $lostWager = $wonWager == $mainPlayer ? $defPlayer : $mainPlayer;
    $numWagersWon = 0;
    $amount = 1;
    if(isset($combatChain[0])) $EffectContext = $combatChain[0];
    if(SearchCurrentTurnEffects("double_down_red", $wonWager)) $amount += CountCurrentTurnEffects("double_down_red", $wonWager);
    for($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
      $hasWager = true;
      if(isset($currentTurnEffects[$i])) {
        switch($currentTurnEffects[$i]) {
          case "good_time_chapeau":
            RemoveCurrentTurnEffect($i);
            PlayAura("might", $wonWager, $amount);//Might
            PlayAura("vigor", $wonWager, $amount);//Vigor
            break;
          case "bet_big_red":
            RemoveCurrentTurnEffect($i);
            PutItemIntoPlayForPlayer("gold", $wonWager, number:$amount, effectController:$mainPlayer);//Gold
            PlayAura("might", $wonWager, $amount);//Might
            PlayAura("vigor", $wonWager, $amount);//Vigor
            break;
          case "big_bop_red": case "big_bop_yellow": case "big_bop_blue":
            RemoveCurrentTurnEffect($i);
            PlayAura("vigor", $wonWager, $amount);//Vigor
            break;
          case "bigger_than_big_red": case "bigger_than_big_yellow": case "bigger_than_big_blue":
            RemoveCurrentTurnEffect($i);
            PlayAura("might", $wonWager, $amount);//Might
            break;
          case "prized_galea":
            RemoveCurrentTurnEffect($i);
            PutItemIntoPlayForPlayer("gold", $wonWager, number:$amount, effectController:$mainPlayer);//Gold
            break;
          case "up_the_ante_blue-1":
            RemoveCurrentTurnEffect($i);
            PlayAura("agility", $wonWager, $amount);//Agility
            break;
          case "up_the_ante_blue-2":
            RemoveCurrentTurnEffect($i);
            PutItemIntoPlayForPlayer("gold", $wonWager, number:$amount, effectController:$mainPlayer);//Gold
            break;
          case "up_the_ante_blue-3":
            RemoveCurrentTurnEffect($i);
            PlayAura("vigor", $wonWager, $amount);//Vigor
            break;
          case "edge_ahead_red": case "edge_ahead_yellow": case "edge_ahead_blue":
            RemoveCurrentTurnEffect($i);
            PlayAura("agility", $wonWager, $amount);//Agility
            break;
          case "hold_em_red": case "hold_em_yellow": case "hold_em_blue":
            RemoveCurrentTurnEffect($i);
            PlayAura("vigor", $wonWager, $amount);//Vigor
            break;
          case "wage_might_red": case "wage_might_yellow": case "wage_might_blue":
            RemoveCurrentTurnEffect($i);
            PlayAura("might", $wonWager, $amount);//Might
            break;
          case "wage_agility_red": case "wage_agility_yellow": case "wage_agility_blue":
            RemoveCurrentTurnEffect($i);
            PlayAura("agility", $wonWager, $amount);//Agility
            break;
          case "wage_vigor_red": case "wage_vigor_yellow": case "wage_vigor_blue":
            RemoveCurrentTurnEffect($i);
            PlayAura("vigor", $wonWager, $amount);//Vigor
            break;
          case "wage_gold_red": case "wage_gold_yellow": case "wage_gold_blue":
            RemoveCurrentTurnEffect($i);
            PutItemIntoPlayForPlayer("gold", $wonWager, number:$amount, effectController:$mainPlayer);//Gold
            break;
          case "money_where_ya_mouth_is_red": case "money_where_ya_mouth_is_yellow": case "money_where_ya_mouth_is_blue":
            RemoveCurrentTurnEffect($i);
            PutItemIntoPlayForPlayer("gold", $wonWager, number:$amount, effectController:$mainPlayer);//Gold
            break;
          case "drink_em_under_the_table_red":
            RemoveCurrentTurnEffect($i);
            Draw($wonWager);
            PummelHit($lostWager);
            break;
          default:
            $hasWager = false;
            break;
        }
      }
      if($hasWager) ++$numWagersWon;
    }
    if($numWagersWon > 0 && $wonWager == $mainPlayer) {
      $char = &GetPlayerCharacter($mainPlayer);
      $hero = ShiyanaCharacter($char[0]);
      if($char[1] == 2 && ($hero == "olympia_prized_fighter" || $hero == "olympia")) {
        PutItemIntoPlayForPlayer("gold", $mainPlayer, effectController:$mainPlayer);//Gold
        WriteLog(CardLink($hero, $hero) . " wins the favor of the crowd!");
      }
    }
  }

  function HasUniversal($cardID)
  {
    switch($cardID) {
      case "wage_gold_red": case "wage_gold_yellow": case "wage_gold_blue": return true;
      default: break;
    }
    return false;
  }

  function Transcend($player, $cardID, $from)
  {
    global $currentPlayer, $CS_Transcended;
    $otherplayer = $player == 1 ? 2 : 1;
    SetClassState($player, $CS_Transcended, 1);
    if(SearchCharacterAlive($player, "twelve_petal_kasaya")) {
      AddDecisionQueue("YESNO", $player, "if you want to gain a resource");
      AddDecisionQueue("NOPASS", $player, "-");
      AddDecisionQueue("GAINRESOURCES", $player, "1", 1);
    }
    WriteLog("HERE: $cardID");
    if(substr($from, 0, 5) == "THEIR") AddPlayerHand($cardID, $otherplayer, "-");
    else AddPlayerHand($cardID, $player, "-");
  }

  /**
   * Decompose is a keyword added in ROS. Per LSS decompose gives you an option to banishes 2 earth and 1 action card for a bonus effect
   *
   * The result of "NOPASS" should be used to add the bonus effects. SPECIFICCARD dq events can be added right after calling decompose to run if the decompose succeeded.
   */
  function Decompose($player, $specificCardDQ, $target = "") {
    $actionBanishes = 1;
    $earthBanishes = 2; 
      // Earth Banishes
      for($i = 0; $i < $earthBanishes; $i++) {
        AddDecisionQueue("MULTIZONEINDICES", $player, "MYDISCARD:talent=EARTH", 1);
        AddDecisionQueue("SETDQCONTEXT", $player, "Choose " . ($earthBanishes - $i) . " Earth card(s) to banish", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
        AddDecisionQueue("MZBANISH", $player, "GY,-", 1);
        AddDecisionQueue("MZREMOVE", $player, "-", 1);
      }

      // Action banishes.
      for($i = 0; $i < $actionBanishes; $i++) {
        AddDecisionQueue("GETCARDSFORDECOMPOSE", $player, "MYDISCARD:type=A&MYDISCARD:type=AA", 1); // Modified MULTIZONEINDICES so if there are no actions it can be sent to the next dq and it will revert gamestate. Can't use "PASS" because YESNO "PASS" result is already present.
        AddDecisionQueue("REVERTGAMESTATEIFNULL", $player, "There aren't any more action cards! Try selecting different Earth cards.", 1);
        AddDecisionQueue("SETDQCONTEXT", $player, "Choose " . ($actionBanishes - $i) . " action card(s) to banish", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
        AddDecisionQueue("MZBANISH", $player, "GY,-", 1);
        AddDecisionQueue("MZREMOVE", $player, "-", 1);
      }
      AddDecisionQueue("SPECIFICCARD", $player, $specificCardDQ . "-" . $target, 1);
      return "";
  }
