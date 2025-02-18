<?php

  function ELERangerPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
  {
    global $currentPlayer;
    $rv = "";
    $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
    switch($cardID)
    {
      case "lexi_livewire": case "lexi":
        if(DelimStringContains($additionalCosts, "LIGHTNING")) { $rv .= "The next attack gains go again."; AddCurrentTurnEffect("lexi_livewire-1", $currentPlayer); }
        if(DelimStringContains($additionalCosts, "ICE")) { if($rv != "") $rv .= " "; $rv .= "The opponent gets a Frostbite."; PlayAura("frostbite", $otherPlayer, effectController: $currentPlayer); }
        return $rv;
      case "shiver":
        LoadArrow($currentPlayer);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a mode", 1);
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, "1_Attack,Dominate", 1);
        AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
        AddDecisionQueue("MODAL", $currentPlayer, "SHIVER", 1);
        return "";
      case "voltaire_strike_twice":
        LoadArrow($currentPlayer);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a mode", 1);
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, "1_Attack,Go_again", 1);
        AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
        AddDecisionQueue("MODAL", $currentPlayer, "VOLTAIRE", 1);
        return "";
      case "frost_lock_blue":
        AddCurrentTurnEffect($cardID . "-1", $otherPlayer);
        return "";
      case "ice_storm_red":
        AddCurrentTurnEffect($cardID . "-1", $currentPlayer);
        if(DelimStringContains($additionalCosts, "ICE") && DelimStringContains($additionalCosts, "LIGHTNING")) {
          AddCurrentTurnEffect($cardID . "-2", $currentPlayer);
        }
        return "";
      case "honing_hood":
        $arsenal = &GetArsenal($currentPlayer);
        for($i=0; $i < count($arsenal); $i+=ArsenalPieces()) {
          AddPlayerHand($arsenal[$i], $currentPlayer, "ARS");
        }
        $arsenal = [];
        MZMoveCard($currentPlayer, "MYHAND", "MYARS,HAND,DOWN", silent:true);
        return "";
      case "seek_and_destroy_red":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "over_flex_red": case "over_flex_yellow": case "over_flex_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        Reload();
        return "";
      default: return "";
    }
  }

  function ELERangerHitEffect($cardID)
  {
    global $defPlayer, $combatChainState, $CCS_AttackFused, $mainPlayer;
    switch($cardID)
    {
      case "light_it_up_yellow":
        if(IsHeroAttackTarget() && $combatChainState[$CCS_AttackFused]) DamageTrigger($defPlayer, NumEquipment($defPlayer), "ATTACKHIT", $cardID);
        break;
      case "boltn_shot_red": case "boltn_shot_yellow": case "boltn_shot_blue":
        if(HasIncreasedAttack()) Reload($mainPlayer);
        break;
      default: break;
    }
  }

  function Fuse($cardID, $player, $elements)
  {
    if(!CanRevealCards($player)) { WriteLog("Cannot fuse because you cannot reveal cards"); return; }
    $elementArray = explode(",", $elements);
    $elementText = "";
    $isAndOrFuse = IsAndOrFuse($cardID);
    for($i=0; $i<count($elementArray); ++$i)
    {
      $element = $elementArray[$i];
      $subsequent = ($i > 0 && !$isAndOrFuse) ? 1 : 0;
      AddDecisionQueue("MULTIZONEINDICES", $player, "MYHAND:talent=" . $element, $subsequent);
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose which ". ucfirst(strtolower($element))." card to reveal for Fusion", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
      AddDecisionQueue("MZOP", $player, "GETCARDID", 1);
      AddDecisionQueue("REVEALCARDS", $player, "-", 1);
      if($isAndOrFuse) {
        AddDecisionQueue("AFTERFUSE", $player, $cardID . "-" . $element, 1);
        if($i > 0) $elementText .= " and/or ";
      }
      else if($i > 0) $elementText .= " and ";
      $elementText .= $element;
    }
    if(!$isAndOrFuse) {
      $elements = implode(",", $elementArray);
      AddDecisionQueue("AFTERFUSE", $player, $cardID . "-" . $elements, 1);
    }
  }

  function IsAndOrFuse($cardID)
  {
    switch($cardID)
    {
      case "fulminate_yellow": case "flashfreeze_red": case "exposed_to_the_elements_blue": return true;
      default: return false;
    }
  }

  function FuseAbility($cardID, $player, $element)
  {
    global $CS_NextNAAInstant, $CS_PlayCCIndex, $CombatChain;
    $otherPlayer = ($player == 2 ? 1 : 2);
    $set = CardSet($cardID);
    switch($cardID)
    {
      case "endless_winter_red": AddCurrentTurnEffect($cardID, $otherPlayer); break;
      case "biting_gale_red": case "biting_gale_yellow": case "biting_gale_blue":
        if (!IsAllyAttacking()) {
          PayOrDiscard($otherPlayer, 2, true);
        }
        break;
      case "turn_timber_red": case "turn_timber_yellow": case "turn_timber_blue":
        $index = GetClassState($player, $CS_PlayCCIndex);
        $CombatChain->Card($index)->ModifyDefense(2);
        break;
      case "glacial_footsteps_red": case "glacial_footsteps_yellow": case "glacial_footsteps_blue": AddCurrentTurnEffect($cardID, $player); break;
      case "mulch_red": case "mulch_yellow": case "mulch_blue": AddCurrentTurnEffect($cardID, $player); break;
      case "snow_under_red": case "snow_under_yellow": case "snow_under_blue": AddCurrentTurnEffect($cardID, $player); break;
      case "emerging_avalanche_red": case "emerging_avalanche_yellow": case "emerging_avalanche_blue": PlayAura("frostbite", $otherPlayer, effectController: $player); break;
      case "strength_of_sequoia_red": case "strength_of_sequoia_yellow": case "strength_of_sequoia_blue": PlayAura("seismic_surge", $player); break;
      case "frost_lock_blue": AddCurrentTurnEffect($cardID . "-2", $player); break;
      case "cold_wave_red": case "cold_wave_yellow": case "cold_wave_blue": AddCurrentTurnEffect($cardID, $otherPlayer); break;
      case "snap_shot_red": case "snap_shot_yellow": case "snap_shot_blue":
        SearchCharacterAddUses($player, 1, "W", "Bow");
        SearchCharacterAddEffect($player, "INSTANT", "W", "Bow");
        break;
      case "blizzard_bolt_red": case "blizzard_bolt_yellow": case "blizzard_bolt_blue": AddCurrentTurnEffect($cardID, $player); break;
      case "buzz_bolt_red": case "buzz_bolt_yellow": case "buzz_bolt_blue": AddCurrentTurnEffect($cardID, $player); break;
      case "chilling_icevein_red": case "chilling_icevein_yellow": case "chilling_icevein_blue": AddCurrentTurnEffect($cardID, $player); break;
      case "dazzling_crescendo_red": case "dazzling_crescendo_yellow": case "dazzling_crescendo_blue": GiveAttackGoAgain(); break;
      case "flake_out_red": case "flake_out_yellow": case "flake_out_blue": AddCurrentTurnEffect($cardID, $player); break;
      case "frazzle_red": case "frazzle_yellow": case "frazzle_blue": AddCurrentTurnEffect($cardID, $player); break;
      case "flicker_wisp_yellow": AddCurrentTurnEffect($cardID, $player); break;
      case "force_of_nature_blue": AddCurrentTurnEffect($cardID, $player); break;
      case "rites_of_lightning_red": case "rites_of_lightning_yellow": case "rites_of_lightning_blue": DealArcane(1, 0, "PLAYCARD", $cardID); break;
      case "arcanic_shockwave_red": case "arcanic_shockwave_yellow": case "arcanic_shockwave_blue": DealArcane(1, 0, "PLAYCARD", $cardID); break;
      case "vela_flash_red": case "vela_flash_yellow": case "vela_flash_blue": SetClassState($player, $CS_NextNAAInstant, 1); break;
      case "rites_of_replenishment_red": case "rites_of_replenishment_yellow": case "rites_of_replenishment_blue":
        PrependDecisionQueue("WRITELOG", $player, "Card chosen: <0>", 1);
        PrependDecisionQueue("SETDQVAR", $player, "0", 1);
        PrependDecisionQueue("MZREMOVE", $player, "-", 1);
        PrependDecisionQueue("MZADDZONE", $player, "MYBOTDECK,GY,DOWN", 1);
        PrependDecisionQueue("MAYCHOOSEMULTIZONE", $player, "<-", 1);
        PrependDecisionQueue("MULTIZONEINDICES", $player, "MYDISCARD:type=AA");
        break;
      case "stir_the_wildwood_red": case "stir_the_wildwood_yellow": case "stir_the_wildwood_blue": AddCurrentTurnEffect($cardID, $player); break;
      case "bramble_spark_red": case "bramble_spark_yellow": case "bramble_spark_blue": AddCurrentTurnEffect($cardID . "-FUSE", $player); break;
      case "inspire_lightning_red": DealArcane(3, 0, "PLAYCARD", $cardID); break;
      case "inspire_lightning_yellow": DealArcane(2, 0, "PLAYCARD", $cardID); break;
      case "inspire_lightning_blue": DealArcane(1, 0, "PLAYCARD", $cardID); break;
      case "fulminate_yellow":
        if(DelimStringContains($element, "LIGHTNING")) AddCurrentTurnEffect($cardID . "-GA", $player);
        if(DelimStringContains($element, "EARTH")) AddCurrentTurnEffect($cardID . "-BUFF", $player);
        break;
      case "flashfreeze_red":
        if(DelimStringContains($element, "LIGHTNING")) AddCurrentTurnEffect($cardID . "-BUFF", $player);
        if(DelimStringContains($element, "ICE")) AddCurrentTurnEffect($cardID . "-DOM", $player);
        break;
      case "exposed_to_the_elements_blue":
        if(DelimStringContains($element, "ICE")) ExposedToTheElementsIce($player);
        if(DelimStringContains($element, "EARTH")) ExposedToTheElementsEarth($player);
        break;
      case "entwine_earth_red": case "entwine_earth_yellow": case "entwine_earth_blue":
        $index = GetClassState($player, $CS_PlayCCIndex);
        $CombatChain->Card($index)->ModifyPower(2);
        break;
      case "entwine_ice_red": case "entwine_ice_yellow": case "entwine_ice_blue": AddCurrentTurnEffect($cardID, $player); break;
      case "entwine_lightning_red": case "entwine_lightning_yellow": case "entwine_lightning_blue": GiveAttackGoAgain(); break;
      default: break;
    }
  }

  function PayOrDiscard($player, $amount, $fromDQ=true, $passable=false)
  {
    $targetHand = &GetHand($player);
    if (count($targetHand) > 0) {
      if ($fromDQ) {
        PrependDecisionQueue("DISCARDCARD", $player, "HAND", 1);
        PrependDecisionQueue("MULTIREMOVEHAND", $player, "-", 1);
        PrependDecisionQueue("CHOOSEHAND", $player, "<-", 1);
        PrependDecisionQueue("SETDQCONTEXT", $player, "Choose a card to discard", 1);
        PrependDecisionQueue("FINDINDICES", $player, "HAND", 1);
        PrependDecisionQueue("ELSE", $player, "-");
        PrependDecisionQueue("PAYRESOURCES", $player, "-", 1);
        PrependDecisionQueue("PASSPARAMETER", $player, $amount, 1);
        PrependDecisionQueue("NOPASS", $player, "-", ($passable ? 1 : 0), 1);
        PrependDecisionQueue("YESNO", $player, "if_you_want_to_pay_" . $amount . "_to_avoid_discarding", ($passable ? 1 : 0), 1);
        PrependDecisionQueue("SETDQCONTEXT", $player, "Choose if you want to pay $amount to avoid discarding");
      } else {
        AddDecisionQueue("SETDQCONTEXT", $player, "Choose if you want to pay $amount to avoid discarding");
        AddDecisionQueue("YESNO", $player, "if_you_want_to_pay_" . $amount . "_to_avoid_discarding", ($passable ? 1 : 0), 1);
        AddDecisionQueue("NOPASS", $player, "-", ($passable ? 1 : 0), 1);
        AddDecisionQueue("PASSPARAMETER", $player, $amount, 1);
        AddDecisionQueue("PAYRESOURCES", $player, "-", 1);
        AddDecisionQueue("ELSE", $player, "-");
        AddDecisionQueue("FINDINDICES", $player, "HAND", 1);
        AddDecisionQueue("SETDQCONTEXT", $player, "Choose a card to discard", 1);
        AddDecisionQueue("CHOOSEHAND", $player, "<-", 1);
        AddDecisionQueue("MULTIREMOVEHAND", $player, "-", 1);
        AddDecisionQueue("DISCARDCARD", $player, "HAND", 1);
        PummelHit($player, $passable=true, fromDQ:false);
      }
    }
  }

  function HasFusion($cardID)
  {
    switch($cardID)
    {
      case "endless_winter_red": return "ICE";
      case "oaken_old_red": return "EARTH,ICE";
      case "awakening_blue": return "EARTH";
      case "biting_gale_red": case "biting_gale_yellow": case "biting_gale_blue": return "ICE";
      case "turn_timber_red": case "turn_timber_yellow": case "turn_timber_blue": return "EARTH";
      case "entangle_red": case "entangle_yellow": case "entangle_blue": return "EARTH";
      case "glacial_footsteps_red": case "glacial_footsteps_yellow": case "glacial_footsteps_blue": return "ICE";
      case "mulch_red": case "mulch_yellow": case "mulch_blue": return "EARTH";
      case "snow_under_red": case "snow_under_yellow": case "snow_under_blue": return "ICE";
      case "emerging_avalanche_red": case "emerging_avalanche_yellow": case "emerging_avalanche_blue": return "ICE";
      case "strength_of_sequoia_red": case "strength_of_sequoia_yellow": case "strength_of_sequoia_blue": return "EARTH";
      case "frost_lock_blue": return "ICE";
      case "light_it_up_yellow": return "LIGHTNING";
      case "ice_storm_red": return "ICE,LIGHTNING";
      case "cold_wave_red": case "cold_wave_yellow": case "cold_wave_blue": return "ICE";
      case "snap_shot_red": case "snap_shot_yellow": case "snap_shot_blue": return "LIGHTNING";
      case "blizzard_bolt_red": case "blizzard_bolt_yellow": case "blizzard_bolt_blue": return "ICE";
      case "buzz_bolt_red": case "buzz_bolt_yellow": case "buzz_bolt_blue": return "LIGHTNING";
      case "chilling_icevein_red": case "chilling_icevein_yellow": case "chilling_icevein_blue": return "ICE";
      case "dazzling_crescendo_red": case "dazzling_crescendo_yellow": case "dazzling_crescendo_blue": return "LIGHTNING";
      case "flake_out_red": case "flake_out_yellow": case "flake_out_blue": return "ICE";
      case "frazzle_red": case "frazzle_yellow": case "frazzle_blue": return "LIGHTNING";
      case "blossoming_spellblade_red": return "EARTH,LIGHTNING";
      case "flicker_wisp_yellow": return "LIGHTNING";
      case "force_of_nature_blue": return "EARTH";
      case "explosive_growth_red": case "explosive_growth_yellow": case "explosive_growth_blue": return "EARTH";
      case "rites_of_lightning_red": case "rites_of_lightning_yellow": case "rites_of_lightning_blue": return "LIGHTNING";
      case "arcanic_shockwave_red": case "arcanic_shockwave_yellow": case "arcanic_shockwave_blue": return "LIGHTNING";
      case "vela_flash_red": case "vela_flash_yellow": case "vela_flash_blue": return "LIGHTNING";
      case "rites_of_replenishment_red": case "rites_of_replenishment_yellow": case "rites_of_replenishment_blue": return "EARTH";
      case "stir_the_wildwood_red": case "stir_the_wildwood_yellow": case "stir_the_wildwood_blue": return "EARTH";
      case "bramble_spark_red": case "bramble_spark_yellow": case "bramble_spark_blue": return "EARTH";
      case "inspire_lightning_red": case "inspire_lightning_yellow": case "inspire_lightning_blue": return "LIGHTNING";
      case "fulminate_yellow": return "EARTH,LIGHTNING";
      case "flashfreeze_red": return "ICE,LIGHTNING";
      case "exposed_to_the_elements_blue": return "EARTH,ICE";
      case "entwine_earth_red": case "entwine_earth_yellow": case "entwine_earth_blue": return "EARTH";
      case "entwine_ice_red": case "entwine_ice_yellow": case "entwine_ice_blue": return "ICE";
      case "entwine_lightning_red": case "entwine_lightning_yellow": case "entwine_lightning_blue": return "LIGHTNING";
      case "encase_red": return "ICE";
      case "freezing_point_red": return "ICE";
      case "sigil_of_permafrost_red": case "sigil_of_permafrost_yellow": case "sigil_of_permafrost_blue": return "ICE";
      case "ice_eternal_blue": return "ICE";
      case "succumb_to_winter_red": case "succumb_to_winter_yellow": case "succumb_to_winter_blue": return "ICE";
      case "aether_icevein_red": case "aether_icevein_yellow": case "aether_icevein_blue": return "ICE";
      case "brain_freeze_red": case "brain_freeze_yellow": case "brain_freeze_blue": return "ICE";
      case "icebind_red": case "icebind_yellow": case "icebind_blue": return "ICE";
      case "polar_cap_red": case "polar_cap_yellow": case "polar_cap_blue": return "ICE";
      case "frozen_to_death_blue": return "ICE";
      default: return "";
    }
  }

  function CurrentTurnFuseEffects($player, $element)
  {
    global $currentTurnEffects;
    $costModifier = 0;
    for($i=count($currentTurnEffects)-CurrentTurnEffectsPieces(); $i>=0; $i-=CurrentTurnEffectsPieces())
    {
      $remove = 0;
      if($player == $currentTurnEffects[$i+1]) {
        switch($currentTurnEffects[$i]) {
          case "isenhowl_weathervane_red": case "isenhowl_weathervane_yellow": case "isenhowl_weathervane_blue":
            if($element == "ICE") {
              $otherPlayer = ($player == 1 ? 2 : 1);
              AddLayer("TRIGGER", $player, $currentTurnEffects[$i], $otherPlayer);
              $remove = 1;
            }
            break;
          default: break;
        }
        if($remove == 1) RemoveCurrentTurnEffect($i);
      }
    }
    return $costModifier;
  }

  function AuraFuseEffects($player, $element)
  {
    $auras = &GetAuras($player);
    $otherPlayer = $player == 1 ? 2 : 1;
    for($i=count($auras)-AuraPieces(); $i>=0; $i-=AuraPieces()) {
      switch($auras[$i]) {
        case "insidious_chill_blue":
          if($element == "ICE") AddLayer("TRIGGER", $player, $auras[$i], $otherPlayer, uniqueID:$auras[$i+6]);
          break;
        default: break;
      }
    }
  }

?>
