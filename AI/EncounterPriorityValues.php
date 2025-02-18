<?php

//Priority Array Values:
//[0] = Block Priority
  //0 -> can't/won't block
  //0.1-0.9 -> will block with only if lethal
  //1.1-1.9 -> NOT IMPLEMENTED (planned to be cards that are blocked with to prevent on hits)
  //2.1-2.9 -> will willingly block with when efficient (higher prio blocked with first)
  //3.1-3.9 -> NOT IMPLEMENTED (if desired, I can add a type where these cards will be blocked with at the first opportunity)
  //10.1-10.9 -> the highest priority in this range will resolve to 0.1-0.9. additional cards in this range resolve to 2.1-2.9
  //11.1-10.9 -> exactly the same as above, just giving a second channel to work with
//[1] = Action Priority
  //0 -> can't/won't play
  //0.1-0.9 -> will play if possible (higher prio played first)
  //10.1-10.9 -> the highest priority in this range will resolve to 0. additional cards in this range resolve to 0.1-0.9
//[2] = Arsenal Action Priority (Implemented for arrows and other cards that change priority based on zone)
  //0 -> can't/won't play
  //0.1-0.9 -> will play if possible (higher prio played first)
//[3] = Reaction Priority
  //0 -> can't/won't play
  //0.1-0.9 -> will play if possible (higher prio played first)
  //10.1-10.9 -> the highest priority in this range will resolve to 0. additional cards in this range resolve to 0.1-0.9
//[4] = Arsenal Reaction Priority
  //0 -> can't/won't play
  //0.1-0.9 -> will play if possible (higher prio played first)
//[5] = Pitch Priority
  //0 -> can't/won't pitch
  //0.1-X.Y Will pitch if possible (higher prio pitched first) (There is no need to implement specific value channels here, I personally use 0.X for reds, 1.X for yellows, and 2.X for blues. Go wild if you want though)
//[6] = Arsenal Priority
  //0 -> can't/won't arsenal
  //0.1-0.9 -> will arsenal if possible (higher prio put in arsenal first)
//[7] = Permanent Priority
  //0 -> can't/won't activate
  //0.1->0.9 -> will activate if possible (higher prio activated first)

//TODO: Implement the following priority values: (These might not be the final indexes. Consider the above indexes final though)
//[8] = Banish Priority

function GetPriority($cardID, $heroID, $type)
{
  global $currentPlayer;
  switch($heroID)
  {
    case "ira_crimson_haze"://Ira
      $redBlockChance = SearchCount(SearchHand(2, pitch:1)) > 1 ? 0.8 : 0.5;
      $resources = &GetResources(2);
      switch($cardID)
      {
        //Kodachis, activate if possible
        case "harmonized_kodachi": case "harmonized_kodachi":
          $blueCount = SearchCount(SearchHand(2, pitch:3)) || $resources[0] > 1;
          $priority = array(0, $blueCount > 0 ? 0.95 : 0.1, 0, 0, 0, 0, 0, $blueCount > 0 ? 0.95 : 0.1); return $priority[$type];
        //Blue combo cards
        case "fluster_fist_blue": case "back_heel_kick_blue": case "crane_dance_blue": case "find_center_blue": case "rising_knee_thrust_blue": case "rushing_river_blue": case "whelming_gustwave_blue": case "winds_of_eternity_blue":
          $blockChance = (IsFirstTurn() || SearchCount(SearchHand(2, pitch:3)) > 1 ? .9 : .1);
          $priority = array($blockChance, 0.1, 0.1, 0, 0, 2.5, 0.1, 0);
          return $priority[$type];
        //Red attacks
        case "censor_red": $priority = array($redBlockChance - 0.2, 0.7, 0.7, 0, 0, 1.1, 0.8, 0); return $priority[$type];
        case "fluster_fist_red": $priority = array($redBlockChance + 0.1, 0.4, 0.4, 0, 0, 1.2, 0.7, 0); return $priority[$type];
        case "soulbead_strike_red": $priority = array($redBlockChance - 0.1, 0.8, 0.8, 0, 0, 1.2, 0.7, 0); return $priority[$type];
        case "torrent_of_tempo_red": $priority = array($redBlockChance - 0.2, 0.9, 0.9, 0, 0, 1.2, 0.7, 0); return $priority[$type];
        case "mauling_qi_red": case "pounding_gale_red": $priority = array($redBlockChance, 0.5, 0.5, 0, 0, 1.2, 0.7, 0); return $priority[$type];
        //Defense Reactions
        case "flic_flak_red": case "fate_foreseen_red": case "sink_below_red": $priority = array(0.9, 0.0, 0.0, 0.9, 0.9, 1.5, 0.9, 0); return $priority[$type];
        case "that_all_you_got_yellow": case "flic_flak_yellow": $priority = array(0.9, 0.0, 0.0, 0.9, 0.9, 2.5, 0.8, 0); return $priority[$type];
        case "flic_flak_blue":
          $blueCount = SearchCount(SearchHand(2, pitch:3));
          $priority = array(0.6, 0.0, 0.0, $blueCount > 1 ? 0.9 : 0.1, $blueCount > 1 ? 0.9 : 0, 2.9, 0.8, 0); return $priority[$type];
        //Equipment
        case "tide_flippers": $priority = array(0, 0, 0, 0, 0, 0, 0, 0); return $priority[$type];
        case "flick_knives": case "fyendals_spring_tunic": case "mask_of_momentum":
          //TODO: Allowing blocking if threatened by lethal damage
          $priority = array(0, 0, 0, 0, 0, 0, 0, 0);
          return $priority[$type];
        default: return 0;
      }
      case "fai_rising_rebellion"://Fai
        $resources = &GetResources(2);
        switch($cardID)
        {
          //Kodachis, activate if possible
          case "fai_rising_rebellion":
            $priorityValue = SearchCount(SearchHand(2, pitch:3)) > 0 || $resources[0] >= NumDraconicChainLinks() ? 1.0 : 0.0;
            $priority = array(0, $priorityValue, 0, 0, 0, 0, 0, $priorityValue); return $priority[$type];
          case "harmonized_kodachi": case "harmonized_kodachi":
            $blueCount = SearchCount(SearchHand(2, pitch:3)) > 0 || $resources[0] > 1;
            $priority = array(0, $blueCount > 0 ? 0.95 : 0.1, 0, 0, 0, 0, 0, $blueCount > 0 ? 0.95 : 0.1); return $priority[$type];
          //Art of War
          case "art_of_war_yellow":
            $playPriority = (!ArsenalEmpty($currentPlayer) && SearchCount(SearchHand(2, pitch:3)) > 0 ? 1.0 : 0.0);//Arsenal if not full hand or don't have blue
            $blueCount = SearchCount(SearchHand(2, pitch:3));
            $priority = array(0, $playPriority, $playPriority, 0, 0, 2.5, 1.0, 0);
            return $priority[$type];
          //Ravenous Rabble - Play early
          case "ravenous_rabble_red": $priority = array(0.1, 0.8, 0.8, 0, 0, 1.1, 0.5, 0); return $priority[$type];
          case "bittering_thorns_red": $priority = array(0.1, 0.85, 0.85, 0, 0, 1.5, 0.5, 0); return $priority[$type];
          case "mounting_anger_red": $priority = array(0.1, 0.85, 0.85, 0, 0, 1.5, 0.5, 0); return $priority[$type];
          case "brand_with_cinderclaw_red": $priority = array(0.1, 0.99, 0.99, 0, 0, 1.1, 0.5, 0); return $priority[$type];
          case "brand_with_cinderclaw_yellow": $priority = array(0.1, 0.99, 0.99, 0, 0, 1.1, 0.5, 0); return $priority[$type];
          case "brand_with_cinderclaw_blue": $priority = array(0.1, 0.99, 0.99, 0, 0, 1.1, 0.5, 0); return $priority[$type];
          case "rising_resentment_red": $priority = array(0.1, 0.9, 0.9, 0, 0, 1.1, 0.5, 0); return $priority[$type];
          case "ronin_renegade_red": $priority = array(0.1, 0.8, 0.8, 0, 0, 1.1, 0.5, 0); return $priority[$type];
          case "blaze_headlong_red": $priority = array(0.1, 0.7, 0.7, 0, 0, 1.1, 0.6, 0); return $priority[$type];
          case "lava_burst_red": $priority = array(0.1, 0.1, 0.1, 0, 0, 1.1, 0.7, 0); return $priority[$type];
          case "phoenix_flame_red": $priority = array(0, 0.7, 0.7, 0, 0, 0, 0.2, 0); return $priority[$type];
          case "double_strike_red": $priority = array(0.1, 0.8, 0.8, 0, 0, 1.1, 0.5, 0); return $priority[$type];
          case "ancestral_empowerment_red": $priority = array(0.1, 0.9, 0.9, 0, 0, 1.1, 0.5, 0); return $priority[$type];
          case "snatch_red": $priority = array(0.1, 0.1, 0.1, 0, 0, 1.1, 0.7, 0); return $priority[$type];
          case "spreading_flames_red": $priority = array(0.1, 1.0, 1.0, 0, 0, 1.1, 0.5, 0); return $priority[$type];
          //Salt the Wound - Play last, or pitch
          case "salt_the_wound_yellow": $priority = array(0.5, 0.1, 0.1, 0, 0, 2.5, 0.2, 0); return $priority[$type];
          //Blues
          case "soulbead_strike_blue": $priority = array(0.5, 0.3, 0.3, 0, 0, 3.5, 0.2, 0); return $priority[$type];
          case "warmongers_diplomacy_blue": $priority = array(0.8, 0.1, 0.1, 0, 0, 3.5, 0.1, 0); return $priority[$type];
          case "stab_wound_blue": $priority = array(0.8, 0.4, 0.4, 0, 0, 3.5, 0.1, 0); return $priority[$type];
          case "lava_vein_loyalty_blue": $priority = array(0.8, 0.5, 0.5, 0, 0, 3.1, 0.1, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE001":
        switch($cardID)
        {
          case "ROGUE002": $priority = array(0, 0.2, 0, 0, 0, 0, 0); return $priority[$type];
          case "stony_woottonhog_red": $priority = array(2.3, 0.8, 0.8, 0, 0, 0.5, 0.8); return $priority[$type];
          case "stony_woottonhog_yellow": $priority = array(2.5, 0.6, 0.6, 0, 0, 1.5, 0.6); return $priority[$type];
          case "stony_woottonhog_blue": $priority = array(2.7, 0.4, 0.4, 0, 0, 2.5, 0.4); return $priority[$type];
          default: return 0;
        }
      case "ROGUE004":
        switch($cardID)
        {
          case "barraging_brawnhide_red": $priority = array(10.4, 0.8, 0.8, 0, 0, 0.5, 0.5, 0); return $priority[$type];
          case "barraging_brawnhide_blue": $priority = array(11.5, 0.6, 0.6, 0, 0, 2.5, 0.3, 0); return $priority[$type];
          case "ROGUE005": $priority = array(0, 0.4, 0, 0, 0, 0, 0, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE003":
        switch($cardID)
        {
          case "ravenous_rabble_red": $priority = array(0.1, 0.3, 0.3, 0, 0, 0.1, 0.3, 0); return $priority[$type];
          case "ravenous_rabble_yellow": $priority = array(0.2, 0.2, 0.2, 0, 0, 0.2, 0.2, 0); return $priority[$type];
          case "ravenous_rabble_blue": $priority = array(0.3, 0.1, 0.1, 0, 0, 0.3, 0.1, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE006":
        switch($cardID)
        {
          case "shock_striker_blue": $priority = array(0.2, 0.5, 0.5, 0, 0, 2.6, 0.5, 0); return $priority[$type];
          case "lightning_press_red": $priority = array(0, 0, 0, 0.9, 0.9, 0.5, 0.4, 0); return $priority[$type];
          case "lightning_press_yellow": $priority = array(0, 0, 0, 0.8, 0.8, 1.5, 0.3, 0); return $priority[$type];
          case "lightning_press_blue": $priority = array(0, 0, 0, 0.7, 0.7, 2.5, 0.2, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE008":
        switch($cardID)
        {
          case "edge_of_autumn": $priority = array(0, 0.85, 0.85, 0, 0, 0.5, 0.85, 0); return $priority[$type];
          case "roar_of_the_tiger_yellow": $priority = array(0.5, 0.9, 0.9, 0, 0, 1.5, 0.9, 0); return $priority[$type];
          case "whirling_mist_blossom_yellow": $priority = array(0.5, 0.82, 0.82, 0, 0, 1.5, 0.82, 0); return $priority[$type];
          case "bittering_thorns_yellow": $priority = array(0.5, 0.8, 0.8, 0, 0, 1.5, 0.8, 0); return $priority[$type];
          case "head_leads_the_tail_red": $priority = array(0.5, 0.81, 0.81, 0, 0, 0.5, 0.81, 0); return $priority[$type];
          case "crouching_tiger": $priority = array(0.5, 0.7, 0.7, 0, 0, 0, 0.7, 0); return $priority[$type];
          case "command_and_conquer_red": $priority = array(0.5, 0.61, 0.61, 0, 0, 0.5, 0.61, 0); return $priority[$type];
          case "flying_kick_red": $priority = array(0.5, 0.6, 0.6, 0, 0, 0.5, 0.6, 0); return $priority[$type];
          case "ancestral_empowerment_red": $priority = array(0.5, 0, 0, 0.9, 0.9, 0.5, 0.9, 0); return $priority[$type];
          case "flying_kick_blue": case "dishonor_blue": case "find_center_blue": case "fluster_fist_blue": case "soulbead_strike_blue": case "stab_wound_blue": case "winds_of_eternity_blue": $priority = array(10.9, 0.1, 0.1, 0, 0, 2.5, 0, 0); return $priority[$type];
          case "mask_of_momentum": case "ironrot_gauntlet": case "ironrot_legs": case "ironrot_plate": $priority = array(0.9, 0, 0, 0, 0, 0, 0, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE009":
        switch($cardID)
        {
          case "endless_arrow_red": $priority = array(0.1, 0, 0.5, 0, 0, 0.1, 0.5, 0); return $priority[$type];
          case "searing_shot_red": $priority = array(0.2, 0, 0.4, 0, 0, 0.1, 0.4, 0); return $priority[$type];
          case "take_aim_red": $priority = array(0.5, 0.9, 0.9, 0, 0, 0.5, 0.9, 0); return $priority[$type];
          case "release_the_tension_red": $priority = array(0.3, 0.8, 0.8, 0, 0, 0.5, 0.8, 0); return $priority[$type];
          case "read_the_glide_path_red": $priority = array(0.4, 0.8, 0.7, 0, 0, 0.5, 0.7, 0); return $priority[$type];
          case "nimblism_red": $priority = array(0.6, 0.7, 0.6, 0, 0, 0.5, 0.6, 0); return $priority[$type];
          case "red_liner": $priority = array(0, 0.4, 0, 0, 0, 0, 0, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE010":
        switch($cardID)
        {
          case "bloodspill_invocation_red": $priority = array(0.4, 0.9, 0, 0, 0, 0.2, 0, 0); return $priority[$type];
          case "bloodspill_invocation_yellow": $priority = array(0.6, 0.8, 0, 0, 0, 1.2, 0, 0); return $priority[$type];
          case "bloodspill_invocation_blue": $priority = array(0.5, 0.3, 0, 0, 0, 2.2, 0, 0); return $priority[$type];
          case "runeblood_incantation_red": $priority = array(0.45, 0.85, 0, 0, 0, 0.3, 0, 0); return $priority[$type];
          case "runeblood_incantation_yellow": $priority = array(0.65, 0.75, 0, 0, 0, 1.3, 0, 0); return $priority[$type];
          case "runeblood_incantation_blue": $priority = array(0.55, 0.2, 0, 0, 0, 2.3, 0, 0); return $priority[$type];
          case "reek_of_corruption_red": $priority = array(0.1, 0.7, 0, 0, 0, 0.4, 0, 0); return $priority[$type];
          case "reek_of_corruption_yellow": $priority = array(0.3, 0.6, 0, 0, 0, 1.4, 0, 0); return $priority[$type];
          case "reek_of_corruption_blue": $priority = array(0.2, 0.5, 0, 0, 0, 2.4, 0, 0); return $priority[$type];
          case "spellblade_assault_red": $priority = array(0.15, 0.65, 0, 0, 0, 0.5, 0, 0); return $priority[$type];
          case "spellblade_assault_yellow": $priority = array(0.35, 0.6, 0, 0, 0, 1.5, 0, 0); return $priority[$type];
          case "spellblade_assault_blue": $priority = array(0.25, 0.4, 0, 0, 0, 2.5, 0, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE013":
        switch($cardID)
        {
          case "head_jab_red": $priority = array(1.7, 0.8, 0.9, 0, 0, 0.9, 0.7, 0); return $priority[$type];
          case "head_jab_yellow": $priority = array(2.2, 0.4, 0.9, 0, 0, 1.9, 0.5, 0); return $priority[$type];
          case "head_jab_blue": $priority = array(0.3, 0.1, 0.1, 0, 0, 2.9, 0.3, 0); return $priority[$type];
          case "leg_tap_red": $priority = array(11.1, 0.7, 0.9, 0, 0, 0.8, 0.8, 0); return $priority[$type];
          case "leg_tap_yellow": $priority = array(11.2, 0.3, 0.9, 0, 0, 1.8, 0.4, 0); return $priority[$type];
          case "surging_strike_red": $priority = array(1.9, 0.6, 0.9, 0, 0, 0.7, 0.9, 0); return $priority[$type];
          case "surging_strike_yellow": $priority = array(2.1, 0.2, 0.9, 0, 0, 1.7, 0.6, 0); return $priority[$type];
          case "harmonized_kodachi": $priority = array(0, 0.9, 0, 0, 0, 0, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE014":
        switch($cardID)
        {
          case "fleece_the_frail_blue": $priority = array(10.3, 0.2, 0.2, 0, 0, 2.2, 1.1, 0); return $priority[$type];
          case "plunder_the_poor_blue": $priority = array(10.3, 0.2, 0.2, 0, 0, 2.2, 1.1, 0); return $priority[$type];
          case "slay_the_scholars_blue": $priority = array(10.3, 0.2, 0.2, 0, 0, 2.2, 1.1, 0); return $priority[$type];
          case "sack_the_shifty_blue": $priority = array(10.3, 0.2, 0.2, 0, 0, 2.2, 1.1, 0); return $priority[$type];
          case "surgical_extraction_blue": $priority = array(10.2, 0.9, 0.9, 0, 0, 2.1, 1.2, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE015":
        switch($cardID)
        {
          case "crouching_tiger": $priority = array(0, 0.5, 0, 0, 0, 0, 0, 0); return $priority[$type];
          case "nimblism_red": $priority = array(0, 0.8, 0, 0, 0, 0.5, 0, 0); return $priority[$type];
          case "nimblism_yellow": $priority = array(0, 0.7, 0, 0, 0, 1.5, 0, 0); return $priority[$type];
          case "nimblism_blue": $priority = array(0, 0.6, 0, 0, 0, 2.5, 0, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE016":
        switch($cardID)
        {
          case "take_aim_red": $priority = array(0.2, 0.9, 0.9, 0, 0, 0.5, 0, 0); return $priority[$type];
          case "take_aim_yellow": $priority = array(0.4, 0.8, 0.8, 0, 0, 1.5, 0, 0); return $priority[$type];
          case "take_aim_blue": $priority = array(0.6, 0.7, 0.7, 0, 0, 2.5, 0, 0); return $priority[$type];
          case "release_the_tension_red": $priority = array(0.2, 0.9, 0.9, 0, 0, 0.5, 0, 0); return $priority[$type];
          case "release_the_tension_yellow": $priority = array(0.4, 0.8, 0.8, 0, 0, 1.5, 0, 0); return $priority[$type];
          case "release_the_tension_blue": $priority = array(0.6, 0.7, 0.7, 0, 0, 2.5, 0, 0); return $priority[$type];
          case "read_the_glide_path_red": $priority = array(0.2, 0.9, 0.9, 0, 0, 0.5, 0, 0); return $priority[$type];
          case "read_the_glide_path_yellow": $priority = array(0.4, 0.8, 0.8, 0, 0, 1.5, 0, 0); return $priority[$type];
          case "read_the_glide_path_blue": $priority = array(0.6, 0.7, 0.7, 0, 0, 2.5, 0, 0); return $priority[$type];
          case "red_liner": $priority = array(0, 0.4, 0, 0, 0, 0, 0, 0); return $priority[$type];
          case "searing_shot_red": $priority = array(0, 0, 0.6, 0, 0, 0, 0.6, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE017":
        switch($cardID)
        {
          case "gorganian_tome": $priority = array(0, ROGUE017GorgPrio(), ROGUE017GorgPrio(), 0, 0, 0, 1.9, 0); return $priority[$type];
          case "hundred_winds_red": $priority = array(0, 0.7, 0.7, 0, 0, 0.4, 0, 0); return $priority[$type];
          case "hundred_winds_yellow": $priority = array(0, 0.6, 0.6, 0, 0, 1.4, 0, 0); return $priority[$type];
          case "head_jab_red": $priority = array(0, 0.5, 0.5, 0, 0, 0.5, 0, 0); return $priority[$type];
          case "head_jab_yellow": $priority = array(0, 0.4, 0.4, 0, 0, 1.5, 0, 0); return $priority[$type];
          case "ride_the_tailwind_red": $priority = array(0, 0.5, 0.5, 0, 0, 0.5, 0, 0); return $priority[$type];
          case "ride_the_tailwind_yellow": $priority = array(0, 0.4, 0.4, 0, 0, 1.5, 0, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE018":
        switch($cardID)
        {
          case "entwine_earth_red": $priority = array(11.1, 0.8, 0.9, 0, 0, 0.1, 0.8, 0); return $priority[$type];
          case "earthlore_surge_red": $priority = array(10.9, 0.9, 0.9, 0, 0, 0.8, 0.7, 0); return $priority[$type];
          case "earthlore_surge_blue": $priority = array(10.2, 0.6, 0.1, 0, 0, 2.6, 0.5, 0); return $priority[$type];
          case "autumns_touch_red": $priority = array(10.7, 0.7, 0.9, 0, 0, 0.7, 0.8, 0); return $priority[$type];
          case "autumns_touch_blue": $priority = array(10.3, 0.5, 0.1, 0, 0, 2.5, 0.5, 0); return $priority[$type];
          case "evergreen_red": $priority = array(10.5, 0.4, 0.9, 0, 0, 0.6, 0.9, 0); return $priority[$type];
          case "evergreen_blue": $priority = array(10.4, 0.3, 0.1, 0, 0, 2.4, 0.6, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE019":
        switch($cardID)
        {
          case "soulbead_strike_red": $priority = array(0, 0.9, 0, 0, 0, 0.5, 0, 0); return $priority[$type];
          case "soulbead_strike_yellow": $priority = array(0, 0.9, 0, 0, 0, 1.5, 0, 0); return $priority[$type];
          case "soulbead_strike_blue": $priority = array(0, 0.9, 0, 0, 0, 2.5, 0, 0); return $priority[$type];
          case "crane_dance_red": $priority = array(0, 0.9, 0, 0, 0, 0.5, 0, 0); return $priority[$type];
          case "crane_dance_yellow": $priority = array(0, 0.9, 0, 0, 0, 1.5, 0, 0); return $priority[$type];
          case "crane_dance_blue": $priority = array(0, 0.9, 0, 0, 0, 2.5, 0, 0); return $priority[$type];
          case "find_center_blue": $priority = array(0, 0.9, 0, 0, 0, 2.5, 0, 0); return $priority[$type];
          case "herons_flight_red": $priority = array(0, 0.9, 0, 0, 0, 0.5, 0, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE020":
        switch($cardID)
        {
          case "tbone_blue": $priority = array(0.2, 0.2, 0.9, 0, 0, 2.1, 0.8, 0); return $priority[$type];
          case "zero_to_sixty_blue": $priority = array(0.1, 0.1, 0.9, 0, 0, 2.1, 0.8, 0); return $priority[$type];
          case "zipper_hit_blue": $priority = array(10.2, 0.3, 0.9, 0, 0, 2.1, 0.8, 0); return $priority[$type];
          case "combustible_courier_blue": $priority = array(10.2, 0.9, 0.9, 0, 0, 2.1, 0.8, 0); return $priority[$type];
          case "high_speed_impact_blue": $priority = array(10.2, 0.4, 0.9, 0, 0, 2.1, 0.8, 0); return $priority[$type];
          case "meganetic_shockwave_blue": $priority = array(10.2, 0.7, 0.9, 0, 0, 2.1, 0.8, 0); return $priority[$type];
          case "over_loop_blue": $priority = array(10.2, 0.8, 0.9, 0, 0, 2.1, 0.8, 0); return $priority[$type];
          case "pedal_to_the_metal_blue": $priority = array(10.2, 0.6, 0.9, 0, 0, 2.2, 0.8, 0); return $priority[$type];
          case "scramble_pulse_blue": $priority = array(10.2, 0.5, 0.9, 0, 0, 2.2, 0.9, 0); return $priority[$type];
          case "throttle_blue": $priority = array(10.2, 0.7, 0.9, 0, 0, 2.1, 0.8, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE021":
        switch($cardID)
        {
          case "barraging_beatdown_red": case "barraging_beatdown_yellow": case "barraging_beatdown_blue": $priority = array(2.1, 0.5, 0.6, 0, 0, 1.1, 0); return $priority[$type];
          case "smash_with_big_tree_red": $priority = array(0, 0.4, 0.1, 0, 0, 0.1, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE022":
        switch($cardID)
        {
          case "ghostly_visit_red": $priority = array(10.3, 0.7, 0.7, 0, 0, 0.5, 0.7, 0); return $priority[$type];
          case "ghostly_visit_yellow": $priority = array(11.1, 0.5, 0.5, 0, 0, 1.5, 0.5, 0); return $priority[$type];
          case "ghostly_visit_blue": $priority = array(11.4, 0.3, 0.3, 0, 0, 2.5, 0.3, 0); return $priority[$type];
          case "lunartide_plunderer_red": $priority = array(10.7, 0.9, 0.9, 0, 0, 0.5, 0.9, 0); return $priority[$type];
          case "lunartide_plunderer_blue": $priority = array(11.6, 0.4, 0.4, 0, 0, 2.5, 0.4, 0); return $priority[$type];
          case "void_wraith_red": $priority = array(10.5, 0.8, 0.8, 0, 0, 0.5, 0.8, 0); return $priority[$type];
          case "void_wraith_blue": $priority = array(11.2, 0.6, 0.6, 0, 0, 2.5, 0.6, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE023":
        switch($cardID)
        {
          case "spinal_crush_red": $priority = array(0.2, 0.8, 0.8, 0, 0, 0.5, 0.8, 0); return $priority[$type];
          case "disable_red": $priority = array(0.2, 0.8, 0.8, 0, 0, 0.5, 0.8, 0); return $priority[$type];
          case "thump_red": $priority = array(0.2, 0.8, 0.8, 0, 0, 0.5, 0.8, 0); return $priority[$type];
          case "disable_blue": $priority = array(0.1, 0.5, 0.5, 0, 0, 2.5, 0.5, 0); return $priority[$type];
          case "debilitate_blue": $priority = array(0.1, 0.5, 0.5, 0, 0, 2.5, 0.5, 0); return $priority[$type];
          case "chokeslam_blue": $priority = array(0.1, 0.5, 0.5, 0, 0, 2.5, 0.5, 0); return $priority[$type];
          case "thump_blue": $priority = array(0.1, 0.5, 0.5, 0, 0, 2.5, 0,5, 0); return $priority[$type];
          case "goliath_gauntlet": $priority = array(0.1, ROGUE023GauntletPrio(), 0, 0, 0, 0, 0, 0); return $priority[$type];
          case "steelbraid_buckler": $priority = array(2.6, 0, 0, 0, 0, 0, 0, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE024":
        switch($cardID)
        {
          case "blizzard_bolt_red": $priority = array(0.1, 0, 0.6, 0, 0, 0.5, 0.8, 0); return $priority[$type];
          case "blizzard_bolt_yellow": $priority = array(0.1, 0, 0.6, 0, 0, 1.5, 0.7, 0); return $priority[$type];
          case "blizzard_bolt_blue": $priority = array(0.1, 0, 0.6, 0, 0, 2.5, 0.6, 0); return $priority[$type];
          case "chilling_icevein_red": $priority = array(0.1, 0, 0.6, 0, 0, 0.5, 0.8, 0); return $priority[$type];
          case "chilling_icevein_yellow": $priority = array(0.1, 0, 0.6, 0, 0, 1.5, 0.7, 0); return $priority[$type];
          case "chilling_icevein_blue": $priority = array(0.1, 0, 0.6, 0, 0, 2.5, 0.6, 0); return $priority[$type];
          case "frost_lock_blue": $priority = array(0.1, 0, 0.6, 0, 0, 2.5, 0.6, 0); return $priority[$type];
          case "polar_blast_blue": $priority = array(0.2, 10.7, 0.9, 0, 0, 2.6, 0.9, 0); return $priority[$type];
          case "cold_snap_blue": $priority = array(0.2, 10.7, 0.9, 0, 0, 2.6, 0.9, 0); return $priority[$type];
          case "isenhowl_weathervane_blue": $priority = array(0.2, 10.7, 0.9, 0, 0, 2.6, 0.9, 0); return $priority[$type];
          case "winters_bite_blue": $priority = array(0.2, 10.7, 0.9, 0, 0, 2.6, 0.9, 0); return $priority[$type];
          case "chill_to_the_bone_blue": $priority = array(0.2, 10.7, 0.9, 0, 0, 2.6, 0.9, 0); return $priority[$type];
          case "shiver": $priority = array(0, ROGUE024BowPrio(), 0, 0, 0, 0, 0, 0); return $priority[$type];
          case "coronet_peak": $priority = array(0.1, ROGUE024PeakPrio(), 0, 0, 0, 0, 0, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE025":
        switch($cardID)
        {
          case "barraging_beatdown_red": $priority = array(0.1, ROGUE025ActionPrio(), ROGUE025ActionPrio(), 0, 0, 0.5, 0.9, 0); return $priority[$type];
          case "barraging_beatdown_yellow": $priority = array(0.1, ROGUE025ActionPrio(), ROGUE025ActionPrio(), 0, 0, 1.5, 0.9, 0); return $priority[$type];
          case "barraging_beatdown_blue": $priority = array(0.1, ROGUE025ActionPrio(), ROGUE025ActionPrio(), 0, 0, 2.5, 0.9, 0); return $priority[$type];
          case "clearing_bellow_blue": $priority = array(0.3, ROGUE025ActionPrio(), ROGUE025ActionPrio(), 0, 0, 2.5, 0.8, 0); return $priority[$type];
          case "high_roller_red": $priority = array(0.3, ROGUE025ActionPrio(), ROGUE025ActionPrio(), 0, 0, 0.5, 0.8, 0); return $priority[$type];
          case "high_roller_yellow": $priority = array(0.3, ROGUE025ActionPrio(), ROGUE025ActionPrio(), 0, 0, 1.5, 0.8, 0); return $priority[$type];
          case "high_roller_blue": $priority = array(0.3, ROGUE025ActionPrio(), ROGUE025ActionPrio(), 0, 0, 2.5, 0.8, 0); return $priority[$type];
          case "high_striker_red": $priority = array(0.3, ROGUE025ActionPrio(), ROGUE025ActionPrio(), 0, 0, 0.5, 0.8, 0); return $priority[$type];
          case "high_striker_yellow": $priority = array(0.3, ROGUE025ActionPrio(), ROGUE025ActionPrio(), 0, 0, 1.5, 0.8, 0); return $priority[$type];
          case "high_striker_blue": $priority = array(0.3, ROGUE025ActionPrio(), ROGUE025ActionPrio(), 0, 0, 2.5, 0.8, 0); return $priority[$type];
          case "ravenous_rabble_red": $priority = array(0.2, 0.7, 0.7, 0, 0, 0.5, 0.7, 0); return $priority[$type];
          case "ravenous_rabble_yellow": $priority = array(0.2, 0.7, 0.7, 0, 0, 1.5, 0.7, 0); return $priority[$type];
          case "ravenous_rabble_blue": $priority = array(0.2, 0.7, 0.7, 0, 0, 2.5, 0.7, 0); return $priority[$type];
          case "rok": $priority = array(0, ROGUE025RokPrio(), 0, 0, 0, 0, 0, 0); return $priority[$type];
          case "spellbound_creepers": $priority = array(2.6, 0, 0, 0, 0, 0, 0, 0); return $priority[$type];
          case "blazen_yoroi": $priority = array(2.6, 0, 0, 0, 0, 0, 0, 0); return $priority[$type];
          case "new_horizon": $priority = array(ROGUE025HorizonsPrio(), 0, 0, 0, 0, 0, 0, 0); return $priority[$type];
          case "copper": $priority = array(0, 0, 0, 0, 0, 0, 0, 0.9); return $priority[$type];
          default: return 0;
        }
      case "ROGUE026":
        switch($cardID)
        {
          case "command_and_conquer_red": case "cut_down_to_size_red": case "erase_face_red": case "humble_red": case "stony_woottonhog_red": case "surging_militia_red": $priority = array(10.6, 0.9, 0.9, 0, 0, 0.5, 0.9, 0); return $priority[$type];
          case "drone_of_brutality_blue": case "cut_down_to_size_blue": case "pursuit_of_knowledge_blue": case "humble_blue": case "stony_woottonhog_blue": case "surging_militia_blue": $priority = array(11.5, 0.6, 0.6, 0, 0, 2.5, 0.6, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE027":
        switch($cardID)
        {
          case "plow_through_red": case "dauntless_red": case "push_forward_red": $priority = array(2.7, 0.9, 0.9, 0, 0, 0.5, 0.9, 0); return $priority[$type];
          case "plow_through_yellow": case "dauntless_yellow": case "push_forward_yellow": $priority = array(2.8, 0.5, 0.9, 0, 0, 1.5, 0.9, 0); return $priority[$type];
          case "ironsong_determination_yellow": $priority = array(2.6, ROGUE027IronsongPrio(), ROGUE027IronsongPrio(), 0, 0, 1.4, 0.8, 0); return $priority[$type];
          case "quicksilver_dagger": $priority = array(0, 0.7, 0, 0, 0, 0, 0, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE028":
        switch($cardID)
        {
          case "veiled_intentions_red": case "phantasmify_red": $priority = array(0.4, 0.9, 0.9, 0, 0, 0.5, 0.9, 0); return $priority[$type];
          case "spears_of_surreality_red": case "spectral_prowler_red": $priority = array(0.6, 0.8, 0.8, 0, 0, 0.5, 0.8, 0); return $priority[$type];
          case "veiled_intentions_blue": case "phantasmify_blue": $priority = array(0.4, 0.6, 0.9, 0, 0, 2.5, 0.9, 0); return $priority[$type];
          case "spears_of_surreality_blue": case "spectral_prowler_blue": $priority = array(0.6, 0.6, 0.8, 0, 0, 2.5, 0.8, 0); return $priority[$type];
          case "spectral_rider_red": $priority = array(0.8, 0.7, 0.7, 0, 0, 0.5, 0.7, 0); return $priority[$type];
          case "spectral_rider_blue": $priority = array(0.8, 0.6, 0.7, 0, 0, 2.5, 0.7, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE029":
        switch($cardID)
        {
          case "spill_blood_red": case "cleave_red": $priority = array(0.4, 0.9, 0.9, 0, 0, 0.5, 0.9, 0); return $priority[$type];
          case "out_for_blood_red": case "stroke_of_foresight_red": $priority = array(0.2, 0, 0, 0.7, 0.7, 0.5, 0.7, 0); return $priority[$type];
          case "overpower_red": $priority = array(0.3, 0, 0, 0.9, 0.9, 0.5, 0.8, 0); return $priority[$type];
          case "out_for_blood_blue": case "stroke_of_foresight_blue": $priority = array(0.1, 0, 0, 0.5, 0.9, 2.5, 0.5, 0); return $priority[$type];
          case "overpower_blue": $priority = array(0.1, 0, 0, 0.6, 0.9, 2.5, 0.5, 0); return $priority[$type];
          case "merciless_battleaxe": $priority = array(0, 0.8, 0, 0, 0, 0, 0, 0); return $priority[$type];
          default: return 0;
        }
      case "ROGUE030":
        switch($cardID)
        {
          case "pummel_red": $priority = array(10.4, 0, 0, 1.7, 1.9, 0.3, 1.4); return $priority[$type];
          case "pummel_blue": $priority = array(11.3, 0, 0, 1.6, 1.8, 2.2, 0.4); return $priority[$type];
          case "pursuit_of_knowledge_blue": $priority = array(0.7, 1.7, 1.8, 0, 0, 2.1, 0.8); return $priority[$type];
          case "command_and_conquer_red": $priority = array(0.8, 1.8, 1.9, 0, 0, 0.1, 2.8); return $priority[$type];
          case "brutal_assault_blue": $priority = array(11.8, 1.1, 1.7, 0, 0, 2.5, 0.5); return $priority[$type];
          case "brutal_assault_red": $priority = array(2.2, 1.2, 1.6, 0, 0, 0.2, 0.4); return $priority[$type];
          case "adrenaline_rush_red": $priority = array(2.1, 1.3, 1.5, 0, 0, 0.5, 0.9); return $priority[$type];
          case "arcanite_skullcap": $priority = array(2.1, 0, 0, 0, 0, 0, 0, 0); return $priority[$type];
          case "goliath_gauntlet": $priority = array(0, 1.9, 0, 0, 0, 0, 0, 1.9); return $priority[$type];
          default: return 0;
        }
      case "ROGUE031":
        switch($cardID)
        {
          case "ball_lightning_blue": $priority = array(0, 0.7); return $priority[$type];
          case "ball_lightning_yellow": $priority = array(0, 0.6); return $priority[$type];
          case "ball_lightning_red": $priority = array(0, 0.5); return $priority[$type];
          case "blaze_headlong_red": $priority = array(0.5, 0.4); return $priority[$type];
          case "lava_burst_red": $priority = array(10.6, 0.1, 0.2, 0, 0, 0, 0.5); return $priority[$type];
          case "ROGUE032": $priority = array(0, ROG032ActionPoint()); return $priority[$type];
          case "flamescale_furnace": $priority = array(2.2, 0, 0, 0, 0); return $priority[$type];
          default: return 0;
        }
      default: return 0;
  }
}

function EncounterBlocksFirstTurn($character) //This is a way to allow encounters to block or not block on the first turn. All encounters block out unless specifically asked not to.
{
  switch($character)
  {
    default: return true;
  }
}

function ROGUE017GorgPrio()
{
  global $currentPlayer;
  $hand = &GetHand($currentPlayer);
  $arsenal = &GetArsenal($currentPlayer);
  $grave = &GetDiscard($currentPlayer);
  $totalTomes = 0;
  for($i = 0; $i < count($hand); ++$i)
  {
    if($hand[$i] == "gorganian_tome") ++$totalTomes;
  }
  for($i = 0; $i < count($arsenal); ++$i)
  {
    if($arsenal[$i] == "gorganian_tome") ++$totalTomes;
  }
  for($i = 0; $i < count($grave); ++$i)
  {
    if($grave[$i] == "gorganian_tome") ++$totalTomes;
  }
  if($totalTomes >= 3) return 1.9;
  else return 0;
}

function ROGUE023GauntletPrio()
{
  global $currentTurnEffects, $currentPlayer;
  for ($i = count($currentTurnEffects) - CurrentTurnEffectsPieces(); $i >= 0; $i -= CurrentTurnEffectsPieces()) {
    if ($currentTurnEffects[$i + 1] == $currentPlayer) {
      switch ($currentTurnEffects[$i]) {
        case "towering_titan_red": case "towering_titan_yellow": case "towering_titan_blue": return 0.9;
        default:
          break;
      }
    }
  }
  return 0;
}

function ROGUE024BowPrio()
{
  global $currentPlayer;
  $hand = &GetHand($currentPlayer);
  $found = false;
  for ($i = 0; $i < count($hand); ++$i)
  {
    if(CardSubType($hand[$i]) == "Arrow") $found = true;
  }
  return $found ? 0.8 : 0;
}

function ROGUE024PeakPrio()
{
  global $currentPlayer;
  $hand = &GetHand($currentPlayer);
  $found = false;
  for ($i = 0; $i < count($hand); ++$i)
  {
    if(CardSubType($hand[$i]) == "Arrow") $found = true;
  }
  $arsenal = &GetArsenal($currentPlayer);
  if(CardSubType($arsenal[0]) == "Arrow") $found = true;
  return $found ? 0 : 0.8;
}

function ROGUE025ActionPrio()
{
  global $currentPlayer;
  $hand = &GetHand($currentPlayer);
  $permanents = &GetPermanents($currentPlayer);
  $resources = &GetResources($currentPlayer);
  $arsenal = &GetArsenal($currentPlayer);
  $found = false;
  for($i = 0; $i < count($permanents); $i += PermanentPieces())
  {
    switch($permanents[$i])
    {
      case "ROGUE803": $found = true;
      default: break;
    }
  }
  //WriteLog("TheoreticalResources->".count($hand)+$resources[0]+count($arsenal));
  if(($found && count($hand)+$resources[0]+count($arsenal)>=2) || (count($hand)+$resources[0]+count($arsenal)>=3)) return 0.8;
  else return 0;
}

function ROGUE025RokPrio()
{
  global $currentPlayer;
  $hand = &GetHand($currentPlayer);
  if(count($hand) == 0) return 0.85;
  else return 0;
}

function ROGUE025HorizonsPrio()
{
  global $currentPlayer;
  $arsenal = &GetArsenal($currentPlayer);
  if(count($arsenal) > 0) return 2.9;
  else return 0.9;
}

function ROGUE027IronsongPrio()
{
  global $currentPlayer;
  $resources = &GetResources($currentPlayer);
  if($resources[0] != 0) return 0.8;
  else return 0;
}

function ROG031Blaze()
{
  //TODO: Get Combat Chain
  return 0.4;
}

function ROG032ActionPoint()
{
  global $actionPoints;
  if($actionPoints > 1){
    return 0.9;
  }
  else {
    return 0;
  }
}
?>
