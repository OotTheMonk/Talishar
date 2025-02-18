<?php

include "EncounterDictionary.php";

function ClearPhase($player)
{
  $decisionQueue = &GetZone($player, "DecisionQueue");
  array_shift($decisionQueue);
  array_shift($decisionQueue);
  array_shift($decisionQueue);
  array_shift($decisionQueue);
  array_shift($decisionQueue);
  array_shift($decisionQueue);
}

function AddDecisionQueue($phase, $player, $parameter1="-", $parameter2="-", $parameter3="-", $subsequent=0, $makeCheckpoint=0)
{
  $decisionQueue = &GetZone($player, "DecisionQueue");
  array_push($decisionQueue, $phase);
  array_push($decisionQueue, $parameter1);
  array_push($decisionQueue, $parameter2);
  array_push($decisionQueue, $parameter3);
  array_push($decisionQueue, $subsequent);
  array_push($decisionQueue, $makeCheckpoint);
}

function PrependDecisionQueue($phase, $player, $parameter1="-", $parameter2="-", $parameter3="-", $subsequent=0, $makeCheckpoint=0)
{
  $decisionQueue = &GetZone($player, "DecisionQueue");
  array_unshift($decisionQueue, $makeCheckpoint);
  array_unshift($decisionQueue, $subsequent);
  array_unshift($decisionQueue, $parameter3);
  array_unshift($decisionQueue, $parameter2);
  array_unshift($decisionQueue, $parameter1);
  array_unshift($decisionQueue, $phase);
}

  function ProcessDecisionQueue($player)
  {
    ContinueDecisionQueue($player);
  }

  //Must be called with the my/their context
  function ContinueDecisionQueue($player, $lastResult="")
  {
    global $makeCheckpoint;
    $decisionQueue = &GetZone($player, "DecisionQueue");
    if(count($decisionQueue) == 0)
    {
      return;
    }
    $phase = $decisionQueue[0];
    $parameter1 = $decisionQueue[1];
    $parameter2 = $decisionQueue[2];
    $parameter3 = $decisionQueue[3];
    $subsequent = $decisionQueue[4];
    $makeCheckpoint = $decisionQueue[5];
    $return = "PASS";
    ClearPhase($player);
    if($subsequent != 1 || is_array($lastResult) || strval($lastResult) != "PASS") $return = DecisionQueueStaticEffect($phase, $player, ($parameter1 == "<-" ? $lastResult : $parameter1), $parameter2, $parameter3, $lastResult);
    //if(strval($return) != "NOTSTATIC") ClearPhase($player);
    if(strval($return) == "NOTSTATIC") PrependDecisionQueue($phase, $player, $parameter1, $parameter2, $parameter3, $subsequent, $makeCheckpoint);
    if($parameter1 == "<-" && !is_array($lastResult) && $lastResult == "-1") $return = "PASS";//Collapse the rest of the queue if this decision point has invalid parameters
    if(strval($return) != "NOTSTATIC")
    {
      ContinueDecisionQueue($player, $return);
    }
  }

  function DecisionQueueStaticEffect($phase, $player, $parameter1, $parameter2, $parameter3, $lastResult)
  {
    global $numPlayers;
    //WriteLog($phase);
    switch($phase)
    {
      case "SETENCOUNTER":
        $params = explode("-", $parameter1);
        $encounter = &GetZone($player, "Encounter");
        $encounter->encounterID = $params[0];
        $encounter->subphase = $params[1];
        //WriteLog("Setting Encounter -> " . $encounter->encounterID . "-" . $encounter->subphase);
        InitializeEncounter($player);
        return 1;
      case "CAMPFIRE":
        switch($lastResult)
        {
          case "Rest":
            $health = &GetZone($player, "Health");
            $gain = (20 - $health[0] > 10 ? 10 : 20 - $health[0]);
            if($gain < 0) $gain = 0;
            $health[0] += $gain;
            WriteLog("You rested and gained " . $gain . " life.");
            break;
          case "Learn":
            WriteLog("You studied and learned a powerful specialization.");
            PrependDecisionQueue("CHOOSECARD", $player, "steelblade_supremacy_red,glistening_steelblade_yellow,singing_steelblade_yellow");
            break;
          case "Reflect":
            WriteLog("You reflected on the trials of the day, and may remove a card.");
            PrependDecisionQueue("REMOVEDECKCARD", $player, GetRandomCards("Deck,All"), "-", "NoReroll");
            break;
          default: break;
        }
        return 1;
      case "BATTLEFIELD":
        switch($lastResult)
        {
          case "Loot":
            WriteLog("You've found some equipment to salvage, and a gold piece in the pocket of one of the fallen.");
            PrependDecisionQueue("CHOOSECARD", $player, "ironrot_helm", "-", "NoReroll");
            $encounter = &GetZone(1, "Encounter");
            $encounter->gold += 1;
            break;
          case "Pay_Respects":
            WriteLog("You honor the fallen. While their mortal form is gone, their stories live on. You carry their spirit with you. You gain 2 health.");
            PrependDecisionQueue("CHOOSECARD", $player, "remembrance_yellow", "-", "NoReroll");
            $health = &GetZone(1, "Health");
            $health[0] += 2;
            break;
          default: break;
        }
        return 1;
      case "LIBRARY":
        switch($lastResult)
        {
          case "Search":
            WriteLog("You searched the library and found an interesting book about fighting techniques.");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Talent-Generic"), "Reward,Class-Class-Talent-Generic");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Talent-Generic"), "Reward,Class-Class-Talent-Generic");
            break;
          case "Leave":
            break;
        }
        return 1;
      case "BLACKSMITH":
        $encounter = &GetZone($player, "Encounter");
        switch($lastResult)
        {
          case "Spend_some_time_forging_equipment_for_yourself":
            WriteLog("You used your might to craft some armor.");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Equipment,Common,Head"), "-", "NoReroll");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Equipment,Common,Chest"), "-", "NoReroll");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Equipment,Common,Arms"), "-", "NoReroll");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Equipment,Common,Legs"), "-", "NoReroll");
            break;
          case "Ask_the_blacksmith_to_make_you_a_piece_of_equipment":
            WriteLog("A giant gave you a legendary gift.");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Equipment,Legendary"), "-", "NoReroll");
            break;
          case "Leave":
            break;
        }
        return 1;
      case "OLDHAG":
        switch($lastResult)
        {
          case "Offer_her_1_life":
            $health = &GetZone($player, "Health");
            $health[0] += -1;
            if(rand(1,4) == 3)
            {
              WriteLog("You gave the old hag some of your blood. It was enough.");
              PrependDecisionQueue("CHOOSECARD", $player, GetPowers());
            }
            else
            {
              WriteLog("You gave the old hag some of your blood, but it wasn't enough.");
              if($health[0] > 1) {
                PrependDecisionQueue("OLDHAG", $player, "-");
                PrependDecisionQueue("BUTTONINPUT", $player, "Offer_her_1_life,Leave");
              }
              else  {
                PrependDecisionQueue("OLDHAG", $player, "-");
                PrependDecisionQueue("BUTTONINPUT", $player, "Leave");
              }
            }
          case "Leave":
            break;
        }
        return 1;
      case "BACKGROUND":
        $deck = &GetZone($player, "Deck");
        $character = &GetZone($player, "Character");
        $encounter = &GetZone($player, "Encounter");
        GiveUniversalEquipment();
        switch($lastResult)
        {
          case "The_Volcai_Sellsword":
            $encounter->background = "Saber";
            array_push($character, "cintari_saber", "cintari_saber"); //Cintari Sabers, both
            array_push($deck, "blade_runner_blue", "slice_and_dice_yellow", "outland_skirmish_red"); //Blade Runner B, Slice and Dice Y, Outland Skirmish R
            break;
          case "The_Lowly_Solanian":
            $encounter->background = "Dawnblade";
            array_push($character, "dawnblade");
            array_push($deck, "overpower_blue", "ironsong_response_yellow", "plow_through_red"); //Overpower B, Ironsong Response Y, Plow Through R
            break;
          case "The_Fierce_Warrior":
            $encounter->background = "Hatchet";
            array_push($character, "hatchet_of_body", "hatchet_of_mind"); //Body and Mind
            array_push($deck, "blade_runner_blue", "felling_swing_yellow", "outland_skirmish_red"); //Blade Runner B, Felling Swing y, Outland Skirmish R
            break;
          case "Spiders_Deserter":
            $encounter->background = "Battleaxe";
            array_push($character, "merciless_battleaxe");
            array_push($deck, "overpower_blue", "sharpen_steel_yellow", "felling_swing_red"); //Overpower B, Sharpen Steel Y, Felling Swing R
            break;
          case "The_Everfest_Showman":
            $encounter->background = "Anothos";
            array_push($character, "anothos");
            array_push($deck, "thunder_quake_red", "crush_confidence_blue", "debilitate_red", "chokeslam_red", "pummel_red", "zealous_belting_red");
            break;
          case "The_Reclusive_Blacksmith":
            $encounter->background = "TitanFist";
            array_push($character, "titans_fist", "seasoned_saviour"); //Titan's Fist and Seasoned Saviour
            array_push($deck, "shield_bash_yellow", "shield_wall_blue", "crush_confidence_red", "crush_confidence_yellow", "fate_foreseen_blue", "unmovable_red");
            break;
          case "The_Slumbering_Giant":
            $encounter->background = "Sledge";
            array_push($character, "sledge_of_anvilheim");
            array_push($deck, "embolden_blue", "seismic_stir_red", "emerging_power_yellow", "emerging_dominance_blue", "raging_onslaught_blue", "lead_the_charge_blue");
            break;
          case "The_Ancient_Ollin":
            $encounter->background = "Shiver";
            array_push($character, "shiver");
            array_push($deck, "blizzard_bolt_red", "blizzard_bolt_yellow", "flake_out_yellow", "flake_out_blue", "ice_quake_blue", "weave_ice_red", "winters_bite_blue", "polar_blast_blue");
            break;
          case "The_Exuberant_Adventurer":
            $encounter->background = "Voltaire";
            array_push($character, "voltaire_strike_twice");
            array_push($deck, "buzz_bolt_yellow", "frazzle_red", "dazzling_crescendo_yellow", "dazzling_crescendo_blue", "weave_lightning_red", "lightning_press_blue", "electrify_yellow", "ball_lightning_red");
            break;
          case "The_Hired_Crow":
            $encounter->background = "DeathDealer";
            array_push($character, "death_dealer");
            array_push($deck, "blizzard_bolt_red", "chilling_icevein_yellow", "frazzle_yellow", "buzz_bolt_red", "electrify_blue", "lightning_press_red", "polar_blast_blue", "ice_quake_yellow");
            break;
          case "The_Roadside_Bandit":
            $encounter->background = "RedLiner";
            array_push($character, "red_liner");
            array_push($deck, "boltn_shot_red", "boltn_shot_yellow", "searing_shot_red", "searing_shot_yellow", "take_aim_red", "take_aim_yellow", "read_the_glide_path_red", "read_the_glide_path_yellow");
            break;
          case "The_Rebel_Organizer":
            $encounter->background = "Emberblade";
            array_push($character, "searing_emberblade");
            array_push($deck, "phoenix_flame_red", "phoenix_flame_red", "rise_from_the_ashes_red", "flamecall_awakening_red", "inflame_red");
            break;
          case "The_Travelling_Duo":
            $encounter->background = "Kodachi";
            array_push($character, "harmonized_kodachi", "harmonized_kodachi");
            array_push($deck, "lava_burst_red", "searing_touch_red", "breaking_point_red", "brand_with_cinderclaw_blue", "lava_vein_loyalty_blue");
            break;
          case "The_Archaeologist":
            $encounter->background = "Edge";
            array_push($character, "edge_of_autumn");
            array_push($deck, "engulfing_flamewave_red", "engulfing_flamewave_yellow", "rebellious_rush_red", "rebellious_rush_blue", "pummel_blue");
            break;
          case "The_Emperor":
            $encounter->background = "Contract";
            array_push($character, "spiders_bite", "scale_peeler");
            array_push($deck, "cut_to_the_chase_yellow", "fleece_the_frail_blue", "sack_the_shifty_red", "plunder_the_poor_red", "rob_the_rich_yellow");
            break;
          case "The_Doctor":
            $encounter->background = "Stealth";
            array_push($character, "spiders_bite", "nerve_scalpel");
            array_push($deck, "prowl_yellow", "infect_red", "sedate_red", "wither_red", "back_stab_blue");
            break;
          case "The_Warrior":
            $encounter->background = "Reaction";
            array_push($character, "spiders_bite", "orbitoclast");
            array_push($deck, "isolate_red", "malign_red", "razors_edge_red", "razors_edge_blue", "spike_with_bloodrot_red");
            break;
        }
        return 1;
      case "STARTADVENTURE":
        switch($lastResult)
        {
          case "Change_your_hero":
            AddDecisionQueue("SETENCOUNTER", $player, "002-PickMode");
            break;
          case "Change_your_bounty":
            AddDecisionQueue("SETENCOUNTER", $player, "003-PickMode");
            break;
          case "Change_your_difficulty":
            AddDecisionQueue("SETENCOUNTER", $player, "004-PickMode");
            break;
          case "Begin_adventure":
            AddDecisionQueue("SETENCOUNTER", $player, "005-PickMode");
            break;
          /*case "Shop_Test":
            AddDecisionQueue("SHOP", $player, "blade_runner_red,blessing_of_steel_red,cleave_red,valiant_dynamo,ironrot_plate,ROGUE501");
            break;*/
        }
        return 1;
      case "CHOOSEDIFFICULTY":
        switch($lastResult)
        {
          case "Easy":
          case "Normal":
          case "hard":
            break;
        }
        return 1;
      case "CHOOSEHERO": //Logic for hero selection moved to ResetHero function
          ResetHero($player, $lastResult);
        return 1;
      case "CHOOSEADVENTURE":
        switch($lastResult)
        {
          case "Ira":
          $encounter = &GetZone($player, "Encounter");
          $encounter->adventure = "Ira";
          break;
        }
        return 1;
      case "VOLTHAVEN":
        switch($lastResult)
        {
          case "Enter_Stream":
            $health = &GetZone($player, "Health");
            if(rand(0,9) < 3)
            {
              $health[0] -= 3;
              if($health[0] < 0) $health[0] = 1;
              WriteLog("You mistimed your jump and got zapped by the energy.");
            }
            else {
              $health[0] += 5;
              if($health[0] > 20) $health[0] = 20;
              WriteLog("You timed your jump perfectly and feel reinvigorated by the stream of energy.");
            }
            break;
          case "Leave":
            break;
        }
        return 1;
      case "ENLIGHTENMENT":
        switch($lastResult)
        {
          case "Quietly_Pray":
            WriteLog("Your spirit is reinvigorated and your strength is renewed. You gain 7 health.");
            $health = &GetZone($player, "Health");
            $health[0] += 7;
            break;
          case "Leave":
            break;
          case "Make_an_offering":
            PrependDecisionQueue("ENLIGHTENMENT", $player, "-");
            PrependDecisionQueue("BUTTONINPUT", $player, "Make_another_offering,Receive_a_small_blessing");
            PrependDecisionQueue("REMOVEDECKCARD", $player, GetRandomCards("Deck,4"), "Deck,4");
            break;
          case "Make_another_offering":
            PrependDecisionQueue("ENLIGHTENMENT", $player, "-");
            PrependDecisionQueue("BUTTONINPUT", $player, "Make_a_final_offering,Receive_a_sizable_blessing");
            PrependDecisionQueue("REMOVEDECKCARD", $player, GetRandomCards("Deck,4"), "Deck,4");
            break;
          case "Make_a_final_offering":
            PrependDecisionQueue("ENLIGHTENMENT", $player, "-");
            PrependDecisionQueue("BUTTONINPUT", $player, "Receive_an_incredible_blessing");
            PrependDecisionQueue("REMOVEDECKCARD", $player, GetRandomCards("Deck,4"), "Deck,4");
            break;
          case "Receive_a_small_blessing":
            WriteLog("Your charity is recognized. May Sol shine upon you.");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Talent-Generic,ForcedRarity-Common"), "Reward,Class-Class-Talent-Generic,ForcedRarity-Common");
            break;
          case "Receive_a_sizable_blessing":
            WriteLog("Your act of selflessness is recognized and celebrated. Contemplate this treasure in your travels.");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Talent-Generic,ForcedRarity-Rare"), "Reward,Class-Class-Talent-Generic,ForcedRarity-Rare");
            break;
          case "Receive_an_incredible_blessing":
            WriteLog("Your depth of character and virtue is boundless. Please honor the church by carrying it's teaching throughout Rathe.");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Talent-Generic,ForcedRarity-Majestic"), "Reward,Class-Class-Talent-Generic,ForcedRarity-Majestic");
            break;
          }
          return 1;
      case "ROCKS":
        switch($lastResult)
        {
          case "Trade_1_gold_pieces_for_the_stone":
            $encounter = &GetZone($player, "Encounter");
            $encounter->gold -= 1;
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("ResourceGems"), "-", "NoReroll");
            break;
          case "Decline_his_offer_and_move_on":
            break;
        }
        return 1;
      case "CLIFF":
        switch($lastResult)
        {
          case "Its_best_to_leave_the_gold_behind":
            $encounter = &GetZone($player, "Encounter");
            $encounter->gold = 0;
            WriteLog("You lost all your gold over the edge of a cliff.");
            break;
          case "Attempt_to_retrieve_your_coins":
            $encounter = &GetZone($player, "Encounter");
            $encounter->gold += 4;
            $health = &GetZone($player, "Health");
            $health[0] -= 4;
            WriteLog("You stumble down a cliff, losing some life but retrieving some gold. You even found some gold someone else had lost.");
            break;
        }
        return 1;
      case "PEACEFULMONK":
        switch($lastResult)
        {
          case "Tell_him_your_story":
            PrependDecisionQueue("REMOVEDECKCARD", $player, GetRandomCards("Deck,4"), "Deck,4");
            break;
          case "Listen_to_his_story":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Talent-Generic"), "Reward,Class-Class-Talent-Generic");
            break;
          case "Sit_peacefully":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Talent-Generic"), "Reward,Class-Class-Talent-Generic");
            PrependDecisionQueue("REMOVEDECKCARD", $player, GetRandomCards("Deck,4"), "Deck,4");
            break;
          case "Leave":
            break;
        }
        return 1;
      case "SPARRINGKNIGHT":
        switch($lastResult)
        {
          case "Spar_for_a_short_while":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Deck,4"), "Deck,4");
            break;
          case "Spar_until_nightfall":
            $health = &GetZone($player, "Health");
            $health[0] -= 4;
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Deck,4"), "Deck,4");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Deck,4"), "Deck,4");
            break;
          case "Politely_decline":
            break;
        }
        return 1;
      case "SHIYANASPEC":
        switch($lastResult)
        {
          case "Your_face":
            $encounter = &GetZone($player, "Encounter");
            switch($encounter->hero)
            {
              case "Dorinthea":
                PrependDecisionQueue("CHOOSECARD", $player, "steelblade_supremacy_red", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "steelblade_supremacy_red,steelblade_supremacy_red", "-", "NoReroll");
                break;
              case "Bravo":
                PrependDecisionQueue("CHOOSECARD", $player, "crippling_crush_red", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "crippling_crush_red,crippling_crush_red", "-", "NoReroll");
                break;
              case "Fai":
                PrependDecisionQueue("CHOOSECARD", $player, "rise_up_red", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "rise_up_red,rise_up_red", "-", "NoReroll");
                break;
              case "Lexi":
                PrependDecisionQueue("CHOOSECARD", $player, "light_it_up_yellow", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "light_it_up_yellow,light_it_up_yellow", "-", "NoReroll");
                break;
              case "Arakni":
                PrependDecisionQueue("CHOOSECARD", $player, "regicide_blue", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "regicide_blue,regicide_blue", "-", "NoReroll");
                break;
            }
            break;
          case "The_face_of_another":
            $encounter = &GetZone($player, "Encounter");
            switch($encounter->hero)
            {
              case "Dorinthea": $cardChoices = array("microprocessor_blue", "teklo_core_blue", "blood_on_her_hands_yellow"); break;
              case "Bravo": $cardChoices = array("alpha_rampage_red", "arknight_ascendancy_red", "shake_down_red"); break;
              case "Fai": $cardChoices = array("force_of_nature_blue", "whirling_mist_blossom_yellow", "spring_tidings_yellow"); break;
              case "Lexi": $cardChoices = array("red_in_the_ledger_red", "frost_hex_blue", "endless_winter_red"); break;
              case "Arakni": $cardChoices = array("steelblade_supremacy_red", "crippling_crush_red", "soul_reaping_red"); break;
            }
            $randNum = rand(0, 2);
            PrependDecisionQueue("CHOOSECARD", $player, $cardChoices[$randNum], "-", "NoReroll");
            PrependDecisionQueue("CHOOSECARD", $player, $cardChoices[$randNum].",".$cardChoices[$randNum], "-", "NoReroll");
            break;
        }
        return 1;
      case "CHEST":
        switch($lastResult)
        {
          case "Open_the_brown_chest":
            $encounter = &GetZone($player, "Encounter");
            $foundGold = rand(4, 10);
            $encounter->gold += $foundGold;
            WriteLog("You dug through the chest and found " . $foundGold . " Gold.");
            break;
          case "Open_the_white_chest":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Equipment"), "Equipment");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Equipment"), "Equipment");
            break;
          case "Open_the_blue_chest":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Class-Class"), "Reward,Class-Class-Class-Class");
            break;
          case "Open_the_red_chest":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Talent-Talent-Talent-Talent"), "Reward,Talent-Talent-Talent-Talent");
            break;
          case "Open_the_green_chest":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Generic-Generic-Generic-Generic"), "Reward,Generic-Generic-Generic-Generic");
            break;
          case "Open_the_purple_chest":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Power,3,Common"), "Power,3,Common");
            break;
          case "Open_the_ornate_brown_chest":
            $encounter = &GetZone($player, "Encounter");
            $foundGold = rand(14, 20);
            $encounter->gold += $foundGold;
            WriteLog("You dug through the chest and found " . $foundGold . " Gold.");
            break;
          case "Open_the_ornate_white_chest":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Equipment,Legendary"), "Equipment,Legendary");
            break;
          case "Open_the_ornate_blue_chest":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Class,ForcedRarity-Majestic"), "Reward,Class-Class-Class,ForcedRarity-Majestic");
            break;
          case "Open_the_ornate_red_chest":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Talent-Talent-Talent,ForcedRarity-Majestic"), "Reward,Talent-Talent-Talent,ForcedRarity-Majestic");
            break;
          case "Open_the_ornate_green_chest":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Generic-Generic-Generic,ForcedRarity-Majestic"), "Reward,Generic-Generic-Generic,ForcedRarity-Majestic");
            break;
          case "Open_the_ornate_purple_chest":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Power,2,Majestic"), "Power,2,Majestic");
            break;
          case "Leave":
            break;
        }
        return 1;
      case "GAMBLER":
        switch($lastResult)
        {
          case "Partake_in_some_entertainment":
            $entertainment = rand(1, 4);
            $encounter = &GetZone($player, "Encounter");
            $encounter->gold += rand(-4, -8);
            if($encounter->gold < 0) $encounter->gold = 0;
            switch($entertainment)
            {
              case 1:
                WriteLog("You gambled and managed to win nearly every game! You should leave before they get angry.");
                $encounter->rerolls += 3;
                break;
              case 2:
                WriteLog("You gambled and did about as well as you expected.");
                $encounter->rerolls += 2;
                break;
              case 3:
                WriteLog("You gambled and barely scraped by with a win.");
                $encounter->rerolls += 1;
                break;
              case 4:
                WriteLog("You gambled and did horribly, losing every game. You should leave before you lose all your belongings");
                break;
            }
          case "Leave":
            break;
        }
        return 1;
      case "MIRROR":
        switch($lastResult)
        {
          case "Stare_into_the_mirror":
            $deck = &GetZone($player, "Deck");
            $newDeck = [];
            for($i = 0; $i < count($deck); ++$i)
            {
              if(CardSubtype($deck[$i]) == "Power")
              {
                array_push($newDeck, $deck[$i]);
              }
              else
              {
                array_push($newDeck, $deck[$i]);
                array_push($newDeck, $deck[$i]);
              }
            }
            $deck = $newDeck;
            WriteLog("You stare into the mirror. You feel your mind become cluttered with thoughts of thoughts.");
            break;
          case "Shatter_the_mirror":
            $deck = &GetZone($player, "Deck");
            $newDeck = [];
            for($i = 0; $i < count($deck); ++$i)
            {
              if(CardSubtype($deck[$i]) == "Power")
              {
                array_push($newDeck, $deck[$i]);
              }
            }
            $deck = $newDeck;
            WriteLog("You shatter the mirror. Your mind becomes empty, and you forget your journey. A short while later, it comes to you.");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Talent-Generic,ForcedRarity-Majestic"), "Reward,Class-Class-Talent-Generic,ForcedRarity-Majestic");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Talent-Generic,ForcedRarity-Majestic"), "Reward,Class-Class-Talent-Generic,ForcedRarity-Majestic");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Talent-Generic,ForcedRarity-Majestic"), "Reward,Class-Class-Talent-Generic,ForcedRarity-Majestic");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Talent-Generic,ForcedRarity-Majestic"), "Reward,Class-Class-Talent-Generic,ForcedRarity-Majestic");
            break;
          case "Leave":
            break;
        }
        return 1;
      case "WEAPONMASTER":
        switch($lastResult)
        {
          case "Take_what_you_can":
            $encounter = &GetZone($player, "Encounter");
            $character = &GetZone($player, "Character");
            switch($encounter->hero)
            {
              case "Dorinthea":
                PrependDecisionQueue("CHOOSECARD", $player, "nebula_blade", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "ravenous_meataxe", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "teklo_plasma_pistol", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "zephyr_needle", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "zephyr_needle,zephyr_needle", "-", "NoReroll");
                $encounter->background = "AllWeps";
                break;
              case "Bravo":
                PrependDecisionQueue("CHOOSECARD", $player, "dread_scythe", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "ravenous_meataxe", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "krakens_aethervein", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "orbitoclast", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "orbitoclast,orbitoclast", "-", "NoReroll");
                break;
              case "Fai":
                PrependDecisionQueue("CHOOSECARD", $player, "scale_peeler", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "scale_peeler,scale_peeler", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "spiders_bite", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "spiders_bite,spiders_bite", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "rosetta_thorn", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "winters_wail", "-", "NoReroll");
                break;
              case "Lexi":
                PrependDecisionQueue("CHOOSECARD", $player, "barbed_castaway", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "dreadbore", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "sandscour_greatbow", "-", "NoReroll");
                break;
              case "Arakni":
                PrependDecisionQueue("CHOOSECARD", $player, "harmonized_kodachi", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "rampart_of_the_rams_head", "-", "NoReroll");
                PrependDecisionQueue("CHOOSECARD", $player, "zephyr_needle", "-", "NoReroll");
                break;
            }
            break;
          case "Put_the_bodies_to_rest":
            PrependDecisionQueue("CHOOSECARD", $player, "sigil_of_solace_red", "-", "NoReroll");
            //PrependDecisionQueue("CHOOSECARD", $player, "sigil_of_solace_red,sigil_of_solace_red", "-", "NoReroll");
            //PrependDecisionQueue("CHOOSECARD", $player, "sigil_of_solace_red,sigil_of_solace_red,sigil_of_solace_red", "-", "NoReroll");
            break;
        }
        return 1;
      case "TWISTEDLIBRARY":
        switch($lastResult)
        {
          case "Search_the_library":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Talent-Generic,SpecialTag-AnyPool"), "Reward,Class-Class-Talent-Generic,SpecialTag-AnyPool");
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Reward,Class-Class-Talent-Generic,SpecialTag-AnyPool"), "Reward,Class-Class-Talent-Generic,SpecialTag-AnyPool");
            break;
          case "Donate_to_the_library":
            PrependDecisionQueue("REMOVEDECKCARD", $player, GetRandomCards("Deck,4"), "Deck,4");
            break;
          case "leave":
            break;
        }
        return 1;
      case "COTTAGEWITCH":
        switch($lastResult)
        {
          case "Take_a_bite":
            $health = &GetZone($player, "Health");
            $health[0] -= $parameter2;
            $nextCost = $parameter2 + 1;
            PrependDecisionQueue("COTTAGEWITCH", $player, "-", $nextCost);
            if($health[0] > $nextCost) PrependDecisionQueue("BUTTONINPUT", $player, "Take_a_bite,Leave");
            else PrependDecisionQueue("BUTTONINPUT", $player, "Leave");
            PrependDecisionQueue("REMOVEDECKCARD", $player, GetRandomCards("Deck,4"), "Deck,4");
            break;
          case "Save_some_for_later":
            AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
            PrependDecisionQueue("CHOOSECARD", $player, "mutated_mass_blue", "-", "NoReroll");
            PrependDecisionQueue("CHOOSECARD", $player, "mutated_mass_blue,mutated_mass_blue", "-", "NoReroll");
            break;
          case "Leave":
            WriteLog("The Witch is dissapointed in your rush to leave, but understands.");
            AddDecisionQueue("SETENCOUNTER", $player, "009-PickMode");
            break;
        }
        return 1;
      case "SIGILSOLACE":
        switch($lastResult)
        {
          case "Rest_at_the_sigil":
            WriteLog("You feel more sure of yourself then you ever have been before. You can feel your destiny begin to change.");
            $encounter = &GetZone($player, "Encounter");
            $encounter->rerolls = 20;
            break;
          case "Take_the_sigil":
            PrependDecisionQueue("CHOOSECARD", $player, "sigil_of_solace_red", "-", "NoReroll");
            PrependDecisionQueue("CHOOSECARD", $player, "sigil_of_solace_red,sigil_of_solace_red", "-", "NoReroll");
            PrependDecisionQueue("CHOOSECARD", $player, "sigil_of_solace_red,sigil_of_solace_red,sigil_of_solace_red", "-", "NoReroll");
            break;
        }
        return 1;
      case "CLEARPOOL":
        switch($lastResult)
        {
          case "Cleanse_yourself_in_the_pool":
            WriteLog("As you get out of the pool, there is a weight on you that was not there before.");
            $encounter = &GetZone($player, "Encounter");
            $encounter->cleanse = true;
            break;
          case "Let_the_waters_wash_over_you":
            PrependDecisionQueue("CHOOSECARD", $player, GetRandomCards("Power,3,Rare"), "Power,3,Rare");
            break;
          case "Leave":
            break;
        }
        return 1;
      case "CROSSROADS":
        switch($lastResult)
        {
          case "Go_towards_the_smoke_rising_in_the_distance": //campfire
            PrependDecisionQueue("SETENCOUNTER", $player, "007-PickMode");
            break;
          case "Follow_the_sounds_of_laughter": //shop
            $encounter = &GetZone(1, "Encounter");
            PrependDecisionQueue("SETENCOUNTER", $player, "008-PickMode");
            break;
          case "Search_through_the_treasures":
            PrependDecisionQueue("SETENCOUNTER", $player, "010-PickMode");
            break;
          case "Approach_your_destination":
            PrependDecisionQueue("SETENCOUNTER", $player, "108-BeforeFight");
            break;
          case "You_wander_through_a_fresh_battlefield": //battlefield
            PrependDecisionQueue("SETENCOUNTER", $player, "201-PickMode");
            break;
          case "You_find_a_great_library": //library
            PrependDecisionQueue("SETENCOUNTER", $player, "202-PickMode");
            break;
          case "You_see_a_small_temple_a_ways_from_the_path": //Enlightenment
            PrependDecisionQueue("SETENCOUNTER", $player, "205-PickMode");
            break;
          case "A_wandering_trader_approaches_you": //Rocks
            PrependDecisionQueue("SETENCOUNTER", $player, "208-PickMode");
            break;
          case "Follow_the_sound_of_metallic_ringing": //Giant Forge
            PrependDecisionQueue("SETENCOUNTER", $player, "204-PickMode");
            break;
          case "You_see_one_of_the_most_beautiful_views_in_all_of_Rathe": //Cliffside
            PrependDecisionQueue("SETENCOUNTER", $player, "210-PickMode");
            break;
          case "You_find_a_small_smithing_hut": //Armorer
            PrependDecisionQueue("SETENCOUNTER", $player, "211-PickMode");
            break;
          case "You_come_across_a_small_dojo": //DuplicateCard
            PrependDecisionQueue("SETENCOUNTER", $player, "212-PickMode");
            break;
          case "A_lavish_noble_passes_you_by": //Noble shop
            PrependDecisionQueue("SETENCOUNTER", $player, "213-PickMode");
            break;
          case "You_pass_a_strange_man_in_robes": //Monk
            PrependDecisionQueue("SETENCOUNTER", $player, "214-PickMode");
            break;
          case "A_knight_approaches_you_asking_to_spar": //Sparring Knight
            PrependDecisionQueue("SETENCOUNTER", $player, "215-PickMode");
            break;
          case "A_radiant_woman_comes_across_your_path": //Shiyana Spec
            PrependDecisionQueue("SETENCOUNTER", $player, "216-PickMode");
            break;
          case "You_find_a_small_brown_chest": //Gold Chest
            PrependDecisionQueue("SETENCOUNTER", $player, "217-PickMode");
            break;
          case "You_find_a_small_white_chest": //Equipment Chest
            PrependDecisionQueue("SETENCOUNTER", $player, "218-PickMode");
            break;
          case "You_find_a_small_blue_chest": //Class Chest
            PrependDecisionQueue("SETENCOUNTER", $player, "219-PickMode");
            break;
          case "You_find_a_small_red_chest": //Talent Chest
            PrependDecisionQueue("SETENCOUNTER", $player, "220-PickMode");
            break;
          case "You_find_a_small_green_chest": //Generic Chest
            PrependDecisionQueue("SETENCOUNTER", $player, "221-PickMode");
            break;
          case "You_find_a_small_purple_chest": //Power Chest
            PrependDecisionQueue("SETENCOUNTER", $player, "222-PickMode");
            break;
          case "You_find_an_ornate_brown_chest": //Gold Chest
            PrependDecisionQueue("SETENCOUNTER", $player, "223-PickMode");
            break;
          case "You_find_an_ornate_white_chest": //Equipment Chest
            PrependDecisionQueue("SETENCOUNTER", $player, "224-PickMode");
            break;
          case "You_find_an_ornate_blue_chest": //Class Chest
            PrependDecisionQueue("SETENCOUNTER", $player, "225-PickMode");
            break;
          case "You_find_an_ornate_red_chest": //Talent Chest
            PrependDecisionQueue("SETENCOUNTER", $player, "226-PickMode");
            break;
          case "You_find_an_ornate_green_chest": //Generic Chest
            PrependDecisionQueue("SETENCOUNTER", $player, "227-PickMode");
            break;
          case "You_find_an_ornate_purple_chest": //Power Chest
            PrependDecisionQueue("SETENCOUNTER", $player, "228-PickMode");
            break;
          case "You_stumble_into_a_lively_tavern": //Gambler
            PrependDecisionQueue("SETENCOUNTER", $player, "229-PickMode");
            break;
          case "You_see_smoke_rising_in_the_distance": //shop, but it is funky
            PrependDecisionQueue("SETENCOUNTER", $player, "230-PickMode");
            break;
          case "You_find_a_large_shrine": //shrine
            PrependDecisionQueue("SETENCOUNTER", $player, "231-PickMode");
            break;
          case "You_find_a_large_mirror": //mirror
            PrependDecisionQueue("SETENCOUNTER", $player, "232-PickMode");
            break;
          case "You_visit_an_old_friend": //Weaponmaster
            PrependDecisionQueue("SETENCOUNTER", $player, "233-PickMode");
            break;
          case "You_come_across_a_strange_library": //twisted library
            PrependDecisionQueue("SETENCOUNTER", $player, "234-PickMode");
            break;
          case "You_find_an_old_cottage": //witch
            PrependDecisionQueue("SETENCOUNTER", $player, "235-PickMode");
            break;
          case "You_see_a_beautiful_sigil": //Sigil of Solace
            PrependDecisionQueue("SETENCOUNTER", $player, "236-PickMode");
            break;
          case "You_find_a_clear_pool": //clear pool
            PrependDecisionQueue("SETENCOUNTER", $player, "237-PickMode");
            break;
          case "Return_to_the_pool": //clear pool
            PrependDecisionQueue("SETENCOUNTER", $player, "238-PickMode");
            break;
          case "Take_the_scenic_route_through_the_back_streets": //Stealthy Stabber
            PrependDecisionQueue("SETENCOUNTER", $player, "114-BeforeFight");
            break;
          case "Make_your_way_up_through_Metrix": //Combustible Courier
            PrependDecisionQueue("SETENCOUNTER", $player, "120-BeforeFight");
            break;
            /* case "Catch_a_ferry_across_the_lake":
              PrependDecisionQueue("SETENCOUNTER", $player, "107-BeforeFight");
              break; */
          case "Venture_into_the_forest_and_attempt_to_sneak_past": //Ravenous Rabble
            PrependDecisionQueue("SETENCOUNTER", $player, "102-BeforeFight");
            break;
          case "Approach_the_roadblock_head_on": //SwingWithBigTree
            PrependDecisionQueue("SETENCOUNTER", $player, "121-BeforeFight");
            break;
          case "Take_precaution_and_find_a_different_way_across": //Crane Master
            PrependDecisionQueue("SETENCOUNTER", $player, "115-BeforeFight");
            break;
          case "Use_the_cover_of_darkness_to_cross": //Sparrow Master
            PrependDecisionQueue("SETENCOUNTER", $player, "117-BeforeFight");
            break;
          case "Turn_back_and_take_the_long_way_around": //Quickshot Apprentice
            PrependDecisionQueue("SETENCOUNTER", $player, "106-BeforeFight");
            break;
          case "Leave_the_town_immediately":
            PrependDecisionQueue("SETENCOUNTER", $player, "118-BeforeFight");
            break;
          case "Brave_the_bridge":
            PrependDecisionQueue("SETENCOUNTER", $player, "119-BeforeFight");
            break;
          case "Ignore_your_instincts_and_stop_for_the_night": //Tortured Soul
            PrependDecisionQueue("SETENCOUNTER", $player, "122-BeforeFight");
            break;
          case "You_notice_a_mountain_pass_you_can_move_through": //Pass Guardian
            PrependDecisionQueue("SETENCOUNTER", $player, "123-BeforeFight");
            break;
          case "Travel_through_a_thin_ravine": //Club Thug
            PrependDecisionQueue("SETENCOUNTER", $player, "130-BeforeFight");
            break;
          case "Travel_through_a_nearby_cavern": //Azvolai
            PrependDecisionQueue("SETENCOUNTER", $player, "131-BeforeFight");
            break;
          case "Attempt_to_cross_the_river_here": //Bow Fisherman
            PrependDecisionQueue("SETENCOUNTER", $player, "124-BeforeFight");
            break;
          case "Explore_the_cave": //mini-boss
            PrependDecisionQueue("SETENCOUNTER", $player, "500-PickMode");
            break;
          case "Stay_very_briefly_to_stock_up": //Shady Merchant
            PrependDecisionQueue("SETENCOUNTER", $player, "126-BeforeFight");
            break;
          case "Catch_a_ferry_across_the_lake": //Swashbuckler
            PrependDecisionQueue("SETENCOUNTER", $player, "127-BeforeFight");
            break;
          case "Travel_upstream_to_the_nearest_town": //Spectral Image
            PrependDecisionQueue("SETENCOUNTER", $player, "128-BeforeFight");
            break;
          case "Travel_downstream_to_find_a_bridge": //Mindscarred Man
            PrependDecisionQueue("SETENCOUNTER", $player, "129-BeforeFight");
            break;
          default: //
            PrependDecisionQueue("SETENCOUNTER", $player, "101-BeforeFight");
            break;
        }
        return 1;
      default:
        return "NOTSTATIC";
    }
  }
function ResetHero($player, $hero="Dorinthea")
  {
  $heroFileArray = file("Heroes/" . $hero . ".txt", FILE_IGNORE_NEW_LINES);
  $health = &GetZone($player, "Health");
  array_push($health, 20); //TODO: Base on hero health
  $character = &GetZone($player, "Character");
  $character = explode(" ", $heroFileArray[0]);
  $deck = &GetZone($player, "Deck");
  $deck = explode(" ", $heroFileArray[1]);
  $encounter = &GetZone($player, "Encounter");
  $encounter->hero = $hero;
  }

?>
