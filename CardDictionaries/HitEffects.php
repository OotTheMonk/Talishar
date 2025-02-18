<?php

function AKOHitEffect($cardID)
{
  global $mainPlayer, $defPlayer, $combatChainState, $CCS_DamageDealt;
  switch ($cardID) {
    case "strength_rules_all_red":
      if (IsHeroAttackTarget()) {
        SetArsenalFacing("UP", $defPlayer);
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRARS:type=AA;maxAttack=" . $combatChainState[$CCS_DamageDealt] - 1);
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want to BANISH", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZBANISH", $mainPlayer, "-", 1);
        AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      }
      break;
    default:
      break;
  }
}

function TCCHitEffect($cardID)
{
  global $mainPlayer, $defPlayer, $combatChainState, $CCS_GoesWhereAfterLinkResolves;
  switch ($cardID) {
    case "mauling_qi_red":
      if (ComboActive()) DamageTrigger($defPlayer, damage: 1, type: "DAMAGE", source: $cardID);
      break;
    case "under_loop_red":
      $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "BOTDECK";
      break;
    case "jinglewood_smash_hit":
      $charIndex = FindCharacterIndex($mainPlayer, $cardID);
      DestroyCharacter($mainPlayer, $charIndex);
      break;
    case "bittering_thorns_red":
      AddCurrentTurnEffectFromCombat($cardID, $mainPlayer);
      break;
    default:
      break;
  }
}

function EVOHitEffect($cardID)
{
  global $mainPlayer, $defPlayer, $combatChainState, $CCS_GoesWhereAfterLinkResolves;
  switch ($cardID) {
    case "banksy":
      if (IsHeroAttackTarget()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "MYITEMS:hasCrank=true");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a card with Crank to get a steam counter", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZADDCOUNTER", $mainPlayer, "-", 1);
      }
      break;
    case "annihilator_engine_red":
      if (IsHeroAttackTarget() && EvoUpgradeAmount($mainPlayer) >= 1) {
        global $combatChain, $CombatChain;
        $defendingCards = GetChainLinkCards($defPlayer);
        if (!empty($defendingCards)) {
          $defendingCardsArr = array_reverse(explode(",", GetChainLinkCards($defPlayer, exclCardTypes: "C")));
          foreach ($defendingCardsArr as $defendingCard) {
            if (CardType($combatChain[$defendingCard]) == "E") {
              WriteLog(CardLink("annihilator_engine_red", "annihilator_engine_red") . " destroyed " . CardLink($combatChain[$defendingCard], $combatChain[$defendingCard]) . ".");
              $charID = FindCharacterIndex($defPlayer, $combatChain[$defendingCard]);
              DestroyCharacter($defPlayer, $charID);
            } else {
              WriteLog(CardLink("annihilator_engine_red", "annihilator_engine_red") . " destroyed " . CardLink($combatChain[$defendingCard], $combatChain[$defendingCard]) . ".");
              AddGraveyard($combatChain[$defendingCard], $defPlayer, "CC");
              $CombatChain->Remove($defendingCard);
            }
          }
        }
      }
      break;
    case "terminator_tank_red":
      if (IsHeroAttackTarget() && EvoUpgradeAmount($mainPlayer) >= 1) PummelHit();
      break;
    case "war_machine_red":
      if (IsHeroAttackTarget() && EvoUpgradeAmount($mainPlayer) >= 1) DestroyArsenal($defPlayer, effectController: $mainPlayer);
      break;
    case "heist_red":
      if (IsHeroAttackTarget()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "MYBANISH:maxCost=1;subtype=Item&THEIRBANISH:maxCost=1;subtype=Item");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose an item to put into play");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
        AddDecisionQueue("PUTPLAY", $mainPlayer, "0", 1);
      }
      break;
    case "spring_a_leak_red":
    case "spring_a_leak_yellow":
    case "spring_a_leak_blue":
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRITEMS:hasSteamCounter=true&THEIRCHAR:hasSteamCounter=true");
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose an equipment, item, or weapon. Remove all steam counters from it.");
      AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZREMOVECOUNTER", $mainPlayer, "-", 1);
      break;
    case "data_link_red":
    case "data_link_yellow":
    case "data_link_blue":
    case "dive_through_data_red":
    case "dive_through_data_yellow":
    case "dive_through_data_blue":
      PlayerOpt($mainPlayer, 1);
      break;
    case "expedite_red":
    case "expedite_yellow":
    case "expedite_blue":
    case "metex_red":
    case "metex_yellow":
    case "metex_blue":
      MZMoveCard($mainPlayer, "MYHAND:subtype=Item;maxCost=1", "", may: true);
      AddDecisionQueue("PUTPLAY", $mainPlayer, "0", 1);
      break;
    case "under_loop_red":
    case "under_loop_yellow":
    case "under_loop_blue":
      $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "BOTDECK";
      break;
    case "already_dead_red":
      if (IsHeroAttackTarget()) {
        $deck = new Deck($defPlayer);
        if ($deck->Empty()) {
          WriteLog("The opponent deck is already... depleted.");
          break;
        }
        $deck->BanishTop(banishedBy: $cardID);
        AddDecisionQueue("SEARCHCOMBATCHAIN", $mainPlayer, "-");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card to banish");
        AddDecisionQueue("CHOOSECARDID", $mainPlayer, "<-", 1);
        AddDecisionQueue("ALREADYDEAD", $mainPlayer, "-", 1);
      }
      break;
    case "intoxicating_shot_blue":
      if (!IsAllyAttackTarget()) {
        PlayAura("courage", $defPlayer);
        PlayAura("quicken", $defPlayer);
      }
      break;
    default:
      break;
  }
}

function HVYHitEffect($cardID)
{
  global $mainPlayer, $defPlayer, $currentTurnEffects;;
  switch ($cardID) {
    case "send_packing_yellow":
      for ($i = 0; $i < count($currentTurnEffects); $i += CurrentTurnEffectsPieces()) {
        if ($currentTurnEffects[$i] == $cardID) {
          RemoveCurrentTurnEffect($i);
          break;
        }
      }
      break;
    case "millers_grindstone":
      if (IsHeroAttackTarget()) {
        Clash($cardID, $mainPlayer);
      }
      break;
    case "command_respect_red":
    case "command_respect_yellow":
    case "command_respect_blue":
      if (IsHeroAttackTarget() && HasIncreasedAttack()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRARS", 1);
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a card you want to destroy from their arsenal", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZDESTROY", $mainPlayer, false, 1);
      }
      break;
    case "concuss_red":
    case "concuss_yellow":
    case "concuss_blue":
      if (IsHeroAttackTarget() && HasIncreasedAttack()) PummelHit();
      break;
    case "pay_up_red":
      if (IsHeroAttackTarget()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRITEMS:type=T;cardID=gold");
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZOP", $mainPlayer, "GAINCONTROL", 1);
        AddDecisionQueue("ELSE", $mainPlayer, "-");
        AddDecisionQueue("DEAL1DAMAGE", $defPlayer, $cardID, 1);
      }
      break;
    case "down_but_not_out_red":
    case "down_but_not_out_yellow":
    case "down_but_not_out_blue":
      if (SearchCurrentTurnEffects($cardID, $mainPlayer, true)) {
        PlayAura("agility", $mainPlayer); //Agility
        PlayAura("might", $mainPlayer); //Might
        PlayAura("vigor", $mainPlayer); //Vigor
      }
      break;
    case "performance_bonus_red":
    case "performance_bonus_yellow":
    case "performance_bonus_blue":
      PutItemIntoPlayForPlayer("gold", $mainPlayer, effectController: $mainPlayer);//Gold
      return "";
    case "judge_jury_executioner_red":
      if (HasAimCounter() && IsHeroAttackTarget()) {
        $defPlayerHand = &GetHand($defPlayer);
        $defPlayerDiscardNum = count($defPlayerHand) - 1;
        for ($i = 0; $i < $defPlayerDiscardNum; ++$i) {
          PummelHit();
        }
        break;
      }
    default:
      break;
  }
}