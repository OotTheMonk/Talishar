<?php

function AKOPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
{
  global $currentPlayer;
  switch ($cardID) {
    case "savage_sash":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    default:
      return "";
  }
}

function ASBPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
{
  global $currentPlayer, $CS_NumCharged, $CombatChain;
  switch ($cardID) {
    case "solar_plexus":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "saving_grace_yellow":
      if (GetClassState($currentPlayer, $CS_NumCharged) > 0) $CombatChain->Card(0)->ModifyPower(-2);
      return "";
    default:
      return "";
  }
}

function HVYPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
{
  global $currentPlayer, $chainLinks, $defPlayer, $CS_NumCardsDrawn, $CS_HighestRoll, $CombatChain, $CS_NumMightDestroyed, $CS_DieRoll;
  $otherPlayer = $currentPlayer == 1 ? 2 : 1;
  $rv = "";
  switch ($cardID) {
    case "mini_meataxe":
      Draw($currentPlayer);
      DiscardRandom();
      return "";
    case "knucklehead":
      $roll = GetDieRoll($currentPlayer);
      AddCurrentTurnEffect($cardID . "-" . $roll, $currentPlayer);
      return "Rolled $roll and your intellect is " . $roll . " until the end of turn.";
    case "monstrous_veil":
      Draw($currentPlayer);
      DiscardRandom($currentPlayer, $cardID);
      return "";
    case "send_packing_yellow":
      if (IsHeroAttackTarget()) {
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRARS");
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZBANISH", $currentPlayer, "CC," . $cardID, 1);
        AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
      } else {
        WriteLog("<span style='color:red;'>No arsenal is banished because it does not attack a hero.</span>");
      }
      return "";
    case "show_no_mercy_red":
      if (IsHeroAttackTarget()) Intimidate();
      return "";
    case "cast_bones_red":
      $deck = new Deck($currentPlayer);
      if ($deck->Reveal(6)) {
        $cards = explode(",", $deck->Top(amount: 6));
        $numSixes = 0;
        for ($i = 0; $i < count($cards); ++$i) {
          if (ModifiedAttackValue($cards[$i], $currentPlayer, "DECK") >= 6) ++$numSixes;
        }
        PlayAura("might", $currentPlayer, $numSixes); //Might
        if (CountAura("might", $currentPlayer) >= 6) PlayAura("agility", $currentPlayer); //Agility

        $zone = &GetDeck($currentPlayer);
        $topDeck = array_slice($zone, 0, 6);
        shuffle($topDeck);
        for ($i = 0; $i < count($topDeck); ++$i) {
          $zone[$i] = $topDeck[$i];
        }
      }
      return "";
    case "reckless_charge_blue":
      RollDie($currentPlayer);
      $roll = GetClassState($currentPlayer, $CS_DieRoll);
      GainActionPoints(intval($roll / 2), $currentPlayer);
      if (GetClassState($currentPlayer, $CS_HighestRoll) == 6) Draw($currentPlayer);
      return "Rolled $roll and gained " . intval($roll / 2) . " action points";
    case "no_fear_red":
      AddCurrentTurnEffect($cardID . "-" . $additionalCosts, $currentPlayer);
      return "";
    case "rawhide_rumble_red":
    case "rawhide_rumble_yellow":
    case "rawhide_rumble_blue":
      if (SearchCurrentTurnEffects("BEATCHEST", $currentPlayer) && IsHeroAttackTarget()) Intimidate();
      return "";
    case "assault_and_battery_red":
    case "assault_and_battery_yellow":
    case "assault_and_battery_blue":
      if (SearchCurrentTurnEffects("BEATCHEST", $currentPlayer)) PlayAura("agility", $currentPlayer);//Agility
      return "";
    case "pound_town_red":
    case "pound_town_yellow":
    case "pound_town_blue":
      if (SearchCurrentTurnEffects("BEATCHEST", $currentPlayer)) PlayAura("might", $currentPlayer);//Might
      return "";
    case "bonebreaker_bellow_red":
    case "bonebreaker_bellow_yellow":
    case "bonebreaker_bellow_blue":
      if ($cardID == "bonebreaker_bellow_red") $amount = 3;
      else if ($cardID == "bonebreaker_bellow_yellow") $amount = 2;
      else if ($cardID == "bonebreaker_bellow_blue") $amount = 1;
      if (SearchCurrentTurnEffects("BEATCHEST", $currentPlayer)) $amount += 2;
      AddCurrentTurnEffect($cardID . "," . $amount, $currentPlayer);
      return "";
    case "smashback_alehorn_blue":
      PlayAura("agility", $currentPlayer);//Agility
      PlayAura("might", $currentPlayer);//Might
      return "";
    case "good_time_chapeau":
      AddCurrentTurnEffect($cardID . "-PAID", $currentPlayer);
      return "";
    case "bet_big_red":
      if (IsHeroAttackTarget()) AskWager($cardID);
      return "";
    case "primed_to_fight_red":
      if (GetClassState($currentPlayer, $CS_NumMightDestroyed) > 0 || SearchAurasForCard("might", $currentPlayer)) AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "lay_down_the_law_red":
      AddCurrentTurnEffect($cardID, $defPlayer);
      return "";
    case "pint_of_strong_and_stout_blue":
      PlayAura("might", $currentPlayer);//Might
      PlayAura("vigor", $currentPlayer);//Vigor
      return "";
    case "goblet_of_bloodrun_wine_blue":
      PlayAura("agility", $currentPlayer);//Agility
      PlayAura("vigor", $currentPlayer);//Vigor
      return "";
    case "kassai_of_the_golden_sand":
    case "kassai":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "prized_galea":
      if (IsHeroAttackTarget()) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
        AddOnWagerEffects();
      }
      return "";
    case "hood_of_red_sand":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "blade_flurry_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      AddCurrentTurnEffectFromCombat($cardID, $currentPlayer);
      return "";
    case "shift_the_tide_of_battle_yellow":
      if (CachedTotalAttack() > AttackValue($CombatChain->AttackCard()->ID())) {
        GiveAttackGoAgain();
        AddCurrentTurnEffect($cardID, $currentPlayer);
      }
      return "";
    case "up_the_ante_blue":
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts, 1);
      AddDecisionQueue("MODAL", $currentPlayer, "UPTHEANTE", 1);
      return "";
    case "commanding_performance_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      AddCurrentTurnEffect($cardID . "-BUFF", $currentPlayer);
      return "";
    case "raise_an_army_yellow":
      PlayAlly("cintari_sellsword", $currentPlayer, number: intval($additionalCosts));
      return "";
    case "cut_the_deck_red":
    case "cut_the_deck_yellow":
    case "cut_the_deck_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      if (NumAttacksBlocking() > 0) {
        Draw($currentPlayer);
        $hand = &GetHand($currentPlayer);
        $arsenal = GetArsenal($currentPlayer);
        if (count($hand) + count($arsenal) == 1) {
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Here's the card that goes on the bottom of your deck.", 1);
          AddDecisionQueue("OK", $currentPlayer, "-");
        }
        if (count($hand) + count($arsenal) > 0) MZMoveCard($currentPlayer, "MYHAND&MYARS", "MYBOTDECK", silent: true);
      }
      return "";
    case "fatal_engagement_red":
    case "fatal_engagement_yellow":
    case "fatal_engagement_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "agile_engagement_red":
    case "agile_engagement_yellow":
    case "agile_engagement_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      if (NumAttacksBlocking() > 0) PlayAura("agility", $currentPlayer); //Agility
      return "";
    case "vigorous_engagement_red":
    case "vigorous_engagement_yellow":
    case "vigorous_engagement_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      if (NumAttacksBlocking() > 0) PlayAura("vigor", $currentPlayer); //Vigor
      return "";
    case "draw_swords_red":
    case "draw_swords_yellow":
    case "draw_swords_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      Draw($currentPlayer);
      return "";
    case "edge_ahead_red":
    case "edge_ahead_yellow":
    case "edge_ahead_blue":
      AddCurrentTurnEffect($cardID . "-BUFF", $currentPlayer);
      return "";
    case "engaged_swiftblade_red":
    case "engaged_swiftblade_yellow":
    case "engaged_swiftblade_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "hold_em_red":
    case "hold_em_yellow":
    case "hold_em_blue":
      AddCurrentTurnEffect($cardID . "-BUFF", $currentPlayer);
      return "";
    case "gauntlet_of_might":
      PlayAura("might", $currentPlayer); //Might
      return "";
    case "talk_a_big_game_blue":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a number");
      AddDecisionQueue("BUTTONINPUT", $currentPlayer, "0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20");
      AddDecisionQueue("WRITELOGLASTRESULT", $currentPlayer, "-", 1);
      AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "talk_a_big_game_blue,");
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "<-");
      return "";
    case "battered_not_broken_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "mighty_windup_red":
    case "mighty_windup_yellow":
    case "mighty_windup_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I") {
        PlayAura("might", $currentPlayer); //Might
        CardDiscarded($currentPlayer, $cardID, source: $cardID);
      }
      return "";
    case "wage_might_red":
    case "wage_might_yellow":
    case "wage_might_blue":
      if (IsHeroAttackTarget()) AskWager($cardID);
      return "";
    case "lead_with_power_red":
    case "lead_with_power_yellow":
    case "lead_with_power_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      PlayAura("might", $currentPlayer); //Might
      return "";
    case "flat_trackers":
      PlayAura("agility", $currentPlayer); //Agility
      return "";
    case "runner_runner_red":
      if (DoesAttackHaveGoAgain()) PlayAura("agility", $currentPlayer); //Agility
      return "";
    case "take_it_on_the_chin_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "agile_windup_red":
    case "agile_windup_yellow":
    case "agile_windup_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I") {
        PlayAura("agility", $currentPlayer); //Agility
        CardDiscarded($currentPlayer, $cardID, source: $cardID);
      }
      return "";
    case "wage_agility_red":
    case "wage_agility_yellow":
    case "wage_agility_blue":
      if (IsHeroAttackTarget()) AskWager($cardID);
      return "";
    case "lead_with_speed_red":
    case "lead_with_speed_yellow":
    case "lead_with_speed_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      PlayAura("agility", $currentPlayer); //Agility
      return "";
    case "vigor_girth":
      PlayAura("vigor", $currentPlayer); //Vigor
      return "";
    case "double_down_red":
      AddCurrentTurnEffect($cardID . "-BUFF", $currentPlayer);
      AddCurrentTurnEffect($cardID, $currentPlayer);
      AddCurrentTurnEffect($cardID, $otherPlayer);
      return "";
    case "slaphappy_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "vigorous_windup_red":
    case "vigorous_windup_yellow":
    case "vigorous_windup_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I") {
        PlayAura("vigor", $currentPlayer); //Vigor
        CardDiscarded($currentPlayer, $cardID, source: $cardID);
      }
      return "";
    case "wage_vigor_red":
    case "wage_vigor_yellow":
    case "wage_vigor_blue":
      if (IsHeroAttackTarget()) AskWager($cardID);
      return "";
    case "lead_with_heart_red":
    case "lead_with_heart_yellow":
    case "lead_with_heart_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      PlayAura("vigor", $currentPlayer); //Vigor
      return "";
    case "balance_of_justice":
      Draw($currentPlayer);
      return "";
    case "glory_seeker":
      Draw($currentPlayer);
      return "";
    case "sheltered_cove":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "ripple_away_blue":
      if (GetResolvedAbilityType($cardID, "HAND") == "I") {
        AddCurrentTurnEffect($cardID, $currentPlayer, $from);
      }
      return "";
    case "standing_order_red":
      MZMoveCard($currentPlayer, "MYARS", "MYBOTDECK", may: true, silent: true);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
      return "";
    case "tenacity_yellow":
      $buff = NumCardsBlocking();
      for ($i = 0; $i < count($chainLinks); ++$i) {
        for ($j = 0; $j < count($chainLinks[$i]); $j += ChainLinksPieces()) {
          if ($chainLinks[$i][$j + 1] == $defPlayer && $chainLinks[$i][$j+2] == 1) ++$buff;
        }
      }
      AddCurrentTurnEffect($cardID . "," . $buff, $currentPlayer);
      return "";
    case "seduce_secrets_yellow":
      LookAtTopCard($currentPlayer, $cardID, showHand: true);
      if ($from == "ARS") AddDecisionQueue("DRAW", $currentPlayer, "-");
      return "";
    case "down_but_not_out_red":
    case "down_but_not_out_yellow":
    case "down_but_not_out_blue":
      if (IsHeroAttackTarget() && PlayerHasLessHealth($currentPlayer) && GetPlayerNumEquipment($currentPlayer) < GetPlayerNumEquipment($otherPlayer) && GetPlayerNumTokens($currentPlayer) < GetPlayerNumTokens($otherPlayer)) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
      }
      return "";
    case "wage_gold_red":
    case "wage_gold_yellow":
    case "wage_gold_blue":
      if (IsHeroAttackTarget()) AskWager($cardID);
      return "";
    case "performance_bonus_red":
    case "performance_bonus_yellow":
    case "performance_bonus_blue":
      if ($from == "ARS") GiveAttackGoAgain();
      return "";
    case "money_where_ya_mouth_is_red":
    case "money_where_ya_mouth_is_yellow":
    case "money_where_ya_mouth_is_blue":
      AddCurrentTurnEffect($cardID . "-BUFF", $currentPlayer);
      return "";
    case "starting_stake_yellow":
      if (CountItem("gold", $currentPlayer, false) == 0) {
        PutItemIntoPlayForPlayer("gold", $currentPlayer, effectController: $currentPlayer);
        WriteLog(CardLink($cardID, $cardID) . " created a Gold token");
      }
      return;
    case "graven_call":
      if ($from == "GY") {
        $character = &GetPlayerCharacter($currentPlayer);
        $uniqueID = EquipWeapon($currentPlayer, "graven_call");
        for ($i = 0; $i < count($character); $i += CharacterPieces()) {
          if ($character[$i + 11] == $uniqueID) {
            if ($character[$i + 3] == 0) {
              ++$character[$i + 3];
            } else {
              ++$character[$i + 15];
            }
          }
        }
      }
      return "";
    case "coercive_tendency_blue":
      if (IsHeroAttackTarget()) {
        $deck = new Deck($otherPlayer);
        if ($deck->RemainingCards() > 0) {
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to put on top of their deck");
          AddDecisionQueue("CHOOSETOPOPPONENT", $currentPlayer, $deck->Top(true, 3));
          AddDecisionQueue("FINDINDICES", $otherPlayer, "TOPDECK", 1);
          AddDecisionQueue("MULTIREMOVEDECK", $otherPlayer, "<-", 1);
          AddDecisionQueue("MULTIBANISH", $otherPlayer, "DECK," . $cardID . "," . $currentPlayer);
        }
      }
      return "";
    case "ancestral_harmony_blue":
      $deck = new Deck($currentPlayer);
      $banishMod = "-";
      if (HasCombo($deck->Top())) $banishMod = "TT";
      $deck->BanishTop($banishMod, $currentPlayer);
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "sonata_galaxia_red":
      $xVal = $resourcesPaid / 2;
      MZMoveCard($currentPlayer, "MYDECK:maxCost=" . $xVal . ";subtype=Aura;class=RUNEBLADE", "MYAURAS", may: true);
      AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
      if ($xVal >= 2) {
        global $CS_NextNAACardGoAgain;
        SetClassState($currentPlayer, $CS_NextNAACardGoAgain, 1);
      }
      return "";
    case "aether_arc_blue":
      DealArcane(1, 1, "PLAYCARD", $cardID);
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "AERTHERARC");
      return "";
    case "dissolve_reality_yellow":
      for ($i = 1; $i < 3; $i += 1) {
        $arsenal = &GetArsenal($i);
        for ($j = 0; $j < count($arsenal); $j += ArsenalPieces()) {
          AddDecisionQueue("FINDINDICES", $i, "ARSENAL");
          AddDecisionQueue("CHOOSEARSENAL", $i, "<-", 1);
          AddDecisionQueue("REMOVEARSENAL", $i, "-", 1);
          AddDecisionQueue("ADDBOTDECK", $i, "-", 1);
        }
        PlayAura("ponder", $i);
      }
      return "";
    case "reel_in_blue":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Here are the top " . ($resourcesPaid + 1) . " cards of your deck.", 1);
      AddDecisionQueue("FINDINDICES", $currentPlayer, "DECKTOPXINDICES," . ($resourcesPaid + 1));
      AddDecisionQueue("DECKCARDS", $currentPlayer, "<-", 1);
      AddDecisionQueue("LOOKTOPDECK", $currentPlayer, "-", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, CardLink($cardID, $cardID) . " shows the top cards of your deck are", 1);
      AddDecisionQueue("MULTISHOWCARDSDECK", $currentPlayer, "<-", 1);
      AddDecisionQueue("FINDINDICES", $currentPlayer, "DECKTOPXINDICES," . ($resourcesPaid + 1));
      AddDecisionQueue("DECKCARDS", $currentPlayer, "<-", 1);
      AddDecisionQueue("TOPDECKCHOOSE", $currentPlayer, $resourcesPaid + 1 . ",Trap", 1);
      AddDecisionQueue("MULTICHOOSEDECK", $currentPlayer, "<-", 1);
      AddDecisionQueue("MULTIREMOVEDECK", $currentPlayer, "-", 1);
      AddDecisionQueue("MULTIADDHAND", $currentPlayer, "-", 1);
      AddDecisionQueue("REVEALCARDS", $currentPlayer, "-", 1);
      AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
      Reload();
      return "";
    default:
      return "";
  }
}

function TCCPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
{
  global $mainPlayer, $currentPlayer, $defPlayer;
  $rv = "";
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  switch ($cardID) {
    case "lay_down_the_law_red":
      AddCurrentTurnEffect($cardID, $defPlayer);
      return "";
    case "jinglewood_smash_hit":
      $abilityType = GetResolvedAbilityType($cardID);
      $character = &GetPlayerCharacter($currentPlayer);
      $charIndex = FindCharacterIndex($mainPlayer, $cardID);
      if ($abilityType == "A") {
        AddDecisionQueue("SETDQCONTEXT", $otherPlayer, "Choose a token to create");
        AddDecisionQueue("MULTICHOOSETEXT", $otherPlayer, "1-Might (+1),Vigor (Resource),Quicken (Go Again)-1");
        AddDecisionQueue("SHOWMODES", $otherPlayer, $cardID, 1);
        AddDecisionQueue("MODAL", $otherPlayer, "JINGLEWOOD", 1);
        PutItemIntoPlayForPlayer("copper", $currentPlayer);
        --$character[$charIndex + 5];
      }
      return "";
    case "nom_de_plume":
      Draw(1);
      Draw(2);
      return "";
    case "heartthrob":
      PlayAura("vigor", 1);
      PlayAura("vigor", 2);
      return "";
    case "fiddledee":
      PlayAura("might", 1);
      PlayAura("might", 2);
      return "";
    case "quickstep":
      PlayAura("quicken", 1);
      PlayAura("quicken", 2);
      return "";
    case "final_act_red":
      $numPitch = SearchCount(SearchPitch($currentPlayer)) + SearchCount(SearchPitch($otherPlayer));
      AddCurrentTurnEffect($cardID . "," . ($numPitch * 2), $currentPlayer);
      return "";
    case "interlude_red":
    case "interlude_yellow":
    case "interlude_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "encore_yellow":
      MZMoveCard($currentPlayer, "MYDISCARD:class=BARD;type=AA", "MYHAND", may: false, isSubsequent: false);
      return "";
    case "song_of_jackbequick_blue":
      PlayAura("quicken", $otherPlayer);
      return "";
    case "song_of_sweet_nectar_blue":
      GainHealth(1, $otherPlayer);
      return "";
    case "song_of_the_rosen_matador_blue":
      PlayAura("vigor", $otherPlayer);
      return "";
    case "song_of_the_shining_knight_blue":
      PlayAura("might", $otherPlayer);
      return "";
    case "song_of_the_wandering_mind_blue":
      Draw($otherPlayer);
      return "";
    case "song_of_yesteryears_blue":
      MZMoveCard($otherPlayer, "MYDISCARD:type=AA", "MYBOTDECK", may: true);
      return "";
    case "mask_of_three_tails":
      Draw($currentPlayer);
      return "";
    case "blood_scent":
      GainResources($currentPlayer, 1);
      return "";
    case "pouncing_paws":
      BanishCardForPlayer("crouching_tiger", $currentPlayer, "-", "TT", $currentPlayer);
      return "";
    case "growl_red":
    case "growl_yellow":
      AddCurrentTurnEffectFromCombat($cardID, $currentPlayer);
      break;
    default:
      return "";
  }
}

function EVOPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
{
  global $mainPlayer, $currentPlayer, $defPlayer, $layers, $combatChain, $CCS_RequiredNegCounterEquipmentBlock, $combatChainState;
  global $CS_NamesOfCardsPlayed, $CS_NumBoosted, $CS_PlayIndex, $CS_NumItemsDestroyed, $CS_DamagePrevention;
  $rv = "";
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  $character = &GetPlayerCharacter($currentPlayer);
  switch ($cardID) {
    case "maxx_the_hype_nitro":
    case "maxx_nitro":
      PutItemIntoPlayForPlayer("hyper_driver", $currentPlayer, 2);
      --$character[5];
      return "";
    case "teklovossen_esteemed_magnate":
    case "teklovossen":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      --$character[5];
      return "";
    case "singularity_red":
      $conditionsMet = CheckIfSingularityConditionsAreMet($currentPlayer);
      if ($conditionsMet != "") return $conditionsMet;
      $char = &GetPlayerCharacter($currentPlayer);
      // We don't want function calls in every iteration check
      $charCount = count($char);
      $charPieces = CharacterPieces();
      AddSoul($char[0], $currentPlayer, "-");
      if (isSubcardEmpty($char, 0)) $char[10] = $char[0];
      else $char[10] = $char[10] . "," . $char[0];
      $char[0] = "teklovossen_the_mechropotent";
      $char[1] = 2;
      $char[2] = 0;
      $char[3] = 0;
      $char[4] = 0;
      $char[5] = 999; // Remove the 'Once per Turn' limitation from Teklovossen
      $char[6] = 0;
      $char[7] = 0;
      $char[8] = 0;
      $char[9] = 2;
      $char[11] = GetUniqueId("teklovossen_the_mechropotent", $currentPlayer);
      $mechropotentIndex = 0; // we pushed it, so should be the last element
      for ($i = $charCount - $charPieces; $i >= 0; $i -= $charPieces) {
        if ($char[$i] != "teklovossen_the_mechropotent" && $char[$i] != "NONE00") {
          EvoTransformAbility("teklovossen_the_mechropotent", $char[$i], $currentPlayer);
          RemoveCharacterAndAddAsSubcardToCharacter($currentPlayer, $i, $mechropotentIndex);
        }
      }
      PutCharacterIntoPlayForPlayer("teklovossen_the_mechropotentb", $currentPlayer);
      return "";
    case "adaptive_plating":
      ModularMove($cardID, $additionalCosts);
      return "";
    case "cogwerx_base_head":
      MZMoveCard($mainPlayer, "MYBANISH:class=MECHANOLOGIST;type=AA", "MYTOPDECK", isReveal: true);
      AddDecisionQueue("SHUFFLEDECK", $mainPlayer, "-", 1);
      return "";
    case "cogwerx_base_chest":
      AddDecisionQueue("GAINRESOURCES", $mainPlayer, "2");
      return "";
    case "cogwerx_base_arms":
      AddCurrentTurnEffectNextAttack($cardID, $mainPlayer);
      return "";
    case "cogwerx_base_legs":
      AddDecisionQueue("GAINACTIONPOINTS", $mainPlayer, "1");
      return "";
    case "evo_circuit_breaker_red":
    case "evo_atom_breaker_red":
    case "evo_face_breaker_red":
    case "evo_mach_breaker_red":
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, "-");
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "EVOBREAKER");
      return "Light up the gem under the equipment when you want to use the conditional effect❗";
    case "demolition_protocol_red":
      if (IsHeroAttackTarget() && EvoUpgradeAmount($mainPlayer) > 0) {
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRITEMS:hasSteamCounter=true&THEIRCHAR:hasSteamCounter=true");
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "MAXCOUNT-" . EvoUpgradeAmount($mainPlayer) . ",MINCOUNT-" . 0 . ",", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose up to " . EvoUpgradeAmount($currentPlayer) . " card" . (EvoUpgradeAmount($mainPlayer) > 1 ? "s" : "") . " to remove all steam counters from.", 1);
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZREMOVECOUNTER", $currentPlayer, "<-");
      }
      return "";
    case "pulsewave_protocol_yellow":
      if (IsHeroAttackTarget() && EvoUpgradeAmount($currentPlayer) > 0) {
        $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
        AddDecisionQueue("PASSPARAMETER", $otherPlayer, EvoUpgradeAmount($currentPlayer), 1);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
        AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
        AddDecisionQueue("APPENDLASTRESULT", $otherPlayer, "-{0}", 1);
        AddDecisionQueue("PREPENDLASTRESULT", $otherPlayer, "{0}-", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose " . EvoUpgradeAmount($currentPlayer) . " card(s)", 1);
        AddDecisionQueue("MULTICHOOSEHAND", $otherPlayer, "<-", 1);
        AddDecisionQueue("IMPLODELASTRESULT", $otherPlayer, ",", 1);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "1");
        AddDecisionQueue("REVEALHANDCARDS", $otherPlayer, "<-", 1);
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "{1}", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card", 1);
        AddDecisionQueue("SPECIFICCARD", $otherPlayer, "PULSEWAVEPROTOCOLFILTER", 1);
        AddDecisionQueue("CHOOSETHEIRHAND", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEHAND", $otherPlayer, "-", 1);
        AddDecisionQueue("ADDCARDTOCHAINASDEFENDINGCARD", $otherPlayer, "HAND", 1);
      }
      return "";
    case "meganetic_protocol_blue":
      $negCounterEquip = explode(",", SearchCharacter($otherPlayer, hasNegCounters: true));
      $numNegCounterEquip = count($negCounterEquip);
      if ($numNegCounterEquip > EvoUpgradeAmount($currentPlayer)) $requiredEquip = EvoUpgradeAmount($currentPlayer);
      else $requiredEquip = $numNegCounterEquip;
      if ($numNegCounterEquip > 0 && $requiredEquip > 0 && !IsAllyAttackTarget()) {
        $combatChainState[$CCS_RequiredNegCounterEquipmentBlock] = $requiredEquip;
        if ($requiredEquip > 1) $rv = CardLink($cardID, $cardID) . " requires you to block with " . $requiredEquip . " equipments";
        else $rv = CardLink($cardID, $cardID) . " requires you to block with " . $requiredEquip . " equipment";
        WriteLog($rv);
      }
      return "";
    case "grinding_gears_blue":
      if ($from == "PLAY") DestroyTopCardTarget($currentPlayer);
      break;
    case "prismatic_lens_yellow":
      if ($from == "PLAY") {
        $deck = new Deck($currentPlayer);
        $deck->Reveal();
        $pitchValue = PitchValue($deck->Top());
        MZMoveCard($currentPlayer, ("MYBANISH:class=MECHANOLOGIST;subtype=Item;pitch=" . $pitchValue), "MYTOPDECK", may: true, isReveal: true);
      }
      break;
    case "quantum_processor_yellow":
      if ($from == "PLAY") {
        MZMoveCard($currentPlayer, "MYHAND:class=MECHANOLOGIST;subtype=Item;maxCost=1", "", may: true);
        AddDecisionQueue("PUTPLAY", $currentPlayer, "0", 1);
      }
      break;
    case "stasis_cell_blue":
      if ($from == "PLAY") {
        $index = GetClassState($currentPlayer, $CS_PlayIndex);
        RemoveItem($currentPlayer, $index);
        $deck = new Deck($currentPlayer);
        $deck->AddBottom($cardID, from: "PLAY");
        AddDecisionQueue("FINDINDICES", $otherPlayer, "EQUIP");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose target equipment it cannot defend this turn");
        AddDecisionQueue("CHOOSETHEIRCHARACTER", $currentPlayer, "<-", 1);
        AddDecisionQueue("EQUIPCANTDEFEND", $otherPlayer, "stasis_cell_blue-B-", 1);
      }
      break;
    case "fuel_injector_blue":
      if ($from == "PLAY") GainResources($currentPlayer, 1);
      return "";
    case "medkit_blue":
      if ($from == "PLAY") GainHealth(2, $currentPlayer);
      return "";
    case "steam_canister_blue":
      if ($from == "PLAY") {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card with Crank to get a steam counter", 1);
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYITEMS:hasCrank=true");
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZADDCOUNTER", $currentPlayer, "-", 1);
      }
      return "";
    case "penetration_script_yellow":
      if ($currentPlayer == $defPlayer) {
        for ($j = CombatChainPieces(); $j < count($combatChain); $j += CombatChainPieces()) {
          if ($combatChain[$j + 1] != $currentPlayer) continue;
          if (CardType($combatChain[$j]) == "AA" && ClassContains($combatChain[$j], "MECHANOLOGIST", $currentPlayer)) CombatChainPowerModifier($j, 1);
        }
      }
      break;
    case "backup_protocol_red_red":
    case "backup_protocol_yel_yellow":
    case "backup_protocol_blu_blue":
      if ($from == "PLAY") {
        MZMoveCard($currentPlayer, "MYDISCARD:pitch=" . PitchValue($cardID) . ";type=AA;class=MECHANOLOGIST", "MYHAND", may: true);
      }
      return "";
    case "dissolving_shield_red":
    case "dissolving_shield_yellow":
    case "dissolving_shield_blue":
      if ($from == "PLAY") {
        AddCurrentTurnEffect($cardID, $currentPlayer);
        IncrementClassState($currentPlayer, $CS_DamagePrevention, 1);
      }
      return "";
    case "hyper_scrapper_blue":
      $items = SearchDiscard($currentPlayer, subtype: "Item");
      $itemsCount = count(explode(",", $items));
      if ($itemsCount < $resourcesPaid) {
        WriteLog("Player " . $currentPlayer . " would need to banish " . $resourcesPaid . " items from their graveyard but they only have " . $itemsCount . " items in their graveyard.");
        RevertGamestate();
      }
      AddDecisionQueue("MULTICHOOSEDISCARD", $currentPlayer, $resourcesPaid . "-" . $items . "-" . $resourcesPaid, 1);
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "HYPERSCRAPPER");
      return "";
    case "scrap_trader_red":
      $numScrap = 0;
      $costAry = explode(",", $additionalCosts);
      for ($i = 0; $i < count($costAry); ++$i) if ($costAry[$i] == "SCRAP") ++$numScrap;
      if ($numScrap > 0) GainResources($currentPlayer, $numScrap * 2);
      return "";
    case "hydraulic_press_red":
    case "hydraulic_press_yellow":
    case "hydraulic_press_blue":
      if ($additionalCosts == "SCRAP") AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "ratchet_up_red":
    case "ratchet_up_yellow":
    case "ratchet_up_blue":
      if (GetClassState($currentPlayer, $CS_NumItemsDestroyed) > 0) AddCurrentTurnEffect($cardID, $defPlayer);
      return "";
    case "scrap_hopper_red":
    case "scrap_hopper_yellow":
    case "scrap_hopper_blue":
      if ($additionalCosts == "SCRAP") PlayAura("quicken", $currentPlayer);
      return "";
    case "junkyard_dogg_red":
    case "junkyard_dogg_yellow":
    case "junkyard_dogg_blue":
      if ($additionalCosts == "SCRAP") AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "scrap_compactor_red":
    case "scrap_compactor_yellow":
    case "scrap_compactor_blue":
      if ($additionalCosts == "SCRAP") AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "scrap_harvester_red":
    case "scrap_harvester_yellow":
    case "scrap_harvester_blue":
      if ($additionalCosts == "SCRAP") {
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYITEMS:hasCrank=true");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card with Crank to get a steam counter", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZADDCOUNTER", $currentPlayer, "-", 1);
      }
      return "";
    case "scrap_prospector_red":
    case "scrap_prospector_yellow":
    case "scrap_prospector_blue":
      if ($additionalCosts == "SCRAP") GainResources($currentPlayer, 1);
      return "";
    case "moonshot_yellow":
      for ($i = 0; $i < $resourcesPaid; $i += 2) AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "steel_street_hoons_blue":
      if (GetClassState($mainPlayer, $CS_NumItemsDestroyed) > 0) AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "meganetic_lockwave_blue":
      if ($resourcesPaid == 0) return;
      AddDecisionQueue("MULTIZONEINDICES", $otherPlayer, "MYCHAR:type=E");
      AddDecisionQueue("PREPENDLASTRESULT", $otherPlayer, "MAXCOUNT-" . $resourcesPaid / 3 . ",MINCOUNT-" . $resourcesPaid / 3 . ",");
      AddDecisionQueue("SETDQCONTEXT", $otherPlayer, "Choose " . $resourcesPaid / 3 . " equipment for the effect of " . CardLink("meganetic_lockwave_blue", "meganetic_lockwave_blue") . ".");
      AddDecisionQueue("CHOOSEMULTIZONE", $otherPlayer, "<-", 1);
      AddDecisionQueue("MZSWITCHPLAYER", $currentPlayer, "<-", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "MEGANETICLOCKWAVE");
      return "";
    case "system_failure_yellow":
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRITEMS:hasSteamCounter=true&THEIRCHAR:hasSteamCounter=true&MYITEMS:hasSteamCounter=true&MYCHAR:hasSteamCounter=true");
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose an equipment, item, or weapon. Remove all steam counters from it.");
      AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZREMOVECOUNTER", $currentPlayer, "-", 1);
      AddDecisionQueue("SYSTEMFAILURE", $currentPlayer, "<-", 1);
      return "";
    case "system_reset_yellow":
      $indices = SearchMultizone($currentPlayer, "MYITEMS:class=MECHANOLOGIST;maxCost=1");
      $indices = str_replace("MYITEMS-", "", $indices);
      $num = SearchCount($indices);
      $num = $resourcesPaid < $num ? $resourcesPaid : $num;
      AddDecisionQueue("MULTICHOOSEITEMS", $currentPlayer, $num . "-" . $indices . "-" . $num);
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "SYSTEMRESET");
      return "";
    case "fabricate_red":
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts, 1);
      AddDecisionQueue("MODAL", $currentPlayer, "FABRICATE", 1);
      return "";
    case "big_shot_red":
    case "burn_rubber_red":
    case "smash_and_grab_red":
      if (GetClassState($currentPlayer, $CS_NumBoosted) >= 2) AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "gigawatt_red":
    case "gigawatt_yellow":
    case "gigawatt_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "gas_up_red":
    case "gas_up_yellow":
    case "gas_up_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      MZMoveCard($currentPlayer, "MYBANISH:isSameName=hyper_driver_red", "", may: true);
      AddDecisionQueue("PUTPLAY", $currentPlayer, "0", 1);
      return "";
    case "quickfire_red":
    case "quickfire_yellow":
    case "quickfire_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "recharge_red":
    case "recharge_yellow":
    case "recharge_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a Hyper Driver to get a steam counter", 1);
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYITEMS:isSameName=hyper_driver_red");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZADDCOUNTER", $currentPlayer, "-", 1);
      return "";
    case "shriek_razors":
      $options = GetChainLinkCards(($currentPlayer == 1 ? 2 : 1), "AA");
      if ($options != "") {
        AddDecisionQueue("CHOOSECOMBATCHAIN", $currentPlayer, $options);
        AddDecisionQueue("COMBATCHAINDEFENSEMODIFIER", $currentPlayer, -1, 1);
      }
      return "";
    case "smashing_performance_yellow":
      Draw($currentPlayer);
      $card = DiscardRandom();
      if (ModifiedAttackValue($card, $currentPlayer, "HAND", source: "smashing_performance_yellow") >= 6) {
        $items = SearchMultizone($currentPlayer, "THEIRITEMS&MYITEMS");
        if ($items != "") {
          $items = explode(",", $items);
          $destroyedItem = $items[GetRandom(0, count($items) - 1)];
          $destroyedItemID = GetMZCard($currentPlayer, $destroyedItem);
          WriteLog(CardLink("smashing_performance_yellow", "smashing_performance_yellow") . " destroys " . CardLink($destroyedItemID, $destroyedItemID) . ".");
          MZDestroy($currentPlayer, $destroyedItem, $currentPlayer);
        }
      }
      return "";
    case "tectonic_rift_blue":
      PlayAura("seismic_surge", $currentPlayer, number: $resourcesPaid);
      return "";
    case "wax_off_blue":
      $cardsPlayed = explode(",", GetClassState($currentPlayer, $CS_NamesOfCardsPlayed));
      for ($i = 0; $i < count($cardsPlayed); ++$i) {
        if (CardName($cardsPlayed[$i]) == "Wax On") {
          PlayAura("zen_state", $currentPlayer);
          break;
        }
      }
      return "";
    case "emboldened_blade_blue":
      if (ArsenalHasFaceDownCard($otherPlayer)) {
        SetArsenalFacing("UP", $otherPlayer);
        if (SearchArsenal($otherPlayer, type: "DR") != "") {
          DestroyArsenal($otherPlayer, effectController: $currentPlayer);
          AddCurrentTurnEffect($cardID, $currentPlayer);
        }
      }
      return "";
    case "sonata_fantasmia_blue":
      $xVal = $resourcesPaid / 2;
      PlayAura("runechant", $currentPlayer, $xVal);
      if ($xVal >= 6) {
        DiscardRandom($otherPlayer, $cardID, $currentPlayer);
        DiscardRandom($otherPlayer, $cardID, $currentPlayer);
        DiscardRandom($otherPlayer, $cardID, $currentPlayer);
      }
      return "";
    case "tome_of_imperial_flame_red":
      Draw($currentPlayer);
      if (IsRoyal($currentPlayer)) Draw($currentPlayer);
      PrependDecisionQueue("OP", $currentPlayer, "BANISHHAND", 1);
      if (SearchCount(SearchHand($currentPlayer, pitch: 1)) >= 2) {
        PrependDecisionQueue("ELSE", $currentPlayer, "-");
        PitchCard($currentPlayer, "MYHAND:pitch=1");
        PitchCard($currentPlayer, "MYHAND:pitch=1");
        PrependDecisionQueue("NOPASS", $currentPlayer, "-");
        PrependDecisionQueue("YESNO", $currentPlayer, "if you want to pitch 2 red cards");
      }
      return "";
    case "dust_from_the_chrome_caverns_red":
      PutPermanentIntoPlay($currentPlayer, $cardID);
      return "";
    case "warband_of_bellona":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "slay_red":
      MZChooseAndDestroy($currentPlayer, "THEIRALLY:subtype=Angel");
      return "";
    case "teklovossen_the_mechropotent":
      if (IsHeroAttackTarget()) PummelHit();
      return "";
    case "evo_command_center_yellow_equip":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "evo_engine_room_yellow_equip":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "evo_smoothbore_yellow_equip":
      AddCurrentTurnEffectNextAttack($cardID, $currentPlayer);
      return "";
    case "evo_thruster_yellow_equip":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:type=W");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a weapon to attack an additional time");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZOP", $currentPlayer, "ADDITIONALUSE", 1);
      return "";
    case "evo_data_mine_yellow_equip":
      Draw($currentPlayer);
      MZMoveCard($currentPlayer, "MYHAND", "MYTOPDECK", silent: true);
      return "";
    case "evo_battery_pack_yellow_equip":
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card with Crank to get a steam counter", 1);
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYITEMS:hasCrank=true");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZADDCOUNTER", $currentPlayer, "-", 1);
      return "";
    case "evo_cogspitter_yellow_equip":
      MZMoveCard($currentPlayer, "MYHAND:subtype=Item;maxCost=1", "MYITEMS", may: true);
      return "";
    case "evo_charging_rods_yellow_equip":
      PlayAura("quicken", $currentPlayer);
      return "";
    default:
      return "";
  }
}

function PhantomTidemawDestroy($player = -1, $index = -1)
{
  global $mainPlayer;
  $auras = &GetAuras($player);
  if ($player == -1) {
    $player = $mainPlayer;
  }

  if ($index == -1) {
    for ($i = 0; $i < count($auras); $i++) {
      if (isset($auras[$i * AuraPieces()]) && $auras[$i * AuraPieces()] == "phantom_tidemaw_blue") {
        ++$auras[$i * AuraPieces() + 3];
      }
    }
  } else if ($index > -1) {
    ++$auras[$index + 3];
  }
}

function ModularMove($cardID, $uniqueID)
{
  global $currentPlayer;
  AddDecisionQueue("LISTEXPOSEDEQUIPSLOTS", $currentPlayer, "-");
  AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose an equipment zone to move " . CardLink($cardID, $cardID) . " to.", 1);
  AddDecisionQueue("BUTTONINPUT", $currentPlayer, "<-", 1);
  AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
  AddDecisionQueue("EQUIPCARD", $currentPlayer, $cardID . "-{0}", 1);
  AddDecisionQueue("REMOVEMODULAR", $currentPlayer, $uniqueID);
}