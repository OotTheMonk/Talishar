<?php

function OUTAbilityCost($cardID)
{
  switch($cardID)
  {
    case "nerve_scalpel": case "nerve_scalpel": return 2;
    case "orbitoclast": case "orbitoclast": return 2;
    case "scale_peeler": case "scale_peeler": return 2;
    case "redback_shroud": return 0;
    case "mask_of_many_faces": return 1;
    case "silverwind_shuriken_blue": return 0;
    case "barbed_castaway": return 1;
    case "trench_of_sunken_treasure": return 0;
    case "quiver_of_abyssal_depths": return 3;
    case "quiver_of_rustling_leaves": return 3;
    case "driftwood_quiver": return 0;
    case "flick_knives": return 0;
    case "mask_of_shifting_perspectives": return 0;
    case "blade_cuff": return 2;
    case "mask_of_malicious_manifestations": return 1;
    case "toxic_tips": return 1;
    case "vambrace_of_determination": return 1;
    case "seekers_hood": case "seekers_gilet": case "seekers_mitts": case "seekers_leggings": return 1;//Seeker's Equips
    case "silken_gi": return 0;
    case "threadbare_tunic": return 0;
    case "fisticuffs": return 2;
    case "fleet_foot_sandals": return 0;
    default: return 0;
  }
}

  function OUTAbilityType($cardID, $index=-1)
  {
    switch ($cardID)
    {
      case "uzuri_switchblade": case "uzuri": return "AR";
      case "nerve_scalpel": case "nerve_scalpel": return "AA";
      case "orbitoclast": case "orbitoclast": return "AA";
      case "scale_peeler": case "scale_peeler": return "AA";
      case "redback_shroud": return "AR";
      case "mask_of_many_faces": return "I";
      case "silverwind_shuriken_blue": return "AR";
      case "barbed_castaway": return "I";
      case "trench_of_sunken_treasure": return "I";
      case "quiver_of_abyssal_depths": return "I";
      case "quiver_of_rustling_leaves": return "I";
      case "driftwood_quiver": return "I";
      case "flick_knives": return "AR";
      case "mask_of_shifting_perspectives": return "AR";
      case "blade_cuff": return "A";
      case "mask_of_malicious_manifestations": return "A";
      case "toxic_tips": return "A";
      case "vambrace_of_determination": return "AR";
      case "seekers_hood": case "seekers_gilet": case "seekers_mitts": case "seekers_leggings": return "I";//Seeker's Equips
      case "silken_gi": return "I";
      case "threadbare_tunic": return "I";
      case "fisticuffs": return "AR";
      case "fleet_foot_sandals": return "AR";
      default: return "";
    }
  }

  function OUTAbilityHasGoAgain($cardID)
  {
    switch ($cardID)
    {
      case "blade_cuff": return true;
      case "mask_of_malicious_manifestations": return true;
      case "toxic_tips": return true;
      default: return false;
    }
  }

  function OUTEffectAttackModifier($cardID)
  {
    $idArr = explode("-", $cardID);
    $idArr2 = explode(",", $idArr[0]);
    $cardID = $idArr2[0];
    switch($cardID)
    {
      case "spike_with_bloodrot_red": case "spike_with_frailty_red": case "spike_with_inertia_red": return 3;
      case "prowl_red": case "prowl_yellow": case "prowl_blue": return 1;
      case "razors_edge_red": return 3;
      case "razors_edge_yellow": return 2;
      case "razors_edge_blue": return 1;
      case "head_leads_the_tail_red": return 1;
      case "deadly_duo_red": case "deadly_duo_yellow": case "deadly_duo_blue": return 2;
      case "melting_point_red": return 4;
      case "fletch_a_red_tail_red": return 4;
      case "fletch_a_yellow_tail_yellow": return 3;
      case "fletch_a_blue_tail_blue": return 2;
      case "lace_with_bloodrot_red": return 3;
      case "lace_with_frailty_red": return 3;
      case "lace_with_inertia_red": return 3;
      case "falcon_wing_red": case "falcon_wing_yellow": case "falcon_wing_blue": return 1;
      case "infecting_shot_red": case "infecting_shot_yellow": case "infecting_shot_blue": return 1;
      case "murkmire_grapnel_red": case "murkmire_grapnel_yellow": case "murkmire_grapnel_blue": return 1;
      case "sedation_shot_red": case "sedation_shot_yellow": case "sedation_shot_blue": return 1;
      case "skybound_shot_red": case "skybound_shot_yellow": case "skybound_shot_blue": return 1;
      case "withering_shot_red": case "withering_shot_yellow": case "withering_shot_blue": return 1;
      case "blade_cuff": return 1;
      case "concealed_blade_blue": return 1;
      case "knives_out_blue": return 1;
      case "plunge_red": case "plunge_yellow": case "plunge_blue": return 1;
      case "short_and_sharp_red": return 3;
      case "short_and_sharp_yellow": return 2;
      case "short_and_sharp_blue": return 1;
      case "silken_gi_2": return -1;
      case "gore_belching_red": return (-1 * $idArr[1]);
      case "premeditate_red_2": return 3;
      case "looking_for_a_scrap_red": case "looking_for_a_scrap_yellow": case "looking_for_a_scrap_blue": return 1;
      case "spring_load_red": return 3;
      case "spring_load_yellow": return 2;
      case "spring_load_blue": return 1;
      case "scout_the_periphery_red": return 3;
      case "scout_the_periphery_yellow": return 2;
      case "scout_the_periphery_blue": return 1;
      default: return 0;
    }
  }

  function OUTCombatEffectActive($cardID, $attackID)
  {
    global $mainPlayer;
    $dashArr = explode("-", $cardID);
    $commaArr = explode(",", $cardID);
    $cardID = $dashArr[0];
    if(strlen($cardID) > 6) $cardID = $commaArr[0];
    switch ($cardID)
    {
      case "spike_with_bloodrot_red": case "spike_with_frailty_red": case "spike_with_inertia_red": return true;
      case "prowl_red": case "prowl_yellow": case "prowl_blue": return HasStealth($attackID);
      case "razors_edge_red": case "razors_edge_yellow": case "razors_edge_blue": return true;
      case "mask_of_many_faces": return CardType($attackID) == "AA";
      case "head_leads_the_tail_red": return CardType($attackID) == "AA" && count($commaArr) > 1 && IsCurrentAttackName(GamestateUnsanitize($commaArr[1]));
      case "be_like_water_red": case "be_like_water_yellow": case "be_like_water_blue": return true;
      case "deadly_duo_red": case "deadly_duo_yellow": case "deadly_duo_blue": return CardType($attackID) == "AA" && AttackValue($attackID) <= 2;//Base power
      case "buzzsaw_trap_blue": return true;
      case "melting_point_red": return CardSubType($attackID) == "Arrow";
      case "fletch_a_red_tail_red": case "fletch_a_yellow_tail_yellow": case "fletch_a_blue_tail_blue": return CardSubType($attackID) == "Arrow";
      case "lace_with_bloodrot_red": return CardSubType($attackID) == "Arrow";
      case "lace_with_frailty_red": return CardSubType($attackID) == "Arrow";
      case "lace_with_inertia_red": return CardSubType($attackID) == "Arrow";
      case "falcon_wing_red": case "falcon_wing_yellow": case "falcon_wing_blue": return true;
      case "infecting_shot_red": case "infecting_shot_yellow": case "infecting_shot_blue": return true;
      case "murkmire_grapnel_red": case "murkmire_grapnel_yellow": case "murkmire_grapnel_blue": return true;
      case "sedation_shot_red": case "sedation_shot_yellow": case "sedation_shot_blue": return true;
      case "skybound_shot_red": case "skybound_shot_yellow": case "skybound_shot_blue": return true;
      case "withering_shot_red": case "withering_shot_yellow": case "withering_shot_blue": return true;
      case "mask_of_shifting_perspectives": return SubtypeContains($attackID, "Dagger");
      case "blade_cuff": return SubtypeContains($attackID, "Dagger");
      case "concealed_blade_blue": return true;
      case "knives_out_blue": return SubtypeContains($attackID, "Dagger");
      case "plunge_red": case "plunge_yellow": case "plunge_blue": return SubtypeContains($attackID, "Dagger");
      case "short_and_sharp_red": case "short_and_sharp_yellow": case "short_and_sharp_blue": return true;
      case "toxic_tips": return CardType($attackID) == "AA";
      case "toxicity_red": case "toxicity_yellow": case "toxicity_blue": return CardType($attackID) == "AA" && (ClassContains($attackID, "ASSASSIN", $mainPlayer) || ClassContains($attackID, "RANGER", $mainPlayer));
      case "silken_gi_2": return CardType($attackID) == "AA";
      case "gore_belching_red": return true;
      case "premeditate_red_1": return CardType($attackID) == "AA";
      case "premeditate_red_2": return CardType($attackID) == "AA" && AttackPlayedFrom() == "ARS";
      case "looking_for_a_scrap_red": case "looking_for_a_scrap_yellow": case "looking_for_a_scrap_blue": return true;
      case "spring_load_red": case "spring_load_yellow": case "spring_load_blue": return true;
      case "scout_the_periphery_red": case "scout_the_periphery_yellow": case "scout_the_periphery_blue": return CardType($attackID) == "AA" && AttackPlayedFrom() == "ARS";
      default: return false;
    }
  }

  function OUTPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
  {
    global $currentPlayer, $CS_PlayIndex, $mainPlayer, $defPlayer, $combatChain, $combatChainState, $CCS_LinkBaseAttack;
    global $CID_Frailty, $CID_BloodRotPox, $CID_Inertia, $CombatChain;
    $rv = "";
    $otherPlayer = $currentPlayer == 1 ? 2 : 1;
    switch ($cardID)
    {
      case "uzuri_switchblade": case "uzuri":
        $banish = new Banish($currentPlayer);
        $card = $banish->FirstCardWithModifier("UZURI");
        if($card == null) return "Uzuri's knife is re-sheathed";
        if(CardType($card->ID()) != "AA") { $card->ClearModifier(); return "Uzuri was bluffing"; }
        if(CardCost($card->ID()) > 2) { $card->ClearModifier(); return "Uzuri was bluffing"; }
        if(substr($CombatChain->AttackCard()->From(), 0, 5) != "THEIR") $deck = new Deck($currentPlayer);
        else $deck = new Deck($otherPlayer);
        if (CardType($combatChain[0]) == "AA") $deck->AddBottom($combatChain[0], "CC");
        else {//chelicera
          $index = SearchCharacterForUniqueID($combatChain[8], $currentPlayer);
          DestroyCharacter($currentPlayer, $index);
        }
        AttackReplaced($card->ID(), $currentPlayer);
        $combatChainState[$CCS_LinkBaseAttack] = ModifiedAttackValue($combatChain[0], $currentPlayer, "CC", source:"");
        $card->Remove();
        return "";
      case "redback_shroud":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "spreading_plague_yellow":
        for($i=0; $i<count($combatChain); $i+=CombatChainPieces())
        {
          if($combatChain[$i+1] == $defPlayer && $combatChain[$i+2] != "PLAY" && CardType($combatChain[$i]) != "C") PlayAura($CID_BloodRotPox, $defPlayer, effectController:$mainPlayer);
        }
        return "";
      case "spike_with_bloodrot_red": case "spike_with_frailty_red": case "spike_with_inertia_red":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "prowl_red": case "prowl_yellow": case "prowl_blue":
        AddCurrentTurnEffectFromCombat($cardID, $currentPlayer);
        return "";
      case "razors_edge_red": case "razors_edge_yellow": case "razors_edge_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "mask_of_many_faces":
        AddDecisionQueue("INPUTCARDNAME", $currentPlayer, "-");
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "mask_of_many_faces-");
        AddDecisionQueue("ADDCURRENTEFFECTNEXTATTACK", $currentPlayer, "<-");
        AddDecisionQueue("WRITELOG", $currentPlayer, "📣<b>{0}</b> was chosen");
        return "";
      case "head_leads_the_tail_red":
        AddDecisionQueue("INPUTCARDNAME", $currentPlayer, "head_leads_the_tail_red");
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "head_leads_the_tail_red,");
        AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "<-");
        AddDecisionQueue("WRITELOG", $currentPlayer, "📣<b>{0}</b> was chosen");
        return "";
      case "visit_the_floating_dojo_blue":
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:isSameName=surging_strike_red");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a Surging Strike from your graveyard");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, "TOP,BOTTOM", 1);
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "{0},", 1);
        AddDecisionQueue("ADDTOPORBOT", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:comboOnly=true");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a Combo card from your graveyard");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, "TOP,BOTTOM", 1);
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "{0},", 1);
        AddDecisionQueue("ADDTOPORBOT", $currentPlayer, "-", 1);
        return "";
      case "bonds_of_ancestry_red": case "bonds_of_ancestry_yellow": case "bonds_of_ancestry_blue":
        if(ComboActive())
        {
          GiveAttackGoAgain();
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:comboOnly=true");
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card with Combo to banish from your graveyard");
          AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("MZBANISH", $currentPlayer, "GY,-," . $currentPlayer, 1);
          AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
          AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "MYDECK:isSameName=", 1);
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "<-", 1);
          AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("MZBANISH", $currentPlayer, "DECK,TCC," . $currentPlayer, 1);
          AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        }
        return "";
      case "barbed_castaway":
        $abilityName = GetResolvedAbilityName($cardID);
        SearchCurrentTurnEffects("barbed_castaway-".$abilityName, $currentPlayer, true);
        if($abilityName == "Load") LoadArrow($currentPlayer);
        else if($abilityName == "Aim") {
          $arsenalFaceDown = ArsenalFaceDownCard($currentPlayer);
          if($arsenalFaceDown != "" && CardSubType($arsenalFaceDown) == "Arrow") {
            SetArsenalFacing("UP", $currentPlayer);
            $arsenal = &GetArsenal($currentPlayer);
            $arsenal[count($arsenal)-ArsenalPieces()+3] += 1;
          }
        }
        return "";
      case "trench_of_sunken_treasure":
        GainResources($currentPlayer, 1);
        return "";
      case "quiver_of_abyssal_depths":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "MYDISCARDARROW");
        AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "3-", 1);
        AddDecisionQueue("MULTICHOOSEDISCARD", $currentPlayer, "<-", 1);
        AddDecisionQueue("VALIDATEALLDIFFERENTNAME", $currentPlayer, "DISCARD", 1);
        AddDecisionQueue("SPECIFICCARD", $currentPlayer, "QUIVEROFABYSSALDEPTH", 1);
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
        return "";
      case "quiver_of_rustling_leaves":
        $deck = new Deck($currentPlayer);
        if($deck->Reveal())
        {
          $topCard = $deck->Top();
          if(CardSubType($topCard) == "Arrow")
          {
            if(!ArsenalFull($currentPlayer))
            {
              AddArsenal($topCard, $currentPlayer, "DECK", "UP");
              $deck->Top(remove:true);
            }
            DestroyCharacter($currentPlayer, GetClassState($currentPlayer, $CS_PlayIndex));
            $rv = "The top card was an arrow, so Quiver of Rustling Leaves is destroyed.";
          }
        }
        return $rv;
      case "driftwood_quiver":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "ARSENAL");
        AddDecisionQueue("CHOOSEARSENAL", $currentPlayer, "<-", 1);
        AddDecisionQueue("REMOVEARSENAL", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDBOTDECK", $currentPlayer, "-", 1);
        break;
      case "melting_point_red":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "fletch_a_red_tail_red": case "fletch_a_yellow_tail_yellow": case "fletch_a_blue_tail_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        AddCurrentTurnEffect($cardID, $defPlayer);
        return "";
      case "lace_with_bloodrot_red":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "lace_with_frailty_red":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "lace_with_inertia_red":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "falcon_wing_red": case "falcon_wing_yellow": case "falcon_wing_blue":
        if(HasAimCounter()) {
          AddCurrentTurnEffect($cardID, $currentPlayer);
        }
        return "";
      case "infecting_shot_red": case "infecting_shot_yellow": case "infecting_shot_blue":
        if(HasAimCounter()) {
          AddCurrentTurnEffect($cardID, $currentPlayer);
        }
        return "";
      case "murkmire_grapnel_red": case "murkmire_grapnel_yellow": case "murkmire_grapnel_blue":
        if(HasAimCounter()) {
          AddCurrentTurnEffect($cardID, $currentPlayer);
          $rv = "Gets +1.";
        }
        return $rv;
      case "sedation_shot_red": case "sedation_shot_yellow": case "sedation_shot_blue":
        if(HasAimCounter()) {
          AddCurrentTurnEffect($cardID, $currentPlayer);
          $rv = "Gets +1.";
        }
        return $rv;
      case "skybound_shot_red": case "skybound_shot_yellow": case "skybound_shot_blue":
        if(HasAimCounter()) {
          AddCurrentTurnEffect($cardID, $currentPlayer);
          $rv = "Gets +1.";
        }
        return $rv;
      case "withering_shot_red": case "withering_shot_yellow": case "withering_shot_blue":
        if(HasAimCounter()) {
          AddCurrentTurnEffect($cardID, $currentPlayer);
          $rv = "Gets +1.";
        }
        return $rv;
      case "flick_knives":
        ThrowWeapon("Dagger", $cardID, target:$target);
        return "";
      case "mask_of_shifting_perspectives":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "blade_cuff":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "concealed_blade_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "knives_out_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "hurl_red": case "hurl_yellow": case "hurl_blue":
        if(DelimStringContains($additionalCosts, "PAY1"))
        {
          ThrowWeapon("Dagger", $cardID);
        }
        return "";
      case "short_and_sharp_red": case "short_and_sharp_yellow": case "short_and_sharp_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "mask_of_malicious_manifestations":
        $cardRemoved = Belch();
        AddPlayerHand($cardRemoved, $currentPlayer, "DECK");
        return "";
      case "toxic_tips":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "codex_of_bloodrot_yellow":
        $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
        if(!ArsenalFull($currentPlayer))
        {
          MZMoveCard($currentPlayer, "MYHAND", "MYARS,HAND,DOWN", silent:true);
        }
        if(!ArsenalFull($otherPlayer))
        {
          MZMoveCard($otherPlayer, "MYHAND", "MYARS,HAND,DOWN", silent:true);
        }
        PlayAura("ponder", $currentPlayer);//Ponder
        PlayAura($CID_BloodRotPox, $otherPlayer, effectController: $currentPlayer);
        return "";
      case "codex_of_frailty_yellow":
        $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
        CodexOfFrailty($currentPlayer);
        PlayAura("ponder", $currentPlayer);
        PlayAura($CID_Frailty, $otherPlayer, effectController: $currentPlayer);
        return "";
      case "codex_of_inertia_yellow":
        $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
        $myDeck = new Deck($currentPlayer);
        if(!ArsenalFull($currentPlayer) && !$myDeck->Empty())
        {
          TopDeckToArsenal($currentPlayer);
          PummelHit($currentPlayer);
        }
        $theirDeck = new Deck($otherPlayer);
        if(!ArsenalFull($otherPlayer) && !$theirDeck->Empty())
        {
          TopDeckToArsenal($otherPlayer);
          PummelHit($otherPlayer);
        }
        PlayAura("ponder", $currentPlayer);
        PlayAura($CID_Inertia, $otherPlayer, effectController: $currentPlayer);
        return "";
      case "toxicity_red": case "toxicity_yellow": case "toxicity_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "Your opponent loses life if your next assassin or ranger attack hits.";
      case "vambrace_of_determination":
        AddCurrentTurnEffect($cardID, $defPlayer);
        return "";
      case "seekers_hood": case "seekers_gilet": case "seekers_mitts": case "seekers_leggings":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        PlayerOpt($currentPlayer, 1);
        return "";
      case "silken_gi":
        AddCurrentTurnEffectNextAttack($cardID . "_1", $currentPlayer);
        AddCurrentTurnEffectNextAttack($cardID . "_2", $currentPlayer);
        return "";
      case "threadbare_tunic":
        GainResources($currentPlayer, 1);
        break;
      case "fleet_foot_sandals":
        GiveAttackGoAgain();
        break;
      case "gore_belching_red":
        $cardRemoved = Belch();
        if($cardRemoved == "") { AddCurrentTurnEffect("gore_belching_red-7", $currentPlayer); return "You cannot reveal cards so Gore Belching gets -7."; }
        else {
          BanishCardForPlayer($cardRemoved, $currentPlayer, "DECK", "-", "gore_belching_red");
          AddCurrentTurnEffect("gore_belching_red-" . ModifiedAttackValue($cardRemoved, $currentPlayer, "DECK", source:"gore_belching_red"), $currentPlayer);
        }
        return "";
      case "burdens_of_the_past_blue":
        if(ShouldAutotargetOpponent($currentPlayer)) {
          AddDecisionQueue("PASSPARAMETER", $currentPlayer, "Target_Opponent");
          AddDecisionQueue("PLAYERTARGETEDABILITY", $currentPlayer, "BURDENSOFTHEPAST", 1);
        } else {
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose target hero");
          AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Target_Opponent,Target_Yourself");
          AddDecisionQueue("PLAYERTARGETEDABILITY", $currentPlayer, "BURDENSOFTHEPAST", 1);
        }
        return "";
      case "premeditate_red":
        AddCurrentTurnEffect($cardID . "_1", $currentPlayer);
        AddCurrentTurnEffect($cardID . "_2", $currentPlayer);
        return "";
      case "infectious_host_red": case "infectious_host_yellow": case "infectious_host_blue":
        if(SearchAuras($CID_Frailty, $currentPlayer)) PlayAura($CID_Frailty, $defPlayer, effectController: $currentPlayer);
        if(SearchAuras($CID_BloodRotPox, $currentPlayer)) PlayAura($CID_BloodRotPox, $defPlayer, effectController: $currentPlayer);
        if(SearchAuras($CID_Inertia, $currentPlayer)) PlayAura($CID_Inertia, $defPlayer, effectController: $currentPlayer);
        return "";
      case "looking_for_a_scrap_red": case "looking_for_a_scrap_yellow": case "looking_for_a_scrap_blue":
        if(DelimStringContains($additionalCosts, "BANISH1ATTACK"))
        {
          AddCurrentTurnEffect($cardID, $currentPlayer);
          GiveAttackGoAgain();
        }
        return "";
      case "spring_load_red": case "spring_load_yellow": case "spring_load_blue":
        $hand = &GetHand($currentPlayer);
        if(count($hand) == 0)
        {
          $rv = "Spring Load gains bonus power";
          AddCurrentTurnEffect($cardID, $currentPlayer);
        }
        return $rv;
      case "scout_the_periphery_red": case "scout_the_periphery_yellow": case "scout_the_periphery_blue":
        LookAtTopCard($currentPlayer, $cardID);
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "brush_off_red": case "brush_off_yellow": case "brush_off_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "peace_of_mind_red": case "peace_of_mind_yellow": case "peace_of_mind_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        PlayAura("ponder", $currentPlayer);
        return "Prevents some of the next combat damage you take this turn.";
      default: return "";
    }
  }

  function OUTHitEffect($cardID, $from)
  {
    global $mainPlayer, $defPlayer, $chainLinks, $chainLinkSummary;
    global $CID_BloodRotPox, $CID_Frailty, $CID_Inertia;
    global $combatChainState, $CCS_GoesWhereAfterLinkResolves, $CCS_FlickedDamage;
    switch ($cardID)
    {
      case "nerve_scalpel": case "nerve_scalpel":
        if (IsHeroAttackTarget()) {
          AddCurrentTurnEffect($cardID, $defPlayer);
        }
        break;
      case "orbitoclast": case "orbitoclast":
        if (IsHeroAttackTarget()) {
          AddCurrentTurnEffect($cardID, $defPlayer);
        }
        break;
      case "scale_peeler": case "scale_peeler":
        if (IsHeroAttackTarget()) {
          AddCurrentTurnEffect($cardID, $defPlayer);
        }
        break;
      case "infiltrate_red":
        if(IsHeroAttackTarget())
        {
          $deck = new Deck($defPlayer);
          $deckCard = $deck->Top(true);
          if($deckCard != "") BanishCardForPlayer($deckCard, $defPlayer, "THEIRDECK", "NTFromOtherPlayer", $cardID);
        }
        break;
      case "shake_down_red":
        if(NumAttackReactionsPlayed() > 0 && IsHeroAttackTarget())
        {
          AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a pitch value", 1);
          AddDecisionQueue("BUTTONINPUT", $mainPlayer, "1,2,3", 1);
          AddDecisionQueue("SETDQVAR", $mainPlayer, "0", 1);
          AddDecisionQueue("WRITELOG", $mainPlayer, "Main player chose: {0}", 1);
          AddDecisionQueue("FINDINDICES", $defPlayer, "HAND");
          AddDecisionQueue("REVEALHANDCARDS", $defPlayer, "-", 1);
          AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRHAND:pitch={0}", 1);
          AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
          AddDecisionQueue("MZBANISH", $mainPlayer, "HAND,-," . $mainPlayer, 1);
          AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
        }
        break;
      case "infect_red": case "infect_yellow": case "infect_blue":
        if(IsHeroAttackTarget()) PlayAura($CID_BloodRotPox, $defPlayer, effectController: $mainPlayer);
        break;
      case "sedate_red": case "sedate_yellow": case "sedate_blue":
        if(IsHeroAttackTarget()) PlayAura($CID_Inertia, $defPlayer, effectController: $mainPlayer);
        break;
      case "wither_red": case "wither_yellow": case "wither_blue":
        if(IsHeroAttackTarget()) PlayAura($CID_Frailty, $defPlayer, effectController: $mainPlayer);
        break;
      case "dishonor_blue":
        $char = &GetPlayerCharacter($defPlayer);
        if(IsHeroAttackTarget() && HasAttackName("Surging Strike") && HasAttackName("Descendent Gustwave") && HasAttackName("Bonds of Ancestry"))
        {
          if($char[0] == "DUMMY") { $char[1] = 4; WriteLog("🤖 Combat Dummies have no honor."); }
          else if($char[1] == 4) WriteLog("🥷 Those who have been dishonored have nothing left to lose.");
          else $char[1] = 4;
        }
        break;
      case "wander_with_purpose_yellow":
        KatsuHit("to_discard_and_search_for_a_combo_card");
        break;
      case "recoil_red": case "recoil_yellow": case "recoil_blue":
        if(ComboActive() && IsHeroAttackTarget()) MZMoveCard($defPlayer, "MYHAND", "MYTOPDECK", silent:true);
        break;
      case "spinning_wheel_kick_red": case "spinning_wheel_kick_yellow": case "spinning_wheel_kick_blue":
        if(ComboActive()) {
          if(substr($from, 0, 5) != "THEIR") {
            AddBottomDeck($cardID, $mainPlayer, "CC");
            $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "-";
          }
          else {
            AddBottomDeck($cardID, $defPlayer, "CC");
            $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "-";
          }
        }
        break;
      case "be_like_water_red": case "be_like_water_yellow": case "be_like_water_blue":
        $hand = &GetHand($mainPlayer);
        $resources = &GetResources($mainPlayer);
        if(Count($hand) > 0 || $resources[0] > 0)
        {
          AddDecisionQueue("YESNO", $mainPlayer, "if you want to pay 1 to give ".CardLink($cardID, $cardID)." a name", 0, 1);
          AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
          AddDecisionQueue("PASSPARAMETER", $mainPlayer, "1", 1);
          AddDecisionQueue("PAYRESOURCES", $mainPlayer, "<-", 1);
          AddDecisionQueue("BUTTONINPUT", $mainPlayer, "Head_Jab,Surging_Strike,Twin_Twisters", 1);
          AddDecisionQueue("SETDQVAR", $mainPlayer, "0", 1);
          AddDecisionQueue("WRITELOG", $mainPlayer, "Attack renamed to <b>{0}</b>", 1);
          AddDecisionQueue("PREPENDLASTRESULT", $mainPlayer, $cardID . "-", 1);
          AddDecisionQueue("ADDCURRENTEFFECT", $mainPlayer, "<-", 1);
        }
        break;
      case "deadly_duo_red": case "deadly_duo_yellow": case "deadly_duo_blue":
        AddCurrentTurnEffectFromCombat($cardID, $mainPlayer);
        break;
      case "onetwo_punch_red": case "onetwo_punch_yellow": case "onetwo_punch_blue":
        if(ComboActive() && IsHeroAttackTarget())
        {
          AddDecisionQueue("DEALDAMAGE", $defPlayer, "2-" . $cardID . "-DAMAGE", 1);
        }
        break;
      case "barbed_undertow_red":
        if(HasAimCounter() && IsHeroAttackTarget()) {
          AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a pitch value", 1);
          AddDecisionQueue("BUTTONINPUT", $mainPlayer, "1,2,3", 1);
          AddDecisionQueue("SETDQVAR", $mainPlayer, "0", 1);
          AddDecisionQueue("PREPENDLASTRESULT", $mainPlayer, "barbed_undertow_red-", 1);
          AddDecisionQueue("ADDCURRENTANDNEXTTURNEFFECT", $defPlayer, "<-", 1);
          AddDecisionQueue("PASSPARAMETER", $mainPlayer, "{0}", 1);
          AddDecisionQueue("PREPENDLASTRESULT", $mainPlayer, "Cannot_pitch_", 1);
          AddDecisionQueue("APPENDLASTRESULT", $mainPlayer, "_resource_cards_this_turn_and_next", 1);
          AddDecisionQueue("WRITELOG", $mainPlayer, "<-", 1);
        }
        break;
      case "infecting_shot_red": case "infecting_shot_yellow": case "infecting_shot_blue":
        if(IsHeroAttackTarget()) PlayAura($CID_BloodRotPox, $defPlayer, effectController: $mainPlayer);
        break;
      case "sedation_shot_red": case "sedation_shot_yellow": case "sedation_shot_blue":
        if(IsHeroAttackTarget()) PlayAura($CID_Inertia, $defPlayer, effectController: $mainPlayer);
        break;
      case "withering_shot_red": case "withering_shot_yellow": case "withering_shot_blue":
        if(IsHeroAttackTarget()) PlayAura($CID_Frailty, $defPlayer, effectController: $mainPlayer);
        break;
      case "stab_wound_blue":
        $numDaggerHits = 0;
        for($i=0; $i<count($chainLinks); ++$i)
        {
          if(CardSubType($chainLinks[$i][0]) == "Dagger" && $chainLinkSummary[$i*ChainLinkSummaryPieces()] > 0) ++$numDaggerHits;
        }
        $numDaggerHits += $combatChainState[$CCS_FlickedDamage];
        if($numDaggerHits > 0) WriteLog("Player " . $defPlayer . " lost " . $numDaggerHits . " life from " . CardLink("stab_wound_blue", "stab_wound_blue"));
        LoseHealth($numDaggerHits, $defPlayer);
        break;
      case "plunge_red": case "plunge_yellow": case "plunge_blue":
        AddCurrentTurnEffect($cardID, $mainPlayer);
        break;
      case "death_touch_red": case "death_touch_yellow": case "death_touch_blue":
        if(IsHeroAttackTarget())
        {
          AddDecisionQueue("CHOOSECARD", $mainPlayer, $CID_BloodRotPox . "," . $CID_Frailty . "," . $CID_Inertia);
          AddDecisionQueue("PUTPLAY", $defPlayer, $mainPlayer, 1);
        }
        break;
      case "amnesia_red":
        if(IsHeroAttackTarget())
        {
          AddCurrentTurnEffect($cardID, $defPlayer);
          AddNextTurnEffect($cardID, $defPlayer);
        }
        break;
      case "humble_red": case "humble_yellow": case "humble_blue":
        if(IsHeroAttackTarget())
        {
          AddCurrentTurnEffect($cardID, $defPlayer);
          AddNextTurnEffect($cardID, $defPlayer);
          $char = &GetPlayerCharacter($defPlayer);
          $char[1] = 3;
        }
        break;
      case "wreck_havoc_red": case "wreck_havoc_yellow": case "wreck_havoc_blue":
        SetArsenalFacing("UP", $defPlayer);
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRARS:type=DR");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want to destroy", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZDESTROY", $mainPlayer, "-", 1);
        break;
      case "cut_down_to_size_red": case "cut_down_to_size_yellow": case "cut_down_to_size_blue":
        if(IsHeroAttackTarget())
        {
          $hand = &GetHand($defPlayer);
          if(count($hand) >= 4) PummelHit($defPlayer);
        }
        break;
      case "destructive_deliberation_red": case "destructive_deliberation_yellow": case "destructive_deliberation_blue":
        PlayAura("ponder", $mainPlayer);//Ponder
        break;
      default: break;
    }
  }

  function CodexOfFrailty($player)
  {
    $otherPlayer = ($player == 1 ? 2 : 1);
    $conditionPlayerMet = false;
    $conditionOtherPlayerMet = false;
    if(!ArsenalFull($player) && SearchDiscard($player, "AA") != "")
    {
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose a card from your graveyard.");
      MZMoveCard($player, "MYDISCARD:type=AA", "MYARS,GY,DOWN");
      $conditionPlayerMet = true;
    }
    if(!ArsenalFull($otherPlayer) && SearchDiscard($otherPlayer, "AA") != "")
    {
      AddDecisionQueue("SETDQCONTEXT", $otherPlayer, "Choose a card from your graveyard.");
      MZMoveCard($otherPlayer, "MYDISCARD:type=AA", "MYARS,GY,DOWN");
      $conditionOtherPlayerMet = true;
    }
    if($conditionPlayerMet) {
      AddDecisionQueue("FINDINDICES", $player, "HAND");
      AddDecisionQueue("SETDQCONTEXT", $player, "Choose a card from your hand to discard.");
      AddDecisionQueue("CHOOSEHAND", $player, "<-", 1);
      AddDecisionQueue("REMOVEMYHAND", $player, "-", 1);
      AddDecisionQueue("DISCARDCARD", $player, "HAND-".$player, 1);   
    }
    if($conditionOtherPlayerMet) {
      AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
      AddDecisionQueue("SETDQCONTEXT", $otherPlayer, "Choose a card from your hand to discard.");
      AddDecisionQueue("CHOOSEHAND", $otherPlayer, "<-", 1);
      AddDecisionQueue("REMOVEMYHAND", $otherPlayer, "-", 1);
      AddDecisionQueue("DISCARDCARD", $otherPlayer, "HAND-".$player, 1);   
    }
  }

  function HasStealth($cardID)
  {
    switch($cardID)
    {
      case "infiltrate_red":
      case "back_stab_red": case "back_stab_yellow": case "back_stab_blue":
      case "infect_red": case "infect_yellow": case "infect_blue":
      case "isolate_red": case "isolate_yellow": case "isolate_blue":
      case "malign_red": case "malign_yellow": case "malign_blue":
      case "prowl_red": case "prowl_yellow": case "prowl_blue":
      case "sedate_red": case "sedate_yellow": case "sedate_blue":
      case "wither_red": case "wither_yellow": case "wither_blue":
      case "bonds_of_agony_blue": case "persuasive_prognosis_blue":
      case "art_of_desire_body_red": case "art_of_desire_soul_yellow": case "art_of_desire_mind_blue": 
      case "bonds_of_attraction_red": case "bonds_of_attraction_yellow": case "bonds_of_attraction_blue": 
      case "double_trouble_red": case "double_trouble_yellow": case "double_trouble_blue": 
      case "bonds_of_memory_red": case "bonds_of_memory_yellow": case "bonds_of_memory_blue": 
      case "desires_of_flesh_red": case "desires_of_flesh_yellow": case "desires_of_flesh_blue": 
      case "impulsive_desire_red": case "impulsive_desire_yellow": case "impulsive_desire_blue": 
      case "minds_desire_red": case "minds_desire_yellow": case "minds_desire_blue":
      case "pick_to_pieces_red": case "pick_to_pieces_yellow": case "pick_to_pieces_blue":
      case "kiss_of_death_red": case "under_the_trapdoor_blue":
      case "bite_red": case "bite_yellow": case "bite_blue":
      case "whittle_from_bone_red": case "whittle_from_bone_yellow": case "whittle_from_bone_blue":
      case "defang_the_dragon_red": case "extinguish_the_flames_red":
      case "mark_of_the_black_widow_red": case "mark_of_the_black_widow_yellow": case "mark_of_the_black_widow_blue":
      case "mark_of_the_funnel_web_red": case "mark_of_the_funnel_web_yellow": case "mark_of_the_funnel_web_blue":
      case "mark_the_prey_red": case "mark_the_prey_yellow": case "mark_the_prey_blue":
      case "plunge_the_prospect_red": case "plunge_the_prospect_yellow": case "plunge_the_prospect_blue":
      case "reapers_call_red": case "reapers_call_yellow": case "reapers_call_blue":
      case "scuttle_the_canal_red": case "scuttle_the_canal_yellow": case "scuttle_the_canal_blue":
      case "graphene_chelicera":
        return true;
      default:
        return false;
    }
  }

  function ThrowWeapon($subtype, $source, $optional = false, $destroy = true, $onHitDraw = false, $target = "-")
  {
    global $currentPlayer, $CCS_HitThisLink, $CCS_FlickedDamage;
    $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
    if ($target == "-") {
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:subtype=" . $subtype . "&COMBATCHAINATTACKS:subtype=$subtype;type=AA");
      AddDecisionQueue("REMOVEINDICESIFACTIVECHAINLINK", $currentPlayer, "<-", 1);
      if($optional) AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      else AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
    }
    else {
      $targetArr = explode("-", $target);
      if ($targetArr[0] == "COMBATCHAINATTACKS") {
        $ccAttacks = GetCombatChainAttacks();
        if ($ccAttacks[$targetArr[1] + 2] != 1) {
          WriteLog("The targetted dagger is no longer there, the layer fails to resolve");
          return;
        }
        $targetInd = $target;
      }
      else {
        $char = GetPlayerCharacter($currentPlayer);
        $ind = SearchCharacterForUniqueID($targetArr[1], $currentPlayer);
        if ($ind == -1 || $char[$ind + 1] == 0) {
          WriteLog("The targetted dagger is no longer there, the layer fails to resolve");
          return;
        }
        $targetInd = "MYCHAR-$ind";
      }
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, $targetInd, 1);
    }
    AddDecisionQueue("SETDQVAR", $currentPlayer, "2", 1);
    if ($destroy) AddDecisionQueue("MZDESTROY", $currentPlayer, "-", 1);
    else AddDecisionQueue("MZOP", $currentPlayer, "GETCARDID", 1);
    AddDecisionQueue("SETDQVAR", $currentPlayer, "1", 1);
    AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "1-", 1);
    AddDecisionQueue("APPENDLASTRESULT", $currentPlayer, "-DAMAGE", 1);
    AddDecisionQueue("DEALDAMAGE", $otherPlayer, "<-", 1);
    AddDecisionQueue("INCREMENTCOMBATCHAINSTATEBY", $currentPlayer, $CCS_FlickedDamage, 1);
    AddDecisionQueue("LESSTHANPASS", $currentPlayer, "1", 1);
    AddDecisionQueue("PASSPARAMETER", $currentPlayer, "{1}", 1);
    AddDecisionQueue("ONHITEFFECT", $otherPlayer, "$source", 1);
    AddDecisionQueue("PASSPARAMETER", $currentPlayer, "1", 1);
    AddDecisionQueue("SETCOMBATCHAINSTATE", $currentPlayer, $CCS_HitThisLink, 1);
    if ($onHitDraw) AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
  }

  function DamageDealtBySubtype($subtype)
  {
    global $chainLinks, $chainLinkSummary, $combatChainState, $CCS_FlickedDamage;
    $damage = 0;
    for($i=0; $i<count($chainLinks); ++$i)
    {
      if(CardSubType($chainLinks[$i][0]) == $subtype) $damage += $chainLinkSummary[$i*ChainLinkSummaryPieces()];
    }
    if ($subtype == "Dagger") $damage += $combatChainState[$CCS_FlickedDamage];
    return $damage;
  }

  function NumAttackReactionsPlayed()
  {
    global $combatChain, $mainPlayer, $layers;
    $numReactions = 0;
    for($i=CombatChainPieces(); $i<count($combatChain); $i+=CombatChainPieces())
    {
      if($combatChain[$i+1] == $mainPlayer && (CardType($combatChain[$i]) == "AR" || GetAbilityType($combatChain[$i]) == "AR")) ++$numReactions;
    }
    for($i=0; $i<count($layers); $i+=LayerPieces())
    {
      if($layers[$i+1] != $mainPlayer) continue;
      if(CardType($layers[$i]) == "AR" || GetAbilityType($layers[$i]) == "AR") ++$numReactions;
    }
    return $numReactions;
  }

  function TrapTriggered($cardID)
  {
    global $mainPlayer, $defPlayer;
    $char = &GetPlayerCharacter($defPlayer);
    $characterID = ShiyanaCharacter($char[0], $defPlayer);
    if($char[1] == 2 && $characterID == "riptide_lurker_of_the_deep" || $characterID == "riptide")
    {
      DamageTrigger($mainPlayer, 1, "DAMAGE", $characterID, $cardID);
    }
  }

  function LookAtTopCard($player, $source, $showHand=false)
  {
    $otherPlayer = ($player == 1 ? 2 : 1);
    AddDecisionQueue("PASSPARAMETER", $player, "ELSE");
    AddDecisionQueue("SETDQVAR", $player, "1");
    AddDecisionQueue("SETDQCONTEXT", $player, "Choose target hero");
    AddDecisionQueue("BUTTONINPUT", $player, "Target_Opponent,Target_Yourself");
    AddDecisionQueue("EQUALPASS", $player, "Target_Opponent");
    AddDecisionQueue("WRITELOG", $player, "Shows your top deck", 1);
    AddDecisionQueue("DECKCARDS", $player, "0", 1);
    AddDecisionQueue("SETDQVAR", $player, "1", 1);
    AddDecisionQueue("SETDQCONTEXT", $player, CardName($source) . " shows the top of your deck is <1>", 1);
    AddDecisionQueue("OK", $player, "-", 1);
    AddDecisionQueue("PASSPARAMETER", $player, "{1}");
    AddDecisionQueue("NOTEQUALPASS", $player, "ELSE");
    if($showHand) {
      AddDecisionQueue("WRITELOG", $otherPlayer, "Shows opponent's hand", 1);
      AddDecisionQueue("SHOWHANDWRITELOG", $otherPlayer, "-", 1);
    }
    else AddDecisionQueue("WRITELOG", $otherPlayer, "Shows opponent's top deck", 1);
    AddDecisionQueue("DECKCARDS", $otherPlayer, "0", 1);
    AddDecisionQueue("SETDQVAR", $otherPlayer, "1", 1);
    AddDecisionQueue("SETDQCONTEXT", $otherPlayer, CardName($source) . " shows the top of their deck is <1>", 1);
    AddDecisionQueue("OK", $player, "-", 1);
    AddDecisionQueue("SETDQCONTEXT", $player, "-");
  }

  function SpireSnipingAbility($player)
  {
    AddDecisionQueue("PASSPARAMETER", $player, "0,1");
    AddDecisionQueue("MULTIREMOVEDECK", $player, "-");
    AddDecisionQueue("SETDQCONTEXT", $player, "Choose a card to put back on top", 1);
    AddDecisionQueue("CHOOSETOP", $player, "<-");
  }

  function Belch()
  {
    global $currentPlayer;
    if(!CanRevealCards($currentPlayer)) return "";
    $cardRemoved = "";
    $deck = &GetDeck($currentPlayer);
    $cardsToReveal = "";
    for($i=0; $i<count($deck); ++$i)
    {
      if($cardsToReveal != "") $cardsToReveal .= ",";
      $cardsToReveal .= $deck[$i];
      if(CardType($deck[$i]) == "AA")
      {
        $cardRemoved = $deck[$i];
        unset($deck[$i]);
        $deck = array_values($deck);
        break;
      }
    }
    RevealCards($cardsToReveal);
    AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
    return $cardRemoved;
  }
