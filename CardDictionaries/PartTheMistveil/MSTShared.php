<?php
function MSTAbilityType($cardID, $index = -1, $from = "-"): string
{
  return match ($cardID) {
    "nuu_alluring_desire", "nuu", "enigma_ledger_of_ancestry", "enigma", "meridian_pathway", "zen_tamer_of_purpose", "zen", "twelve_petal_kasaya", "truths_retold", "uphold_tradition", "aqua_seeing_shell",
    "skycrest_keikoi", "skybody_keikoi", "skyhold_keikoi", "skywalker_keikoi", "restless_coalescence_yellow", "longdraw_halfglove", "enigma_new_moon" => "I",
    "beckoning_mistblade", "tiger_taming_khakkara" => "AA",
    "mask_of_recurring_nightmares", "arousing_wave", "undertow_stilettos", "waves_of_aqua_marine", "aqua_laps" => "AR",
    default => ""
  };
}

function MSTAbilityCost($cardID): int
{
  return match ($cardID) {
    "nuu_alluring_desire", "mask_of_recurring_nightmares", "meridian_pathway", "enigma", "enigma_ledger_of_ancestry", "twelve_petal_kasaya", "aqua_seeing_shell", "zen", "zen_tamer_of_purpose", "enigma_new_moon", "nuu" => 3,
    "tiger_taming_khakkara", "beckoning_mistblade" => 2,
    "arousing_wave", "uphold_tradition", "truths_retold", "aqua_laps", "waves_of_aqua_marine", "undertow_stilettos" => 1,
    default => 0
  };
}

function MSTCombatEffectActive($cardID, $attackID): bool
{
  global $mainPlayer, $combatChainState, $CCS_LinkBaseAttack, $CombatChain;
  $from = $CombatChain->AttackCard()->From();
  $idArr = explode(",", $cardID);
  $cardID = $idArr[0];
  return match ($cardID) {
    "mistcloak_gully" => IsHeroAttackTarget(),
    "beckoning_mistblade", "first_tenet_of_chi_moon_blue", "first_tenet_of_chi_tide_blue", "prismatic_leyline_yellow-BLUE" => ColorContains($attackID, 3, $mainPlayer),
    "wind_chakra_red-1", "wind_chakra_yellow-1", "wind_chakra_blue-1", "wind_chakra_red-2", "wind_chakra_yellow-2", "wind_chakra_blue-2", "sacred_art_jade_tiger_domain_blue", "tiger_form_incantation_red", "tiger_form_incantation_yellow", "tiger_form_incantation_blue",
    "tiger_taming_khakkara", "chase_the_tail_red", "untamed_red", "untamed_yellow", "untamed_blue" => CardNameContains($attackID, "Crouching Tiger", $mainPlayer),
    "first_tenet_of_chi_wind_blue" => $from != "PLAY" && ColorContains($attackID, 3, $mainPlayer) && (TypeContains($attackID, "AA", $mainPlayer) || TypeContains($attackID, "A", $mainPlayer)),
    "prismatic_leyline_yellow-RED" => ColorContains($attackID, 1, $mainPlayer),
    "prismatic_leyline_yellow-YELLOW" => ColorContains($attackID, 2, $mainPlayer),
    "water_the_seeds_red", "water_the_seeds_yellow", "water_the_seeds_blue" => $combatChainState[$CCS_LinkBaseAttack] <= 1,
    "longdraw_halfglove" => CardSubType($attackID) == "Arrow",
    "tide_chakra_red-1", "tide_chakra_yellow-1", "tide_chakra_blue-1", "tide_chakra_red-2", "tide_chakra_yellow-2", "tide_chakra_blue-2", "hiss_red", "hiss_yellow", "hiss_blue", "intimate_inducement_red-BUFF",
    "intimate_inducement_yellow-BUFF", "intimate_inducement_blue-BUFF", "venomous_bite_red", "venomous_bite_yellow", "venomous_bite_blue", "fang_strike", "slither", "tooth_and_claw_red-BUFF", "waves_of_aqua_marine", "attune_with_cosmic_vibrations_blue",
    "levels_of_enlightenment_blue", "dense_blue_mist_blue-HITPREVENTION", "dense_blue_mist_blue-DEBUFF", "deep_blue_sea_blue", "wide_blue_yonder_blue", "big_blue_sky_blue", "a_drop_in_the_ocean_blue", "the_grain_that_tips_the_scale_blue", "just_a_nick_red-BUFF",
    "just_a_nick_red-HIT", "maul_yellow-BUFF", "maul_yellow-HIT", "stonewall_gauntlet", "emissary_of_tides_red", "murky_water_red", "shadowrealm_horror_red-1", "shadowrealm_horror_red-2" => true,
    default => false,
  };
}

function MSTEffectAttackModifier($cardID): int
{
  global $mainPlayer;
  $idArr = explode(",", $cardID);
  $cardID = $idArr[0];
  return match ($cardID) {
    "a_drop_in_the_ocean_blue", "stonewall_gauntlet" => -1,
    "mistcloak_gully", "dense_blue_mist_blue-DEBUFF" => IsHeroAttackTarget() ? -1 : 0,
    "deep_blue_sea_blue", "wide_blue_yonder_blue" => SearchPitchForColor($mainPlayer, 3),
    "tide_chakra_red-2", "wind_chakra_red-2", "just_a_nick_red-BUFF" => 5,
    "tide_chakra_yellow-2", "wind_chakra_yellow-2", "longdraw_halfglove" => 4,
    "tide_chakra_red-1", "tide_chakra_blue-2", "hiss_red", "venomous_bite_red", "wind_chakra_red-1", "wind_chakra_blue-2", "tiger_form_incantation_red", "attune_with_cosmic_vibrations_blue", "chase_the_tail_red", "maul_yellow-BUFF",
    "prismatic_leyline_yellow-BLUE" => 3,
    "tide_chakra_yellow-1", "hiss_yellow", "venomous_bite_yellow", "wind_chakra_yellow-1", "tiger_form_incantation_yellow", "levels_of_enlightenment_blue", "first_tenet_of_chi_tide_blue", "prismatic_leyline_yellow-YELLOW", "emissary_of_tides_red" => 2,
    "beckoning_mistblade", "hiss_blue", "venomous_bite_blue", "fang_strike", "tooth_and_claw_red-BUFF", "sacred_art_jade_tiger_domain_blue", "wind_chakra_blue-1", "tiger_form_incantation_blue", "tide_chakra_blue-1", "intimate_inducement_red-BUFF",
    "intimate_inducement_yellow-BUFF", "intimate_inducement_blue-BUFF", "waves_of_aqua_marine", "the_grain_that_tips_the_scale_blue", "tiger_taming_khakkara", "untamed_red", "untamed_yellow", "untamed_blue", "prismatic_leyline_yellow-RED", "water_the_seeds_red",
    "water_the_seeds_yellow", "water_the_seeds_blue", "murky_water_red", "shadowrealm_horror_red-1" => 1,
    default => 0,
  };
}

function MSTPlayAbility($cardID, $from, $resourcesPaid, $target = "-", $additionalCosts = "")
{
  global $currentPlayer, $CS_NumBluePlayed, $CS_Transcended, $mainPlayer, $CS_DamagePrevention, $CS_PlayIndex;
  global $combatChain, $defPlayer, $CombatChain, $chainLinks, $combatChainState, $CCS_LinkBaseAttack, $CS_NumAttacks;
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  $hand = &GetHand($currentPlayer);
  switch ($cardID) {
    case "mistcloak_gully":
      if (GetClassState($otherPlayer, $CS_NumAttacks) <= 1) AddCurrentTurnEffect($cardID, $otherPlayer);
      return "";
    case "nuu_alluring_desire":
    case "nuu":
      AddDecisionQueue("DECKCARDS", $otherPlayer, "0");
      AddDecisionQueue("SETDQVAR", $otherPlayer, "0");
      AddDecisionQueue("ALLCARDPITCHORPASS", $currentPlayer, "3", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Do you want to banish <0> with " . CardLink($cardID, $cardID)."?");
      AddDecisionQueue("YESNO", $currentPlayer, "whether to banish a card with " . CardLink($cardID, $cardID), 1);
      AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
      AddDecisionQueue("PARAMDELIMTOARRAY", $currentPlayer, "0", 1);
      AddDecisionQueue("MULTIREMOVEDECK", $otherPlayer, "0", 1);
      AddDecisionQueue("MULTIBANISH", $otherPlayer, "DECK,-," . $cardID, 1);
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, "{0}");
      AddDecisionQueue("NONECARDPITCHORPASS", $currentPlayer, "3");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, CardLink($cardID, $cardID)." shows the top of your deck is <0>");
      AddDecisionQueue("OK", $currentPlayer, "whether to banish a card with " . CardLink($cardID, $cardID), 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "-");
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "mask_of_recurring_nightmares":
      AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
      AddDecisionQueue("SETDQCONTEXT", $otherPlayer, "Choose a card to banish", 1);
      AddDecisionQueue("CHOOSEHAND", $otherPlayer, "<-", 1);
      AddDecisionQueue("MULTIREMOVEHAND", $otherPlayer, "-", 1);
      AddDecisionQueue("BANISHCARD", $otherPlayer, "HAND,-", 1);
      return "";
    case "arousing_wave":
      AddPlayerHand("fang_strike", $currentPlayer, $cardID); //Fang Strike
      return "";
    case "undertow_stilettos":
      AddPlayerHand("slither", $currentPlayer, $cardID); //Slither
      return "";
    case "gorgons_gaze_yellow":
      AddPlayerHand("slither", $currentPlayer, $cardID); //Slither
      $mod = "-";
      if (SearchCardList($additionalCosts, $currentPlayer, subtype: "Chi") != "") $mod = "TCCGorgonsGaze";
      $defendingCards = GetChainLinkCards($defPlayer);
      if (!empty($defendingCards)) {
        $defendingCards = explode(",", $defendingCards);
        foreach (array_reverse($defendingCards) as $card) {
          if (CardType($combatChain[$card]) === "AA") {
            BanishCardForPlayer($combatChain[$card], $defPlayer, "CC", $mod, $cardID);
            $index = GetCombatChainIndex($combatChain[$card], $defPlayer);
            $CombatChain->Remove($index);
          }
        }
      }
      foreach ($chainLinks as &$link) {
        for ($k = 0; $k < count($link); $k += ChainLinksPieces()) {
          if (CardType($link[$k]) == "AA" && $link[$k + 1] == $defPlayer) {
            BanishCardForPlayer($link[$k], $defPlayer, "CC", $mod, $cardID);
            $link[$k + 2] = 0;
          }
        }
      }
      return "";
    case "sirens_call_red":
      $deck = new Deck($defPlayer);
      if (IsHeroAttackTarget()) {
        LookAtHand($defPlayer);
        AddDecisionQueue("FINDINDICES", $otherPlayer, "HANDPITCH,3");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want to add to the chain link", 1);
        AddDecisionQueue("CHOOSETHEIRHAND", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEHAND", $otherPlayer, "-", 1);
        AddDecisionQueue("ADDCARDTOCHAINASDEFENDINGCARD", $otherPlayer, "HAND", 1);
        AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
      }
      return "";
    case "sacred_art_undercurrent_desires_blue":
      if ($additionalCosts != "-") {
        $modes = explode(",", $additionalCosts);
        for ($i = 0; $i < count($modes); ++$i) {
          switch ($modes[$i]) {
            case "Create_a_Fang_Strike_and_Slither":
              AddPlayerHand("fang_strike", $currentPlayer, $cardID); //Fang Strike
              AddPlayerHand("slither", $currentPlayer, $cardID); //Slither
              break;
            case "Banish_up_to_2_cards_in_an_opposing_hero_graveyard":
              AddDecisionQueue("FINDINDICES", $otherPlayer, $cardID);
              AddDecisionQueue("MULTICHOOSETHEIRDISCARD", $currentPlayer, "<-", 1);
              AddDecisionQueue("MULTIREMOVEDISCARD", $otherPlayer, "-", 1);
              AddDecisionQueue("MULTIBANISH", $otherPlayer, "DISCARD,Source-" . $cardID . "," . $cardID, 1);
              AddDecisionQueue("UNDERCURRENTDESIRES", $currentPlayer, "-", 1);
              break;
            case "Transcend":
              Transcend($currentPlayer, "MST010_inner_chi_blue", $from);
              break;
            default:
              break;
          }
        }
      }
      return "";
    case "tide_chakra_red":
    case "tide_chakra_yellow":
    case "tide_chakra_blue":
    case "wind_chakra_red":
    case "wind_chakra_yellow":
    case "wind_chakra_blue":
    case "moon_chakra_red":
    case "moon_chakra_yellow":
    case "moon_chakra_blue":
      if (GetClassState($currentPlayer, $CS_Transcended) <= 0) AddCurrentTurnEffect($cardID . "-1", $currentPlayer);
      else AddCurrentTurnEffect($cardID . "-2", $currentPlayer);
      return "";
    case "hiss_red":
    case "hiss_yellow":
    case "hiss_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      if (SearchPitchForColor($currentPlayer, 3) > 0) AddPlayerHand("slither", $currentPlayer, $cardID); //Slither
      return "";
    case "intimate_inducement_red":
    case "intimate_inducement_yellow":
    case "intimate_inducement_blue":
      $amount = 4;
      if ($cardID == "intimate_inducement_yellow") $amount = 3;
      elseif ($cardID == "intimate_inducement_blue") $amount = 2;
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Here's the card that on top of your deck.", 1);
      AddDecisionQueue("FINDINDICES", $otherPlayer, "DECKTOPXINDICES," . $amount);
      AddDecisionQueue("DECKCARDS", $currentPlayer, "<-", 1);
      AddDecisionQueue("LOOKTOPDECK", $currentPlayer, "<-", 1);
      AddDecisionQueue("CHOOSETHEIRDECK", $currentPlayer, "<-", 1);
      AddDecisionQueue("ADDCARDTOCHAINASDEFENDINGCARD", $otherPlayer, "DECK", 1);
      AddDecisionQueue("ALLCARDPITCHORPASS", $currentPlayer, "3", 1);
      AddDecisionQueue("PUTCOMBATCHAINDEFENSE0", $otherPlayer, "-", 1);
      AddDecisionQueue("PUTINANYORDER", $currentPlayer, $amount - 1);
      AddCurrentTurnEffect($cardID . "-BUFF", $currentPlayer);
      return "";
    case "venomous_bite_red":
    case "venomous_bite_yellow":
    case "venomous_bite_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      if (SearchPitchForColor($currentPlayer, 3) > 0) AddPlayerHand("fang_strike", $currentPlayer, $cardID); //Fang Strike
      return "";
    case "fang_strike":
    case "slither":
    case "meridian_pathway":
    case "waves_of_aqua_marine":
    case "deep_blue_sea_blue":
    case "wide_blue_yonder_blue":
    case "big_blue_sky_blue":
    case "first_tenet_of_chi_moon_blue":
    case "first_tenet_of_chi_tide_blue":
    case "first_tenet_of_chi_wind_blue":
    case "tiger_taming_khakkara":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "enigma_ledger_of_ancestry":
    case "enigma":
      PlayAura("spectral_shield", $currentPlayer, 1, numAttackCounters: 1);
      return "";
    case "truths_retold":
      MZMoveCard($currentPlayer, "MYDISCARD:subtype=Aura", "MYBOTDECK");
      return "";
    case "uphold_tradition":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYAURAS:hasWard=true", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose target aura");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZADDCOUNTERS", $currentPlayer, "1", 1);
      return "";
    case "sacred_art_immortal_lunar_shrine_blue":
      if ($additionalCosts != "-") {
        $modes = explode(",", $additionalCosts);
        for ($i = 0; $i < count($modes); ++$i) {
          switch ($modes[$i]) {
            case "Create_2_Spectral_Shield":
              PlayAura("spectral_shield", $currentPlayer, 2);
              break;
            case "Put_a_+1_counter_on_each_aura_with_ward_you_control":
              AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYAURAS:hasWard=true", 1);
              AddDecisionQueue("ADDALLATTACKCOUNTERS", $currentPlayer, "1", 1);
              break;
            case "Transcend":
              Transcend($currentPlayer, "MST032_inner_chi_blue", $from);
              break;
            default:
              break;
          }
        }
      }
      return "";
    case "zen_tamer_of_purpose":
    case "zen":
      AddPlayerHand("crouching_tiger", $currentPlayer, "NA");
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDECK:comboOnly=true", 1);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZADDZONE", $currentPlayer, "MYBANISH,DECK,TT", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
      return "";
    case "twelve_petal_kasaya":
      PlayAura("zen_state", $currentPlayer); //Zen Token
      return "";
    case "tooth_and_claw_red":
      if (CanRevealCards($currentPlayer)) {
        AddDecisionQueue("FINDINDICES", $currentPlayer, "CROUCHINGTIGERHAND");
        AddDecisionQueue("LESSTHANPASS", $currentPlayer, "1", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which cards to reveal", 1);
        AddDecisionQueue("MAYMULTICHOOSEHAND", $currentPlayer, "<-", 1);
        AddDecisionQueue("REVEALHANDCARDS", $currentPlayer, "-", 1);
        AddDecisionQueue("TOOTHANDCLAW", $currentPlayer, "-", 1);
      }
      break;
    case "shifting_winds_of_the_mystic_beast_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      if (SearchCardList($additionalCosts, $currentPlayer, subtype: "Chi") != "") AddPlayerHand("crouching_tiger", $currentPlayer, "NA", 2);
      return "";
    case "sacred_art_jade_tiger_domain_blue":
      if ($additionalCosts != "-") {
        $modes = explode(",", $additionalCosts);
        for ($i = 0; $i < count($modes); ++$i) {
          switch ($modes[$i]) {
            case "Create_2_Crouching_Tigers":
              AddPlayerHand("crouching_tiger", $currentPlayer, "NA", 2);
              break;
            case "Crouching_Tigers_Get_+1_this_turn":
              AddCurrentTurnEffect($cardID, $currentPlayer);
              break;
            case "Transcend":
              Transcend($currentPlayer, "MST053_inner_chi_blue", $from);
              break;
            default:
              break;
          }
        }
      }
      return "";
    case "companion_of_the_claw_red":
    case "companion_of_the_claw_yellow":
    case "companion_of_the_claw_blue":
    case "harmony_of_the_hunt_red":
    case "harmony_of_the_hunt_yellow":
    case "harmony_of_the_hunt_blue":
      if (SearchPitchForColor($currentPlayer, 3) > 0) AddPlayerHand("crouching_tiger", $currentPlayer, $cardID);
      return "";
    case "tiger_form_incantation_red":
    case "tiger_form_incantation_yellow":
    case "tiger_form_incantation_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      if (SearchPitchForColor($currentPlayer, 3) > 0) AddPlayerHand("crouching_tiger", $currentPlayer, $cardID);
      return "";
    case "aqua_seeing_shell":
      Draw($currentPlayer);
      return "";
    case "aqua_laps":
      GiveAttackGoAgain();
      return "";
    case "skycrest_keikoi":
    case "skybody_keikoi":
    case "skyhold_keikoi":
    case "skywalker_keikoi":
      IncrementClassState($currentPlayer, $CS_DamagePrevention);
      return "";
    case "attune_with_cosmic_vibrations_blue":
      if (IsHeroAttackTarget()) {
        $deck = new Deck($defPlayer);
        if ($deck->Reveal(1) && PitchValue($deck->Top()) == 3) {
          AddCurrentTurnEffect($cardID, $currentPlayer);
        }
      }
      return "";
    case "cosmic_awakening_blue":
      $chiArray = explode(",", SearchCardList($additionalCosts, $currentPlayer, subtype: "Chi"));
      $amountChiPitch = count($chiArray);
      if (SearchCardList($additionalCosts, $currentPlayer, subtype: "Chi") != "") {
        switch ($amountChiPitch) {
          case 1:
            $combatChainState[$CCS_LinkBaseAttack] = 10;
            break;
          case 2:
            $combatChainState[$CCS_LinkBaseAttack] = 15;
            break;
          case 3:
            $combatChainState[$CCS_LinkBaseAttack] = 20;
            break;
          default:
            break;
        }
      }
      return "";
    case "levels_of_enlightenment_blue":
      $modalities = "Draw_a_card,Buff_Power,Go_again";
      $numChoices = SearchPitchForColor($currentPlayer, 3);
      if ($numChoices >= 3) {
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, $modalities);
        AddDecisionQueue("MODAL", $currentPlayer, "LEVELSOFENLIGHTENMENT", 1);
        AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      } elseif ($numChoices < 3 && $numChoices > 0) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose " . $numChoices . " modes");
        AddDecisionQueue("MULTICHOOSETEXT", $currentPlayer, $numChoices . "-" . $modalities . "-" . $numChoices);
        AddDecisionQueue("MODAL", $currentPlayer, "LEVELSOFENLIGHTENMENT", 1);
        AddDecisionQueue("SHOWMODES", $currentPlayer, $cardID, 1);
      }
      return "";
    case "unravel_aggression_blue":
      if (SearchCardList($additionalCosts, $currentPlayer, subtype: "Chi") != "") Draw($currentPlayer);
      return "";
    case "dense_blue_mist_blue":
      AddCurrentTurnEffect($cardID . "-DEBUFF", $otherPlayer);
      if (SearchCardList($additionalCosts, $currentPlayer, subtype: "Chi") != "") AddCurrentTurnEffect($cardID . "-HITPREVENTION", $currentPlayer);
      return "";
    case "orihon_of_mystic_tenets_blue":
      Draw($currentPlayer);
      Draw($currentPlayer);
      if (SearchCardList($additionalCosts, $currentPlayer, subtype: "Chi") != "") Draw($currentPlayer);
      return "";
    case "a_drop_in_the_ocean_blue":
      if ($CombatChain->HasCurrentLink() || HasAttackLayer()) {
        AddCurrentTurnEffect($cardID, $mainPlayer);
      }
      if (GetClassState($currentPlayer, $CS_NumBluePlayed) > 1) AddDecisionQueue("TRANSCEND", $currentPlayer, "MST095_inner_chi_blue," . $from);
      return "";
    case "homage_to_ancestors_blue":
      GainHealth(1, $currentPlayer);
      if (GetClassState($currentPlayer, $CS_NumBluePlayed) > 1) AddDecisionQueue("TRANSCEND", $currentPlayer, "MST096_inner_chi_blue," . $from);
      return "";
    case "pass_over_blue":
      $params = explode("-", $target);
      $index = SearchdiscardForUniqueID($params[1], $otherPlayer);
      if ($index != -1) {
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "THEIRDISCARD-" . $index, 1);
        AddDecisionQueue("MZADDZONE", $currentPlayer, "THEIRBANISH,GY,-," . $cardID, 1);
        AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        if (GetClassState($currentPlayer, $CS_NumBluePlayed) > 1) AddDecisionQueue("TRANSCEND", $currentPlayer, "MST097_inner_chi_blue," . $from);
      } else {
        WriteLog(CardLink($cardID, $cardID) . " layer fails as there are no remaining targets for the targeted effect and this card does not transcend.");
        return "FAILED";
      }
      return "";
    case "path_well_traveled_blue":
      GiveAttackGoAgain();
      if (GetClassState($currentPlayer, $CS_NumBluePlayed) > 1) AddDecisionQueue("TRANSCEND", $currentPlayer, "MST098_inner_chi_blue," . $from);
      return "";
    case "preserve_tradition_blue":
      $params = explode("-", $target);
      $index = SearchdiscardForUniqueID($params[1], $currentPlayer);
      if ($index != -1) {
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "MYDISCARD-" . $index, 1);
        AddDecisionQueue("MZADDZONE", $currentPlayer, "MYBOTDECK", 1);
        AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        if (GetClassState($currentPlayer, $CS_NumBluePlayed) > 1) AddDecisionQueue("TRANSCEND", $currentPlayer, "MST099_inner_chi_blue," . $from);
      } else {
        WriteLog(CardLink($cardID, $cardID) . " layer fails as there are no remaining targets for the targeted effect and this card does not transcend.");
        return "FAILED";
      }
      return "";
    case "rising_sun_setting_moon_blue":
      Draw($currentPlayer);
      if (count($hand) == 1) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Show the card drawn:");
        AddDecisionQueue("OK", $currentPlayer, "<-", 1);
      }
      MZMoveCard($currentPlayer, "MYHAND", "MYBOTDECK", silent: true, DQContext: "Choose a card to put on the bottom of your deck:");
      if (GetClassState($currentPlayer, $CS_NumBluePlayed) > 1) AddDecisionQueue("TRANSCEND", $currentPlayer, "MST100_inner_chi_blue," . $from);
      return "";
    case "stir_the_pot_blue":
      AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
      if (GetClassState($currentPlayer, $CS_NumBluePlayed) > 1) AddDecisionQueue("TRANSCEND", $currentPlayer, "MST101_inner_chi_blue," . $from);
      return "";
    case "the_grain_that_tips_the_scale_blue":
      if ($CombatChain->HasCurrentLink() || HasAttackLayer()) {
        AddCurrentTurnEffect($cardID, $mainPlayer);
      }
      if (GetClassState($currentPlayer, $CS_NumBluePlayed) > 1) AddDecisionQueue("TRANSCEND", $currentPlayer, "MST102_inner_chi_blue," . $from);
      return "";
    case "just_a_nick_red":
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts, 1);
      AddDecisionQueue("MODAL", $currentPlayer, "JUSTANICK", 1);
      return "";
    case "rage_specter_blue":
      if ($from != "PLAY") {
        $illusionistAuras = SearchAura($currentPlayer, class: "ILLUSIONIST");
        $arrayAuras = explode(",", $illusionistAuras);
        if (count($arrayAuras) <= 1) GainActionPoints(1, $currentPlayer);
      }
      return "";
    case "restless_coalescence_yellow":
      $index = GetClassState($currentPlayer, $CS_PlayIndex);
      $auras = GetAuras($currentPlayer);
      $abilityType = GetResolvedAbilityType($cardID, $from);
      if ($from != "PLAY") {
        $count = CountAuraAtkCounters($currentPlayer) + 10; //+10 is an arbitrary number to keep the loop going until the player pass
        for ($i = 0; $i < $count; $i++) {
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYAURAS:hasAttackCounters=true", 1);
          AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose an aura to remove a -1 attack counter or pass", 1);
          AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("MZOP", $currentPlayer, "TRANSFERATKCOUNTER", 1);
        }
        AddCurrentTurnEffect($cardID, $currentPlayer, $from, $auras[count($auras) - AuraPieces() + 6]);
      }
      if ($abilityType != "I") return "";

      if (SearchCurrentTurnEffectsForUniqueID($auras[$index + 6] . "-PAID") != -1) {
        PlayAura("spectral_shield", $currentPlayer);
        RemoveCurrentTurnEffect(SearchCurrentTurnEffectsForUniqueID($auras[$index + 6] . "-PAID"));
      } elseif (SearchCurrentTurnEffectsForPartielID("PAID")) //It needs to check if the auras was destroy, but it's already paid for
      {
        PlayAura("spectral_shield", $currentPlayer);
        RemoveCurrentTurnEffect(SearchCurrentTurnEffectsForUniqueID($auras[$index + 6] . "-PAID"));
      } else {
        WriteLog("You do not have the counters to pay for " . CardLink($cardID, $cardID) . " ability.", highlight: true);
      }
      return "";
    case "astral_etchings_red":
    case "astral_etchings_yellow":
    case "astral_etchings_blue":
      $amount = 3;
      if ($cardID == "astral_etchings_yellow") $amount = 2;
      else if ($cardID == "astral_etchings_blue") $amount = 1;
      $params = explode("-", $target);
      if(substr($params[0], 0, 5) != "THEIR") {
        $zone = "MYAURAS-";
        $player = $currentPlayer;
      }
      else {
        $zone = "THEIRAURAS-";
        $player = $otherPlayer;
      }
      $index = SearchAurasForUniqueID($params[1], $player);
      if ($index != -1) {
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, $zone . $index, 1);
        AddDecisionQueue("MZADDCOUNTERS", $currentPlayer, $amount, 1);
      }
      else {
        WriteLog(CardLink($cardID, $cardID) . " layer fails as there are no remaining targets for the targeted effect.");
        return "FAILED";
      }
      return "";
    case "single_minded_determination_red":
    case "single_minded_determination_yellow":
    case "single_minded_determination_blue":
      if ($from != "PLAY") {
        $auras = &GetAuras($currentPlayer);
        $illusionistAuras = SearchAura($currentPlayer, class: "ILLUSIONIST");
        $arrayAuras = explode(",", $illusionistAuras);
        $amount = 3;
        if ($cardID == "single_minded_determination_yellow") $amount = 2;
        else if ($cardID == "single_minded_determination_blue") $amount = 1;
        if (count($arrayAuras) <= 1) {
          $index = count($auras) - AuraPieces();
          $auras[$index + 3] += $amount;
        }
      }
      return "";
    case "solitary_companion_red":
    case "solitary_companion_yellow":
    case "solitary_companion_blue":
      if ($from != "PLAY") {
        $illusionistAuras = SearchAura($currentPlayer, class: "ILLUSIONIST");
        $arrayAuras = explode(",", $illusionistAuras);
        if (count($arrayAuras) <= 1) PlayAura("spectral_shield", $currentPlayer);
      }
      return "";
    case "spectral_manifestations_red":
    case "spectral_manifestations_yellow":
    case "spectral_manifestations_blue":
      if (SearchAura($currentPlayer, class: "ILLUSIONIST") != "") $amount = 0;
      else if ($cardID == "spectral_manifestations_red") $amount = 3;
      else if ($cardID == "spectral_manifestations_yellow") $amount = 2;
      else if ($cardID == "spectral_manifestations_blue") $amount = 1;
      PlayAura("spectral_shield", $currentPlayer, numAttackCounters: $amount);
      return "";
    case "chase_the_tail_red":
      if (ComboActive()) AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "maul_yellow":
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, $additionalCosts, 1);
      AddDecisionQueue("MODAL", $currentPlayer, "MAUL", 1);
      return "";
    case "aspect_of_tiger_body_red":
    case "aspect_of_tiger_soul_yellow":
    case "aspect_of_tiger_mind_blue":
      if (ComboActive()) {
        BanishCardForPlayer("crouching_tiger", $currentPlayer, "-", "TT", $currentPlayer);
      }
      return "";
    case "breed_anger_red":
    case "breed_anger_yellow":
    case "breed_anger_blue":
      if (ComboActive()) {
        BanishCardForPlayer("crouching_tiger", $currentPlayer, "-", "TT", $currentPlayer);
        GiveAttackGoAgain();
      }
      return "";
    case "untamed_red":
    case "untamed_yellow":
    case "untamed_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "prismatic_leyline_yellow":
      AddCurrentTurnEffect($cardID . "-RED", $currentPlayer);
      AddCurrentTurnEffect($cardID . "-YELLOW", $currentPlayer);
      AddCurrentTurnEffect($cardID . "-BLUE", $currentPlayer);
      break;
    case "emissary_of_moon_red":
      if ($additionalCosts != "-") Draw($currentPlayer);
      break;
    case "emissary_of_tides_red":
      if ($additionalCosts != "-") AddCurrentTurnEffect($cardID, $currentPlayer);
      break;
    case "emissary_of_wind_red":
      if ($additionalCosts != "-") AddDecisionQueue("OP", $currentPlayer, "GIVEATTACKGOAGAIN", 1);
      break;
    case "gravekeeping_red":
    case "gravekeeping_yellow":
    case "gravekeeping_blue":
      if (IsHeroAttackTarget()) MZMoveCard($currentPlayer, "THEIRDISCARD", "THEIRBANISH", true, DQContext: "Choose a card to banish from their graveyard.");
      return "";
    case "water_the_seeds_red":
    case "water_the_seeds_yellow":
    case "longdraw_halfglove":
    case "water_the_seeds_blue":
      AddCurrentTurnEffectNextAttack($cardID, $currentPlayer);
      return "";
    case "visit_goldmane_estate_blue":
      PutItemIntoPlayForPlayer("gold", $currentPlayer, effectController: $currentPlayer);
      $numGold = CountItem("gold", $currentPlayer);
      if ($numGold >= 3 && !SearchCurrentTurnEffects("amnesia_red", $currentPlayer)) {
        PlayAura("might", $currentPlayer, $numGold); //Might
        WriteLog(CardLink($cardID, $cardID) . " created a Gold token and " . $numGold . " Might tokens");
      } else WriteLog(CardLink($cardID, $cardID) . " created a Gold token");
      return "";
    case "visit_the_golden_anvil_blue":
      for ($i = 0; $i < intval($additionalCosts); ++$i) {
        AddDecisionQueue("VISITTHEGOLDENANVIL", $currentPlayer, "-");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to equip");
        AddDecisionQueue("CHOOSECARD", $currentPlayer, "<-");
        AddDecisionQueue("APPENDLASTRESULT", $currentPlayer, "-INVENTORY");
        AddDecisionQueue("EQUIPCARDINVENTORY", $currentPlayer, "<-");
      }
      return "";
    case "supercell_blue":
      $cardList = SearchItemsByName($currentPlayer, "Hyper Driver");
      $countHyperDriver = count(explode(",", $cardList));
      if ($resourcesPaid > $countHyperDriver) $resourcesPaid = $countHyperDriver;
      for ($i = 0; $i < $resourcesPaid; $i++) {
        if ($i == 0) {
          AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYITEMS:isSameName=hyper_driver_red");
          AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
        }
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose " . $resourcesPaid . " Hyper Driver to get " . $resourcesPaid . " steam counter", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "{0}", 1);
        AddDecisionQueue("SUPERCELL", $currentPlayer, $resourcesPaid, 1);
      }
      AddDecisionQueue("PASSPARAMETER", $currentPlayer, "hyper_driver", 1);
      AddDecisionQueue("PUTPLAY", $currentPlayer, $resourcesPaid, 1);
      if ($resourcesPaid >= 3 && SearchBanishForCardName($currentPlayer, "construct_nitro_mechanoid_yellow") != -1) {
        MZMoveCard($currentPlayer, "MYBANISH:isSameName=construct_nitro_mechanoid_yellow", "MYTOPDECK", true, true, DQContext: "Choose a card to shuffle in your deck, or pass");
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-", 1);
      }
      return "";
    case "murky_water_red":
      if (HasAimCounter()) {
        AddCurrentTurnEffect($cardID, $currentPlayer);
      }
      return "";
    case "kindle_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      if (count($hand) == 0) Draw($currentPlayer);
      return "";
    case "dust_from_stillwater_shrine_red":
      PutPermanentIntoPlay($currentPlayer, $cardID);
      return "";
    case "enigma_new_moon":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:type=E;faceDown=true");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("ENIGMAMOON", $currentPlayer, "-", 1);
      return "";
    default:
      return "";
  }
}

function MSTHitEffect($cardID, $from): void
{
  global $mainPlayer, $defPlayer, $combatChainState, $CCS_DamageDealt;
  $deck = new Deck($defPlayer);
  $discard = new Discard($defPlayer);
  switch ($cardID) {
    case "beckoning_mistblade":
      AddCurrentTurnEffectNextAttack($cardID, $mainPlayer);
      break;
    case "bonds_of_agony_blue":
      $count = count(GetDeck($defPlayer));
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRHAND");
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a card from your opponent hand", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZSETDQVAR", $mainPlayer, "0", 1);
      for ($i = 0; $i < 3; $i++) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRHAND:isSameName={0}&THEIRDECK:isSameName={0}&THEIRDISCARD:isSameName={0}", 1);
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which cards you want your opponent to banish", 1);
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZBANISH", $mainPlayer, "-,Source-" . $cardID . "," . $cardID, 1);
        AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      }
      AddDecisionQueue("FINDINDICES", $defPlayer, "DECKTOPXINDICES," . $count);
      AddDecisionQueue("DECKCARDS", $defPlayer, "<-", 1);
      AddDecisionQueue("LOOKTOPDECK", $defPlayer, "-", 1);
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, CardLink($cardID, $cardID) . " shows the your opponents deck are", 1);
      AddDecisionQueue("MULTISHOWCARDSTHEIRDECK", $mainPlayer, "<-", 1);
      AddDecisionQueue("SHUFFLEDECK", $defPlayer, "-");
      break;
    case "persuasive_prognosis_blue":
      if (IsHeroAttackTarget()) {
        LookAtHand($defPlayer);
        $pitchValue = PitchValue($deck->Top());
        $deck->BanishTop("Source-" . $cardID, banishedBy: $cardID);
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRHAND:pitch=" . $pitchValue);
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want your opponent to banish", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZBANISH", $mainPlayer, "HAND,Source-" . $cardID . "," . $cardID, 1);
        AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      }
      break;
    case "art_of_desire_body_red":
    case "art_of_desire_soul_yellow":
    case "art_of_desire_mind_blue":
      if (IsHeroAttackTarget()) {
        $deck->BanishTop("Source-" . $cardID, banishedBy: $cardID);
      }
      break;
    case "bonds_of_attraction_red":
    case "bonds_of_attraction_yellow":
    case "bonds_of_attraction_blue":
      if (IsHeroAttackTarget()) {
        $deck->BanishTop("Source-" . $cardID, banishedBy: $cardID);
        if ($discard->NumCards() > 0) MZMoveCard($mainPlayer, "THEIRDISCARD", "THEIRBANISH,GY,Source-" . $cardID . "," . $cardID, silent: true);
      }
      break;
    case "double_trouble_red":
    case "double_trouble_yellow":
    case "double_trouble_blue":
      if (IsHeroAttackTarget() && NumAttackReactionsPlayed() > 1) {
        $deck->BanishTop("Source-" . $cardID, banishedBy: $cardID);
        $deck->BanishTop("Source-" . $cardID, banishedBy: $cardID);
      }
      break;
    case "bonds_of_memory_red":
    case "bonds_of_memory_yellow":
    case "bonds_of_memory_blue":
      if (IsHeroAttackTarget()) {
        $deck = new Deck($defPlayer);
        $deck->BanishTop("Source-" . $cardID, banishedBy: $cardID);
        if ($discard->NumCards() > 0) MZMoveCard($mainPlayer, "THEIRDISCARD", "THEIRBANISH,GY,Source-" . $cardID . "," . $cardID, silent: true);
      }
      break;
    case "desires_of_flesh_red":
    case "desires_of_flesh_yellow":
    case "desires_of_flesh_blue":
    case "impulsive_desire_red":
    case "impulsive_desire_yellow":
    case "impulsive_desire_blue":
    case "minds_desire_red":
    case "minds_desire_yellow":
    case "minds_desire_blue":
      if (IsHeroAttackTarget()) {
        $deck->BanishTop("Source-" . $cardID, banishedBy: $cardID);
      }
      break;
    case "biting_breeze_red":
    case "biting_breeze_yellow":
    case "biting_breeze_blue":
      BanishCardForPlayer("crouching_tiger", $mainPlayer, "-", "TT", $cardID);
      break;
    case "rowdy_locals_blue":
      $hand = GetHand($mainPlayer);
      if (count($hand) > 0) {
        AddDecisionQueue("FINDINDICES", $mainPlayer, "HAND");
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a card from your hand to discard.");
        AddDecisionQueue("CHOOSEHAND", $mainPlayer, "<-", 1);
        AddDecisionQueue("REMOVEMYHAND", $mainPlayer, "-", 1);
        AddDecisionQueue("DISCARDCARD", $mainPlayer, "HAND-" . $mainPlayer, 1);
        AddDecisionQueue("FINDINDICES", $defPlayer, "HAND", 1);
        AddDecisionQueue("SETDQCONTEXT", $defPlayer, "Choose a card from your hand to discard.", 1);
        AddDecisionQueue("CHOOSEHAND", $defPlayer, "<-", 1);
        AddDecisionQueue("REMOVEMYHAND", $defPlayer, "-", 1);
        AddDecisionQueue("DISCARDCARD", $defPlayer, "HAND-" . $mainPlayer, 1);
      }
      break;
    case "the_weakest_link_red":
      LookAtHand($defPlayer);
      AddDecisionQueue("BLOCKLESS0HAND", $defPlayer, "THEIRHAND:maxDef=-1");
      AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose which card you want your opponent to discard", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZDISCARD", $mainPlayer, "HAND," . $mainPlayer, 1);
      AddDecisionQueue("MZREMOVE", $mainPlayer, "-", 1);
      AddDecisionQueue("DRAW", $mainPlayer, "-", 1);
      break;
    case "blanch_red":
    case "blanch_yellow":
    case "blanch_blue":
      AddCurrentTurnEffect($cardID, $defPlayer);
      AddNextTurnEffect($cardID, $defPlayer);
      break;
    case "factfinding_mission_red":
    case "factfinding_mission_yellow":
    case "factfinding_mission_blue":
      AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRCHAR:type=E;faceDown=true&THEIRARS:faceDown=true");
      AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
      AddDecisionQueue("MZREVEAL", $mainPlayer, "-", 1);
      break;
    case "murky_water_red":
      $trapsArr = explode(",", SearchDiscard($mainPlayer, subtype: "Trap"));
      if (count($trapsArr) >= 3) {
        AddDecisionQueue("YESNO", $mainPlayer, "if you want to banish traps");
        AddDecisionQueue("NOPASS", $mainPlayer, "-");
        AddDecisionQueue("FINDINDICES", $mainPlayer, "MULTITRAPSBANISH", 1);
        AddDecisionQueue("PREPENDLASTRESULT", $mainPlayer, "3-", 1);
        AddDecisionQueue("APPENDLASTRESULT", $mainPlayer, "-3", 1);
        AddDecisionQueue("MULTICHOOSEDISCARD", $mainPlayer, "<-", 1);
        AddDecisionQueue("SPECIFICCARD", $mainPlayer, "MURKYWATER", 1);
      }
      break;
    default:
      break;
  }
}
