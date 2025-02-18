<?php

  function MONTalentPlayAbility($cardID, $from, $resourcesPaid, $target="-", $additionalCosts = "")
  {
    global $currentPlayer, $mainPlayer, $CS_NumAddedToSoul, $CombatChain, $CS_PlayIndex;
    $otherPlayer = $currentPlayer == 1 ? 2 : 1;
    switch($cardID)
    {
      case "great_library_of_solana":
        if($from == "PLAY") DestroyLandmark(GetClassState($currentPlayer, $CS_PlayIndex));
        return "";
      case "halo_of_illumination":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "HAND");
        AddDecisionQueue("MAYCHOOSEHAND", $currentPlayer, "<-");
        AddDecisionQueue("REMOVEMYHAND", $currentPlayer, "-", 1);
        AddDecisionQueue("ADDSOUL", $currentPlayer, "HAND", 1);
        AddDecisionQueue("ALLCARDTALENTORPASS", $currentPlayer, "LIGHT", 1);
        AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
        return "";
      case "soul_food_yellow":
        $hand = &GetHand($currentPlayer);
        for($i = 0; $i < count($hand); ++$i) AddSoul($hand[$i], $currentPlayer, "HAND");
        $hand = [];
        return "";
      case "tome_of_divinity_yellow":
        Draw($currentPlayer);
        Draw($currentPlayer);
        if(GetClassState($currentPlayer, $CS_NumAddedToSoul) > 0) Draw($currentPlayer);
        return "";
      case "invigorating_light_red": case "invigorating_light_yellow": case "invigorating_light_blue":
        if(count(GetSoul($currentPlayer)) == 0) AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "glisten_red": case "glisten_yellow": case "glisten_blue":
        if($cardID == "glisten_red") $count = 4;
        else if($cardID == "glisten_yellow") $count = 3;
        else $count = 2;
        for($i=0; $i<$count; ++$i) {
          AddDecisionQueue("FINDINDICES", $currentPlayer, "WEAPON");
          AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
          AddDecisionQueue("ADDATTACKCOUNTERS", $currentPlayer, "1", 1);
        }
        if($currentPlayer == $mainPlayer) AddCurrentTurnEffect($cardID, $currentPlayer);
        else AddNextTurnEffect($cardID, $currentPlayer);
        return "";
      case "seek_enlightenment_red": case "seek_enlightenment_yellow": case "seek_enlightenment_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "blinding_beam_red": case "blinding_beam_yellow": case "blinding_beam_blue":
        if($cardID == "blinding_beam_red") $amount = -3;
        else if($cardID == "blinding_beam_yellow") $amount = -2;
        else $amount = -1;
        if($target != "-") $CombatChain->Card(intval($target))->ModifyPower($amount);
        return "";
      case "ray_of_hope_yellow":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "ebon_fold":
        AddDecisionQueue("FINDINDICES", $currentPlayer, "HAND");
        AddDecisionQueue("MAYCHOOSEHAND", $currentPlayer, "<-");
        AddDecisionQueue("REMOVEMYHAND", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIBANISH", $currentPlayer, "HAND,NA", 1);
        AddDecisionQueue("ALLCARDTALENTORPASS", $currentPlayer, "SHADOW", 1);
        AddDecisionQueue("DRAW", $currentPlayer, "-", 1);
        return "";
      case "doomsday_blue":
        PlayAlly("blasmophet_the_soul_harvester", $currentPlayer);
        return "";
      case "eclipse_blue":
        PlayAlly("ursur_the_soul_reaper", $currentPlayer);
        return "";
      case "shadow_puppetry_red":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "tome_of_torment_red":
        Draw($currentPlayer);
        return "";
      case "howl_from_beyond_red": case "howl_from_beyond_yellow": case "howl_from_beyond_blue":
        AddCurrentTurnEffect($cardID, $currentPlayer);
        return "";
      case "spew_shadow_red": case "spew_shadow_yellow": case "spew_shadow_blue":
        if($cardID == "spew_shadow_red") $maxCost = 2;
        else if($cardID == "spew_shadow_yellow") $maxCost = 1;
        else $maxCost = 0;
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYBANISH:type=AA;maxCost=" . $maxCost);
        AddDecisionQueue("CHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZOP", $currentPlayer, "SETPIECE,1=spew_shadow_red", 1);
        return "";
      case "blood_tribute_red": case "blood_tribute_yellow": case "blood_tribute_blue":
        if($cardID == "blood_tribute_red") $amount = 3;
        else if($cardID == "blood_tribute_yellow") $amount = 2;
        else $amount = 1;
        PlayerOpt($currentPlayer, $amount);
        AddDecisionQueue("FINDINDICES", $currentPlayer, "TOPDECK", 1);
        AddDecisionQueue("MULTIREMOVEDECK", $currentPlayer, "<-", 1);
        AddDecisionQueue("MULTIBANISH", $currentPlayer, "DECK,NA", 1);
        return "";
      case "eclipse_existence_blue":
        $theirCharacter = GetPlayerCharacter($otherPlayer);
        if(TalentContains($theirCharacter[0], "LIGHT", $otherPlayer)) {
          if(GetHealth($currentPlayer) > GetHealth($otherPlayer)) {
            AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYDISCARD:type=A&MYDISCARD:type=AA");
            AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
            AddDecisionQueue("MZBANISH", $currentPlayer, "GY,-," . $currentPlayer, 1);
            AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
          }
          AddCurrentTurnEffect($cardID, $currentPlayer);
        }
        return "";
      case "blasmophet_the_soul_harvester":
        $otherPlayer = $currentPlayer == 2 ? 1 : 2;
        AddDecisionQueue("MULTIZONEINDICES", $currentPlayer, "MYHAND:talent=SHADOW");
        AddDecisionQueue("SETDQCONTEXT", $currentPlayer, "Choose if you want to banish a shadow card");
        AddDecisionQueue("MAYCHOOSEMULTIZONE", $currentPlayer, "<-", 1);
        AddDecisionQueue("MZREMOVE", $currentPlayer, "-", 1);
        AddDecisionQueue("MULTIBANISH", $currentPlayer, "HAND,NA", 1);
        if(!IsAllyAttackTarget()) {
          AddDecisionQueue("PASSPARAMETER", $otherPlayer, "0", 1);
          AddDecisionQueue("MULTIBANISHSOUL", $otherPlayer, "-", 1);
        }
        return "";
      default: return "";
    }
  }

  function MONTalentHitEffect($cardID)
  {
    global $combatChainState, $CCS_GoesWhereAfterLinkResolves, $defPlayer;
    switch($cardID)
    {
      case "illuminate_red": case "illuminate_yellow": case "illuminate_blue": $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "SOUL"; break;
      case "rising_solartide_red": case "rising_solartide_yellow": case "rising_solartide_blue": $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "SOUL"; break;
      case "soul_harvest_blue":
        if(IsHeroAttackTarget()) {
          $numSoul = count(GetSoul($defPlayer));
          if($numSoul > 0) {
            LoseHealth($numSoul, $defPlayer);
            $char = &GetPlayerCharacter($defPlayer);
            if($char[0] == "levia_redeemed") WriteLog("<span style='color:red;'>I find your lack of faith disturbing.</span>");
            else if($char[0] == "levia_redeemed") WriteLog("<span style='color:red;'>When I left you, I was but the learner. Now I am the master.</span>");
          }
          for($i=0; $i<$numSoul; ++$i) BanishFromSoul($defPlayer);
        }
        break;
      case "lunartide_plunderer_red": case "lunartide_plunderer_yellow": case "lunartide_plunderer_blue":
        if(IsHeroAttackTarget()) {
          BanishFromSoul($defPlayer);
          $combatChainState[$CCS_GoesWhereAfterLinkResolves] = "BANISH";
        }
        break;
      default: break;
    }
  }

  function ShadowPuppetryHitEffect()
  {
    global $mainPlayer;
    AddDecisionQueue("DECKCARDS", $mainPlayer, "0");
    AddDecisionQueue("SETDQVAR", $mainPlayer, "0", 1);
    AddDecisionQueue("SETDQCONTEXT", $mainPlayer, "Choose if you want to banish <0> with ".CardLink("shadow_puppetry_red", "shadow_puppetry_red"), 1);
    AddDecisionQueue("YESNO", $mainPlayer, "if_you_want_to_banish_the_card", 1);
    AddDecisionQueue("NOPASS", $mainPlayer, "-", 1);
    AddDecisionQueue("PARAMDELIMTOARRAY", $mainPlayer, "0", 1);
    AddDecisionQueue("MULTIREMOVEDECK", $mainPlayer, "0", 1);
    AddDecisionQueue("MULTIBANISH", $mainPlayer, "DECK,-", 1);
    AddDecisionQueue("SETDQVAR", $mainPlayer, "0", 1);
    AddDecisionQueue("WRITELOG", $mainPlayer, "<0> was banished.", 1);
  }

  function IsImmuneToBloodDebt($player)
  {
    global $CS_Num6PowBan;
    $character = &GetPlayerCharacter($player);
    $characterID = ShiyanaCharacter($character[0]);
    if($character[1] == 2 && $characterID == "levia_redeemed") return true;
    if($character[1] == 2 && ($characterID == "levia_shadowborn_abomination" || $characterID == "levia") && GetClassState($player, $CS_Num6PowBan) > 0) return true;
    return false;
  }
