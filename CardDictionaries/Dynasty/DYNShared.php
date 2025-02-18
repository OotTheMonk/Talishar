<?php

function DYNAbilityCost($cardID)
{
  switch($cardID) {
    case "emperor_dracai_of_aesir": return 3;
    case "rok": return 3;
    case "yoji_royal_protector": return 3;
    case "jubeel_spellbane": return 1;
    case "merciless_battleaxe": return 3;
    case "quicksilver_dagger": case "quicksilver_dagger": return 1;
    case "spiders_bite": case "spiders_bite": return 2;
    case "sandscour_greatbow": return 1;
    case "annals_of_sutcliffe": return 3;
    case "surgent_aethertide": return 2;
    case "seerstone": return 3;
    case "ornate_tessen": return 1;
    case "imperial_warhorn_red": return 1;
    case "gold": return 2;
    case "suraya_archangel_of_knowledge": return 2;
    default: return 0;
  }
}

function DYNAbilityType($cardID, $index = -1)
{
  switch($cardID) {
    case "emperor_dracai_of_aesir": return "A";
    case "rok": return "AA";
    case "tearing_shuko": return "I";
    case "jubeel_spellbane": return "AA";
    case "merciless_battleaxe": return "AA";
    case "quicksilver_dagger": case "quicksilver_dagger": return "AA";
    case "hanabi_blaster": return "AA";
    case "yoji_royal_protector": return "I";
    case "spiders_bite": case "spiders_bite": return "AA";
    case "blacktek_whisperers": return "AR";
    case "mask_of_perdition": return "AR";
    case "sandscour_greatbow": return "A";
    case "amethyst_tiara": return "I";
    case "annals_of_sutcliffe": return "A";
    case "surgent_aethertide": return "A";
    case "seerstone": return "A";
    case "ornate_tessen": return "I";
    case "imperial_edict_red": return "A";
    case "imperial_ledger_red": return "A";
    case "imperial_warhorn_red": case "gold": return "A";
    case "nitro_mechanoida": return "AA";
    case "suraya_archangel_of_knowledge": return "AA";
    default: return "";
  }
}

function DYNAbilityHasGoAgain($cardID)
{
  switch($cardID) {
    case "sandscour_greatbow": case "surgent_aethertide": case "imperial_edict_red": case "gold": return true;
    default: return false;
  }
}

function DYNEffectAttackModifier($cardID)
{
  global $mainPlayer;
  $params = explode(",", $cardID);
  $cardID = $params[0];
  if(count($params) > 1) $parameter = $params[1];
  switch($cardID) {
    case "savage_beatdown_red": return 6;
    case "blessing_of_savagery_red": return 3;
    case "blessing_of_savagery_yellow": return 2;
    case "blessing_of_savagery_blue": return 1;
    case "madcap_muscle_red": case "madcap_muscle_yellow": case "madcap_muscle_blue": return 3;
    case "rumble_grunting_red": return 4;
    case "rumble_grunting_yellow": return 3;
    case "rumble_grunting_blue": return 2;
    case "buckle_blue": return 1;
    case "tearing_shuko": return 2;
    case "roar_of_the_tiger_yellow": return 1;
    case "blessing_of_qi_red": return 3;
    case "blessing_of_qi_yellow": return 2;
    case "blessing_of_qi_blue": return 1;
    case "cleave_red": return 4;
		case "blessing_of_steel_red": return 3;
    case "blessing_of_steel_yellow": return 2;
    case "blessing_of_steel_blue": return 1;
    case "precision_press_red": return (NumEquipBlock() > 0 ? 3 : 0);
    case "precision_press_yellow": return (NumEquipBlock() > 0 ? 2 : 0);
    case "precision_press_blue": return (NumEquipBlock() > 0 ? 1 : 0);
    case "puncture_red": return 3;
    case "puncture_yellow": return 2;
    case "puncture_blue": return 1;
    case "felling_swing_red": return 6;
    case "felling_swing_yellow": return 5;
    case "felling_swing_blue": return 4;
    case "visit_the_imperial_forge_red": return (NumEquipBlock() > 0 ? 3 : 0);
    case "visit_the_imperial_forge_yellow": return (NumEquipBlock() > 0 ? 2 : 0);
    case "visit_the_imperial_forge_blue": return (NumEquipBlock() > 0 ? 1 : 0);
    case "galvanic_bender-UNDER": return 1;
    case "bios_update_red-1": return 3;
    case "cut_to_the_chase_red": return 3;
    case "cut_to_the_chase_yellow": return 2;
    case "cut_to_the_chase_blue": return 1;
    case "dead_eye_yellow": return 3;
    case "long_shot_red": case "long_shot_yellow": case "long_shot_blue": return 2;
    case "point_the_tip_red": return 3;
    case "point_the_tip_yellow": return 2;
    case "point_the_tip_blue": return 1;
    case "deathly_duet_red": case "deathly_duet_yellow": case "deathly_duet_blue": return 2;
    case "runic_reaping_red-BUFF": case "runic_reaping_yellow-BUFF": case "runic_reaping_blue-BUFF": return 1;
    default:
      return 0;
  }
}

function DYNCombatEffectActive($cardID, $attackID)
{
  global $combatChainState, $CCS_IsBoosted, $mainPlayer;
  $params = explode(",", $cardID);
  $cardID = $params[0];
  switch($cardID) {
    case "savage_beatdown_red": return true;
    case "blessing_of_savagery_red": case "blessing_of_savagery_yellow": case "blessing_of_savagery_blue": return AttackValue($attackID) >= 6;//Specifies base attack
    case "madcap_muscle_red": case "madcap_muscle_yellow": case "madcap_muscle_blue": return true;
    case "rumble_grunting_red": case "rumble_grunting_yellow": case "rumble_grunting_blue": return ClassContains($attackID, "BRUTE", $mainPlayer);
    case "buckle_blue": return ClassContains($attackID, "GUARDIAN", $mainPlayer);
    case "tearing_shuko": return CardNameContains($attackID, "Crouching Tiger", $mainPlayer);
    case "roar_of_the_tiger_yellow": return CardNameContains($attackID, "Crouching Tiger", $mainPlayer);
    case "blessing_of_qi_red": case "blessing_of_qi_yellow": case "blessing_of_qi_blue": return CardNameContains($attackID, "Crouching Tiger", $mainPlayer);
    case "crouching_tiger": return true;
    case "merciless_battleaxe": return true;
    case "cleave_red": return CardSubType($attackID) == "Axe";
    case "blessing_of_steel_red": case "blessing_of_steel_yellow": case "blessing_of_steel_blue": return TypeContains($attackID, "W", $mainPlayer);
    case "precision_press_red": case "precision_press_yellow": case "precision_press_blue":
    case "puncture_red": case "puncture_yellow": case "puncture_blue":
      $subtype = CardSubType($attackID);
      return ($subtype == "Sword") || ($subtype == "Dagger");
    case "felling_swing_red": case "felling_swing_yellow": case "felling_swing_blue": return CardSubType($attackID) == "Axe";
    case "visit_the_imperial_forge_red": case "visit_the_imperial_forge_yellow": case "visit_the_imperial_forge_blue": return (CardSubType($attackID) == "Sword" || CardSubType($attackID) == "Dagger");
    case "galvanic_bender-UNDER":
      $character = &GetPlayerCharacter($mainPlayer);
      $index = FindCharacterIndex($mainPlayer, "nitro_mechanoida");
      return $attackID == "nitro_mechanoida" && $character[$index + 2] >= 1;
      case "bios_update_red-1": return $combatChainState[$CCS_IsBoosted];
    case "cut_to_the_chase_red": case "cut_to_the_chase_yellow": case "cut_to_the_chase_blue": return true;
    case "immobilizing_shot_red": return true;
    case "dead_eye_yellow": return CardSubType($attackID) == "Arrow";
    case "long_shot_red": case "long_shot_yellow": case "long_shot_blue": return true;
    case "point_the_tip_red": case "point_the_tip_yellow": case "point_the_tip_blue": return CardSubType($attackID) == "Arrow";
    case "cryptic_crossing_yellow": return true;
    case "deathly_duet_red": case "deathly_duet_yellow": case "deathly_duet_blue": return true;
    case "runic_reaping_red-BUFF": case "runic_reaping_yellow-BUFF": case "runic_reaping_blue-BUFF": return CardType($attackID) == "AA" && ClassContains($attackID, "RUNEBLADE", $mainPlayer);
    case "runic_reaping_red-HIT": case "runic_reaping_yellow-HIT": case "runic_reaping_blue-HIT": return CardType($attackID) == "AA" && ClassContains($attackID, "RUNEBLADE", $mainPlayer);
    default:
      return false;
  }
}

function DYNPlayAbility($cardID, $from, $resourcesPaid, $target, $additionalCosts)
{
  global $currentPlayer, $CS_PlayIndex, $CS_NumContractsCompleted, $combatChainState, $CCS_NumBoosted, $CS_NumCrouchingTigerPlayedThisTurn;
  $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
  $rv = "";
  switch($cardID) {
    case "emperor_dracai_of_aesir":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDECK:cardID=command_and_conquer_red");
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      AddDecisionQueue("ATTACKWITHIT", $currentPlayer, "-", 1);
      AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
      return "";
    case "savage_beatdown_red":
      if(ModifiedAttackValue($additionalCosts, $currentPlayer, "HAND", source:$cardID) >= 6) AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "berserk_yellow": AddCurrentTurnEffect($cardID, $currentPlayer); return "";
    case "dust_from_the_golden_plains_red": PutPermanentIntoPlay($currentPlayer, $cardID); return "";
    case "dust_from_the_red_desert_red": PutPermanentIntoPlay($currentPlayer, $cardID); return "";
    case "dust_from_the_shadow_crypts_red": PutPermanentIntoPlay($currentPlayer, $cardID); return "";
    case "madcap_charger_red": case "madcap_charger_yellow": case "madcap_charger_blue": if(ModifiedAttackValue($additionalCosts, $currentPlayer, "HAND", source:$cardID) >= 6) GiveAttackGoAgain(); return "";
    case "madcap_muscle_red": case "madcap_muscle_yellow": case "madcap_muscle_blue": if(ModifiedAttackValue($additionalCosts, $currentPlayer, "HAND", source:$cardID) >= 6) AddCurrentTurnEffect($cardID, $currentPlayer); return "";
    case "rumble_grunting_red": case "rumble_grunting_yellow": case "rumble_grunting_blue": AddCurrentTurnEffect($cardID, $currentPlayer); return "";
    case "buckle_blue": AddCurrentTurnEffect($cardID, $currentPlayer); return "";
    case "shield_bash_red": case "shield_bash_yellow": case "shield_bash_blue":
      if(!IsAllyAttacking()) {
        if(SearchCombatChainLink($currentPlayer, subtype:"Off-Hand", class:"GUARDIAN") != "") {
          AddDecisionQueue("MULTIZONEINDICES", $otherPlayer, "MYHAND", 1);
          AddDecisionQueue("MAYCHOOSEMULTIZONE", $otherPlayer, "<-", 1);
          AddDecisionQueue("MZDISCARD", $otherPlayer, "HAND,".$currentPlayer, 1);
          AddDecisionQueue("MZREMOVE", $otherPlayer, "-", 1);
          AddDecisionQueue("ELSE", $otherPlayer, "-");
          AddDecisionQueue("TAKEDAMAGE", $otherPlayer, "1-".$cardID, 1);
        }
      }
      return "";
    case "reinforce_steel_red": case "reinforce_steel_yellow": case "reinforce_steel_blue":
      if($cardID == "reinforce_steel_red") $maxDef = 3;
      else if($cardID == "reinforce_steel_yellow") $maxDef = 2;
      else $maxDef = 1;
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:type=E;subtype=Off-Hand;hasNegCounters=true;maxDef=" . $maxDef . ";class=GUARDIAN");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZOP", $currentPlayer, "GETCARDINDEX", 1);
      AddDecisionQueue("MODDEFCOUNTER", $currentPlayer, "1", 1);
      return "";
    case "withstand_red": case "withstand_yellow": case "withstand_blue":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:type=E;subtype=Off-Hand;class=GUARDIAN");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which Guardian Off-Hand to buff", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, $cardID, 1);
      return "";
    case "tearing_shuko":
      AddCurrentTurnEffectNextAttack($cardID, $currentPlayer);
      return "";
    case "roar_of_the_tiger_yellow":
      AddPlayerHand("crouching_tiger", $currentPlayer, "NA");
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "predatory_streak_red": case "predatory_streak_yellow": case "predatory_streak_blue":
      if($cardID == "predatory_streak_red") $amount = 3;
      else if($cardID == "predatory_streak_yellow") $amount = 2;
      else $amount = 1;
      for($i=0; $i < $amount; $i++) BanishCardForPlayer("crouching_tiger", $currentPlayer, "-", "TT", $currentPlayer);
      return "";
    case "merciless_battleaxe":
      CacheCombatResult();
      if(IsWeaponGreaterThanTwiceBasePower()) AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "cleave_red":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "ironsong_pride_red":
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYCHAR:type=W;subtype=Sword");
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a Sword to gain a +1 counter");
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("ADDATTACKCOUNTERS", $currentPlayer, "1", 1);
      return "";
    case "precision_press_red": case "precision_press_yellow": case "precision_press_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "puncture_red": case "puncture_yellow": case "puncture_blue":
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
		case "felling_swing_red": case "felling_swing_yellow": case "felling_swing_blue":
      if($cardID == "felling_swing_red") $amount = 3;
      else if($cardID == "felling_swing_yellow") $amount = 2;
      else $amount = 1;
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "visit_the_imperial_forge_red": case "visit_the_imperial_forge_yellow": case "visit_the_imperial_forge_blue":
      if($cardID == "visit_the_imperial_forge_red") $amount = 3;
      else if($cardID == "visit_the_imperial_forge_yellow") $amount = 2;
      else $amount = 1;
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "pulsewave_harpoon_red":
      $numBoosted = $combatChainState[$CCS_NumBoosted];
      $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
      $otherPlayerHand = GetHand($otherPlayer);
      if(IsHeroAttackTarget() && $numBoosted > 0 && count($otherPlayerHand) > 0)
      {
        AddDecisionQueue("PASSPARAMETER", $otherPlayer, $numBoosted, 1);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
        AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
        AddDecisionQueue("APPENDLASTRESULT", $otherPlayer, "-{0}", 1);
        AddDecisionQueue("PREPENDLASTRESULT", $otherPlayer, "{0}-", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose $numBoosted card(s)", 1);
        AddDecisionQueue("MULTICHOOSEHAND", $otherPlayer, "<-", 1);
        AddDecisionQueue("IMPLODELASTRESULT", $otherPlayer, ",", 1);
        AddDecisionQueue("SETDQVAR", $currentPlayer, "1");
        AddDecisionQueue("REVEALHANDCARDS", $otherPlayer, "<-", 1);
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "{1}", 1);
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card", 1);
        AddDecisionQueue("SPECIFICCARD", $otherPlayer, "PULSEWAVEHARPOONFILTER", 1);
        AddDecisionQueue("CHOOSETHEIRHAND", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIREMOVEHAND", $otherPlayer, "-", 1);
        AddDecisionQueue("ADDCARDTOCHAINASDEFENDINGCARD", $otherPlayer, "HAND", 1);
      }
      return "";
    case "bios_update_red":
      AddCurrentTurnEffect($cardID . "-1", $currentPlayer);
      AddCurrentTurnEffect($cardID . "-2", $currentPlayer);
      return "";
    case "construct_nitro_mechanoid_yellow":
      $conditionsMet = CheckIfConstructNitroMechanoidConditionsAreMet($currentPlayer);
      if ($conditionsMet != "") return $conditionsMet;
      $char = &GetPlayerCharacter($currentPlayer);
      // Add the new weapon stuff so we can put cards under it
      PutCharacterIntoPlayForPlayer("nitro_mechanoida", $currentPlayer);
      // We don't want function calls in every iteration check
      $charCount = count($char);
      $charPieces = CharacterPieces();
      $mechanoidIndex = $charCount - $charPieces; // we pushed it, so should be the last element
      //Congrats, you have met the requirement to summon the mech! Let's remove the old stuff
      for ($i = $charCount - $charPieces; $i >= 0; $i -= $charPieces) {
        if(CardType($char[$i]) != "C" && $char[$i] != "nitro_mechanoida") {
          if($char[$i] == "galvanic_bender") AddCurrentTurnEffect($char[$i] . "-UNDER", $currentPlayer);
          RemoveCharacterAndAddAsSubcardToCharacter($currentPlayer, $i, $mechanoidIndex);
        }
      }

      $hyperDrivers = SearchMultizone($currentPlayer, "MYITEMS:isSameName=hyper_driver_red");
      $hyperDrivers = str_replace("MYITEMS-", "", $hyperDrivers); // MULTICHOOSEITEMS expects indexes only but SearchItems does not have a sameName parameter
      AddDecisionQueue("MULTICHOOSEITEMS", $currentPlayer, "3-" . $hyperDrivers. "-3");
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "CONSTRUCTNITROMECHANOID," . $mechanoidIndex, 1);
      //Now add the remaining new stuff
      PutCharacterIntoPlayForPlayer("nitro_mechanoidb", $currentPlayer);//Armor
      PutItemIntoPlayForPlayer("nitro_mechanoidc", $currentPlayer);//Item
      return "";
    case "scramble_pulse_red": case "scramble_pulse_yellow": case "scramble_pulse_blue": AddCurrentTurnEffect($cardID, $currentPlayer); return "";
    case "pay_day_blue":
      if(GetClassState($currentPlayer, $CS_NumContractsCompleted) > 0) {
        PutItemIntoPlayForPlayer("silver", $currentPlayer, 0, 4);
      }
      return "";
    case "shred_red": case "shred_yellow": case "shred_blue":
      if($cardID == "shred_red") $amount = -4;
      else if($cardID == "shred_yellow") $amount = -3;
      else $amount = -2;
      Shred($currentPlayer, $amount);
      return "";
    case "cut_to_the_chase_red": case "cut_to_the_chase_yellow": case "cut_to_the_chase_blue":
      $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
      AddDecisionQueue("DECKCARDS", $otherPlayer, "0", 1);
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose if you want sink <0>", 1);
      AddDecisionQueue("YESNO", $currentPlayer, "if_you_want_to_sink_the_opponent's_card", 1);
      AddDecisionQueue("NOPASS", $currentPlayer, "-", 1);
      AddDecisionQueue("FINDINDICES", $otherPlayer, "TOPDECK", 1);
      AddDecisionQueue("MULTIREMOVEDECK", $otherPlayer, "<-", 1);
      AddDecisionQueue("ADDBOTDECK", $otherPlayer, "-", 1);
      AddDecisionQueue("ELSE", $currentPlayer, "-");
      AddDecisionQueue("WRITELOG", $currentPlayer, "Left the card on top", 1);
      AddCurrentTurnEffect($cardID, $currentPlayer);
      return "";
    case "sandscour_greatbow":
      $deck = new Deck($currentPlayer);
      AddDecisionQueue("DECKCARDS", $currentPlayer, "0", 1);
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0", 1);
      if(ArsenalFull($currentPlayer)) {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Top of your deck: <0>");
        AddDecisionQueue("OK", $currentPlayer, "", 1);
        return "Your arsenal is full";
      }
      if(CardSubType($deck->Top()) != "Arrow") {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Top of your deck: <0>");
        AddDecisionQueue("OK", $currentPlayer, "", 1);
        AddDecisionQueue("PASSPARAMETER", $currentPlayer, "NO");
      } else {
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose if you want to put <0> in your arsenal", 1);
        AddDecisionQueue("YESNO", $currentPlayer, "", 1);
      }
      AddDecisionQueue("SPECIFICCARD", $currentPlayer, "SANDSCOURGREATBOW");
      return "";
    case "dead_eye_yellow": AddCurrentTurnEffect($cardID, $currentPlayer); return "";
    case "long_shot_red": case "long_shot_yellow": case "long_shot_blue": if(HasAimCounter()) AddCurrentTurnEffect($cardID, $currentPlayer); return "";
    case "point_the_tip_red": case "point_the_tip_yellow": case "point_the_tip_blue":
      $arsenal = &GetArsenal($currentPlayer);
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYARS:faceUp=true", 1);
      AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose which card you want to buff and a aim counter on", 1);
      AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZADDCOUNTERANDEFFECT", $currentPlayer, $cardID, 1);
      return "";
    case "amethyst_tiara": AddCurrentTurnEffect($cardID, $currentPlayer); return "";
    case "annals_of_sutcliffe":
      Draw($currentPlayer);
      if(SearchCardList($additionalCosts, $currentPlayer, "A") != "" && SearchCardList($additionalCosts, $currentPlayer, "AA") != "") PlayAura("runechant", $currentPlayer);
      return $rv;
    case "cryptic_crossing_yellow":
      if(SearchCardList($additionalCosts, $currentPlayer, "A") != "" && SearchCardList($additionalCosts, $currentPlayer, "AA") != "" && IsHeroAttackTarget()) AddCurrentTurnEffect($cardID, $currentPlayer, $from);
      return "";
    case "diabolic_ultimatum_red":
      if(SearchCardList($additionalCosts, $currentPlayer, "AA") != "") {
        MZChooseAndDestroy($otherPlayer, "MYALLY");
        MZChooseAndDestroy($currentPlayer, "MYALLY");
      }
      if(SearchCardList($additionalCosts, $currentPlayer, "A") != "") {
        MZChooseAndDestroy($otherPlayer, "MYAURAS");
        MZChooseAndDestroy($currentPlayer, "MYAURAS");
      }
      return "";
    case "looming_doom_blue":
      $numRunechants = DestroyAllThisAura($currentPlayer, "runechant");
      $auras = &GetAuras($currentPlayer);
      $index = count($auras) - AuraPieces();
      $auras[$index+2] = $numRunechants;
      return "";
    case "deathly_duet_red": case "deathly_duet_yellow": case "deathly_duet_blue":
      if(SearchCardList($additionalCosts, $currentPlayer, "AA") != "") AddCurrentTurnEffect($cardID, $currentPlayer);
      if(SearchCardList($additionalCosts, $currentPlayer, "A") != "") PlayAura("runechant", $currentPlayer, 2, true);
      return "";
    case "aether_slash_red": case "aether_slash_yellow": case "aether_slash_blue":
      if(SearchCardList($additionalCosts, $currentPlayer, "A") != "") DealArcane(1, 2, "PLAYCARD", $cardID);
      return "";
    case "runic_reaping_red": case "runic_reaping_yellow": case "runic_reaping_blue":
      if($cardID == "runic_reaping_red") $amount = 3;
      else if($cardID == "runic_reaping_yellow") $amount = 2;
      else $amount = 1;
      AddCurrentTurnEffect($cardID . "-HIT", $currentPlayer);
      if(SearchCardList($additionalCosts, $currentPlayer, "AA") != "") AddCurrentTurnEffect($cardID . "-BUFF", $currentPlayer);
      return "";
    case "sky_fire_lanterns_red": case "sky_fire_lanterns_yellow": case "sky_fire_lanterns_blue":
      $deck = new Deck($currentPlayer);
      if($deck->Reveal(1)) if(ColorContains($deck->Top(), PitchValue($cardID), $currentPlayer)) PlayAura("runechant", $currentPlayer, 1, true);
      return "";
    case "surgent_aethertide":
      DealArcane(1, 1, "ABILITY", $cardID, resolvedTarget: $target);
      AddDecisionQueue("GREATERTHAN0ORPASS", $currentPlayer, "");
      AddDecisionQueue("PREPENDLASTRESULT", $currentPlayer, "surgent_aethertide,", 1);
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "<-", 1);
      return "";
    case "seerstone":
      PlayerOpt($currentPlayer, 1, false);
      PlayAura("ponder", $currentPlayer);
      return "";
  	case "mind_warp_yellow": case "swell_tidings_red": case "aether_quickening_red": case "aether_quickening_yellow": case "aether_quickening_blue": case "prognosticate_red": case "prognosticate_yellow": case "prognosticate_blue":
    case "sap_red": case "sap_yellow": case "sap_blue": DealArcane(ArcaneDamage($cardID), 0, "PLAYCARD", $cardID, resolvedTarget: $target); return "";
    case "brainstorm_blue": AddCurrentTurnEffect($cardID, $currentPlayer); return "";
    case "tempest_aurora_red": case "tempest_aurora_yellow": case "tempest_aurora_blue": AddCurrentTurnEffect($cardID, $currentPlayer); return "";
    case "invoke_suraya_yellow": Transform($currentPlayer, "spectral_shield", "suraya_archangel_of_knowledge"); return "";
    case "phantasmal_symbiosis_yellow":
      AddDecisionQueue("INPUTCARDNAME", $currentPlayer, "-");
      AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
      AddDecisionQueue("WRITELOG", $currentPlayer, "📣<b>{0}</b> was chosen");
      AddDecisionQueue("ADDCURRENTEFFECT", $currentPlayer, "phantasmal_symbiosis_yellow-{0}");
      AddDecisionQueue("ADDCURRENTEFFECT", $otherPlayer, "phantasmal_symbiosis_yellow-{0}");
      return "";
    case "tranquil_passing_red": case "tranquil_passing_yellow": case "tranquil_passing_blue":
      if($from == "PLAY") return "";
      $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
      $auras = &GetAuras($currentPlayer);
      $uniqueID = $auras[count($auras) - AuraPieces() + 6];
      if($cardID == "tranquil_passing_red") $maxCost = 3;
      else if($cardID == "tranquil_passing_yellow") $maxCost = 2;
      else $maxCost = 1;
      AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "THEIRAURAS:maxCost=" . $maxCost);
      AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
      AddDecisionQueue("MZBANISH", $currentPlayer, "AURAS," . $cardID . "-" . $uniqueID, 1);
      AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
      return "";
    case "spectral_prowler_red": case "spectral_prowler_yellow": case "spectral_prowler_blue":
      if(SearchAuras("spectral_shield", $currentPlayer)) GiveAttackGoAgain();
      return "";
    case "spectral_rider_red": case "spectral_rider_yellow": case "spectral_rider_blue":
      if(SearchAuras("spectral_shield", $currentPlayer)) AddCurrentTurnEffect("spectral_rider_red", $currentPlayer);
      return "";
    case "water_glow_lanterns_red": case "water_glow_lanterns_yellow": case "water_glow_lanterns_blue":
      $deck = new Deck($currentPlayer);
      if($deck->Reveal(1) && ColorContains($deck->Top(), PitchValue($cardID), $currentPlayer)) PlayAura("spectral_shield", $currentPlayer, 1, true);
      return "";
    case "ornate_tessen":
      BottomDeck($currentPlayer, false, shouldDraw:true);
      return "";
    case "imperial_edict_red":
      $rv = "";
      if($from == "PLAY") {
        DestroyItemForPlayer($currentPlayer, GetClassState($currentPlayer, $CS_PlayIndex));
        if(IsRoyal($currentPlayer))
        {
          $rv .= CardLink($cardID, $cardID) . " revealed the opponent's hand";
          $otherPlayer = ($currentPlayer == 1 ? 2 : 1);
          AddDecisionQueue("FINDINDICES", $otherPlayer, "HAND");
          AddDecisionQueue("REVEALHANDCARDS", $otherPlayer, "-", 1);
        }
        AddDecisionQueue("INPUTCARDNAME", $currentPlayer, "-");
        AddDecisionQueue("SETDQVAR", $currentPlayer, "0");
        AddDecisionQueue("WRITELOG", $currentPlayer, "📣<b>{0}</b> was chosen");
        AddDecisionQueue("ADDCURRENTANDNEXTTURNEFFECT", $otherPlayer, "imperial_edict_red-{0}");
      }
      return $rv;
    case "imperial_ledger_red":
      if($from == "PLAY") {
        DestroyItemForPlayer($currentPlayer, GetClassState($currentPlayer, $CS_PlayIndex), true);
        PutItemIntoPlayForPlayer((IsRoyal($currentPlayer) ? "gold": "copper"), $currentPlayer);
        $deck = new Deck($currentPlayer);
        $deck->AddBottom("imperial_ledger_red", "PLAY");
        AddDecisionQueue("SHUFFLEDECK", $currentPlayer, "-");
      }
      return "";
    case "imperial_warhorn_red":
      if($from == "PLAY") {
        DestroyItemForPlayer($currentPlayer, GetClassState($currentPlayer, $CS_PlayIndex));
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose any number of heroes");
        AddDecisionQueue("BUTTONINPUT", $currentPlayer, "Target_Opponent,Target_Both_Heroes,Target_Yourself,Target_No_Heroes");
        AddDecisionQueue("PLAYERTARGETEDABILITY", $currentPlayer, "IMPERIALWARHORN", 1);
      }
      return "";
    case "gold":
      $rv = "";
      if($from == "PLAY") {
        DestroyItemForPlayer($currentPlayer, GetClassState($currentPlayer, $CS_PlayIndex));
        Draw($currentPlayer);
      }
      return $rv;
    case "suraya_archangel_of_knowledge":
      $soul = &GetSoul($currentPlayer);
      if(count($soul) > 0){
        AddDecisionQueue("FINDINDICES", $currentPlayer, "SOUL");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose a card to banish");
        AddDecisionQueue("MAYCHOOSEMYSOUL", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIBANISHSOUL", $currentPlayer, "-", 1);
        AddDecisionQueue("THREATENARCANE", $currentPlayer, $cardID, 1);
      }
      return "";
    default: return "";
  }
}

function DYNHitEffect($cardID, $from, $attackID)
{
  global $mainPlayer, $defPlayer, $combatChainState, $CCS_DamageDealt, $CCS_NumBoosted, $combatChain;
  switch($cardID) {
    case "tiger_swipe_red":
      if(ComboActive()) {
        $numLinks = NumChainLinksWithName("Crouching Tiger");
        for($i=0; $i < $numLinks; $i++) BanishCardForPlayer("crouching_tiger", $mainPlayer, "-", "TT", $mainPlayer);
      }
      break;
    case "flex_claws_red": case "flex_claws_yellow": case "flex_claws_blue": BanishCardForPlayer("crouching_tiger", $mainPlayer, "-", "TT", $mainPlayer); break;
    case "jubeel_spellbane": if(IsHeroAttackTarget() && !SearchAuras("spellbane_aegis", $mainPlayer)) PlayAura("spellbane_aegis", $mainPlayer); break;
    case "urgent_delivery_red": case "urgent_delivery_yellow": case "urgent_delivery_blue":
      MZMoveCard($mainPlayer, "MYHAND:subtype=Item;class=MECHANOLOGIST;maxCost=" . $combatChainState[$CCS_NumBoosted], "MYITEMS", may:true);
      break;
    case "spiders_bite": case "spiders_bite": if(IsHeroAttackTarget()) AddCurrentTurnEffect($cardID, $defPlayer); break;
    case "blacktek_whisperers": GiveAttackGoAgain(); break;
    case "mask_of_perdition":
      $deck = new Deck($defPlayer);
      if($deck->Empty()) { WriteLog("The opponent deck is already... depleted."); break; }
      $deck->BanishTop("Source-" . $attackID, banishedBy:$attackID);
      break;
    case "eradicate_yellow":
      if(IsHeroAttackTarget()) {
        $deck = new Deck($defPlayer);
        if($deck->Empty()) { WriteLog("The opponent deck is already... depleted."); break; }
        if($deck->RemainingCards() < $combatChainState[$CCS_DamageDealt]) $deck->BanishTop(banishedBy:$cardID, amount:$deck->RemainingCards());
        else $deck->BanishTop(banishedBy:$cardID, amount:$combatChainState[$CCS_DamageDealt]);
      }
      break;
    case "leave_no_witnesses_red":
      if(IsHeroAttackTarget()) {
        $deck = new Deck($defPlayer);
        if($deck->Empty()) { WriteLog("The opponent deck is already... depleted."); }
        else $deck->BanishTop(banishedBy:$cardID);
        MZMoveCard($mainPlayer, "THEIRARS", "THEIRBANISH,ARS,-," . $mainPlayer, true);
      }
      break;
    case "regicide_blue": if(IsHeroAttackTarget() && IsRoyal($defPlayer))
      {
        PlayerLoseHealth($defPlayer, GetHealth($defPlayer));
      }
      break;
    case "surgical_extraction_blue":
      if(IsHeroAttackTarget()) {
        $deck = new Deck($defPlayer);
        if($deck->Empty()) { WriteLog("The opponent deck is already... depleted."); }
        else $deck->BanishTop(banishedBy:$cardID);
        MZMoveCard($mainPlayer, "THEIRHAND", "THEIRBANISH,HAND,-," . $mainPlayer);
      }
      break;
    case "plunder_the_poor_red": case "plunder_the_poor_yellow": case "plunder_the_poor_blue": case "rob_the_rich_red": case "rob_the_rich_yellow": case "rob_the_rich_blue":
    case "annihilate_the_armed_red": case "annihilate_the_armed_yellow": case "annihilate_the_armed_blue": case "fleece_the_frail_red": case "fleece_the_frail_yellow": case "fleece_the_frail_blue":
    case "nix_the_nimble_red": case "nix_the_nimble_yellow": case "nix_the_nimble_blue": case "sack_the_shifty_red": case "sack_the_shifty_yellow": case "sack_the_shifty_blue":
    case "slay_the_scholars_red": case "slay_the_scholars_yellow": case "slay_the_scholars_blue":
      if(IsHeroAttackTarget()) {
        $deck = new Deck($defPlayer);
        if($deck->Empty()) { WriteLog("The opponent deck is already... depleted."); break; }
        $deck->BanishTop(banishedBy:$cardID);
      }
      break;
    case "heat_seeker_red": AddCurrentTurnEffectFromCombat($cardID, $mainPlayer); break;
    case "immobilizing_shot_red": if(HasAimCounter() && IsHeroAttackTarget()) AddNextTurnEffect($cardID, $defPlayer); break;
    case "drill_shot_red": case "drill_shot_yellow": case "drill_shot_blue":
      if(IsHeroAttackTarget()){
        AddDecisionQueue("FINDINDICES", $defPlayer, "EQUIP");
        AddDecisionQueue("CHOOSETHEIRCHARACTER", $mainPlayer, "<-", 1);
        AddDecisionQueue("MODDEFCOUNTER", $defPlayer, "-1", 1);
      }
      break;
    case "hemorrhage_bore_red": case "hemorrhage_bore_yellow": case "hemorrhage_bore_blue":
      if(HasAimCounter() && IsHeroAttackTarget()) {
        AddDecisionQueue("MULTIZONEINDICES", $mainPlayer, "THEIRARS", 1);
        AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose a card you want to destroy from their arsenal", 1);
        AddDecisionQueue("CHOOSEMULTIZONE", $mainPlayer, "<-", 1);
        AddDecisionQueue("MZDESTROY", $mainPlayer, false, 1);
      }
      break;  
    default: break;
  }
}

function IsRoyal($player)
{
  $mainCharacter = &GetPlayerCharacter($player);
  if(SearchCharacterForCard($player, "crown_of_dominion")) return true;//crown
  if (SearchCurrentTurnEffects("erase_face_red", $player)) return false;//erase face
  switch($mainCharacter[0]) {
    case "emperor_dracai_of_aesir": return true;//emperor
    case "cindra_dracai_of_retribution":
    case "cindra"://cindra
      return true;
    case "fang_dracai_of_blades":
    case "fang"://fang
      return true;
    default: break;
  }
  return false;
}

function HasSurge($cardID)
{
  switch($cardID) {
    case "mind_warp_yellow": case "swell_tidings_red": case "aether_quickening_red": case "aether_quickening_yellow": case "aether_quickening_blue":
    case "prognosticate_red": case "prognosticate_yellow": case "prognosticate_blue": case "sap_red": case "sap_yellow": case "sap_blue":
    case "destructive_aethertide_blue"://destructive aethertide
    case "eternal_inferno_red"://eternal inferno
    case "glyph_overlay_red": case "glyph_overlay_yellow": case "glyph_overlay_blue":
    case "pop_the_bubble_red": case "pop_the_bubble_yellow": case "pop_the_bubble_blue":
    case "etchings_of_arcana_red": case "etchings_of_arcana_yellow": case "etchings_of_arcana_blue":
    case "open_the_flood_gates_red": case "open_the_flood_gates_yellow": case "open_the_flood_gates_blue"://open the floodgates
    case "overflow_the_aetherwell_red": case "overflow_the_aetherwell_yellow": case "overflow_the_aetherwell_blue":
    case "perennial_aetherbloom_red": case "perennial_aetherbloom_yellow": case "perennial_aetherbloom_blue":
    case "trailblazing_aether_red": case "trailblazing_aether_yellow": case "trailblazing_aether_blue":
      return true;
    default: return false;
  }
}

function ContractType($cardID)
{
  switch($cardID)
  {
    case "eradicate_yellow": return "YELLOWPITCH";
    case "leave_no_witnesses_red": return "REDPITCH";
    case "surgical_extraction_blue": return "BLUEPITCH";
    case "plunder_the_poor_red": case "plunder_the_poor_yellow": case "plunder_the_poor_blue": return "COST1ORLESS";
    case "rob_the_rich_red": case "rob_the_rich_yellow": case "rob_the_rich_blue": return "COST2ORMORE";
    case "annihilate_the_armed_red": case "annihilate_the_armed_yellow": case "annihilate_the_armed_blue": return "AA";
    case "fleece_the_frail_red": case "fleece_the_frail_yellow": case "fleece_the_frail_blue": return "BLOCK2ORLESS";
    case "nix_the_nimble_red": case "nix_the_nimble_yellow": case "nix_the_nimble_blue": return "REACTIONS";
    case "sack_the_shifty_red": case "sack_the_shifty_yellow": case "sack_the_shifty_blue": return "GOAGAIN";
    case "slay_the_scholars_red": case "slay_the_scholars_yellow": case "slay_the_scholars_blue": return "NAA";
    case "already_dead_red": return "NONACTION";
    case "defang_the_dragon_red": return "HITMARKEDFANG";
    case "extinguish_the_flames_red": return "HITMARKEDCINDRA";
    default: return "";
  }
}

function ContractCompleted($player, $cardID)
{
  global $CS_NumContractsCompleted, $EffectContext;
  WriteLog("Player " . $player . " completed the contract for " . CardLink($cardID, $cardID));
  IncrementClassState($player, $CS_NumContractsCompleted);
  if($EffectContext == "coercive_tendency_blue") AddCurrentTurnEffect("coercive_tendency_blue", $player);
  switch($cardID)
  {
    case "eradicate_yellow": case "leave_no_witnesses_red": case "surgical_extraction_blue":
    case "plunder_the_poor_red": case "plunder_the_poor_yellow": case "plunder_the_poor_blue":
    case "rob_the_rich_red": case "rob_the_rich_yellow": case "rob_the_rich_blue":
    case "annihilate_the_armed_red": case "annihilate_the_armed_yellow": case "annihilate_the_armed_blue":
    case "fleece_the_frail_red": case "fleece_the_frail_yellow": case "fleece_the_frail_blue":
    case "nix_the_nimble_red": case "nix_the_nimble_yellow": case "nix_the_nimble_blue":
    case "sack_the_shifty_red": case "sack_the_shifty_yellow": case "sack_the_shifty_blue":
    case "slay_the_scholars_red": case "slay_the_scholars_yellow": case "slay_the_scholars_blue":
    case "already_dead_red":
      PutItemIntoPlayForPlayer("silver", $player);
      break;
    case "defang_the_dragon_red": case "extinguish_the_flames_red":
      Draw($player);
      break;
    default: break;
  }
}

function CheckHitContracts($mainPlayer, $otherPlayer)
{
  global $CombatChain, $chainLinks;
  for($i = 0; $i < $CombatChain->NumCardsActiveLink(); ++$i) {
    $chainCard = $CombatChain->Card($i, cardNumber:true);
    $contractType = ContractType($chainCard->ID());
    if($contractType != "" && CheckHitContract($contractType, $otherPlayer)) ContractCompleted($mainPlayer, $chainCard->ID());
  }
  for($i = 0; $i < count($chainLinks); ++$i) {
    for($j = 0; $j < count($chainLinks[$i]); $j += ChainLinksPieces()) {
      if($chainLinks[$i][$j+2] == 0) continue;
      $contractType = ContractType($chainLinks[$i][$j]);
      if($contractType != "" && CheckHitContract($contractType, $otherPlayer)) ContractCompleted($mainPlayer, $chainLinks[$i][$j]);
    }
  }
}

function CheckHitContract($contractType, $otherPlayer)
{
  $otherChar = &GetPlayerCharacter($otherPlayer);
  switch($contractType) {
    case "HITMARKEDFANG": return CheckMarked($otherPlayer) & CardNameContains($otherChar[0], "Fang");
    case "HITMARKEDCINDRA": return CheckMarked($otherPlayer) & CardNameContains($otherChar[0], "Cindra");
    default: return false;
  }
}

function CheckContracts($banishedBy, $cardBanished)
{
  global $CombatChain, $chainLinks;
  for($i = 0; $i < $CombatChain->NumCardsActiveLink(); ++$i) {
    $chainCard = $CombatChain->Card($i, cardNumber:true);
    if($chainCard->PlayerID() != $banishedBy) continue;
    $contractType = ContractType($chainCard->ID());
    if($contractType != "" && CheckContract($contractType, $cardBanished)) ContractCompleted($banishedBy, $chainCard->ID());
  }
  for($i = 0; $i < count($chainLinks); ++$i) {
    for($j = 0; $j < count($chainLinks[$i]); $j += ChainLinksPieces()) {
      if($chainLinks[$i][$j+1] != $banishedBy) continue;
      if($chainLinks[$i][$j+2] == 0) continue;
      $contractType = ContractType($chainLinks[$i][$j]);
      if($contractType != "" && CheckContract($contractType, $cardBanished)) ContractCompleted($banishedBy, $chainLinks[$i][$j]);
    }
  }
}

function ImperialWarHorn($player, $term)
{
  AddDecisionQueue("MULTIZONEINDICES", $player, $term . "ALLY&" . $term . "AURAS&"  . $term . "ITEMS&LANDMARK");
  AddDecisionQueue("SETDQCONTEXT", $player, "Choose a card to destroy", 1);
  AddDecisionQueue("CHOOSEMULTIZONE", $player, "<-", 1);
  AddDecisionQueue("MZDESTROY", $player, "-", 1);
}

function CheckContract($contractType, $cardBanished)
{
  switch($contractType) {
    case "REDPITCH": return PitchValue($cardBanished) == 1;
    case "YELLOWPITCH": return PitchValue($cardBanished) == 2;
    case "BLUEPITCH": return PitchValue($cardBanished) == 3;
    case "COST1ORLESS": return CardCost($cardBanished) <= 1 && CardCost($cardBanished) >= 0;
    case "COST2ORMORE": return CardCost($cardBanished) >= 2;
    case "AA": return CardType($cardBanished) == "AA";
    case "GOAGAIN": return HasGoAgain($cardBanished);
    case "NAA": return CardType($cardBanished) == "A";
    case "BLOCK2ORLESS": return BlockValue($cardBanished) <= 2 && BlockValue($cardBanished) >= 0;
    case "REACTIONS": return CardType($cardBanished) == "AR" || CardType($cardBanished) == "DR";
    case "NONACTION": return !IsActionCard($cardBanished);
    default: return false;
    }
}

function Shred($currentPlayer, $amount)
{
  $options = GetChainLinkCards(($currentPlayer == 1 ? 2 : 1), "", "C");
  if($options != "") {
    AddDecisionQueue("CHOOSECOMBATCHAIN", $currentPlayer, $options);
    AddDecisionQueue("COMBATCHAINDEFENSEMODIFIER", $currentPlayer, $amount, 1);
  }
}